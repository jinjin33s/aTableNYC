<?php
class Admin_DishcategoryController extends Zend_Controller_Action
{
	public function indexAction()
	{           
                $this->view->messages = $this->_flashMessenger->getMessages();
		$this->view->placeholder('section')->set("manager");
		$search = new Zend_Session_Namespace('dishcategory_search');
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
		$select->from(array("dc"=>"dish_categories"), '*');
		//$select->where("status='enabled'");
		$select ->order(array("$sort $dir"));                
		if ($mask) {
			$search->mask = $mask;                        
			$select->where("dc.name LIKE '%$mask%'");
		}
		if ($status) {
			$search->status = $status;
			$select->where("dc.status = '$status'");
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
        
        public function addAction()
	{
		$this->view->placeholder('section')->set("detailview");

		$chef = $this->getRequest()->getParam('data', $this->_dishCategory);

		if( $this->getRequest()->isPost() ){
			$clean = $chef;

			$this->_db->insert('dish_categories', $clean);
			$id = $this->_db->lastInsertId();
			$this->_redirect("/admin/dishcategory");
		}
	}        
	
	public function editAction()
	{
		$this->view->placeholder('section')->set("detailview");
                
		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'SELECT * FROM dish_categories WHERE id='.(int)$id.' LIMIT 1';
			$this->view->dish = $this->_db->fetchRow($sql);
                        
			if( $this->getRequest()->isPost() ){
				$dishcategory = $this->getRequest()->getParam('data', array());				
                                $clean = $dishcategory;
                                
				//$clean['modified'] = new Zend_Db_Expr('NOW()');
                                
				$this->_db->update('dish_categories', $clean, 'id='.$id );
				$this->_redirect("/admin/dishcategory");
			}
		}else{
			$this->_redirect('/admin/dishcategory');
		}
	}
        
        public function viewAction()
	{
		$this->view->placeholder('section')->set("detailview");

		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'SELECT * FROM dish_categories WHERE id='.(int)$id.' LIMIT 1';
			$this->view->dish = $this->_db->fetchRow($sql);
		}else{
			$this->_redirect("/admin/dishcategory");
		}
	}

	public function deleteAction()
	{
		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'DELETE FROM dish_categories WHERE id='.(int)$id.' LIMIT 1';
			$this->_db->query($sql);
		}
		$this->_redirect('/admin/dishcategory');
	}

	function init()
	{
		$this->view->placeholder('section')->set("dishcategory");

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

		$this->_statuses = array(
			'enabled' => 'Enabled',
			'disabled' => 'Disabled'
		);
		$this->view->statuses = $this->_statuses;

		$this->_dishCategory = array(
                    'name'      => '',
                    'status'    => '',
                    'sort'      => ''
		);                
                
		$subSectionMenu =   '
                                    <li><a href="/admin/dishcategory/">Dishes</a></li><span>|</span>
                                    <li><a href="/admin/kitchen/">Cuisines</a></li><span>|</span>
                                    <li><a href="/admin/foodcategory/">Food</a></li><span>|</span>
                                    <li><a href="/admin/chefs/">Chefs</a></li><span>|</span>
                                    <li><a href="/admin/appliances/">Appliances</a></li><span>|</span>
                                    <li><a href="/admin/allergens/">Allergens</a></li><span>|</span>
                                    <li><a href="/admin/units/">Units</a></li><span>|</span>
                                    <li><a href="/admin/ingredient/">Ingredients</a></li><span>|</span>
                                    <li><a href="/admin/ingredientcategory/">Ingredients Categories</a></li>
                                    ';
		$this->view->placeholder("subSectionMenu")->set($subSectionMenu);

	}
}
