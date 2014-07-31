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
class Push
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
     * @MongoDB\String
     */
    protected $deviceToken;

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
     * 0 => On Queue
     * 1 => Successfully Sent
     * 2 => Requeued,
     * 3 => Deleted Device
     */
    protected $status;

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
        $this->status    = 0;
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
        $this->data = json_encode(json_decode($data));
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $deviceToken
     */
    public function setDeviceToken($deviceToken)
    {
        $this->deviceToken = $deviceToken;
    }

    /**
     * @return mixed
     */
    public function getDeviceToken()
    {
        return $this->deviceToken;
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
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
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

}
