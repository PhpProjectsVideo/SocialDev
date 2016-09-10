<?php

namespace PhpProjects\SocialDev\Model\Url;

use Goutte\Client as GoutteClient;
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
    }
}