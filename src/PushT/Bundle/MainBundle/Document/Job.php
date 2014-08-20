<?php
/**
 * User: mberberoglu
 * Date: 28/07/14
 * Time: 23:01
 */

namespace PushT\Bundle\MainBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Job
{
    /**
     * @MongoDB\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $userId;

    /**
     * @MongoDB\Int
     */
    protected $createdAt;

    /**
     * @MongoDB\Int
     */
    protected $updatedAt;

    /**
     * @MongoDB\String
     */
    protected $pushKey;

    /**
     * @MongoDB\Int
     * 0 => Android, 1 => IOS, 2 => Windows
     */
    protected $type;

    /**
     * @MongoDB\String
     */
    protected $data;

    /**
     * @MongoDB\String
     */
    protected $collapseKey;

    /**
     * @MongoDB\Int
     */
    protected $timeToLive;

    public function __construct()
    {
        $this->createdAt = time();
        $this->updatedAt = time();
    }

    /**
     * @param mixed $collapseKey
     */
    public function setCollapseKey($collapseKey)
    {
        $this->collapseKey = $collapseKey;
    }

    /**
     * @return mixed
     */
    public function getCollapseKey()
    {
        return $this->collapseKey;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
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
     * @param mixed $pushKey
     */
    public function setPushKey($pushKey)
    {
        $this->pushKey = $pushKey;
    }

    /**
     * @return mixed
     */
    public function getPushKey()
    {
        return $this->pushKey;
    }

    /**
     * @param mixed $timeToLive
     */
    public function setTimeToLive($timeToLive)
    {
        $this->timeToLive = $timeToLive;
    }

    /**
     * @return mixed
     */
    public function getTimeToLive()
    {
        return $this->timeToLive;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    public function toArray()
    {
        return array(
            'id'        => $this->getId(),
            'userId'    => $this->getUserId(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
            'pushKey'   => $this->getPushKey(),
            'data'      => $this->getData(),
            'collapseKey'   => $this->getCollapseKey(),
            'timeToLive'    => $this->getTimeToLive()
        );
    }

}
