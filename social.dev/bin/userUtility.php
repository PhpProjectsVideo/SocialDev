<?php

use Doctrine\ORM\EntityRepository;
use PhpProjects\SocialDev\Application\SocialApplication;
use PhpProjects\SocialDev\Model\User\UserEntity;

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/config.php';

$app = new SocialApplication(['debug' => true]);
$app->boot();

$operation = $_SERVER['argv'][1] ?? false;

/* @var $em \Doctrine\ORM\EntityManager */
$em = $app['orm.em'];

/* @var $userRepository EntityRepository */
$userRepository = $em->getRepository(UserEntity::class);

switch ($operation)
{
    case 'create-user':
        if ($_SERVER['argc'] < 4)
        {
            echo "Need more parameters\n";
            break;
        }

        $userEntity = $userRepository->findOneBy(['username' => $_SERVER['argv'][2]]);
        if (!empty($userEntity))
        {
            echo "User {$_SERVER['argv'][2]} already exists\n";
            break;
        }

        $userEntity = $userRepository->findOneBy(['email' => $_SERVER['argv'][3]]);
        if (!empty($userEntity))
        {
            echo "User {$userEntity->getUsername()} already has email {$_SERVER['argv'][3]}\n";
            break;
        }

        $userEntity = new UserEntity();
        $userEntity->setUsername($_SERVER['argv'][2]);
        $userEntity->setEmail($_SERVER['argv'][3]);

        $em->persist($userEntity);
        $em->flush();
        echo "User Created!\n";
        exit(0);
}

echo "Usages:\n"
    . "  Create a User -\n"
    . "    php bin/userUtility.php create-user <username> <email address>\n"
;
exit(1);
