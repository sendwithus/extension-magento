<?php
/** 
* @category Sendwithus
* @package Sendwithus_Mail
* @author Koval Anatoly
**/
class Sendwithus_Mail_Model_Mysql4_Emails_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
	public function _construct(){
		parent::_construct();
		$this->_init('mail/emails');
	}
}