<?php

namespace PhpProjects\SocialDev\Model\User;
use PhpProjects\SocialDev\Model\LikedUrl\LikedUrlEntity;
use PhpProjects\SocialDev\Model\Url\UrlEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * UserEntity
 */
class UserEntity implements UserInterface
{
    /**
     * @var integer
     */
    private $user_id;

    /**
     * @var string
     */
    private $googleUid;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $username;

    /**
     * @var array
     */
    private $urlIds;


    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set googleUid
     *
     * @param string $googleUid
     *
     * @return UserEntity
     */
    public function setGoogleUid($googleUid)
    {
        $this->googleUid = $googleUid;

        return $this;
    }

    /**
     * Get googleUid
     *
     * @return string
     */
    public function getGoogleUid()
    {
        return $this->googleUid;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return UserEntity
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return UserEntity
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return [ 'ROLE_USER' ];
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return '';
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }
    
    public function isFullyRegistered()
    {
        return !empty($this->getUsername());
    }

    /**
     * Marks a given url as liked by the current user
     * 
     * @param UrlEntity $url
     * @return LikedUrlEntity
     */
    public function likeUrl(UrlEntity $url) : LikedUrlEntity
    {
        return new LikedUrlEntity($this, $url, time());
    }

    /**
     * Validations to run for this entity
     * 
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity(array(
            'fields'  => 'username',
        )));

        $metadata->addPropertyConstraint('username', new Assert\Regex([
            'pattern' => '/^[0-9A-Za-z.-_]{2,}$/',
        ]));

        $metadata->addPropertyConstraint('username', new Assert\NotBlank());
    }

    public function __toString()
    {
        return $this->getUsername();
    }
}

