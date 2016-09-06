<?php

namespace PhpProjects\SocialDev\Pheanstalk;

use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class PheanstalkProvider implements ServiceProviderInterface
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
        $app['pheanstalk.config_default'] = [
            'host' => '127.0.0.1',
            'port' => PheanstalkInterface::DEFAULT_PORT,
            'timeout' => null,
            'persistent' => false,
        ];

        $app['pheanstalk.config_resolver'] = function () use ($app) {
            return array_merge($app['pheanstalk.config_default'], $app['pheanstalk.config'] ?? []);
        };

        $app['pheanstalk.client'] = function () use ($app) {
            return new Pheanstalk(
                $app['pheanstalk.config_resolver']['host'],
                $app['pheanstalk.config_resolver']['port'],
                $app['pheanstalk.config_resolver']['timeout'],
                $app['pheanstalk.config_resolver']['persistent']
            );
        };
    }
}