<?php
class Wireframe_LoadController extends Zend_Controller_Action
{
	public function init()
	{
		/* Initialize action controller here */
	}
 
	public function pageAction()
	{
		$this->_helper->layout->setLayout('wireframe');
		$controller = $this->getRequest()->getParam('cntrllr', 'index');
		$action = $this->getRequest()->getParam('actn', 'index');
		$this->render($controller.'/'.$action, null, true);
	}
}
