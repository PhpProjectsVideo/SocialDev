<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use PhpProjects\SocialDev\Application\SocialApplication;

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/config.php';

$app = new SocialApplication(['debug' => true]);

$entityManager = $app['orm.em'];

return ConsoleRunner::createHelperSet($entityManager);
