<?php
/** 
* @category Sendwithus
* @package Sendwithus_Mail
* @author Koval Anatoly
**/
class Sendwithus_Mail_Model_Emails extends Mage_Core_Model_Abstract{
	public function _construct(){
		parent::_construct();
		$this->_init('mail/emails');
	}
}