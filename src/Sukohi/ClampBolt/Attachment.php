<?php

namespace Sukohi\ClampBolt\App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
	// Override

	public function delete($remove_file = false) {

		$result = parent::delete();

		if($result && $remove_file) {

			@unlink($this->full_path);

		}

		return $result;

	}

	// Accessor

	public function getParametersAttribute($value) {

		return json_decode($value, true);

	}

	public function getFullPathAttribute() {

		return $this->path .'/'. $this->filename;

	}

	// Mutator

	public function setParametersAttribute($values) {

		$this->attributes['parameters'] = json_encode($values);

	}
}
