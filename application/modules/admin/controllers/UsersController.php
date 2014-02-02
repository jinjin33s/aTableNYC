<?php
class Admin_UsersController extends Zend_Controller_Action
{
	public $_db, $_status, $_roles, $_user;

	public function indexAction()
	{
		$this->_redirect('/admin/users/list');
	}

	public function listAction()
	{
		$this->view->placeholder('section')->set("manager");

		$search = new Zend_Session_Namespace('users_search');
                $mask = $role = $status = "";

		$page = $this->getRequest()->getParam('page',1);
		$this->view->dir = $dir = $this->_getParam('dir','ASC');
		$this->view->sort = $sort = $this->_getParam('sort','lastname');

		if ($search->mask != '') $this->view->mask = $mask = $search->mask;
		if ($search->role != '') $this->view->role = $role = $search->role;
		if ($search->status != '') $this->view->status = $status = $search->status;
		if( $this->getRequest()->isPost() ){
			$this->view->mask = $mask = $this->_getParam('mask','');
			$this->view->role = $role = $this->_getParam('role','');
			$this->view->status = $status = $this->_getParam('status','');

			if ($mask == '') {
				$search->setExpirationSeconds(1);
				$search->mask = '';
			} else $search->mask = $mask;
		}

		$select = $this->_db->select();
		$select->from('admin_users', '*');
		$select ->order(array("$sort $dir"));

		if ($mask) {
			$search->mask = $mask;
			$select->where("username LIKE '%$mask%' OR firstname = '$mask' OR lastname LIKE '%$mask%'");
		}
		if ($role) {
			$search->mask = $role;
			$select->where("role = '$role'");
		}
		if ($status) {
			$search->status = $status;
			$select->where("status = '$status'");
		}

		if(isset($isCSVExport)) {
			$this->_helper->layout()->disableLayout();

			$stmt = $this->_db->query($select);
			$this->view->members = $stmt->fetchAll();

			$this->render('csvexport');
		}

		$paginator = Zend_Paginator::factory($select);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(25);
		$this->view->userList = $paginator;
	}

	public function addAction()
	{
		$user = $this->getRequest()->getParam('data', $this->_user);

		$this->view->message = $this->getRequest()->getParam('message', '');
		$this->view->user = $user;
	}

	public function editAction()
	{
		$user_id = $this->getRequest()->getParam('user_id');
		if(!empty($user_id) && is_numeric($user_id)){
			$sql = 'SELECT * FROM admin_users WHERE id='.(int)$user_id.' LIMIT 1';
			$this->view->user = $this->_db->fetchRow($sql);
		}else{
			$this->_redirect('/admin/users/');
		}
	}

	public function saveAction()
	{
		$user = $this->getRequest()->getParam('data', $this->_user);
		$clean_user = $user;
		$user_id = $this->getRequest()->getParam('id', 0);

		if (trim($clean_user['password']) == "" || $clean_user['password'] != $clean_user['cpassword']) unset($clean_user['password']);
		unset($clean_user['cpassword']);

		if ($clean_user['password']) {
			$filter = new Zend_Filter_Encrypt(array("adapter"=>"mcrypt","key"=>$this->_auth_key));
			$filter->setVector($this->_auth_vector);
			$clean_user['password'] = $filter->filter($clean_user['password']);
		}

		$this->_db->update( 'admin_users', $clean_user, 'id='.$user_id );
		$this->_redirect('/admin/users/');
	}

	public function insertAction()
	{
		$user = $this->getRequest()->getParam('data', $this->_user);
		$clean_user = $user;

		if (trim($clean_user['password']) == "" || $clean_user['password'] != $clean_user['cpassword']) unset($clean_user['password']);
		unset($clean_user['cpassword']);

		if ($clean_user['password']) {
			$filter = new Zend_Filter_Encrypt(array("adapter"=>"mcrypt","key"=>$this->_auth_key));
			$filter->setVector($this->_auth_vector);
			$clean_user['password'] = $filter->filter($clean_user['password']);
		}

		$clean_user['created'] = new Zend_Db_Expr('NOW()');
		$this->_db->insert( 'admin_users', $clean_user );
		$this->_redirect('/admin/users/');
	}

	public function deleteAction()
	{
		$user_id = $this->getRequest()->getParam('user_id');
		if(!empty($user_id) && is_numeric($user_id)){
			$sql = 'DELETE FROM admin_users WHERE id='.(int)$user_id.' LIMIT 1';
			$this->_db->query($sql);
		}
		$this->_redirect('/admin/users/');
	}

	public function init()
	{
		$this->view->placeholder('section')->set("detailview");

		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('admin'));
		if(!$auth->hasIdentity() || $auth->getIdentity()->role != "admin"){
			$auth->clearIdentity();
			$this->_redirect('/admin/auth');
		}else{
			$this->view->placeholder('logged_in')->set(true);
		}

		$bootstrap = $this->getInvokeArg('bootstrap'); // gets the boostrapper
		$resource = $bootstrap->getPluginResource('multidb'); //multi db support
		$this->_db = $resource->getDefaultDb();

		$options = $bootstrap->getOptions();
		$this->_auth_key = $options['auth']['key'];
		$this->_auth_vector = $options['auth']['vector'];

		$this->_roles = array(
			'admin' => 'Admin'
		);
		$this->view->roles = $this->_roles;
		$this->_statuses = array(
			'enabled' => 'Enabled',
			'disabled' => 'Disabled'
		);
		$this->view->statuses = $this->_statuses;
		$this->_user = array(
			'username' => '',
			'password' => '',
			'cuserpassword' => '',
			'firstname' => '',
			'lastname' => '',
			'email' => '',
			'status' => '',
			'role' => ''
		);
		$subSectionMenu = '<li><a href="/admin/users/list">User Listing</a></li><span>|</span>
                                   <li><a href="/admin/users/add">Add A User</a></li><span>|</span>';
		$this->view->placeholder("subSectionMenu")->set($subSectionMenu);
	}
}