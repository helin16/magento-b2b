<?php
/**
 * Entity for AccountingCode
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class AccountingCode extends BaseEntityAbstract
{
	const TYPE_ID_ASSET = 1;
	const TYPE_ID_REVENUE = 4;
	const TYPE_ID_COST = 5;
	/**
	 * The code of the accounting code
	 * 
	 * @var int
	 */
	private $code;
	/**
	 * The type of the accounting code
	 * 
	 * @var int
	 */
	private $typeId;
	/**
	 * The Description of the accounting code
	 * @var string
	 */
	private $description;
	/**
	 * Getter for code
	 *
	 * @return AccountingCode
	 */
	public function getCode()
	{
		return $this->code;
	}
	/**
	 * Setter for name
	 *
	 * @param string $value The name
	 *
	 * @return AccountingCode
	 */
	public function setCode($value)
	{
		$this->code = $value;
		return $this;
	}
	/**
	 * Getter for typeId
	 *
	 * @return AccountingCode
	 */
	public function getTypeId()
	{
		return $this->typeId;
	}
	/**
	 * Setter for type
	 *
	 * @param string $value The name
	 *
	 * @return AccountingCode
	 */
	public function setTypeId($value)
	{
		$this->typeId = $value;
		return $this;
	}
	/**
	 * Getter for description
	 *
	 * @return AccountingCode
	 */
	public function getDescription()
	{
		return $this->description;
	}
	/**
	 * Setter for description
	 *
	 * @param string $value The description
	 *
	 * @return AccountingCode
	 */
	public function setDescription($value)
	{
		$this->description = $value;
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::__toString()
	 */
	public function __toString()
	{
		return trim($this->getName());
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'sell_item');
	
		DaoMap::setIntType('code', 'int', '10');
		DaoMap::setIntType('typeId', 'int', '10');
		DaoMap::setStringType('description', 'varchar', '255');
		
		parent::__loadDaoMap();
		DaoMap::createIndex('code');
		DaoMap::createIndex('typeId');
		DaoMap::commit();
	}
	/**
	 * creating accountcode
	 * 
	 * @param int    $typeId
	 * @param int    $code
	 * @param string $description
	 * 
	 * @return Ambigous <BaseEntityAbstract, GenericDAO>
	 */
	public static function create($typeId, $code, $description = '')
	{
		$item = new AccountingCode();
		return $item->setTypeId($typeId)
			->setCode($code)
			->setDescription(trim($description))
			->save();
	}
}