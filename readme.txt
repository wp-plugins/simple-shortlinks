=== Simple Short Links ===
Contributors: miqrogroove
Tags: shortlinks, short, links, url, tiny
Requires at least: 2.5
Tested up to: 2.9
Stable tag: 1.1

Automatically advertise shortlinks using WordPress native ID forwarding.

== Description ==

Advertises short URLs similar to post GUIDs using page headers.  These can be useful for micro-blogging, and allow you to use your own domain instead of a 3rd-party short URL service.

A template tag enables you to display a human-readable link in addition to the automatically generated headers.

== Installation ==

1. Upload the `simple-shortlinks` directory to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

This is a zero-configuration plugin.  There are no settings.

Deactivation removes everything except the files you uploaded.  There is no "uninstall" necessary.

== Changelog ==

= 1.1 =
* Added a template tag, released 26 December 2009

= 1.0 =
* First version, released 24 December 2009

== Theme ==

Here is a basic reference for the template functions provided by Simple Short Links.

`
/**
 * Template Tag for Displaying the Short Link
 *
 * Must be called from inside "The Loop"
 *
 * Call like <?php the_shortlink(__('Shortlinkage FTW')); ?>
 *
 * @since 1.1
 * @param string $text Optional The link text or HTML to be displayed.  Defaults to 'This is the short link.'
 * @param string $title Optional The tooltip for the link.  Must be sanitized.  Defaults to the sanitized post title.
 */
function the_shortlink($text = '', $title = '');

`

If you use that template tag at all, you should also add this contingency to your theme's functions.php file:

`
if (!function_exists('the_shortlink')) {
    function the_shortlink($a = '', $b = '') {
        return; //Just define this function in case its plugin is ever missing.
    }
}

`