<?php

namespace PhpProjects\SocialDev\Application;

use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Gigablah\Silex\OAuth\OAuthServiceProvider;
use PhpProjects\SocialDev\Model\Url\UrlServiceProvider;
use PhpProjects\SocialDev\Model\User\SocialDevUserProvider;
use PhpProjects\SocialDev\Model\User\UserEntity;
use Saxulum\DoctrineOrmManagerRegistry\Provider\DoctrineOrmManagerRegistryProvider;
use Silex\Application;
use Silex\Provider\CsrfServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

/**
 * Class SocialApplication
 *
 * Custom application class to handl provider registration, traits, and some other cool stuff.
 */
class SocialApplication extends Application
{
    use Application\TwigTrait;
    use Application\FormTrait;
    use Application\UrlGeneratorTrait;

    /**
     * SocialApplication constructor.
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->register(new DoctrineServiceProvider(), [
            'db.options' => [
                'driver' => 'pdo_mysql',
                'dbname' => 'social',
                'user' => 'social',
                'password' => 'social123',
            ],
        ]);

        $this->register(new DoctrineOrmServiceProvider, [
            'orm.proxies_dir' => __DIR__ . '/../Model/proxies',
            'orm.em.options' => [
                'mappings' => [
                    [
                        'type' => 'simple_yml',
                        'namespace' => 'PhpProjects\SocialDev\Model',
                        'path' => __DIR__.'/../Model/mappings',
                    ],
                ],
            ],
        ]);

        $this->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__ . '/../../views',
            'twig.form.templates' => [ 'bootstrap_3_layout.html.twig' ],
        ]);
        
        $this->register(new SessionServiceProvider(), [
            'session.db_options' => [
                'db_table'        => 'session',
                'db_id_col'       => 'session_id',
                'db_id_col'       => 'session_id',
                'db_id_col'       => 'session_id',
                'db_data_col'     => 'session_value',
                'db_lifetime_col' => 'session_lifetime',
                'db_time_col'     => 'session_time',
                'lock_mode'       => PdoSessionHandler::LOCK_ADVISORY,
            ],
            'session.storage.handler' => function () {
                return new PdoSessionHandler(
                    $this['db']->getWrappedConnection(),
                    $this['session.db_options']
                );
            },
        ]);

        $this->register(new CsrfServiceProvider());
        
        $this['form.csrf_provider'] = $this['csrf.token_manager'];

        $this->register(new OAuthServiceProvider(), [
            'oauth.services' => [
                'Google' => [
                    'key' => GOOGLE_API_CLIENT_ID,
                    'secret' => GOOGLE_API_CLIENT_SECRET,
                    'scope' => [
                        'https://www.googleapis.com/auth/userinfo.email',
                        'https://www.googleapis.com/auth/userinfo.profile'
                    ],
                    'user_endpoint' => 'https://www.googleapis.com/oauth2/v1/userinfo'
                ],
            ]
        ]);

        $this->register(new SecurityServiceProvider(), [
            'security.firewalls' => [
                'default' => [
                    'pattern' => '^/',
                    'anonymous' => true,
                    'oauth' => [
                        'login_path' => '/auth/{service}',
                        'callback_path' => '/auth/{service}/callback',
                        'check_path' => '/auth/{service}/check',
                        'failure_path' => '/',
                        'with_csrf' => true
                    ],
                    'logout' => [
                        'logout_path' => '/logout',
                        'with_csrf' => true
                    ],
                    'users' => function () {

                        return new SocialDevUserProvider($this['orm.em'], $this['orm.em']->getRepository(UserEntity::class));
                    }
                ]
            ],
            'security.access_rules' => [
                ['^/auth', 'ROLE_USER'],
                ['^/register', 'ROLE_USER'],
                ['^/user/', 'ROLE_USER'],
            ]
        ]);

        $this->register(new FormServiceProvider());

        $this->register(new TranslationServiceProvider(), array(
            'translator.domains' => array(),
            'locale' => 'en'
        ));

        $this->register(new ValidatorServiceProvider());

        $this->register(new DoctrineOrmManagerRegistryProvider());

        $this->register(new UrlServiceProvider());
    }

}