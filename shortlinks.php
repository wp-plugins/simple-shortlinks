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
 * @version: 1.0
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

add_action('wp', 'miqro_shortlink_http', 10, 0);
add_action('wp_head', 'miqro_shortlink_html', 10, 0);

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
 */
function miqro_get_the_shortlink($pid) {
    $pid = (int) $pid;
    return user_trailingslashit(get_bloginfo('url', 'display'))."?p=$pid";
}
