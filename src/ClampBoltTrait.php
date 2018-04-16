<?php namespace Sukohi\ClampBolt;

use Sukohi\ClampBolt\App\Attachment;
use Symfony\Component\HttpFoundation\File\File;

trait ClampBoltTrait {

	private $clamp_bolt_attachments = [],
			$clamp_bolt_detachments = [],
			$clamp_bolt_deletions = [];

	public function attach($key, $path = '', $parameters = [], $deleting_flag = false) {

		$keys = (!is_array($key)) ? [$key => $path] : $key;

		foreach ($keys as $key => $file_path) {

            $path = $this->getAttachmentFilePath($key, $file_path);

			if(file_exists($path)) {

				$this->clamp_bolt_attachments[$key] = [
					'path' => $path,
					'parameters' => $parameters
				];
                $this->clamp_bolt_deletions[$key] = $deleting_flag;

				if(isset($this->clamp_bolt_detachments[$key])) {

					unset($this->clamp_bolt_detachments[$key]);

				}

			} else {

				throw new \Exception('File does not exist.');

			}

		}

		return $this;

	}

	public function detach($key, $deleting_flag = false) {

		$keys = (!is_array($key)) ? [$key] : $key;

		foreach ($keys as $key) {

			$this->clamp_bolt_detachments[$key] = true;
			$this->clamp_bolt_deletions[$key] = $deleting_flag;

			if(isset($this->clamp_bolt_attachments[$key])) {

				unset($this->clamp_bolt_attachments[$key]);

			}

		}

		return $this;

	}

	public function detachAll($deleting_flag = false) {

        foreach ($this->attachments as $attachment) {

            $this->detach($attachment->key, $deleting_flag);

        }

		return $this;

	}

	public function saveAttachments() {

		if(empty($this->clamp_bolt_attachments) && empty($this->clamp_bolt_detachments)) {

			return true;

		}

		if($this->attachments->count() > 0) {

			foreach ($this->attachments as $attachment) {

				$key = $attachment->key;

				if(isset($this->clamp_bolt_attachments[$key])) {

					$path = $this->clamp_bolt_attachments[$key]['path'];
					$parameters = $this->clamp_bolt_attachments[$key]['parameters'];

					if($attachment->full_path != $path) {

                        if(isset($this->clamp_bolt_deletions[$key]) && $this->clamp_bolt_deletions[$key]) {

                            @unlink($attachment->full_path);

                        }

						$file = new File($path);
						$attachment->dir = $file->getPath();
						$attachment->filename = $file->getFilename();

					}

					$this->saveAttachment($attachment, $key, $path, $parameters);
					unset($this->clamp_bolt_attachments[$key]);

				}

				if(isset($this->clamp_bolt_detachments[$key])) {

					$path = $attachment->full_path;

					if($attachment->delete()) {

                        $this->fireAttachmentEvent('detached', $attachment);

						if(isset($this->clamp_bolt_deletions[$key]) && $this->clamp_bolt_deletions[$key]) {

                            @unlink($path);

                        }

					}

				}

			}

		}

		if(count($this->clamp_bolt_attachments) > 0) {

			foreach ($this->clamp_bolt_attachments as $key => $attachment) {

				$this->saveAttachment(null, $key, $attachment['path'], $attachment['parameters']);

			}

		}

		$this->load('attachments');
		return true;

	}

	private function saveAttachment($attachment = null, $key, $path, $parameters) {

		if(is_null($attachment)) {

			$attachment = new Attachment;

		}

		$file = new File($path);
		$attachment->model = $this->getCurrentClassName();
		$attachment->model_id = $this->id;
		$attachment->key = $key;
		$attachment->dir = $file->getPath();
		$attachment->filename = $file->getFilename();
		$attachment->extension = $file->getExtension();
		$attachment->mime_type = $file->getMimeType();
		$attachment->size = $file->getSize();
		$attachment->parameters = $parameters;
		$result = $attachment->save();

		if($result) {

            $this->fireAttachmentEvent('attached', $attachment);

        }

		return $result;

	}

	private function getAttachmentFilePath($key, $file_path) {

	    $path = '';

        if($file_path instanceof \Illuminate\Http\UploadedFile) {

            $keys = explode('.', $key);
            $first_key = $keys[0];
            $filename = date('Ymd_His_') . str_random() .'.'. $file_path->extension();
            $file_path->storeAs($first_key, $filename);
            $path = storage_path('app/'. $first_key .'/'. $filename);

        } else if(is_string($file_path)) {

            $path = $file_path;

        }

        return $path;

    }

    private function fireAttachmentEvent($event, $attachment) {

        if (!in_array($event, ['attached', 'detached']) || ! isset($this->dispatchesEvents[$event])) {
            return;
        }

        $result = static::$dispatcher->fire(new $this->dispatchesEvents[$event]($attachment));

        if (! is_null($result)) {
            return $result;
        }
    }

	private function getCurrentClassName() {

		return __CLASS__;

	}

	// Override

	public function save(array $options = []) {

		$result = parent::save($options);

		if($result) {

			$this->saveAttachments();

		}

		return $result;

	}

	public function delete() {

		\DB::beginTransaction();

		try {

			parent::delete();
			$model = $this->getCurrentClassName();
			$model_id = $this->id;
			\DB::table('attachments')->where('model', $model)
				->where('model_id', $model_id)
				->delete();
			\DB::commit();

			if($this->attachments->count() > 0) {

				$this->detachAll();

			}

			return true;

		} catch (\Exception $e) {

			\DB::rollBack();

		}

		return false;

	}

	// Relationship

	public function attachments() {

		return $this->hasMany('Sukohi\ClampBolt\App\Attachment', 'model_id', 'id');

	}

	// Accessors

	public function getAttachmentFilenamesAttribute() {

		$filenames = $this->attachments->lists('filename', 'key')->all();
		return $this->convertMultiDimensionalArray($filenames);

	}

	public function getAttachmentPathsAttribute() {

		$paths = [];

		foreach ($this->attachments as $index => $attachment) {

			$key = $attachment->key;
			$path = $attachment->full_path;
			$paths[$key] = $path;

		}

		return $this->convertMultiDimensionalArray($paths);

	}

	// Others

	private function convertMultiDimensionalArray(array $values) {

		$array = [];

		foreach ($values as $key => $value) {

			if(strpos($key, '.') !== false) {

				$array_keys = explode('.', $key);
				$json_key = '__KEY_VALUE__';
				$json_str = $json_key;

				foreach ($array_keys as $array_key) {

					$json_str = str_replace($json_key, '{"'. $array_key .'":'. $json_key .'}', $json_str);

				}

				$json_str = str_replace($json_key, '"'. $value .'"', $json_str);
				$sub_array = json_decode($json_str, true);
				$array = array_replace_recursive($array, $sub_array);

			} else {

				$array[$key] = $value;

			}

		}

		return $array;

	}

	public function hasAttachment($key) {

        return $this->attachments->contains('key', $key);

    }

    public function getAttachment($key) {

        $attachments_keys = $this->attachments->keyBy('key');

        if(!$attachments_keys->has($key)) {

            return new Attachment;

        }

        return $attachments_keys->get($key);

    }

}