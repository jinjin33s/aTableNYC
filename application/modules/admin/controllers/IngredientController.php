<?php
class Admin_IngredientController extends Zend_Controller_Action
{
	public function indexAction()
	{            
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->view->placeholder('section')->set("manager");
		$search = new Zend_Session_Namespace('ingredient_search');
                $mask = $status = "";
		$page = $this->getRequest()->getParam('page',1);
		$this->view->dir = $dir = $this->_getParam('dir','DESC');
		$this->view->sort = $sort = $this->_getParam('sort','id');

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
		$select->from(array("ac"=>"ingredients"), '*');
		//$select->joinLeft(array("u"=>"units"),"i.unit1 = u.id", array("unit1"=>"u.name"));		
		$select ->order(array("$sort $dir"));     
                
		if ($mask) {
			$search->mask = $mask;                        
			$select->where("ac.ingredient LIKE '%$mask%'");
		}
		if ($status) {
			$search->status = $status;
			$select->where("ac.status = '$status'");
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
                                
		$dish = $this->getRequest()->getParam('data', $this->_ingredient);

		if( $this->getRequest()->isPost() ){
                                        
                    //allergen array handle
                    $allergen = $dish['allergen_category_id'];
                    $allVal = implode(',', $allergen);                    
                    $dish['allergen_category_id'] = $allVal;                    
                    //allergen name insert
                    foreach($allergen as $cart_key => $val){
                        $sql = 'SELECT * 
                          FROM allergen_categories 
                         WHERE id='.(int)$val.' LIMIT 1';
                        $allergenctg = $this->_db->fetchRow($sql);
                        $allergenctgName[] = $allergenctg['name'];
                    }
                    $allNameVal = implode(',', $allergenctgName);
                    $dish['allergen'] = $allNameVal;
                    
                    
                    //ingredient array handle
                    $ingctg = $dish['ingredientcategory_id'];
                     $sql = 'SELECT * 
                          FROM ingredient_categories 
                         WHERE id='.(int)$ingctg.' LIMIT 1';
                    $ingCategName = $this->_db->fetchRow($sql); 
                    $dish['ingredientcategory'] = $ingCategName['name'];                      
                    
                    $clean = $dish;
                    $clean['created'] = new Zend_Db_Expr('NOW()');
                    
			$this->_db->insert('ingredients', $clean);
			$id = $this->_db->lastInsertId();
			$this->_redirect("/admin/ingredient/");
		}
	}        
	
	public function editAction()
	{
		$this->view->placeholder('section')->set("detailview");

		$id = $this->getRequest()->getParam('id');
//                $ingredientcategory = $this->getRequest()->getParam('ingredientcategory');
                
		if(!empty($id) && is_numeric($id)){
                    
			$sql = 'SELECT * FROM ingredients WHERE id='.(int)$id.' LIMIT 1';
			$this->view->dish = $this->_db->fetchRow($sql);
			if( $this->getRequest()->isPost() ){
				$dish = $this->getRequest()->getParam('data', array());
                                
                                //allergen array handle
                                $allergen = $dish['allergen_category_id'];
                                $allVal = implode(',', $allergen);                    
                                $dish['allergen_category_id'] = $allVal;                    
                                //allergen name insert
                                foreach($allergen as $cart_key => $val){
                                    $sql = 'SELECT * 
                                      FROM allergen_categories 
                                     WHERE id='.(int)$val.' LIMIT 1';
                                    $allergenctg = $this->_db->fetchRow($sql);
                                    $allergenctgName[] = $allergenctg['name'];
                                }
                                $allNameVal = implode(',', $allergenctgName);
                                $dish['allergen'] = $allNameVal;
                                
                                //ingredient array handle
                                $ingctg = $dish['ingredientcategory_id'];                                
                                $sql = 'SELECT * 
                                      FROM ingredient_categories 
                                     WHERE id='.(int)$ingctg.' LIMIT 1';
                                $ingCategName = $this->_db->fetchRow($sql); 
                                $dish['ingredientcategory'] = $ingCategName['name'];  
                                
				$clean = $dish;                               
				$clean['modified'] = new Zend_Db_Expr('NOW()');

				$this->_db->update('ingredients', $clean, 'id='.$id );
				$this->_redirect("/admin/ingredient");
			}
		}else{
			$this->_redirect('/admin/ingredient');
		}
	}
        
        public function viewAction()
	{
		$this->view->placeholder('section')->set("detailview");

		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'SELECT * FROM ingredients WHERE id='.(int)$id.' LIMIT 1';
			$this->view->dish = $this->_db->fetchRow($sql);
		}else{
			$this->_redirect("/admin/ingredient");
		}
	}
        
	public function deleteAction()
	{
		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'DELETE FROM ingredients WHERE id='.(int)$id.' LIMIT 1';
			$this->_db->query($sql);
		}
		$this->_redirect('/admin/ingredient');
	}

	function init()
	{
		$this->view->placeholder('section')->set("ingredients");

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

		$this->_ingredient = array(
                    'name'      => '',
                    'status'    => ''
		);      
                
                $sql = "SELECT id, name FROM units WHERE status='enabled' ORDER BY name ASC";
		$this->view->unit_categories = $this->_db->fetchPairs($sql);
                
                $sql = "SELECT id, name FROM ingredient_categories WHERE status='enabled' ORDER BY name ASC";
		$this->view->ingredient_categories = $this->_db->fetchPairs($sql);
                
                $sql = "SELECT id, name FROM allergen_categories WHERE status='enabled' ORDER BY name ASC";
		$this->view->allergen_categories = $this->_db->fetchPairs($sql);
                
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
