<?php

namespace Sukohi\ClampBolt\App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
	/**
	 * The attributes that should be casted to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'parameters' => 'array',
	];

	// Accessor

	public function getFullPathAttribute() {

		return $this->dir .'/'. $this->filename;

	}
}
