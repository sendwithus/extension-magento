<?php
/**
* @category Sendwithus
* @package Sendwithus_Mail
* @author Koval Anatoly
**/
class Sendwithus_Mail_Adminhtml_MailController extends Mage_Adminhtml_Controller_Action{
	protected function _initAction(){
		$this->loadLayout()
			->_setActiveMenu('Sendwithus/mail')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Mail Manager'),
			Mage::helper('adminhtml')->__('Mail Manager'));
		return $this;
	}
	public function indexAction() {
		$this->_initAction();
		$this->renderLayout();
	}
    /**
     * Grid Action
     * Display list of products related to current category
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_registryObject();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('mail/adminhtml_mail_edit_tab_emailsgrid')
                ->toHtml()
        );
    }

	public function editAction(){
		// load avaialble emails
        $helper = Mage::helper('mail');
		$helper->loadAvailableMails();

		// sync the mail_emails table with current system emails
		$helper->syncEmailsList();

		$mailId = $this->getRequest()->getParam('id');

		$mailModel = Mage::getModel('mail/emails')->load($mailId);
		Mage::register('mail_data', $mailModel);

		if ($mailModel->getId() || $mailId == 0) {
			$this->loadLayout();
			$this->_setActiveMenu('Sendwithus/mail');
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('MailManager'), Mage::helper('adminhtml')->__('Mail Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('MailDescription'), Mage::helper('adminhtml')->__('Store information'));
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('mail/adminhtml_mail_edit'))
				->_addLeft($this->getLayout()->createBlock('mail/adminhtml_mail_edit_tabs'));
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mail')->__('Mail does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction(){
		$this->_forward('edit');
	}

	public function saveAction(){

		if ($this->getRequest()->getPost()) {
			try {

				$postData = $this->getRequest()->getPost();

				$this->saveMailSelections($postData);

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Data was successfully saved'));

				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			} catch (Exception $e) {
                Mage::log("Error saving mappings: $e", null, 'mail.log');
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setCredentialsData($this->getRequest()->getPost());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}

	}

	private function saveMailSelections($post){
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName("mail/emails");

		$w_sel = array();
		foreach ($post as $k=>$v){
			if (strpos($k, "checkbox") === false) continue;
			$ww = explode("_", $k);
			$w_sel[$ww[1]] = $post["select_".$ww[1]];
		}

        // update table
        $query = sprintf("SELECT * FROM `%s`", $table);
		$results = $readConnection->fetchAll($query);


		foreach ($results as $v) {
			$id = $v["id"];

			if (isset($w_sel[$id])) {
                $query = sprintf("UPDATE `%s` SET checked=1, available_id=%d WHERE id=%d",
                    $table, $w_sel[$id], $id);
			} else {
                $query = sprintf('UPDATE `%s` SET checked=0, available_id=NULL WHERE id=%d',
                    $table, $id
                );
			}

			$writeConnection->query($query);
		}
	}
    protected function _registryObject()
    {
//        Mage::register('', Mage::getModel(''));
    }
}
