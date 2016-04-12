<?php

namespace Tabasoft;

class SendPush
{
    private $apple_cert;
    private $message;
    private $token;
    private $http2_server;
    private $app_bundle_id;

    /**
     * @param $apple_cert       the path to the certificate
     * @param $message          the payload to send (JSON)
     * @param $token            the token of the device
     * @param $mode             development/production
     * @param $app_bundle_id    the app bundle id
     */
    public function __construct($apple_cert, $message, $token, $mode="development", $app_bundle_id) {

        $this->apple_cert = $apple_cert;
        $this->message = $message;
        $this->token = $token;
        $this->http2_server = $mode == "development" ? 'https://api.development.push.apple.com' : 'api.push.apple.com';
        $this->app_bundle_id = $app_bundle_id;
    }

    public function openConnection() {

        // open connection
        if (!defined('CURL_HTTP_VERSION_2_0')) {
            define('CURL_HTTP_VERSION_2_0', 3);
        }

        $this->http2ch = curl_init();
        curl_setopt($this->http2ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
    }

    // return the status code (see https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/Chapters/APNsProviderAPI.html#//apple_ref/doc/uid/TP40008194-CH101-SW18)
    public function sendPush() {

        $status = $this->sendHTTP2Push($this->http2ch, $this->http2_server, $this->apple_cert, $this->app_bundle_id, $this->message, $this->token);

        return $status;
    }


    public function closeConnection() {

        curl_close($this->http2ch);
    }

    private function sendHTTP2Push($http2ch, $http2_server, $apple_cert, $app_bundle_id, $message, $token) {

        $milliseconds = round(microtime(true) * 1000);

        // url (endpoint)
        $url = "{$http2_server}/3/device/{$token}";

        // certificate
        $cert = realpath($apple_cert);

        // headers
        $headers = array(
            "apns-topic: {$app_bundle_id}",
            "User-Agent: My Sender"
        );

        // other curl options
        curl_setopt_array($http2ch, array(
            CURLOPT_URL => "{$url}",
            CURLOPT_PORT => 443,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $message,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSLCERT => $cert,
            CURLOPT_HEADER => 1
        ));

        // go...
        $result = curl_exec($http2ch);
        if ($result === FALSE) {
            throw new \Exception('Curl failed with error: ' . curl_error($http2ch));
        }

        // get response
        $status = curl_getinfo($http2ch, CURLINFO_HTTP_CODE);

        $duration = round(microtime(true) * 1000) - $milliseconds;

        return $status == 200 ? "OK:\nPush sent successfully in {$duration}ms" : "Error:\n$result";
    }
}