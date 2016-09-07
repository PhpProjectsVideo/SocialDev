<?php

namespace PhpProjects\SocialDev\Search;

use Elasticsearch\ClientBuilder;
use PhpProjects\SocialDev\Model\DomainEventManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provider for our elastic search searvice
 */
class ElasticSearchServiceProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $app A container instance
     */
    public function register(Container $app)
    {
        $app['elasticSearch.client'] = function () {
            return ClientBuilder::create()
                ->build();
        };
        
        $app['elasticSearch.userIndexingService'] = function ($app) {
            return new UserIndexingService($app['elasticSearch.client']);
        };


        DomainEventManager::getInstance()->attachListener(DomainEventManager::EVENT_NEWUSER, function ($arguments) use ($app) {
            /* @var $userIndexingService UserIndexingService */
            $userIndexingService = $app['elasticSearch.userIndexingService'];

            /* @var $url \PhpProjects\SocialDev\Model\User\UserEntity */
            $user = $arguments['user'];

            $userIndexingService->indexUser($user);
        });
    }
}