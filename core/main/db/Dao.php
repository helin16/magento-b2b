<?php
/**
 * Class for executing DaoQuery objects and managing database connections
 *
 * @package    Core
 * @subpackage Dao
 * @author     lhe<helin16@gmail.com>
 */
abstract class Dao
{
    /**
     * Output result as objects
     * @var int
     */
    const AS_OBJECTS = 1;
    /**
     * Output result as an array
     * @var int
     */
    const AS_ARRAY = 2;
    /**
     * Output result as an associative array
     * @var int
     */
    const AS_ASSOC = 3;
    /**
     * Output result as xml
     * @var int
     */
    const AS_XML = 4;
    /**
     * The PDO
     * @var PDO
     */
    private static $_db = null;
    /**
     * The pagination stats
     *
     * @var array
     */
    private static $_pageStats = array('totalPages' => null, 'totalRows' => null, 'pageNumber' => null, 'pageSize' => DaoQuery::DEFAUTL_PAGE_SIZE);
    /**
     * Whether the Dao is running in Debug mode
     * @var bool
     */
    public static $debug = false;
    /**
     * Connect to the database
     */
    public static function connect()
    {
        // Only connect if we don't have a handle on the database
        if (!is_null(self::$_db))
        return self::$_db;
         
        try
        {
            // DSN FORMAT: "mysql:host=localhost;dbname=test"
            $dsn = Config::get('Database', 'Driver') . ':host=' . Config::get('Database', 'DBHost') . ';dbname=' . Config::get('Database', 'DB');
            self::$_db = new PDO($dsn, Config::get('Database','Username'), Config::get('Database','Password'), array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            self::$_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e)
        {
            throw new DaoException("Error (Dao::connect): " . $e->getMessage());
        }
    }
    /**
     * Start a transaction
     *
     * @return bool
     */
    public static function beginTransaction()
    {
        self::connect();
        return self::$_db->beginTransaction();
    }
    /**
     * Commit a transaction
     *
     * @return bool
     */
    public static function commitTransaction()
    {
        self::connect();
        return self::$_db->commit();
    }
    /**
     * Rollback a transaction
     *
     * @return bool
     */
    public static function rollbackTransaction()
    {
        self::connect();
        return self::$_db->rollBack();
    }
    /**
     * Internal function to calculate the paging stats for a paged select
     *
     * @param DaoQuery $qry     The query that we are running
     * @param array    $results The result that we've got from this DaoQuery
     *
     * @return array The page stats
     */
    private static function _calculatePageStats(DaoQuery $qry, $results)
    {
        if ($qry->isPaged())
        {
            $sql = 'select found_rows()';
            $stmt = self::$_db->prepare($sql);
            if (!$stmt->execute())
            return;
            $my = $stmt->fetch(PDO::FETCH_NUM);
            self::$_pageStats['totalRows'] = (int)$my[0];
            list(self::$_pageStats['pageNumber'], self::$_pageStats['pageSize']) = $qry->getPageStats();
            self::$_pageStats['totalPages'] = (int)ceil(self::$_pageStats['totalRows'] / self::$_pageStats['pageSize']);
        }
        else
        {
            self::$_pageStats['pageNumber'] = null;
            self::$_pageStats['totalRows'] = null;
            self::$_pageStats['totalPages'] = null;
            if (is_array($results))
            {
                self::$_pageStats['totalPages'] = 1;
                self::$_pageStats['totalRows'] = self::$_pageStats['pageSize'] = count($results);
            }
        }
        return self::$_pageStats;
    }
    /**
     * returning the pagination stats
     *
     * @return array
     */
    public static function getPageStats()
    {
        return self::$_pageStats;
    }
    /**
     * Find all objects within a DaoQuery
     *
     * @param DaoQuery $qry          The dao query
     * @param int      $pageNumber   The page number for pagination
     * @param int      $pageSize     The page size for pagination
     * @param array    $orderBy      The order by clause
     * @param int      $outputFormat The result output format: object, xml, array...
     *
     * @return array
     */
    public static function findAll(DaoQuery $qry, $pageNumber = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), $outputFormat = self::AS_OBJECTS)
    {
        self::connect();
        $results = self::findByCriteria($qry, '', array(), $pageNumber, $pageSize, $orderBy, $outputFormat);
        return $results;
    }
    /**
     * Retrieve an entity from the database by its primary key
     *
     * @param DaoQuery $qry          The dao query
     * @param int      $id           The ID of the entity
     * @param int      $outputFormat The result output format: object, xml, array...
     *
     * @return BaseEntityAbstract|null
     */
    public static function findById(DaoQuery $qry, $id, $outputFormat = self::AS_OBJECTS)
    {
        self::connect();
        DaoMap::loadMap($qry->getFocusClass());
        $results = self::findByCriteria($qry->setSelectActiveOnly(false), '`' . DaoMap::$map[strtolower($qry->getFocusClass())]['_']['alias'] . '`.`id`=?', array($id), null, DaoQuery::DEFAUTL_PAGE_SIZE, array(), $outputFormat);
        if (is_array($results) && sizeof($results) > 0)
        	return $results[0];
        if ($results instanceof SimpleXMLElement)
        	return $results;
        return null;
    }
    /**
     * Retrieve an entity from the database with a modified where clause
     *
     * @param DaoQuery $qry          The dao query
     * @param string   $criteria     The where clause
     * @param array    $params       The parameters
     * @param int      $pageNumber   The page number for pagination
     * @param int      $pageSize     The page size for pagination
     * @param array    $orderBy      The order by clause
     * @param int      $outputFormat The result output format: object, xml, array...
     *
     * @return array
     */
    public static function findByCriteria(DaoQuery $qry, $criteria, $params, $pageNumber = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, array $orderByParams = array(), $outputFormat = self::AS_OBJECTS)
    {
        self::connect();
        $qry->getPage($pageNumber, $pageSize);
        if(trim($criteria) !== '')
        	$qry = $qry->where($criteria);
        foreach ($orderByParams as $field => $direction)
        $qry = $qry->orderBy($field,$direction);
        $sql = $qry->generateForSelect();
        $results = self::_getResults($qry, $sql, $params, $outputFormat);
        return $results;
    }
    /**
     * Run a native SQL statement against the database and return the record
     *
     * @param string $sql       The sql statement
     * @param array  $params    The parameters
     * @param int    $pdoOption The option for fetching results: PDO::FETCH_ASSOC, PDO::FETCH_NUM...
     *
     * @return array
     */
    public static function getSingleResultNative($sql, array $params = array(), $pdoOption = PDO::FETCH_ASSOC)
    {
        $stmt = self::_execSql($sql, $params);
        $my = $stmt->fetch($pdoOption);
        return $my;
    }
    /**
     * Run a native SQL statement against the database and return the result set
     *
     * @param string $sql       The sql statement
     * @param array  $params    The parameters
     * @param int    $pdoOption The option for fetching results: PDO::FETCH_ASSOC, PDO::FETCH_NUM...
     *
     * @return array
     */
    public static function getResultsNative($sql, array $params = array(), $pdoOption = PDO::FETCH_ASSOC)
    {
        $stmt = self::_execSql($sql, $params);
        $results = $stmt->fetchAll($pdoOption);
        return $results;
    }
    /**
     * saving the entity
     *
     * @param BaseEntityAbstract $entity The entity that we are trying to populate for
     *
     * @return BaseEntityAbstract
     */
    public static function save(BaseEntityAbstract $entity)
    {
        $qry = new DaoQuery(get_class($entity));
        $entity->preSave();
        $params = self::_getParams($entity);
        $id = $entity->getId();
        if(trim($id) === '')
        {
            $now = new UDate();
            $params['active'] = 1;
            $entity->setActive(true);
            $params['created'] = $entity->getCreated();
            if(!$params['created'] instanceof UDate)
            {
                $params['created'] = $now;
                $entity->setCreated($now);
            }
            $params['updated'] = $entity->getUpdated();
            if(!$params['updated'] instanceof UDate)
            {
                $params['updated'] = $now;
                $entity->setUpdated($now);
            }
            self::_execSql($qry->generateForInsert(), $params, $id);
            $entity->setId($id);
        }
        else
        {
            $params['id'] = $id;
            $params['updated'] = new UDate();
            self::_execSql($qry->generateForUpdate(), $params);
        }
        $entity->postSave();
        return $entity;
    }
    /**
     * Getting the params for the Save fucntion
     *
     * @param BaseEntityAbstract $entity The entity that we are trying to translate
     *
     * @return array;
     */
    private static function _getParams(BaseEntityAbstract $entity)
    {
        $params = array();
        foreach (DaoMap::$map[strtolower(get_class($entity))] as $field => $properties)
        {
            //ignore metadata
            if (trim($field) === '_')
                continue;
             
            //if it's just a private data for this entity class
            if (!isset($properties['rel']))
                $params[$field] = self::_getProperty($entity, $field);
            //if it's a relationship then we need to consider repopulate object(s)
            else if ($properties['rel'] === DaoMap::MANY_TO_ONE || ($properties['rel'] === DaoMap::ONE_TO_ONE))
            {
                $childEntity = self::_getProperty($entity, $field);
                if ($childEntity instanceof BaseEntityAbstract)
                    $params[$field] = $childEntity->getId();
                else if ($properties['nullable'] === true)
                    $params[$field] = null;
                else
                    throw new DaoException('The field(' . $field . ') for "' . get_class($entity) . '" is NOT a BaseEntity!');
            }
        }
        return $params;
    }
    /**
     * Run an SQL statement and return the PDOStatement object
     *
     * @param string $sql          The sql statement
     * @param array  $params       The parameters for the sql
     * @param int    $lastInsertId The id of the last insert sql
     *
     * @return PDOStatement
     */
    private static function _execSql($sql, array $params = array(), &$lastInsertId = null)
    {
        self::connect();
        $stmt = self::$_db->prepare($sql);
        $flattenSql = $stmt->queryString . '. With Params(size=' . count($params) . '): ' .  print_r($params, true);
        try
        {
            if(self::$debug === true)
            echo '<pre>Debug for: ' . $flattenSql;
            $stmt->execute($params);
            $retVal = self::$_db->lastInsertId();
            if(is_numeric($retVal) && $retVal > 0)
            {
                $lastInsertId = $retVal;
                if(self::$debug === true)
                echo "\n Last Insert Id: " . $lastInsertId;
            }
            if(self::$debug === true)
            echo "\n============================</pre>";
        }
        catch (Exception $ex)
        {
            //             echo '<pre>';
            //             $stmt->debugDumpParams();
            //             die();
            throw new DaoException("Sql error(" . $ex->getMessage() . "): " . $flattenSql);
        }
        return $stmt;
    }
    /**
     * Retrieve a list of records from the database and convert the output to entities
     *
     * @param DaoQuery $qry          The dao query
     * @param string   $sql          The sql statement
     * @param array    $params       The parameters for the sql
     * @param int      $outputFormat The result output format: object, xml, array...
     *
     * @return array
     */
    private static function _getResults(DaoQuery $qry, $sql, array $params = array(), $outputFormat = self::AS_OBJECTS)
    {
        $stmt = self::_execSql($sql, $params);
        $results = array();
        switch ($outputFormat)
        {
            case self::AS_XML:
                {
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    self::_calculatePageStats($qry, $results);
                    $xml = new SimpleXMLElement('<results></results>');
                    foreach($results as $row)
                    {
                        self:: _formatAsXml($row, $qry->getFocusClass(), $xml);
                    }
                    $results = $xml;
                    break;
                }
            case self::AS_ASSOC:
                {
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    self::_calculatePageStats($qry, $results);
                    break;
                }
            case self::AS_ARRAY:
                {
                    $results = $stmt->fetchAll(PDO::FETCH_NUM);
                    self::_calculatePageStats($qry, $results);
                    break;
                }
            case self::AS_OBJECTS:
                {
                    $results = array();
                    while ($my = $stmt->fetch(PDO::FETCH_ASSOC))
                    $results[] = self::_objectify($qry, $my);
                    self::_calculatePageStats($qry, $results);
                    break;
                }
            default:
                throw new DaoException("Invalid output format provided: " . $outputFormat);
        }
        return $results;
    }
    /**
     * Set an object property via its setter or public property
     *
     * @param BaseEntityAbstract $entity The entity that we are trying to populate for
     * @param string             $field  The field that we are setting on
     * @param mixed              $value  The new value that we are trying to set into this new entity
     *
     * @return BaseEntityAbstract
     */
    private static function _setProperty(BaseEntityAbstract $entity, $field, $value)
    {
        $method = 'set' . ucwords($field);
        if (method_exists($entity, $method))
        {
            $entity->$method($value);
            return $entity;
        }
        $property = strtolower(substr($field, 0, 1)) . substr($field, 1);
        $entity->$property = &$value;
        return $entity;
    }
    /**
     * Get an object property via its getter or public property
     *
     * @param BaseEntityAbstract $entity The entity that we are trying to populate for
     * @param string             $field  The field that we are setting on
     *
     * @return mixed
     */
    private static function _getProperty(BaseEntityAbstract $entity, $field)
    {
        $method = 'get' . ucwords($field);
        if (method_exists($entity, $method))
        return $entity->$method();
        $property = strtolower(substr($field, 0, 1)) . substr($field, 1);
        return $entity->$property;
    }
    /**
     * Convert an array into a set of objects defined by a DaoQuery instance
     *
     * @param DaoQuery $qry The dao query
     * @param array    $row The result that we've got for the binding
     *
     * @return BaseEntityAbstract
     */
    private static function _objectify(DaoQuery $qry, array $row)
    {
        // Populate the focus object
        $fClass = $qry->getFocusClass();
        $newEntity = new $fClass;
        $newEntity->setId($row['id']);
        DaoMap::loadMap(strtolower($fClass));
        foreach (DaoMap::$map[strtolower($fClass)] as $field => $properties)
        {
            //ignore metadata
            if (trim($field) === '_')
            continue;
            //if it's just a private data for this entity class
            if (!isset($properties['rel']))
            $value = $row[$field];
            //if it's a relationship then we need to consider repopulate object(s)
            else if ($properties['rel'] === DaoMap::MANY_TO_ONE || ($properties['rel'] === DaoMap::ONE_TO_ONE))
            {
                //creates a empty object
                $id = $row[$field . 'Id'];
                $cls = DaoMap::$map[strtolower($fClass)][$field]['class'];
                $value = new $cls;
                $value->setId($id);
                $value->setProxyMode(true);
            }
            else if ($properties['rel'] === DaoMap::ONE_TO_MANY || ($properties['rel'] === DaoMap::MANY_TO_MANY))
            {
                $value = array(); //creates an empty array to stand out as an array
            }
            else
            continue;
            self::_setProperty($newEntity, $field, $value);
        }
        return $newEntity;
    }
    /**
     * Convert an entity into an XML string
     *
     * @param array            $row   The result row that we are trying to format
     * @param string           $class The class name of the entity
     * @param SimpleXMLElement $xml   The resultset xml
     *
     * @return SimpleXMLElement
     */
    private static function _formatAsXml($row, $class, SimpleXMLElement &$xml)
    {
        DaoMap::loadMap($class);
         
        // Populate the focus object
        $newRow = $xml->addChild('result');
        $newRow->addAttribute('class', $class);
         
        //add id element
        $idCol = $newRow->addChild('id');
        $idCol->addAttribute('value', $row['id']);
        foreach (DaoMap::$map[strtolower($class)] as $field => $properties)
        {
            //ignore metadata
            if (trim($field) === '_')
            continue;
            $newCol = $newRow->addChild($field);
            //if it's just a private data for this entity class
            if (!isset($properties['rel']))
            $newCol->addAttribute('value', $row[$field]);
            //if it's a relationship then we need to consider repopulate object(s)
            else if ($properties['rel'] === DaoMap::MANY_TO_ONE || ($properties['rel'] === DaoMap::ONE_TO_ONE))
            {
                $newCol->addAttribute('ref', $properties['rel']);
                $newCol->addAttribute('childClass', DaoMap::$map[strtolower($fClass)][$field]['class']);
                $newCol->addAttribute('childId', $row[$field . 'Id']);
            }
            else if ($properties['rel'] === DaoMap::ONE_TO_MANY || ($properties['rel'] === DaoMap::MANY_TO_MANY))
            {
                $newCol->addAttribute('ref', $properties['rel']);
                $newCol->addAttribute('childrenCount', 0);
            }
            else
            continue;
        }
        return $xml;
    }
    /**
     * Getting the total count for the search criteria
     *
     * @param DaoQuery $qry      The dao query
     * @param string   $criteria The where clause
     * @param array    $params   The parameters
     *
     * @return int
     */
    public static function countByCriteria(DaoQuery $qry, $criteria, $params = array())
    {
        self::connect();
        $qry->getPage(null, DaoQuery::DEFAUTL_PAGE_SIZE);
        $qry = $qry->where($criteria);
        $sql = $qry->generateForCount();
        $results = self::_getResults($qry, $sql, $params, self::AS_ARRAY);
        return (isset($results[0]) && isset($results[0][0])) ? $results[0][0] : 0;
    }
    /**
     * Updating a table for the search criteria
     *
     * @param DaoQuery $qry      The dao query
     * @param string   $criteria The where clause
     * @param array    $params   The parameters
     *
     * @return int
     */
    public static function updateByCriteria(DaoQuery $qry, $setClause, $criteria, $params = array())
    {
        self::connect();
        return Dao::_execSql('update `' . strtolower($qry->getFocusClass()) . '` set ' . $setClause . ' , updatedById = ' . Core::getUser()->getId() . ' where ' . $criteria, $params);
    }
    /**
     * delete a table for the search criteria
     *
     * @param DaoQuery $qry      The dao query
     * @param string   $criteria The where clause
     * @param array    $params   The parameters
     *
     * @return int
     */
    public static function deleteByCriteria($qry, $criteria, $params = array())
    {
        self::connect();
        return Dao::_execSql('delete from `' . strtolower($qry instanceof DaoQuery ? $qry->getFocusClass() :  trim($qry)) . '` where (' . $criteria . ')', $params);
    }
    /**
     * replace into
     * 
     * @param string $table   The table name
     * @param array  $columns The name of the columns
     * @param array  $values  The values that will match agains the column names
     * @param array  $params  The params
     * 
     * @return PDOStatement
     */
    public static function replaceInto($table, $columns, $values, $params = array())
    {
        self::connect();
        return Dao::_execSql('REPLACE INTO `' . $table . '` (`' . implode('`, `', $columns) . '`) values (' . implode(', ', $values) . ')', $params);
    }
    /**
     * Add a join table record for many to many relationship
     *
     * @param DaoQuery $qry         The dao query
     * @param string   $rightEntity The other entity
     * @param int      $leftId      The id of the left side entity
     * @param int      $rightId     The id of the right side entity
     *
     * @return int
     */
    public static function saveManyToManyJoin(DaoQuery $qry, $rightClass, $leftId, $rightId)
    {
        if(self::existsManyToManyJoin($qry, $rightClass, $leftId, $rightId) === true)
        return 0;
        return Dao::_execSql($qry->generateInsertForMTM($rightClass), array($leftId, $rightId, Core::getUser()->getId()));
    }
    /**
     * Remove a join table record for many to many relationship
     *
     * @param DaoQuery $qry         The dao query
     * @param string   $rightEntity The other entity
     * @param int      $leftId      The id of the left side entity
     * @param int      $rightId     The id of the right side entity
     *
     * @return int
     */
    public static function deleteManyToManyJoin(DaoQuery $qry, $rightClass, $leftId, $rightId)
    {
        $leftClass = $qry->getFocusClass();
        $qry->where(strtolower(substr($leftClass, 0, 1)) . substr($leftClass, 1) . 'Id = ? and ' . strtolower(substr($rightClass, 0, 1)) . substr($rightClass, 1) . 'Id = ?');
        return Dao::_execSql($qry->generateDeleteForMTM($rightClass), array($leftId, $rightId));
    }
    /**
     * finds if a join table record for many to many relationship exists
     *
     * @param DaoQuery $qry         The dao query
     * @param string   $rightEntity The other entity
     * @param int      $leftId      The id of the left side entity
     * @param int      $rightId     The id of the right side entity
     *
     * @return bool
     */
    public static function existsManyToManyJoin(DaoQuery $qry, $rightClass, $leftId, $rightId)
    {
        $leftClass = $qry->getFocusClass();
        $qry->where(strtolower(substr($leftClass, 0, 1)) . substr($leftClass, 1) . 'Id = ? and ' . strtolower(substr($rightClass, 0, 1)) . substr($rightClass, 1) . 'Id = ?');
        $results = self::_getResults($qry, $qry->generateSelectForMTM($rightClass), array($leftId, $rightId), self::AS_ARRAY);
        return count($results) > 0;
    }
}

?>
