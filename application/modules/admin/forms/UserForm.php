<?php
/**
* This is a Zend Form for the Castingagent's Registration form
*/
class Admin_Form_UserForm extends Zend_Form
{
	function __construct($options = null, $data = null)
	{
		parent::__construct($options);

		/*
		 *	Validators
		 */
		Zend_Loader::loadClass('Cny_Validate_Unique');
		Zend_Loader::loadClass('Zend_Validate_NotEmpty');
		Zend_Loader::loadClass('Zend_Validate_EmailAddress');
		$notEmpty_password = new Zend_Validate_NotEmpty();
		$notEmpty_password->setMessage("Please enter a password");
		$notEmpty_firstname = new Zend_Validate_NotEmpty();
		$notEmpty_firstname->setMessage("Please enter a name");
		$notEmpty_lastname = new Zend_Validate_NotEmpty();
		$notEmpty_lastname->setMessage("Please enter a last name");
		$notEmpty_username = new Zend_Validate_NotEmpty();
		$notEmpty_username->setMessage("Please enter a username");
		$notEmpty_email = new Zend_Validate_NotEmpty();
		$notEmpty_email->setMessage("Please enter an email address");
		$emailAddress = new Zend_Validate_EmailAddress();
		$emailAddress->setMessage("Please enter a valid email address");
		$unique = new Cny_Validate_Unique();
		$unique->omit_ids[] = $options['omit_ids'];

		//Decorators
		$decor_element = array('AdminDecorator');

		//Options
		$status_options = array(
			'Enabled' => 'Enabled',
			'Disabled' => 'Disabled'
		);


		/*
		 *	Generate the Form
		 */
		$this->setName('admin')
			->setAction('')
			->setMethod('post')
			->setAttrib('id', 'adminForm')
			->addElementPrefixPath('Cny_Decorator','Cny/Decorator/','decorator');

		$firstname = new Zend_Form_Element_Text('firstname');
		$firstname->setLabel('First Name')
			->setRequired(true)
			->addFilter('StripTags')
			->addValidator($unique)
			->setDecorators($decor_element)
			->setValue($data['firstname']);

		$lastname = new Zend_Form_Element_Text('lastname');
		$lastname->setLabel('Last Name')
			->setRequired(true)
			->addFilter('StripTags')
			->addValidator($unique)
			->setDecorators($decor_element)
			->setValue($data['lastname']);

		$username = new Zend_Form_Element_Text('username');
		$username->setLabel('Username')
			->setRequired(true)
			->addFilter('StripTags')
			->addValidator($unique)
			->setDecorators($decor_element)
			->setValue($data['username']);

		$password = new Zend_Form_Element_Text('password');
		$password->setLabel('Password')
			->setRequired(true)
			->addFilter('StripTags')
			->addValidator($notEmpty_password)
			->setDecorators($decor_element)
			->setValue($data['password']);

		$name = new Zend_Form_Element_Text('name');
		$name->setLabel('Name')
			->setRequired(true)
			->addFilter('StripTags')
			->addValidator($notEmpty_firstname)
			->setDecorators($decor_element)
			->setValue($data['name']);

		$status = new Zend_Form_Element_Select('status');
		$status->setLabel('Status')
			->setRequired(false)
			->addFilter('StripTags')
			->setMultiOptions($status_options)
			->setDecorators($decor_element)
			->setValue($data['status']);

		$this->addElements(
			array(
				$firstname,
				$lastname,
				$username,
				$password,
				$name,
				$status
			)
		);
	}
}
