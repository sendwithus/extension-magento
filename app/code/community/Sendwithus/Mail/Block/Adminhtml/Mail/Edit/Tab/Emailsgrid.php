<?php
 
class Sendwithus_Mail_Block_Adminhtml_Mail_Edit_Tab_Emailsgrid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('mail_form');
        $this->setDefaultSort('mail_name');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(false);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('mail/available')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
    	
        $this->addColumn('id', array(
            'header'    => Mage::helper('mail')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'id',
            "filter"	=>false,
            'sortable'  => false
        ));
 
        $this->addColumn('name', array(
            'header'    => Mage::helper('mail')->__('Name'),
            'align'     =>'left',
            'index'     => 'name',
            "filter"	=>false,
            'sortable'  => false
        ));
        $this->addColumn('email_id', array(
            'header'    => Mage::helper('mail')->__('Email Id'),
            'align'     =>'left',
            'index'     => 'email_id',
            "filter"	=>false,
            'sortable'  => false
        ));
 
        return parent::_prepareColumns();
    }
 
    public function getRowUrl($row)
    {
        return "";// no URL for rows
    }
    /**
     * get selected products
     *
     * @return array|mixed
     */
    protected function _getSelectedEmails()
    {
        $emails = $this->getRequest()->getPost('selected_emails');
        if (is_null($emails) && Mage::registry('sendwithus_emails')) {
            return array_keys($this->getSelectedEmails());
        }

        return $emails;
    }
    /**
     * get selected emails
     *
     * @return array
     */
    public function getSelectedEmails()
    {
        $emails = array();
        if (Mage::registry('sendwithus_emails')) {
            foreach (Mage::registry('endwithus_emails')->getEmalPosition() as $id => $pos) {
                $emails[$id] = array('position' => $pos);
            }
        }

        return $emails;
    }
	/*    
	*	Remove filter/search buttons
	*/
	public function getMainButtonsHtml()
	{
    	return '';
	}
}