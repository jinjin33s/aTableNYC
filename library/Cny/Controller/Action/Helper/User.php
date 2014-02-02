<?php
class Cny_Controller_Action_Helper_User extends Zend_Controller_Action_Helper_Abstract
{
    public function role()
    {
		$role = 'guest';
		$auth = Zend_Auth::getInstance();
		if( $auth->hasIdentity() ){
			$role = $auth->getIdentity()->role;
		}
        return $role;
    }
}
