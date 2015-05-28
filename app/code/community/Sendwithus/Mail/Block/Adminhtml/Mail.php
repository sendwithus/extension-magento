<?php
/** 
* @category Sendwithus
* @package Sendwithus_Mail
* @author Koval Anatoly
**/
class Sendwithus_Mail_Block_Adminhtml_Mail extends Mage_Adminhtml_Block_Widget_Grid_Container{
	public function __construct(){
		$this->_controller = 'adminhtml_mail';
		$this->_blockGroup = 'mail';
		$this->_headerText = Mage::helper('mail')->__('Mail manager');
		parent::__construct();
	}
}
?>
