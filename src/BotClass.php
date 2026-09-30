<?php

if(!defined('BOT_CLASS')) throw new Exception ('the file '.__FILE__.' can\'t run alone');

class Bot{
    private $BotToken;
    private $BotId;
    private $BotName;
    private $BotUserName;
    private $DBName;
    private $Debug;
    private $ApiUrl;
    private $beautifi = true;
    private $update = null;
    private $webHook = null;
    private $webPagePreview = true;
    private $Notification = false;
    private $ParseMode = null;

    public function __construct($token, $Debug = false){
        $this->BotToken = $token;
        $this->Debug = $Debug;
        // a local Bot API server can be used by setting BOT['api_url']
        $this->ApiUrl = rtrim(BOT['api_url'] ?? "https://api.telegram.org", "/");

        // the bot info is cached, so getMe isn't called on every update
        $cacheFile = DATA_PATH."bot-".md5($token).".json";
        $botInfo = is_file($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : null;
        if(!isset($botInfo['id'], $botInfo['username'])){
            $res = $this->Request("getMe");
            if(empty($res['ok']) || empty($res['result']['is_bot']))
                throw new Exception('can\'t get the bot info, check the bot token');
            $botInfo = $res['result'];
            @file_put_contents($cacheFile, json_encode($botInfo), LOCK_EX);
        }

        $this->BotId = $botInfo['id'];
        $this->BotName = $botInfo['first_name'];
        $this->BotUserName = $botInfo['username'];
        $this->DBName = DATA_PATH.$this->BotId." - ".$this->BotUserName.'.sqlite';

        //Update WebHook (only when the webhook settings were changed)
        if(isset(BOT['webHookUrl'])){
            $webHookConf = md5(json_encode(array(BOT['webHookUrl'], BOT['allowed_updates'] ?? null)));
            $webHookFile = DATA_PATH."bot-".md5($token).".webhook";
            if(!is_file($webHookFile) || file_get_contents($webHookFile) !== $webHookConf){
                if($this->SyncWebHook())
                    @file_put_contents($webHookFile, $webHookConf, LOCK_EX);
            }
        }
    }

    private function SyncWebHook(){
        $info = $this->Request("getWebhookInfo");
        if(empty($info['ok']))
            return false;

        $allowedUpdates = BOT['allowed_updates'] ?? null;
        $currentAllowed = $info['result']['allowed_updates'] ?? array();
        if(is_array($allowedUpdates)){
            sort($allowedUpdates);
            sort($currentAllowed);
        }

        if(($info['result']['url'] ?? null) == BOT['webHookUrl'] && ($allowedUpdates === null || $allowedUpdates == $currentAllowed))
            return true;

        $res = $this->Request("setWebhook", array('url' => BOT['webHookUrl'], 'allowed_updates' => BOT['allowed_updates'] ?? null));
        return !empty($res['ok']);
    }

    public function SaveID($id, $type){
        try{
            $DBConn = new SQLite3($this->DBName, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
            $DBConn->enableExceptions(true);
            $DBConn->exec('CREATE TABLE IF NOT EXISTS "users" (
                        "user_id" INT(11) PRIMARY KEY,
                        "type" VARCHAR,
                        "time" TIMESTAMP
                    )');
            $stmt = $DBConn->prepare('INSERT OR IGNORE INTO "users" ("user_id", "type", "time") VALUES (:id, :type, :time)');
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $stmt->bindValue(':type', $type, SQLITE3_TEXT);
            $stmt->bindValue(':time', time(), SQLITE3_INTEGER);
            $stmt->execute();
            $DBConn->close();
        }
        catch(Exception $e){
            if($this->Debug)
                $this->logging($e->getMessage(), "SaveID", false);
        }
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
        return $this->Request('setWebhook', array("url" => $val, "allowed_updates" => BOT['allowed_updates'] ?? null))['ok'];
    }
    public function DeleteWebHook(){
        $this->webHook = null;
        return $this->Request('deleteWebhook')['ok'];
    }
    // old name, kept for backward compatibility
    public function DetWebHook(){
        return $this->DeleteWebHook();
    }
        //Updates - BETA!
    public function SetUpdate($update){
        $this->update = $update;
        if($this->Debug)
            $this->logging($update, false, "Update input:", true);
    }
    public function GetUpdate(){
        return $this->update;
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
    private function Request($method, $data = array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->ApiUrl."/bot".$this->BotToken."/".$method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, self::PrepareFields($data));

        $res = curl_exec($ch);
        $error = curl_error($ch);
        if($error){
            if($this->Debug)
                $this->logging($error, "Curl: ".$method, false, false, $data);
            return array('ok' => false, 'description' => 'Curl error: '.$error);
        }

        $res = json_decode($res, true);
        if(!is_array($res))
            $res = array('ok' => false, 'description' => 'Invalid response from the Telegram API');

        // you can send to your self the error details
        if(!$res['ok'] && $this->Debug){
            Helpers::error_handler($res, true);
        }

        if($this->Debug)
            $this->logging($res, "Curl: ".$method, true, true, $data);
        return $res;
    }

    // drop empty (null) fields and encode booleans and arrays the way the Telegram API expects them
    private static function PrepareFields($data){
        $fields = array();
        foreach($data as $key => $value){
            if($value === null)
                continue;
            if(is_bool($value))
                $value = $value ? "true" : "false";
            elseif(is_array($value))
                $value = json_encode($value);
            $fields[$key] = $value;
        }
        return $fields;
    }

    //Logging
    public function logging($data, $method = null, $success = false, $array = false, $helpArgs = null){
        $tmp = ($this->beautifi ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE;
        if(!$array || !is_array($data))
            $data = array("data" => $data);
        
        $data['added_by_log']['helpArgs'] = $helpArgs;
        $data['added_by_log']['date'] = date(DATE_RFC850);
        $data['added_by_log']['botUserName'] = $this->BotUserName;
        $data['added_by_log']['success'] = ($success ? "Success!" : "Error");
        $data['added_by_log']['method'] = $method;
        
        $data = json_encode($data, $tmp);
        // the log is saved in DATA_PATH and not in the web folder, it contains the users data
        file_put_contents(DATA_PATH.($this->BotUserName ?: "bot")." - log.log", $data.",\n", FILE_APPEND | LOCK_EX);
    }
    
    //Methods
    public function sendMessage($id, $text, $replyMarkup = null, $replyMessage = null){
        $data["chat_id"] = $id;
        $data["text"] = Helpers::text_adjust($text);
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
        $data["caption"] = Helpers::text_adjust($caption);
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
        $data["caption"] = Helpers::text_adjust($caption);
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
        $data["caption"] = Helpers::text_adjust($caption);
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
        if(!in_array($action, ["typing", "upload_photo", "record_video", "upload_video", "record_voice", "upload_voice", "record_audio", "upload_audio", "upload_document", "choose_sticker", "find_location", "record_video_note", "upload_video_note"]))
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
    public function banChatMember($id, $uId){
        $data["chat_id"] = $id;
        $data["user_id"] = $uId;
        return $this->Request("banChatMember", $data);
    }
    // kickChatMember was renamed by Telegram to banChatMember
    public function kickChatMember($id, $uId){
        return $this->banChatMember($id, $uId);
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
    public function getChatMemberCount($id){
        $data["chat_id"] = $id;
        return $this->Request("getChatMemberCount", $data);
    }
    // getChatMembersCount was renamed by Telegram to getChatMemberCount
    public function getChatMembersCount($id){
        return $this->getChatMemberCount($id);
    }
    public function getChatMember($id, $uId){
        $data["chat_id"] = $id;
        $data["user_id"] = $uId;
        return $this->Request("getChatMember", $data);
    }
    public function answerCallbackQuery($callback, $text = null, $alert = false){
        $data["callback_query_id"] = $callback;
        $data["text"] = Helpers::text_adjust($text);
        $data["show_alert"] = $alert;
        return $this->Request("answerCallbackQuery", $data);
    }
    public function editMessageText($id, $messageId, $inlineMessage, $text, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["message_id"] = $messageId;
        $data["inline_message_id"] = $inlineMessage;
        $data["text"] = Helpers::text_adjust($text);
        $data["parse_mode"] = $this->ParseMode;
        $data["disable_web_page_preview"] = $this->webPagePreview;
        $data["reply_markup"] = $replyMarkup;
        return $this->Request("editMessageText", $data);
    }
    public function editMessageCaption($id = null, $messageId = null, $inlineMessage = null, $caption = null, $replyMarkup = null){
        $data["chat_id"] = $id;
        $data["message_id"] = $messageId;
        $data["inline_message_id"] = $inlineMessage;
        $data["caption"] = Helpers::text_adjust($caption);
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
}
