<?php
/**
 * User: mberberoglu
 * Date: 28/07/14
 * Time: 23:11
 */

namespace PushT\Bundle\MainBundle\Validator;

use Doctrine\ODM\MongoDB\DocumentManager;
use PushT\Bundle\MainBundle\Document\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\LegacyExecutionContext;
use Symfony\Component\Validator\Validation;

class BaseValidator
{
    /** @var  Container */
    protected $container;
    /** @var  DocumentManager */
    protected $dm;

    /** @var  NotBlank */
    protected $notBlank;

    /** @var  Length */
    protected $length;

    /** @var  Range */
    protected $range;

    /** @var  User */
    protected $user;

    /** @var  SecurityContext */
    protected $securityContext;

    /**
     * @param $container Container
     * @param $securityContext SecurityContext
     */
    public function __construct($container, $securityContext)
    {
        $this->email = new Email();
        $this->email->message = 'Email is not valid';

        $this->notBlank = new NotBlank();
        $this->notBlank->message = 'This field can not be empty';

        $this->container = $container;
        $this->securityContext = $securityContext;
    }

    /**
     * @param $input
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    public function defaultValidator($input)
    {
        $validator = Validation::createValidator();
        $constraint = new Collection(array(
            'pushtSecret' => array(
                $this->getNotBlank('Secret Key'),
                new Callback(array('methods' => array(
                    array($this, 'checkSecretKey')
                ))),
            ),
            'token' => array(
                $this->getNotBlank('Token'),
                new Callback(array('methods' => array(
                    array($this, 'checkUserAuthorized')
                ))),
            )
        ));

        $violations = $validator->validateValue($input, $constraint);
        return $violations;
    }

    /**
     * @param $violations
     * @return array
     */
    public function parseViolationErrors($violations)
    {
        $violationErrors = array();
        /** @var $violate ConstraintViolation */
        foreach ($violations as $violate) {
            $data = array(
                'message' => $violate->getMessage()
            );
            if (isset($violate->getParameters()['code'])) {
                $data['code'] = $violate->getParameters()['code'];
            } else {
                $data['code'] = 0;
            }

            if (isset($violate->getParameters()['{{ field }}'])) {
                $data['message'] = $violate->getParameters()['{{ field }}'].' => '.$violate->getMessage();
            }
            $violationErrors[] = $data;
        }
        return $violationErrors;
    }

    public function parseViolationsToString($violations, $divider = ' \n ')
    {
        $violationErrors = array();
        /** @var $violate ConstraintViolation */
        foreach ($violations as $violate) {
            $violationErrors[] = $violate->getMessage();
        }
        return implode($divider, $violationErrors);
    }

    /**
     * @param $violations
     * @return JsonResponse
     */
    public function error($violations)
    {
        $violationErrors = $this->parseViolationErrors($violations);
        $violationsString = $this->parseViolationsToString($violations);
        return new JsonResponse(
            array(
                'success' => false,
                'message' => $violationsString,
                'errors'  => $violationErrors
            ),
            JsonResponse::HTTP_UNAUTHORIZED
        );
    }

    /**
     * @param $fieldName
     * @return NotBlank
     */
    protected function getNotBlank($fieldName)
    {
        $this->notBlank = new NotBlank();
        $this->notBlank->message = $fieldName.' can not be empty';
        return $this->notBlank;
    }

    /**
     * @param array $options
     * @param $fieldName
     * @return Length
     */
    public function getLengthValidator(array $options, $fieldName)
    {
        $this->length = new Length($options);
        $this->length->minMessage = $fieldName.' must be at least {{ limit }} characters.';
        return $this->length;
    }

    /**
     * @param $min
     * @param $max
     * @return Range
     */
    protected function getRangeValidator($min, $max)
    {
        $this->range = new Range(array(
            'min' => $min,
            'max' => $max,
        ));
        return $this->range;
    }

    /**
     * Checks that user authorised
     *
     * @param $token
     * @param $context LegacyExecutionContext
     */
    public function checkUserAuthorized($token, $context)
    {
        $user = $this->getDm()->getRepository('MainBundle:User')->findOneBy(array('confirmationToken' => $token));
        if (!$user) {
            $context->addViolation('Error on token parameter', array('code' => 103), null);
        } else {
            $this->user = $user;
        }
    }

    /**
     * Checks that username is not already registered
     *
     * @param $date
     * @param $context LegacyExecutionContext
     */
    public function checkDate($date, $context)
    {
        //TODO $date checker
    }

    /**
     * Checks that username is not already registered
     *
     * @param $secretKey
     * @param $context LegacyExecutionContext
     */
    public function checkSecretKey($secretKey, $context)
    {
        $secret = $this->container->getParameter('api.secret');
        if ($secret != $secretKey) {
            $context->addViolation('Error on secret key', array('code' => 100), null);
        }
    }

    /**
     * Checks that username is not already registered
     *
     * @param $username
     * @param $context LegacyExecutionContext
     */
    public function checkUsernameNotRegistered($username, $context)
    {
        $user = $this->getDm()->getRepository('MainBundle:User')->findBy(array('username' => $username));
        if ($user) {
            $context->addViolation('Username already taken', array('code' => 101), null);
        }
    }

    /**
     * Checks that e-mail is not already registered
     *
     * @param $email
     * @param $context LegacyExecutionContext
     */
    public function checkMailNotRegistered($email, $context)
    {
        $user = $this->getDm()->getRepository('MainBundle:User')->findOneBy(array('email' => $email));
        if ($user) {
            $context->addViolation('Email address already using', array('code' => 102), null);
        }
    }


    /**
     * @param $token
     * @param $context LegacyExecutionContext
     */
    public function checkTokenEqual($token, $context)
    {
        if ($token != $this->container->getParameter('api.secret')) {
            $context->addViolation('Unauthorised', array('code' => 107), null);
        }
    }

    public function getDm()
    {
        if ($this->dm === null) {
            $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        }
        return $this->dm;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}