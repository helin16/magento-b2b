<?php
/**
 * Class for generating meta data for use by a DaoQuery instance
 *
 * @package    Core
 * @subpackage Dao
 * @author     lhe<helin16@gmail.com>
 */
abstract class DaoMap
{
    /**
     * Used for one-to-one to detirmed where the matching id is
     * @var bool
     */
    const RELATION_OWNER = true;
    /**
     * sed for join tables
     * @var int
     */
    const LEFT_SIDE = 1;
    /**
     * sed for join tables
     * @var int
     */
    const RIGHT_SIDE = 2;
    /**
     * Relationship type for ONE to ONE
     * @var int
     */
    const ONE_TO_ONE = 1;
    /**
     * Relationship type for ONE_TO_MANY
     * @var int
     */
    const ONE_TO_MANY = 2;
    /**
     * Relationship type for MANY_TO_ONE
     * @var int
     */
    const MANY_TO_ONE = 3;
    /**
     * Relationship type for MANY_TO_MANY
     * @var int
     */
    const MANY_TO_MANY = 4;
    /**
     * The sorting order for asc
     * @var string
     */
    const SORT_ASC = 'asc';
    /**
     * The sorting order for desc
     * @var string
     */
    const SORT_DESC = 'desc';
    /**
     * The entity map holder during the runtime
     * @var array
     */
    public static $map = array();
    /**
     * The active class name
     * @var string
     */
    private static $_activeClassRaw = null;
    /**
     * The temp may used between begin() and commit()
     * @var array
     */
    private static $_tempMap = array();

    /**
     * Check if the dao map has been generated for an entity or class name
     *
     * @param BaseEntityAbstract|string $entityOrClass The BaseEntityAbstract entity or the classname of the entity
     *
     * @return bool
     */
    public static function hasMap($entityOrClass)
    {
        if (is_string($entityOrClass))
        {
            $entityOrClass = strtolower($entityOrClass);
            return isset(self::$map[$entityOrClass]);
        }
        if ($entityOrClass instanceof BaseEntityAbstract)
        return isset(self::$map[strtolower(get_class($entityOrClass))]);
        return false;
    }
    /**
     * Load the internal Dao map for a given entity class
     *
     * @param string $class The classname of the entity that we are trying to load map for
     */
    public static function loadMap($class)
    {
        if (!DaoMap::hasMap($class))
        {
            $obj = new $class;
            $obj->__loadDaoMap();
        }
    }
    /**
     * Start a DaoMap transaction
     *
     * @param BaseEntityAbstract $entity The entity that we are trying to load map for
     */
    public static function begin(BaseEntityAbstract $entity, $alias = null)
    {
        self::$_activeClassRaw = get_class($entity);
        $activeClass = strtolower(self::$_activeClassRaw);
        self::$_tempMap[$activeClass] = array();
        if (is_null($alias))
        $alias = $activeClass;
        self::$_tempMap[$activeClass]['_']['alias'] = $alias;
        self::$_tempMap[$activeClass]['_']['sort'] = null;
    }
    /**
     * Set the default sort order to apply when querying this entity
     *
     * @param string $field     Which field that we are sorting on
     * @param string $direction Which direction that we are sorting on
     */
    public static function defaultSortOrder($field, $direction = DaoMap::SORT_ASC)
    {
        self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['sort'] = array($field, $direction);
    }
    /**
     * Register a one-to-many relationship
     *
     * @param string $field       The field name of the entity which is the foreign key in this focus class
     * @param string $entityClass The th
     * @param string $alias
     */
    public static function setOneToMany($field, $entityClass, $alias = null)
    {
        if (is_null($alias))
        $alias = $field;
        self::$_tempMap[strtolower(self::$_activeClassRaw)][$field] = array(
                        'type' => 'int',
                        'size' => 10,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => 0,
                        'class' => $entityClass,
                        'alias' => $alias,
                        'rel' => self::ONE_TO_MANY
        );
    }
    /**
     * Register a one-to-one relationship
     *
     * @param string $field
     * @param string $entityClass
     * @param bool $isOwner DaoMap::RELATION_OWNER if $this contains an id field mapping to the other end of the relationship
     * @param string $alias
     */
    public static function setOneToOne($field, $entityClass, $isOwner, $alias = null, $nullable = false)
    {
        if (is_null($alias))
        $alias = $field;
        self::$_tempMap[strtolower(self::$_activeClassRaw)][$field] = array(
                        'type' => 'int',
                        'size' => 10,
                        'unsigned' => true,
                        'nullable' => ($isOwner) ? $nullable : false,
                        'default' => 0,
                        'class' => $entityClass,
                        'alias' => $alias,
                        'owner' => $isOwner,
                        'rel' => self::ONE_TO_ONE
        );
        if ($isOwner)
        self::createIndex($field);
    }
    /**
     * Register a many-to-many relationship
     *
     * @param string $field
     * @param string $entityClass
     * @param int $side DaoMap::LEFT_SIDE | DaoMap::RIGHT_SIDE
     * @param string $alias
     */
    public static function setManyToMany($field, $entityClass, $side, $alias = null, $nullable = false)
    {
        if (is_null($alias))
        $alias = $field;
        self::$_tempMap[strtolower(self::$_activeClassRaw)][$field] = array(
                        'type' => 'int',
                        'size' => 10,
                        'unsigned' => true,
                        'nullable' => $nullable,
                        'default' => 0,
                        'class' => $entityClass,
                        'alias' => $alias,
                        'side' => $side,
                        'rel' => self::MANY_TO_MANY
        );
    }
    /**
     * Register a many-to-one relationship
     *
     * @param string $field
     * @param string $entityClass
     * @param string $alias
     */
    public static function setManyToOne($field, $entityClass, $alias = null, $nullable = false)
    {
        if (is_null($alias))
        $alias = $field;
        self::$_tempMap[strtolower(self::$_activeClassRaw)][$field] = array(
                        'type' => 'int',
                        'size' => 10,
                        'unsigned' => true,
                        'nullable' => $nullable,
                        'default' => 0,
                        'class' => $entityClass,
                        'alias' => $alias,
                        'rel' => self::MANY_TO_ONE
        );
        self::createIndex($field);
    }
    /**
     * Register a string type
     *
     * @param string $field
     * @param string $dataType
     * @param int $size
     * @param bool $nullable
     * @param string $defaultValue
     */
    public static function setStringType($field, $dataType='varchar', $size=50, $nullable=false, $defaultValue='')
    {
        self::$_tempMap[strtolower(self::$_activeClassRaw)][$field] = array(
                        'type' => $dataType,
                        'size' => $size,
                        'nullable' => $nullable,
                        'default' => $defaultValue
        );
    }
    /**
     * Register an integer type
     *
     * @param string $field
     * @param string $dataType
     * @param int $size
     * @param bool $unsigned
     * @param bool $nullable
     * @param string $defaultValue
     */
    public static function setIntType($field, $dataType='int', $size=10, $unsigned=true, $nullable=false, $defaultValue=0, $class='')
    {
        self::$_tempMap[strtolower(self::$_activeClassRaw)][$field] = array(
                        'type' => $dataType,
                        'size' => $size,
                        'unsigned' => $unsigned,
                        'nullable' => $nullable,
                        'default' => $defaultValue
        );
    }
    /**
     * Register a boolean type
     *
     * @param string $field
     * @param string $dataType
     * @param int $defaultValue
     */
    public static function setBoolType($field, $dataType='bool', $defaultValue=0)
    {
        self::$_tempMap[strtolower(self::$_activeClassRaw)][$field] = array(
                        'type' => $dataType,
                        'default' => $defaultValue
        );
    }
    /**
     * Register a date type
     *
     * @param string $field
     * @param string $dataType
     * @param bool $nullable
     * @param string $defaultValue
     */
    public static function setDateType($field, $dataType='datetime', $nullable=false, $defaultValue='0001-01-01 00:00:00')
    {
        self::$_tempMap[strtolower(self::$_activeClassRaw)][$field] = array(
                        'type' => $dataType,
                        'nullable' => $nullable,
                        'default' => $defaultValue
        );
    }
    /**
     * Register which properties on an entity are searchable. Takes multiple strings as parameter
     *
     * @param string
     */
    public static function setSearchFields()
    {
        self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['search'] = func_get_args();
    }
    /**
     * Set the base query that should be built off every time
     *
     * @param string $query
     */
    public static function setBaseQuery($query)
    {
        self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['base'] = $query;
    }
    /**
     * Register which filters the entity will respond to
     *
     * @param string
     * @param string
     */
    public static function createFilter($filterName, $filterClause)
    {
        self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['filters'][$filterName] = $filterClause;
    }
    /**
     * Create an index. Takes multiple strings as parameter
     *
     * @param string
     */
    public static function createIndex()
    {
        if (!isset(self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['index']))
        self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['index'] = array();
        self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['index'][] = func_get_args();
    }
     
    /**
     * Create a unique index. Takes multiple strings as parameter
     *
     * @param string
     */
    public static function createUniqueIndex()
    {
        if (!isset(self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['unique']))
        self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['unique'] = array();
        self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['unique'][] = func_get_args();
    }
    /**
     * Link a table to a tablespace
     *
     * @param string
     */
    public static function useTablespace($tablespace)
    {
        self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['tablespace'] = $tablespace;
    }
    /**
     * Specify which special case storage engine to use for this entity
     *
     * @param string $engine eg. Federated
     * @param string $confSection eg. LogDatabase
     */
    public static function storageEngine($engine, $confSection)
    {
        self::$_tempMap[strtolower(self::$_activeClassRaw)]['_']['engine'] = array($engine, $confSection);
    }
    /**
     * Commit the data map to the internal hash table
     */
    public static function commit()
    {
        // Copy the temp data into the live properties
        self::$map[strtolower(self::$_activeClassRaw)] = self::$_tempMap[strtolower(self::$_activeClassRaw)];
         
        // Reset the temp variables
        self::$_tempMap = array();
    }
}

?>
