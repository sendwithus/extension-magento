<?php
/** 
* @category Sendwithus
* @package Sendwithus_Mail
* @author Koval Anatoly
**/
class Sendwithus_Mail_Block_Adminhtml_Mail_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs{
	public function __construct(){
		parent::__construct();
		$this->setId('mail_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('mail')->__('Mail Information'));
	}
	protected function _beforeToHtml(){
		$this->addTab('emails_section', array(
			'label' => Mage::helper('mail')->__('Manage Mappings'),
			'title' => Mage::helper('mail')->__('Manage Mappings'),
			'content' => $this->getLayout()->createBlock('mail/adminhtml_mail_edit_tab_emails')->toHtml(),
		));

		$grid_content = $this->getLayout()->createBlock('mail/adminhtml_mail_edit_tab_emailsgrid', 'mail_emails.grid')->toHtml();
		$serialize_block = $this->getLayout()->createBlock('adminhtml/widget_grid_serializer');
		$serialize_block->initSerializerBlock('mail_emails.grid', 'getSelectedEmails', 'emails', 'selected_emails');
		$serialize_block->addColumnInputName('position');
		$grid_content .= $serialize_block->toHtml();

		$this->addTab('emails_grid_section', array(
			'label' => Mage::helper('mail')->__('Available Emails'),
			'title' => Mage::helper('mail')->__('Available Emails'),
			'content' =>$grid_content
		));
		
		return parent::_beforeToHtml();
	}
}