<?php
/** 
* @category cyb 
* @package cyb_StoreReviews
* @author Koval Anatoly
**/

require_once(Mage::getBaseDir('lib') . '/Sendwithus/API.php');

class Sendwithus_Mail_Helper_Data extends Mage_Core_Helper_Abstract {

	/*
	*	Sync current system emails with the table mail_emails
	*/
	public function syncEmailsList($sys_emails = null) {
        Mage::log('syncEmailsList', null, 'mail.log');

        if ($sys_emails == null)
            $sys_emails =  Mage_Core_Model_Email_Template::getDefaultTemplatesAsOptionsArray();

        $custom_templates = Mage::getSingleton('core/email_template')->getCollection();
		$wsys_mails = array();
        $c_mails = array();
		$core_resource = Mage::getSingleton('core/resource');
        $table = $core_resource->getTableName("mail/emails");
		$readConnection = $core_resource->getConnection('core_read');
		$writeConnection = $core_resource->getConnection('core_write');

        $prefix = 'Custom Template: ';

		// tramsform system emails
		foreach ($sys_emails as $v) {
			if(empty($v["value"])) continue;
			$wsys_mails[$v["value"]] = $v["label"];
		}
        
        // transform custom templates
        foreach ($custom_templates as $t) {
            $c_mails[$prefix . $t["template_code"]] = $t["template_id"];
        }

		/* 		Execute the query and store the results in $results 		*/
        $query = sprintf("SELECT * FROM `%s`", $table);
		$results = $readConnection->fetchAll($query);

		foreach ($results as $k=>$v) {
            $code = $v["email_code"];
            // Mage::log("Checking if `$code` is set", null, 'mail.log');
            if (!isset($wsys_mails[$code]) && !isset($c_mails[$code])) {

                // current email does not exists in the sys emails
                // Mage::log("Removing `$code`", null, 'mail.log');
                $query = sprintf("DELETE FROM `%s` WHERE id=%d",
                    $table, (int)$v['id']
                );
				$writeConnection->query($query);
			} else {
                if (isset($wsys_mails[$code]))
                    unset($wsys_mails[$code]);
                if (isset($c_mails[$code]))
                    unset($c_mails[$code]);
			}
		}

		// add the new system emails
		foreach ($wsys_mails as $k=>$v){
            $query = sprintf("INSERT INTO `%s` (email_code, email_name) VALUES ('%s', '%s')",
                $table, $k, $v
            );

            /* Execute the query */
            $writeConnection->query($query);
		}

        // add new custom emails
        foreach ($c_mails as $code => $id) {
            // Mage::log("adding `$code`", null, 'mail.log');
            $query = sprintf("INSERT INTO `%s` (email_code, email_name, core_email_template_id) VALUES ('%s', '%s', %d)",
                $table,
                $code,
                $code,
                (int)$id
            );

            $writeConnection->query($query);
        }
	}

	public function loadAvailableMails(){
        Mage::log('loadAvailableMails', null, 'mail.log');

		$remoteList = $this->getListOfAvailable();
		if(!is_array($remoteList)) {
			return;
		}
		$availableList = array();
		foreach ($remoteList as $v){
			$availableList[$v["id"]] = $v["name"];
		}
		// update table email_available
		$resource = Mage::getSingleton('core/resource');

		/*		Retrieve the read connection */
		$readConnection = $resource->getConnection('core_read');

		/* Retrieve the write connection  */
		$writeConnection = $resource->getConnection('core_write');

		$query = "SELECT * FROM ".$resource->getTableName("mail/available");
		/* 		Execute the query and store the results in $results 		*/
		$results = $readConnection->fetchAll($query);

		foreach ($results as $v){
			$id = $v["email_id"];
			if(isset($availableList[$id])){
				$query = "UPDATE ".$resource->getTableName("mail/available")." SET name='".$availableList[$id]."' where id=".$v["id"];
				unset($availableList[$id]);	// remove  updated available email from the list
			} else {	// delete not existing available email
				$query = "DELETE FROM ".$resource->getTableName("mail/available").
					" WHERE id=".$v["id"];
			}
	    	/* Execute the query */
			$writeConnection->query($query);
		}
		// now the availableList contain only non existsin emails, add to the list
		foreach ($availableList as $k=>$v){
			$query = "INSERT INTO ".$resource->getTableName("mail/available").
					" (name, email_id) VALUES ('".$v."','".$k."')";
	    	/* Execute the query */
			$writeConnection->query($query);
		}


		$modelAvailable = Mage::getModel('mail/emails');
	}

	/*
	*	get the available emails from remote service
	*/
	private function getListOfAvailable(){
		$API_KEY = Mage::getStoreConfig(
				'sendwithus_mail/credentials/API_KEY',
				null
			);

		$options = array(
  			// 'DEBUG' => true
		);

		$api = new \sendwithus\API($API_KEY, $options);

		$res = null;

		try{
			$response = $api->emails();

			if(empty($response->status)){
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
				Mage::helper('mail')->__('Error while connect to remote service.')."<br />Reason: ".$code);

			}
		} catch(\sendwithus\API_Error $e){
			Mage::getSingleton('adminhtml/session')->addError(
				Mage::helper('mail')->__('Error connection to remote service.'));
			//echo "<br>Exception ".var_export(e, true);
		}
 		return $res;
	}
}
