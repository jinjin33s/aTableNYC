<?php
class Admin_MembersController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();

		$this->view->placeholder('section')->set("manager");

		$search = new Zend_Session_Namespace('member_search');
                $mask = $status = "";

		$page = $this->getRequest()->getParam('page',1);
		$this->view->dir = $dir = $this->_getParam('dir','ASC');
		$this->view->sort = $sort = $this->_getParam('sort','name');

		if ($search->mask != '') $this->view->mask = $mask = $search->mask;
		if ($search->status != '') $this->view->status = $status = $search->status;
		if( $this->getRequest()->isPost() ){
			$this->view->mask = $mask = $this->_getParam('mask','');
			$this->view->status = $status = $this->_getParam('status','');

			if ($mask == '') {
				$search->setExpirationSeconds(1);
				$search->mask = '';
			} else $search->mask = $mask;
		}

		$select = $this->_db->select();
		$select->from(array("m"=>"members"), '*');
		$select ->order(array("$sort $dir"));

		if ($mask) {
			$search->mask = $mask;
			$select->where("name LIKE '%$mask%'");
		}
		if ($status) {
			$search->status = $status;
			$select->where("status = '$status'");
		}

		if(isset($isCSVExport)) {
			$this->_helper->layout()->disableLayout();

			$stmt = $this->_db->query($select);
			$this->view->staff = $stmt->fetchAll();

			$this->render('csvexport');
		}

		$paginator = Zend_Paginator::factory($select);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(25);
		$this->view->list = $paginator;
	}

	public function viewAction()
	{
                $this->view->placeholder('section')->set("detailview");
		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'SELECT * FROM members WHERE id='.(int)$id.' LIMIT 1';
			$this->view->member = $this->_db->fetchRow($sql);
		}else{
			$this->_redirect("/admin/members");
		}
	}
        
        public function addAction()
	{
                $this->view->placeholder('section')->set("detailview");
                
		if( $this->getRequest()->isPost() ){
                    
                        $member = $this->getRequest()->getParam('data', $this->_member);
                        
                        $filter = new Zend_Filter_Encrypt(array("adapter"=>"mcrypt","key"=>$this->_auth_key));
			$filter->setVector($this->_auth_vector);
			$member['password'] = $filter->filter($member['password']);
                         
			$clean = $member;  

			$this->_db->insert('members', $clean);
			$id = $this->_db->lastInsertId();
			$this->_redirect("/admin/members"); 
		}
	}
        
	public function editAction()
	{
                $this->view->placeholder('section')->set("detailview");
                
		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			
                        $sql = 'SELECT * FROM members WHERE id='.(int)$id.' LIMIT 1';
			$member_info = $this->_db->fetchRow($sql);
                        $filter = new Zend_Filter_Decrypt(array("adapter"=>"mcrypt","key"=>$this->_auth_key));
                        $filter->setVector($this->_auth_vector);
                    
                        $member_info['password'] = rtrim($filter->filter($member_info['password']));                         
                        $this->view->member = $member_info;
                        
			if( $this->getRequest()->isPost() ){
                            
				$members = $this->getRequest()->getParam('data', $this->_member);			
                                                                
                                $filter = new Zend_Filter_Encrypt(array("adapter"=>"mcrypt","key"=>$this->_auth_key));
                                $filter->setVector($this->_auth_vector);
                                $members['password'] = $filter->filter($members['password']);
                                
                                $clean = $members;
                                $clean['modified'] = new Zend_Db_Expr('NOW()');
                                
				$this->_db->update( 'members', $clean, 'id='.$id );
				$this->_redirect("/admin/members");
			}
		}else{
			$this->_redirect('/admin/members');
		}
	}

	public function deleteAction()
	{
		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'DELETE FROM members WHERE id='.(int)$id.' LIMIT 1';
			$this->_db->query($sql);
		}
		$this->_redirect('/admin/members');
	}

	function init()
	{
		$this->view->placeholder('section')->set("members");

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
		$resource = $bootstrap->getPluginResource('multidb'); //multi db support
		$this->_db = $resource->getDefaultDb();
		$options = $bootstrap->getOptions();
                
                $this->_auth_key = $options['auth']['key'];
                $this->_auth_vector = $options['auth']['vector'];
                        
		$this->_statuses = array(
			'enabled' => 'Enabled',
			'disabled' => 'Disabled'
		);
		$this->view->statuses = $this->_statuses;
		$this->_member = array(
			'name' => '',
			'status' => ''
		);
		$subSectionMenu = '<li><a href="/admin/members/">Member Manager</a></li><span>|</span>
                                    <li><a href="/admin/members/add">Add Member</a></li>
                                ';
		$this->view->placeholder("subSectionMenu")->set($subSectionMenu);
	}
}
