<?php
class Admin_Bootstrap extends Zend_Application_Module_Bootstrap
{
	protected function _initAdminAutoload()
	{
		$autoloader = new Zend_Application_Module_Autoloader(
			array(
				'namespace' => 'Admin_',
				'basePath'  => dirname(__FILE__),
			)
		);
		return $autoloader;
	}
	
	protected function _initFrontControllerPlugins()
	{
		$this->bootstrap( 'frontController' );
		$fc = $this->getResource( 'frontController' );
		$fc->registerPlugin( new Cny_Controller_Plugin_ModuleLayout() );
	}

	protected function _initPagination()
	{
		Zend_Paginator::setDefaultScrollingStyle('Sliding');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination_control.phtml');
		Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/helpers');
	}
}
