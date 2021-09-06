<?php

namespace WPPConnect;

require '../../vendor/autoload.php';

use WPPConnect\Http\Response;
use WPPConnect\Helpers\Util;
use WPPConnect\Db\Adapter;

$config = require_once('../config/config.php');

$util = new Util() ;
$webhook = new Response($config);
$adapter = new Adapter($config);

$adapter->connect();

# QRCode
if ($webhook->getEvent() == 'qrcode') :
    $file = $util->base64ToFile($webhook->getQrcode(), 'image/png', $webhook->getFilesFolder());
    $adapter->insert(
        'chat',
        [
            'session' => $webhook->getSession(),
            'type' => 'QrCode',
            'file_name' => $file,
            'create_at' => date("Y-m-d H:i:s")
        ]
    );
endif;

# Message
if ($webhook->getEvent() == 'onmessage' and $webhook->getType() == 'chat') :
    $adapter->insert(
        'chat',
        [
            'session' => $webhook->getSession(),
            'content' => $webhook->getContent(),
            'from_number' => $webhook->getFrom(),
            'to_number' => $webhook->getTo(),
            'type' => $webhook->getType(),
            'create_at' => $webhook->getDate("Y-m-d H:i:s")
        ]
    );
endif;

# File: Audio / Imagem / Arquivo / Video / Sticker
if (
    $webhook->getEvent() == 'onmessage' and
    (
        $webhook->getType() == 'ptt' or
        $webhook->getType() == 'image' or
        $webhook->getType() == 'document' or
        $webhook->getType() == 'video' or
        $webhook->getType() == 'sticker'
    )
) :
    $file = $util->base64ToFile(
        $webhook->getBody(),
        $webhook->getMimetype(),
        $webhook->getFilesFolder()
    );
    $adapter->insert(
        'chat',
        [
        'session' => $webhook->getSession(),
        'from_number' => $webhook->getFrom(),
        'to_number' => $webhook->getTo(),
        'type' => $webhook->getType(),
        'file_name' => $file,
        'create_at' => $webhook->getDate("Y-m-d H:i:s")
        ]
    );
endif;
