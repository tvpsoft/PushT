<?php
/**
 * User: mberberoglu
 * Date: 02/08/14
 * Time: 23:45
 */

namespace PushT\Bundle\MainBundle\Cache;

use PushT\Bundle\MainBundle\Document\Push;

class PushCache extends BaseCache
{
    public function __construct($container)
    {
        parent::__construct($container, 'push');
    }

    /**
     * @param $push Push
     */
    public function setPush($push)
    {
        $this->set($push->getId(), $push);
    }

    /**
     * @param $pushId
     * @return Push
     */
    public function getPush($pushId)
    {
        $push = $this->get($pushId);
        if (!$push) {
            $push = $this->mongodb->getRepository('MainBundle:Push')->find($pushId);
            if ($push) {
                $this->setPush($push);
            }
        }

        return $push;
    }
}
