<?php
/**
* @category Avk
* @package Sendwithus_Mail
* @author Koval Anatoly
**/

$installer = $this;
$installer->startSetup();
$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable('mail/emails')}`;
	CREATE TABLE `{$this->getTable('mail/emails')}` (
		`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
		`email_code` VARCHAR(255) DEFAULT '',
		`email_name` VARCHAR(255) DEFAULT '',
		`checked` BOOLEAN DEFAULT 0,
		`available_id` INT UNSIGNED DEFAULT NULL,
		`core_email_template_id` INT UNSIGNED DEFAULT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS `{$this->getTable('mail/available')}`;
	CREATE TABLE `{$this->getTable('mail/available')}` (
		`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(255) DEFAULT '',
		`email_id` VARCHAR(255) DEFAULT '',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
