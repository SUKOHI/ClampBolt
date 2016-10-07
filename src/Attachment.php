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

    public function stream() {

        $path = $this->getFullPathAttribute();
        $full_size = $size = filesize($path);
        $file = fopen($path, 'r');
        $http_code = 200;
        $headers = [
            'Accept-Ranges' => 'bytes',
            'Content-type' => $this->mime_type
        ];

        $range = \Request::header('Range');

        if(preg_match('!(bytes)=([0-9]+)-([0-9]+)!', $range, $matches)) {

            $range_unit = $matches[1];
            $start_range = $matches[2];
            $end_range = $matches[3];
            $start = intval(substr($range, $start_range+1, $end_range));
            $success = fseek($file, $start);

            if($success == 0) {

                $size = $full_size - $start;
                $http_code = 206;
                $headers['Accept-Ranges'] = $range_unit;
                $headers['Content-Range'] = $range_unit . ' ' . $start . '-' . ($full_size-1) . '/' . $full_size;

            }

        }

        $headers['Content-Length'] = $size;

        return response()->stream(function () use ($file) {

            if ($file === false) {
                return false;
            }

            while(!feof($file)) {

                $buffer = fgets($file, 1024*1024);
                echo $buffer;
                ob_flush();
                flush();

            }

            exit;

        }, $http_code, $headers);

    }
}