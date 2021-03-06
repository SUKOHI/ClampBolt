<?php

namespace Sukohi\ClampBolt\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PublicAttachment extends Model
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $table = 'attachments';
    protected $visible = [
        'extension',
        'filename',
        'id',
        'key',
        'mime_type',
        'model_id',
        'parameters',
        'public_url'
    ];
    protected $casts = ['parameters' => 'array'];
    protected $appends = ['public_url'];

    // Accessor

    public function getPathAttribute() {

        if(!empty($this->dir) && !empty($this->filename)) {

            return $this->dir .'/'. $this->filename;

        }

        return '';

    }

    public function getFullPathAttribute() {

        return $this->path;

    }

    public function getPublicUrlAttribute() {

        $storage_public_path = storage_path('app/public');

        if(Str::startsWith($this->path, $storage_public_path)) {

            $pattern = '|^'. $storage_public_path .'|';
            $public_path = 'storage'. preg_replace($pattern, '', $this->path);
            return url($public_path);

        }

        return '';

    }

    // Others

    public function response() {

        $path = $this->path;

        if(!file_exists($path)) {

            throw new \Exception('File not exists.');

        }

        return response()->file($path);

    }

    public function download($name = '') {

        $path = $this->path;

        if(!file_exists($path)) {

            throw new \Exception('File not exists.');

        }

        return response()->download($path, $name);

    }
}
