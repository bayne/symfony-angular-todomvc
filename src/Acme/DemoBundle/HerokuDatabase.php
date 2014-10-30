<?php

namespace Acme\DemoBundle;

use Composer\Script\Event;

class HerokuDatabase
{
    public static function populateEnvironment(Event $event)
    {
        $url = getenv("DATABASE_URL");

        if ($url) {
            $url = parse_url($url);
            putenv("DATABASE_HOST={$url['host']}");
            putenv("DATABASE_USER={$url['user']}");
            putenv("DATABASE_PASSWORD={$url['pass']}");
            $db = substr($url['path'],1);
            putenv("DATABASE_NAME={$db}");
        }

        $io = $event->getIO();

        $io->write("DATABASE_URL=".getenv("DATABASE_URL"));
    }
}
