<?php

namespace PhpProjects\SocialDev\Model\LikedUrl;

use Doctrine\ORM\EntityRepository;
use PhpProjects\SocialDev\Model\User\UserEntity;

/**
 * Custom repository for liked urls
 */
class LikedUrlEntityRepository extends EntityRepository
{
    /**
     * Returns the most recent liked urls for a given user
     * 
     * @param UserEntity $user
     * @return array
     */
    public function getRecentLikedUrls(UserEntity $user)
    {
        return $this->findBy(['user' => $user], ['timestamp' => 'DESC'], 10);
    }
}