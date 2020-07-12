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

namespace YehudaEi\TelegramBots;

use YehudaEi\TelegramBots\Objects;
use YehudaEi\TelegramBots\Exception\TelegramException;

class Parser{
    public $updateObject;

    public function __construct($update){
        if(gettype($update) == "string"){
            $update = json_decode($update, true);
            if($update == null){
                throw new TelegramException("Broken json update");
            }
        }

        if(gettype($update) == "array"){
            $this->updateObject = new Objects\Update();
            $this->currentObj = &$this->updateObject;
            $this->walker($update);
        }
        else{
            throw new TelegramException("Update type error");
        }
    }

    public function walker($arr){
        foreach($arr as $subarrK => $subarrV){
            if(gettype($subarrV) == "array"){
                $className = $subarrK;
                if($subarrK == "from") $className = "chat";

                $objName = __NAMESPACE__ ."\\Objects\\". ucfirst($className);
                $newObject = new $objName();

                $this->currentObj->$subarrK = $newObject; // creat new sub object to update key

                $tmp = $this->currentObj; // put pointer to previus level in tmp 

                $this->currentObj = $this->currentObj->$subarrK; // set current object to dipper
                $this->walker($subarrV); // walk over the sub object
                $this->currentObj = $tmp; // return the pointer
            }else{
                $this->currentObj->$subarrK = $subarrV;
            }
        }
    }
}