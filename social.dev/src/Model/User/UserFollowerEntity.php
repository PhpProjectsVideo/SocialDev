<?php

namespace PhpProjects\SocialDev\Model\User;

/**
 * Models the relationship between a follower and the user being followed (or the followee)
 */
class UserFollowerEntity
{
    /**
     * @var UserEntity
     */
    private $follower;

    /**
     * @var UserEntity
     */
    private $followee;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * UserFollowerEntity constructor.
     * @param UserEntity $follower
     * @param UserEntity $followee
     * @param int $timestamp
     */
    public function __construct(UserEntity $follower, UserEntity $followee, int $timestamp)
    {
        $this->follower = $follower;
        $this->followee = $followee;
        $this->timestamp = $timestamp;
    }

    /**
     * Unwraps the users being followed from a list of user followers
     * @param array $userFollowerEntities
     * @return array
     */
    public static function unwrapFollowedUsers(array $userFollowerEntities) : array
    {
        $users = [];
        foreach ($userFollowerEntities as $userFollower)
        {
            if ($userFollower instanceof self)
            {
                $users[$userFollower->followee->getUserId()] = $userFollower->followee;
            }
            else
            {
                throw new \InvalidArgumentException("All objects must be UserFollowerEntities");
            }
        }
        
        return $users;
    }

    /**
     * @return UserEntity
     */
    public function getFollower() : UserEntity
    {
        return $this->follower;
    }

    /**
     * @return UserEntity
     */
    public function getFollowee() : UserEntity
    {
        return $this->followee;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}