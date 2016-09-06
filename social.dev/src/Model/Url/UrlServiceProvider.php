<?php

namespace PhpProjects\SocialDev\Model\Url;

use Doctrine\Common\EventArgs;
use Goutte\Client as GoutteClient;
use PhpProjects\SocialDev\Model\DomainEventManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * A silex provider for the urls.
 * 
 * @package PhpProjects\SocialDev\Model\Url
 */
class UrlServiceProvider implements ServiceProviderInterface
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
        $app['url.goutte.client'] = function () {
            return new GoutteClient();
        };

        $app['url.httpUrlService'] = function ($app) {
            return new HttpUrlService($app['url.goutte.client']);
        };

        $app['url.urlCrawlerWorker'] = function ($app) {
            return new UrlCrawlerWorker($app['url.httpUrlService'], $app['orm.em'], $app['pheanstalk.client']);
        };

        DomainEventManager::getInstance()->attachListener(DomainEventManager::EVENT_NEWURL, function ($arguments) use ($app) {
            /* @var $urlCrawlerWorker UrlCrawlerWorker */
            $urlCrawlerWorker = $app['url.urlCrawlerWorker'];

            /* @var $url \PhpProjects\SocialDev\Model\Url\UrlEntity */
            $url = $arguments['url'];

            $urlCrawlerWorker->queueUrl($url->getUrl());
        });
    }
}