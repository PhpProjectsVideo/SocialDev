<?php

namespace PhpProjects\SocialDev\Search;

use Doctrine\ORM\EntityRepository;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use PhpProjects\SocialDev\Model\User\UserEntity;

/**
 * Handles interactions with our user index
 */
class UserIndexingService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * UserIndexingService constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Empties out the user index and rebuilds it from scratch with all users in the elastic search service.
     *
     * This is not practical once the user table gets large, you would need to then load the data in chunks, but
     * that is easily doable using multiple range queries on the user repo.
     *
     * @param EntityRepository $userRepository
     */
    public function reloadUserIndex(EntityRepository $userRepository)
    {
        try
        {
            $this->client->indices()->delete(['index' => 'users']);
        }
        catch (Missing404Exception $e)
        {
            // we can ignore
        }

        $this->client->indices()->create([
            'index' => 'users',
            'body' => [
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            'trigrams_filter' => [
                                'type' => 'ngram',
                                'min_gram' => 3,
                                'max_gram' => 3,
                            ],
                        ],
                        'analyzer' => [
                            'trigrams' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'trigrams_filter'
                                ],
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    'user' => [
                        'properties' => [
                            'username' => [
                                'type' => 'string',
                                'analyzer' => 'trigrams'
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $users = $userRepository->findAll();

        /* @var $user UserEntity */
        foreach ($users as $user)
        {
            $this->indexUser($user);
        }
    }

    /**
     * Returns the user ids of the user names matching $query
     *
     * @param string $query
     * @return array
     */
    public function getUserIds(string $query)
    {
        $results = $this->client->search([
            'index' => 'users',
            'type' => 'user',
            'body' => [
                'query' => [
                    'match' => [
                        'username' => [
                            'query' => $query
                        ],
                    ],
                ],
            ],
        ]);
        
        $userIds = [];
        if (isset($results['hits']['hits']))
        {
            foreach ($results['hits']['hits'] as $hit)
            {
                $userIds[] = $hit['_id'];
            }
        }
        
        return $userIds;
    }

    /**
     * Adds a new user to our index
     *
     * @param UserEntity $user
     */
    public function indexUser(UserEntity $user)
    {
        $docParams = [
            'index' => 'users',
            'type' => 'user',
            'id' => $user->getUserId(),
            'body' => [
                'username' => $user->getUsername(),
            ],
        ];

        $this->client->index($docParams);
    }
}