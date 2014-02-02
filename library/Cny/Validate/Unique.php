<?php
	class Cny_Validate_Unique extends Zend_Validate_Abstract
	{
		const UNIQUE = 'unique';
		
		public $omit_ids = array();

		protected $_messageTemplates = array(
			self::UNIQUE => "The username '%value%' is not unique"
		);

		public function isValid($value)
		{
			$db = Zend_Db_Table::getDefaultAdapter();
			$this->_setValue($value);

			$sql = $db->quoteInto('SELECT COUNT(*) FROM admin_users WHERE username = ?', $value);
			
			if( !empty($this->omit_ids) ){
				$sql .= " AND id NOT IN ('". implode("','",$this->omit_ids)."')";
			}
				
			$cnt = $db->fetchOne($sql);
			if($cnt > 0){
				$this->_error();
				return false;
			}

			return true;
		}
	}
