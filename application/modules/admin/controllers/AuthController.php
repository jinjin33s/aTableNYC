<?php
class Admin_AuthController extends Zend_Controller_Action
{
	protected $_redirectUrl = '/admin/';

	public function indexAction()
	{
           
		$this->_forward('login');
	}

	public function loginAction()
	{
		$form = new Admin_Form_Login();
		$this->view->loginform = $form;
		$this->view->error = $this->getRequest()->getParam('error', '');
	}

	public function logoutAction()
	{
		Zend_Session :: forgetMe();

		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		$this->_redirect('/admin/auth');
	}

	public function identifyAction()
	{
            if( $this->_request->isPost() ){
                $formData = $this->_request->getPost();
                if( empty($formData['username']) || empty($formData['password']) ){
                        $error = 'Empty email or password.';
                }else{// do the authentication
                    $authAdapter = $this->_getAuthAdapter($formData);
                    $auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('admin'));
                    $result = $auth->authenticate($authAdapter);
                    
                    if( !$result->isValid() ){                        
                        $error = 'Login failed';
                        //check if special root user
                        if ($formData['username'] == $this->_rootuser && md5($formData['password']) == $this->_rootpass) {
                                $data = array("username"=>$this->_rootuser,"firstname"=>"Cyber-NY","lastname"=>"","email"=>"support@cyber-ny.com","status"=>"enabled","role"=>"admin");
                                $auth->getStorage()->write((object)$data);
                                $url_redirect = ($this->_redirectUrl) ? $this->_redirectUrl : '/admin/';
                                
                                $this->_redirect($url_redirect);
                                
                                return;
                        }
                    }else{
                        $last_login = date("Y-m-d H:i:s");
                        $data = $authAdapter->getResultRowObject(null,'password');
                        $db = Zend_Db_Table::getDefaultAdapter();
                        $db->update('admin_users', array('lastlogin'=>$last_login), 'id = '.$data->id );

                        $auth->getStorage()->write($data);
                        $url_redirect = ($this->_redirectUrl) ? $this->_redirectUrl : '/admin/';
                        $this->_redirect($url_redirect);
                        return;
                    }
                }
            }
            
            $this->_redirect('/admin/auth/login/error/'.$error);
	}

	protected function _flashMessage($message)
	{
		$flashMessenger = $this->_helper->FlashMessenger;
		$flashMessenger->setNamespace('actionErrors');
		$flashMessenger->addMessage($message);
	}

	public function forgotAction()
	{
		$bootstrap = $this->getInvokeArg('bootstrap');
		$resource = $bootstrap->getPluginResource('multidb'); //multi db support
		$db = $resource->getDefaultDb();

		if( $this->_request->isPost() ){
			$username = $this->_getParam("username","");

			$sql = "SELECT * FROM admin_users WHERE username = '$username'";
			$user = $db->fetchRow($sql);

			if (!$user || !$username) {
				$this->view->error = "Invalid Username";
			}else {
				$filter = new Zend_Filter_Decrypt(array("adapter"=>"mcrypt","key"=>$this->_auth_key));
				$filter->setVector($this->_auth_vector);

				$mail = new Zend_Mail();
				$mail->setBodyHtml("Your password is: ".$filter->filter($user['password']));
				$mail->setFrom($this->_email, $this->_from);
				$mail->addTo($user['email'], $user['firstname']." ".$user['lastname']);
				$mail->setSubject('Password recovery for '.$this->_site.' Admin');
				$mail->send();

				$error = "Password email sent";
				$this->_redirect('/admin/auth/login/error/'.$error);
			}
		}
	}

	protected function _getAuthAdapter($formData)
	{
		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$authAdapter = new Zend_Auth_Adapter_DbTable( $dbAdapter );
		$authAdapter->setTableName('admin_users')
			->setIdentityColumn('username')
			->setCredentialColumn('password')
			->setCredentialTreatment('AND status = "enabled"');
		// get "salt" for better security
		// $config = Zend_Registry::get('config');
		// $salt = $config->auth->salt;
		// $password = $salt.$formData['password'];

		//encrypt password to match stored values
		$filter = new Zend_Filter_Encrypt(array("adapter"=>"mcrypt","key"=>$this->_auth_key));
		$filter->setVector($this->_auth_vector);
		$password = $filter->filter($formData['password']);
		$authAdapter->setIdentity($formData['username']);
		$authAdapter->setCredential($password);
                //var_dump($authAdapter); die();
		return $authAdapter;
	}

	function init()
	{
		$this->view->layout()->setLayout("clean");

		$bootstrap = $this->getInvokeArg('bootstrap');
		$options = $bootstrap->getOptions();
		$this->_auth_key = $options['auth']['key'];
		$this->_auth_vector = $options['auth']['vector'];
		$this->_from = $options['auth']['from_name'];
		$this->_email = $options['auth']['from_email'];
		$this->_site = $options['site']['name'];
		$this->_rootuser = $options['root']['user'];
		$this->_rootpass = $options['root']['password'];

		$auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('admin'));
		if( $auth->hasIdentity() && $_SERVER['REQUEST_URI'] != "/admin/auth/logout"){
			$this->_redirect('/');
		}else{
			$this->view->placeholder('logged_in')->set(false);
			$this->view->placeholder('section')->set("login");
		}
	}
}
