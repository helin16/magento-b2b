<?php
/**
 * Session Entity - storing the session data in the database
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Session extends BaseEntityAbstract
{
    /**
     * The session ID
     * 
     * @var string
     */
    private $key;
    /**
     * The session data
     * 
     * @var string
     */
    private $data;
    /**
     * Getting the sesison ID
     * 
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
    /**
     * Setter for the session ID
     * 
     * @param string $key The 
     * 
     * @return string
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }
    /**
     * Getter for the session data
     * 
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * Setter for the session data
     * 
     * @param string $data The session data
     * 
     * @return Session
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::toString()
	 */
	public function toString()
	{
        return $tis->data;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'sess');
		DaoMap::setStringType('key', 'varchar', 32);
		DaoMap::setStringType('data', 'longtext');
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('key');
		DaoMap::commit();
	}
}

?>