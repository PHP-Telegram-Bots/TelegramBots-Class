<?php

define('BOT_CLASS', 1);

$dir = dirname(__FILE__);

$files = array(
    $dir."/init.php", 
    $dir."/helpers.php", 
    $dir."/update.php", 
    $dir."/BotClass.php"
);

foreach($files as $file){
    if(file_exists($file))
        require_once($file);
    else
        throw new Exception ($file.' not found');
}

if(BOT['debug']){
    // uncomment this function to get the php errors in telegram
    set_exception_handler(array('Helpers', 'error_handler'));
    set_error_handler(array('Helpers', 'error_handler_php'));
    
/*
    or: 
    
    set_exception_handler('error_handler');
    //set_error_handler('reportError'); //from https://gist.github.com/YehudaEi/c0ae248fae39020ab4aabc1047984902
    function error_handler($e){
        global $bot;
        $r["file"] = $e->getFile();
        $r["error"] = $e->getMessage();
        $r["line"] = $e->getLine();
        $bot->sendMessage(WEBMASTER_TG_ID, $r);
    }
*/
}


$bot = new Bot(BOT['token'], BOT['debug']);
Helpers::SetBot($bot);

if(isset($chatType) && isset($chatId))
    $bot->SaveID($chatId, $chatType);
