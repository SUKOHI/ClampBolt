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

        return $this->path;

    }

    public function getPathAttribute() {

        if(!empty($this->dir) && !empty($this->filename)) {

            return $this->dir .'/'. $this->filename;

        }

        return '';

    }

    public function getPublicUrlAttribute() {

        $storage_public_path = storage_path('app/public');

        if(starts_with($this->path, $storage_public_path)) {

            $pattern = '|^'. $storage_public_path .'|';
            $public_path = 'storage'. preg_replace($pattern, '', $this->path);
            return url($public_path);

        }

        return '';

    }

    // Others

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