<?php
class Admin_MenusController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$this->_redirect("/admin/menus/full");
	}

	public function fullAction()
	{
            //auto update status
            $today = new Zend_Db_Expr('NOW()');
            $sql = "SELECT * 
                      FROM menus 
                     WHERE date($today) between start_date and end_date";
            $liveid = $this->_db->fetchPairs($sql);
            foreach ($liveid as $live_id =>$live_name) {
                        }
            $this->view->liveid = $live_id;
            
		$this->view->messages = $this->_flashMessenger->getMessages();

		$this->view->placeholder('section')->set("manager");

		$search = new Zend_Session_Namespace('menu_full_search');
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
		$select->from(array("m"=>"menus"), '*');
		$select ->order(array("$sort $dir"));

//		if ($mask) {
//			$search->mask = $mask;
//			$select->where("start_date <= '$mask' AND end_date >= '$mask'");
//		}
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

	public function fastAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();

		$this->view->placeholder('section')->set("manager");

		$search = new Zend_Session_Namespace('menu_full_search');
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
		$select->from(array("m"=>"menus"), '*');
		$select->where("type='fast'");
		$select ->order(array("$sort $dir"));

		if ($mask) {
			$search->mask = $mask;
			$select->where("start_date <= '$mask' AND end_date >= '$mask'");
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
			$sql = 'SELECT * FROM menus WHERE id='.(int)$id.' LIMIT 1';
			$this->view->menu = $this->_db->fetchRow($sql);

			$sql = $this->_db->quoteInto(
                                "SELECT mi.*, mi.modified, mm.created, dc.name AS category 
                                   FROM menu_items AS mi, menu_map AS mm, dish_categories AS dc
                                  WHERE mi.dish_category_id = dc.id 
                                    AND mm.menu_item_id = mi.id 
                                    AND mm.menu_id = ? 
                                  ORDER BY dc.sort ASC ",$id);
			$this->view->items = $this->_db->fetchAssoc($sql);
		}else{
			$this->_redirect("/admin/menus");
		}
	}

	public function addAction()
	{
		$this->view->placeholder('section')->set("detailview");
                
                $auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('admin'));                
                $this->view->author = $author = $auth->getIdentity()->username;
                
		$menu = $this->getRequest()->getParam('data', $this->_menu);
                
		if( $this->getRequest()->isPost() ){
                    
			$clean = $menu;

			$input_start = $clean['start_date'] = date("Y-m-d",strtotime($menu['start_date']));
			$input_end   = $clean['end_date'] = date("Y-m-d",strtotime($menu['end_date']));
                        
                        $diff = abs(strtotime($input_end) - strtotime($input_start));
                        $weeks = floor($diff / 604800);
                        
                        $clean['weeks'] = $weeks;
                        
                        //get website menu's date for checking date range
                        $today = new Zend_Db_Expr('NOW()');
                        $sql = "SELECT * FROM menus WHERE date($today) between start_date and end_date LIMIT 1";
                        $webmenu = $this->_db->fetchRow($sql);
                        $web_start  = $webmenu['start_date'];
                        $web_end    = $webmenu['end_date'];
                        
                        if($input_start < $web_start && $input_end < $web_start){    
                            $clean['created'] = new Zend_Db_Expr('NOW()');
                            $clean['modified'] = new Zend_Db_Expr('NOW()');
                            $clean['author'] = $author;

                            $this->_db->insert( 'menus', $clean);
                            $id = $this->_db->lastInsertId();
                            $this->_redirect("/admin/menus/edit/id/$id");
                        }elseif($input_start > $web_end && $input_end > $web_end){
                            $clean['created'] = new Zend_Db_Expr('NOW()');
                            $clean['modified'] = new Zend_Db_Expr('NOW()');
                            $clean['author'] = $author;

                            $this->_db->insert( 'menus', $clean);
                            $id = $this->_db->lastInsertId();
                            $this->_redirect("/admin/menus/edit/id/$id");
                        }  else{
                            ?>
                            <script type="text/javascript">
                                alert("Date Not Available.\nPlease avoid <?=$web_start?> ~ <?=$web_end?>");
                            </script>
                            <?php
                            return false;
                        }
//                        
//                        $clean['created'] = new Zend_Db_Expr('NOW()');
//			$clean['modified'] = new Zend_Db_Expr('NOW()');
//                        $clean['author'] = $author;
//                        
//			$this->_db->insert( 'menus', $clean);
//			$id = $this->_db->lastInsertId();
//			$this->_redirect("/admin/menus/edit/id/$id");
		}
	}
        
        public function check_date_is_within_range($start_date, $end_date, $todays_date)
        {

          $start_timestamp = strtotime($start_date);
          $end_timestamp = strtotime($end_date);
          $today_timestamp = strtotime($todays_date);

          return (($today_timestamp >= $start_timestamp) && ($today_timestamp <= $end_timestamp));

        }


	public function editAction()
	{
            $this->view->placeholder('section')->set("detailview");
            $id = $this->getRequest()->getParam('id');
            
            $auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('admin'));                
            $this->view->author = $author = $auth->getIdentity()->username;
            $sql = "SELECT id, name 
                      FROM menu_items 
                     WHERE food_category_id=21 
                     ORDER BY name ASC";
            $this->view->projects = array(""=>"Select Project")+$this->_db->fetchPairs($sql);
            if(!empty($id) && is_numeric($id)){
                $sql = 'SELECT * 
                          FROM menus 
                         WHERE id='.(int)$id.' LIMIT 1';
                $this->view->menu = $this->_db->fetchRow($sql);
                $this->view->items = array();
                $this->view->menu_items = array();
                foreach ($this->_dish_categories as $cat_id => $cat_name) {
                    $sql = $this->_db->quoteInto("SELECT mm.menu_item_id, mi.name, mm.created, mi.modified, mm.fastmenu
                                                    FROM menu_items AS mi, menu_map AS mm
                                                   WHERE mi.dish_category_id = '$cat_id' 
                                                     AND mm.menu_item_id = mi.id 
                                                     AND mm.menu_id = ?",$id);
                    $this->view->items[$cat_id] = ($this->_db->fetchAll($sql))?$this->_db->fetchAll($sql):array(0=>array("menu_item_id"=>"","name"=>""));
                    $sql = $this->_db->quoteInto("SELECT id, name 
                                                    FROM menu_items 
                                                   WHERE status = 'enabled'
                                                     AND dish_category_id = ?",$cat_id);
                    $jin = $this->view->menu_items[$cat_id] = $this->_db->fetchPairs($sql);
                }
                if( $this->getRequest()->isPost() ){
                    $menu = $this->getRequest()->getParam('data', array());
                    $clean = $menu;
                    $input_start = $clean['start_date'] = date("Y-m-d",strtotime($menu['start_date']));
                    $input_end   = $clean['end_date'] = date("Y-m-d",strtotime($menu['end_date']));
                        
                    $diff = abs(strtotime($input_end) - strtotime($input_start));
                    $weeks = floor($diff / 604800);

                    $clean['weeks'] = $weeks;

                    //get website menu's date for checking date range
                    $today = new Zend_Db_Expr('NOW()');
                    $sql = "SELECT * FROM menus WHERE date($today) between start_date and end_date LIMIT 1";
                    $webmenu = $this->_db->fetchRow($sql);
                    $web_start  = $webmenu['start_date'];
                    $web_end    = $webmenu['end_date'];
                    $clean['modified'] = new Zend_Db_Expr('NOW()');
                    
                    if($webmenu['id'] != $id){                   
                                        
                        if($input_start < $web_start && $input_end < $web_start){ 
                            $this->_db->update('menus', $clean, 'id='.$id );
                        }elseif($input_start > $web_end && $input_end > $web_end){
                            $this->_db->update('menus', $clean, 'id='.$id );
                        }  else{
                            ?>
                                <script type="text/javascript">
                                    alert("Date Not Available.\nPlease avoid <?=$web_start?> ~ <?=$web_end?>");
                                </script>
                            <?php
                            return false;
                        }                    
                    }
                    
                    $this->_db->update('menus', $clean, 'id='.$id );
                    $items = $this->_getParam('items',array());
                    
                    foreach ($items as $cat_id => $menu_item_id) {
                            if ($menu_item_id)
                            $this->_db->insert("menu_map",array("menu_id"=>$id,"menu_item_id"=>$menu_item_id,));
                    }                                
                    $this->_redirect("/admin/menus/full");
                } 
                }else{
                    $this->_redirect('/admin/menus');
            }
	}
        
        public function copyAction()
	{
            $this->view->placeholder('section')->set("detailview");
            $id = $this->getRequest()->getParam('id');            
            $auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('admin'));                
            $this->view->author = $author = $auth->getIdentity()->username;
            if(!empty($id) && is_numeric($id)){
                $sql = 'SELECT * 
                          FROM menus 
                         WHERE id='.(int)$id.' LIMIT 1';
                $menu = $this->view->menu = $this->_db->fetchRow($sql);                
                $menu['created'] = new Zend_Db_Expr('NOW()');
                $menu['modified'] = new Zend_Db_Expr('NOW()');
                $menu['author'] = $author;
                $menu['id'] = '';        
                $this->_db->insert('menus', $menu);                
                $map_id = $this->_db->lastInsertId();
                $sql = 'SELECT * 
                          FROM menu_map
                         WHERE menu_id='.(int)$id;
                $ha = $this->_db->fetchAll($sql);
                foreach($ha as $value){
                    $this->_db->insert("menu_map",array("menu_id"=>$map_id,"menu_item_id"=>$value['menu_item_id']));
                }                
                $this->_redirect("/admin/menus/edit/id/$map_id");
            }                
	}

	public function itemAddAction()
	{
            $menu_id = $this->getRequest()->getParam('menu_id');
            $cat_id = $this->getRequest()->getParam('cat_id');

            if( $this->getRequest()->isPost() ){
                if(!empty($menu_id) && is_numeric($menu_id)){

                    $item = $this->_getParam("item",0);
                    $created = new Zend_Db_Expr('NOW()');
                    
                    $this->_db->insert("menu_map",array("menu_id"=>$menu_id,"menu_item_id"=>$item,"created"=>$created));
                    $this->_redirect("/admin/menus/edit/id/$menu_id/#$cat_id");
                }
            }
	}

	public function itemDeleteAction()
	{
		$menu_id = $this->getRequest()->getParam('menu_id');
		$cat_id = $this->getRequest()->getParam('cat_id');
		$item_id = $this->getRequest()->getParam('item_id',0);
                
		if(!empty($menu_id) && is_numeric($menu_id) && is_numeric($item_id)){
			
			$this->_db->delete("menu_map","menu_item_id = $item_id AND menu_id = $menu_id");

			$this->_redirect("/admin/menus/edit/id/$menu_id/#$cat_id");
		}
		$this->_redirect("/admin/menus/edit/id/$menu_id/#$cat_id");
	}
        
        public function itemFastmenuAction()
	{
		$menu_id = $this->getRequest()->getParam('menu_id');
		$cat_id = $this->getRequest()->getParam('cat_id');
		$item_id = $this->getRequest()->getParam('item_id',0);
                $fast = $this->getRequest()->getParam('fast');
                
                if($fast == 'on'){
                    $clean['fastmenu'] = 'off';
                }elseif($fast == 'off'){
                    $clean['fastmenu'] = 'on';
                }
		if(!empty($menu_id) && is_numeric($menu_id) && is_numeric($item_id)){
			                        
                        $this->_db->update('menu_map', $clean, "menu_item_id = $item_id AND menu_id = $menu_id");
			$this->_redirect("/admin/menus/edit/id/$menu_id/#$cat_id");
		}
		$this->_redirect("/admin/menus/edit/id/$menu_id/#$cat_id");
	}

	public function deleteAction()
	{
		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
//			$sql = 'SELECT type FROM menus WHERE id='.(int)$id.' LIMIT 1';
//			$type = $this->_db->fetchOne($sql);

			$sql = 'DELETE FROM menus WHERE id='.(int)$id.' LIMIT 1';
			$this->_db->query($sql);
		}
		$this->_redirect('/admin/menus/'.$type);
	}


	function init()
	{
		$this->view->placeholder('section')->set("menus");

		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('admin'));
                $this->view->userName = $userName = $auth->getIdentity()->username;
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

		$this->_menu_types = array(
			'full'  => 'Full Menu',
			'fast'  => 'Fast Menu'
		);
                
		$this->view->menu_types = $this->_menu_types;
                
                $this->_display = array(    
			'Mon-Sun'  => 'Mon-Sun',
			'Mon-Fri'  => 'Mon-Fri'
		);
                $this->view->display = $this->_display;
                
		$this->_statuses = array(
			'inactive'  => 'Inactive',
			'active'    => 'Active'
		);
		$this->view->statuses = $this->_statuses;

		$this->_menu = array(
                        'name'          => '',
                        'author'        => '',
			'start_date'    => '',
			'end_date'      => '',
			'status'        => '',
                        'userName'      => $userName
		);

		$sql = "SELECT id, name FROM dish_categories WHERE status='enabled' ORDER BY sort ASC";
		$this->view->dish_categories = $this->_dish_categories = $this->_db->fetchPairs($sql);

		$subSectionMenu = ' 
                                <li><a href="/admin/menus/full/">Menu Management</a></li><span>|</span>
                                <li><a href="/admin/menus/add">Add Menu</a></li>			
                                ';
		$this->view->placeholder("subSectionMenu")->set($subSectionMenu);

	}
}
