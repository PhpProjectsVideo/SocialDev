<?php

namespace PhpProjects\SocialDev\Model;

/**
 * Handles domain events for our application
 */
class DomainEventManager
{
    // Listing of all of our events
    const EVENT_NEWURL = 'onNewUrl';
    const EVENT_NEWUSER = 'onNewUser';
    const EVENT_LIKEDURL = 'onLikedUrl';
    const EVENT_NEWCOMMENT = 'onNewComment';

    /**
     * @var DomainEventManager
     */
    private static $instance;

    /**
     * @var array
     */
    private $listeners = [];

    /**
     * @return DomainEventManager
     */
    public static function getInstance() : DomainEventManager
    {
        if (empty(self::$instance))
        {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    /**
     * Dispatches an event with the given args.
     *
     * @param string $event
     * @param array $args
     */
    public function dispatchEvent(string $event, array $args)
    {
        if (!empty($this->listeners[$event]))
        {
            foreach ($this->listeners[$event] as $callback)
            {
                call_user_func($callback, $args);
            }
        }
    }

    /**
     * Attaches a listener for a given event.
     *
     * @param string $event
     * @param $callback
     */
    public function attachListener(string $event, callable $callback)
    {
        $this->listeners[$event][] = $callback;
    }
}