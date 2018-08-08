<?php

require_once('twilio/Rest/Client.php');
require_once('twilio/Domain.php');
require_once('twilio/Exceptions/TwilioException.php');
require_once('twilio/Exceptions/EnvironmentException.php');
require_once('twilio/Exceptions/RestException.php');
require_once('twilio/Version.php');
require_once('twilio/ListResource.php');
require_once('twilio/Values.php');
require_once('twilio/Serialize.php');
require_once('twilio/VersionInfo.php');
require_once('twilio/InstanceContext.php');
require_once('twilio/Rest/Api/V2010/Account/MessageList.php');
require_once('twilio/Rest/Api/V2010/AccountContext.php');
require_once('twilio/Rest/Api/V2010.php');
require_once('twilio/Rest/Api.php');
require_once('twilio/Http/Client.php');
require_once('twilio/Http/Response.php');
require_once('twilio/Http/CurlClient.php');

use Twilio\Rest\Client;

class Twilio{

    private $api;
    private $token;
    private $number;
    private $mail;
    private static $_instance;

    public static function getInstance($api = '',$token = '', $number = ''){
        if(!self::$_instance){
            self::$_instance = new self($api, $token, $number);
        }
        return self::$_instance;
    }

    private function __construct($api = '', $token = '', $number = ''){
        $this->api = $api;
        $this->token = $token;
        $this->number = $number;
    }

    public function sms($mobile,$msg){
        try {

            // Your Account SID and Auth Token from twilio
            $sid = $this->api;
            $token = $this->token;

            $client = new Client($sid, $token);

            // Use the client to do fun stuff like send text messages!
            $client->messages->create(
                // the number you'd like to send the message to
                "+63".$mobile,
                array(
                    'from' => $this->number,
                    'body' => $msg
                )
            );
            return true;
        } catch (Exception $e) {

             var_dump($e); 
             return false;
        }
    }

}
