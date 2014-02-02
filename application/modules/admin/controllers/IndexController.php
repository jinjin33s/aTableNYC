<?php
class Admin_IndexController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
	}

	function init()
	{
		$this->view->placeholder('section')->set("home");
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('admin'));
		if(!$auth->hasIdentity() || $auth->getIdentity()->role != "admin"){
			$auth->clearIdentity();
			$this->_redirect('/admin/auth');
		}else{
			$this->view->user = $auth->getIdentity();
			$this->view->placeholder('logged_in')->set(true);
		}

		$bootstrap = $this->getInvokeArg('bootstrap');
		$options = $bootstrap->getOptions();
		$this->view->sitename = $options['site']['name'];
	}
}
