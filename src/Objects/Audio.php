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

class Audio extends TelegramObject{
    protected $fields =  [
        'file_id'        => "",
        'file_unique_id' => "",
        'duration'       => null,
        'performer'      => "",
        'title'          => "",
        'mime_type'      => "",
        'file_size'      => null,
        'thumb'          => PhotoSize::class,
    ];
}


