<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("index.php");
require_once("mail.php");
require_once("twilio.php");

$api = base64_decode("QUM0NGIwMGJiN2MwNzkzMmRhM2M5MjgwY2RmNDViNGVlOQ==");
$token = base64_decode("NDg5ZjJmMzQ3MzFmNWI2ZTUyMTQ5MzY4ZTQ2NDFkNjY=");
$number = '+15005550006';

$tw = Twilio::getInstance($api,$token,$number);
$tw->sms('9988560027','Hello world');


?>