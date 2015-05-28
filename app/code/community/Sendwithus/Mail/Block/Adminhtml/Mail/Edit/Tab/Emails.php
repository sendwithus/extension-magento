<?php
/** 
* @category Sendwithus
* @package Sendwithus_Mail
* @author Koval Anatoly
**/
class Sendwithus_Mail_Block_Adminhtml_Mail_Edit_Tab_Emails extends Mage_Adminhtml_Block_Widget_Form{
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('mail_form', array('legend'=>Mage::helper('mail')->__('Manage system email\'s status information by making corresponding checkbox on / off')));
		
        // $helper = Mage::helper('mail');
		// $helper->syncEmailsList();

    	$values = array();
    	
    	$emailData = Mage::getSingleton('mail/emails')->getCollection();
    	
    	$availableMails = Mage::getSingleton('mail/available')->getCollection();
    	
    	$availableValues["-1"] = "Please Select.."; 
    	$formData = array();

    	foreach ($availableMails as $available){
	    	$availableValues[(int)$available->getId()] = $available->getName(); 
    	}

    	foreach ($emailData as $email){
			$fieldset->addField('checkbox_'.$email->getId(), 'checkbox', array(
				'label'     => $email->getEmail_name(),
				'name'      => 'checkbox_'.$email->getId(),
				'checked' => $email->getChecked()?true:false,
				'onclick' => "",
				'onchange' => "",
				'values'  => array(
						"id"=>$email->getId(),
						"checked"=>$email->getChecked()?true:false,"available"=>$availableValues,
						"selected"=>(int)$email->getAvailable_id()
					),
				'disabled' => false,
				'tabindex' => 1
			));

			$form->getElement('checkbox_'.$email->getId())->setRenderer(Mage::app()->getLayout()->createBlock(
                'Sendwithus_Mail_Block_Adminhtml_Mail_Edit_Renderer_Systememail'
            ));
    	}
    	
		return parent::_prepareForm();
	}
}
