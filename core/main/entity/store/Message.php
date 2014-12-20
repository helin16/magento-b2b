<?php
/**
 * Entity for Customer
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Message extends BaseEntityAbstract
{
	const TYPE_MESSAGE = 'Message';
	
	const STATUS_NEW = 'NEW';
	const STATUS_SENDING = 'SENDING';
	const STATUS_SENT = 'SENT';
	const STATUS_FAILED = 'FAILED';
	
	/**
	 * The type of the message
	 *
	 * @var string
	 */
	private $type;
	/**
	 * The originator of message
	 *
	 * @var string
	 */
	private $from;
	/**
	 * The destination of message
	 *
	 * @var string
	 */
	private $to;
	/**
	 * The subject of message
	 *
	 * @var string
	 */
	private $subject;
	/**
	 * The body of message
	 *
	 * @var string
	 */
	private $body;
	/**
	 * The status of message
	 *
	 * @var string
	 */
	private $status = self::STATUS_NEW;
	/**
	 * caching the transid
	 * 
	 * @var string
	 */
	private static $_transId = '';
	/**
	 * Getter for the type
	 * 
	 * @return string
	 */
	public function getType() 
	{
	    return $this->type;
	}
	/**
	 * Setter for the type
	 * 
	 * @param string $value The type of the message
	 * 
	 * @return Message
	 */
	public function setType($value) 
	{
	    $this->type = $value;
	    return $this;
	}
	/**
	 * Getter for the from
	 * 
	 * @return string
	 */
	public function getFrom() 
	{
	    return $this->from;
	}
	/**
	 * Setter for the from
	 * 
	 * @param string $value The from of the message
	 * 
	 * @return Message
	 */
	public function setFrom($value) 
	{
	    $this->from = $value;
	    return $this;
	}
	/**
	 * Getter for the to
	 * 
	 * @return string
	 */
	public function getTo() 
	{
	    return $this->to;
	}
	/**
	 * Setter for the to
	 * 
	 * @param string $value The to of the message
	 * 
	 * @return Message
	 */
	public function setTo($value) 
	{
	    $this->to = $value;
	    return $this;
	}
	/**
	 * Getter for the subject
	 * 
	 * @return string
	 */
	public function getSubject() 
	{
	    return $this->subject;
	}
	/**
	 * Setter for the subject
	 * 
	 * @param string $value The subject of the message
	 * 
	 * @return Message
	 */
	public function setSubject($value) 
	{
	    $this->subject = $value;
	    return $this;
	}
	/**
	 * Getter for the body
	 * 
	 * @return string
	 */
	public function getBody() 
	{
	    return $this->body;
	}
	/**
	 * Setter for the body
	 * 
	 * @param string $value The body of the message
	 * 
	 * @return Message
	 */
	public function setBody($value) 
	{
	    $this->body = $value;
	    return $this;
	}
	/**
	 * Getter for the status
	 * 
	 * @return string
	 */
	public function getStatus() 
	{
	    return $this->status;
	}
	/**
	 * Setter for the status
	 * 
	 * @param string $value The status of the message
	 * 
	 * @return Message
	 */
	public function setStatus($value) 
	{
	   if(trim($this->getId()) !== "") {
			$oldStatuses = Dao::getResultsNative('select status from message where id = ?', array($this->getId()));
			if(count($oldStatuses) > 0 && ($oldStatus = trim($oldStatuses[0]['status'])) === trim($value))//no change of the status
				$this->status = trim($value);
			else
				$this->pushStatus(trim($value));
		}
		else
	    	$this->status = trim($value);
	    return $this;
	}
	/**
	 * pushing the status of the status
	 *
	 * @param string $status The new status of the PO
	 *
	 * @throws EntityException
	 * @return PurchaseOrder
	 */
	public function pushStatus($status)
	{
		if(!$this->_validateStatus($status))
			throw new EntityException('Invalid status(=' . $status . ').');
		$oldStatuses = Dao::getResultsNative('select status from purchaseorder where id = ?', array($this->getId()));
		if(count($oldStatuses) > 0 && ($oldStatus = trim($oldStatuses[0]['status'])) === trim($status))//no change of the status
			return $this;
		$this->status = trim($status);
		if(trim($this->getId()) !== '')
		{
			$msg = 'Changed status from "' . $oldStatus . '" to "' . $status . '"';
			$this->addComment($msg, Comments::TYPE_SYSTEM)
			->addLog($msg, Log::TYPE_SYSTEM);
		}
		return $this;
	}
	/**
	 * validating the status
	 *
	 * @param string $status The status that we are validating
	 *
	 * @return boolean
	 */
	private function _validateStatus($status)
	{
		$oClass = new ReflectionClass (get_class($this));
		foreach($oClass->getConstants() as $name => $value)
		{
			if(strpos($name, 'STATUS_') === 0 && trim($value) === trim($status))
				return true;
		}
		return false;
	}
	/**
	 * Getting the transid
	 * 
	 * @param string $salt The salt of making the trans id
	 * 
	 * @return string
	 */
	public static function getTransKey($salt = '')
	{
		if(trim(self::$_transId) === '')
			self::$_transId = StringUtilsAbstract::getRandKey($salt);
		return self::$_transId;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
	}
	/**
	 * Creating a instance of this
	 *
	 * @param string  $from
	 * @param string  $to
	 * @param string  $subject
	 * @param string  $body
	 * @param string  $type
	 * @param string  $status
	 *
	 * @return Ambigous Message
	 */
	public static function create($from, $to, $subject, $body, $type, $status)
	{
		$name = trim($from);
		$to = trim($to);
		$subject = trim($subject);
		$body = trim($body);
		$type = trim($type);
		$status = trim($status);
		
		$class = get_called_class();
		$entity = new $class();
		
		return self::setFrom($from)
		->setTo($to)
		->setSubject($subject)
		->setBody($body)
		->setType($type)
		->setStatus($status)
		->save();
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = '', $reset = false)
	{
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'msg');
	
		DaoMap::setStringType('transId','varchar', 32);
		DaoMap::setStringType('type', 'varchar', 50);
		DaoMap::setStringType('from', 'varchar', 200);
		DaoMap::setStringType('to', 'varchar', 255);
		DaoMap::setStringType('subject', 'varchar', 200);
		DaoMap::setStringType('body', 'longtext');
		DaoMap::setStringType('status', 'varchar', 10);
		
		parent::__loadDaoMap();
	
		DaoMap::createIndex('transId');
		DaoMap::createIndex('type');
		DaoMap::createIndex('from');
		DaoMap::createIndex('to');
		DaoMap::createIndex('status');
	
		DaoMap::commit();
	}
}