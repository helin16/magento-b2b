<?php
/**
 * Common entity class
 *
 * @package Core
 * @subpackage Entity
 */
abstract class TreeEntityAbstract extends BaseEntityAbstract
{
    /**
     * how many digits of PER LEVEL
     * @var int
     */
    const POS_LENGTH_PER_LEVEL = 4;
    /**
     * The default separator for breadCrubms
     * @var string
     */
    const BREADCRUMBS_SEPARATOR = ' / ';
	/**
     * The parent category of this category
     * 
     * @var Category
     */
    protected $parent;
    /**
     * The position of the category with the category tree
     *
     * @var string
     */
    protected $position = 1;
    /**
     * The root category of this category
     * 
     * @var Category
     */
    protected $root;
    /**
     * getter position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }
    /**
     * setter position
     * 
     * @param string $position The new position
     * 
     * @return TreeEntityAbstract
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }
    /**
     * getter parent
     *
     * @return TreeEntityAbstract
     */
    public function getParent()
    {
        $this->loadManyToOne('parent');
        return $this->parent;
    }
    /**
     * setter parent
     * 
     * @param TreeEntityAbstract $parent The parent node
     * 
     * @return TreeEntityAbstract
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }
    /**
     * getter root
     *
     * @return TreeEntityAbstract
     */
    public function getRoot()
    {
        $this->loadManyToOne('root');
        return $this->root;
    }
    /**
     * setter parent
     * 
     * @param TreeEntityAbstract $root The root node
     * 
     * @return TreeEntityAbstract
     */
    public function setRoot($root)
    {
        $this->root = $root;
        return $this;
    }
    /**
     * Getting the next position for the new children of the provided parent
     *
     * @throws ServiceException
     * @return int
     */
    public function getNextPosition()
    {
        $parentPos = trim($this->getPosition());
        $sql="select position from " . strtolower(get_class($this)) . " where active = 1 and position like '" . $parentPos . str_repeat('_', self::POS_LENGTH_PER_LEVEL). "' order by position asc";
        $result = Dao::getResultsNative($sql);
        if(count($result) === 0)
        return $parentPos . str_repeat('0', self::POS_LENGTH_PER_LEVEL);
         
        $expectedAccountNos = array_map(create_function('$a', 'return "' . $parentPos . '".str_pad($a, ' . self::POS_LENGTH_PER_LEVEL . ', 0, STR_PAD_LEFT);'), range(0, str_repeat('9', self::POS_LENGTH_PER_LEVEL)));
        $usedAccountNos = array_map(create_function('$a', 'return $a["position"];'), $result);
        $unUsed = array_diff($expectedAccountNos, $usedAccountNos);
        sort($unUsed);
        if (count($unUsed) === 0)
        throw new ServiceException("Position over loaded (parentId = " . $this->getId() . ")!");
         
        return $unUsed[0];
    }
    public function postSave()
    {
        $class = get_class($this);
        if(!$this->getRoot() instanceof $class)
        {
            $fakeParent = new $class();
            $fakeParent->setProxyMode(true);
            $fakeParent->setId($this->getId());
            $this->setRoot($fakeParent);
            EntityDao::getInstance($class)->save($this);
        }
    }
	/**
	 * load the default elments of the base entity
	 */
	protected function __loadDaoMap()
	{
	    DaoMap::setManyToOne('root', get_class($this), null, true);
	    DaoMap::setManyToOne('parent', get_class($this), null, true);
		DaoMap::setStringType('position', 'varchar', 255, false, '1');
	    parent::__loadDaoMap();
	}
}

?>