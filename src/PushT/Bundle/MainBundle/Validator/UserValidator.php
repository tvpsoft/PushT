<?php
/**
 * User: mberberoglu
 * Date: 28/07/14
 * Time: 23:23
 */

namespace PushT\Bundle\MainBundle\Validator;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validation;

class UserValidator extends BaseValidator {
    /**
     * @param $container Container
     * @param $securityContext SecurityContext
     */
    public function __construct($container, $securityContext)
    {
        parent::__construct($container, $securityContext);
    }

    /**
     * @param $input
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    public function registerValidator($input)
    {
        $validator = Validation::createValidator();
        $constraint = new Collection(array(
            'pushtSecret' => array(
                $this->getNotBlank('Secret Key'),
                new Callback(array('methods' => array(
                    array($this, 'checkSecretKey')
                ))),
            ),
            'email' => array(
                $this->getNotBlank('Email'),
                $this->email,
                new Callback(array('methods' => array(
                    array($this, 'checkMailNotRegistered')
                ))),
            ),
            'username'  => array(
                $this->getNotBlank('Username'),
                $this->getLengthValidator(array('min' => 3), 'Username'),
                new Callback(array('methods' => array(
                    array($this, 'checkUsernameNotRegistered')
                ))),
            ),
            'name'  => array(
                $this->getNotBlank('Name'),
            ),
            'surname'  => array(
                $this->getNotBlank('Surname'),
            ),
            'password'  => array(
                $this->getNotBlank('Password'),
                $this->length,
                $this->getLengthValidator(array('min' => 4), 'Password'),
            ),
        ));

        $violations = $validator->validateValue($input, $constraint);
        return $violations;
    }
}