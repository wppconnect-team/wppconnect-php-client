# WPPConnect Team
## _Wppconnect PHP Client_

A simple API class PHP, providing easy access to wppconnect's endpoints.

## Requirements

* PHP 7.0 or newer

## Install

Simply Download the Release to install the API client class.

## Config

Associative array of Request Options, that are applied to every request, created by the client.

Example:
``` php
$this->options = [
    /**
     * Configures a base URL for the client so that requests created using
     * a relative URL are combined with the base_url
     */
    'base_url' => 'http://192.168.0.76:21465',
    
    /**
     * Secret Key
     * See: https://github.com/wppconnect-team/wppconnect-server#secret-key
     */
    'secret_key' => 'MYKeYPHP',

    /**
     * Your Session Name
     */
    'session' => 'mySession'
];
```

## Usage

``` php
$wppconnect = new Wppconnect([
    'base_url' => 'http://192.168.0.76:21465',
    'secret_key' => 'MYKeYPHP',
    'session' => 'mySession',
]);
 ```
 ``` php
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
 ```
 ``` php
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
 ```
 ``` php
# Function: Check Connection Session
# /api/:session/check-connection-session

if (isset($_SESSION['token']) and isset($_SESSION['init'])) :
    $response = $wppconnect->checkConnectionSession([
        'session' => $wppconnect->options['session'],
    ]);
    $response = $wppconnect->toArray($response);

    #debug
    $wppconnect->debug($response);
endif;
 ```
 ``` php
# Function: Send Message
# /api/:session/send-message

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
 ```
 
  ``` php
# Function: Send File Base64
# /api/:session/send-file-base64

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
 ```
 
``` php
# Function: Send Link Preview
# /api/:session/send-link-preview

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
 ```
 ``` php
# Function: Send Location
# /api/:session/send-location

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
 ```

## Functions/methods supported (Up to now)

This class is still in development.
See [here](https://github.com/wppconnect-team/wppconnect-server/blob/main/src/routes/index.js) all the wppconnect-server functions. 

### Sessions
- generateToken([:session, :secret_key]) 
- startSession([:session]) 
- closeSession([:session]) 
- checkConnectionSession([:session]) 
- showAllSessions([:session]) 
- getMediaByMessage([:session, :messageId])

### Message
- sendMessage([:session, :phone, :message]) 
- sendFileBase64([:session, :phone, :base64]) 
- sendLinkPreview([:session, :phone, :url, :caption]) 
- sendLocation([:session, :phone, :lat, :long, :title]) 
- sendImage([:session, :phone, :caption, :path]) 
- sendStatus([:session, :message]) 

### Group
- createGroup([:session, :participants[:phone, :phone, ...], :name]) 

### Other
- showAllContacts([:session]) 
- showAllChats([:session]) 
- showAllGroups([:session]) 
- showAllBlocklist([:session]) 
- getChatById([:session, :phone]) 
- blockContact([:session, :phone]) 
- unblockContact([:session, :phone]) 
- archiveChat([:session, :phone]) 
- pinChat([:session, :phone, :state]) 
- deleteMessage([:session, :phone]) 
- forwardMessages([:session, :phone, :messageId]) 
- markUnseenContact([:session, :phone]) 

### Device
- getBatteryLevel([:session]) 
- getHostDevice([:session]) 

## Webhook Test

A helper [class](https://github.com/wppconnect-team/wppconnect-php-client/blob/main/util/webhook.php) to log and get the Wppconnect webhook request.

### Configuration
This server use environment variables to define some options, that can be:

* `WEBHOOK_URL` - The webhook url to receive WhatsApp events (Messages, ACKs, States)

As a alternative, you can define theses options using the `.env` file.
To do that, you can make a copy of `.env.example` (`cp .env.example .env`) and change the values

### Usage

``` php
$webhook = new Webhook();
$requestData = $webhook->getRequest();
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[manual]: http://guzzle.readthedocs.org/en/latest/
