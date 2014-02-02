<?php
class Wireframe_Bootstrap extends Zend_Application_Module_Bootstrap
{
	protected function _initWireframeAutoload()
	{
		$autoloader = new Zend_Application_Module_Autoloader(
			array(
				'namespace' => 'Wireframe_',
				'basePath'  => dirname(__FILE__),
			)
		);
		return $autoloader;
	}
	protected function _initWireframeRouters()
	{
		$this->bootstrap('FrontController');
		$router = $this->getContainer()->frontcontroller->getRouter();
		$route = new Zend_Controller_Router_Route(
			'wireframe/:cntrllr/:actn/*',
			array(
				'module'     => 'wireframe',
				'controller' => 'load',
				'action'     => 'page'
			)
		);
		$router->addRoute('wireframe', $route);
		$route = new Zend_Controller_Router_Route(
			'wireframe',
			array(
				'module'     => 'wireframe',
				'controller' => 'load',
				'action'     => 'page'
			)
		);
		$router->addRoute('wireframe_static', $route);
	}
}
