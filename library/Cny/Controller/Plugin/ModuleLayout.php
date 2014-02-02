<?php
class Cny_Controller_Plugin_ModuleLayout extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch( Zend_Controller_Request_Abstract $request )
	{
		// Set the layout directory for the loaded module
		$layout = Zend_Layout::getMvcInstance();
		$path = APPLICATION_PATH .'/modules/'. $request->getModuleName() .'/layouts';
		if( file_exists($path) )
			$layout->setLayoutPath( $path );
	}
}
