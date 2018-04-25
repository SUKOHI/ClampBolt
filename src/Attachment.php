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

        if(!empty($this->dir) && !empty($this->filename)) {

            return $this->dir .'/'. $this->filename;

        }

        return '';

    }

    public function response() {

        $full_path = $this->full_path;

        if(!file_exists($full_path)) {

            throw new \Exception('File not exists.');

        }

        return response()->file($full_path);

    }

    public function download($name = '') {

        $full_path = $this->full_path;

        if(!file_exists($full_path)) {

            throw new \Exception('File not exists.');

        }

        return response()->download($full_path, $name);

    }
}