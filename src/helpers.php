<?php

if(!defined('BOT_CLASS')) throw new Exception ('the file '.__FILE__.' can\'t run alone');

class Helpers{
    private static $Bot;

    public function Helpers($Bot = null){
        if($Bot != null)
            self::$Bot = $Bot;
        else{
            global $bot;
            if(gettype($bot) == "object")
                self::$Bot = $bot;
        }
    }

    public static function SetBot($Bot){
        if($Bot != null)
            self::$Bot = $Bot;
    }

    public static function entToRealTxt($text, $replace, $offset, $length, $delay){
        $text = substr_replace($text, $replace, $offset + $delay, 0);
        return substr_replace($text, $replace, $offset + $length + strlen($replace) + $delay, 0);
    }

    public static function postRequest($url, $data = array()){
        $ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch ,CURLOPT_POSTFIELDS, $data);
       
        $res = curl_exec($ch);
        if(curl_error($ch)){
            if(gettype(self::$Bot) == "object" && self::$Bot->GetDebug())
                self::$Bot->logging(curl_error($ch), "Curl: ".$url, false, false, $data);
            curl_close($ch);

            return false;
        }else{
            curl_close($ch);

            if(gettype(self::$Bot) == "object" && self::$Bot->GetDebug())
                self::$Bot->logging($res, "Curl: ".$url, true, false, $data);
            return $res;
        }
    }

    // builde inline keyboard from array
	// argument is array(array( 'text' => 'data', 'text2' => 'data2'), /*row 2*/ array( 'text3' => 'data3', 'text4' => 'data4') )
	// by defult the button type is callback_data, you can also set button to url button by array(array( 'text_button' => array('url' => 'link'), 'callback button' => 'data'))
    public static function makeKeyboard($data){
        $keyCol = array(); 
        $keyRow = array();
        foreach($data as $row){
            foreach($row as $key => $value){
                if(gettype($value) == "array"){
                    $k = key($value);
                    $keyCol[] = array(
                        'text' => $key, 
                        $k => $value[$k]
                    );
                }
                else
                    $keyCol[] = array(
                        'text' => $key, 
                        'callback_data' => $value
                    );
            }

            $keyRow[] = $keyCol;
            $keyCol = array();
        }

        return json_encode(array('inline_keyboard' => $keyRow)); 
    }

    // parepare the text to avoid send errors
    public static function text_adjust($text){
        if(gettype($text) != "string")
        	$text = var_export($text, true);

        if(mb_strlen($text) > 4090){
            $delDog = self::postRequest("https://del.dog/documents", $text);
            $delDogKey = json_decode($delDog, true)["key"];
            $text = "message is too long. https://del.dog/".$delDogKey;
        }
        elseif($text == '')
            $text = "message empty";

        // parse_mode errors ...
        if(gettype(self::$Bot) == "object"){
            if(self::$Bot->GetParseMode() == "markdown"){
                $text = str_replace('_', "\_", $text);
            }
            elseif(self::$Bot->GetParseMode() == "html"){
                $text = str_replace('<', "&lt;", $text);
                $text = str_replace('>', "&gt;", $text);
            }
        }

        return $text;
    }

    public static function getFullBotInfo($token = ""){
        $baseUrl = "https://api.telegram.org/bot{$token}/";

        $botInfo = json_decode(self::postRequest($baseUrl."getMe", null), true);
        $botWebhookInfo = json_decode(self::postRequest($baseUrl."getWebHookInfo", null), true);
        if($botInfo['ok'] == true){
            $botInfo['result']['webHookInfo'] = $botWebhookInfo;
            return $botInfo;
        }
        return false; 
    }

    // TODO Need urgent renovation !!!
    // if you too laze to open logs to chack what happend you can send to your self the errors
	// uncomment the call to this function in Request function
    public static function error_handler($errorData, $TGapi = false){
        if(gettype(self::$Bot) != "object"){
            global $bot;
            self::$Bot = $bot;
        }
        if(gettype(self::$Bot) != "object")
            return;

        if($TGapi === true){
            self::$Bot->SetParseMode("");
            
            foreach (debug_backtrace() as $key => $value) {
                if($key == 0) continue;
                else if($value['function'] == "error_handler"){
                    self::$Bot->sendMessage(WEBMASTER_TG_ID, "loop error");
                    self::$Bot->sendMessage(WEBMASTER_TG_ID, $errorData['description']);
                    die();
                }
            }

            $res["calledByFunc"] = debug_backtrace()[2]['function'];
            $res["fileLocation"] = debug_backtrace()[2]['file'];
            $res["lineInFile"] = debug_backtrace()[2]['line'];
            $res["errorType"] = "Telegram api output error";

            $res['telegramRespons'] = $errorData;

            global $update;
            if(!empty($update))
                $res['telegramUpdate'] = $update;
            
            if(gettype(self::$Bot) == "object" && self::$Bot->GetDebug())
                self::$Bot->logging($res, "errorHandler: ", false, true);
            
            self::$Bot->sendMessage(WEBMASTER_TG_ID, $res);
        }
        else{
            $r["message"] = "You have a exception in you bot code:";
            $r["file"] = $errorData->getFile();
            $r["error"] = $errorData->getMessage();
            $r["line"] = $errorData->getLine();
            self::$Bot->sendMessage(WEBMASTER_TG_ID, $r);
        }
    }
    public static function error_handler_php($ErrorId, $ErrorMes, $ErrorFile, $ErrorLine, $ErrorFatherFiles){
        // Original - https://gist.github.com/YehudaEi/c0ae248fae39020ab4aabc1047984902
        
        if(gettype(self::$Bot) != "object"){
            global $bot;
            self::$Bot = $bot;
        }
        if(gettype(self::$Bot) != "object")
            return;
        
        $data = array();
        $data["message"] = "You have a error in you bot code:";
        $data['errorType'] = "phpError";
        $data['errorCode'] = $ErrorId;
        $data['errorMes'] = $ErrorMes;
        $data['errorFile'] = $ErrorFile;
        $data['errorLineInFile'] = $ErrorLine;
        $data['fatherFiles'] = $ErrorFatherFiles;
        
        self::$Bot->sendMessage(WEBMASTER_TG_ID, $data);
        return NULL;
    }
}
