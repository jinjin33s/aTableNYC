<?php
class Cny_Controller_Plugin_SslMode extends Zend_Controller_Plugin_Abstract
{
	public function dispatchLoopStartup( Zend_Controller_Request_Abstract $request )
	{
		$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
		$options = $bootstrap->getOptions();

		$domain = $options['site']['domain'];
		$secure_domain = $options['site']['secure_domain'];
		$secure_controllers = $options['ssl']['controllers'];

		$controller = $request->getControllerName();
		if (in_array($controller,$secure_controllers)) {
			if ('80' == $_SERVER['SERVER_PORT']) {
				header('Location: https://'.$secure_domain.$_SERVER['REQUEST_URI']);
				exit;
			}
		} else {
			if ('443' == $_SERVER['SERVER_PORT']) {
				header('Location: http://'.$domain.$_SERVER['REQUEST_URI']);
				exit;
			}
		}
	}
}
