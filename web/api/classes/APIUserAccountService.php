<?php
class APIUserAccountService extends APIServiceAbstract
{
   protected $entityName = 'UserAccount';
   public function get_login($params)
   {
       return $this->_login($params);
   }
   public function put_login($params)
   {
       return $this->_login($params);
   }
   public function post_login($params)
   {
       return $this->_login($params);
   }
   private function _login($params)
   {
       if(!isset($params['username']) || ($username = trim($params['username'])) === '')
           throw new Exception('username is empty!');
       if(!isset($params['password']) || ($password = trim($params['password'])) === '')
           throw new Exception('password is empty!');

       $userAccount = UserAccount::getUserByUsernameAndPassword($username, $password, true);
       $role = null;
       if(count($roles = $userAccount->getRoles()) > 0)
           $role = $roles[0];
       Core::setUser($userAccount, $role);
       return array();
   }
}