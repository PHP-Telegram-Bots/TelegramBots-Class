<?php

if(!defined('BOT_CLASS')) throw new Exception ('the file '.__FILE__.'can\'t run alone');

class Helpers{
    private static $Bot;

    public static function Helpers($Bot = null){
        if($Bot != null)
            $this->Bot = $Bot;
        else{
            global $bot;
            if(gettype($bot) == "Bot")
                $this->Bot = $bot;
        }
    }

    public static function SetBot($Bot){
        if($Bot != null)
            $this->Bot = $Bot;
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
            if(gettype($this->Bot) == "Bot" && $Bot->GetDebug())
                $this->Bot->logging(curl_error($ch), "Curl: ".$url, false, false, $data);
            curl_close($ch);

            return false;
        }else{
            curl_close($ch);

            if(gettype($this->Bot) == "Bot" && $Bot->GetDebug())
                $this->Bot->logging($res, "Curl: ".$url, true, false, $data);
            return $res;
        }
    }

    // builde inline keyboard from array
	// argument must be: array(array( 'text' => 'data', 'text2' => 'data2'), /*row 2*/ array( 'text3' => 'data3', 'text4' => 'data4') )
	// by defult the button type is callback_data, you can also set button to url button by: array(array( 'link button' => array('url' => 'link'), 'callback button' => 'data'))
    public static function makeKeyboard($data){
        $keyRow = array(); 
        $ketCol = array();
        foreach($data as $row){
            foreach($row as $key => $value){
                if(gettype($value) == "array"){
                    $k = key($value);
                    $keyCol() = array(
                        'text' => $key, 
                        $k => $value($k)
                    );
                }
                else
                    $keyCol() = array(
                        'text' => $key, 
                        'callback_data' => $value
                    );
            }

            $keyRow() = $keyCol;
            $keyCol = array();
        }

        return json_encode(array('inline_keyboard' => $keyRow)); 
    }

    // parepare the text to avoid send errors
    public static function text_adjust($text){
        $text = var_export($text, true);

        if(mb_strlen($text) > 4500){
            $delDog = $this->postRequest("https://del.dog/documents", $text);
            $delDogKey = json_decode($delDog, true)["key"];
            $text = "message is too long. https://del.dog/".$delDogKey;
        }
        elseif($text == '')
            $text = "message empty";

        // parse_mode errors ...
        if(gettype($this->Bot) == "Bot"){
            if($this->Bot->GetParseMode() == "markdown"){
                $text = str_replace('_', "\_", $text);
            }
            elseif($this->Bot->GetParseMode() == "html"){
                $text = str_replace('<', "&lt;", $text);
                $text = str_replace('>', "&gt;", $text);
            }
        }

        return $text;
    }

    public static function getFullBotInfo($token = ""){
        $baseUrl = "https://api.telegram.org/bot{$token}/";

        $botInfo = $this->postRequest($baseUrl."getMe", null);
        $botWebhookInfo = $this->postRequest($baseUrl."getWebHookInfo", null);
        if($botInfo['ok'] == true){
            $botInfo['result']['webHookInfo'] = $botWebhookInfo;
            return $botInfo;
        }
        return false; 
    }

    // TODO Need urgent renovation !!!
    // if you too laze to open logs to chack what happend you can send to your self the errors
	// uncomment the call to this function in Request function
    public static function error_handler($respons, $TGapi = false){
        if(gettype($this->Bot) != "Bot"){
            global $bot;
            $this->Bot = $bot;
        }
        if(gettype($this->Bot) != "Bot")
            return;

        if($TGapi){
            $this->Bot->SetParseMode();
            
            if($respons['error_code'] == 429){
                $this->Bot->sendMessage(WEBMASTER_TG_ID, "flood, wait ".$respons['parameters']['retry_after']. " seconds");
                die();
            }

            elseif(strpos($respons['description'], "can't parse entities") !== 0){
                
            }
            elseif($respons['error_code'] == 403){
                $this->Bot->sendMessage(WEBMASTER_TG_ID, "forrbiden ".debug_backtrace()[2]["args"][0]);
            }
            foreach (debug_backtrace() as $key => $value) {
                if($key == 0)
                    continue;
                if($value['function'] == "error_heandler"){
                    $this->Bot->sendMessage(WEBMASTER_TG_ID, "loop error");
                    $this->Bot->sendMessage(WEBMASTER_TG_ID, $respons['description']);
                    die();
                }
            }

            global $update;
            $respons["call_by"] = debug_backtrace()[2]['function'];
            $respons["from_line"] = debug_backtrace()[2]['line'];
            $respons["error_type"] = "error output";

            if(!empty($update))
                $respons['update'] = $update;

            $this->Bot->sendMessage(WEBMASTER_TG_ID, $respons);
        }
    }
}
