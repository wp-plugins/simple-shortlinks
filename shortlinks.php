<?php
/**
 * Plugin Name: Simple Short Links
 *
 * Description: Use WordPress native ID forwarding for HTTP and HTML shortlinks.
 *
 * This plugin does not make any permanent changes.
 *
 * Plugin URI: http://blogyul.miqrogroove.com/about/
 * Author URI: http://www.miqrogroove.com/
 *
 * @author: Robert Chapin (miqrogroove)
 * @version: 1.2
 * @copyright Copyright © 2009 by Robert Chapin
 * @license GPL
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
/* Plugin Bootup */

add_action('wp', 'miqro_shortlink_http', 10, 0);
add_action('wp_head', 'miqro_shortlink_html', 10, 0);


/* Template Functions */

/**
 * Template Tag for Displaying the Short Link
 *
 * Must be called from inside "The Loop"
 *
 * Call like the_shortlink(__('Shortlinkage FTW'))
 *
 * @since 1.1
 * @param string $text Optional The link text or HTML to be displayed.  Defaults to 'This is the short link.'
 * @param string $title Optional The tooltip for the link.  Must be sanitized.  Defaults to the sanitized post title.
 */
function the_shortlink($text = '', $title = '') {
    global $post;
    if (strlen($text) == 0) $text = 'This is the short link.';
    if (strlen($title) == 0) $title = the_title_attribute(array('echo' => FALSE));
    $shortlink = miqro_get_the_shortlink($post->ID);
    echo "<a rel='shortlink' href='$shortlink' title='$title'>$text</a>";
}


/* Plugin Functions */

/**
 * Output a shortlink HTTP header.
 */
function miqro_shortlink_http() {
    global $post;

    // Check if post has a shortlink, but avoid feeds, redirects, etc.
    if (isset($post->ID) and is_singular() and !(get_query_var('p') > 0)
     and !is_front_page() and !is_feed() and !is_trackback()
    ) {
        if($post->ID > 0 and !headers_sent()) {
            header('Link: <'.miqro_get_the_shortlink($post->ID).'>; rel=shortlink');
        }
    }
}

/**
 * Output a shortlink XHTML LINK element.
 */
function miqro_shortlink_html() {
    global $post;

    // Just check if post has a shortlink.
    if (isset($post->ID) and is_singular() and !is_front_page()) {
        if($post->ID > 0) {
            echo '<link rel="shortlink" href="'.miqro_get_the_shortlink($post->ID).'" />';
        }
    }
}

/**
 * Create a shortlink given an ID from the posts table.
 *
 * This is sometimes different from the GUID.
 *
 * @param int $pid ID number from the posts table.
 * @return string The short link absolute URL.
 */
function miqro_get_the_shortlink($pid) {
    $pid = (int) $pid;
    return trailingslashit(get_bloginfo('url', 'display'))."?p=$pid";
}
?>
