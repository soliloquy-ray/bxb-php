<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("index.php");
require_once("mail.php");
require_once("twilio.php");

$api = base64_decode("QUMzNzc5MDE2YTE2NDcxZjU2NTYxNDZmOGUyMDY3ZmUxOQ==");
$token = base64_decode("MmNjZjExNzQ5YzI5MTY1MDA2ZWQyZWJkYWZlNzNkY2Y=");
$number = '+13123455441';

$tw = Twilio::getInstance($api,$token,$number);
$tw->sms('9988560026','Hello world');


?>