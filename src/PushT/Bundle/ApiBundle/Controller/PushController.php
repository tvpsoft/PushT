<?php
/**
 * User: mberberoglu
 * Date: 28/07/14
 * Time: 23:11
 */

namespace PushT\Bundle\ApiBundle\Controller;

use PushT\Bundle\MainBundle\Document\Push;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class PushController extends BaseApiController
{
    /**
     * @return array
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

            $pushArray = array();
            if (is_array($data['deviceTokens'])) {
                foreach ($data['deviceTokens'] as $key => $deviceToken) {
                    $push = new Push();
                    $push->setUserId($user->getId());
                    $push->setDeviceToken($deviceToken);
                    $push->setData($data['data']);
                    $push->setType($data['type']);
                    $push->setCollapseKey($request->request->get('collapseKey'));
                    $push->setPushKey($request->request->get('pushKey'));
                    $push->setTimeToLive($request->request->get('timeToLive'));
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
}
