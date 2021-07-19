=== ACF Prevent Timeouts ===
Contributors: Hube2
Tags: advanced custom fields, acf, add on, timeout, prevent
Requires at least: 4.0.0
Tested up to: 5.7 
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Eliminates ACF timeout Issues

== Description ==

This plugin is for use with Advanced Custom Fields (ACF) and will work with any version of ACF
that includes the hooks 'acf/save_post' and 'acf/update_value'. As far as I know this means any
version of ACF past or future.

Have you ever created an extremely complex ACF field groups for something like a page builder?
One day someone is editing a page and they've added more content and fields into the repeaters
and flexible content than it can handle and when they save it the page just times out?

This pluign is a means to prevent this situation. This plugin does not speed up the process of saving
the updates, but it does prevent the timeout error situation by intervening when the save process is
taking to long.

When it appears the the update is taking too long and a timeout may be iminent, this plugin
sends the user to a temporary page so that the update can continue in the background. Once the update is
complete then the user is sent on there way to the page that they were supposed to get to after the update.

Please note again, like I said above, this plugin does not correct the issue in ACF assocaited with
saving many custom fields. The bigger problem for me is the timeout that happens when the number of
fields becomes too large. While a slow admin may be a little frustrating to some, an admin page
that crashes is unexceptable. I may not be able to speed things up but I can stop the page from crashing.

***Important Note***

Please ensure that your site and all ACF save processes are working correctly and without any errors
before activating this pluging. Any errors that may occur when the temporary page is being shown
will not be visible. These errors can prevent the correct saving of custom field data.

== Screenshots ==

1. Temorary Page

== Installation ==

Intall like any other plugin

== Other Notes ==

== Filters ==

There are some adjustments that you can make to this plugin using filters.

**Timeout**

The timeout is set to 20 seconds. When the page has been processing for this amount of time it will
trigger the temporary page will be shown. You can change this duration by adding the following filter
to your functions.php file

`
add_filter('acf/prevent-timeout/time', 'my_acf_timeout_time');
function my_acf_timeout_time($time) {
  // set time to 10 seconds
	$time = 10;
	return $time;
}
`

== Changelog ==

= 1.0.0 =
Imporved filters by passing current post ID

= 0.0.1 =
* First commit to GitHub
