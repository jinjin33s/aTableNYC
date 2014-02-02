<?php
class Admin_DishesController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();

		$this->view->placeholder('section')->set("manager");

		$search = new Zend_Session_Namespace('dish_search');
                $cvs = $mask = $status = "";
                $cvs = $this->_getParam('csv');

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
		$select->from(array("m"=>"menu_items"), '*');
		//$select->joinLeft(array("dc"=>"dish_categories"),"m.dish_category_id = dc.id", array("type"=>"dc.name"));
		$select ->order(array("$sort $dir"));

		if ($mask) {
			$search->mask = $mask;
			$select->where("m.name LIKE '%$mask%'");
		}
		if ($status) {

			$search->status = $status;
			$select->where("m.status = '$status'");
		}

		if($cvs == 1) {
			$this->_helper->layout()->disableLayout();
                        //make a select query include changed to text.

                        $query = "SELECT mi.id,
                                    mi.name,
                                    cc.name as chef,
                                    mi.description,
                                    dc.name as dish_category,
                                    fc.name as food_category,
                                    mi.season,
                                    mi.cuisine,
                                    mi.vegetarian,
                                    mi.vegan,
                                    mi.cold,
                                    mi.frozen,
                                    mi.allergen_category,
                                    mi.calories as calories,
                                    mi.nutrition_facts as nutrition_facts,
                                    mi.reheat_instructions as reheating,
                                    mi.nutritionist_comment as nutritionist_comment,
                                    mi.chef_comment as chef_comment,
                                    mi.wine_pairing as wine_pairing,
                                    mi.servings as servings,
                                    mi.prep_time as prep_time,
                                    mi.cooking_time as cooking_time,
                                    mi.appliance_category,
                                    mi.prep_work as pre_work,
                                    mi.procedures as procedures,
                                    mi.price as sale_price,
                                    mi.price_scale as price_scale,
                                    mi.start_date,
                                    mi.end_date,
                                    mi.status
                        FROM menu_items mi
                        LEFT JOIN chef_categories cc ON mi.chef_category_id = cc.id
                        LEFT JOIN dish_categories dc ON mi.dish_category_id = dc.id
                        LEFT JOIN food_categories fc ON mi.food_category_id = fc.id
                        LEFT JOIN ingredients ic ON mi.ingredients_id = ic.id order by id DESC";
                        $stmt = $this->_db->query($query);
			$this->view->list = $stmt->fetchAll();

                        $query =   "SELECT im.id, im.menu_item_id, im.menu_ingredient_id, ing.ref, ing.ingredient, un.name, im.quantity, im.menu_unit_id, ing.unit1
                                      FROM ingredients AS ing, ingredient_map AS im, units AS un
                                     WHERE im.menu_ingredient_id = ing.id
                                       AND un.id = im.menu_unit_id";
                        $stmt = $this->_db->query($query);
			$this->view->items = $stmt->fetchAll();

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

                        $this->view->items = array();
                        $sql = $this->_db->quoteInto("SELECT im.id, im.menu_item_id,
                                                             im.menu_ingredient_id, ing.ref,
                                                             ing.ingredient, un.name, im.quantity,
                                                             im.menu_unit_id, ing.allergen
                                                        FROM ingredients AS ing, ingredient_map AS im, units AS un
                                                       WHERE im.menu_ingredient_id = ing.id
                                                         AND un.id = im.menu_unit_id
                                                         AND im.menu_item_id = ?",$id);

                        $this->view->items[$id] = ($this->_db->fetchAll($sql))?$this->_db->fetchAll($sql):array(0=>array("menu_ingredient_id"=>"","name"=>""));

                        $sql = 'SELECT im.id, im.menu_item_id, im.menu_ingredient_id,
                               ing.allergen
                          FROM ingredients AS ing, ingredient_map AS im, units AS un
                         WHERE im.menu_ingredient_id = ing.id
                           AND un.id = im.menu_unit_id
                           AND im.menu_item_id ='.(int)$id;

                       $this->view->allergenList = $this->_db->fetchAll($sql);

		}else{


			$this->_redirect("/admin/dishes/index");
		}
	}

	public function addAction()
	{
		$this->view->placeholder('section')->set("detailview");
		$dish = $this->getRequest()->getParam('data', $this->_dish);
		if( $this->getRequest()->isPost() ){

                    /////////////////////////////////////////////////////////////
                    //insert chef name
                    $chefctg = $dish['chef_category_id'];
                     $sql = 'SELECT *
                          FROM chef_categories
                         WHERE id='.(int)$chefctg.' LIMIT 1';
                    $chefName = $this->_db->fetchRow($sql);
                    $dish['chef_category'] = $chefName['name'];

                    //insert dish name
                    $dishctg = $dish['dish_category_id'];
                     $sql = 'SELECT *
                          FROM dish_categories
                         WHERE id='.(int)$dishctg.' LIMIT 1';
                    $dishCategName = $this->_db->fetchRow($sql);
                    $dish['dish_category'] = $dishCategName['name'];

                    //insert cuisine name
                    $dishctg = $dish['cuisine'];
                     $sql = 'SELECT *
                          FROM kitchen_categories
                         WHERE id='.(int)$dishctg.' LIMIT 1';
                    $cuisineCategName = $this->_db->fetchRow($sql);
                    $dish['cuisine'] = $cuisineCategName['name'];

                    //insert food name
                    $foodctg = $dish['food_category_id'];
                     $sql = 'SELECT *
                          FROM food_categories
                         WHERE id='.(int)$foodctg.' LIMIT 1';
                    $foodctgName = $this->_db->fetchRow($sql);
                    $dish['food_category'] = $foodctgName['name'];

                    /////////////////////////////////////////////////////////////

                    //appliance array handle

                    $appliance = $dish['appliance_category_id'];
                    $appVal = implode(',', $appliance);
                    $dish['appliance_category_id'] = $appVal;

                    //appliance name insert
                    foreach($appliance as $cart_key => $val){
                        $sql = 'SELECT *
                          FROM appliance_categories
                         WHERE id='.(int)$val.' LIMIT 1';
                        $appliancectg = $this->_db->fetchRow($sql);
                        $appliancectgName[] = $appliancectg['name'];
                    }
                    $appNameVal = implode(',', $appliancectgName);
                    $dish['appliance_category'] = $appNameVal;

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
                    $dish['allergen_category'] = $allNameVal;

                    /////////////////////////////////////////////////////////////
                    //Total time calculate
                    $totalTime = $dish['prep_time']
                                + $dish['cooking_time']
                                + $dish['cooling_time']
                                + $dish['other_time'];
                    $dish['total_time'] = $totalTime;

                    //start, end date and weeks.
                    $input_start    = $dish['start_date'] = date("Y-m-d",strtotime($dish['start_date']));
                    $input_end      = $dish['end_date'] = date("Y-m-d",strtotime($dish['end_date']));

                    $diff = abs(strtotime($input_end) - strtotime($input_start));
                    $weeks = floor($diff / 604800);

                    $clean['weeks'] = $weeks;

                    $clean = $dish;
                    $clean['created'] = new Zend_Db_Expr('NOW()');

                    $this->_db->insert( 'menu_items', $clean);
                    $id = $this->_db->lastInsertId();
                    $this->_redirect("/admin/dishes/edit/id/$id/#mediamanagertable1");
		}
	}

	public function editAction()
	{
            $this->view->placeholder('section')->set("detailview");
            $id = $this->getRequest()->getParam('id');
            if(!empty($id) && is_numeric($id)){
                $sql = 'SELECT *
                          FROM menu_items
                         WHERE id='.(int)$id.' LIMIT 1';
                $this->view->dish = $dish = $this->_db->fetchRow($sql);
                $ingredientsId = $dish['ingredients_id'];

                $sql = 'SELECT *
                          FROM ingredients
                         WHERE id='.(int)$ingredientsId.' LIMIT 1';
                $this->view->units = $this->_db->fetchRow($sql);

                $this->view->items = array();
                $sql = $this->_db->quoteInto("SELECT im.id, im.menu_item_id, im.menu_ingredient_id,
                                                     ing.ref, ing.ingredient, un.name, im.quantity,
                                                     im.menu_unit_id, ing.unit1, ing.allergen, ing.variety
                                                FROM ingredients AS ing, ingredient_map AS im, units AS un
                                               WHERE im.menu_ingredient_id = ing.id
                                                 AND un.id = im.menu_unit_id
                                                 AND im.menu_item_id = ?",$id);

                $this->view->items[$id] = ($this->_db->fetchAll($sql))?$this->_db->fetchAll($sql):array(0=>array("menu_ingredient_id"=>"","name"=>""));

                $sql = 'SELECT im.id, im.menu_item_id, im.menu_ingredient_id,
                               ing.allergen
                          FROM ingredients AS ing, ingredient_map AS im, units AS un
                         WHERE im.menu_ingredient_id = ing.id
                           AND un.id = im.menu_unit_id
                           AND im.menu_item_id ='.(int)$id;
                $this->view->allergenList = $this->_db->fetchAll($sql);

                if( $this->getRequest()->isPost() ){
                    $dish = $this->getRequest()->getParam('data', array());

                    /////////////////////////////////////////////////////////////
                    //insert chef name
                    $chefctg = $dish['chef_category_id'];
                     $sql = 'SELECT *
                          FROM chef_categories
                         WHERE id='.(int)$chefctg.' LIMIT 1';
                    $chefName = $this->_db->fetchRow($sql);
                    $dish['chef_category'] = $chefName['name'];

                    //insert dish name
                    $dishctg = $dish['dish_category_id'];
                     $sql = 'SELECT *
                          FROM dish_categories
                         WHERE id='.(int)$dishctg.' LIMIT 1';
                    $dishCategName = $this->_db->fetchRow($sql);
                    $dish['dish_category'] = $dishCategName['name'];

                    //insert cuisine name
                    $dishctg = $dish['kitchen_category_id'];
                     $sql = 'SELECT *
                          FROM kitchen_categories
                         WHERE id='.(int)$dishctg.' LIMIT 1';
                    $cuisineCategName = $this->_db->fetchRow($sql);
                    $dish['cuisine'] = $cuisineCategName['name'];

                    //insert food name
                    $foodctg = $dish['food_category_id'];
                     $sql = 'SELECT *
                          FROM food_categories
                         WHERE id='.(int)$foodctg.' LIMIT 1';
                    $foodctgName = $this->_db->fetchRow($sql);
                    $dish['food_category'] = $foodctgName['name'];


                    /////////////////////////////////////////////////////////////

                    //appliance array handle
                    $appliance = $dish['appliance_category_id'];
                    $appVal = implode(',', $appliance);
                    $dish['appliance_category_id'] = $appVal;

                    //appliance name insert
                    foreach($appliance as $cart_key => $val){
                        $sql = 'SELECT *
                          FROM appliance_categories
                         WHERE id='.(int)$val.' LIMIT 1';
                        $appliancectg = $this->_db->fetchRow($sql);
                        $appliancectgName[] = $appliancectg['name'];
                    }
                    $appNameVal = implode(',', $appliancectgName);
                    $dish['appliance_category'] = $appNameVal;


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
                    $dish['allergen_category'] = $allNameVal;

                    //Total time calculate
                    $totalTime = $dish['prep_time']
                                + $dish['cooking_time']
                                + $dish['cooling_time']
                                + $dish['other_time'];
                    $dish['total_time'] = $totalTime;

                    //start, end date and weeks
                    $input_start    = $dish['start_date'] = date("Y-m-d",strtotime($dish['start_date']));
                    $input_end      = $dish['end_date'] = date("Y-m-d",strtotime($dish['end_date']));

                    $diff = abs(strtotime($input_end) - strtotime($input_start));
                    $weeks = floor($diff / 604800);

                    $dish['weeks'] = $weeks;
                    /////////////////////////////////////////////////////////////

                    $clean = $dish;
                    $clean['modified'] = new Zend_Db_Expr('NOW()');

                    $this->_db->update( 'menu_items', $clean, 'id='.$id );
                    $this->_redirect("/admin/dishes/view/id/$id");
                }
            }else{
                $this->_redirect('/admin/dishes');
            }
	}

        public function itemAddAction()
	{
            $dish_id = $this->getRequest()->getParam('dish_id');

            if( $this->getRequest()->isPost() ){
                if(!empty($dish_id) && is_numeric($dish_id)){

                    $ingredient = $this->_getParam("ingredient",0);
                    $unit = $this->_getParam("unit",0);
                    $dish = $this->getRequest()->getParam('data', array());
                    $quantity = $dish['quantity'];
                    $this->_db->insert("ingredient_map",array("menu_item_id"=>$dish_id,"menu_ingredient_id"=>$ingredient,"menu_unit_id"=>$unit,"quantity"=>$quantity));

                    //update allergen into menu_item
                    $sql = 'SELECT *
                          FROM ingredients
                         WHERE id='.(int)$ingredient.' LIMIT 1';
                    $row = $this->_db->fetchRow($sql);
                    $dish['allergen_category_id'] = $row['allergen_category_id'];
                    $clean = $dish;
                    //echo $dish['allergen_category_id']; die();
                    $this->_db->update('menu_items', $clean, 'id='.$dish_id );

                    $this->_redirect("/admin/dishes/edit/id/$dish_id/#mediamanagertable1");
                }
            }
	}
        public function itemDeleteAction()
	{
            $dish_id    = $this->getRequest()->getParam('dish_id');
            $im_id      = $this->getRequest()->getParam('id');

            $this->_db->delete("ingredient_map", "id = $im_id");
            $this->_redirect("/admin/dishes/edit/id/$dish_id/#mediamanagertable1");

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

        public function importAction()
        {
            $this->view->placeholder('section')->set("detailview");

            if( $this->getRequest()->isPost() ){

                $file_name = $_FILES['datafile']['tmp_name'];
                $lines = file($file_name);
                $count = count($lines);
                $row = 0;
                $handle = fopen($file_name, "r");
                //$this->_db->query("TRUNCATE menu_items_test");
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $num = count($data);
//                    if ($row==0){}else{
//                    var_dump($data[0]);die();
//                    }
                    echo "<p> $num fields in line $row: <br /></p>\n";
                    $row++;

                    echo "<p>$ingredientData</br></p>\n";
                    $sql = false;
                    $sql = "INSERT INTO menu_items (id,name,chef_category,description,dish_category,
                        food_category,season,cuisine,cold,frozen,allergen_category,calories,serving_size,
                        nutrition_facts,reheat_instructions,nutritionist_comment,chef_comment,wine_pairing,
                        servings,prep_time,cooking_time,cooling_time,other_time,appliance_category,prep_work,
                        procedures,price,price_scale) VALUES (";

                    for ($c=0; $c < $num; $c++) {
                        $sql .= "'" . $data[$c] . "'";
                        if($c+1 !== $num){
                            $sql .= ", ";
                        }
                    }
                    $sql .= ");";

                    if ($row == 1){}else{
                    echo "$sql<br />";
                        try{
                            $this->_db->query($sql);

                            if($data[42]){
                                        $ingredientData='';
                                        $ingredientData = $data[43];
                                        $ingredata = explode('|',$ingredientData,-1);
                                        $inNum = count($ingredata);
                                        for ($j=0; $j<$inNum; $j++){
                                            $ingItem = explode(';',$ingredata[$j]);
                                            $sql2 = "INSERT INTO ingredient_map_test VALUES (id, '$data[0]',";
                                            for ($t=0; $t<3; $t++){
                                                $sql2 .= "'" . $ingItem[$t] . "'";
                                                if($t+1 !== 3){
                                                $sql2 .= ", ";
                                                }
                                            }
                                            $sql2 .= ");";
                                            echo "$sql2<br />";
                                            $this->_db->query($sql2);
                                        }
                                    }

                        }catch(Exception $e){
                            //echo $data[0];
                            $delsql = "DELETE FROM menu_items_test WHERE id=$data[0]";
                            $this->_db->query($delsql);
                            //echo $sql;
                            $this->_db->query($sql);
                        }

                    }
                 }
                 echo "Complete import";
                 fclose($handle);


            }

        }
	function init()
	{
		$this->view->placeholder('section')->set("dishes");

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
                    'enabled'   => 'Enabled',
                    'disabled'  => 'Disabled'
		);

                $this->_season  = array(
                    'ALL'       => 'ALL',
                    'SPRING'    => 'SPRING',
                    'SUMMER'    => 'SUMMER',
                    'FALL'      => 'FALL',
                    'WINTER'    => 'WINTER'
                    );

                $this->_price_scale  = array(
                    'cheap'     => '$-cheap',
                    'moderate'  => '$$-moderate',
                    'expensive' => '$$$-expensive'
                    );

                $this->_vegetarian  = array('YES' => 'YES','NO' => 'NO');
                $this->_vegan       = array('YES' => 'YES','NO' => 'NO');
                $this->_cold        = array('YES' => 'YES','NO' => 'NO');

		$this->view->statuses = $this->_statuses;
                $this->view->season = $this->_season;
                $this->view->price_scale = $this->_price_scale;
                $this->view->vegetarian = $this->_vegetarian;
                $this->view->vegan = $this->_vegan;

		$this->_dish = array(
                    'name'                  => '',
                    'description'           => '',
                    'chef_category_id'      => '',
                    'dish_category_id'      => '',
                    'food_category_id'      => '',
                    'kitchen_category_id'   => '',
                    'appliance_category_id' => '',
                    'allergen_category_id'  => '',
                    'season'                => '',
                    'cuisine'               => '',
                    'kitchen'               => '',
                    'vegetarian'            => '',
                    'vegan'                 => '',
                    'ingredients_id'        => '',
                    'procedures'            => '',
                    'nutrition_facts'       => '',
                    'servings'              => '1',
                    'reheat_instructions'   => '',
                    'nutritionist_comment'  => '',
                    'chef_comment'          => '',
                    'wine_pairing'          => '',
                    'cost'                  => '',
                    'price'                 => '',
                    'price_scale'           => '',
                    'created'               => '',
                    'modified'              => '',
                    'code'                  => '',
                    'weeks'                 => '',
                    'status'                => ''
		);

		$sql = "SELECT id, name FROM dish_categories WHERE status='enabled' ORDER BY name ASC";
		$this->view->dish_categories = $this->_db->fetchPairs($sql);

		$sql = "SELECT id, name FROM food_categories WHERE status='enabled' ORDER BY name ASC";
		$this->view->food_categories = $this->_db->fetchPairs($sql);

                $sql = "SELECT id, name FROM chef_categories WHERE status='enabled' ORDER BY name ASC";
		$this->view->chef_categories = $this->_db->fetchPairs($sql);

                $sql = "SELECT id, name FROM kitchen_categories WHERE status='enabled' ORDER BY name ASC";
		$this->view->kitchen_categories = $this->_db->fetchPairs($sql);

                $sql = "SELECT id, name FROM allergen_categories WHERE status='enabled' ORDER BY name ASC";
		$this->view->allergen_categories = $this->_db->fetchPairs($sql);

                $sql = "SELECT id, name FROM appliance_categories WHERE status='enabled' ORDER BY name ASC";
		$this->view->appliance_categories = $this->_db->fetchPairs($sql);

                $sql = "SELECT id, concat(ingredient,', ',variety) as ingredient FROM ingredients ORDER BY ingredient ASC";
		$this->view->ingredient_categories = $this->_db->fetchPairs($sql);

                $sql = "SELECT id, name FROM units WHERE status='enabled' ORDER BY name ASC";
		$this->view->unit_categories = $this->_db->fetchPairs($sql);

		$subSectionMenu =   '<li><a href="/admin/dishes/">Dishes</a></li><span>|</span>
                                    <li><a href="/admin/dishes/add">Add a Dish</a></li><span>|</span>
                                    <li><a href="/admin/dishes/import">Import Dishes</a></li>
                                    ';
		$this->view->placeholder("subSectionMenu")->set($subSectionMenu);

	}
}

	
	
	
	
