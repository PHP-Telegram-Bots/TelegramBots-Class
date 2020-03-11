<?php

/***********************************************
 * 
 * PHP-Telegram-BOT class
 * 
 * Owner: Yehuda Eisenberg.
 * 
 * Mail: Yehuda.telegram@gmail.com
 * 
 * Link: https://yehudae.ga
 * 
 * Telegram: @YehudaEisenberg
 * 
 * GitHub: https://github.com/YehudaEi
 *
 * License: MIT - אסור לעשות שימוש ציבורי, חובה להשאיר קרדיט ליוצר
 * 
************************************************/


/***********************************************
 * 
 * define('BOT', array(
 *   "token" => "<BOT_TOKEN>",
 *   "webHookUrl" => "https://telegram.org/Bot.php",
 *   "allowed_updates" => array("message", "edited_message"),
 *   "debug" => false
 * ));
 * require_once("BotClass.php");
 * 
***********************************************/

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Jerusalem');
if(!defined('DATA_PATH'))
    define('DATA_PATH', '/var/telegram-bots/BotsDATA/');
if(!defined('WEBMASTER_TG_ID'))
    define('WEBMASTER_TG_ID', '560402434');

$update = json_decode(file_get_contents('php://input'), true); 
if(($update == NULL || !defined('BOT')) && !defined('SEND_MESSAGE')){
    http_response_code(403);
    include '403.html';
    die();
}


if(!BOT['debug'])
    error_reporting(0);

global $bot;
$bot = new Bot(BOT['token'], BOT['debug']);

//text
$message = $update['message']['text']                                ?? null;
//photo
$tphoto = $update['message']['photo']                                ?? null;
if(!empty($tphoto))
    $phid = $update['message']['photo'][count($tphoto)-1]['file_id'] ?? null;
//audio
$auid = $update['message']['audio']['file_id']                       ?? null;
//document
$did = $update['message']['document']['file_id']                     ?? null;
//video
$vidid = $update['message']['video']['file_id']                      ?? null;
//voice
$void = $update['message']['voice']['file_id']                       ?? null;
//video_note
$vnid = $update['message']['video_note']['file_id']                  ?? null;
//contact
$conid = $update['message']['contact']['phone_number']               ?? null;
$conf = $update['message']['contact']['first_name']                  ?? null;
$conl = $update['message']['contact']['last_name']                   ?? null;
//location
$locid1 = $update['message']['location']['latitude']                 ?? null;
$locid2 = $update['message']['location']['longitude']                ?? null;
//Sticker
$sti = $update['message']['sticker']['file_id']                      ?? null;
//Venue
$venLoc1 = $update['message']['venue']['location']['latitude']       ?? null;
$venLoc2 = $update['message']['venue']['location']['longitude']      ?? null;
$venTit = $update['message']['venue']['title']                       ?? null;
$venAdd = $update['message']['venue']['address']                     ?? null;
//all media
$cap = $update['message']['caption']                                 ?? null;

//Inline
$inlineQ = $update["inline_query"]["query"]                          ?? null;
$InlineQId = $update["inline_query"]["id"]                           ?? null;
$InlineMsId = $update["callback_query"]["inline_message_id"]         ?? null;

//Callbeck
$callId = $update["callback_query"]["id"]                            ?? null;
$callData = $update["callback_query"]["data"]                        ?? null;
$callFromId = $update["callback_query"]["from"]["id"]                ?? null;
$callMessageId = $update["callback_query"]["message"]["message_id"]  ?? null;

//Global parmeters
$chatId = $update['message']['chat']['id']                           ?? null;
$fromId = $update['message']['from']['id']                           ?? null;
$chatType = $update["message"]["chat"]["type"]                       ?? null;
$messageId = $update['message']['message_id']                        ?? null;
$rfid = $update['message']['reply_to_message']['forward_from']['id'] ?? null;
$rtx = $update['message']['reply_to_message']['text']                ?? null;
$forwrdId = $update['message']['forward_from']['id']                 ?? null;
$forwrdFN = $update['message']['forward_from']['first_name']         ?? null;
$forwrdLN = $update['message']['forward_from']['last_name']          ?? null;
$forwrdUN = $update['message']['forward_from']['username']           ?? null;
$fName = $update["message"]["from"]["first_name"]                    ?? null;
$lName = $update["message"]["from"]["last_name"]                     ?? null;

//Channel
if(isset($update['channel_post'])){
    //text
    $message = $update['channel_post']['text']                                ?? null;
    //photo
    $tphoto = $update['channel_post']['photo']                                ?? null;
    if(!empty($tphoto))
        $phid = $update['channel_post']['photo'][count($tphoto)-1]['file_id'] ?? null;
    //audio
    $auid = $update['channel_post']['audio']['file_id']                       ?? null;
    //document
    $did = $update['channel_post']['document']['file_id']                     ?? null;
    //video
    $vidid = $update['channel_post']['video']['file_id']                      ?? null;
    //voice
    $void = $update['channel_post']['voice']['file_id']                       ?? null;
    //video_note
    $vnid = $update['channel_post']['video_note']['file_id']                  ?? null;
    //contact
    $conid = $update['channel_post']['contact']['phone_number']               ?? null;
    $conf = $update['channel_post']['contact']['first_name']                  ?? null;
    $conl = $update['channel_post']['contact']['last_name']                   ?? null;
    //location
    $locid1 = $update['channel_post']['location']['latitude']                 ?? null;
    $locid2 = $update['channel_post']['location']['longitude']                ?? null;
    //Sticker
    $sti = $update['channel_post']['sticker']['file_id']                      ?? null;
    //Venue
    $venLoc1 = $update['channel_post']['venue']['location']['latitude']       ?? null;
    $venLoc2 = $update['channel_post']['venue']['location']['longitude']      ?? null;
    $venTit = $update['channel_post']['venue']['title']                       ?? null;
    $venAdd = $update['channel_post']['venue']['address']                     ?? null;
    //all media
    $cap = $update['channel_post']['caption']                                 ?? null;

    
    $chatId = $update['channel_post']['chat']['id'];
    $messageId = $update['channel_post']['message_id'];
    $chatType = "channel";
}
//EditMessage
if(isset($update['edited_message'])){
    $isEdited = true;
    $message = $update['edited_message']['text'] ?? null;
    $chatId = $update['edited_message']['chat']['id'];
    $chatType = $update["edited_message"]["chat"]["type"];
    $messageId = $update['edited_message']['message_id'];
}
//CallBeck
if(isset($update['callback_query'])){
    $message = $update["callback_query"]["data"];
    $chatId = $update["callback_query"]["message"]["chat"]["id"];
    $fromId = $update["callback_query"]["from"]["id"];
    $messageFromId = $update["callback_query"]["message"]["from"]["id"] ?? null;
    $messageId = $update["callback_query"]["message"]["message_id"];
}
//InlineMode
if(isset($update['inline_query'])){
    $fromId = $update["inline_query"]["from"]["id"];
}

if(isset($chatType) && isset($chatId))
    $bot->SaveID($chatId, $chatType);

class Bot{
    private $BotToken;
    private $BotId;
    private $BotName;
    private $BotUserName;
    private $DBName;
    private $Debug;
    private $beautifi = true;
    private $update = null;
    private $webHook = null;
    private $webPagePreview = true;
    private $Notification = false;
    private $ParseMode = null;

    public function Bot($token, $Debug = false){
        $botInfo = json_decode(file_get_contents("https://api.telegram.org/bot".$token."/getMe"), true);
        if($Debug && 0)
            $this->logging($botInfo, false, "BotInfoOutput: Success!", true);
        if($botInfo['ok'] == true && $botInfo['result']['is_bot'] == true){
            $this->BotToken = $token;
            $this->Debug = $Debug;
            $this->BotId = $botInfo['result']['id'];
            $this->BotName = $botInfo['result']['first_name'];
            $this->BotUserName = $botInfo['result']['username'];
            $this->DBName = DATA_PATH.$botInfo['result']['id']." - ".$this->BotUserName.'.sqlite';
            
            //$old_db_name = DATA_PATH.$this->BotUserName.'.sqlite';
            //if(isset($old_db_name)){
            //    copy($old_db_name, $this->DBName);
            //    unlink($old_db_name);
            //}
                
            //Update WebHook
<<<<<<< Updated upstream:BotClass.php
            $res = $this->Request("getwebhookinfo");
            if($res['result']['url'] != BOT['webHookUrl'])
                $this->Request("setwebhook", array('url' => BOT['webHookUrl']));
            return true;
=======
            if(isset(BOT['webHookUrl'])){
                $res = $this->Request("getwebhookinfo");
                if($res['result']['url'] != BOT['webHookUrl'])
                    if(isset(BOT['allowed_updates']))
                        $this->Request("setwebhook", array('url' => BOT['webHookUrl'], "allowed_updates" => BOT['allowed_updates']));
                    else
                        $this->Request("setwebhook", array('url' => BOT['webHookUrl']));
                return true;
            }
            // set ParseMode
            if(isset(BOT['parseMode'])){
                $this->SetParseMode(BOT['parseMode']);
            }
>>>>>>> Stashed changes:src/BotClass.php
        }
        else return false;
    }
    
    private function DB($q){
        try{
            $DBConn = new SQLite3($this->DBName , SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE) 
                    or $this->sendMessage(WEBMASTER_TG_ID, 'error conncet to db. username:'.$this->BotUserName);
            $DBConn->query('CREATE TABLE IF NOT EXISTS "users" (
                        "user_id" INT(11) PRIMARY KEY,
                        "type" VARCHAR,
                        "time" TIMESTAMP
                    )');
            $DBConn->query($q);
            $DBConn->close();
        }
        catch(Exception $e){
            
        }
    }
    
    public function SaveID($id, $type){
        $this->DB('INSERT OR IGNORE INTO "users" ("user_id", "type", "time")
            VALUES ("'.$id.'", "'.$type.'" ,"'.time().'")');
    }
    
    //Setters && Getters
        //Debug Mode
    public function GetDebug(){
        return $this->Debug;
    }
    public function SetDebug($val){
        $this->Debug = $val;
    }
        //WebHook
    public function GetWebHook(){
        return $this->webHook;
    }
    public function SetWebHook($val){
        $this->webHook = $val;
        return $this->Request('setwebhook', array("url" => $val, "allowed_updates" => BOT['allowed_updates']))['ok'];
    }
    public function DetWebHook(){
        $this->webHook = NULL;
        return $this->Request('setwebhook', array("url"))['ok'];
    }
        //Updates - BETA!
    public function SetUpdate($update){
        $this->Update = $update;
        if($this->Debug)
            $this->logging($update, false, "Update input:", true);
    }
    public function GetUpdate(){
        return $this->Update;
    }
        //WebPagePreview Mode
    public function GetWebPagePreview(){
        return $this->webPagePreview;
    }
    public function SetWebPagePreview($val){
        $this->webPagePreview = $val;
    }
        //Notification Mode
    public function GetNotification(){
        return $this->Notification;
    }
    public function SetNotification($val){
        $this->Notification = $val;
    }
        //ParseMode Mode
    public function GetParseMode(){
        return $this->ParseMode;
    }
    public function SetParseMode($val){
        if("markdown" == strtolower($val) || "html" == strtolower($val) || null == $val)
            $this->ParseMode = $val;
    }
        //DBName
    public function GetDBName(){
        return $this->DBName;
    }
    
    //SendRequest
    private function Request($method, $data =[] ==null){
        $BaseUrl = "https://api.telegram.org/bot".$this->BotToken."/".$method;
    	
        $ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $BaseUrl);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch ,CURLOPT_POSTFIELDS, $data);
       
        $res = curl_exec($ch);
        if(curl_error($ch)){
            if($this->Debug)
                $this->logging(curl_error($ch), "Curl: ".$method, false, false, $data);
    		curl_close($ch);
        }else{
            curl_close($ch);
            $res = json_decode($res, true);
            if($this->Debug)
                $this->logging($res, "Curl: ".$method, true, true, $data);
            return $res;
        }
    }
    
    //Logging
    public function logging($data, $method = null, $success = false, $array = false, $helpArgs = null){
        $tmp = ($this->beautifi ? JSON_PRETTY_PRINT : null ) | JSON_UNESCAPED_UNICODE;
        if(!$array)
            $data = array("data" => $data);
        
        $data['added_by_log']['helpArgs'] = $helpArgs;
        $data['added_by_log']['date'] = date(DATE_RFC850);
        $data['added_by_log']['botUserName'] = $this->BotUserName;
        $data['added_by_log']['success'] = ($success ? "Success!" : "Error");
        $data['added_by_log']['method'] = $method;
        
        $data = json_encode($data, $tmp);
        file_put_contents($this->BotUserName." - log.log", $data.",\n", FILE_APPEND | LOCK_EX);
    }
    
    //Methods
    public function sendMessage($id, $text, $replyMarkup = null, $replyMessage = null){
        $data["chat_id"] = $id;
        $data["text"] = $text;
        $data["parse_mode"] = $this->ParseMode;
        $data["disable_web_page_preview"] = $this->webPagePreview;
        $data["disable_notification"] = $this->Notification;
        $data["reply_to_message_id"] = $replyMessage;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("sendMessage", $data);
    }
    public function forwardMessage($id, $fromChatId, $messageId){
        $data["chat_id"] = $id;
        $data["from_chat_id"] = $fromChatId;
        $data["disable_notification"] = $this->Notification;
        $data["message_id"] = $messageId;
        return $this->Request("forwardMessage", $data);
    }
    public function sendPhoto($id, $photo, $caption = null, $replyMessage = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["photo"] = $photo;
        $data["caption"] = $caption;
        $data["parse_mode"] = $this->ParseMode;
        $data["disable_notification"] = $this->Notification;
        $data["reply_to_message_id"] = $replyMessage;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("sendPhoto", $data);
    }
    public function sendAudio($id, $audio, $duration = null, $performer = null, $title = null, $replyMessage = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["audio"] = $audio;
        $data["duration"] = $duration;
        $data["performer"] = $performer;
        $data["title"] = $title;
        $data["disable_notification"] = $this->Notification;
        $data["reply_to_message_id"] = $replyMessage;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("sendAudio", $data);
    }
    public function sendDocument($id, $document, $caption = null, $replyMessage = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["document"] = $document;
        $data["caption"] = $caption;
        $data["parse_mode"] = $this->ParseMode;
        $data["disable_notification"] = $this->Notification;
        $data["reply_to_message_id"] = $replyMessage;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("sendDocument", $data);
    }
    public function sendSticker($id, $sticker, $replyMessage = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["sticker"] = $sticker;
        $data["disable_notification"] = $this->Notification;
        $data["reply_to_message_id"] = $replyMessage;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("sendSticker", $data);
    }
    public function sendVideo($id, $video, $caption = null, $duration = null, $width = null, $height = null, $replyMessage = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["video"] = $video;
        $data["duration"] = $duration;
        $data["width"] = $width;
        $data["height"] = $height;
        $data["caption"] = $caption;
        $data["parse_mode"] = $this->ParseMode;
        $data["disable_notification"] = $this->Notification;
        $data["reply_to_message_id"] = $replyMessage;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("sendVideo", $data);
    }
    public function sendVoice($id, $voice, $duration = null, $replyMessage = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["voice"] = $voice;
        $data["duration"] = $duration;
        $data["disable_notification"] = $this->Notification;
        $data["reply_to_message_id"] = $replyMessage;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("sendVoice", $data);
    }
    public function sendLocation($id, $latitude, $longitude, $replyMessage = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["latitude"] = $latitude;
        $data["longitude"] = $longitude;
        $data["disable_notification"] = $this->Notification;
        $data["reply_to_message_id"] = $replyMessage;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("sendLocation", $data);
    }
    public function sendVenue($id, $latitude, $longitude, $title, $address, $foursquare = null, $replyMessage = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["latitude"] = $latitude;
        $data["longitude"] = $longitude;
        $data["title"] = $title;
        $data["address"] = $address;
        $data["foursquare_id"] = $foursquare;
        $data["disable_notification"] = $this->Notification;
        $data["reply_to_message_id"] = $replyMessage;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("sendVenue", $data);
    }
    public function sendContact($id, $phoneNumber, $firstName, $lastName = null, $replyMessage = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["phone_number"] = $phoneNumber;
        $data["first_name"] = $firstName;
        $data["last_name"] = $lastName;
        $data["disable_notification"] = $this->Notification;
        $data["reply_to_message_id"] = $replyMessage;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("sendContact", $data);
    }
    public function sendChatAction($id, $action){
        if(!in_array($action, ["typing", "upload_photo", "record_video", "upload_video", "record_audio", "upload_audio", "upload_document", "find_location"]))
            return false;
        $data["chat_id"] = $id;
        $data["action"] = $action;
        return $this->Request("sendChatAction", $data);
    }
    public function getUserProfilePhotos($uId, $offset = null, $limit = null){
        $data["user_id"] = $uId;
        $data['offset'] = $offset;
        $data['limit'] = $limit;
        return $this->Request("getUserProfilePhotos", $data);
    }
    public function kickChatMember($id, $uId){
        $data["chat_id"] = $id;
        $data["user_id"] = $uId;
        return $this->Request("kickChatMember", $data);
    }
    public function unbanChatMember($id, $uId){
        $data["chat_id"] = $id;
        $data["user_id"] = $uId;
        return $this->Request("unbanChatMember", $data);
    }
    public function getFile($fileId){
        $data["file_id"] = $fileId;
        return $this->Request("getFile", $data);
    }
    public function leaveChat($id){
        $data["chat_id"] = $id;
        return $this->Request("leaveChat", $data);
    }
    public function getChat($id){
        $data["chat_id"] = $id;
        return $this->Request("getChat", $data);
    }
    public function getChatAdministrators($id){
        $data["chat_id"] = $id;
        return $this->Request("getChatAdministrators", $data);
    }
    public function getChatMembersCount($id){
        $data["chat_id"] = $id;
        return $this->Request("getChatMembersCount", $data);
    }
    public function getChatMember($id, $uId){
        $data["chat_id"] = $id;
        $data["user_id"] = $uId;
        return $this->Request("getChatMember", $data);
    }
    public function answerCallbackQuery($callback, $text = null, $alert = false){
        $data["callback_query_id"] = $callback;
        $data["text"] = $text;
        $data["show_alert"] = $alert;
        return $this->Request("answerCallbackQuery", $data);
    }
    public function editMessageText($id = null, $messageId = null, $inlineMessage = null, $text, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["message_id"] = $messageId;
        $data["inline_message_id"] = $inlineMessage;
        $data["text"] = $text;
        $data["parse_mode"] = $this->ParseMode;
        $data["disable_web_page_preview"] = $this->webPagePreview;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("editMessageText", $data);
    }
    public function editMessageCaption($id = null, $messageId = null, $inlineMessage = null, $caption = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["message_id"] = $messageId;
        $data["inline_message_id"] = $inlineMessage;
        $data["caption"] = $caption;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("editMessageCaption", $data);
    }
    public function editMessageMedia($id = null, $messageId = null, $inlineMessage = null, $media = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["message_id"] = $messageId;
        $data["inline_message_id"] = $inlineMessage;
        $data["media"] = $media;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("editMessageMedia", $data);
    }
    public function editMessageReplyMarkup($id = null, $messageId = null, $inlineMessage = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["message_id"] = $messageId;
        $data["inline_message_id"] = $inlineMessage;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("editMessageReplyMarkup", $data);
    }
    public function deleteMessage($id, $messageId){
        $data["chat_id"] = $id;
        $data["message_id"] = $messageId;
        return $this->Request("deleteMessage", $data);
    }
    public function answerInlineQuery($inlineMessage, $res, $cacheTime = null, $isPersonal = null, $nextOffset = null, $switchPmText = null, $switchPmParameter = null){
        $data["inline_query_id"] = $inlineMessage;
        $data["results"] = $res;
        $data["cache_time"] = $cacheTime;
        $data["is_personal"] = $isPersonal;
        $data["next_offset"] = $nextOffset;
        $data["switch_pm_text"] = $switchPmText;
        $data["switch_pm_parameter"] = $switchPmParameter;
        return $this->Request("answerInlineQuery", $data);
    }
<<<<<<< Updated upstream:BotClass.php
}
=======
    
}
>>>>>>> Stashed changes:src/BotClass.php
