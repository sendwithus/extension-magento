<?php
/** 
* @category Sendwithus
* @package Sendwithus_Mail
* @author Koval Anatoly
**/
class Sendwithus_Mail_Model_Mysql4_Available extends Mage_Core_Model_Mysql4_Abstract{
	public function _construct(){
		$this->_init('mail/available', 'id');
	}
}
?>