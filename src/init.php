<?php

if(!defined('BOT_CLASS')) throw new Exception ('the file '.__FILE__.'can\'t run alone');

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Jerusalem');
// used to save sqlite data file
if(!defined('DATA_PATH'))
    define('DATA_PATH', '/var/telegram-bots/BotsDATA/');

// used by error heandlig function
if(!defined('WEBMASTER_TG_ID'))
    define('WEBMASTER_TG_ID', '<YOUR_TELEGRAM_ID>');

// Debug mode
if(!BOT['debug'])
    error_reporting(0);

$update = json_decode(file_get_contents('php://input'), true); 
if(($update == NULL || !defined('BOT')) && !defined('SEND_MESSAGE')){
    http_response_code(403);
    //include '403.html';
    die();
}
