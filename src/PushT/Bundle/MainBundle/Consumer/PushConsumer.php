<?php
/**
 * User: mberberoglu
 * Date: 30/07/14
 * Time: 03:06
 */

namespace PushT\Bundle\MainBundle\Consumer;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PushT\Bundle\MainBundle\Document\Push;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Intl\Exception\NotImplementedException;

class PushConsumer implements ConsumerInterface
{

    /** @var  Container */
    private $container;

    /** @var  DocumentManager */
    private $dm;

    /** @var  DocumentRepository */
    private $userTable;

    /** @var  DocumentRepository */
    private $pushTable;

    public function __construct($container)
    {
        $this->container    = $container;
    }

    public function execute(AMQPMessage $msg)
    {
//TODO Push Send Service
        throw new NotImplementedException('Push Consumer');
        $data = unserialize($msg->body);

        return false;
    }

    public function sendPushToAndroid($data)
    {
        //TODO: Android Send
    }

    /**
     * @return DocumentManager
     */
    public function getDm()
    {
        if ($this->dm === null) {
            $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        }

        return $this->dm;
    }

    /**
     * @return DocumentRepository
     */
    public function getUserTable()
    {
        if ($this->userTable === null) {
            $this->userTable = $this->getDm()->getRepository('MainBundle:User');
        }

        return $this->userTable;
    }

    /**
     * @return DocumentRepository
     */
    public function getPushTable()
    {
        if ($this->pushTable === null) {
            $this->pushTable = $this->getDm()->getRepository('MainBundle:Push');
        }

        return $this->pushTable;
    }
}
