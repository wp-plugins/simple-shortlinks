=== Simple Short Links ===
Contributors: miqrogroove
Tags: shortlinks, shortlink, short, links, link, url, tiny, micro, shortening, shortener
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 1.6.1

Automatically advertise shortlinks on your blog's domain using WordPress native ID forwarding.

== Description ==

Advertises short URLs similar to post GUIDs.

http://blog.yourdomain.com/?p=1234

These can be useful for micro-blogging, and they allow you to use your own domain instead of a 3rd-party short URL service.

The URLs are automatically added to the HTTP and HTML headers of each post, page, attachment, and category.

A template tag enables you to display a human-readable link in addition to the automatically generated headers.  Use of human-readable links is highly encouraged, because Google is known to favor them and rank them higher than some longer URLs.  This has the effect of boosting the rank of URLs that were deemed "too long" by Google's standards.  Also, in many mobile web browsers, the easiest way for a visitor to find a short link is by seeing it somewhere on the page.

The template tag idea can be extended further with CSS code for print media, which could ensure that each article's short link URL is printed along with the article.  This will make it much easier for the user to return to the article after reading a hard copy.

Simple Short Links was designed to do this with no frills, and with an eye on eventually incorporating some or all of its basic functionality into the WordPress core. One benefit of the no-frills system is that you will never worry about the forwarding service itself, which is already built in to WordPress. This plugin simply reveals hidden short URLs that already work on your blog.

One disadvantage of this bare-bone simplicity is there will be no short links for tags or external URLs.

= Older Versions =

WordPress 2.6 through 2.9.2, please use Simple Short Links v1.4.1.

WordPress 2.5, please use Simple Short Links v1.2.

== Installation ==

1. Upload the `simple-shortlinks` directory to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

The plugin is fully functional at activation.  A settings page is included for advanced configuration.

Settings will be preserved during deactivation.  An "uninstall" script is included, which will automatically delete the settings data if you click the Delete on the Plugins screen.

== Frequently Asked Questions ==

= Can I see a sample of your short url for wordpress? =

Yes.  You can see this plugin live on my personal blog.  For example:

[miqrogroove.com?p=788](http://miqrogroove.com?p=788)

That short link will forward to a specific post permalink from a September 2012 article.  Here is an example of a category short link:

[miqrogroove.com?cat=12](http://miqrogroove.com?cat=12)

This plugin is also compatible with qTranslate, so you can see paths like /zh/?p=4517 when both plugins are installed.

[http://blogyul.miqrogroove.com/zh/?p=4517](http://blogyul.miqrogroove.com/zh/?p=4517)

Notice how much longer the permalinks may become when the short links are clicked!


= Is it only the page or the post url that it shortens? =

Short Links are extra URLs that get forwarded to the normal URLs.  The existing URLs stay the same.

This does work for:

* Posts
* Pages
* Attachments
* Categories

This particular plugin does not make short links for:

* Tags
* Image files
* Other websites


= Does it take the header as the description? =

Activating this plugin adds two invisible links to each post.  That part of the protocol does not involve descriptions.

The template tag, when added to your theme, does use a normal hyperlink with a title description.
The template tag is customizable.


== Changelog ==

= 1.6.1 =
* Minor updates, released 23 November 2012.
* Cosmetic improvements on the settings page.
* WordPress 3.5-RC1 tested.

= 1.6 =
* New features, released 3 November 2012.
* Added a settings page for advanced configuration.
* Links can be slightly shorter in some cases.
* Links can be disabled by content type.
* Either of the header types can be disabled.

= 1.5 =
* Minor updates, released 5 January 2012.
* WordPress minimum raised to 3.0 from 2.6.
* WordPress 3.2.1 tested.
* WordPress 3.3.3 tested. 8 Sep 2012.
* WordPress 3.4.2 tested. 9 Sep 2012.

= 1.4.1 =
* Alpha compatibility, released 12 March 2010
* Template tag the_shortlink() is now in core. :)
* WordPress 3.1.4 tested 21 July 2011.

= 1.4 =
* Alpha compatibility, released 10 March 2010
* Fixed cosmetic linefeed issue in XHTML headers.
* Header hooks updated for yesterday's core changes.

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

Here is a basic reference for the template functions provided by Simple Short Links.  WordPress 3.0+ users, see also wp-includes/link-template.php for more details about the_shortlink().

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

= The CSS Printing Trick =

Try adding something like this to your theme's CSS file to make the URL for the short link appear when printed.

`
@media print {
 .shortlink a:link:after {
	content: " " attr(href);
 }
}
`
The CSS example assumes the_shortlink() is used inside of a DIV or P element with a class attribute called "shortlink".