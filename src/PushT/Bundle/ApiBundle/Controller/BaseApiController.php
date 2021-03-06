<?php
/**
 * User: mberberoglu
 * Date: 28/07/14
 * Time: 23:07
 */

namespace PushT\Bundle\ApiBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use FOS\RestBundle\Controller\FOSRestController;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class BaseApiController extends FOSRestController
{
    /** @var  DocumentManager */
    private $dm;

    /** @var  DocumentRepository */
    private $userTable;

    /** @var  DocumentRepository */
    private $jobTable;

    /** @var  DocumentRepository */
    private $pushTable;

    /** @var  Producer */
    private $pushProducer;

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
    public function getJobTable()
    {
        if ($this->jobTable === null) {
            $this->jobTable = $this->getDm()->getRepository('MainBundle:Job');
        }

        return $this->jobTable;
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

    public function publishPush($data)
    {
        if ($this->pushProducer === null) {
            $this->pushProducer = $this->get('old_sound_rabbit_mq.push_service_producer');
        }
        $this->pushProducer->publish(serialize($data));
    }
}
