<?php
class Admin_TestController extends Zend_Controller_Action
{
	public function indexAction() 
	{
            $this->view->placeholder('section')->set("detailview"); 
            $this->view->page = "print";
	}        
        
        public function bubbleAction()
	{
                $this->view->placeholder('section')->set("detailview"); 
                $this->view->page = "print";
                
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
                
                // Function to calculate script execution time.                
                function microtime_float ()
                {
                    list ($msec, $sec) = explode(' ', microtime());
                    $microtime = (float)$msec + (float)$sec;
                    return $microtime;
                }
                
                // Generate random numbers
                $cnt = 0;
                $chk = $arr = array();
                while ($cnt<5000)
                {
                    $rand = mt_rand(1,5000);  
                    if ( !isset($chk[$rand]) ) {
                    $chk[$rand] = 1;
                    $arr[] = $rand;
                    $cnt++;
                    }
                } 
                
                // Get starting time.
                $start = microtime_float();
                
                    $before_bubble_sort = $arr;
                    $arr = bubble_sort($arr);
                    $after_bubble_sort = $arr;
                                    
                $end = microtime_float();

                // Print results.
                
                $time = 'Bubble Sort Execution Time: ' . round($end - $start, 4) . ' seconds';    
                
                $this->view->before_bubble_sort = $before_bubble_sort;
                $this->view->after_bubble_sort = $after_bubble_sort;
                $this->view->time = $time;   
	}
         
        public function bubbledAction() 
	{
                $this->view->placeholder('section')->set("detailview"); 
                $this->view->page = "print";
                
                function bubble_sort($arr) {
                    $size = count($arr);
                    for ($i=0; $i<$size; $i++) {
                        for ($j=0; $j<$size-1-$i; $j++) {
                            if ($arr[$j+1] > $arr[$j]) {
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
                
                // Function to calculate script execution time.                
                function microtime_float ()
                {
                    list ($msec, $sec) = explode(' ', microtime());
                    $microtime = (float)$msec + (float)$sec;
                    return $microtime;
                }
                
                // Generate random numbers
                $cnt = 0;
                $chk = $arr = array();
                while ($cnt<5000)
                {
                    $rand = mt_rand(1,5000);  
                    if ( !isset($chk[$rand]) ) {
                    $chk[$rand] = 1;
                    $arr[] = $rand;
                    $cnt++;
                    }
                } 
                
                // Get starting time.
                $start = microtime_float();
                
                    $before_bubble_sort = $arr;
                    $arr = bubble_sort($arr);
                    $after_bubble_sort = $arr;
                                    
                $end = microtime_float();

                // Print results.
                
                $time = 'Bubble Sort Execution Time: ' . round($end - $start, 4) . ' seconds';    
                
                $this->view->before_bubble_sort = $before_bubble_sort;
                $this->view->after_bubble_sort = $after_bubble_sort;
                $this->view->time = $time;   
	}
        
        
            public function quickAction() {
                
                $this->view->placeholder('section')->set("detailview"); 
                $this->view->page = "print";  
                
                function quickSortRecursive( $arr, $left = 0 , $right = NULL )
                {
                // when the call is recursive we need to change
                //the array passed to the function yearlier
                static $array = array();
                if( $right == NULL )
                $array = $arr;

                if( $right == NULL )
                $right = count($array)-1;//last element of the array

                $i = $left;
                $j = $right;

                $tmp = $array[(int)( ($left+$right)/2 )];

                // partion the array in two parts.
                // left from $tmp are with smaller values,
                // right from $tmp are with bigger ones
                do
                {
                while( $array[$i] < $tmp )
                $i++;

                while( $tmp < $array[$j] )
                $j--;

                // swap elements from the two sides
                if( $i <= $j )
                {
                $w = $array[$i];
                $array[$i] = $array[$j];
                $array[$j] = $w;

                $i++;
                $j--;
                }
                }while( $i <= $j );

                // devide left side if it is longer the 1 element
                if( $left < $j )
                quickSortRecursive(NULL, $left, $j);

                // the same with the right side
                if( $i < $right )
                quickSortRecursive(NULL, $i, $right);

                // when all partitions have one element
                // the array is sorted

                return $array;
                }
                
                // Function to calculate script execution time.                
                function microtime_float ()
                {
                    list ($msec, $sec) = explode(' ', microtime());
                    $microtime = (float)$msec + (float)$sec;
                    return $microtime;
                }
                
                // Generate random numbers
                $cnt = 0;
                $chk = $arr = array();
                while ($cnt<100000) 
                {
                    $rand = mt_rand(1,100000);  
                    if ( !isset($chk[$rand]) ) {
                    $chk[$rand] = 1;
                    $arr[] = $rand;
                    $cnt++;
                    }
                }                
                
                $start = microtime_float();
                
                    $before_quick_sort = $arr;
                    $arr = quickSortRecursive($arr);
                    $after_quick_sort = $arr;
                                    
                $end = microtime_float();
                
                $time = 'Quick Sort Execution Time: ' . round($end - $start, 4) . ' seconds';    
                
                $this->view->before_quick_sort = $before_quick_sort;
                $this->view->after_quick_sort = $after_quick_sort;
                $this->view->time = $time;   
                
                
        }
        
        public function quickdAction() {
                
                $this->view->placeholder('section')->set("detailview"); 
                $this->view->page = "print";  
                
                function quickSortRecursive( $arr, $left = 0 , $right = NULL )
                {
                // when the call is recursive we need to change
                //the array passed to the function yearlier
                static $array = array();
                if( $right == NULL )
                $array = $arr;

                if( $right == NULL )
                $right = count($array)-1;//last element of the array

                $i = $left;
                $j = $right;

                $tmp = $array[(int)( ($left+$right)/2 )];

                // partion the array in two parts.
                // left from $tmp are with smaller values,
                // right from $tmp are with bigger ones
                do
                {
                while( $array[$i] < $tmp )
                $i++;

                while( $tmp < $array[$j] )
                $j--;

                // swap elements from the two sides
                if( $i <= $j )
                {
                $w = $array[$i];
                $array[$i] = $array[$j];
                $array[$j] = $w;

                $i++;
                $j--;
                }
                }while( $i <= $j );

                // devide left side if it is longer the 1 element
                if( $left < $j )
                quickSortRecursive(NULL, $left, $j);

                // the same with the right side
                if( $i < $right )
                quickSortRecursive(NULL, $i, $right);

                // when all partitions have one element
                // the array is sorted

                return $array;
                }
                
                // Function to calculate script execution time.                
                function microtime_float ()
                {
                    list ($msec, $sec) = explode(' ', microtime());
                    $microtime = (float)$msec + (float)$sec;
                    return $microtime;
                }
                
                // Generate random numbers
                $cnt = 0;
                $chk = $arr = array();
                while ($cnt<10000)
                {
                    $rand = mt_rand(1,10000);  
                    if ( !isset($chk[$rand]) ) {
                    $chk[$rand] = 1;
                    $arr[] = $rand;
                    $cnt++;
                    }
                }                
                
                $start = microtime_float();
                
                    $before_quick_sort = $arr;
                    $arr = quickSortRecursive($arr);
                    $after_quick_sort = $arr;
                                    
                $end = microtime_float();
                
                $time = 'Quick Sort Execution Time: ' . round($end - $start, 4) . ' seconds';    
                
                $this->view->before_quick_sort = $before_quick_sort;
                $this->view->after_quick_sort = $after_quick_sort;
                $this->view->time = $time;   
                
                
        }

	function init()
	{
		//$this->view->placeholder('section')->set("orders");

		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
//		$auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('admin'));
//		if(!$auth->hasIdentity() || $auth->getIdentity()->role != "admin"){
//			$auth->clearIdentity();
//			$this->_redirect('/admin/auth');
//		}else{
//			$this->view->user = $auth->getIdentity();
//			$this->view->placeholder('logged_in')->set(true);
//		}
                
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
