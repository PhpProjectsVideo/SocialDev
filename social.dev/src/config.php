<?php

if (file_exists(__DIR__ . '/config.local.php'))
{
    include __DIR__ . '/config.local.php';
}

if (!defined('GOOGLE_API_CLIENT_ID'))
{
    die("You must set your own GOOGLE_API_CLIENT_ID in " . __DIR__ . "/config.local.php");
}
if (!defined('GOOGLE_API_CLIENT_SECRET'))
{
    die("You must set your own GOOGLE_API_CLIENT_SECRET in " . __DIR__ . "/config.local.php");
}

if (!defined('PHEANSTALK_HOST'))
{
    define('PHEANSTALK_HOST', '127.0.0.1');
}

if (!defined('PHEANSTALK_PORT'))
{
    define('PHEANSTALK_PORT', \Pheanstalk\PheanstalkInterface::DEFAULT_PORT);
}

if (!defined('MYSQL_HOST'))
{
    define('MYSQL_HOST', '127.0.0.1');
}

if (!defined('MYSQL_DBNAME'))
{
    define('MYSQL_DBNAME', 'social');
}

if (!defined('MYSQL_USER'))
{
    define('MYSQL_USER', 'social');
}
if (!defined('MYSQL_PASS'))
{
    define('MYSQL_PASS', 'social123');
}