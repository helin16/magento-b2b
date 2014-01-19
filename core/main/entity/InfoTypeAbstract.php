<?php
class InfoTypeAbstract extends BaseEntityAbstract
{
	/**
	 * The name of the type
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * Getter for the name
	 * 
	 * @return string
	 */
	public function getName() 
	{
	    return $this->name;
	}
	/**
	 * Setter for the name
	 * 
	 * @param string $value The name of the type
	 * 
	 * @return InfoTypeAbstract
	 */
	public function setName($value) 
	{
	    $this->name = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::setStringType('name','varchar', 100);
		parent::__loadDaoMap();
		DaoMap::createIndex('name');
		DaoMap::commit();
	}
}