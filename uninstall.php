<?php
    if (!defined('WP_UNINSTALL_PLUGIN')) {
		exit();
	}
	
	// Delete the settings options
		delete_option('twilio_account_sid');
		delete_option('twilio_auth_token');
		delete_option('twilio_phone');
?>
