<?php

/*
 * Copyright 2021 WPPConnect Team
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);

namespace WPPConnect\Helpers;

use ZipArchive;

class Util
{

    /**
     * Debug
     * Like laravel dd
     *
     * @param mixed $array
     * @return string
     */
    public function debug($array): void
    {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
        die;
    }

    /*
     * Logger
     * @return void
     */
    public function logger(string $path, object $data, bool $serialize = false): void
    {

        $this->createDirectory($path);
        $this->zipFileLog($path);

        $filename = (!empty($path)) ? $path . '/' . date("Ymd") . '.log' : date('Ymd') . '.log' ;
        $fh = fopen($filename, 'a') or die("can't open file");
        if ($serialize === true) :
            fwrite($fh, serialize($data));
        else :
            fwrite($fh, "\n\n---------------------------------------------------------------\n");
            fwrite($fh, print_r($data, true));
        endif;
        fclose($fh);
    }

    /**
     * File to Base64
     *
     * @param string $filePath
     * @return string
     */
    public function fileToBase64(string $filePath): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return 'data:' . $finfo->file($filePath) . ';base64,' . base64_encode(file_get_contents($filePath));
    }

    /**
     * Base64 to File
     *
     * @param string $base64
     * @return string|null
     */
    public function base64ToFile(
        string $base64,
        string $mimetype,
        string $path = null,
        string $filename = null
    ): ?string {
        $this->createDirectory($path);
        $ext = $this->mimes('extension', $mimetype);
        if ($ext) :
            $filename = ($filename !== null) ? $filename :  $this->randomName() . "." . $ext;
            if ($file = fopen((!empty($path) ? $path . '/' . $filename : $filename), "wb")) :
                fwrite($file, base64_decode($base64));
                fclose($file);
                return $filename;
            else :
                return null;
            endif;
        else :
            return null;
        endif;
    }

    /**
     * Random Name
     *
     * @return string
     */
    public function randomName(): string
    {
        $var = "";
        $var .= substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVXWYZ"), 0, 2);
        $var .= substr(str_shuffle("abcdefghijklmnopqrstuvxwyz"), 0, 3);
        $var .= substr(str_shuffle("0123456789"), 0, 2);
        $var .= substr(str_shuffle("#@!*"), 0, 1);
        return md5(str_shuffle($var));
    }

    /**
     * Create Directory
     *
     * @param string $path
     * @return boolean
     */
    public function createDirectory(string $path, int $permission = 0777): bool
    {
        if (!empty($path)) :
            if (strpos($path, ".") !== false) :
                $path = implode('/', explode('/', $path, -1));
            endif;
            if (!is_dir($path)) :
                return (!mkdir($path, $permission, true)) ? false : true;
            endif;
                return false;
        endif;
        return false;
    }

    /**
     * Zip File Log
     *
     * @param string $path
     * @return boolean
     */
    public function zipFileLog(string $path): bool
    {

        $filename = (date('Ymd') - 1);
        if (!file_exists($path . '/' . $filename . '.zip') and file_exists($path . '/' . $filename . '.log')) :
            $zip = new ZipArchive();
            $zip->open($path . '/' . $filename . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $zip->addFile($path . '/' . $filename . '.log');
            $zip->close();
            return (file_exists($filename . '.zip')) ? true : false;
        endif;
        return false;
    }

    /**
     * Force Download
     *
     * @param string $filename
     * @return void
     */
    public function forceDownload(string $filename): void
    {
        header('X-Content-Type-Options: nosniff');
        if (!preg_match("/\.(gif|jpe?g|png)$/i", $filename)) :
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        else :
            $type = $this->mimes('mime', (strtolower(pathinfo($filename, PATHINFO_EXTENSION))));
            header('Content-Type: ' . $type);
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Content-Transfer-Encoding: binary');
        endif;
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_clean();
        flush();
        readfile($filename);
    }

  /**
   * toArray
   *
   * @param string $content
   * @return array
   */
    public function toArray(string $content): array
    {
        $content =  json_decode($content, true);
        return (is_array($content)) ?  $content : ['status' => 'Error', 'message' => $content];
    }

    /**
     * Mimes
     *
     * @param string $type
     * @param string $value
     * @return string|null
     */
    public function mimes(string $type, string $value): ?string
    {
        $mimes = [
        'text/x-comma-separated-values' => 'csv',
        'application/macbinary' => 'bin',
        'application/x-photoshop' => 'psd',
        'application/pdf' => 'pdf',
        'application/postscript' => 'eps',
        'application/smil' => 'smil',
        'application/vnd.mif' => 'mif',
        'application/excel' => 'xls',
        'application/vnd.ms-excel' => 'xls',
        'application/msexcel' => 'xls',
        'application/powerpoint' => 'ppt',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/wbxml' => 'wbxml' ,
        'application/wmlc' => 'wmlc',
        'application/x-director' => 'dcr',
        'application/x-director' => 'dir',
        'application/x-director' => 'dxr',
        'application/x-dvi' => 'dvi',
        'application/x-gtar' => 'gtar',
        'application/x-gzip' => 'gz',
        'application/x-httpd-php' => 'php',
        'application/x-javascript' => 'js',
        'application/x-shockwave-flash' => 'swf',
        'application/x-stuffit' => 'sit',
        'application/x-tar' => 'tar',
        'application/x-gzip-compressed' => 'tgz',
        'application/xhtml+xml' => 'xhtml',
        'application/x-zip' => 'zip',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
        'application/json' => 'json',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/octet-stream' => 'doc',
        'application/excel' => 'xl',
        'audio/midi' => 'mid',
        'audio/mpeg' => 'mp3',
        'audio/mpg' => 'mp3',
        'audio/mpeg3' => 'mp3',
        'audio/mp3' => 'mp3',
        'audio/x-wav' => 'wav',
        'audio/ogg; codecs=opus' => 'ogg',
        'audio/ogg' => 'ogg',
        'image/bmp' => 'bmp',
        'image/gif' =>  'gif',
        'image/jpeg' => 'jpeg',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/tiff' => 'tiff',
        'image/webp' => 'webp',
        'message/rfc822' => 'eml',
        'text/css' => 'css',
        'text/html' => 'html',
        'text/plain' => 'txt',
        'text/x-log' => 'log',
        'text/richtext' => 'rtx',
        'text/rtf' => 'rtf',
        'text/xml' => 'xml',
        'text/json' => 'json',
        'text/x-php' => 'php',
        'video/mpeg' => 'mpeg',
        'video/quicktime' => 'mov',
        'video/x-msvideo' => 'avi',
        'video/mp4' => 'mp4',
        'video/x-ms-asf' => 'wmv',
        'video/x-sgi-movie' => 'movie',
        ];

        if ($type == 'mime') :
            return (array_search($value, $mimes)) ? array_search($value, $mimes) : null;
        elseif ($type == 'extension') :
            return (array_key_exists($value, $mimes)) ? $mimes[$value] : null;
        endif;
        return null;
    }

    /**
     * Build Link Location
     *
     * @param float $lat
     * @param float $lng
     * @param string $caption
     * @return string
     */
    public function buildLinkLocation(float $lat, float $lng, string $caption = 'See Location'): string
    {
        return '<a href="https://www.google.com/maps/search/?api=1&query=' . $lat . ',' . $lng . '" 
            target="_blank">' . $caption . '</a>';
    }

    /**
     * Build Qrcode Image
     *
     * @param string $imageBase64
     * @param string $caption
     * @return string
     */
    public function buildQrcodeImage(string $imageBase64, string $caption = 'Qrcode'): string
    {
        return '<img src="data:image/png;base64,' . $imageBase64 . '" 
            alt="' . $caption . '" title="' . $caption . '" />';
    }

    /**
     * Brazil Mobile Number Format
     *
     * @param string $phone
     * @param boolean $allowPlus
     * @return string
     */
    public function brazilMobileNumberFormat(string $phone, bool $allowPlus = false): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $phone = (substr($phone, 0, 2) == 55 and strlen($phone) == 12)
            ? substr($phone, 0, 4) . '9' . substr($phone, 4, 12) : $phone;
        return ($allowPlus === true) ? '+' . $phone : $phone;
    }

    /**
     * vCard Data
     *
     * @param string $vCard
     * @param array $filter
     * @return array|null
     */
    public function vCardData(string $vCard, array $filter = ['FN', 'TEL']): ?array
    {
        $cardData = [];
        $vCard = str_replace(["\r\n", "\r"], "\n", $vCard);
        $vCard = preg_replace("/\n(?:[ \t])/", "", $vCard);
        $lines = explode("\n", $vCard);
        foreach ($lines as $line) :
            $line = trim($line);
            $line = preg_replace('/^\w+\./', '', $line);
            @list($type, $value) = explode(':', $line, 2);
            $types = explode(';', $type);
            $element = strtoupper($types[0]);
            if (in_array($element, $filter)) :
                $cardData[$element] = ($element == 'TEL') ? $this->brazilMobileNumberFormat($value) : $value;
            endif;
        endforeach;
        return (count($cardData) > 0) ? $cardData : null;
    }
}
