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

class Request
{

    public function __construct(array $options = [])
    {

        $this->options = [
            /**
             * Configure a base URL for the client so that requests created using
             * a relative URL are combined with the base_url
             */
            'base_url' => $options['base_url'],

             /**
             * Session
             * Configure your session name
             */
            'session' => $options['session'],

             /**
             * Secret Key
             * See: https://github.com/wppconnect-team/wppconnect-server#secret-key
             */
            'secret_key' => $options['secret_key'],

             /**
             * Token
             * See: https://github.com/wppconnect-team/wppconnect-server#generate-token
             */
            'token' => $options['token']
        ];
    }

    /**
     * Return the server information such as headers, paths
     * and script locations.
     *
     * @param string $id
     * @return string
     */
    protected function getServerVar(string $id): string
    {
        return isset($_SERVER[$id]) ? $_SERVER[$id] : '';
    }

    /**
     *  Create a header
     *
     * @param string $str
     * @return string
     */
    protected function header(string $str): string
    {
        return $str;
    }

    /**
     * Define a head
     *
     * @return void
     */
    protected function head(): void
    {
        $this->header('Pragma: no-cache');
        if (strpos($this->getServerVar('HTTP_ACCEPT'), 'application/json') !== false) :
            $this->header('Content-type: application/json');
        else :
            $this->header('Content-type: text/plain');
        endif;
    }

    /**
     * Create a body
     *
     * @param string $str
     * @return string
     */
    protected function body(string $str): string
    {
        return json_encode($str);
    }

    /**
     * Create a response
     *
     * @param string $content
     * @param boolean $print
     * @return string
     */
    protected function response(string $content, bool $print = true): string
    {
        if ($print) :
            $this->head();
            $this->body($content);
        endif;
        return $content;
    }

    /**
     * cURL to make POST and GET requests
     *
     * @param string $method
     * @param string $function
     * @param array $data
     * @param string $param
     * @return string
     */
    protected function sendCurl(string $method, string $function, array $data, string $param = null): string
    {
        /**
         * Route
         */
        $function = strtolower(preg_replace("([A-Z])", "-$0", $function));

        /**
         * Api URL
         */
        if ($function == "start-all") :
            $api =  'api/' . $this->options['secret_key'];
        else :
            $api = ($function == "generate-token") ? 'api/' . $this->options['session'] . '/' .
                $this->options['secret_key'] : 'api/' . $this->options['session'];
        endif;

        /**
         * Header define
         */
        $header = ['Content-Type: application/json','Cache-control: no-cache'];
        if (isset($this->options['token'])) :
            array_push($header, 'Authorization: Bearer ' .  $this->options['token']);
        endif;

        /**
         * Request via cURL
         */
        $ch = curl_init();
        if ($method == "post") :
            #TEMP HOOK (NEED TO CHANGE ROUTE IN WPPSERVER)
            $function = ($function == "set-profile-pic" or $function == "set-profile-status")
                ? substr($function, 4) : $function;
            curl_setopt($ch, CURLOPT_URL, $this->options['base_url'] . '/' .  $api . '/' . $function);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        else :
            if ($param !== null) :
                curl_setopt($ch, CURLOPT_URL, $this->options['base_url'] . '/' .  $api . '/' . $function .
                '/' . $data[$param]);
            else :
                curl_setopt($ch, CURLOPT_URL, $this->options['base_url'] . '/' .  $api . '/' . $function .
                '?' . http_build_query($data));
            endif;
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        endif;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        #some servers need SSL VERIFY PEER option. if your case, please uncomment it.
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        if ($result === false) :
            echo 'Curl error: ' . curl_error($ch);
            die;
        endif;
        curl_close($ch);
        return $this->response($result);
    }

    /**
     * Generation Token
     *
     * @param array $data
     * @return string
     */
    public function generateToken(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Start All Session
     *
     * @param array $data
     * @return string
     */
    public function startAll(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Show All Session
     *
     * @param array $data
     * @return string
     */
    public function showAllSessions(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Check Connection Session
     *
     * @param array $data
     * @return string
     */
    public function checkConnectionSession(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Get Media By Message
     *
     * @param array $data
     * @return string
     */
    public function getMediaByMessage(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'messageId');
    }

    /**
     * Status Session
     *
     * @param array $data
     * @return string
     */
    public function statusSession(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Qrcode Session
     *
     * @param array $data
     * @return string
     */
    public function qrcodeSession(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Start Session
     *
     * @param array $data
     * @return string
     */
    public function startSession(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Close Session
     *
     * @param array $data
     * @return string
     */
    public function closeSession(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Logout Session
     *
     * @param array $data
     * @return string
     */
    public function logoutSession(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }


    /**
     * Subscribe Presence
     *
     * @param array $data
     * @return string
     */
    public function subscribePresence(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Message
     *
     * @param array $data
     * @return string
     */
    public function sendMessage(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Image
     *
     * @param array $data
     * @return string
     */
    public function sendImage(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Reply
     *
     * @param array $data
     * @return string
     */
    public function sendReply(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send File
     *
     * @param array $data
     * @return string
     */
    public function sendFile(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data, 'send-file');
    }

    /**
     * Send File Base64
     *
     * @param array $data
     * @return string
     */
    public function sendFileBase64(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Voice
     *
     * @param array $data
     * @return string
     */
    public function sendVoice(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Status
     *
     * @param array $data
     * @return string
     */
    public function sendStatus(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Link Preview
     *
     * @param array $data
     * @return string
     */
    public function sendLinkPreview(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Location
     *
     * @param array $data
     * @return string
     */
    public function sendLocation(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Mentioned
     *
     * @param array $data
     * @return string
     */
    public function sendMentioned(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Buttons
     *
     * @param array $data
     * @return string
     */
    public function sendButtons(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * All Broadcast List
     *
     * @param array $data
     * @return string
     */
    public function allBroadcastList(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * All Groups
     *
     * @param array $data
     * @return string
     */
    public function allGroups(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }


    /**
     * Group Members
     *
     * @param array $data
     * @return string
     */
    public function groupMembers(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'groupId');
    }

    /**
     * Group Admins
     *
     * @param array $data
     * @return string
     */
    public function groupAdmins(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'groupId');
    }

    /**
     * Group Invite Link
     *
     * @param array $data
     * @return string
     */
    public function groupInviteLink(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'groupId');
    }

    /**
     * Group Revoke Link
     *
     * @param array $data
     * @return string
     */
    public function groupRevokeLink(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'groupId');
    }

    /**
     * Group Members Ids
     *
     * @param array $data
     * @return string
     */
    public function groupMembersIds(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'groupId');
    }

    /**
     * Create Group
     *
     * @param array $data
     * @return string
     */
    public function createGroup(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Leave Group
     *
     * @param array $data
     * @return string
     */
    public function leaveGroup(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Join Code
     *
     * @param array $data
     * @return string
     */
    public function joinCode(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Add Participant Group
     *
     * @param array $data
     * @return string
     */
    public function addParticipantGroup(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Add Participant Group
     *
     * @param array $data
     * @return string
     */
    public function removeParticipantGroup(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Promote Participant Group
     *
     * @param array $data
     * @return string
     */
    public function promoteParticipantGroup(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Demote Participant Group
     *
     * @param array $data
     * @return string
     */
    public function demoteParticipantGroup(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Group Info From Invite Link
     *
     * @param array $data
     * @return string
     */
    public function groupInfoFromInviteLink(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Group Description
     *
     * @param array $data
     * @return string
     */
    public function groupDescription(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Group Property
     *
     * @param array $data
     * @return string
     */
    public function groupProperty(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Group Subject
     *
     * @param array $data
     * @return string
     */
    public function groupSubject(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Messages Admins Only
     *
     * @param array $data
     * @return string
     */
    public function messagesAdminsOnly(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Group Pic
     *
     * @param array $data
     * @return string
     */
    public function groupPic(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Change Privacy Group
     *
     * @param array $data
     * @return string
     */
    public function changePrivacyGroup(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * All Chats
     *
     * @param array $data
     * @return string
     */
    public function allChats(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * All Chats With Messages
     *
     * @param array $data
     * @return string
     */
    public function allChatsWithMessages(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * All Messages In Chat
     *
     * @param array $data
     * @return string
     */
    public function allMessagesInChat(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * All Messages New Messages
     *
     * @param array $data
     * @return string
     */
    public function allNewMessages(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Unread Messages
     *
     * @param array $data
     * @return string
     */
    public function unreadMessages(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * All Unread Messages
     *
     * @param array $data
     * @return string
     */
    public function allUnreadMessages(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Chat By Id
     *
     * @param array $data
     * @return string
     */
    public function chatById(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * Message By Id
     *
     * @param array $data
     * @return string
     */
    public function messageById(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * Chat Is Online
     *
     * @param array $data
     * @return string
     */
    public function chatIsOnline(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * Last Seen
     *
     * @param array $data
     * @return string
     */
    public function lastSeen(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * List Mutes
     *
     * @param array $data
     * @return string
     */
    public function listMutes(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'type');
    }

    /**
     * Load Messages In Chat
     *
     * @param array $data
     * @return string
     */
    public function loadMessagesInChat(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * Load Earlier Messages
     *
     * @param array $data
     * @return string
     */
    public function loadEarlierMessages(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * Archive Chat
     *
     * @param array $data
     * @return string
     */
    public function archiveChat(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Get Clear Chat
     *
     * @param array $data
     * @return string
     */
    public function clearChat(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Get Delete Chat
     *
     * @param array $data
     * @return string
     */
    public function deleteChat(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Delete Message
     *
     * @param array $data
     * @return string
     */
    public function deleteMessage(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Forward Messages
     *
     * @param array $data
     * @return string
     */
    public function forwardMessages(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Mark Unseen
     *
     * @param array $data
     * @return string
     */
    public function markUnseen(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Pin Chat
     *
     * @param array $data
     * @return string
     */
    public function pinChat(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Contact Vcard
     *
     * @param array $data
     * @return string
     */
    public function contactVcard(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Mute
     *
     * @param array $data
     * @return string
     */
    public function sendMute(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Seen
     *
     * @param array $data
     * @return string
     */
    public function sendSeen(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Chat State
     *
     * @param array $data
     * @return string
     */
    public function chatState(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Temporary Messages
     *
     * @param array $data
     * @return string
     */
    public function temporaryMessages(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Typing
     *
     * @param array $data
     * @return string
     */
    public function typing(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Star Message
     *
     * @param array $data
     * @return string
     */
    public function starMessage(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Check Number Status
     *
     * @param array $data
     * @return string
     */
    public function checkNumberStatus(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * All Contacts
     *
     * @param array $data
     * @return string
     */
    public function allContacts(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Contact
     *
     * @param array $data
     * @return string
     */
    public function contact(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * Profile
     *
     * @param array $data
     * @return string
     */
    public function profile(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * Profile Pic
     *
     * @param array $data
     * @return string
     */
    public function profilePic(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * Profile Status
     *
     * @param array $data
     * @return string
     */
    public function profileStatus(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data, 'phone');
    }

    /**
     * BlockList
     *
     * @param array $data
     * @return string
     */
    public function blocklist(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Block Contact
     *
     * @param array $data
     * @return string
     */
    public function blockContact(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Get Business Profiles Products
     *
     * @param array $data
     * @return string
     */
    public function getBusinessProfilesProducts(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Get Order By Message Id
     *
     * @param array $data
     * @return string
     */
    public function getOderByMessageId(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Unblock Contact
     *
     * @param array $data
     * @return string
     */
    public function unblockContact(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Get Battery Level
     *
     * @param array $data
     * @return string
     */
    public function getBatteryLevel(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Host Device
     *
     * @param array $data
     * @return string
     */
    public function hostDevice(array $data = []): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Download Media
     *
     * @param array $data
     * @return string
     */
    public function downloadMedia(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * kill Service Workier
     *
     * @param array $data
     * @return string
     */
    public function killServiceWorkier(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Restart Service
     *
     * @param array $data
     * @return string
     */
    public function restartService(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Set Profile Pic
     *
     * @param array $data
     * @return string
     */
    public function setProfilePic(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Set Profile Status
     *
     * @param array $data
     * @return string
     */
    public function setProfileStatus(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Change Username
     *
     * @param array $data
     * @return string
     */
    public function changeUsername(array $data = []): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }
}
