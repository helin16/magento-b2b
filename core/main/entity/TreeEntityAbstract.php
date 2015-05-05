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
     * The default separator for PATH
     * @var string
     */
    const PATH_SEPARATOR = ',';
	/**
     * The parent category of this category
     *
     * @var Category
     */
    protected $parent;
    /**
     * The root category of this category
     *
     * @var Category
     */
    protected $root;
    /**
     * The path of the entity
     *
     * @var string
     */
    private $path;
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
     * Getter for Path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    /**
     * Setter for Path
     *
     * @param string $value The Path
     *
     * @return TreeEntityAbstract
     */
    public function setPath($value)
    {
        $this->path = $value;
        return $this;
    }
    /**
     * Getting the Path
     *
     * @return multitype:
     */
    public function getPaths()
    {
    	return explode(self::PATH_SEPARATOR, trim($this->getPath()));
    }
    /**
     * Getting the name paths
     *
     * @param bool $reset Whether to reset the cache
     *
     * @return multitype:|NULL
     */
    public function getNamePaths($reset = false)
    {
    	if(trim($this->getId()) === '')
    		return array();
    	$key = 'name_paths_' . $this->getId();
		if($reset === false && self::cacheExsits($key))
			return self::getCache($key);
		if(count($pathIds = $this->getPaths()) === 0)
			return array();
		$entityClass = get_class($this);
		$names = array();
		foreach(self::getAllByCriteria('id in (' . implode(', ', array_fill(0, count($pathIds), '?')). ')', $pathIds, false) as $node) {
			$names[$node->getId()] = trim($node->getName());
		}
		$return = array();
		foreach($pathIds as $id)
			$return = isset($names[$id]) ? $names[$id] : '';
		$return = array_filter($return);

		self::addCache($key, $return);
		return $return;
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntityAbstract::preSave()
     */
    public function preSave()
    {
    	$entityClass = get_class($this);
    	if($this->getParent() instanceof $entityClass) {
    		if($this->getParent()->getId() === $this->getId())
    			throw new EntityException('You can NOT save a ' . $entityClass . ' with parent of itself');
    	}
    	if($this->getRoot() instanceof $entityClass) {
    		if($this->getRoot()->getId() === $this->getId())
    			throw new EntityException('You can NOT save a ' . $entityClass . ' with root of itself');
    	}
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntityAbstract::postSave()
     */
    public function postSave()
    {
    	$entityClass = get_class($this);
    	if($this->getParent() instanceof $entityClass) {
    		$parentPathIds = $this->getParent()->getPaths();
    		$parentPathIds[] = $this->getId();
    		$root = $this->getParent()->getRoot() instanceof $entityClass ? $this->getParent()->getRoot() : $this->getParent();
    		$this->setRoot($root)
    			->setPath(($pathStrig = implode(self::PATH_SEPARATOR, $parentPathIds)));
    		self::updateByCriteria('path = ?, rootId = ?', 'id = ?', array($pathStrig, $root->getId(), $this->getId()));
    	} else {
    		$this->setRoot(null)
    			->setPath(($pathStrig = $this->getId()));
    		self::updateByCriteria('path = ?, rootId = null', 'id = ?', array($pathStrig, $this->getId()));
    	}
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntityAbstract::getJson()
     */
    public function getJson($extra = array(), $reset = false)
    {
    	$array = $extra;
    	if(!$this->isJsonLoaded($reset))
    	{
    		$entityClass = get_class($this);
    		$array['breadCrumbs'] = array('ids' => $this->getPaths(), 'names' => $this->getNamePaths($reset));
    		$array['parent'] = $this->getParent() instanceof $entityClass ? $this->getParent()->getJson(array(), $reset) : array();
    		$array['root'] = $this->getRoot() instanceof $entityClass && $this->getRoot()->getId() !== $this->getId() ? $this->getRoot()->getJson(array(), $reset) : $array;
    	}
    	return parent::getJson($array, $reset);
    }
	/**
	 * load the default elments of the base entity
	 */
	protected function __loadDaoMap()
	{
	    DaoMap::setManyToOne('root', get_class($this), null, true);
	    DaoMap::setManyToOne('parent', get_class($this), null, true);
		DaoMap::setStringType('path', 'varchar', 255);
	    parent::__loadDaoMap();
	}
}

?>