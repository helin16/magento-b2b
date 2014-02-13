<?php
/**
 * Class for generating SQL statements for use by the Dao pattern
 *
 * @package    Core
 * @subpackage Dao
 * @author     lhe<helin16@gmail.com>
 */
class DaoQuery
{
    /**
     * Default page size for the pagination
     * @var int
     */
    const DEFAUTL_PAGE_SIZE = 30;
    /**
     * Default join type
     * @var string
     */
    const DEFAULT_JOIN_TYPE = 'inner join';
    /**
     * Get focus entity name
     * @var string
     */
    private $_focus = null;
    /**
     * The page number of the pagination
     * @var int
     */
    private $_pageNumber = null;
    /**
     * The page size of the pagination
     * @var int
     */
    private $_pageSize = self::DEFAUTL_PAGE_SIZE;
    /**
     * whether we are trying to get the distinct values
     * @var bool
     */
    private $_distinct = false;
    /**
     * The where clause of the query
     * @var array
     */
    private $_whereClause = array();
    /**
     * the order by clause
     * @var array
     */
    private $_orderBy = array();
    /**
     * tracks the joins, to prevent we don't do the same join again
     * @var array
     */
    private $_joins = array();
    /**
     * selecting active record only
     * @var bool
     */
    private $_selectActiveOnly = true;
    /**
     * Creates a new DaoQuery, initialised to a focus object
     *
     * @param string $entityName The name of the focus entity
     * @param int    $pageNumber The page number of the pagination, when NULL it's no pagination
     * @param int    $pageSize   The page size of the pagination
     * @param bool   $distinct   Whether we are getting the unique values from the query
     */
    public function __construct($entityName, $pageNumber = null, $pageSize = self::DEFAUTL_PAGE_SIZE, $distinct = true)
    {
        $this->_focus = $entityName;
        $this->getPage($pageNumber, $pageSize)
        	->distinct($distinct);
        DaoMap::loadMap($this->_focus);
    }
    /**
     * Setter for selectActiveOnly
     * 
     * @param string $selectActiveOnly
     * 
     * @return DaoQuery
     */
    public function setSelectActiveOnly($selectActiveOnly = true)
    {
    	$this->_selectActiveOnly = $selectActiveOnly;
    	return $this;
    }
    /**
     * Returns the class name used to instantiate this DaoQuery instance
     *
     * @return string
     */
    public function getFocusClass()
    {
        return $this->_focus;
    }
    /**
     * Set the distinct behaviour on a select query
     *
     * @param bool $bool Whether we are getting the unique values from the query
     *
     * @return DaoQuery
     */
    public function distinct($bool)
    {
        $this->_distinct = (bool)$bool;
        return $this;
    }
    /**
     * Set a relationship to eager load for performance reasons
     *
     * @param string $relationship The relationship for the entities: UserAccount.person
     *
     * @return DaoQuery
     */
    public function eagerLoad($relationship, $joinType = self::DEFAULT_JOIN_TYPE, $alias = null, $overrideCond = '')
    {
        list($joinClass, $joinField) = explode('.', $relationship);
        DaoMap::loadMap($joinClass);
        if(!isset(DaoMap::$map[strtolower($joinClass)]) || !isset(DaoMap::$map[strtolower($joinClass)][$joinField]))
            throw new DaoException('Invalid relationship for: ' . $relationship);
        $alias = ($alias = trim($alias)) === '' ? DaoMap::$map[strtolower($joinClass)][$joinField]['alias'] : $alias;
        $this->_buildJoin($joinField, $joinClass, $alias, $joinType, $overrideCond);
        return $this;
    }
    /**
     * Set the order by clauses on the query
     *
     * @param string $field     Which field we are ordering on
     * @param string $direction Which direction of ordering is ASC or DESC
     *
     * @return DaoQuery
     */
    public function orderBy($field, $direction = DaoMap::SORT_ASC)
    {
        $this->_orderBy[$field] = $direction;
        return $this;
    }
    /**
     * Set the where clause on the query
     *
     * @param string $clause
     */
    public function where($clause)
    {
        if(!in_array($clause, $this->_whereClause))
        $this->_whereClause[] = $clause;
        return $this;
    }
    /**
     * Set which page number should return in the results
     *
     * @param int $pageNumber The new page number for the pagination of the result
     * @param int $pageSize   The new page size for the pagination of the result
     *
     * @return DaoQuery
     */
    public function getPage($pageNumber = null, $pageSize = self::DEFAUTL_PAGE_SIZE)
    {
        if (!is_null($pageNumber))
        $this->_pageNumber = (intval($pageNumber) < 1 ? 1 : intval($pageNumber));
        else
        $this->_pageNumber = null;
        $this->_pageSize = intval($pageSize);
        return $this;
    }
    /**
     * Get the paging stats that were used in the query
     *
     * @return array[int,int]
     */
    public function getPageStats()
    {
        return array($this->_pageNumber, $this->_pageSize);
    }
    /**
     * Check if the results are paged or not
     *
     * @return bool
     */
    public function isPaged()
    {
        return $this->_pageNumber !== null;
    }
    /**
     * Create a select SQL query
     *
     * @return string
     */
    public function generateForSelect()
    {
        $focus = strtolower($this->_focus);
        $fAlias = DaoMap::$map[$focus]['_']['alias'];
         
        $sql = 'select ';
        //get distinct
        $sql .= $this->_distinct === true ? 'distinct ' : '';
        //get pagination stats
        $sql .= $this->isPaged() ? 'sql_calc_found_rows ' : '';
        //get all fields
        $sql .= implode(', ', $this->_buildFieldsForSelect()) . ' ';
        //get from table
        $sql .= sprintf('from `%s` `%s`', $focus, $fAlias) . ' ';
        //get all joins
        $sql .= count($this->_joins) === 0 ? '' : implode(' ', array_map(create_function('$a', 'return $a["joinType"] . " `" . $a["joinClass"] . "` `" . $a["joinAlias"] . "` on (" . $a["joinCondition"] . ")";'), $this->_joins)) . ' ';
        //get whereclause
        if($this->_selectActiveOnly === true)
        	$this->where('`' . $fAlias . '`.`active` = 1');
        $sql .= count($this->_whereClause) === 0 ? '' : 'where (' . implode(') AND (', $this->_whereClause) . ')';
        //get orderby
        $sql .= count($orders = $this->_buildOrderByForSelect()) === 0 ? '' : ' order by ' . implode(', ', $orders);
        //get limit for pagination
        $sql .= ($this->isPaged() === false) ? '' : ' limit ' . (($this->_pageNumber - 1) * $this->_pageSize) . ', ' . $this->_pageSize;
        return $sql;
    }
    /**
     * Create a count SQL query
     *
     * @return string
     */
    public function generateForCount()
    {
        $focus = strtolower($this->_focus);
        $fAlias = DaoMap::$map[$focus]['_']['alias'];
         
        $sql = 'select count(';
        //get distinct
        $sql .= $this->_distinct === true ? 'distinct ' : '';
        $sql .= '`' . $fAlias  . '`.id) `count` ';
        //get from table
        $sql .= sprintf('from `%s` `%s`', $focus, $fAlias) . ' ';
        //get all joins
        $sql .= count($this->_joins) === 0 ? '' : implode(' ', array_map(create_function('$a', 'return $a["joinType"] . " " . $a["joinClass"] . " " . $a["joinAlias"] . " on (" . $a["joinCondition"] . ")";'), $this->_joins)) . ' ';
        //get whereclause
        if(!$this->_selectActiveOnly === true)
        	$this->where('`' . $fAlias . '`.`active` = 1');
        $sql .= count($this->_whereClause) === 0 ? '' : 'where (' . implode(') AND (', $this->_whereClause) . ')';
        return $sql;
    }
    /**
     * building up an array of field for this table
     *
     * @return multitype:string
     */
    private function _buildFieldsForSelect()
    {
        // ----------------------------------------------------------
        // Select which fields to return in the query on the focus table
        // ----------------------------------------------------------
        $focus = strtolower($this->_focus);
        $fAlias = DaoMap::$map[$focus]['_']['alias'];
        $fields = array();
        $fields[] = '`' . $fAlias . '`.`id`';
        foreach (DaoMap::$map[$focus] as $field => $properties)
        {
            //entity metadata
            if (trim($field) === '_')
            continue;
             
            //if this is not a relationship
            if (!isset($properties['rel']))
            {
                $fields[] = '`' . $fAlias . '`.`' . $field . '`';
                continue;
            }
             
            //if this is a relationship
            switch ($properties['rel'])
            {
                // Don't return any of these field types
                case DaoMap::ONE_TO_MANY:
                case DaoMap::MANY_TO_MANY:
                    {
                        break;
                    }
                default:
                    $field .= 'Id';
                $fields[] = '`' . $fAlias . '`.`' . $field . '`';
                break;
            }
        }
        return $fields;
    }
    /**
     * Building up the order by clause array
     *
     * @return multitype:string
     */
    private function _buildOrderByForSelect()
    {
        $focus = strtolower($this->_focus);
        if (count($this->_orderBy) === 0 && is_array(DaoMap::$map[$focus]['_']['sort']))
        $this->orderBy(DaoMap::$map[$focus]['_']['sort'][0], DaoMap::$map[$focus]['_']['sort'][1]);
        $orders = array();
        foreach ($this->_orderBy as $field => $order)
        {
            $orders[] = $field . ' ' . $order;
        }
        return $orders;
    }
    /**
     * generating the key for joins, easy to check
     *
     * @param string $table     The table that we are tyring to join on
     * @param string $alias     The alias for joining table
     * @param string $condition The condition that we are joining on
     * @param string $type      The type of the join: inner join or left join
     *
     * @return string
     */
    private function _genJoinKey($table, $alias, $condition, $type)
    {
        return md5($table . ':' . $alias . ':' . $condition . ':' . $type);
    }
    /**
     * Checking whether the join is in the query already
     *
     * @param string $table     The table that we are tyring to join on
     * @param string $alias     The alias for joining table
     * @param string $condition The condition that we are joining on
     * @param string $type      The type of the join: inner join or left join
     *
     * @return boolean
     */
    private function _joinLoaded($table, $alias, $condition, $type)
    {
        return array_key_exists($this->_genJoinKey($table, $alias, $condition, $type), $this->_joins);
    }
    /**
     * adding a join onto the query
     *
     * @param string $table     The table that we are tyring to join on
     * @param string $alias     The alias for joining table
     * @param string $condition The condition that we are joining on
     * @param string $type      The type of the join: inner join or left join
     *
     * @return DaoQuery
     */
    private function _addJoin($table, $alias, $condition, $type)
    {
        if($this->_joinLoaded($table, $alias, $condition, $type))
        return $this;
         
        $table = strtolower(trim($table));
        $this->_joins[$this->_genJoinKey($table, $alias, $condition, $type)] = array(
                'joinType' => $type,
                'joinClass' => $table,
                'joinAlias' => $alias,
                'joinCondition' => $condition
        );
        return $this;
    }
    /**
     * building up the join query
     *
     * @param string $field     The field of the focus class
     * @param string $joinClass The classname of table that we are trying to join
     * @param string $alias     The alias of the classname of left side of the join
     * @param string $joinType  The type of the join
     *
     * @return DaoQuery
     */
    private function _buildJoin($field, $joinClass, $alias, $joinType = self::DEFAULT_JOIN_TYPE, $overrideCond = "")
    {
    	$overrideCond = trim($overrideCond);
        //load the dao map of the join class
        DaoMap::loadMap($joinClass);
        $focus = strtolower($this->_focus);
        $fClass = strtolower($joinClass); // the fieldclass
        $fAlias = DaoMap::$map[$fClass]['_']['alias']; // the join class's alias
        $fieldMap = DaoMap::$map[$fClass][$field];
        $ref = DaoMap::$map[strtolower($joinClass)][$field]['rel'];
        switch($ref)
        {
            case DaoMap::MANY_TO_MANY:
                {
                    $joinTableMap = strtolower($fieldMap['class']);
                    //Join in the many to many join table
                    if ($fieldMap['side'] == DaoMap::RIGHT_SIDE)
                    $mtmJoinTable = $fClass . '_' . $joinTableMap;
                    else
                    $mtmJoinTable = $joinTableMap . '_' . $fClass;
                    $this->_addJoin($mtmJoinTable, $mtmJoinTable, $fAlias . '.id = ' . $mtmJoinTable . '.' . StringUtilsAbstract::lcFirst($joinClass) . 'Id', $joinType);
                     
                    //join in the target table
                    $joinCondition = ($overrideCond === '' ? $fieldMap['alias'] . '.id = ' . $mtmJoinTable . '.' . StringUtilsAbstract::lcFirst($fieldMap['class']) . 'Id' : $overrideCond);
                    $this->_addJoin($joinTableMap, $fieldMap['alias'], $joinCondition, $joinType);
                    break;
                }
            case DaoMap::ONE_TO_MANY:
                {
                    $joinCondition = ($overrideCond === '' ? $fAlias . '.id = ' . $alias . '.' . StringUtilsAbstract::lcFirst($joinClass) . 'Id' : $overrideCond);
                    $this->_addJoin($fieldMap['class'], $alias, $joinCondition, $joinType);
                    break;
                }
            case DaoMap::MANY_TO_ONE:
                {
                    $joinCondition = ($overrideCond === '' ? $fAlias . '.' . $field . 'Id = ' . $alias . '.id' : $overrideCond);
                    $this->_addJoin($fieldMap['class'], $alias, $joinCondition, $joinType);
                    break;
                }
            case DaoMap::ONE_TO_ONE:
                {
                    if($fieldMap['owner']) //like MANY_TO_ONE
	                    $joinCondition = ($overrideCond === '' ? $fAlias . '.' . $field . 'Id = ' . $alias . '.id' : $overrideCond);
                    else //ONE_TO_MANY
	                    $joinCondition = ($overrideCond === '' ? '.id = ' . $alias . '.' . StringUtilsAbstract::lcFirst($joinClass) . 'Id' : $overrideCond);
                    $this->_addJoin($joinClass, $alias, $fAlias . '.id = ' . $alias . '.' . StringUtilsAbstract::lcFirst($joinClass) . 'Id', $joinType);
                    break;
                }
            default:
                throw new DaoException('Invalid type(' . $ref . ') for buidling up joins in ' . __CLASS__ . '!');
        }
    }
    /**
     * Create an update SQL query
     *
     * @return string
     */
    public function generateForUpdate()
    {
        DaoMap::loadMap($this->_focus);
        $focus = strtolower($this->_focus);
        $fields = array();
        foreach (DaoMap::$map[$focus] as $field => $properties)
        {
            if ($field == '_')
            continue;
            $fieldName = $field;
            if (isset($properties['rel']))
            {
                if (in_array($properties['rel'], array(DaoMap::MANY_TO_MANY, DaoMap::ONE_TO_MANY)))
                continue;
                if ($properties['rel'] == DaoMap::ONE_TO_ONE and !$properties['owner'])
                continue;
                if ($properties['rel'] == DaoMap::MANY_TO_ONE or ($properties['rel'] == DaoMap::ONE_TO_ONE and $properties['owner']))
                $field .= 'Id';
            }
            $fields[] = '`' . $field . '`= :' . $fieldName;
        }
        $sql = 'update `' . $focus . '` set ';
        $sql .= implode(', ', $fields);
        $sql .= ' where id = :id';
        return $sql;
    }
    /**
     * Create an insert SQL query
     *
     * @return string
     */
    public function generateForInsert()
    {
        // Load the Dao map for the focus entity
        DaoMap::loadMap($this->_focus);
        $focus = strtolower($this->_focus);
        $fields = array();
        $values = array();
        foreach (DaoMap::$map[$focus] as $field => $properties)
        {
            if ($field == '_')
            continue;
            $fieldName = $field;
            if (isset($properties['rel']))
            {
                if (in_array($properties['rel'], array(DaoMap::MANY_TO_MANY, DaoMap::ONE_TO_MANY)))
                continue;
                if ($properties['rel'] == DaoMap::ONE_TO_ONE and !$properties['owner'])
                continue;
                if ($properties['rel'] == DaoMap::MANY_TO_ONE or ($properties['rel'] == DaoMap::ONE_TO_ONE and $properties['owner']))
                $field .= 'Id';
            }
            $fields[] = '`' . $field . '`';
            $values[] = ':' . $fieldName;
        }
        $sql = 'insert into `' . $focus . '`';
        $sql .= ' (' . implode(', ', $fields) . ') values (' . implode(', ', $values) . ')';
        return $sql;
    }
    /**
     * Create a delete SQL query
     *
     * @return string
     */
    public function generateForDelete()
    {
        DaoMap::loadMap($this->_focus);
        $focus = strtolower($this->_focus);
        $sql = 'delete from `' . $focus . '` where id = :id';
        return $sql;
    }
    /**
     * Generating the insert sql statement for the Many-To-Many relationship
     *
     * @param string $rightClass The right hand class
     *
     * @return string
     */
    public function generateInsertForMTM($rightClass)
    {
        $sql = sprintf('insert into ' . $this->_getTableForMTM($rightClass). ' (%sId, %sId, createdById) values (?, ?, ?)',
        strtolower(substr($this->_focus, 0, 1)) . substr($this->_focus, 1),
        strtolower(substr($rightClass, 0, 1)) . substr($rightClass, 1)
        );
        return $sql;
    }
    /**
     * Generating the select sql statement for the Many-To-Many relationship
     *
     * @param string $rightClass The right hand class
     *
     * @return string
     */
    public function generateSelectForMTM($rightClass, $select = '*')
    {
        return 'select ' . $select . ' from ' . $this->_getTableForMTM($rightClass). (count($this->_whereClause) === 0 ? '' : ' where (' . implode(') AND (', $this->_whereClause) . ')');
    }
    /**
     * Generating the select sql statement for the Many-To-Many relationship
     *
     * @param string $rightClass The right hand class
     *
     * @return string
     */
    public function generateDeleteForMTM($rightClass)
    {
        if(count($this->_whereClause) === 0)
        throw new DaoException('Where clause needed for delete!!!!!');
        return 'delete from ' . $this->_getTableForMTM($rightClass). ' where (' . implode(') AND (', $this->_whereClause) . ')';
    }
    /**
     * Generating the m_m join table
     *
     * @param string $rightClass The other join class
     *
     * @throws DaoException
     * @return string
     */
    private function _getTableForMTM($rightClass)
    {
        $leftClass = $this->_focus;
        DaoMap::loadMap($leftClass);
        foreach (DaoMap::$map[strtolower($leftClass)] as $field => $properties)
        {
            if(isset($properties['rel']) && $properties['rel'] == DaoMap::MANY_TO_MANY)
            {
                if(isset($properties['class']) && $properties['class'] == $rightClass)
                {
                    if(isset($properties['side']) && $properties['side'] == DaoMap::RIGHT_SIDE)
                    return '`' . strtolower($leftClass) . '_' . strtolower($rightClass) . '`';
                }
            }
        }
        throw new DaoException('Many-to-many relationship not found');
    }
    /**
     * magic function for toString()
     * @return string
     */
    public function __toString()
    {
        return 'DaoQuery("' . $this->_focus . '")';
    }
}

?>
