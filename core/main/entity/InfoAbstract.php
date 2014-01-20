<?php
class InfoAbstract extends BaseEntityAbstract
{
	/**
	 * The value of the information
	 * 
	 * @var string
	 */
	private $value;
	/**
	 * The type of the information
	 * 
	 * @var InfoTypeAbstract
	 */
	protected $type;
	/**
	 * The entity this information is for
	 * 
	 * @var InfoEntityAbstract
	 */
	protected $entity;
	/**
	 * Getter for the value
	 * 
	 * @return string
	 */
	public function getValue() 
	{
	    return $this->value;
	}
	/**
	 * Setter for the value
	 * 
	 * @param string $value The value for the information
	 * 
	 * @return InfoAbstract
	 */
	public function setValue($value) 
	{
	    $this->value = $value;
	    return $this;
	}
	/**
	 * Getter for the Type
	 * 
	 * @return InfoTypeAbstract
	 */
	public function getType() 
	{
		$this->loadManyToOne('type');
	    return $this->type;
	}
	/**
	 * Setter for the type
	 * 
	 * @param InfoTypeAbstract $value The type of the information
	 * 
	 * @return InfoAbstract
	 */
	public function setType($value) 
	{
	    $this->type = $value;
	    return $this;
	}
	/**
	 * Getter for the entity
	 * 
	 * @return InfoEntityAbstract
	 */
	public function getEntity() 
	{
		$this->loadManyToOne('entity');
	    return $this->entity;
	}
	/**
	 * Setter for the enttiy
	 * 
	 * @param InfoEntityAbstract $value The entity 
	 * 
	 * @return InfoAbstract
	 */
	public function setEntity($value) 
	{
	    $this->entity = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::setStringType('value', 'varchar', 255);
		$entityClass = str_replace('Info', '', get_class($this));
		DaoMap::setManyToOne(StringUtilsAbstract::lcFirst($entityClass), $entityClass, strtolower(get_class($this)) . '_entity');
		DaoMap::setManyToOne('type', get_class($this) . 'Type', strtolower(get_class($this)) . '_info_type');
		parent::__loadDaoMap();
	}
}