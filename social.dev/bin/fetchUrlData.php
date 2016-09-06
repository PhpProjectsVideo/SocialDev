<?php

use PhpProjects\SocialDev\Application\SocialApplication;
use PhpProjects\SocialDev\Model\Url\UrlEntity;

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/config.php';

$app = new SocialApplication(['debug' => true]);
$app->boot();

$url = $_SERVER['argv'][1];

if (empty($url))
{
    die('You need to provide a url' . PHP_EOL);
}

/* @var $em \Doctrine\ORM\EntityManager */
$em = $app['orm.em'];

/* @var $urlRepository \PhpProjects\SocialDev\Model\Url\UrlEntityRepository */
$urlRepository = $em->getRepository(UrlEntity::class);
$urlEntity = $urlRepository->getOneByUrl($url);
if (empty($urlEntity))
{
    $urlEntity = new UrlEntity($url);
    $em->persist($urlEntity);
}

$em->flush();

echo "Fetched {$url}: \"{$urlEntity->getTitle()}\"\n";

