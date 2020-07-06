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

class VideoNote extends TelegramObject{
    protected $fields =  [
        'file_id'        => "",
        'file_unique_id' => "",
        'length'         => -1,
        'duration'       => -1,
        'thumb'          => PhotoSize::class,
        'file_size'      => -1,
    ];
}



