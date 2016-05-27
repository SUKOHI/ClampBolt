<?php namespace Sukohi\ClampBolt;

use Sukohi\ClampBolt\App\Attachment;
use Symfony\Component\HttpFoundation\File\File;

trait ClampBoltTrait {

	private $clamp_bolt_attachments,
			$clamp_bolt_detachments,
			$clamp_bolt_db_data,
			$clamp_bolt_db_map = [];
	private $clamp_bolt_init_flag = false;

	public function attach($path, $parameters = []) {

		$this->loadDb();
		$file = new File($path);

		if($file->isFile()) {

			$key = $this->clampBoltAttachmentKey($path);
			$this->clamp_bolt_attachments[$key] = [
				'file' => $file,
				'parameters' => $parameters
			];

			if(isset($this->clamp_bolt_detachments[$key])) {

				unset($this->clamp_bolt_detachments[$key]);

			}

			return $this;

		}

		throw new \Exception('File does not exist.');

	}

	public function detach($id_or_path, $remove_file = false) {

		$this->loadDb();
		$key = '';

		if(is_int($id_or_path) && isset($this->clamp_bolt_db_map[$id_or_path])) {

			$key = $this->clamp_bolt_db_map[$id_or_path];

		} else if(is_string($id_or_path)) {

			$key = $this->clampBoltAttachmentKey($id_or_path);

		}

		if(array_search($key, $this->clamp_bolt_db_map) !== false) {

			$this->clamp_bolt_detachments[$key] = [
				'remove_file' => (boolean)$remove_file
			];
			unset($this->clamp_bolt_attachments[$key]);

		}

	}

	public function saveAttachments() {

		if($this->id <= 0) {

			throw new \Exception('Model data do not exist.');

		} else if(empty($this->clamp_bolt_attachments) && empty($this->clamp_bolt_detachments)) {

			return true;

		}

		$model = $this->clampBoltClassName();
		$attachments_with_key = [];

		if($this->clamp_bolt_db_data->count() > 0) {

			foreach ($this->clamp_bolt_db_data as $attachment) {

				$attachment_key = $this->clampBoltAttachmentKey($attachment->full_path);
				$attachments_with_key[$attachment_key] = $attachment;

			}

		}

		if(!empty($this->clamp_bolt_attachments)) {

			foreach ($this->clamp_bolt_attachments as $key => $clamp_bolt_attachment) {

				$file = $clamp_bolt_attachment['file'];
				$parameters = $clamp_bolt_attachment['parameters'];

				$attachment = array_get($attachments_with_key, $key, $this->clampBoltAttachmentModel());
				$attachment->model = $model;
				$attachment->model_id = $this->id;
				$attachment->path = $file->getPath();
				$attachment->filename = $file->getFilename();
				$attachment->extension = $file->getExtension();
				$attachment->mime_type = $file->getMimeType();
				$attachment->size = $file->getSize();
				$attachment->parameters = $parameters;
				$attachment->save();

			}

		}
		
		if(!empty($this->clamp_bolt_detachments)) {

			foreach ($this->clamp_bolt_detachments as $key => $clamp_bolt_detachment) {

				$detachment = $attachments_with_key[$key];
				$remove_file = $clamp_bolt_detachment['remove_file'];
				$detachment->delete($remove_file);

			}

		}

		$this->clamp_bolt_init_flag = false;
		return true;

	}

	private function loadDb() {

		if(!$this->clamp_bolt_init_flag) {

			$this->init();

		}

	}

	private function init() {

		$this->clamp_bolt_db_map = [];
		$model = $this->clampBoltClassName();
		$attachments = $this->clampBoltAttachmentModel()
			->where('model', $model)
			->where('model_id', $this->id)
			->get();

		if($attachments->count() > 0) {

			foreach ($attachments as $attachment) {

				$id = $attachment->id;
				$key = $this->clampBoltAttachmentKey($attachment->full_path);
				$this->clamp_bolt_db_map[$id] = $key;

			}

		}

		$this->clamp_bolt_db_data = $attachments;
		$this->clamp_bolt_init_flag = true;

	}

	private function clampBoltClassName() {

		return __CLASS__;

	}

	private function clampBoltAttachmentModel() {

		return new Attachment;

	}

	private function clampBoltAttachmentKey($path) {

		$model = $this->clampBoltClassName();
		return md5($model .'_'. $path);

	}

	// Override

	public function save(array $options = []) {

		$result = parent::save($options);

		if($result) {

			$this->saveAttachments();

		}

		return $result;

	}

	// Relationship

	public function attachments() {

		return $this->hasMany('Sukohi\ClampBolt\App\Attachment', 'model_id', 'id');

	}

}