// JavaScript Document

/*
		1) check for completion
		2) When complete check for redirect url
		3) When redirect url returned send clear
		4) When clear confirmation recieved, redirect
*/

	var acf_prevent_timeouts_timeout = null;

	jQuery(document).ready(function($) {
		acf_prevent_timeouts_timeout = setTimeout(acf_prevent_timeouts_refresh(), acf_prevent_timeouts.refresh_rate);
	});

	function acf_prevent_timeouts_refresh() {
		clearTimeout(acf_prevent_timeouts_timeout);
		acf_prevent_timeouts.requests_sent++;
		var url = acf_prevent_timeouts.url
		jQuery.get(
			acf_prevent_timeouts.url, {
				'action': acf_prevent_timeouts.action,
				'id': acf_prevent_timeouts.id,
				'set_clear': acf_prevent_timeouts.set_clear
			},
			function(json) {
				acf_prevent_timeouts.replies_recieved++;
				for (i in json) {
					acf_prevent_timeouts[i] = json[i];
				}
				acf_prevent_timeouts_check_response();
			},
			'json'
		);
	} // end function acf_prevent_timeouts_refresh

	function acf_prevent_timeouts_check_response() {
		$ = jQuery;
		if (acf_prevent_timeouts.all_clear) {
			document.location = acf_prevent_timeouts.redirect;
			return;
		}
		if (acf_prevent_timeouts.redirect) {
			acf_prevent_timeouts.set_clear = true;
		}
		$('#stand-by').append('.');
		if (acf_prevent_timeouts.replies_recieved % 150 == 0) {
			$('#stand-by').append('<br>');
		}
		acf_prevent_timeouts_timeout = setTimeout(acf_prevent_timeouts_refresh(), acf_prevent_timeouts.refresh_rate);
	} // end function acf_prevent_timeouts_check_response
