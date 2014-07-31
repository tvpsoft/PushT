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

class PushValidator extends BaseValidator
{
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
    public function sendValidator($input)
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
            ),
            'type'  => array(
                $this->getRangeValidator(0, 2)
            ),
            'data'  => array(
                $this->getNotBlank('Data')
            ),
            'deviceTokens'  => array(
                $this->getNotBlank('Device Tokens')
            )
        ));

        $violations = $validator->validateValue($input, $constraint);

        return $violations;
    }
}
