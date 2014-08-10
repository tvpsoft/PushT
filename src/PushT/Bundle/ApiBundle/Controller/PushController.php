<?php
/**
 * User: mberberoglu
 * Date: 28/07/14
 * Time: 23:11
 */

namespace PushT\Bundle\ApiBundle\Controller;

use PushT\Bundle\MainBundle\Document\Job;
use PushT\Bundle\MainBundle\Document\Push;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class PushController extends BaseApiController
{
    /**
     * @return JsonResponse
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  description="User Settings GCM Api Key etc.",
     *  parameters={
     *      {"name"="PushT-Secret", "dataType"="string", "required"="true", "description"="RjbpWbpG14ZRyjsqHoAt412ThvkNQ5Au"},
     *      {"name"="PushT-Token", "dataType"="string", "required"="true", "description"="7ESfyHBmABwxsp0ut7XQaNh9xn0iVbb3Q3joLULlwiTYHTtb1r2beklMIm5DxgHnk0M5VyrrQlqyeTKgkMrz7NlP9rtHIb52bS87"},
     *      {"name"="type", "dataType"="integer", "required"="true", "description"="Type of device to send 0 => Android, 1 => IOS, 2 => Windows"},
     *      {"name"="data", "dataType"="json string", "required"="true", "description"="Data that swift to user"},
     *      {"name"="deviceTokens", "dataType"="array", "required"="true", "description"="Devices tokens to send"},
     *      {"name"="collapseKey", "dataType"="string", "required"="false", "description"="GCM collapse key. NOT REQUIRED."},
     *      {"name"="pushKey", "dataType"="string", "required"="false", "description"="Unique key to track group of push notification. NOT REQUIRED."},
     *      {"name"="timeToLive", "dataType"="integer", "required"="false", "description"="GCM Time to live in seconds. Default 4 weeks. NOT REQUIRED."},
     *  }
     * )
     */
    public function postPushAction()
    {
        $request = Request::createFromGlobals();
        $data = array(
            'pushtSecret'  => $request->headers->get('PushT-Secret'),
            'token' => $request->headers->get('PushT-Token'),
            'type'  => $request->request->get('type'),
            'data'  => $request->request->get('data'),
            'deviceTokens' => $request->request->get('deviceTokens')
        );
        $pushValidator = $this->container->get('validator.push');
        $violations = $pushValidator->sendValidator($data);
        if ($violations->count()) {
            return $pushValidator->error($violations);
        } else {
            $user = $pushValidator->getUser();

            $job = new Job();
            $job->setUserId($user->getId());
            $job->setData($data['data']);
            $job->setType($data['type']);
            $job->setCollapseKey($request->request->get('collapseKey'));
            $job->setPushKey($request->request->get('pushKey'));
            $job->setTimeToLive($request->request->get('timeToLive'));
            $this->getDm()->persist($job);

            $pushArray = array();
            if (is_array($data['deviceTokens'])) {
                foreach ($data['deviceTokens'] as $key => $deviceToken) {
                    $push = new Push();
                    $push->setJobId($job->getId());
                    $push->setDeviceToken($deviceToken);
                    $this->getDm()->persist($push);

                    if ($key % 25 == 0) {
                        $this->getDm()->flush();
                    }
                    $pushArray[] = $push;
                }
            }
            $this->getDm()->flush();

            foreach ($pushArray as $push) {
                $this->publishPush($push);
            }

            return new JsonResponse(
                array(
                    'success' => true
                )
            );
        }
    }

    /**
     * @param $pushId
     * @return JsonResponse
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Notify server that push is received",
     *  parameters={
     *      {"name"="PushT-Secret", "dataType"="string", "required"="true", "description"="RjbpWbpG14ZRyjsqHoAt412ThvkNQ5Au"},
     *      {"name"="PushT-Token", "dataType"="string", "required"="true", "description"="7ESfyHBmABwxsp0ut7XQaNh9xn0iVbb3Q3joLULlwiTYHTtb1r2beklMIm5DxgHnk0M5VyrrQlqyeTKgkMrz7NlP9rtHIb52bS87"},
     *      {"name"="pushId", "dataType"="string", "required"="true", "description"="Push Id"},
     *  }
     * )
     */
    public function postPushReceiveAction($pushId)
    {
        $request = Request::createFromGlobals();
        $data = array(
            'pushtSecret'  => $request->headers->get('PushT-Secret'),
            'token' => $request->headers->get('PushT-Token'),
            'pushId'  => $pushId,
        );
        $pushValidator = $this->container->get('validator.push');
        $violations = $pushValidator->pushValidator($data);
        if ($violations->count()) {
            return $pushValidator->error($violations);
        } else {
            $pushCache = $this->get('cache.push');
            $jobCache  = $this->get('cache.job');

            $push = $pushValidator->getPush();
            $job  = $pushValidator->getJob();

            if ($push->getStatus() != 1) {
                $push->setStatus(1);
            }

            $push->setUpdatedAt(time());
            $job->setUpdatedAt(time());

            $this->getDm()->persist($push);
            $this->getDm()->persist($job);
            $this->getDm()->flush();

            $pushCache->setPush($push);
            $jobCache->setJob($job);

            //TODO update solr
            return new JsonResponse(
                array(
                    'success' => true
                )
            );
        }
    }

    /**
     * @param $pushId
     * @return JsonResponse
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Notify server that push is opened",
     *  parameters={
     *      {"name"="PushT-Secret", "dataType"="string", "required"="true", "description"="RjbpWbpG14ZRyjsqHoAt412ThvkNQ5Au"},
     *      {"name"="PushT-Token", "dataType"="string", "required"="true", "description"="7ESfyHBmABwxsp0ut7XQaNh9xn0iVbb3Q3joLULlwiTYHTtb1r2beklMIm5DxgHnk0M5VyrrQlqyeTKgkMrz7NlP9rtHIb52bS87"},
     *      {"name"="pushId", "dataType"="string", "required"="true", "description"="Push Id"},
     *  }
     * )
     */
    public function postPushOpenAction($pushId)
    {
        $request = Request::createFromGlobals();
        $data = array(
            'pushtSecret'  => $request->headers->get('PushT-Secret'),
            'token' => $request->headers->get('PushT-Token'),
            'pushId'  => $pushId,
        );
        $pushValidator = $this->container->get('validator.push');
        $violations = $pushValidator->pushValidator($data);
        if ($violations->count()) {
            return $pushValidator->error($violations);
        } else {
            $pushCache = $this->get('cache.push');
            $jobCache  = $this->get('cache.job');

            $push = $pushValidator->getPush();
            $job  = $pushValidator->getJob();

            if ($push->getStatus() == 1) {
                $push->setStatus(6);
            }

            $push->setUpdatedAt(time());
            $job->setUpdatedAt(time());

            $this->getDm()->persist($push);
            $this->getDm()->persist($job);
            $this->getDm()->flush();

            $pushCache->setPush($push);
            $jobCache->setJob($job);

            //TODO update solr
            return new JsonResponse(
                array(
                    'success' => true
                )
            );
        }
    }
}
