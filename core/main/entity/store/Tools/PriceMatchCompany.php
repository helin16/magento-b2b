<?php
/**
 * Entity for PriceMatchCompany
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class PriceMatchCompany extends BaseEntityAbstract
{
	/**
	 * The name of the Company
	 * 
	 * @var string
	 */
	private $companyName;
	
	/**
	 * The Alias of the company
	 * 
	 * @var string
	 */
	private $companyAlias;
	
	/**
	 * Getter for Company Name
	 *
	 * @return string
	 */
	public function getCompanyName() 
	{
	    return $this->companyName;
	}
	
	/**
	 * Setter for Company Name
	 *
	 * @param string $companyName The name
	 *
	 * @return PriceMatchCompany
	 */
	public function setCompanyName($companyName) 
	{
	    $this->companyName = $companyName;
	    return $this;
	}
	
	/**
	 * Getter for Company Alias
	 *
	 * @return string
	 */
	public function getCompanyAlias() 
	{
	    return $this->companyAlias;
	}
	
	/**
	 * Setter for Company Alias
	 *
	 * @param string $companyAlias The name
	 *
	 * @return PriceMatchCompany
	 */
	public function setCompanyAlias($companyAlias) 
	{
	    $this->companyAlias = $companyAlias;
	    return $this;
	}
	
	/**
	 * get all PriceMatchCompany(s)
	 *
	 * @param bool  $searchActiveOnly
	 * @param int   $pageNo
	 * @param int   $pageSize
	 * @param array $orderBy
	 *
	 * @return Ambigous <multitype:, multitype:BaseEntityAbstract >
	 */
	public static function findAll($searchActiveOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array())
	{
		return FactoryAbastract::dao(__CLASS__)->findAll($searchActiveOnly, $pageNo, $pageSize, $orderBy);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'price_match_company');
		DaoMap::setStringType('companyName', 'varchar', 50);
		DaoMap::setStringType('companyAlias', 'varchar', 255);
		parent::__loadDaoMap();
		
		DaoMap::createIndex('companyName');
		DaoMap::createIndex('companyAlias');
		DaoMap::commit();
	}
}