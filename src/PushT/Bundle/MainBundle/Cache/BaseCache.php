<?php
/**
 * User: mberberoglu
 * Date: 02/08/14
 * Time: 23:45
 */

namespace PushT\Bundle\MainBundle\Cache;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\DependencyInjection\Container;

class BaseCache
{
    /** @var \Redis $redis */
    private $redis = null;

    /** @var  ManagerRegistry */
    protected $mongodb;

    private $prefix = 'pusht_';

    /**
     * @param $container Container
     * @param $predix
     */
    public function __construct($container, $predix)
    {
        $this->mongodb = $container->get('doctrine_mongodb');
        $this->redis = $container->get('snc_redis.default');
        $this->prefix .= $predix.'_';
    }

    public function del($key)
    {
        $this->redis->del($this->prefix.$key);
    }

    public function keys($pattern)
    {
        return $this->redis->keys($this->prefix.$pattern);
    }

    public function set($key, $value)
    {
        $data = base64_encode(gzcompress(igbinary_serialize($value)));
        $this->redis->set($this->prefix.$key, $data);
    }

    public function get($key)
    {
        $data = $this->redis->get($this->prefix.$key);
        if ($data) {
            return igbinary_unserialize(gzuncompress(base64_decode($data)));
        } else {
            return null;
        }
    }

    public function expire($key, $time)
    {
        $this->redis->expire($this->prefix.$key, $time);
    }
}