<?php namespace Sukohi\ClampBolt;

use Sukohi\ClampBolt\App\Attachment;
use Symfony\Component\HttpFoundation\File\File;

trait ClampBoltTrait {

	private $clamp_bolt_attachments = [],
			$clamp_bolt_detachments = [],
			$clamp_bolt_deletions = [],
            $clamp_bolt_attachment_dir = '';

	public function attach($key, $path = '', $parameters = [], $deleting_flag = false) {

		$keys = (!is_array($key)) ? [$key => $path] : $key;

		foreach ($keys as $key => $file_path) {

		    if($this->isWildcardKey($key)) {

                $key = $this->getWildcardKey($key);

            }

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

	    if($this->isWildcardKey($key)) {

            $keys = [];
            $first_part_key = $this->getWildcardFirstPartKey($key);
            $model = $this->getCurrentClassName();
            $model_id = $this->id;
            $attachments = Attachment::where('model', $model)
                ->where('model_id', $model_id)
                ->where('key', 'LIKE', $first_part_key .'.%')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($attachments as $attachment) {

                if($this->matchWildcardKeys($key, $attachment->key)) {

                    $keys[] = $attachment->key;

                }

            }

        } else {

            $keys = (!is_array($key)) ? [$key] : $key;

        }

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

					if($attachment->path != $path) {

                        if(isset($this->clamp_bolt_deletions[$key]) && $this->clamp_bolt_deletions[$key]) {

                            @unlink($attachment->path);

                        }

						$file = new File($path);
						$attachment->dir = $file->getPath();
						$attachment->filename = $file->getFilename();

					}

					$this->saveAttachment($attachment, $key, $path, $parameters);
					unset($this->clamp_bolt_attachments[$key]);

				}

				if(isset($this->clamp_bolt_detachments[$key])) {

					$path = $attachment->path;

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

            $path = $this->storeAttachment($key, $file_path);

        } else if(is_string($file_path)) {

            $path = $file_path;

        }

        return $path;

    }

    private function storeAttachment($key, $file) {

        $keys = explode('.', $key);
        $filename = date('Ymd_His_') . str_random() .'.'. $file->extension();
        $first_key = $keys[0];
	    $storing_dir = $first_key;

        if(!empty($this->clamp_bolt_attachment_dir)) {

            $storing_dir = $this->clamp_bolt_attachment_dir .'/'. $first_key;

        }

        $file->storeAs($storing_dir, $filename);
        return storage_path('app/'. $storing_dir .'/'. $filename);

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
            Attachment::where('model', $model)
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

		return $this->hasMany('Sukohi\ClampBolt\App\Attachment', 'model_id', 'id')->where('model', $this->getCurrentClassName());

	}

	// Accessors

	public function getAttachmentFilenamesAttribute() {

	    $filenames = [];

        foreach ($this->attachments as $attachment) {

            $key = $attachment->key;
            $filename = $attachment->filename;
            $filenames[$key] = $filename;

	    }

		return $this->convertMultiDimensionalArray($filenames);

	}

	public function getAttachmentPathsAttribute() {

		$paths = [];

		foreach ($this->attachments as $attachment) {

			$key = $attachment->key;
			$path = $attachment->path;
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

        if($this->isWildcardKey($key)) {

            return $this->getWildcardAttachments($key);

        }

        return $this->attachments->keyBy('key')->get($key, new Attachment);

    }

    private function getWildcardAttachments($key) {

        $first_part_key = $this->getWildcardFirstPartKey($key);
        $model = $this->getCurrentClassName();
        $model_id = $this->id;
        $attachments = Attachment::where('model', $model)
            ->where('model_id', $model_id)
            ->where('key', 'LIKE', $first_part_key .'.%')
            ->orderBy('id', 'asc')
            ->get();
        $filtered_attachments = $attachments->filter(function($attachment) use($key) {

            return ($this->matchWildcardKeys($key, $attachment->key));

        });

        $index = 0;

        foreach ($filtered_attachments as $attachment) {

            $new_key = $first_part_key .'.'. $index;
            $attachment->key = $new_key;
            $attachment->save();
            $index++;

        }

        return $filtered_attachments;

    }

    private function isWildcardKey($key) {

	    return ends_with($key, '.*');

    }

    private function matchWildcardKeys($key_1, $key_2) {

        $first_key_1 = $this->getWildcardFirstPartKey($key_1);
        $first_key_2 = $this->getWildcardFirstPartKey($key_2);
        return ($first_key_1 == $first_key_2);

    }

    private function getWildcardFirstPartKey($key) {

	    $keys = explode('.', $key);
        array_pop($keys);
        return implode('.', $keys);

    }

    private function getWildcardKey($key) {

        $attachments = $this->getWildcardAttachments($key);
        $attachments_count = $attachments->count();

        foreach (array_keys($this->clamp_bolt_attachments) as $attachment_key) {

            if($this->matchWildcardKeys($key, $attachment_key)) {

                $attachments_count++;

            }

        }

        return $this->getWildcardFirstPartKey($key) .'.'. $attachments_count;

    }

    public function setAttachmentDir($dir) {

	    if(ends_with($dir, '/')) {

            $dir = substr($dir, 0, -1);

        }

        $this->clamp_bolt_attachment_dir = $dir;

    }

}