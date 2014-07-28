<?php
/**
 * User: mberberoglu
 * Date: 28/07/14
 * Time: 23:07
 */

namespace PushT\Bundle\MainBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
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
            $this->dm = $this->get('doctrine_mongodb')->getManager();
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