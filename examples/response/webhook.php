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
