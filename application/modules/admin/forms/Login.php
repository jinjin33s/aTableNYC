<?php
/**
* Login Zend Form
*/
class Admin_Form_Login extends Zend_Form
{
	function __construct($options = null)
	{
		parent::__construct($options);

		/* Decorators */
		$decor_form = array(
			'FormElements',
			array('HtmlTag', array('tag' => '<p>', 'class' => 'login')),
			'Form',
		);
		$decor_element = array(
			'ViewHelper',
			'Errors',
			array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
			array('Label', array('tag' => 'td', 'class' => 'label')),
			array(array('row' => 'HtmlTag'), array('tag' => 'p')),
		);
		$decor_submit = array(
			'ViewHelper',
			array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2', 'align' => 'center', 'class' => 'submit')),
			array(array('label' => 'HtmlTag'), array('tag' => 'td', 'placement' => 'prepend')),
			array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		$decor_hidden = array(
			'ViewHelper',
			'Errors',
			array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2', 'class' => 'element')),
			array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);

		/* Generate the Form */
		$this->setName('login')
			->setAction('/admin/auth/identify')
			->setMethod('post')
			->setAttrib('id', 'login');

		$username = new Zend_Form_Element_Text('username');
		$username->setLabel('Username: ')
			->setRequired(true)
			->setDecorators($decor_element)
			->addValidator('alnum', false);

		$password = new Zend_Form_Element_Password('password');
		$password->setLabel('Password: ')
			->setRequired(true)
			->setDecorators($decor_element)
			->addValidator('alnum', false);

		$submit = new Zend_Form_Element_Submit('Login');
		$submit->removeDecorator('label')
				->setDecorators($decor_form);

		$this->addElements(
			array(
				$username,
				$password,
				$submit
			)
		);
	}
}
