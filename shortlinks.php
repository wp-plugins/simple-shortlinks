<?php
/**
 * Plugin Name: Simple Short Links
 *
 * Description: Use WordPress native shortlinks on your blog's domain.
 *
 * This plugin does not make any permanent changes.
 *
 * Plugin URI: http://blogyul.miqrogroove.com/about/
 * Author URI: http://www.miqrogroove.com/
 *
 * @author: Robert Chapin (miqrogroove)
 * @version: 1.4
 * @copyright Copyright © 2009-2010 by Robert Chapin
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

if (version_compare(get_bloginfo('version'), '3.0-alpha', '<')) {
    add_action('template_redirect', 'miqro_shortlink_http', 11, 0); //see wp-includes/template-loader.php  Priority must be > 10 to avoid canonical redirection.
    add_action('wp_head', 'miqro_shortlink_html', 10, 0);
} else {
    add_filter('pre_get_shortlink', 'miqro_shortlink_query', 10, 3); //see wp-includes/link-template.php
}


/* Template Functions */

/**
 * Template Tag for Displaying the Short Link for a Post
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

/**
 * Template Tag for Displaying the Short Link for a Category
 *
 * Should be called from outside "The Loop"
 *
 * Call like the_single_shortlink(__('Shortlinkage FTW'))
 *
 * @since 1.3
 * @param string $text Optional The link text or HTML to be displayed.  Defaults to 'This is the short link.'
 * @param string $title Optional The tooltip for the link.  Must be sanitized.  Defaults to the sanitized category name.
 */
function the_single_shortlink($text = '', $title = '') {
    global $wp_query;

    if (empty($text)) $text = 'This is the short link.';
    
    if (is_category()) {

        if (empty($title)) $title = esc_attr(single_cat_title('', FALSE));

    /*  Tag GUIDs are not supported.  See http://core.trac.wordpress.org/ticket/11711
    } elseif (is_tag()) {
        if (strlen($title) == 0) $title = esc_attr(single_tag_title('', FALSE));
    */
    
    } elseif (is_singular()) {

        if (empty($title)) $title = esc_attr(single_post_title('', FALSE));

    } else {

        return;
    }
    
    $url = miqro_shortlink_query(0, 0, 'query');
    if (FALSE !== $url) echo "<a rel='shortlink' href='$url' title='$title'>$text</a>";
}


/* Plugin Functions */

/**
 * Output a shortlink HTTP header.
 */
function miqro_shortlink_http() {
    if (headers_sent()) return;
    $url = miqro_shortlink_query(0, 0, 'query');
    if (FALSE !== $url) header('Link: <'.$url.'>; rel=shortlink', FALSE);
}

/**
 * Output a shortlink XHTML LINK element.
 */
function miqro_shortlink_html() {
    $url = miqro_shortlink_query(0, 0, 'query');
    if (FALSE !== $url) echo "<link rel='shortlink' href='".$url."' />\n";
}

/**
 * Determines type of request, then calls the shortlink generator.
 *
 * @since 1.4
 * @param bool $null
 * @param int $id The post id, if $context is 'post'
 * @param string $context 'query' if unknown, otherwise 'post', 'media', 'blog' per wp_get_shortlink()
 * @return string|bool The shortlink, if available, otherwise FALSE.
 */
function miqro_shortlink_query($null, $id, $context) {
    global $wp_query;

    if ('query' == $context) {
        // Check if post has a shortlink, but avoid feeds, redirects, etc.
        if (is_feed() or is_front_page() or is_trackback()) return FALSE;

        if (is_singular()) {
            $type = 'post';
        /*
        } elseif (is_tag()) {
            $type = 'tag';
        */
        } elseif (is_category()) {
            $type = 'cat';
        } else {
            return FALSE;
        }
        $id = $wp_query->get_queried_object_id();
    } elseif ('post' == $context or 'media' == $context) {
        $type = 'post';
    } else {
        return FALSE;
    }

    if ($id <= 0) {
        return FALSE;
    } else {
        return miqro_get_the_shortlink($id, $type);
    }
}

/**
 * Create a shortlink given an ID from the posts table.
 *
 * This is sometimes different from the GUID.
 *
 * @param int $pid ID number from the posts table.
 * @param string $type Optional.  Should be 'cat' or 'post' if specified.
 * @return string The short link absolute URL.
 */
function miqro_get_the_shortlink($pid, $type='post') {
    $pid = (int) $pid;
    switch ($type) {
    case 'cat':
        $query = "?cat=$pid";
        break;
    /*
    case 'tag':
        $query = "?tag_id=$pid";
        break;
    */
    case 'post':
    default:
        $query = "?p=$pid";
    }
    return trailingslashit(get_bloginfo('url', 'display')).$query;
}

/**
 * Disable shortlinks for this request.
 *
 * @since 1.3
 */
function miqro_shortlink_unhook() {
    remove_action('template_redirect', 'miqro_shortlink_http', 11, 0);
    remove_action('template_redirect', 'wp_shortlink_header', 11, 0);
    remove_action('wp_head', 'miqro_shortlink_html', 10, 0);
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
}
?>
