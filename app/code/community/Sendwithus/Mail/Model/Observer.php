<?php
/**
* @category SendWithUS
* @package SendWithUS
* @author Koval Anatoly
**/

require_once(Mage::getBaseDir('lib') . '/Sendwithus/API.php');

class Sendwithus_Mail_Model_Observer {
    /**
     * Observes the event
    */
    public function trackSystemConfigSaveAfter($info) {
        $groups = Mage::app()->getFrontController()->getRequest()->getPost('groups');
        $api_key = $groups["credentials"]["fields"]["API_KEY"]["value"];

        $options = array(
            // 'DEBUG' => true
        );

        $api = new \sendwithus\API($api_key, $options);
        $res = null;

        try {
            $response = $api->emails();

            if (empty($response->status)) {
                $res = array();
                foreach ($response as $v){
                    $res[] = array("id"=>$v->id, "name"=>$v->name);
                }
            } else {
                $code = "";
                switch($response->code){
                    case "403":
                        $code = "403 (bad api key)";
                        break;
                    case "400":
                        $code = "400 (malformed request)";
                        break;
                    case "":
                        $code = "Connection error";
                        break;
                    default:
                        $code = "Unknown error";
                }
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('mail')->__('Error while check connection to remote service.')."<br />Reason: ".$code);
            }
        } catch(\sendwithus\API_Error $e){
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('mail')->__('Error while check connection to remote service.'));

        }
    }
}
