<?php
/**
 * User: mberberoglu
 * Date: 28/07/14
 * Time: 23:01
 */

namespace PushT\Bundle\MainBundle\Document;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class User extends BaseUser
{
    /**
     * @MongoDB\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $androidGCMApiKey;

	/**
	 * @MongoDB\String
	 */
	protected $pamFile;

    public function __construct()
    {
        parent::__construct();
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
     * @param mixed $androidGCMApiKey
     */
    public function setAndroidGCMApiKey($androidGCMApiKey)
    {
        $this->androidGCMApiKey = $androidGCMApiKey;
    }

    /**
     * @return mixed
     */
    public function getAndroidGCMApiKey()
    {
        return $this->androidGCMApiKey;
    }

    public function toArray()
    {
        return array(
            'userId'    => $this->getId(),
            'username'  => $this->getUsername(),
            'email'     => $this->getEmail(),
            'googleApiKey' => $this->getAndroidGCMApiKey(),
			'pamFile'	=> $this->getPamFile()
        );
    }

	/**
	 * @return mixed
	 */
	public function getPamFile()
	{
		return $this->pamFile;
	}

	/**
	 * @param mixed $pamFile
	 */
	public function setPamFile($pamFile)
	{
		$this->pamFile = $pamFile;
	}



}
