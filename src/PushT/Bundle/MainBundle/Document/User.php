<?php
/**
 * User: mberberoglu
 * Date: 28/07/14
 * Time: 23:01
 */

namespace PushT\Bundle\MainBundle\Document;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class User extends BaseUser
{
    /**
     * @MongoDB\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $androidGCMApiKey;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $androidGCMApiKey
     */
    public function setAndroidGCMApiKey($androidGCMApiKey)
    {
        $this->androidGCMApiKey = $androidGCMApiKey;
    }

    /**
     * @return mixed
     */
    public function getAndroidGCMApiKey()
    {
        return $this->androidGCMApiKey;
    }


}