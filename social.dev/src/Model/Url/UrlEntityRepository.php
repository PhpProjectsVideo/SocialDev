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
}