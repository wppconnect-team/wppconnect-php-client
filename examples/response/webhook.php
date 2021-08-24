<?php

namespace WPPConnect;

use WPPConnect\Http\Response;
use WPPConnect\Helpers\Util;

require '../../vendor/autoload.php';

$config = require_once('../config/config.php');
$webhook = new Response($config);
$util = new Util();

# QRCode
if ($webhook->getEvent() == 'qrcode') :
    'File: ' .
        $util->base64ToFile($webhook->getQrcode(), 'image/png', $webhook->getFilesFolder());
    die;
endif;

# Message
if ($webhook->getEvent() == 'onmessage' and $webhook->getType() == 'chat') :
    echo '
        Content: ' . $webhook->getContent() . ' 
        Date: ' . $webhook->getDate() . ' 
        From: ' . $webhook->getFrom() . '
        To: ' . $webhook->getTo();
    die;
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
    echo '
        File: ' .
        $util->base64ToFile(
            $webhook->getBody(),
            $webhook->getMimetype(),
            $webhook->getFilesFolder()
        )        . ' 
        Date: ' . $webhook->getDate() . ' 
        From: ' . $webhook->getFrom() . '
        To: ' . $webhook->getTo();
    die;
endif;
