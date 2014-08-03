<?php
/**
 * User: mberberoglu
 * Date: 02/08/14
 * Time: 23:45
 */

namespace PushT\Bundle\MainBundle\Cache;

use PushT\Bundle\MainBundle\Document\User;

class UserCache extends BaseCache
{
    public function __construct($container)
    {
        parent::__construct($container, 'user');
    }


    /**
     * @param $user User
     */
    public function setUser($user)
    {
        $this->set($user->getId(), $user->toArray());
    }

    /**
     * @param $userId
     * @return User
     */
    public function getUser($userId)
    {
        $user = $this->get($userId);
        if (!$user) {
            $user = $this->mongodb->getRepository('MainBundle:User')->find($userId);
            if ($user) {
                $this->setUser($user);
            }
            $user = $user->toArray();
        }
        return $user;
    }
}