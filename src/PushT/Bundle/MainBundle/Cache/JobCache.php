<?php
/**
 * User: mberberoglu
 * Date: 02/08/14
 * Time: 23:45
 */

namespace PushT\Bundle\MainBundle\Cache;

use PushT\Bundle\MainBundle\Document\Job;

class JobCache extends BaseCache
{
    public function __construct($container)
    {
        parent::__construct($container, 'job');
    }


    /**
     * @param $job Job
     */
    public function setJob($job)
    {
        $this->set($job->getId(), $job);
    }

    /**
     * @param $jobId
     * @return Job
     */
    public function getJob($jobId)
    {
        $job = $this->get($jobId);
        if (!$job) {
            $job = $this->mongodb->getRepository('WaitressApiBundle:Comment')->find($jobId);
            if ($job) {
                $this->setJob($job);
            }
        }
        return $job;
    }
}