<?php

namespace Sukohi\ClampBolt\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attachment extends Model
{
    protected $casts = ['parameters' => 'array'];
    protected $appends = ['public_url'];

    // Relationship

    public function parent() {

        return $this->belongsTo($this->model, 'model_id', 'id');

    }

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

    public function thumbnail($width, $height) {

        if(!Str::startsWith($this->mime_type, 'image/')) {

            throw new \Exception('This is not an image file.');

        }

        $image = \Image::make($this->path);
        $thumbnail_key = $this->key .'_thumbnail_'. $width .'x'. $height;
        $thumbnail_path = str_replace(
            '.',
            '_thumbnail_'. $width .'x'. $height .'.',
            $this->path
        );
        $image->fit($width, $height)
            ->save($thumbnail_path);

        $model = $this->parent;
        $model->attach(
            $thumbnail_key,
            $thumbnail_path,
            ['parent_attachment_key' => $this->key],
            true
        );
        $model->save();

    }
}
