<?php
class Cny_View_Helper_LoggedInUser
{
	protected $view;
	function setView($view)
	{
		$this->view = $view;
	}

	function loggedInUser()
	{
		$auth = Zend_Auth::getInstance();
		if( $auth->hasIdentity() && $auth->getIdentity()->role == "admin" && $_SERVER['REQUEST_URI'] != "/admin/auth/logout"){
			return true;
		}else{
			return false;
		}
	}
}
