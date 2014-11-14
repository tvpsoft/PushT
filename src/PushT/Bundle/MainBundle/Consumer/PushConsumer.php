<?php
/**
 * User: mberberoglu
 * Date: 30/07/14
 * Time: 03:06
 */

namespace PushT\Bundle\MainBundle\Consumer;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PushT\Bundle\MainBundle\Cache\JobCache;
use PushT\Bundle\MainBundle\Cache\PushCache;
use PushT\Bundle\MainBundle\Cache\UserCache;
use PushT\Bundle\MainBundle\Document\Job;
use PushT\Bundle\MainBundle\Document\Push;
use PushT\Bundle\MainBundle\Document\User;
use Symfony\Component\DependencyInjection\Container;

class PushConsumer implements ConsumerInterface
{

    /** @var  Container */
    private $container;

    /** @var  DocumentManager */
    private $dm;

    /** @var  DocumentRepository */
    private $userTable;

    /** @var  DocumentRepository */
    private $pushTable;

    /** @var  JobCache */
    private $jobCache;

    /** @var  PushCache */
    private $pushCache;

    /** @var  UserCache */
    private $userCache;

    public function __construct($container)
    {
        $this->container    = $container;
    }

    public function execute(AMQPMessage $msg)
    {
        /** @var Push $push */
        $push = unserialize($msg->body);

        $job = $this->getJobCache()->getJob($push->getJobId());
        if ($job === null) {
            $this->getDm()->remove($push);
            $this->getDm()->flush();
            //Returning true will be delete it from queue
            return true;
        }

        if ($job->getType() == 0) {
            $push =  $this->sendPushToAndroid($job, $push);
        } elseif ($job->getType() == 1) {
            $push = $this->sendPushToIOS($push, $job);
        } elseif ($job->getType() == 2) {
            //TODO send to Windows
        }

        $this->updateData($job, $push);

        switch ($push->getStatus()) {
            case 0:
                return false;
            case 1:
                return true;
            case 2:
                if ($push->getBounce() < 3) {
                    $push->incrBounce();
                    $this->updateData($job, $push);

                    return false;
                } else {
                    $push->setStatus(5);
                    $this->updateData($job, $push);

                    return true;
                }
            case 3:
                return true;
            case 4:
                return true;
			default:
				return true;
        }
    }

    /**
     * @param $job Job
     * @param $push Push
     * @return Push
     */
    public function sendPushToAndroid($job, $push)
    {
        $registrationIds = array($push->getDeviceToken());

        $msg = json_decode($job->getData(), 1);
        $msg['jobId'] = $job->getId();
        $msg['msgId'] = $push->getId();

        $fields = array(
            'registration_ids'    => $registrationIds,
            'data'                => $msg
        );

        $user = $this->getUserCache()->getUser($job->getUserId());
        if (!$user['googleApiKey']) {
            $push->setStatus(4);

            return $push;
        }
        $headers = array(
            'Authorization: key=' . $user['googleApiKey'],
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = json_decode(curl_exec($ch), 1);
        curl_close($ch);
        if ($result['success'] == 1) {
            echo 'Android send'.PHP_EOL;
            $push->setStatus(1);
        } else {
            echo '### Error => '.$result['results'][0]['error'].PHP_EOL;
            $push->setErrorMessage($result['results'][0]['error']);
            if ($result['results'][0]['error'] == 'InvalidRegistration') {
                $push->setStatus(3);

                /** @var User $user */
                $user = $this->getUserTable()->find($user['userId']);
                if ($user) {
                    $user->setAndroidGCMApiKey(null);
                    $this->getDm()->persist($user);
                    $this->getDm()->flush();

                    $this->getUserCache()->setUser($user);
                }
            } else {
                $push->setStatus(2);
            }
        }

        return $push;
    }

	/**
	 * @param $job Job
	 * @param $push Push
	 * @return Push
	 */
	public function sendPushToIOS(Push $push, Job $job) {
		$badge = 3;
		$sound = $job->getSound();

		$payload = array();
		$payload['aps'] = array(
			'alert' => $job->getAlert(),
			'badge' => intval($badge),
			'sound' => $sound,
			'jobId' => $job->getId(),
			'msgId' => $push->getId(),
			'data' 	=> json_encode($job->getData())
		);
		$payload = json_encode($payload);

		$user = $this->getUserCache()->getUser($job->getUserId());
		$pamFile = $this->container->getParameter('basepath').$user['pamFile'];

		$stream_context = stream_context_create();
		stream_context_set_option($stream_context, 'ssl', 'local_cert', $pamFile);

		$apns = stream_socket_client('ssl://gateway.push.apple.com:' . 2195, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);

		$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $push->getDeviceToken())) . chr(0) . chr(strlen($payload)) . $payload;
		$fwrite = fwrite($apns, $apnsMessage);
		if(!$fwrite) {
			$push->setStatus(3);
		} else {
			$push->setStatus(1);
		}


		@socket_close($apns);
		@fclose($apns);

		echo 'IOS send'.PHP_EOL;

		return $push;
	}

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

    /**
     * @return DocumentRepository
     */
    public function getPushTable()
    {
        if ($this->pushTable === null) {
            $this->pushTable = $this->getDm()->getRepository('MainBundle:Push');
        }

        return $this->pushTable;
    }

    /**
     * @return \PushT\Bundle\MainBundle\Cache\JobCache
     */
    public function getJobCache()
    {
        if ($this->jobCache === null) {
            $this->jobCache = $this->container->get('cache.job');
        }

        return $this->jobCache;
    }

    /**
     * @return \PushT\Bundle\MainBundle\Cache\PushCache
     */
    public function getPushCache()
    {
        if ($this->pushCache === null) {
            $this->pushCache = $this->container->get('cache.push');
        }

        return $this->pushCache;
    }

    /**
     * @return \PushT\Bundle\MainBundle\Cache\UserCache
     */
    public function getUserCache()
    {
        if ($this->userCache === null) {
            $this->userCache = $this->container->get('cache.user');
        }

        return $this->userCache;
    }

    /**
     * @param $job Job
     * @param $push Push
     */
    private function updateData($job, $push)
    {
        $job->setUpdatedAt(time());
        $push->setUpdatedAt(time());

        $this->getDm()->persist($job);
        $this->getDm()->persist($push);
        $this->getDm()->flush();

        $this->getPushCache()->setPush($push);
        $this->getJobCache()->setJob($job);
    }

}
