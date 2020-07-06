<?php

/**
 * This file is part of the TelegramBots-Class package.
 *
 * (C) Yehuda Eisenberg
 * This file can be used under the MIT license.
 * 
 * @link https://github.com/PHP-Telegram-Bots/TelegramBots-Class
 * @package TelegramBots-Class
 * 
 */

namespace YehudaEi\TelegramBots\Objects;

use YehudaEi\TelegramBots\Exception\TelegramException;

class TelegramObject{
    protected $fields = [];

    public function __get($fieldName){
        if(isset($this->fields[$fieldName])){
            return $this->fields[$fieldName];
        }
        else{
            throw new TelegramException("Field '$fieldName' not defined in class " . __CLASS__);
        }
    }

    public function __set($fieldName, $value){
        if(isset($this->fields[$fieldName])){
            $this->fields[$fieldName] = $value;
        }
        else{
            throw new TelegramException("Field '$fieldName' not defined in class " . __CLASS__);
        }
    }

    public function __toString(){
        $res = get_class($this) . " {\n";
        foreach($this->fields as $fieldName => $value){
            $res .= "\t\"" . $fieldName . "\" => \"" . $value . "\"\n";
        }
        $res .= "}";
        return $res;
    }

    public function __toJson(){
        $res = array();
        $res['type'] = get_class($this);
        $res['filed'] = $this->fields;

        return $res;
    }
}
