<?php

namespace eru123\fs;

use Exception;

class File
{

    protected $path;
    protected $name;
    protected $ext;
    protected $mime;
    protected $size;

    public function __construct($path)
    {
        $this->path = realpath($path);

        if (!$this->path || !file_exists($this->path)) {
            return;
        }

        $this->name = basename($this->path);

        if (function_exists('pathinfo')) {
            $this->ext = pathinfo($this->path, PATHINFO_EXTENSION);
        } else {
            $this->ext = substr($this->name, strrpos($this->name, '.') + 1);
        }

        $this->mime = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $this->mime = mime_content_type($this->path);
        } else if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $this->mime = finfo_file($finfo, $this->path);
            finfo_close($finfo);
        } else if (function_exists('exif_imagetype')) {
            $this->mime = image_type_to_mime_type(exif_imagetype($this->path));
        } else {
            $mimes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'html' => 'text/html',
                'htm' => 'text/html',
                'txt' => 'text/plain',
                'csv' => 'text/csv',
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon',
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed',
                '7z' => 'application/x-7z-compressed',
                'tar' => 'application/x-tar',
                'gz' => 'application/gzip',
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'ogg' => 'audio/ogg',
                'mp4' => 'video/mp4',
                'webm' => 'video/webm',
                'mkv' => 'video/x-matroska',
                'avi' => 'video/x-msvideo',
                'flv' => 'video/x-flv',
                'wmv' => 'video/x-ms-wmv',
                'mov' => 'video/quicktime',
                'swf' => 'application/x-shockwave-flash',
                'php' => 'text/x-php',
                'asp' => 'text/asp',
                'aspx' => 'text/aspx',
                'py' => 'text/x-python',
                'rb' => 'text/x-ruby',
                'pl' => 'text/x-perl',
                'sh' => 'text/x-shellscript',
                'sql' => 'text/x-sql',
                'c' => 'text/x-csrc',
                'cpp' => 'text/x-c++src',
                'java' => 'text/x-java',
                'cs' => 'text/x-csharp',
                'vb' => 'text/x-vb',
                'ini' => 'text/x-ini',
            ];

            if (isset($mimes[strtolower($this->ext)])) {
                $this->mime = $mimes[strtolower($this->ext)];
            }
        }

        $this->size = filesize($this->path);
    }

    public function stream(int|string $bytes = '128kb')
    {
        if (!$this->path || !file_exists($this->path)) {
            return;
        }

        if (strtolower($this->ext) == 'php' || $this->mime == 'text/x-php') {
            $f = $this->path;
            (function () use ($f) {
                include $f;
            })();
            exit;
        }

        $bytes = Helper::to_bytes((string) $bytes);
        if (empty($bytes)) {
            $bytes = 128 * 1024;
        }
        if (!$this->path || !file_exists($this->path)) {
            return;
        }

        $fp = fopen($this->path, 'rb');
        if (!$fp) {
            return;
        }

        $size = $this->size;
        $offset = 0;

        header('Content-Type: ' . $this->mime);
        header('Content-Length: ' . $size);
        header('Accept-Ranges: bytes');
        while ($offset < $size && !feof($fp) && !connection_aborted()) {
            $buffer = fread($fp, $bytes);
            echo $buffer;
            flush();
            $offset += $bytes;
        }

        fclose($fp);
        exit;
    }
}
