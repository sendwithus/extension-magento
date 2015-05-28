<?php
/** 
* @category Sendwithus
* @package Sendwithus_Mail
* @author Koval Anatoly
**/
class Sendwithus_Mail_Block_Adminhtml_Mail_Edit_Form extends Mage_Adminhtml_Block_Widget_Form{
    public function __construct()
    {
        parent::__construct();
        $this->setId('mail_form');
        $this->setTitle(Mage::helper('mail')->__('SendwithUS Mail information'));
    } 
    protected function _prepareForm(){
		$form = new Varien_Data_Form(array(
				'id' => 'edit_form',
				'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
				'method' => 'post',
			)
		);
		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
}
?>