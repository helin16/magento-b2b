<?php
class ErrorLog extends BaseEntityAbstract
{
	/**
	 * The hased key for an error
	 * 
	 * @var string
	 */
	private $key = '';
	/**
	 * The error msg
	 * 
	 * @var string
	 */
	private $msg;
	/**
	 * The error code
	 * 
	 * @var string
	 */
	private $code;
	protected $traces;
	/**
	 * Getter for key
	 *
	 * @return 
	 */
	public function getKey() 
	{
	    return $this->key;
	}
	/**
	 * Setter for key
	 *
	 * @param string $value The key
	 *
	 * @return ErrorLog
	 */
	public function setKey($value) 
	{
	    $this->key = $value;
	    return $this;
	}
	/**
	 * Getter for code
	 *
	 * @return 
	 */
	public function getCode() 
	{
	    return $this->code;
	}
	/**
	 * Setter for code
	 *
	 * @param string $value The code
	 *
	 * @return ErrorLog
	 */
	public function setCode($value) 
	{
	    $this->code = $value;
	    return $this;
	}
	/**
	 * Getter for msg
	 *
	 * @return 
	 */
	public function getMsg() 
	{
	    return $this->msg;
	}
	/**
	 * Setter for msg
	 *
	 * @param string $value The msg
	 *
	 * @return ErrorLog
	 */
	public function setMsg($value) 
	{
	    $this->msg = $value;
	    return $this;
	}
	/**
	 * Getter for traces
	 *
	 * @return array
	 */
	public function getTraces() 
	{
	    return $this->traces;
	}
	/**
	 * Setter for traces
	 *
	 * @param array $value The traces
	 *
	 * @return ErrorLog
	 */
	public function setTraces(array $value) 
	{
	    $this->traces = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	protected function preSave()
	{
		if(trim($this->getKey()) === '')
			$this->setKey(md5(new UDate() . Core::getUser()));
	}
	/**
	 * Loging the exception
	 * 
	 * @param Exception $ex
	 * 
	 * @return ErrorLog
	 */
	public static function logException(Exception $ex)
	{
		$class = get_called_class();
		$log = new $class;
		$log->setCode($ex->getCode())
			->setMsg($ex->getMessage())
			->save();
		
		//add trace
		foreach($ex->getTrace() as $trace)
			ErrorLogTrace::addTrace($log, $trace['file'], $trace['line'], $funcName);
		return $log;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'errlog');
	
		DaoMap::setStringType('key','varchar', 50);
		DaoMap::setStringType('code','varchar', 10);
		DaoMap::setStringType('msg','varchar', 255);
	
		parent::__loadDaoMap();
	
		DaoMap::createIndex('key');
		DaoMap::createIndex('code');
	
		DaoMap::commit();
	}
}