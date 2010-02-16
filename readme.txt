=== Simple Short Links ===
Contributors: miqrogroove
Tags: shortlinks, short, links, url, tiny, micro, shortening
Requires at least: 2.6
Tested up to: 3.0
Stable tag: 1.3

Automatically advertise shortlinks on your blog's domain using WordPress native ID forwarding.

== Description ==

Advertises short URLs similar to post GUIDs.

http://blog.yourdomain.com/?p=1234

These can be useful for micro-blogging, and they allow you to use your own domain instead of a 3rd-party short URL service.

The URLs are automatically added to the HTTP and HTML headers of each post, page, attachment, and category.

A template tag enables you to display a human-readable link in addition to the automatically generated headers.  Use of human-readable links is highly encouraged, because Google is known to favor them and rank them higher than some longer URLs.  This has the effect of boosting the rank of URLs that were deemed "too long" by Google's standards.

Simple Short Links was designed to do this with no frills, and with an eye on eventually incorporating some or all of its basic functionality into the WordPress core. One benefit of the no-frills system is that you will never worry about the forwarding service itself, which is already built in to WordPress. This plugin simply reveals hidden short URLs that already work on your blog.

One disadvantage of this bare-bone simplicity is there will be no short links for tags or external URLs.

== Installation ==

1. Upload the `simple-shortlinks` directory to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

This is a zero-configuration plugin.  There are no settings.

Deactivation removes everything except the files you uploaded.  There is no "uninstall" necessary.

== Frequently Asked Questions ==

= Can I see a sample of your short url for wordpress? =

Yes.  You can see this plugin live at my friend's blog.  For example:

[http://blogyul.miqrogroove.com/?p=4517](http://blogyul.miqrogroove.com/?p=4517)

That link will forward to a specific attachment from a December 2009 article.  Here is an example of a category short link:

[http://blogyul.miqrogroove.com/?cat=331](http://blogyul.miqrogroove.com/?cat=331)

This plugin is also compatible with qTranslate, so you can see paths like /zh/?p=4517 when both plugins are installed.


= Is it only the page or the post url that it shortens? =

Short Links are extra URLs that get forwarded to the normal URLs.  The existing URLs stay the same.

This does work for pages, posts, and categories, yes.  This particular plugin does not make short links for tags.


= Does it take the header as the description? =

Activating this plugin adds two invisible links to each post.  That part of the protocol does not involve descriptions.

The template tag, when added to your theme, does use a normal hyperlink with a title description.
The template tag is customizable.


== Changelog ==

= 1.3 =
* New features, released 11 February 2010
* Added short link support for categories.
* WordPress minimum raised to 2.6 from 2.5.
* WordPress 3.0-alpha tested 15 February 2010

= 1.2 =
* Minor bug fix, released 29 December 2009
* Always include a slash before ?p=
* FAQ Added
* WordPress 2.9.1-RC1 tested.

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


A second, similar tag is now available in case you need to display a self-referring short link on a category page.

`
/**
 * Template Tag for Displaying the Short Link for a Category
 *
 * Should be called from outside "The Loop"
 *
 * Call like <? the_single_shortlink(__('Shortlinkage FTW')); ?>
 *
 * @since 1.3
 * @param string $text Optional The link text or HTML to be displayed.  Defaults to 'This is the short link.'
 * @param string $title Optional The tooltip for the link.  Must be sanitized.  Defaults to the sanitized category name.
 */
function the_single_shortlink($text = '', $title = '');
`


There are some situations where you might not need to have short links.  For example, unlike post and attachment URLs, it is common for
blogs that have only a few page-type URLs to have relatively short page slugs already.  You could then safely disable
the short link headers for all page objects by adding this code to your theme's functions.php file:

`
if (function_exists('miqro_shortlink_unhook')) add_action('wp', 'shortlink_nopages', 10, 0);
function shortlink_nopages() {
    if (is_page()) miqro_shortlink_unhook('yes');
}

`