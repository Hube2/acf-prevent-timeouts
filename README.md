# Prevent ACF Timeouts

This plugin is for use with Advanced Custom Fields (ACF) and will work with any version of ACF
that includes the hooks 'acf/save_post' and 'acf/update_value'. As far as I know this means any
version of ACF past or future.

Have you ever created an extremely complex ACF field group for something like a page builder?
One day someone is editing a page and they've added more content and fields into the repeaters
and flexible content than it can handle and when they save it the page just times out.

This pluign is a means to prevent this situation. This plugin does not speed up the process of saving
the updates, but it does prevent the timeout error situation by intervening when the save process is
taking too long.

When it appears the the update is taking too long and a timeout may be iminent, this plugin
sends the user to a temporary page so that the update can continue in the background. Once the update is
complete then the user is sent on their way to the page that they were supposed to get to after the update.

![screenshot](https://github.com/Hube2/acf-prevent-timeouts/blob/master/screenshot-1.png)

Please note again, like I said above, this plugin does not correct the issue in ACF assocaited with
saving many custom fields. The bigger problem for me is the timeout that happens when the number of
fields becomes too large. While a slow admin may be a little frustrating to some, an admin page
that crashes is unexceptable. I may not be able to speed things up but I can stop the page from crashing.

## Important Note

Please ensure that your site and all ACF save processes are working correctly and without any errors
before activating this pluging. Any errors that may occur when the temporary page is being shown
will not be visible. These errors can prevent the correct saving of custom field data.


### Filters

There are some adjustments that you can make to this plugin using filters.

##### Timeout

The timeout is set to 20 seconds. When the page has been processing for this amount of time it will
trigger the temporary page will be shown. You can change this duration by adding the following filter
to your functions.php file
```
add_filter('acf/prevent-timeout/time', 'my_acf_timeout_time', 10, 2);
function my_acf_timeout_time($time, $post_id) {
  // set time to 10 seconds
	$time = 10;
	return $time;
}
```

### Updates

This plugin is currently set up to work with [GitHub Updater](https://github.com/afragen/github-updater).
If it seems popular and useful I will publish it to WordPress.org.
