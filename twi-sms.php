<?php
	/*
		Plugin Name: TwiSMS
		Description: Use Twilio to send SMS from your website!
		Version: 1.0.1
		Author: Plain Plugins
	*/


	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}


	class TwiSMS {
		
		// - Get the static instance variable
			private static $_instance = null;
		
		
		public static function Instantiate() {
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		
		
			private function __construct() {
			
				// - Side Menu (which loads the back-end)
					add_action('admin_menu', function() {
						$this->AdminMenu();
					});
			}
			
			
		// Main Admin Page Menu

			private function AdminMenu() {
				
				// Output the main menu item in the left menu				
					add_menu_page(
						'TwiSMS', 
						'TwiSMS', 
						'manage_options', 
						'twi-sms', 
						function() {
							$this->MainPageHTML();
						}, 
						'dashicons-admin-comments'
					);
					
				// Output the second menu item
					add_submenu_page(
						'twi-sms', 
						'TwiSMS Settings',
						'Settings',
						'manage_options',
						'twi-sms-settings',
						function() {
							$this->SettingsPageHTML();
						},
						1
					);
					
			}
			
			
			private function MainPageHTML() {
				add_filter('admin_footer_text', function() {	// Add a footer that links to our website
					$this->AddAdminFooter();
				});
				
				$send_results_html = '';
				$send_results_msg_bubble = '';
				if (isset($_POST['send_sms'])) {
					$send_results = $this->SendSMS();
					
					//$send_results_html = '
					//	<pre>
					//		'.print_r($send_results, true).'
					//	</pre>
					//';
					
					$status = isset($send_results['status']) ? sanitize_text_field($send_results['status']) : '';
					
					if ($status == 'queued') {
						
						$to = isset($send_results['to']) ? sanitize_text_field($send_results['to']) : '';
						$from = isset($send_results['from']) ? sanitize_text_field($send_results['from']) : '';
						$body = isset($send_results['body']) ? sanitize_text_field($send_results['body']) : '';
					
						
						$send_results_html .= '
							<div class="notice notice-success is-dismissible">
								<p>Your message has been queued for delivery.</p>
							</div>
						';
						
						$send_results_msg_bubble .= '<div class="msg-bubble">';
							$send_results_msg_bubble .= '<div style="margin-bottom:5px;">';
								$send_results_msg_bubble .= '<b style="font-size:0.8em; font-weight:700;">Sent</b>';
								$send_results_msg_bubble .= '<b style="float:right; font-size:0.8em; font-weight:700;">'.$to.'</b>';
							$send_results_msg_bubble .= '</div>';
							$send_results_msg_bubble .= $body;
						$send_results_msg_bubble .= '</div>';
					}
					else {
						$send_results_html .= '
							<div class="notice notice-error is-dismissible">
								<p>There was an error sending your message.</p>
							</div>
							<div class="notice notice-error is-dismissible">
								<p>Please check and confirm that your settings are correct and that your recipient\'s phone number was entered correctly.</p>
							</div>
						';
					}
				}
				
				$html = '
					<div class="wrap">
						<h1 class="wp-heading-inline"></h1>
						
						'.$send_results_html.'
						
						<form method="post" style="display:inline-block;">
							<input type="hidden" name="send_sms" value="1" />
							
							
								
							<div style="display:inline-block;">
								<div>
									<div><b>To Phone</b></div>
									<input type="text" name="twilio_to_phone" placeholder="To Phone" value="" class="regular-text">
								</div>
								
								<br>
								
								<div>
									<div><b>Message</b></div>
									<textarea class="regular-text" name="twilio_msg" style="height:120px;"></textarea>
								</div>
								
								<br>
								
								<div>
									<button type="submit" class="button button-primary">
										Send Message
									</button>
								</div>
							</div>
							
						</form>
							
					</div>
					
				';
					
				echo $html;
			}
			
			private function SettingsPageHTML() {
				
				add_filter('admin_footer_text', function() {	// Add a footer that links to our website
					$this->AddAdminFooter();
				});
				
				$saved_message = '';
				if (isset($_POST['save_settings'])) {	// If we need to try saving settings...
					$this->SaveSettings();
					$saved_message = '
						<div class="notice notice-success is-dismissible">
							<p>Saved Settings</p>
						</div>
					';
				}
				
				$twilio_account_sid = get_option('twilio_account_sid', '');
				$twilio_auth_token = get_option('twilio_auth_token', '');
				$twilio_phone = get_option('twilio_phone', '');
				
				$html = '
					<div class="wrap">
						<h1 class="wp-heading-inline">TwiSMS Settings</h1>
						
						'.$saved_message.'
						
						<form method="post">
							<input type="hidden" name="save_settings" value="1" />
							
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row">Account SID</th>
										<td>
											<input type="text" name="twilio_account_sid" placeholder="Twilio Account SID" value="'.$twilio_account_sid.'" class="regular-text">
										</td>
									</tr>
									<tr>
										<th scope="row">Auth Token</th>
										<td>
											<input type="text" name="twilio_auth_token" placeholder="Twilio Auth Token" value="'.$twilio_auth_token.'" class="regular-text">
										</td>
									</tr>
									<tr>
										<th scope="row">Twilio Phone Number</th>
										<td>
											<input type="text" name="twilio_phone" placeholder="+12223334444" value="'.$twilio_phone.'" class="regular-text">
										</td>
									</tr>
									<tr>
										<th scope="row"></th>
										<td>
											<button type="submit" class="button button-primary">
												Save
											</button>
										</td>
									</tr>
									<tr>
										<th scope="row">Don\'t have a Twilio account?</th>
										<td>
											Visit Twilio\'s website to <a href="https://www.twilio.com/referral/jglM1h" target="_blank">Sign Up for your Twilio account</a> and retrieve your <b>Account SID</b>, <b>Auth Token</b>, and <b>Phone Number</b>.
										</td>
									</tr>
									<tr>
										<th scope="row"></th>
										<td>
											<p class="description">
												Twilio is a cloud communications platform that allows users to send and receive text messages.
											</p>
										</td>
									</tr>
								</tbody>
							</table>
							
						</form>
						
						
					</div>
				';
					
				echo $html;
			}
			
			private function SaveSettings() {
				
				// Get the settings
					$twilio_account_sid = isset($_POST['twilio_account_sid']) ? sanitize_text_field($_POST['twilio_account_sid']) : '';
					$twilio_auth_token = isset($_POST['twilio_auth_token']) ? sanitize_text_field($_POST['twilio_auth_token']) : '';
					$twilio_phone = isset($_POST['twilio_phone']) ? sanitize_text_field($_POST['twilio_phone']) : '';
				
				// Save the settings
					update_option('twilio_account_sid', $twilio_account_sid);
					update_option('twilio_auth_token', $twilio_auth_token);
					update_option('twilio_phone', $twilio_phone);
					
			}
			
			private function SendSMS() {
				$results = '';
				
				// Get phone and message
					$twilio_to_phone = isset($_POST['twilio_to_phone']) ? sanitize_text_field($_POST['twilio_to_phone']) : '';
					$twilio_msg = isset($_POST['twilio_msg']) ? sanitize_text_field($_POST['twilio_msg']) : '';
					
				// Get the other relevant data
					$twilio_account_sid = get_option('twilio_account_sid', '');
					$twilio_auth_token = get_option('twilio_auth_token', '');
					$twilio_phone = get_option('twilio_phone', '');
				
				// Send the SMS
					$results = $this->CURL_SendSMS($twilio_account_sid, $twilio_auth_token, $twilio_phone, $twilio_to_phone, $twilio_msg);
				
				return ($results);
			}
			
			private function CURL_SendSMS($twilio_account_sid, $twilio_auth_token, $twilio_phone, $twilio_to_phone, $twilio_msg) {
				
				// URL to send data to
					$url = 'https://api.twilio.com/2010-04-01/Accounts/'.$twilio_account_sid.'/Messages.json';
				
				
				// Set the data we will post to Twilio
					$data = array(
						'From' => $twilio_phone,
						'To' => $twilio_to_phone,
						'Body' => $twilio_msg,
					);
					
				// Set the authorization header
					$basic_auth = 'Basic ' . base64_encode($twilio_account_sid . ':' . $twilio_auth_token);
					$headers = array( 
						'Authorization' => $basic_auth,
					);
					
					$args = array(
						'body' => $data,
						'timeout' => '60',
						'headers' => $headers,
					);
					
					$response = wp_remote_post($url, $args);
					$json = json_decode($response['body'], true);	// Convert the returned JSON string into an array
				
				return ($json);
			}
			
			
		// Message to output in the WordPress admin footer
			private function AddAdminFooter() {
				echo 'Plain Plugins | Check out our website at <a href="https://plainplugins.altervista.org" target="_blank">plainplugins.altervista.org</a> for more plugins!';
			}
	}
	
	TwiSMS::Instantiate();	// Instantiate an instance of the class
	
	
	
	