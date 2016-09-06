<?php

namespace PhpProjects\SocialDev\Model\Url;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for url entities
 */
class UrlEntityRepository extends EntityRepository
{
    /**
     * Returns the url by full url.
     * 
     * Translates the url into a url id so we can perform the lookup by PK.
     * 
     * @return null|UrlEntity
     */
    public function getOneByUrl(string $url)
    {
        $urlId = UrlEntity::generateUrlHash($url);
        
        return $this->find($urlId);
    }

    /**
     * Gets the most recent urls since the given timestamp
     * 
     * @param int $timestamp
     * @return array
     */
    public function getMostRecentUrlsSince($timestamp = 0)
    {
        $builder = $this->createQueryBuilder('url');

        $andWhere = $builder->expr()->andX(
            $builder->expr()->eq('url.status', UrlEntity::STATUS_DATA_LOADED)
        );

        if ($timestamp)
        {
            $andWhere->add(
                $builder->expr()->gt('url.timestamp', ':timestamp')
            );
            $builder->setParameter('timestamp', $timestamp);
        }
        $builder->where($andWhere);

        $builder->orderBy('url.timestamp', 'DESC')
            ->setMaxResults(10);

        return $builder->getQuery()->getResult();
    }
}