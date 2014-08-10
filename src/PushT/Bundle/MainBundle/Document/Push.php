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
    protected $jobId;

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
     * @MongoDB\Int
     */
    protected $bounce;

    /**
     * @MongoDB\Int
     * 0 => On Queue
     * 1 => Successfully Sent
     * 2 => Requeued,
     * 3 => Deleted Device,
     * 4 => Invalid Api Key - Waiting,
     * 5 => Out of bounce
     * 6 => Opened
     */
    protected $status;

    /**
     * @MongoDB\String
     */
    protected $errorMessage;

    public function __construct()
    {
        $this->createdAt = time();
        $this->updatedAt = time();
        $this->status    = 0;
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
     * @param mixed $jobId
     */
    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
    }

    /**
     * @return mixed
     */
    public function getJobId()
    {
        return $this->jobId;
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
     * @param mixed $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param mixed $bounce
     */
    public function setBounce($bounce)
    {
        $this->bounce = $bounce;
    }

    /**
     *
     */
    public function incrBounce()
    {
        $this->bounce++;
    }

    /**
     * @return mixed
     */
    public function getBounce()
    {
        return $this->bounce;
    }

}
