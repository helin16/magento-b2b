<?php
class ErrorLogTrace extends BaseEntityAbstract
{
	/**
	 * The path of the file
	 * 
	 * @var string
	 */
	private $file;
	/**
	 * The line number of the exception file
	 * 
	 * @var number
	 */
	private $lineNo;
	/**
	 * The function name of the exception file
	 * 
	 * @var string
	 */
	private $funcName;
	/**
	 * The args name 
	 * 
	 * @var string
	 */
	private $args;
	/**
	 * The error log
	 * 
	 * @var ErrorLog
	 */
	protected $errLog;
	/**
	 * Adding the trace to an errorlog
	 * 
	 * @param ErrorLog $log
	 * @param string   $file
	 * @param number   $lineNo
	 * @param string   $funcName
	 * @param array    $args
	 * 
	 * @return ErrorLog
	 */
	public static function addTrace(ErrorLog &$log, $file, $lineNo, $funcName, array $args = array())
	{
		$trace = new ErrorLogTrace();
		$trace->setErrLog($log)
			->setFile($file)
			->setLineNo($lineNo)
			->setFuncName($funcName)
			->setArgs($args)-
			->save();
		return $log;
	}
	/**
	 * Getter for file
	 *
	 * @return 
	 */
	public function getFile() 
	{
	    return $this->file;
	}
	/**
	 * Setter for file
	 *
	 * @param string $value The file
	 *
	 * @return ErrorLogTrace
	 */
	public function setFile($value) 
	{
	    $this->file = $value;
	    return $this;
	}
	/**
	 * Getter for lineNo
	 *
	 * @return number
	 */
	public function getLineNo() 
	{
	    return $this->lineNo;
	}
	/**
	 * Setter for lineNo
	 *
	 * @param number $value The lineNo
	 *
	 * @return ErrorLogTrace
	 */
	public function setLineNo($value) 
	{
	    $this->lineNo = $value;
	    return $this;
	}
	/**
	 * Getter for funcName
	 *
	 * @return ErrorLogTrace
	 */
	public function getFuncName() 
	{
	    return $this->funcName;
	}
	/**
	 * Setter for funcName
	 *
	 * @param string $value The funcName
	 *
	 * @return ErrorLogTrace
	 */
	public function setFuncName($value) 
	{
	    $this->funcName = $value;
	    return $this;
	}
	/**
	 * Getter for args
	 *
	 * @return array
	 */
	public function getArgs() 
	{
	    return (is_string($this->args) ? json_decode($this->args) : $this->args);
	}
	/**
	 * Setter for args
	 *
	 * @param mixed $value The args
	 *
	 * @return ErrorLogTrace
	 */
	public function setArgs($value) 
	{
		$temp = array();
		if(is_array($value))
		{
			foreach($value as $v)
				$temp[] = array('class' => get_class($v) ,  "value" => trim($v));
			$value = $temp;
		}
	    $this->args = (is_string($value) ? json_decode($value) : $value);
	    return $this;
	}
	/**
	 * Getter for errLog
	 *
	 * @return ErrorLog
	 */
	public function getErrLog() 
	{
		$this->loadManyToOne('errLog');
	    return $this->errLog;
	}
	/**
	 * Setter for errLog
	 *
	 * @param ErrorLog $value The errLog
	 *
	 * @return ErrorLogTrace
	 */
	public function setErrLog($value) 
	{
	    $this->errLog = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	protected function preSave()
	{
		if(!is_string($this->args))
			$this->args = json_encode($this->args);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'errlogtrace');
	
		DaoMap::setManyToOne('errLog', 'ErrorLog', 'errlog');
		DaoMap::setStringType('file','varchar', 255);
		DaoMap::setIntType('lineNo');
		DaoMap::setStringType('funcName','varchar', 10);
		DaoMap::setStringType('args','varchar', 255);
	
		parent::__loadDaoMap();
	
		DaoMap::createIndex('file');
		DaoMap::createIndex('lineNo');
		DaoMap::createIndex('funcName');
	
		DaoMap::commit();
	}
}