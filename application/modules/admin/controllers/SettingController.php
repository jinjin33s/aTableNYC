<?php
class Admin_SettingController extends Zend_Controller_Action
{
	public function indexAction()
	{            
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->view->placeholder('section')->set("manager");
		$search = new Zend_Session_Namespace('dishcategory_search');
                $mask = $status = "";
		$page = $this->getRequest()->getParam('page',1);
		$this->view->dir = $dir = $this->_getParam('dir','ASC');
		$this->view->sort = $sort = $this->_getParam('sort','addr1');

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
		$select->from(array("kc"=>"setting"), '*');
		//$select->where("status='enabled'");
		$select ->order(array("$sort $dir"));                
		if ($mask) {
			$search->mask = $mask;                        
			$select->where("kc.addr1 LIKE '%$mask%'");
		}
		if ($status) {
			$search->status = $status;
			$select->where("kc.addr2 = '$status'");
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

		$chef = $this->getRequest()->getParam('data', $this->_chef);
                
//                function rand_best() {
//                    $generated = array();
//                    for ($i = 0; $i < 100; $i++) {
//                        $a = mt_rand(1, 100);
//                        
//                    }
//                    shuffle($generated);
//                    $position = mt_rand(0, 99);
//                    echo $generated[$position];
//                }
//                die();
                /* test bubble sort */
                function bubble_sort($arr) {
                    $size = count($arr);
                    for ($i=0; $i<$size; $i++) {
                        for ($j=0; $j<$size-1-$i; $j++) {
                            if ($arr[$j+1] < $arr[$j]) {
                                swap($arr, $j, $j+1);
                            }
                        }
                    }
                    return $arr;
                }
                
                function swap(&$arr, $a, $b) {
                    $tmp = $arr[$a];
                    $arr[$a] = $arr[$b];
                    $arr[$b] = $tmp;
                }
                   
                $arr = array(1,3,2,8,5,7,4,0);

                print("Before sorting");
                print_r($arr); 

                $arr = bubble_sort($arr);
                print("</br>After sorting by using bubble sort");
                print_r($arr);

		if( $this->getRequest()->isPost() ){
			$clean = $chef;

			$this->_db->insert('setting', $clean);
			$id = $this->_db->lastInsertId();
			$this->_redirect("/admin/setting");
		}
	}        
	
	public function editAction()
	{
		$this->view->placeholder('section')->set("detailview");

		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'SELECT * FROM setting WHERE id='.(int)$id.' LIMIT 1';
			$this->view->dish = $this->_db->fetchRow($sql);

			if( $this->getRequest()->isPost() ){
				$dish = $this->getRequest()->getParam('data', array());
				$clean = $dish;

				//$clean['modified'] = new Zend_Db_Expr('NOW()');

				$this->_db->update( 'setting', $clean, 'id='.$id );
				$this->_redirect("/admin/setting");
			}
		}else{
			$this->_redirect('/admin/setting');
		}
	}

        public function viewAction()
	{
		$this->view->placeholder('section')->set("detailview");

		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'SELECT * FROM setting WHERE id='.(int)$id.' LIMIT 1';
			$this->view->dish = $this->_db->fetchRow($sql);
		}else{
			$this->_redirect("/admin/setting");
		}
	}
        
	public function deleteAction()
	{
		$id = $this->getRequest()->getParam('id');
		if(!empty($id) && is_numeric($id)){
			$sql = 'DELETE FROM setting WHERE id='.(int)$id.' LIMIT 1';
			$this->_db->query($sql);
		}
		$this->_redirect('/admin/setting');
	}

	function init()
	{
		$this->view->placeholder('section')->set("setting");

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

		$this->_chef = array(
                    'name'      => '',
                    'status'    => ''
		);                
                
		$subSectionMenu =   '';
		$this->view->placeholder("subSectionMenu")->set($subSectionMenu);

	}
}
