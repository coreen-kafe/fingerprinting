<?php

/**
 * @package SimpleSAMLphp
 */
class sspmod_fingerprinting_Auth_Process_Fingerprinting extends SimpleSAML_Auth_ProcessingFilter {



	/**
	 *
	 * @param array $state  The state of the response.
	 */
	public function process(&$state) {
		assert('is_array($state)');

		if (isset($state['isPassive']) && $state['isPassive'] === TRUE) {
			return;
		}

		// Save state and redirect.
		$id = SimpleSAML_Auth_State::saveState($state, 'fingerprinting:request');
		$url = SimpleSAML_Module::getModuleURL('fingerprinting/showprinting.php');
		\SimpleSAML\Utils\HTTP::redirectTrustedURL($url, array('StateId' => $id));
	}
	


}
