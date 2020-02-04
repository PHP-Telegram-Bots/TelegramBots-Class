<?php

if(!defined('BOT_CLASS')) throw new Exception ('the file '.__FILE__.'can\'t run alone');

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
                
            //Update WebHook
            $res = $this->Request("getwebhookinfo");
            if($res['result']['url'] != BOT['webHookUrl'])
                if(isset(BOT['allowed_updates']))
                    $this->Request("setwebhook", array('url' => BOT['webHookUrl'], "allowed_updates" => BOT['allowed_updates']));
                else
                    $this->Request("setwebhook", array('url' => BOT['webHookUrl']));
            return true;
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

            // in TODO
            // you can send to your self the error details
			if(!$res['ok']){
                Helpers::error_handler($res);
            }
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
        $data["text"] = Helpers::text_adjust($text);
        $data["show_alert"] = $alert;
        return $this->Request("answerCallbackQuery", $data);
    }
    public function editMessageText($id = null, $messageId = null, $inlineMessage = null, $text, $replyMarkup = null){
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
