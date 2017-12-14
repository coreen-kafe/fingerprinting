<?php

/**
 * @package SimpleSAMLphp
 */


if (!array_key_exists('StateId', $_REQUEST)) {
	throw new SimpleSAML_Error_BadRequest('Missing required StateId query parameter.');
}
$id = $_REQUEST['StateId'];
$state = SimpleSAML_Auth_State::loadState($id, 'fingerprinting:request');

if (array_key_exists('yes', $_REQUEST)) {
	SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
}


$globalConfig = SimpleSAML_Configuration::getInstance();

$t = new SimpleSAML_XHTML_Template($globalConfig, 'fingerprinting:fingerprinting.php');
$t->data['yesTarget'] = SimpleSAML_Module::getModuleURL('fingerprinting/showprinting.php');
$t->data['yesData'] = array('StateId' => $id);
$t->show();
