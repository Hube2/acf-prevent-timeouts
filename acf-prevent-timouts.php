<?php 

	/*
		Plugin Name: ACF Prevent Timeouts
		Plugin URI: https://github.com/Hube2/acf-prevent-timeouts
		GitHub Plugin URI: https://github.com/Hube2/acf-prevent-timeouts
		Description: Eliminates ACF Timout Issues
		Version: 0.0.1
		Author: John A. Huebner II
		Author URI: https://github.com/Hube2
		
	*/
	
	// exit if accessed directly
	if (!defined('ABSPATH')) {
		exit;
	}
	
	new acf_prevent_timeout();
	
	class acf_prevent_timeout {
		
		private $version = '0.0.1';
		private $start_time = NULL;
		private $max_time = 20; // max time in seconds to run before timeout page
		private $timed_out = false;
		private $complete = false; // set to true when flag is cleard
		private $error = false; // set to true if error is detected
		private $id = NULL; // a unique ID for this save action
		private $redirect = false; // set to redirect url when finsished
		private $error_text = '';
		private $all_clear = false;
		private $fields_processed = 0;
		
		public function __construct() {
			$this->start_time = microtime(true);
			add_action('init', array($this, 'init'));
			add_action('wp_ajax_acf_prevent_timout_refresh', array($this, 'refresh'));
		} // end public function __construct
		
		public function init() {
			$priority = apply_filters('acf/prevent-timeout/priority-before', -99999);
			add_action('acf/save_post', array($this, 'acf_save_post_before'), $priority);
		} // end public function init
		
		public function acf_save_post_before($post_id) {
			// this function runs before all other acf/save_post actions
			// make sure there isn't a flag set for post id
			// if there is then clear it
			// set uniqid
			$this->id = uniqid('acf-prevent-timeouts-', true);
			$this->max_time = apply_filters('acf/prevent-timout/time', $this->max_time);
			
			add_action('acf/update_value', array($this, 'update_value'), 10, 3);
		} // end public function acf_save_post_before
		
		public function update_value($value, $post_id, $field) {
			// this function will run every time a field value is updates
			// if we reach max time then output will be sent to the browser
			// time limit will be set
			// flag will be set for the post id
			// executions will continue
			$this->fields_processed++;
			
			$time_now = microtime(true);
			if ($time_now - $this->start_time < $this->max_time) {
				return $value;
			}
			// timed out
			$this->output_and_continue();
			$this->timed_out = true;
			$this->update_option();
			// remove this filter and add a filter to check for errors
			remove_filter('acf/update_value', array($this, 'update_value'));
			add_action('acf/update_value', array($this, 'count_fields_processed'), 10, 3);
			
			// add action to fire after update
			$priority = apply_filters('acf/prevent-timeout/priority-after', 99999);
			add_action('acf/save_post', array($this, 'acf_save_post_after'), $priority);
			
			// don't forget to return $value
			return $value;
		} // end public function update_value
		
		public function count_fields_processed($value, $post_id, $field) {
			// this function will only run when max time is exceeded
			$this->fields_processed++;
			$this->update_option();
			return $value;
		} // end private function public
		
		private function output_and_continue() {
			while (ob_get_level()) {
				ob_end_clean();
			}
			if (function_exists('apache_setenv')) {
				apache_setenv('no-gzip', 1);
			}
			header('X-Accel-Buffering: no');
			ini_set('zlib.output_compression', 0);
			set_time_limit(0);
			ignore_user_abort(true);
			ob_start();
			$this->timout_page();
			header('Connection: close');
			$size = ob_get_length();
			header('Content-Length: '.$size);
			ob_end_flush();
			flush();
			ob_start();
		} // end private function output_and_continue
		
		public function timout_page() {
			// this page is copied mostly fromt he WP function _default_wp_die_handler
			// with modification for use here
			// need to remove the styles that I'm not using
			nocache_headers();
			?><!DOCTYPE html>
<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono
-->
<html xmlns="http://www.w3.org/1999/xhtml" <?php 
		if (function_exists('language_attributes') && function_exists('is_rtl')) {
			language_attributes();
		}
	?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width">
		<?php
			if (function_exists('wp_no_robots')) {
				wp_no_robots();
			}
		?>
		<title><?php 
			$text = 'Processing Request';
			echo apply_filters('acf/prevent-timeout/timeout-page/title', $text); 
		?></title>
		<script type="text/javascript" src="<?php 
			echo 	includes_url('js/jquery/jquery.js');
			?>"></script>
		<script type="text/javascript" src="<?php 
			echo 	includes_url('js/jquery/jquery-migrate.js');
			?>"></script>
		<script type="text/javascript">
			<?php 
				$value = array(
					'url' => admin_url('admin-ajax.php'),
					'id' => $this->id,
					'action' => 'acf_prevent_timout_refresh',
					'timeout' => $this->timed_out,
					'error' => $this->error,
					'complete' => $this->complete,
					'redirect' => $this->redirect,
					'error_text' => $this->error_text,
					'set_clear' => false,
					'all_clear' => $this->all_clear,
					'requests_sent' => 0,
					'replies_recieved' => 0,
					'refresh_rate' => 1000,
					'fields_processed' => $this->fields_processed,
					'messages' => array(
						'error1' => 'PROCESSING ERROR!',
						'error2' => 'It appears that an error may have occurred during this process. Please contact your system administator.',
					)
				);
			?>
			var acf_prevent_timouts = <?php echo json_encode($value); ?>;
		</script>
		<script type="text/javascript" src="<?php 
			echo 	plugin_dir_url(__FILE__);
			?>acf-prevent-timeouts.js?version=<?php echo $this->version; ?>"></script>
		<style type="text/css">
			html {
				background: #f1f1f1;
			}
			body {
				background: #fff;
				color: #444;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				margin: 2em auto;
				padding: 1em 2em;
				max-width: 700px;
				-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
				box-shadow: 0 1px 3px rgba(0,0,0,0.13);
			}
			h1 {
				border-bottom: 1px solid #dadada;
				clear: both;
				color: #666;
				font-size: 24px;
				margin: 30px 0 0 0;
				padding: 0;
				padding-bottom: 7px;
			}
			#error-page {
				margin-top: 50px;
			}
			#error-page p {
				font-size: 14px;
				line-height: 1.5;
				margin: 25px 0 20px;
			}
			#error-page p.note {
				font-size: .8em;
				font-style: italic;
			}
			#error-page p.error {
				font-weight: bold;
				font-style: italic;
				color: #CC0000;
			}
			#error-page p#stand-by {
				font-weight: bold;
				font-style: italic;
			}
			#error-page code {
				font-family: Consolas, Monaco, monospace;
			}
			ul li {
				margin-bottom: 10px;
				font-size: 14px ;
			}
			a {
				color: #0073aa;
			}
			a:hover,
			a:active {
				color: #00a0d2;
			}
			a:focus {
				color: #124964;
					-webkit-box-shadow:
						0 0 0 1px #5b9dd9,
					0 0 2px 1px rgba(30, 140, 190, .8);
					box-shadow:
						0 0 0 1px #5b9dd9,
					0 0 2px 1px rgba(30, 140, 190, .8);
				outline: none;
			}
			.button {
				background: #f7f7f7;
				border: 1px solid #ccc;
				color: #555;
				display: inline-block;
				text-decoration: none;
				font-size: 13px;
				line-height: 26px;
				height: 28px;
				margin: 0;
				padding: 0 10px 1px;
				cursor: pointer;
				-webkit-border-radius: 3px;
				-webkit-appearance: none;
				border-radius: 3px;
				white-space: nowrap;
				-webkit-box-sizing: border-box;
				-moz-box-sizing:    border-box;
				box-sizing:         border-box;
	
				-webkit-box-shadow: 0 1px 0 #ccc;
				box-shadow: 0 1px 0 #ccc;
				vertical-align: top;
			}
			.button.button-large {
				height: 30px;
				line-height: 28px;
				padding: 0 12px 2px;
			}
			.button:hover,
			.button:focus {
				background: #fafafa;
				border-color: #999;
				color: #23282d;
			}
			.button:focus  {
				border-color: #5b9dd9;
				-webkit-box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
				box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
				outline: none;
			}
			.button:active {
				background: #eee;
				border-color: #999;
				-webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
				box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
				-webkit-transform: translateY(1px);
				-ms-transform: translateY(1px);
				transform: translateY(1px);
			}
		</style>
	</head>
	<body id="error-page">
		<h1><?php 
			$text = 'Your Request is Being Processed';
			echo apply_filters('acf/prevent-timeout/timeout-page/heading', $text); ?></h1>
		<p class="message">
			<?php 
				$text = 'It is taking longer than expected to process your request.';
				echo apply_filters('acf/prevent-timeout/timeout-page/message', $text);
			?>
		</p>
		<p id="stand-by">
			<?php 
				$text = 'Please Stand By.';
				echo apply_filters('acf/prevent-timeout/timeout-page/standby', $text);
			?>
		</p>
		<p class="note">
			<?php 
				$text = 'This is not an error.<br />'.
								'You will be redirected back to what you were doing as soon as the process in completed.';
				echo apply_filters('acf/prevent-timeout/timeout-page/note', $text);
			?>
		</p>
	</body>
</html><?php 
		} // end public function timout_page
		
		public function refresh() {
			$this->id = $_GET['id'];
			$this->get_option();
			if ($_GET['set_clear'] == 'false') {
				$_GET['set_clear'] = false;
			}
			if ($_GET['set_clear']) {
				// the timeout page has recieived the redirect value
				// is is save to delete the option and send the all clear
				// so that the timeout page can redirect to where it was supposed to go
				$this->delete_option();
				$this->all_clear = true;
			}
			$value = array(
				'error' => $this->error,
				'complete' => $this->complete,
				'redirect' => $this->redirect,
				'error_text' => $this->error_text,
				'all_clear' => $this->all_clear,
				'fields_processed' => $this->fields_processed
			);
			echo json_encode($value);
			exit;
		} // end public function refresh
		
		private function get_option() {
			$values = get_option($this->id, false);
			if ($values === false || !is_array($values) || empty($values)) {
				$this->error = true;
				return;
			}
			foreach ($values as $key => $value) {
				$this->{$key} = $value;
			}
		} // end private function get_option
		
		private function update_option() {
			$option_name = $this->id;
			$value = array(
				'timeout' => $this->timed_out,
				'error' => $this->error,
				'complete' => $this->complete,
				'redirect' => $this->redirect,
				'error_text' => $this->error_text,
				'all_clear' => $this->all_clear,
				'fields_processed' => $this->fields_processed
			);
			update_option($option_name, $value, true);
		} // end private funciton update_option
		
		private function delete_option() {
			delete_option($this->id);
		} // end private function delete_option
		
		public function acf_save_post_after($post_id) {
			// this function will only be called if max time was exceeded
			// acf save timed out
			$this->complete = true;
			$this->update_option();
			// add a filter to grab the ridirect url so it can be passed to timeout page
			add_filter('wp_redirect', array($this, 'wp_redirect'), 9999, 2);
		} // end public function acf_save_post_after
		
		public function wp_redirect($location, $status) {
			// this action will be called when wp_redirect is called
			// it will clear the flag and set the redirect value
			// to send on the next ajax request
			// grab the redirect and store it to pass to the timeout page and then exit
			$this->redirect = $location;
			$this->update_option();
			exit;
		} // end public function wp_redirect
		
	} // end class acf_prevent_timeout
	
?>