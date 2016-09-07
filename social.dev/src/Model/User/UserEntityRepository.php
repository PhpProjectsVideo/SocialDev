<?php

namespace PhpProjects\SocialDev\Model\User;

use Doctrine\ORM\EntityRepository;
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
}