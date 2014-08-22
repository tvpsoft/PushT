<?php
/**
 * User: mberberoglu
 * Date: 28/07/14
 * Time: 23:11
 */

namespace PushT\Bundle\ApiBundle\Controller;

use PushT\Bundle\MainBundle\Document\Job;
use PushT\Bundle\MainBundle\Document\Push;
use PushT\Bundle\MainBundle\Model\Statistic;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class JobController extends BaseApiController
{
    /**
     * @return JsonResponse
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get Job List",
     *  parameters={
     *      {"name"="PushT-Secret", "dataType"="", "required"="true", "format"="SECRET", "format"="RjbpWbpG14ZRyjsqHoAt412ThvkNQ5Au"},
     *      {"name"="PushT-Token", "dataType"="", "required"="true", "format"="TOKEN", "format"="7ESfyHBmABwxsp0ut7XQaNh9xn0iVbb3Q3joLULlwiTYHTtb1r2beklMIm5DxgHnk0M5VyrrQlqyeTKgkMrz7NlP9rtHIb52bS87"},
     *  }
     * )
     */
    public function getJobsAction()
    {
        $request = Request::createFromGlobals();
        $data = array(
            'pushtSecret'  => $request->headers->get('PushT-Secret'),
            'token' => $request->headers->get('PushT-Token'),
        );
        $jobValidator = $this->container->get('validator.job');
        $violations = $jobValidator->defaultValidator($data);
        if ($violations->count()) {
            return $jobValidator->error($violations);
        } else {
            $user = $jobValidator->getUser();

            $sort = array(
                'createdAt' => ($request->query->get('sort', 'asc') == 'asc')? 'ASC' : 'DESC'
            );
            //TODO Solr Integration
            $jobs = $this->getJobTable()->findBy(array('userId' => $user->getId()), $sort);

            $jobsData = array();
            /** @var Job $job */
            foreach ($jobs as $job) {
                $jobsData[] = $job->toArray();
            }

            return new JsonResponse(
                array(
                    'success' => true,
                    'jobs'    => $jobsData,
                )
            );
        }
    }

    /**
     * @param $jobId
     * @return JsonResponse
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get Job Detail by JobId",
     *  parameters={
     *      {"name"="PushT-Secret", "dataType"="string", "required"="true", "format"="SECRET"},
     *      {"name"="PushT-Token", "dataType"="string", "required"="true", "format"="TOKEN"},
     *      {"name"="jobId", "dataType"="string", "required"="true", "format"="String"},
     *  }
     * )
     */
    public function getJobAction($jobId)
    {
        $request = Request::createFromGlobals();
        $data = array(
            'pushtSecret'  => $request->headers->get('PushT-Secret'),
            'token' => $request->headers->get('PushT-Token'),
            'jobId' => $jobId
        );
        $jobValidator = $this->container->get('validator.job');
        $violations = $jobValidator->detailValidator($data);
        if ($violations->count()) {
            return $jobValidator->error($violations);
        } else {
            $job = $jobValidator->getJob();

            //TODO Solr Integration
            $pushes = $this->getPushTable()->findBy(array('jobId' => $job->getId()));

            $pushesData = array();
            /** @var Push $push */
            foreach ($pushes as $push) {
                $pushesData[] = $push->toArray();
            }

            return new JsonResponse(
                array(
                    'success' => true,
                    'job'     => $job->toArray(),
                    'statistic' => Statistic::pushes($pushes),
                    'pushes'  => $pushesData
                )
            );
        }
    }
}
