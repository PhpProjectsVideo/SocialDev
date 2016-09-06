<?php

use PhpProjects\SocialDev\Application\SocialApplication;
use PhpProjects\SocialDev\Model\Url\UrlCrawlerWorker;

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/config.php';

$app = new SocialApplication(['debug' => true]);
$app->boot();

/* @var $urlCrawlerWorker UrlCrawlerWorker */
$urlCrawlerWorker = $app['url.urlCrawlerWorker'];

$urlCrawlerWorker->processJobs();