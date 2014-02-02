<?php
class Admin_CategoryController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();

		$this->view->placeholder('section')->set("manager");

		$search = new Zend_Session_Namespace('dish_search');
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
		$select->from(array("m"=>"menu_items"), '*');
		$select->joinLeft(array("dc"=>"dish_categories"),"m.dish_category_id = dc.id", array("type"=>"dc.name"));
		$select ->order(array("$sort $dir"));
                
		if ($mask) {
			$search->mask = $mask;                        
			$select->where("m.name LIKE '%$mask%'");
		}
		if ($status) {
			$search->status = $status;
			$select->where("m.status = '$status'");
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
			$sql = 'SELECT * FROM menu_items WHERE id='.(int)$id.' LIMIT 1';
			$this->view->dish = $this->_db->fetchRow($sql);
		}else{
			$this->_redirect("/admin/dishes");
		}
	}

	public function addAction()
	{
		$this->view->placeholder('section')->set("detailview");

		$dish = $this->getRequest()->getParam('data', $this->_dish);

		if( $this->getRequest()->isPost() ){
			$clean = $dish;

			$clean['modified'] = new Zend_Db_Expr('NOW()');

			$this->_db->insert( 'menu_items', $clean);
			$this->_redirect("/admin/dishes");
		}
	}

	public function editAction()
	{
		$this->view->placeholder('section')->set("detailview");

		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'SELECT * FROM menu_items WHERE id='.(int)$id.' LIMIT 1';
			$this->view->dish = $this->_db->fetchRow($sql);

			if( $this->getRequest()->isPost() ){
				$dish = $this->getRequest()->getParam('data', array());
				$clean = $dish;

				$clean['modified'] = new Zend_Db_Expr('NOW()');

				$this->_db->update( 'menu_items', $clean, 'id='.$id );
				$this->_redirect("/admin/dishes");
			}
		}else{
			$this->_redirect('/admin/dishes');
		}
	}

	public function deleteAction()
	{
		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'DELETE FROM menu_items WHERE id='.(int)$id.' LIMIT 1';
			$this->_db->query($sql);
		}
		$this->_redirect('/admin/dishes');
	}

	function init()
	{
		$this->view->placeholder('section')->set("category");

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

		$this->_dish = array(
                    'name'                  => '',
                    'description'           => '',
                    'chef_category_id'      => '',
                    'dish_category_id'      => '',
                    'food_category_id'      => '',
                    'season'                => '',
                    'kitchen'               => '',
                    'vegetarian'            => '',
                    'vegan'                 => '',
                    'ingredients'           => '',
                    'appliances'            => '',
                    'allergens'              => '',
                    'nutrition_facts'       => '',
                    'servings'              => '1',
                    'reheat_instructions'   => '',
                    'nutritionist_comment'  => '',
                    'chef_comment'          => '',
                    'wine_pairing'          => '',                    
                    'cost'                  => '',
                    'price'                 => '',
                    'price_scale'           => '',
                    'start_date'            => '',
                    'end_date'              => '',
                    'status'                => ''
		);
                
		$sql = "SELECT id, name FROM dish_categories WHERE status='enabled' ORDER BY name ASC";
		$this->view->dish_categories = $this->_db->fetchPairs($sql);

		$sql = "SELECT id, name FROM food_categories WHERE status='enabled' ORDER BY name ASC";
		$this->view->food_categories = $this->_db->fetchPairs($sql);
                
                $sql = "SELECT id, name FROM chef_categories WHERE status='enabled' ORDER BY name ASC";
		$this->view->chef_categories = $this->_db->fetchPairs($sql);
                
		$subSectionMenu =   '                                    
                                    <li><a href="/admin/dishcategory/">Dish List</a></li><span>|</span>
                                    <li><a href="/admin/foodcategory/">Food List</a></li><span>|</span>
                                    <li><a href="/admin/chefs/">Chef List</a></li><span>|</span>
                                    <li><a href="/admin/kitchen/">Kitchen List</a></li><span>|</span>
                                    <li><a href="/admin/units/">Units List</a></li><span>|</span>
                                    <li><a href="/admin/ingredient/">Ingredients List</a>
                                    ';
		$this->view->placeholder("subSectionMenu")->set($subSectionMenu);

	}
}
