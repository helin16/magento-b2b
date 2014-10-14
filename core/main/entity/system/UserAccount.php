<?php
/**
 * UserAccount Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class UserAccount extends BaseEntityAbstract
{
    /**
     * The id of the system account
     * 
     * @var int
     */
    const ID_SYSTEM_ACCOUNT = 10;
    /**
     * The username
     *
     * @var string
     */
    private $username;
    /**
     * The password
     *
     * @var string
     */
    private $password;
    /**
     * The person
     *
     * @var Person
     */
    protected $person;
    /**
     * The roles that this person has
     *
     * @var array
     */
    protected $roles;
    /**
     * getter UserName
     *
     * @return String
     */
    public function getUserName()
    {
        return $this->username;
    }
    /**
     * Setter UserName
     *
     * @param String $UserName The username
     *
     * @return UserAccount
     */
    public function setUserName($UserName)
    {
        $this->username = $UserName;
        return $this;
    }
    /**
     * getter Password
     *
     * @return String
     */
    public function getPassword()
    {
        return $this->password;
    }
    /**
     * Setter Password
     *
     * @param string $Password The password
     *
     * @return UserAccount
     */
    public function setPassword($Password)
    {
        $this->password = $Password;
        return $this;
    }
    /**
     * getter Person
     *
     * @return Person
     */
    public function getPerson()
    {
        $this->loadManyToOne("person");
        return $this->person;
    }
    /**
     * Setter Person
     *
     * @param Person $Person The person that this useraccount belongs to
     *
     * @return UserAccount
     */
    public function setPerson(Person $Person)
    {
        $this->person = $Person;
        return $this;
    }
    /**
     * getter Roles
     *
     * @return Roles
     */
    public function getRoles()
    {
        $this->loadManyToMany("roles");
        return $this->roles;
    }
    /**
     * setter Roles
     *
     * @param array $Roles The roles that this user has
     *
     * @return UserAccount
     */
    public function setRoles(array $Roles)
    {
        $this->roles = $Roles;
        return $this;
    }
    /**
     * Clear all the roles
     * 
     * @return UserAccount
     */
    public function clearRoles()
    {
    	if(trim($this->getId()) === '')
    		return $this;
    	foreach($this->getRoles() as $role)
    		$this->removeRole($role);
    	return $this;
    }
    /**
     * Adding a role
     * 
     * @param Role $role
     * 
     * @throws CoreException
     * @return UserAccount
     */
    public function addRole(Role $role)
    {
    	if(trim($this->getId()) === '')
    		throw new CoreException('Save this useraccount first!');
    	self::saveManyToManyJoin($role, $this);
    	return $this;
    }
    /**
     * Deleting the role
     * 
     * @param Role $role
     * 
     * @return UserAccount
     */
    public function removeRole(Role $role)
    {
    	if(trim($this->getId()) === '')
    		return $this;
    	self::deleteManyToManyJoin($role, $this);
    	return $this;
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__toString()
     */
    public function __toString()
    {
        return $this->getUserName();
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntityAbstract::getJson()
     */
    public function getJson($extra = '', $reset = false)
    {
    	$array = array();
    	if(!$this->isJsonLoaded($reset))
    	{
    		$array['person'] = $this->getPerson()->getJson();
    		$array['roles'] = array();
    		foreach($this->getRoles() as $role)
    			$array['roles'][] = $role->getJson();
    	}
    	return parent::getJson($array, $reset);
    }
    /**
     * (non-PHPdoc)
     * @see BaseEntity::__loadDaoMap()
     */
    public function __loadDaoMap()
    {
        DaoMap::begin($this, 'ua');
        DaoMap::setStringType('username', 'varchar', 100);
        DaoMap::setStringType('password', 'varchar', 40);
        DaoMap::setManyToOne("person", "Person", "p");
        DaoMap::setManyToMany("roles", "Role", DaoMap::LEFT_SIDE, "r", false);
        parent::__loadDaoMap();
        
        DaoMap::createUniqueIndex('username');
        DaoMap::createIndex('password');
        DaoMap::commit();
    }
    /**
     * Getting UserAccount
     *
     * @param string  $username The username string
     * @param string  $password The password string
     *
     * @throws AuthenticationException
     * @throws Exception
     * @return Ambigous <BaseEntityAbstract>|NULL
     */
    public static function getUserByUsernameAndPassword($username, $password, $noHashPass = false)
    {
    	self::getQuery()->eagerLoad('UserAccount.roles', DaoQuery::DEFAULT_JOIN_TYPE, 'r');
    	$userAccounts = self::getAllByCriteria("`UserName` = :username AND `Password` = :password", array('username' => $username, 'password' => ($noHashPass === true ? $password : sha1($password))), true, 1, 2);
    	if(count($userAccounts) === 1)
    		return $userAccounts[0];
    	else if(count($userAccounts) > 1)
    		throw new AuthenticationException("Multiple Users Found!Contact you administrator!");
    	else
    		throw new AuthenticationException("No User Found!");
    }
    /**
     * Getting UserAccount by username
     *
     * @param string $username    The username string
     *
     * @throws AuthenticationException
     * @throws Exception
     * @return Ambigous <BaseEntityAbstract>|NULL
     */
    public static function getUserByUsername($username)
    {
    	self::getQuery()->eagerLoad('UserAccount.roles', DaoQuery::DEFAULT_JOIN_TYPE, 'r');
    	$userAccounts = self::getAllByCriteria("`UserName` = :username  AND r.id != :roleId", array('username' => $username), true, 1, 2);
    	if(count($userAccounts) === 1)
    		return $userAccounts[0];
    	else if(count($userAccounts) > 1)
    		throw new AuthenticationException("Multiple Users Found!Contact you administrator!");
    	else
    		throw new AuthenticationException("No User Found!");
    }
}

?>
