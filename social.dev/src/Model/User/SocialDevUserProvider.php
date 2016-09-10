<?php

namespace PhpProjects\SocialDev\Model\User;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthToken;
use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthTokenInterface;
use Gigablah\Silex\OAuth\Security\User\Provider\OAuthUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SocialDevUserProvider implements OAuthUserProviderInterface, UserProviderInterface
{
    /**
     * @var EntityRepository
     */
    private $er;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em, EntityRepository $er)
    {
        $this->em = $em;
        $this->er = $er;
    }

    /**
     * Loads a user based on OAuth credentials.
     *
     * @param OAuthTokenInterface $token
     *
     * @return UserInterface
     */
    public function loadUserByOAuthCredentials(OAuthTokenInterface $token)
    {
        $user = $this->er->findOneBy(['googleUid' => $token->getUid()]);
        
        if (empty($user) && $token instanceof OAuthToken)
        {
            $user = new UserEntity();
            $user->setGoogleUid($token->getUid());
            $user->setEmail($token->getEmail());

            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $user = $this->er->findOneBy(['username' => $username]);
        
        if ($user)
        {
            return $user;
        }
        else
        {
            $e = new UsernameNotFoundException();
            $e->setUsername($username);
            throw $e;
        }
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if ($user instanceof  UserEntity)
        {
            $newUser = $this->er->findOneBy(['googleUid' => $user->getGoogleUid()]);
            
            if (!empty($newUser))
            {
                return $newUser;
            }
        }
        
        throw new UnsupportedUserException();
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class == ltrim(UserEntity::class, '\\');
    }
}