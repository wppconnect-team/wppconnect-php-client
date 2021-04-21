<?php

namespace Wppconnect;

class Wppconnect
{


    public function __construct(array $options = [])
    {

        $this->options = [
            /**
             * Configures a base URL for the client so that requests created using
             * a relative URL are combined with the base_url
             */
            'base_url' => $options['base_url'],

            /**
             * Secret Key
             * See: https://github.com/wppconnect-team/wppconnect-server#secret-key
             */
            'secret_key' => $options['secret_key'],

            /**
             * Your Session Name
             */
            'session' => $options['session'],
        ];
    }

    /**
     * Debug function
     * Like laravel dd
     *
     * @param array $array
     * @return string
     */
    public function debug(array $array): void
    {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
        die;
    }

  /**
   * toArray function
   *
   * @param object $content
   * @return array
   */
    public function toArray(string $content): array
    {
        return json_decode($content, true);
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
     * @return string
     */
    protected function sendCurl(string $method, string $function, array $data): string
    {
        /**
         * Route
         */
        $function = strtolower(preg_replace("([A-Z])", "-$0", $function));

        /**
         * Api URL
         */
        $api = ($function == "generate-token") ? 'api/' . $this->options['session'] . '/' .
             $this->options['secret_key'] : 'api/' . $this->options['session'];

        /**
         * Header define
         */
        $header = ['Content-Type: application/json','Cache-control: no-cache'];
        if (isset($_SESSION['token'])) :
            array_push($header, 'Authorization: Bearer ' . $_SESSION['token']);
        endif;

        /**
         * Request via cURL
         */
        $ch = curl_init();
        if ($method == "post") :
            curl_setopt($ch, CURLOPT_URL, $this->options['base_url'] . '/' .  $api . '/' . $function);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        else :
            curl_setopt($ch, CURLOPT_URL, $this->options['base_url'] . '/' .  $api . '/' . $function .
                 '?' . http_build_query($data));
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
    public function generateToken(array $data): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }


    /**
     * Start Session
     *
     * @param array $data
     * @return string
     */
    public function startSession(array $data): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Close Session
     *
     * @param array $data
     * @return string
     */
    public function closeSession(array $data): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Check Connection Session
     *
     * @param array $data
     * @return string
     */
    public function checkConnectionSession(array $data): string
    {
        return $this->sendCurl('get', __FUNCTION__, $data);
    }

    /**
     * Send Message
     *
     * @param array $data
     * @return string
     */
    public function sendMessage(array $data): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send File Base64
     *
     * @param array $data
     * @return string
     */
    public function sendFileBase64(array $data): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Link Preview
     *
     * @param array $data
     * @return string
     */
    public function sendLinkPreview(array $data): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }

    /**
     * Send Location
     *
     * @param array $data
     * @return string
     */
    public function sendLocation(array $data): string
    {
        return $this->sendCurl('post', __FUNCTION__, $data);
    }
}
