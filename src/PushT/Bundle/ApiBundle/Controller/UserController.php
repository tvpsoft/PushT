<?php
/**
 * User: mberberoglu
 * Date: 28/07/14
 * Time: 23:11
 */

namespace PushT\Bundle\ApiBundle\Controller;

use PushT\Bundle\MainBundle\Document\User;
use PushT\Bundle\MainBundle\Model\Helper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserController extends BaseApiController
{
    /**
     * @return array
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Register User",
     *  parameters={
     *      {"name"="PushT-Secret", "dataType"="", "required"="true", "format"="SECRET", "description"="RjbpWbpG14ZRyjsqHoAt412ThvkNQ5Au"},
     *      {"name"="name", "dataType"="", "required"="false", "format"="token"},
     *      {"name"="surname", "dataType"="", "required"="false", "format"="String"},
     *      {"name"="password", "dataType"="", "required"="false", "format"="String"},
     *      {"name"="email", "dataType"="", "required"="false", "format"="String"}
     *  }
     * )
     */
    public function postUserRegisterAction()
    {
        $request = Request::createFromGlobals();

        $data = array(
            'pushtSecret'  => $request->headers->get('PushT-Secret'),
            'email' => $request->request->get('email'),
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
        );
        $userValidator = $this->container->get('validator.user');
        $violations = $userValidator->registerValidator($data);
        if ($violations->count()) {
            $violationErrors = $userValidator->parseViolationErrors($violations);
            $violationsString = $userValidator->parseViolationsToString($violations, ' #$# ');

            return new JsonResponse(
                array(
                    'success' => false,
                    'message' => $violationsString,
                    'errors'  => $violationErrors
                )
            );
        } else {
            $userManager = $this->container->get('fos_user.user_manager');
            $user = $userManager->createUser();
            /** @var $user User */
            $user->setEmail($data['email'])
                ->setUsername($data['username'])
                ->setPlainPassword($data['password'])
                ->setEnabled(1)
                ->setConfirmationToken(Helper::randomString(100));
            $userManager->updateUser($user);

            $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
            $this->get("security.context")->setToken($token);

            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("fos_user.registration.success", $event);

            return new JsonResponse(
                array(
                    'success'       => true,
                    'userId'        => $user->getId(),
                    'token'         => $user->getConfirmationToken(),
                    'username'      => $user->getUsername(),
                    'mail'          => $user->getEmail()
                )
            );
        }
    }

    /**
     * @return array
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Authorize user with token or email+password or username+password",
     *  parameters={
     *      {"name"="PushT-Secret", "dataType"="", "required"="true", "format"="SECRET", "description"="RjbpWbpG14ZRyjsqHoAt412ThvkNQ5Au"},
     *      {"name"="token", "dataType"="", "required"="false", "format"="TOKEN"},
     *      {"name"="username", "dataType"="", "required"="false", "format"="String"},
     *      {"name"="password", "dataType"="", "required"="false", "format"="String"},
     *      {"name"="email", "dataType"="", "required"="false", "format"="String"}
     *  }
     * )
     */
    public function postUserAuthorizeAction()
    {
        $request = Request::createFromGlobals();
        /** @var $user User */

        if ($request->request->get('token')) {
            $user = $this->getUserTable()->findOneBy(array('confirmationToken' => $request->request->get('token')));
        } elseif ($request->request->get('email') && $request->request->get('password')) {
            $user = $this->getUserTable()->findOneBy(array('email' => $request->request->get('email')));
        } elseif ($request->request->get('username') && $request->request->get('password')) {
            $user = $this->getUserTable()->findOneBy(array('username' => $request->request->get('username')));
            if ($user) {
                $factory = $this->container->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($request->request->get('password'), $user->getSalt());
                if ($password != $user->getPassword()) {
                    $user = null;
                }
            }
        } else {
            return new JsonResponse(
                array(
                    'success' => false,
                    'message' => 'Please fill all fields',
                    'parameters'    => array(
                        'token',
                        'email and password',
                        'username and password'
                    )
                )
            );
        }

        if ($user) {
            $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
            $this->container->get("security.context")->setToken($token);

            $event = new InteractiveLoginEvent($request, $token);
            $this->container->get("event_dispatcher")->dispatch("security.interactive_login", $event);

            $user->addRole('ROLE_ADMIN');
            $this->getDm()->persist($user);
            $this->getDm()->flush();

            return new JsonResponse(
                array(
                    'success'       => true,
                    'userId'        => $user->getId(),
                    'token'         => $user->getConfirmationToken(),
                    'mail'          => $user->getEmail(),
                )
            );
        } else {
            return new JsonResponse(
                array(
                    'success' => false,
                    'message' => 'Error on username/password'
                )
            );
        }
    }

    /**
     * @return array
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  description="User Settings GCM Api Key, Pam File etc.",
     *  parameters={
     *      {"name"="PushT-Secret", "dataType"="", "required"="true", "format"="SECRET", "description"="RjbpWbpG14ZRyjsqHoAt412ThvkNQ5Au"},
     *      {"name"="PushT-Token", "dataType"="", "required"="true", "format"="TOKEN", "description"="7ESfyHBmABwxsp0ut7XQaNh9xn0iVbb3Q3joLULlwiTYHTtb1r2beklMIm5DxgHnk0M5VyrrQlqyeTKgkMrz7NlP9rtHIb52bS87"},
     *      {"name"="gcmApiKey", "dataType"="", "required"="false", "format"="String"},
	 *      {"name"="pamFile", "dataType"="", "required"="false", "format"="File"},
     *  }
     * )
     */
    public function postUserSettingsAction()
    {
        $request = Request::createFromGlobals();

        $data = array(
            'pushtSecret'  => $request->headers->get('PushT-Secret'),
            'token' => $request->headers->get('PushT-Token'),
        );
        $userValidator = $this->container->get('validator.user');
        $violations = $userValidator->defaultValidator($data);
        if ($violations->count()) {
            return $userValidator->error($violations);
        } else {
            $user = $userValidator->getUser();

            if ($request->request->has('gcmApiKey')) {
                $user->setAndroidGCMApiKey($request->request->get('gcmApiKey'));
            }


			if ($request->files->has('pamFile')) {
				/** @var UploadedFile $pamFile */
				$pamFile = $request->files->get('pamFile');
				$fileName = Helper::randomString(10);
				$pamFile->move($this->container->getParameter('basepath').'/pam/', $fileName);
				$user->setPamFile($fileName);
			}


            $this->getDm()->persist($user);
            $this->getDm()->flush();

            $userCache = $this->get('cache.user');
            $userCache->setUser($user);

            return new JsonResponse(
                array(
                    'success' => true
                )
            );
        }
    }
}
