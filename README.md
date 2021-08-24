# WPPConnect Team
## _Wppconnect PHP Client_

Um simples cliente PHP que proporciona acesso fácil aos endpoints do WPPConnect Server.

## Nossos canais online

[![Discord](https://img.shields.io/discord/844351092758413353?color=blueviolet&label=Discord&logo=discord&style=flat)](https://discord.gg/JU5JGGKGNG)
[![Telegram Group](https://img.shields.io/badge/Telegram-Group-32AFED?logo=telegram)](https://t.me/wppconnect)
[![WhatsApp Group](https://img.shields.io/badge/WhatsApp-Group-25D366?logo=whatsapp)](https://chat.whatsapp.com/C1ChjyShl5cA7KvmtecF3L)
[![YouTube](https://img.shields.io/youtube/channel/subscribers/UCD7J9LG08PmGQrF5IS7Yv9A?label=YouTube)](https://www.youtube.com/c/wppconnect)

## Requisitos

* PHP 7.4 ou superior.
* PHP Zip Extension

## Instalação 

```
composer require wppconnect-team/wppconnect-php-client
```

## Uso

### Request

``` php
namespace WPPConnect;

require '../../vendor/autoload.php';

use WPPConnect\Http\Request;
use WPPConnect\Helpers\Util;

$wppconnect = new Request([
    'base_url' => 'http://localhost:8081',
    'secret_key' => 'MYKeYPHP',
    'session' => 'mySession',
    'token' => null
]);
$util = new Util();
 ```
 
 ``` php
# Function: Generated Token
# /api/:session/generate-token
$response = $wppconnect->generateToken();
$response = $util->toArray($response);
if (isset($response['status']) and $response['status'] == 'success') :
    $wppconnect->options['token'] = $response['token'];
endif;
#debug
$util->debug($response);
 ```
 ``` php
 # Function: Start Session
 # /api/:session/start-session
$response = $wppconnect->startSession([
    'webhook' => null,
    'waitQrCode' => true
]);
$response = $wppconnect->toArray($response);
#debug
$util->debug($response);
 ```
 ``` php
# Function: Check Connection Session
# /api/:session/check-connection-session
$response = $wppconnect->checkConnectionSession([);
$response = $wppconnect->toArray($response);
#debug
$util->debug($response);

 ```
 ``` php
# Function: Send Message
# /api/:session/send-message    
$response = $wppconnect->sendMessage([
    'phone' => '5500000000000',
    'message' => 'Opa, funciona mesmo!',
    'isGroup' => false
]);
$response = $wppconnect->toArray($response);
#debug
$util->debug($response);

 ```
 ``` php
# Function: Send File Base64
# /api/:session/send-file-base64 
$response = $wppconnect->sendFileBase64([
    'phone' => '5500000000000',
    'filename' => 'Xpto',
    'base64' => $wppconnect->fileToBase64('xpto.jpg'),
    'isGroup' => false
]);
$response = $wppconnect->toArray($response);
#debug
$util->debug($response);
 ```
 
``` php
# Function: Send Link Preview
# /api/:session/send-link-preview
$response = $wppconnect->sendLinkPreview([
    'phone' => '5500000000000',
    'url' => 'https://github.com/wppconnect-team',
    'caption' => 'WppConnectTeam',
    'isGroup' => false
]);
$response = $wppconnect->toArray($response);
#debug
$util->debug($response);
 ```
 ``` php
# Function: Send Location
# /api/:session/send-location 
$response = $wppconnect->sendLocation([
    'phone' => '5500000000000',
    'lat' => '-23.5489',
    'lng' => '-46.6388',
    'title' => 'Cidade de São Paulo'
    'isGroup' => false
]);
$response = $wppconnect->toArray($response);
#debug
$util->debug($response);
 ```

### Response (Webhook)
Exemplo de webhook para registrar/obter a solicitação/respostas do webhook WPPConnect.

``` php
namespace WPPConnect;

use WPPConnect\Http\Response;
use WPPConnect\Helpers\Util;

require '../../vendor/autoload.php';

$config = require_once('../config/config.php');
$webhook = new Response($config);
$util = new Util();
```
``` php
# QRCode
if ($webhook->getEvent() == 'qrcode') :
    'File: ' .
        $util->base64ToFile($webhook->getQrcode(), 'image/png', $webhook->getFilesFolder());
    die;
endif;
```
``` php
# Message
if ($webhook->getEvent() == 'onmessage' and $webhook->getType() == 'chat') :
    echo '
        Content: ' . $webhook->getContent() . ' 
        Date: ' . $webhook->getDate() . ' 
        From: ' . $webhook->getFrom() . '
        To: ' . $webhook->getTo();
    die;
endif;
```
``` php
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
```
## Funções para uso do Banco de Dados (SQLite, MySQL e Postgres)
``` php
namespace WPPConnect;

use WPPConnect\Db\Adapter;
use WPPConnect\Helpers\Util;

require '../../vendor/autoload.php';

$config = require_once('../config/config.php');

#SQLite
#$adapter = new Adapter($config, 'sqlite');

#Postgres
#$adapter = new Adapter($config, 'postgres');

#MySQL - Default
$adapter = new Adapter($config);
$util = new Util() ;
```
``` php
#Connect
$adapter->connect();

#Disconnect
$adapter->disconnect();
```

``` php
#Create Table
$adapter->createTable('mensagem', [
    'id' => 'serial PRIMARY KEY', // use INTEGER instead of SERIAL on sqlite to get auto ids
    'session' => 'varchar(255)',
    'content' => 'varchar(255)'
]);

#Insert
$adapter->insert('mensagem', ['session' => 'MySessionName', 'content' => 'MyContent']);

#Update
$adapter->update('mensagem', ['content' => 'MyContent_Edit'], ['id' => 1]);

#Update
$adapter->delete('mensagem', ['id' => 1]);

#Truncate Table
$adapter->truncateTable('mensagem');

#Drop Table
$adapter->dropTable('mensagem');

#Get All Tables
$adapter->getAllTables();

#Fetch
$adapter->fetchAll('mensagem', ['id', 'content'], ['id' => 1], [], [], ['id'], 'ASC', 3);
$adapter->fetchAll('mensagem');
$adapter->fetchRow('mensagem', [], [], [], [], ['id'], 'ASC');

#Exec / Query
$adapter->exec('SELECT * FROM mensagem WHERE id = 1');
$adapter->query('SELECT * FROM mensagem WHERE id = ?', 1);

#Others
$adapter->count('mensagem');
$adapter->getColumns('mensagem');
$adapter->hasTable('mensagem');
$adapter->hasColumn('mensagem', 'id');
$adapter->getColumnDatatype('mensagem', 'id');
$adapter->getPrimaryKey('mensagem');

#Foreign Keys
$adapter->getForeignKeys('session');
$adapter->getForeignTablesOut('session');
$adapter->getForeignTablesIn('mensagem');
$adapter->isForeignKey('session', 'id');

#Debug Query
$adapter->debugQuery("SELECT * FROM mensagem WHERE id = ? or id = ?", [1, 2]);
```
## Funções/Métodos Suportados (até este momento) 

Este cliente PHP ainda está em desenvolvimento. 
Veja [aqui](https://github.com/wppconnect-team/wppconnect-server/blob/main/src/routes/index.js) todos os endpoints do WPPConnect Server. 

### Token
- generateToken([:session,:secret_key]) 

### Session
- startAll([:secret_key,:token])
- showAllSessions([:session,,:token]);
- startSession([:session,:token,:webhook,:waitQrCode]);
- closeSession([:session,:token]);
- logoutSession([:session,:token]);
- checkConnectionSession([:session,:token]);
- statusSession([:session,:token]);
- qrcodeSession([:session,:token]);

### Mensagem
- sendMessage([:session,:token,:phone,:message,:isGroup]);
- sendReply([:session,:token,:phone,:message,:messageId,:isGroup]);
- sendFileBase64([:session,:token,:phone,:filename:base64:isGroup]);
- sendStatus([:session,:token,:message,:isGroup]);
- sendLinkPreview([:session,:token,:phone,:url,:caption,:isGroup]);
- sendLocation([:session,:token,:phone,:lat,:lng,:title,:isGroup]);
- sendMentioned([:session,:token,:phone,:message,:mentioned,:isGroup]);
- sendButtons([:session,:token,:phone,:message,:title,:footer,:buttons]);

### Grupo
- createGroup([:session,:token,:participants[:phone,:phone,...],:name]);
- leaveGroup([:session,:token,:groupId]);
- joinCode([:session,:token,:inviteCode]);
- groupMembers([:session,:token,:groupId]);
- addParticipantGroup([:session,:token,:groupId,:phone]);
- removeParticipantGroup([:session,:token,:groupId,:phone,]);
- promoteParticipantGroup([:session,:token,:groupId,:phone]);
- demoteParticipantGroup([:session,:token,:groupId,:phone]);
- groupAdmins([:session,:token,:groupId]);
- groupInviteLink([:session,:token,:groupId]);
- groupRevokeLink([:session,:token,:groupId]);
- allGroups([:session,:token]);
- groupInfoFromInviteLink([:session,:token,:inviteCode]);
- groupMembersIds([:session,:token,:groupId]);
- groupDescription([:session,:token,:groupId,:description]);
- groupProperty([:session,:token,:groupId,:property,:value]);
- groupSubject([:session,:token,:groupId,:title]);
- messagesAdminsOnly([:session,:token,:groupId,:value]);

### Chat
- archiveChat([:session,:token,:phone,:isGroup]);
- clearChat([:session,:token,:phone,:isGroup]);
- deleteChat([:session,:token,:phone]);
- deleteMessage([:session,:token,:phone,:messageId]);
- forwardMessages([:session,:token,:phone,:messageId]);
- allChats([:session,:token]);
- allChatsWithMessages([:session,:token]);
- allMessagesInChat([:session,:token,:phone]);
- allNewMessages([:session,:token,:phone]);
- unreadMessages([:session,:token]);
- allUnreadMessages([:session,:token]);
- chatById([:session,:token,:phone]);
- chatIsOnline([:session,:token,:phone]);
- lastSeen([:session,:token,:phone]);
- listMutes([:session,:token,:type]);
- loadMessagesInChat([:session,:token,:phone]);
- markUnseen([:session,:token,:phone]);
- pinChat([:session,:token,:phone,:state,:isGroup]);
- contactVcard([:session,:token,:phone,:contactsId]);
- sendMute([:session,:token,:phone,:time,:type]);
- sendSeen([:session,:token,:phone]);
- chatState([:session,:token,:phone,:chatstate]);
- typing([:session,:token,:phone,:value,:isGroup]);
- starMessage([:session,:token,:messageId,:star]);
- getMediaByMessage([:session,:token,:messageId]);

### Contatos
- checkNumberStatus([:session,:token,:phone]);
- allContacts([:session,:token]);
- contact([:session,:token,:phone]);
- profile([:session,:token,:phone,]);
- profilePic([:session,:token,:phone]);
- profileStatus([:session,:token,:phone]);
- blockContact([:session,:token,:phone]);
- unblockContact([:session,:token,:phone]);
- blocklist([:session,:token]);
- setProfileStatus([:session,:token,:status]);
- changeUsername([:session,:token,:name]);

### Device
- getBatteryLevel([:session,:token]);
- hostDevice([:session,:token]);

### Outros
- allBroadcastList([:session,:token]);
- subscribePresence([:session,:token,:isGroup,:all]);
- killServiceWorkier([:session,:token]);
- restartService([:session,:token]);

## Postman

Acesse o [Postman Collection do WPPConnect](https://documenter.getpostman.com/view/9139457/TzshF4jQ) com todos os endpoints.