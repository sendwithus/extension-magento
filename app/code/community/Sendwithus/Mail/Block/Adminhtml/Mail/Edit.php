<?php
/** 
* @category Sendwithus
* @package Sendwithus_Mail
* @author Koval Anatoly
**/
class Sendwithus_Mail_Block_Adminhtml_Mail_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{
	public function __construct(){
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'mail';
		$this->_controller = 'adminhtml_mail';
		$this->_updateButton('save', 'label', Mage::helper('mail')->__('Save data'));
		$this->_removeButton('delete');
		$this->_removeButton('back');
	}
	public function getHeaderText(){
		return Mage::helper('mail')->__('Mail manager');
	}
}