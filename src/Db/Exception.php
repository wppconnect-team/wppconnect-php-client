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

namespace WPPConnect\Db;

class Exception extends \Exception
{

    /**
     * UnknownDriver
     *
     * @return string
     */
    public static function unknownDriver(): string
    {
        return self::debug(new Exception("Drive unknown"));
    }

    /**
     * UnknownConfig
     *
     * @return string
     */
    public static function unknownConfig(): string
    {
        return self::debug(new Exception("Config unknown"));
    }

    /**
     * objectInQuery
     *
     * @return string
     */
    public static function objectInQuery(): string
    {
        return self::debug(new Exception("object in query"));
    }

    /**
     * genericError
     *
     * @return string
     */
    public static function genericError(string $e): string
    {
        return self::debug(new Exception($e));
    }

    /**
     * Debug
     *
     * @param object $e
     * @return string
     */
    public static function debug(object $e): string
    {
        /**
         * Gather error info:
         */
        $error = array();
        $error['Message'] = $e->getMessage();

        /**
         * Follow backtrace to the top where the error was first raised
         */
        $backtrace = debug_backtrace();
        foreach ($backtrace as $info) :
            if ($info['file'] != __FILE__) :
                $error['Backtrace'] = $info['file'] . ' @ line ' . $info['line'];
            endif;
        endforeach;
        $error['File'] = $e->getFile() . ' @ line ' . $e->getLine();

        /**
         * Show args if set
         */
        if (!empty($backtrace[1]['args'])) :
            $error['Args'] = '<pre>' . print_r($backtrace[1]['args'], true) . '</pre>';
        endif;

        /**
         * Don't show variables if GLOBALS are set
         */
        if (!empty($context) and empty($context['GLOBALS'])) :
            $error['Current Variables'] = '<pre>' . print_r($context, true) . '</pre>';
        endif;

        /**
         * Server Address
         */
        $error['Environment'] = $_SERVER['SERVER_ADDR'];

        /**
         * Build the Message
         */
        $message = null ;
        $message .= '<style type="text/css">
        .debug, .debug pre  { font: 12px Arial, Helvetica, sans-serif; text-align: left; }
        .debug { background: #FDF0EB; border: 1px solid #990000; color: #990000; 
                    margin: 0.75em; min-width: 400px; padding: 0.75em; }
        .debug h3 { border-bottom: 1px solid #990000; margin: 0; padding-bottom: 0.25em; }
        .debug label { display: block; font-weight: bold; padding-top: 1em; }
        .debug pre { margin: 0; }
        </style>';
        $message .= "\n" . '<div class="debug">' . "\n\t" . '<h3>' . __METHOD__ . '</h3>';
        foreach ($error as $key => $value) :
            $message .= "\n\t" . '<label>' . $key . ':</label>' . $value;
        endforeach;
        $message .= "\n" . '</div>';

        return $message;
    }
}
