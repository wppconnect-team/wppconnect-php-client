<?php

namespace Wppconnect;

session_start();

# Use require or autoload
require('wppconnect.php');

$wppconnect = new Wppconnect([
    'base_url' => 'http://192.168.0.76:21465',
    'secret_key' => 'MYKeYPHP',
    'session' => 'mySession',
]);

# Function: Generated Token
# /api/:session/generate-token

if (!isset($_SESSION['token'])) :
    $response = $wppconnect->generateToken([
        'session' => $wppconnect->options['session'],
        'secret_key' => $wppconnect->options['secret_key']
    ]);
    $response = $wppconnect->toArray($response);

    if ($response['status'] == 'Success') :
        $_SESSION['token'] = $response['token'];
    endif;

    #debug
    $wppconnect->debug($response);
endif;

# Function: Start Session
# /api/:session/start-session

if (isset($_SESSION['token']) and !isset($_SESSION['init'])) :
    $response = $wppconnect->startSession([
        'session' => $wppconnect->options['session'],
    ]);
    $response = $wppconnect->toArray($response);

    if ($response['message'] == 'Inicializando Sessão') :
        $_SESSION['init'] = true;
    endif;

    #debug
    $wppconnect->debug($response);
endif;

# Function: Check Connection Session
# /api/:session/check-connection-session
/*
if (isset($_SESSION['token']) and isset($_SESSION['init'])) :
    $response = $wppconnect->checkConnectionSession([
        'session' => $wppconnect->options['session'],
    ]);
    $response = $wppconnect->toArray($response);

    #debug
    $wppconnect->debug($response);
endif;
*/

# Function: Send Message
# /api/:session/send-message
/*
if (isset($_SESSION['token']) and isset($_SESSION['init'])) :
    $response = $wppconnect->sendMessage([
        'session' => $wppconnect->options['session'],
        'phone' => '0000000000000',
        'message' => 'Opa, funciona mesmo!'
    ]);
    $response = $wppconnect->toArray($response);

    #debug
    $wppconnect->debug($response);
endif;
*/

# Function: Send File Base64
# /api/:session/send-file-base64
/*
if (isset($_SESSION['token']) and isset($_SESSION['init'])) :
    $response = $wppconnect->sendFileBase64([
        'session' => $wppconnect->options['session'],
        'phone' => '0000000000000',
        'base64' => 'data:image/jpg;base64,' . base64_encode(file_get_contents('xpto.jpg'))
    ]);
    $response = $wppconnect->toArray($response);

    #debug
    $wppconnect->debug($response);
endif;
*/

# Function: Send Link Preview
# /api/:session/send-link-preview
/*
if (isset($_SESSION['token']) and isset($_SESSION['init'])) :
    $response = $wppconnect->sendLinkPreview([
        'session' => $wppconnect->options['session'],
        'phone' => '0000000000000',
        'url' => 'https://github.com/wppconnect-team',
        'caption' => 'WppConnectTeam'
    ]);

    $response = $wppconnect->toArray($response);

    #debug
    $wppconnect->debug($response);
endif;
*/


# Function: Send Location
# /api/:session/send-location
/*
if (isset($_SESSION['token']) and isset($_SESSION['init'])) :
    $response = $wppconnect->sendLocation([
        'session' => $wppconnect->options['session'],
        'phone' => '0000000000000',
        'lat' => '-23.5489',
        'long' => '-46.6388',
        'title' => 'Cidade de São Paulo'
    ]);

    $response = $wppconnect->toArray($response);

    #debug
    $wppconnect->debug($response);
endif;
*/
