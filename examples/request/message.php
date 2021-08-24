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

namespace WPPConnect;

use WPPConnect\Http\Request;
use WPPConnect\Helpers\Util;

require '../../vendor/autoload.php';

$wppconnect = new Request([
    'base_url' => 'http://localhost:8081',
    'secret_key' => 'My53cr3tKY',
    'session' => 'mySessionPHP',
    'token' => null
]);
$util = new Util();

# Function: Generated Token
# /api/:session/generate-token
$response = $wppconnect->generateToken();
$response = $util->toArray($response);
if (isset($response['status']) and $response['status'] == 'success') :
    $wppconnect->options['token'] = $response['token'];
endif;

#debug
$util->debug($response);

# Function: Start Session
# /api/:session/start-session
$response = $wppconnect->startSession([
    'webhook' => null,
    'waitQrCode' => true
]);
$response = $util->toArray($response);

#debug
$util->debug($response);

# Function: Send Message
# /api/:session/send-message
$response = $wppconnect->sendMessage([
    'phone' => '5500000000000',
    'message' => 'Opa, funciona mesmo!',
    'isGroup' => false
]);
$response = $util->toArray($response);

#debug
$util->debug($response);

# Function: Send File Base64
# /api/:session/send-file-base64
$response = $wppconnect->sendFileBase64([
    'phone' => '5500000000000',
    'filename' => 'WPPConnect',
    'base64' => $util->fileToBase64('wppconnect.jpeg'),
    'isGroup' => false
]);
$response = $util->toArray($response);

#debug
$util->debug($response);

# Function: Send Status
# /api/:session/send-status
$response = $wppconnect->sendStatus([
    'message' => 'Mando o Status',
    'isGroup' => false
]);
$response = $util->toArray($response);

#debug
$util->debug($response);

# Function: Send Link Preview
# /api/:session/send-link-preview
$response = $wppconnect->sendLinkPreview([
    'phone' => '5500000000000',
    'url' => 'https://github.com/wppconnect-team',
    'caption' => 'WppConnectTeam',
    'isGroup' => false
]);
$response = $util->toArray($response);

#debug
$util->debug($response);

# Function: Send Location
# /api/:session/send-location
$response = $wppconnect->sendLocation([
    'phone' => '5500000000000',
    'lat' => '-22.282027',
    'lng' => '-48.1280803',
    'title' => 'Cidade de Brotas',
    'isGroup' => false
]);
$response = $util->toArray($response);

#debug
$util->debug($response);
