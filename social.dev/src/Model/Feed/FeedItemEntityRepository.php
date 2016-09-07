<?php

namespace PhpProjects\SocialDev\Model\Feed;

use Doctrine\ORM\EntityRepository;
use PhpProjects\SocialDev\Model\User\UserEntity;

/**
 * Custom repository for feed item entities
 */
class FeedItemEntityRepository extends EntityRepository
{
    /**
     * returns the most recent feed items for a given user
     * @param UserEntity $user
     * @param int $since
     * @return array
     */
    public function getRecentFeedItemsForUser(UserEntity $user, int $since = 0) : array
    {
        $builder = $this->createQueryBuilder('feed');

        $andWhere = $builder->expr()->andX(
            $builder->expr()->eq('feed.user', ':user')
        );
        $builder->setParameter('user', $user);

        if ($since)
        {
            $andWhere->add(
                $builder->expr()->gt('feed.timestamp', ':timestamp')
            );
            $builder->setParameter('timestamp', $since);
        }
        $builder->where($andWhere);

        $builder->orderBy('feed.timestamp', 'DESC')
            ->setMaxResults(10);

        return $builder->getQuery()->getResult();
    }
}