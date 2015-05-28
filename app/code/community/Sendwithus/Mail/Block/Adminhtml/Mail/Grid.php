<?php
/** 
* @category Sendwithus
* @package Sendwithus_Mail
* @author Koval Anatoly
**/
class Sendwithus_Mail_Block_Adminhtml_Mail_Grid extends Mage_Adminhtml_Block_Widget_Grid{
	public function __construct(){
		parent::__construct();
		$this->setId('id');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}
	protected function _prepareCollection(){
		$collection = Mage::getModel('mail/emails')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	protected function _prepareColumns(){
		$this->addColumn('id', array(
			'header' => Mage::helper('mail')->__('ID'),
			'align' =>'right',
			'width' => '50px',
			'index' => 'id',
		));
 		$this->addColumn('email_name', array(
			'header' => Mage::helper('mail')->__(''),
			'align' =>'left',
			'width' => '330px',
			'index' => 'created_time',
		));
			
		return parent::_prepareColumns();
	}
	public function getRowUrl($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}