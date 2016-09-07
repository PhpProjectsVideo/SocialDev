<?php

namespace PhpProjects\SocialDev\Model\User;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PhpProjects\SocialDev\Search\UserIndexingService;

/**
 * Repository for User Entities
 */
class UserEntityRepository extends EntityRepository
{
    /**
     * Returns the user entities whose usernames match the $query string
     * @param string $query
     * @param UserIndexingService $indexingService
     * @return array
     */
    public function getUsersFromSearch(string $query, UserIndexingService $indexingService)
    {
        $userIds = $indexingService->getUserIds($query);
        
        $rows = $this->findBy([ 'user_id' => $userIds ]);
        
        $userIdMap = array_flip($userIds);
        
        // The userIdMap above is going to contain index order values, we can use these below to reorder the array
        // accordingly
        usort($rows, function (UserEntity $u1, UserEntity $u2) use ($userIdMap) {
            return $userIdMap[$u1->getUserId()] - $userIdMap[$u2->getUserId()];
        });
        
        return $rows;
    }

    /**
     * Returns user entities for users that have similar likes to the passed $userEntity
     * 
     * @param UserEntity $userEntity
     * @return array
     */
    public function getUsersWithSimilarLikes(UserEntity $userEntity)
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(UserEntity::class, 'su');

        $selectClause = $rsm->generateSelectClause();

        $sql = "
          SELECT {$selectClause}
          FROM user su 
          JOIN user_url su_url ON su_url.user_id = su.user_id 
          JOIN user_url u_url ON u_url.url_id = su_url.url_id
          WHERE u_url.user_id = :userId AND su.user_id <> :userId
        ";

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('userId', $userEntity->getUserId());

        return $query->getResult();
    }
}