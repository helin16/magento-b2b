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
	const TYPE_EMAIL = 'EMAIL';
	
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
	 * Transaction ID
	 * 
	 * @var string
	 */
	private $transId = '';
	/**
	 * The comma separated assetIds
	 * 
	 * @var string
	 */
	private $attachAssetIds = '';
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
    	$this->status = trim($value);
	    return $this;
	}
	/**
	 * Getter for the transId
	 * 
	 * @return string
	 */
	public function getTransId() 
	{
	    return $this->transId;
	}
	/**
	 * Setter for the transId
	 * 
	 * @param string $value The transId of the message
	 * 
	 * @return Message
	 */
	public function setTransId($value) 
	{
    	$this->transId = trim($value);
	    return $this;
	}
	/**
	 * Getter for the attachAssetIds
	 * 
	 * @return array()
	 */
	public function getAttachAssetIds() 
	{
		return $this->attachAssetIds;
	}
	/**
	 * Setter for the attachAssetIds
	 * 
	 * @param string $value The attachAssetIds of the message
	 * 
	 * @return Message
	 */
	public function setAttachAssetIds($value) 
	{
    	$this->attachAssetIds = $value;
	    return $this;
	}
	/**
	 * Getting the array of assetids
	 * 
	 * @return multitype:string
	 */
	public function getAttachmentAssetIdArray()
	{
   		return array_map(create_function('$a', 'return trim($a);'), explode(',', $this->getAttachAssetIds()));
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
		DaoMap::setStringType('attachAssetIds', 'longtext');
		
		parent::__loadDaoMap();
	
		DaoMap::createIndex('transId');
		DaoMap::createIndex('type');
		DaoMap::createIndex('from');
		DaoMap::createIndex('to');
		DaoMap::createIndex('status');
	
		DaoMap::commit();
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
	 * @return Message
	 */
	public static function create($from, $to, $subject, $body, $type, $attachmentAssets = array())
	{
		$attacheAssetIds = array();
		foreach($attachmentAssets as $asset) {
			if($asset instanceof Asset)
				$attacheAssetIds[] = trim($asset->getAssetId());
		}
		$entity = new Message();
		return $entity->setFrom(trim($from))
			->setTo(trim($to))
			->setSubject(trim($subject))
			->setBody(trim($body))
			->setType(trim($type))
			->setAttachAssetIds($attacheAssetIds)
			->save();
	}
}