<?php
/**
* @category Avk
* @package Sendwithus_Mail
* @author Koval Anatoly
**/

require_once(Mage::getBaseDir('lib') . '/Sendwithus/API.php');

class Sendwithus_Mail_Model_Template extends Mage_Core_Model_Email_Template
{
    CONST DO_NOT_SEND = 99999;
    
    private function mageObjToArray($obj)
    {
        $result = array();
        $props = get_class_methods($obj);

        foreach ($props as $prop) {
            try {
                if (substr($prop, 0, 3) == 'get' || substr($prop, 0, 2) == 'if') {
                    $result[$prop] = $obj->$prop();
                }
            } catch (Exception $e) {
                // pass
            }
        }

        return $result;
    }

    private function mageSerialize($obj, $depth=0)
    {
        $result = array();

        if ($depth > 3)
            return $result;

        if (is_object($obj) && (is_subclass_of($obj,"Varien_Object") || get_class($obj) == "Varien_Object")) {
            $result = array_replace($result, $obj->getData());
        } else {
			if(is_object($obj))
            	$result = $this->mageObjToArray($obj);
            else
            	$result = $obj;
            //$result = $obj->getData();
            Mage::log('done processing obj', null, 'mail.log');
        }

        if( is_array($result)){	
        	// check, if some of children are not simple values
	        foreach ($result as $k=>$val){
    	    	if(is_object($val)) $result[$k] = $this->mageSerialize($val, $depth+1);
        	}
        }
        return $result;
    }

    public function getOrderItems($order){
        $cleanItemList = array();
        $orderItems = $order->getAllVisibleItems();
        foreach($orderItems as $item){

            $itemData = $item->getData();
            $itemData['product_options'] = $item->getProductOptions();

           //$prod = Mage::getModel('catalog/product')->load($item->getId());
           //if($attribute = $prod->getResource()->getAttribute('thumbnail')) {
             //   $itemData['image'] = $attribute->getFrontend()->getUrl($prod);
           // }
            $cleanItemList[] = $itemData;
        }

        return $cleanItemList;
    }

    /**
     * Send transactional email to recipient
     *
     * @param   int $templateId
     * @param   string|array $sender sneder informatio, can be declared as part of config path
     * @param   string $email recipient email
     * @param   string $name recipient name
     * @param   array $vars varianles which can be used in template
     * @param   int|null $storeId
     * @return  Mage_Core_Model_Email_Template
     */

    public function sendTransactional($templateId, $sender, $email, $name, $vars=array(), $storeId=null)
    {
        try {
            $this->setSentSuccess(false);

            if (($storeId === null) && $this->getDesignConfig()->getStore()) {
                $storeId = $this->getDesignConfig()->getStore();
            }

            $store = Mage::app()->getStore();
            $base_template = null;
            $mail = null;
            $orig_template_code = '';

            // check, if this email should be processed by sendwithus_mail
            $collection = Mage::getSingleton('mail/emails')->getCollection();
            Mage::log("Going to try to send id $templateId", null, 'mail.log');

            if (ctype_digit($templateId)) {
                $base_template = Mage::getModel('core/email_template')->load((int)$templateId);
                $orig_template_code = $base_template->orig_template_code;
                Mage::log("We got a orig_template_code $templateId", null, 'mail.log');
            } else {
                $orig_template_code = $templateId;
            }

            if ($orig_template_code == "") {
                // this template id is not mapped to a core template
                Mage::log("orig_template_code was blank", null, 'mail.log');

                $collection->addFieldToFilter('core_email_template_id', (int)$templateId);
                $collection->addFieldToFilter('checked', 1);
                $collection->addFieldToFilter('available_id', array("notnull" => true));

                if (count($collection) > 0) {
                    Mage::log("Found non-default template", null, 'mail.log');

                    $mail = $collection->getFirstItem();
                }
            } else {
                //filter for email_code (templateId), checked, selected available email
                Mage::log("Let's lookk up orig template code", null, 'mail.log');

                $collection->addFieldToFilter('email_code', $orig_template_code);
                $collection->addFieldToFilter('checked', 1);
                $collection->addFieldToFilter('available_id', array("notnull" => true));

                if (count($collection) > 0) {
                    Mage::log("Found base template ($templateId)", null, 'mail.log');

                    $mail = $collection->getFirstItem();
                }
            }

            //if the template has been set to our artificial do not send template, just return here
            if($mail && $mail->getAvailableId() == Sendwithus_Mail_Model_Template::DO_NOT_SEND)
            {
                return $this;
            }

            if ($mail != null) {	// found system email to process
                Mage::log('Trying to send mail with sendwithus!', null, 'mail.log');

                $availableMail = Mage::getSingleton('mail/available')->load($mail->getAvailable_id());

                if (!is_array($sender)) {
                    $sender = array(
                        'name' => Mage::getStoreConfig('trans_email/ident_' . $sender . '/name', $storeId),
                        'address' => Mage::getStoreConfig('trans_email/ident_' . $sender . '/email', $storeId)
                    );
                } else {
                    $sender = array(
                        'name' => $sender['name'],
                        'address' => $sender['email']
                    );
                }

                $API_KEY = Mage::getStoreConfig(
                    'sendwithus_mail/credentials/API_KEY',
                    null
                );

                // setup the sendwithus api
                $api = new \sendwithus\API($API_KEY);

                // $email MAY be an array
                $emails = array_values((array)$email);
                $names = is_array($name) ? $name : (array)$name;
                $names = array_values($names);

                foreach ($emails as $key => $address) {
                    if (!isset($names[$key])) {
                        $names[$key] = substr($address, 0, strpos($address, '@'));
                    }

                    $recipient = array(
                        'address' => $address,
                        'name' => $names[$key]
                    );

                    Mage::log('Sending email:' . $templateId . ' to ' . $recipient['address'], null, 'mail.log');

                    $email_data = array();

                    foreach ($vars as $k=>$var) {
                        Mage::log("Got var $var", null, 'mail.log');

                        if ($k == 'order') {
                            // we are going to serialize an order
                            Mage::log('Got order ' . $k, null, 'mail.log');
                            $email_data['order_items'] = $this->getOrderItems($var);
                            $email_data['shipping'] = $var->getShippingAddress()->getData();
                        }

                        if(substr($k, 0, 1)=="_") continue;	// the fields, beginning from _ are not for use by external app
                        $email_data[$k] = $this->mageSerialize($var);
                    }

                    $email_data['magento_action'] = $templateId;

                    $email_data['store'] = $this->mageSerialize($store);
                    // add the store URL to the 'store' array
                    $email_data['store']['url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
                    // add store name to the 'store' array
                    $email_data['store']['frontName'] = Mage::app()->getStore()->getFrontEndName();

                    // Mage::log(print_r($email_data, true), null, 'mail.log');

                    $response = $api->send($availableMail->getEmail_id(), 
                        $recipient,
                        array(
                            'email_data' => $email_data,
                            'sender' => $sender
                        )
                    );
                }

                $this->setSentSuccess(true);
                return $this; // return
            }
        } catch (Exception $e) {
            Mage::log("Exception: $e", null, 'mail.log');
        }

		// continue default transactional processing
		parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
        return $this;
    }
}
