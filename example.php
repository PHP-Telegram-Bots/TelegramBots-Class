<?php

/*******************************************
 * 
 * https://t.me/returnJson_MYBOT
 * 
 * Created by @YehudaEisenberg
 *  
*******************************************/

namespace YehudaEi\TelegramBots;

require_once('src/AutoLoader.php');
require_once('error.php');

// Fake update - Tests
$update = array (
    'update_id' => 602412044,
    'message' => array (
      'message_id' => 12345,
      'from' => array (
        'id' => 100,
        'is_bot' => false,
        'first_name' => 'Yehuda',
        'last_name' => 'Eisenberg',
        'language_code' => 'en',
      ),
      'chat' => array (
        'id' => 101,
        'first_name' => 'Yehuda',
        'last_name' => 'Eisenberg',
        'type' => 'private',
      ),
      'date' => 1594018800,
      'text' => 'Test Message Text',
    ),
);

$config = new Config();
$config->token = "<TOKEN>";
$config->username = "<USERNAME>";
$config->webhookUrl = "<WEBHOOK_URL>";
$config->logging = true;
$config->parseMode = "html";

$bot = new TelegramBot($config);

$bot->setUpdate($update);

// var_dump($bot->getUpdate()->update_id);
var_dump($bot->getUpdate());

// $bot->sendMessage($user, $text);

