<?php

/*******************************************
 * 
 * https://t.me/returnJson_MYBOT
 * 
 * Created by @YehudaEisenberg
 *  
*******************************************/

define('BOT', array(
    "token" => "<TOKEN>",
    "webHookUrl" => "https://telegram.org/returnJson_MYBOT.php",
    "allowed_updates" => array ("message", "edited_message", "channel_post", "edited_channel_post", "callback_query", "inline_query", "poll", "chosen_inline_result"),
    "debug" => false
));
require_once("src/autoload.php");

$bot->SetUpdate($update);
$bot->SetParseMode('MarkDown');

if($message == "callback"){
    $main_message = json_encode(array(
      'inline_keyboard' => array(array(array('text' => 'callback_data is your id', 'callback_data' => $chatId))),
      'one_time_keyboard' => true,'resize_keyboard' => true
    ));
    $bot->sendMessage($chatId, "callback", $main_message, $messageId);
}
elseif($message == "/start"){
    $main_message = json_encode(array(
      'inline_keyboard' => array(array(array('text' => 'Switch to inline', 'switch_inline_query_current_chat' => ""))),
      'one_time_keyboard' => true,'resize_keyboard' => true
    ));
    $bot->sendMessage($chatId, "hello,\nSend me the text \"`callback`\" to get a message with a button.\n\nCreated by @YehudaEisenberg.", $main_message, $messageId);
}
elseif(isset($InlineQId)){
    $inlineRes = array(array(
            "type" => "article",
            "id" => "1",
            "title" => "Click me!",
            "message_text" => "```\n".json_encode($update, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."```",
            "parse_mode" => $bot->GetParseMode()
        ));
    $bot->answerInlineQuery($InlineQId, json_encode($inlineRes));
}
else
    $bot->sendMessage($chatId,"```\n".json_encode($update, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."```", null, $messageId);