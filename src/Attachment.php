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

    public function stream($buffer = 1024) {

        $file = $this->getFullPathAttribute();
        $fp = @fopen($file, 'rb');
        $size   = filesize($file);
        $length = $size;
        $start  = 0;
        $end = $size - 1;
        header('Content-type: '. $this->mime_type);
        header('Accept-Ranges: bytes');

        if(isset($_SERVER['HTTP_RANGE'])) {

            $c_start = $start;
            $c_end   = $end;
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

            if(strpos($range, ',') !== false) {

                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;

            }

            if($range == '-') {

                $c_start = $size - substr($range, 1);

            } else{

                $range  = explode('-', $range);
                $c_start = $range[0];
                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;

            }

            $c_end = ($c_end > $end) ? $end : $c_end;

            if($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {

                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;

            }

            $start  = $c_start;
            $end    = $c_end;
            $length = $end - $start + 1;
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');

        }

        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: ".$length);

        while(!feof($fp) && ($p = ftell($fp)) <= $end) {

            if($p + $buffer > $end) {

                $buffer = $end - $p + 1;

            }

            set_time_limit(0);
            echo fread($fp, $buffer);
            flush();

        }

        fclose($fp);
        exit();

    }
}