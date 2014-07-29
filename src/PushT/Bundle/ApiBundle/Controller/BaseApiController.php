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


class BaseApiController extends FOSRestController
{
    /** @var  DocumentManager */
    private $dm;

    /** @var  DocumentRepository */
    private $userTable;

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
}