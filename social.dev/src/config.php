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
