<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use PhpProjects\SocialDev\Application\SocialApplication;
use Silex\Application;

// replace with file to your own project bootstrap
require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/config.php';

$app = new SocialApplication();
$app['debug'] = true;

// replace with mechanism to retrieve EntityManager in your app

$entityManager = $app['orm.em'];

return ConsoleRunner::createHelperSet($entityManager);
