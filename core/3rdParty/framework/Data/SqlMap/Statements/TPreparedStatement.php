<?php
/**
 * TPreparedStatement class file.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @link http://www.pradosoft.com/
 * @copyright Copyright &copy; 2005-2014 PradoSoft
 * @license http://www.pradosoft.com/license/
 * @package System.Data.SqlMap.Statements
 */

/**
 * TpreparedStatement class.
 *
 * @author Wei Zhuo <weizho[at]gmail[dot]com>
 * @package System.Data.SqlMap.Statements
 * @since 3.1
 */
class TPreparedStatement extends TComponent
{
	private $_sqlString='';
	private $_parameterNames;
	private $_parameterValues;

	public function getPreparedSql(){ return $this->_sqlString; }
	public function setPreparedSql($value){ $this->_sqlString = $value; }

	public function getParameterNames($needed = true)
	{
		if (!$this->_parameterNames and $needed)
			$this->_parameterNames = new TList;
		return $this->_parameterNames;
	}

	public function setParameterNames($value){ $this->_parameterNames = $value; }

	public function getParameterValues($needed = true)
	{
		if (!$this->_parameterValues and $needed)
			$this->_parameterValues=new TMap;
		return $this->_parameterValues;
	}

	public function setParameterValues($value){ $this->_parameterValues = $value; }

	public function __sleep()
	{
		$exprops = array(); $cn = __CLASS__;
		if (!$this->_parameterNames or !$this->_parameterNames->getCount()) $exprops[] = "\0$cn\0_parameterNames";
		if (!$this->_parameterValues or !$this->_parameterValues->getCount()) $exprops[] = "\0$cn\0_parameterValues";
		return array_diff(parent::__sleep(),$exprops);
	}
}

