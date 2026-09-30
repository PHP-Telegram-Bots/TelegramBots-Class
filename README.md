# Telegram-Bots Class

**the best class in php to telegram-bots!!!**

### install
```bash
$ composer require yehudae/telegram-bots-class
```

use:
```php
define('BOT', array(
    "token" => "<TOKEN>",
    "webHookUrl" => "https://telegram.org/Bot.php",
    "allowed_updates" => array ("message", "edited_message"),
    "debug" => false,
    // optional:
    // "timezone" => "Asia/Jerusalem",
    // "api_url" => "https://api.telegram.org", // for a local Bot API server
    ));

// the folder for the bot data (sqlite db, logs, cache), must be writable and outside the web folder
define('DATA_PATH', '/var/telegram-bots/BotsDATA/');

require_once("vendor/yehudae/telegram-bots-class/src/autoload.php");
```

Requires PHP 7.0 or newer (PHP 8 supported).

Contact in telegram: [@YehudaEisenbergBot](http://t.me/YehudaEisenbergBot "@YehudaEisenbergBot")
