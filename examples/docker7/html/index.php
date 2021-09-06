<?php

namespace WPPConnect;

require '../vendor/autoload.php';

use WPPConnect\Http\Request;
use WPPConnect\Helpers\Util;

$wppconnect = new Request([
    'base_url' => 'http://wppconnect-nginx:8081',
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
    'webhook' => 'http://wppconnect-nginx:8080/webhook/index.php',
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
