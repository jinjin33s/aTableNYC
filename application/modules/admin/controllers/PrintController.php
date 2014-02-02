<?php
class Admin_PrintController extends Zend_Controller_Action
{
	public function indexAction()
	{
//		$this->view->messages = $this->_flashMessenger->getMessages();
//
//		$this->view->placeholder('section')->set("manager");
//
//		$search = new Zend_Session_Namespace('order_search');
//                $mask = $status = "";
//
//		$page = $this->getRequest()->getParam('page',1);
//		$this->view->dir = $dir = $this->_getParam('dir','DESC');
//		$this->view->sort = $sort = $this->_getParam('sort','orderdate');
//
//		if ($search->mask != '') $this->view->mask = $mask = $search->mask;
//		if ($search->status != '') $this->view->status = $status = $search->status;
//		if( $this->getRequest()->isPost() ){
//			$this->view->mask = $mask = $this->_getParam('mask','');
//			$this->view->status = $status = $this->_getParam('status','');
//
//			if ($mask == '') {
//				$search->setExpirationSeconds(1);
//				$search->mask = '';
//			} else $search->mask = $mask;
//		}
//
//		$select = $this->_db->select();
//		$select->from(array("m"=>"order_main"), '*');
//		$select ->order(array("$sort $dir"));  
//
//		if ($mask) {
//			$search->mask = $mask;
//			$select->where("name LIKE '%$mask%'");
//		}
//		if ($status) {
//			$search->status = $status;
//			$select->where("status = '$status'");
//		}
//
//		if(isset($isCSVExport)) {
//			$this->_helper->layout()->disableLayout();
//
//			$stmt = $this->_db->query($select);
//			$this->view->staff = $stmt->fetchAll();
//
//			$this->render('csvexport');
//		}
//
//		$paginator = Zend_Paginator::factory($select);
//		$paginator->setCurrentPageNumber($page);
//		$paginator->setItemCountPerPage(25);
//		$this->view->list = $paginator;
	}        
        
        public function printAction()
	{
                $this->view->placeholder('section')->set("detailview"); 
                $this->view->page = "print";
                
		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
		
                    //get user id
                    $this->view->id = $id; 
                    
                    //get order status info
                    $sql = 'SELECT * FROM order_main WHERE id='.(int)$id.' limit 1';
                    $order_main = $this->view->main_order_info = $this->_db->fetchRow($sql);
                    
                    
                    //get order detail info
                    $sql = 'SELECT * FROM order_detail WHERE order_id='.(int)$id.' order by date ASC';
                    $order_detail = $this->view->order_info = $this->_db->fetchAssoc($sql);
                    
                    //get user delivery address
                    $user_id = $order_main['user_id']; 
                    $sql = 'SELECT * FROM members WHERE id='.(int)$user_id.' limit 1';
                    $this->view->user_info = $this->_db->fetchRow($sql); 
                    
                    //get Return Address info
                    $sql = 'SELECT * FROM setting limit 1';
                    $return_addr = $this->view->return_addr_info = $this->_db->fetchRow($sql);
		}
	}
        

	function init()
	{
		//$this->view->placeholder('section')->set("orders");

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
			'Pending'           => 'Pending',
                        'In Progress'       => 'In Progress',
			'Next Delivery Due' => 'Next Delivery Due',
                        'Completed'         => 'Completed',
                        'Cancel'            => 'Cancel'
		);
		$this->view->statuses = $this->_statuses;
		$this->_member = array(
			'name' => '',
			'status' => ''
		);
		//$subSectionMenu = '<li><a href="/admin/orders/">Order Manager</a></li>';
                
		//$this->view->placeholder("subSectionMenu")->set($subSectionMenu);

	}
}
