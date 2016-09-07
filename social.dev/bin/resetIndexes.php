<?php

use Doctrine\ORM\EntityRepository;
use PhpProjects\SocialDev\Application\SocialApplication;
use PhpProjects\SocialDev\Model\User\UserEntity;
use PhpProjects\SocialDev\Search\UserIndexingService;

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/config.php';

$app = new SocialApplication(['debug' => true]);
$app->boot();

/* @var $em \Doctrine\ORM\EntityManager */
$em = $app['orm.em'];

/* @var $userRepository EntityRepository */
$userRepository = $em->getRepository(UserEntity::class);

/* @var $userIndexingService UserIndexingService */
$userIndexingService = $app['elasticSearch.userIndexingService'];
$userIndexingService->reloadUserIndex($userRepository);

echo "User Index Rebuilt\n";