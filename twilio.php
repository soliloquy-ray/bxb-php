<?php

require('twilio/Rest/Client.php');
require('twilio/Domain.php');
require('twilio/Exceptions/TwilioException.php');
require('twilio/Exceptions/EnvironmentException.php');
require('twilio/Exceptions/RestException.php');
require('twilio/Version.php');
require('twilio/ListResource.php');
require('twilio/Values.php');
require('twilio/Serialize.php');
require('twilio/VersionInfo.php');
require('twilio/InstanceContext.php');
require('twilio/Rest/Api/V2010/Account/MessageInstance.php');
require('twilio/Rest/Api/V2010/Account/MessageList.php');
require('twilio/Rest/Api/V2010/AccountContext.php');
require('twilio/Rest/Api/V2010.php');
require('twilio/Rest/Api.php');
require('twilio/Http/Client.php');
require('twilio/Http/Response.php');
require('twilio/Http/CurlClient.php');

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
