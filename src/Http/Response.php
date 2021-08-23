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

namespace WPPConnect\Http;

use WPPConnect\Helpers\Util;

class Response
{
    /**
     * @var object
     */
    private $data;

    public function __construct(array $options = [], object $data = null)
    {
        $this->data = json_decode(file_get_contents("php://input")) ?: (object)[];
        $this->options = $options;
        if ($options['logs']['enable'] === true) :
            $util = new Util();
            $util->logger($this->options['logs']['folder'], $this->getData());
        endif;
    }

    /*
     * Data File
     *
     * @param string $file
     * @return void
     */
    public function getDataFile(string $file): object
    {
        return $this->data = unserialize(file_get_contents(trim($file)));
    }

    /**
     * Files Folder
     *
     * @return string
     */
    public function getFilesFolder(): string
    {
        return $this->options['library'][$this->options['library']['default']]['files_folder'];
    }

    /**
     * Data
     *
     * @return object
     */
    public function getData(): ?object
    {
        return $this->data;
    }

    /**
     * Event
     *
     * @return string
     */
    public function getEvent(): string
    {
        return $this->data->event;
    }

    /**
     * Session
     *
     * @return string
     */
    public function getSession(): string
    {
        return $this->data->session;
    }

    /**
     * Qrcode
     *
     * @return string
     */
    public function getQrcode(): string
    {
        return $this->data->qrcode;
    }

    /**
     * Status
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->data->status;
    }

    /**
     * Id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->data->id;
    }

    /**
     * Body
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->data->body;
    }

    /**
     * Type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->data->type;
    }

    /**
     * Date
     *
     * @param string $format
     * @return string
     */
    public function getDate(string $format = "Y-m-d"): string
    {
        return date($format, $this->data->t);
    }

    /**
     * Notify Name
     *
     * @return string
     */
    public function getNotifyName(): string
    {
        return $this->data->notifyName;
    }

    /**
     * From
     *
     * @return string
     */
    public function getFrom(): string
    {
        return filter_var($this->data->from, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * To
     *
     * @return string
     */
    public function getTo(): string
    {
        return filter_var($this->data->to, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Author
     *
     * @return string
     */
    public function getAuthor(): string
    {
        return filter_var($this->data->author, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Self
     *
     * @return string
     */
    public function getSelf(): string
    {
        return $this->data->self;
    }

    /**
     * Ack
     *
     * @return integer
     */
    public function getAck(): int
    {
        return $this->data->ack;
    }

    /**
     * Invis
     *
     * @return boolean
     */
    public function getInvis(): bool
    {
        return $this->data->invis;
    }

    /**
     * Is New Message
     *
     * @return boolean
     */
    public function getIsNewMsg(): bool
    {
        return $this->data->isNewMsg;
    }

    /**
     * Is Star
     *
     * @return boolean
     */
    public function getIsStar(): bool
    {
        return $this->data->star;
    }

    /**
     * Recv Fresh
     *
     * @return boolean
     */
    public function getRecvFresh(): bool
    {
        return $this->data->recvFresh;
    }

    /**
     * Client Url
     *
     * @return boolean
     */
    public function getClientUrl(): bool
    {
        return $this->data->clientUrl;
    }

    /**
     * Deprecated Mms3 Url
     *
     * @return string
     */
    public function getDeprecatedMms3Url(): string
    {
        return $this->data->deprecatedMms3Url;
    }

    /**
     * Direct Path
     *
     * @return string
     */
    public function getDirectPath(): string
    {
        return $this->data->directPath;
    }

    /**
     * Mimetype
     *
     * @return string
     */
    public function getMimetype(): string
    {
        return $this->data->mimetype;
    }

    /**
     * Duaration
     *
     * @return integer
     */
    public function getDuration(): int
    {
        return intval($this->data->duration);
    }

    /**
     * File Hash
     *
     * @return string
     */
    public function getFilehash(): string
    {
        return $this->data->filehash;
    }

    /**
     * Upload Hash
     *
     * @return string
     */
    public function getUploadhash(): string
    {
        return $this->data->uploadhash;
    }

    /**
     * Enc File Hash
     *
     * @return string
     */
    public function getEncFilehash(): string
    {
        return $this->data->encFilehash;
    }

    /**
     * Width
     *
     * @return integer
     */
    public function getWidth(): int
    {
        return $this->data->width;
    }

     /**
     * Height
     *
     * @return integer
     */
    public function getHeight(): int
    {
        return $this->data->height;
    }

    /**
     * Size
     *
     * @return integer
     */
    public function getSize(): int
    {
        return $this->data->size;
    }

    /**
     * Caption
     *
     * @return string
     */
    public function getCaption(): string
    {
        return $this->data->caption;
    }

    /**
     * Streaming Sidecar
     *
     * @return object|null
     */
    public function getStreamingSidecar(): ?object
    {
        return $this->data->streamingSidecar;
    }

    /**
     * Media Key
     *
     * @return string
     */
    public function getMediaKey(): string
    {
        return $this->data->mediaKey;
    }

    /**
     * Media Key Timestamp
     *
     * @return integer
     */
    public function getMediaKeyTimestamp(): int
    {
        return $this->data->mediaKeyTimestamp;
    }

    /**
     * Location
     *
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->data->loc;
    }

    /**
     * Latitude
     *
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->data->lat;
    }

    /**
     * Logitude
     *
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->data->lng;
    }

    /**
     * Is From Template
     *
     * @return boolean
     */
    public function getIsFromTemplate(): bool
    {
        return $this->data->isFromTemplate;
    }

    /**
     * Broadcast
     *
     * @return string
     */
    public function getBroadcast(): bool
    {
        return $this->data->broadcast;
    }

    /**
     * Mentioned J Id List
     *
     * @return array
     */
    public function getMentionedJidList(): array
    {
        return $this->data->mentionedJidList;
    }

    /**
     * Is Vcard Over Mms Document
     *
     * @return boolean
     */
    public function getIsVcardOverMmsDocument(): bool
    {
        return $this->data->isVcardOverMmsDocument;
    }

    /**
     * Is Forwarded
     *
     * @return boolean
     */
    public function getIsForwarded(): bool
    {
        return $this->data->isForwarded;
    }

    /**
     * Labels
     *
     * @return array
     */
    public function getLabels(): array
    {
        return $this->data->labels;
    }

    /**
     * Product Header Image Rejected
     *
     * @return boolean
     */
    public function getProductHeaderImageRejected(): bool
    {
        return $this->data->productHeaderImageRejected;
    }

    /**
     * Is Dynamic Reply Buttons Msg
     *
     * @return boolean
     */
    public function getIsDynamicReplyButtonsMsg(): bool
    {
        return $this->data->isDynamicReplyButtonsMsg;
    }

    /**
     * Is Md History Msg
     *
     * @return boolean
     */
    public function getIsMdHistoryMsg(): bool
    {
        return $this->data->isMdHistoryMsg;
    }

    /**
     * Chat Id
     *
     * @return string
     */
    public function getChatId(): string
    {
        return $this->data->chatId;
    }

    /**
     * From Me
     *
     * @return boolean
     */
    public function getFromMe(): bool
    {
        return $this->data->fromMe;
    }

    /**
     * Sender Id
     *
     * @return string
     */
    public function getSenderId(): string
    {
        return $this->data->sender->id;
    }

    /**
     * Sender Name
     *
     * @return string
     */
    public function getSenderName(): string
    {
        return $this->data->sender->name;
    }

    /**
     * Sender Short Name
     *
     * @return string
     */
    public function getSenderShortName(): string
    {
        return $this->data->sender->shortName;
    }

    /**
     * Sender Pushname
     *
     * @return string
     */
    public function getSenderPushname(): string
    {
        return $this->data->sender->pushname;
    }

    /**
     * Sender Type
     *
     * @return string
     */
    public function getSenderType(): string
    {
        return $this->data->sender->type;
    }

    /**
     * Sender Is Business
     *
     * @return boolean
     */
    public function getSenderIsBusiness(): bool
    {
        return $this->data->sender->isBusiness;
    }

    /**
     * Sender Is Enterprise
     *
     * @return boolean
     */
    public function getSenderIsEnterprise(): bool
    {
        return $this->data->sender->isEnterprise;
    }

    /**
     * Sendert Status Mute
     *
     * @return boolean
     */
    public function getSenderStatusMute(): bool
    {
        return $this->data->sender->statusMute;
    }

    /**
     * Sender Section Header
     *
     * @return string
     */
    public function getSenderSectionHeader(): string
    {
        return $this->data->sender->sectionHeader;
    }

    /**
     * Seder Labels
     *
     * @return array
     */
    public function getSenderLabels(): array
    {
        return $this->data->sender->labels;
    }

    /**
     * Sender Formatted Name
     *
     * @return string
     */
    public function getSenderFormattedName(): string
    {
        return $this->data->sender->formattedName;
    }

    /**
     * Sender Is Me
     *
     * @return boolean
     */
    public function getSenderIsMe(): bool
    {
        return $this->data->sender->isMe;
    }

    /**
     * Sender Is PSA
     *
     * @return boolean
     */
    public function getSenderIsPSA(): bool
    {
        return $this->data->sender->isMyContact;
    }

    /**
     * Sender Is User
     *
     * @return boolean
     */
    public function getSenderIsUser(): bool
    {
        return $this->data->sender->isPSA;
    }

    /**
     * Sender Is WA Contact
     *
     * @return boolean
     */
    public function getSenderIsWAContact(): bool
    {
        return $this->data->sender->isWAContact;
    }

    /**
     *  Send Profile Pic Eurl
     *
     * @return string
     */
    public function getSenderProfilePicEurl(): string
    {
        return $this->data->sender->profilePicThumbObj->eurl;
    }

    /**
     *  Send Profile Pic Id
     *
     * @return string
     */
    public function getSenderProfilePicId(): string
    {
        return $this->data->sender->profilePicThumbObj->id;
    }

    /**
     *  Send Profile Pic Img
     *
     * @return string
     */
    public function getSenderProfilePicImg(): string
    {
        return $this->data->sender->profilePicThumbObj->img;
    }

    /**
     *  Send Profile Pic Img Full
     *
     * @return string
     */
    public function getSenderProfilePicImgFull(): string
    {
        return $this->data->sender->profilePicThumbObj->imgFull;
    }

    /**
     *  Send Profile Pic Ram
     *
     * @return string|null
     */
    public function getSenderProfilePicRam(): ?string
    {
        return $this->data->sender->profilePicThumbObj->raw;
    }

    /**
     * Send Profile Pic Tag
     *
     * @return string
     */
    public function getSenderProfilePicTag(): string
    {
        return $this->data->sender->profilePicThumbObj->tag;
    }

    /**
     * Sender Msgs
     *
     * @return string|null
     */
    public function getSenderMsgs(): ?string
    {
        return $this->data->sender->msgs;
    }

    /**
     * Content
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->data->content;
    }

    /**
     * Is Group Msg
     *
     * @return boolean
     */
    public function getIsGroupMsg(): bool
    {
        return $this->data->isGroupMsg;
    }

    /**
     * Is Media
     *
     * @return boolean
     */
    public function getIsMedia(): bool
    {
        return $this->data->isMedia;
    }

    /**
     * Is Notification
     *
     * @return boolean
     */
    public function getIsNotification(): bool
    {
        return $this->data->isNotification;
    }

    /**
     * Is PSA
     *
     * @return boolean
     */
    public function getIsPSA(): bool
    {
        return $this->data->isPSA;
    }

    /**
     * Media Data Type
     *
     * @return string
     */
    public function getMediaDataType(): string
    {
        return $this->data->mediaData->type;
    }

    /**
     * Media Data Media Stage
     *
     * @return string
     */
    public function getMediaDataMediaStage(): string
    {
        return $this->data->mediaData->mediaStage;
    }

    /**
     * Media Data Animation Duration
     *
     * @return integer
     */
    public function getMediaDataAnimationDuration(): int
    {
        return $this->data->mediaData->animationDuration;
    }

    /**
     * Media Data Animated As New Msg
     *
     * @return boolean
     */
    public function getMediaDataAnimatedAsNewMsg(): bool
    {
        return $this->data->mediaData->animatedAsNewMsg;
    }

    /**
     * Media Data Is View Once
     *
     * @return boolean
     */
    public function getMediaDataIsViewOnce(): bool
    {
        return $this->data->mediaData->isViewOnce;
    }

    /**
     * Media Data Sw Streaming Supported
     *
     * @return boolean
     */
    public function getMediaDataSwStreamingSupported(): bool
    {
        return $this->data->mediaData->_swStreamingSupported;
    }

    /**
     * Media Data Listening To Sw Support
     *
     * @return boolean
     */
    public function getMediaDataListeningToSwSupport(): bool
    {
        return $this->data->mediaData->_listeningToSwSupport;
    }

    /**
     * Media Data Is Vcard Over Mms Documment
     *
     * @return boolean
     */
    public function getMediaDataIsVcardOverMmsDocument(): bool
    {
        return $this->data->mediaData->isVcardOverMmsDocument;
    }
}
