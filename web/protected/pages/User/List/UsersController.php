<?php
class UsersController extends BPCPageAbstract
{
	public $menuItem = 'users';
	
	public function __construct()
	{
		if(!AccessControl::canAccessUsersPage(Core::getRole()))
			die('You have no access to this page!');
		parent::__construct();
	}
}
