<?php
/**
 * Session Service
 * 
 * @package    Core
 * @subpackage Service
 * @author     lhe<helin16@gmail.com>
 */
class SessionService extends BaseServiceAbastract 
{
    /**
     * constructor
     */
	public function __construct()
	{
	    parent::__construct("Session");
	}
	/**
	 * Getting the Session Data
	 * 
	 * @param string $sessionId The sesison ID
	 * 
	 * @return string
	 */
	public function read($sessionId)
	{
	    $session = $this->getSession($sessionId);
	    return ($session instanceof Session ? $session->getData() : '');
	}
	/**
	 * Writting the Session Data
	 * 
	 * @param string $sessionId   The sesison ID
	 * @param string $sessionData The sesison data
	 * 
	 * @return Session|null
	 */
	public function write($sessionId, $sessionData)
	{
	    $user = Core::getUser(); 
	    $user = ($user instanceof UserAccount ? $user : FactoryAbastract::service('UserAccount')->get(UserAccount::ID_SYSTEM_ACCOUNT));
	    Core::setUser($user, Core::getRole());
	    $session = $this->getSession($sessionId);
	    $session = ($session instanceof Session ? $session : new Session());
        $session->setKey($sessionId);
        $session->setData($sessionData);
        $this->save($session);
	    return $session;
	}
	/**
	 * Writting the Session Data
	 * 
	 * @param string $sessionId The sesison ID
	 * 
	 * @return SessionService
	 */
	public function delete($sessionId)
	{
        FactoryAbastract::dao('Session')->deleteByCriteria('`key` = ?', array($sessionId));
	    return $this;
	}
	/**
	 * delete all sessions that has been timed out
	 * 
	 * @param int $maxTimeOut The number of seconds for the session's life time
	 * 
	 * @return SessionService
	 */
	public function cleanUp($maxTimeOut)
	{
	    $now = new UDate();
	    $now->modify('-' . $maxTimeOut . ' second');
	    return FactoryAbastract::dao('Session')->deleteByCriteria('`active` = 0 and `updated` < ?' , array($now->__toString()));
	}
	/**
	 * Getting the session object from the session ID
	 * 
	 * @param string $sessionId The sesison ID
	 * 
	 * @return Session|null
	 */
	public function getSession($sessionId)
	{
	    $session = $this->findByCriteria('`key` = ?', array($sessionId));
	    return (count($session) > 0 ? $session[0] : null);
	}
}
