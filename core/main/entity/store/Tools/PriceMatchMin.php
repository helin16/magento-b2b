<?php
/**
 * Entity for PriceMatchMin
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class PriceMatchMin extends BaseEntityAbstract
{
	/**
	 * The record for PriceMatchMin
	 * 
	 * @var PriceMatchRecord
	 */
	protected $record;
	/**
	 * The sku of the PriceMatchMin
	 * 
	 * @var string
	 */
	private $sku;
	
	/**
	 * Getter for record
	 *
	 * @return PriceMatchRecord
	 */
	public function getRecord()
	{
		$this->loadManyToOne('record');
	    return $this->record;
	}
	/**
	 * Setter for record
	 *
	 * @param PriceMatchRecord or '' $value The record
	 *
	 * @return PriceMatchMin
	 */
	public function setRecord(PriceMatchRecord $value = null)
	{
	    $this->record= $value;
	    return $this;
	}
	/**
	 * Getter for sku
	 *
	 * @return string
	 */
	public function getSku()
	{
		return $this->sku;
	}
	/**
	 * Setter for sku
	 *
	 * @param string $value The sku
	 *
	 * @return PriceMatchMin
	 */
	public function setSku($value)
	{
		$this->sku= $value;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'price_match_min');
		DaoMap::setStringType('sku', 'varchar', 50);
		DaoMap::setManyToOne('record', 'PriceMatchRecord', 'price_match_min_record', true);
		parent::__loadDaoMap();
		
		DaoMap::createIndex('sku');
		DaoMap::commit();
	}
	/**
	 * create function for PriceMatchMin
	 * 
	 * @param PriceMatchRecord $record
	 * @param string $sku
	 * @throws Exception
	 */
	public static function create($sku, PriceMatchRecord $record = null)
	{
		if(trim($sku) === '')
			throw new Exception('sku cannot be empty');
		if(!$record instanceof PriceMatchRecord && trim($record) !== '')
			throw new Exception('record must be instance of PriceMatchRecord or empty string');
		$entity = ($i = self::getBySku($sku, false)) instanceof self ? $i : new self();
		$entity->setSku($sku)->setRecord($record)->setActive(true)->save();
		return $entity;
	}
	/**
	 * get min record by sku
	 * 
	 * @param unknown $sku
	 * @param string $activeOnly
	 * @throws Exception
	 * @return Ambigous <string, Ambigous>
	 */
	public static function getBySku($sku, $activeOnly = true)
	{
		if(trim($sku) === '')
			throw new Exception('sku cannot be empty');
		$result = self::getAllByCriteria('sku = ?', array(trim($sku)), $activeOnly, 1, 1, array('id'=> 'desc'));
		return count($result) > 0 ? ($result[0]->sku === '' ? '' : $result[0]) : '';
	}
	public function getMin()
	{
		$sku = trim($this->getSku());
		$product = Product::getBySku($sku);
		
		if($product instanceof Product)
		{
			$rule = ProductPriceMatchRule::getByProduct($product);
			if($rule instanceof ProductPriceMatchRule)
			{
				$company = $rule->getCompany();
				
				$where = array(1);
				$params = array();
				
				$where[] = "minId = ? ";
				$params[] = $this->getId();
				
				$companies = $company->getAllAlias();
				$companyIds = array_map(create_function('$a', 'return $a->getId();'), $companies);
				$where[] = 'companyId IN ('.implode(", ", array_fill(0, count($companyIds), "?")).')';
				$params = array_merge($params, $companyIds);
				
				//calculate real price range
				$records = PriceMatchRecord::getAllByCriteria(implode(' AND ', $where), $params, true, 1, 1, array('price'=>'asc'));
				if(count($records) > 0)
					$this->setRecord($records[0])->setActive(true)->save();
			}
		}
		return $this->getRecord();
	}
}