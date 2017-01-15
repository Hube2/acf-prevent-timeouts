// JavaScript Document

/*
		1) check for completion
		2) When complete check for redirect url
		3) When redirect url returned send clear
		4) When clear confirmation recieved, redirect
*/
	
	var acf_prevent_timeouts_timout = null;
	
	jQuery(document).ready(function($) {
		acf_prevent_timeouts_timout = setTimeout(acf_prevent_timeouts_refresh(), acf_prevent_timouts.refresh_rate);
	});
	
	function acf_prevent_timeouts_refresh() {
		clearTimeout(acf_prevent_timeouts_timout);
		acf_prevent_timouts.requests_sent++;
		var url = acf_prevent_timouts.url
		jQuery.get(
			acf_prevent_timouts.url, {
				'action': acf_prevent_timouts.action,
				'id': acf_prevent_timouts.id,
				'set_clear': acf_prevent_timouts.set_clear
			},
			function(json) {
				acf_prevent_timouts.replies_recieved++;
				for (i in json) {
					acf_prevent_timouts[i] = json[i];
				}
				acf_prevent_timeouts_check_response();
			},
			'json'
		);
	} // end function acf_prevent_timeouts_refresh
	
	function acf_prevent_timeouts_check_response() {
		$ = jQuery;
		if (acf_prevent_timouts.all_clear) {
			document.location = acf_prevent_timouts.redirect;
			return;
		}
		if (acf_prevent_timouts.redirect) {
			acf_prevent_timouts.set_clear = true;
		}
		$('#stand-by').append('.');
		if (acf_prevent_timouts.replies_recieved % 150 == 0) {
			$('#stand-by').append('<br>');
		}
		acf_prevent_timeouts_timout = setTimeout(acf_prevent_timeouts_refresh(), acf_prevent_timouts.refresh_rate);
	} // end function acf_prevent_timeouts_check_response