<?php
/**
 * Plugin Name: Simple Short Links
 *
 * Description: Use WordPress native shortlinks on your blog's domain.
 *
 * This plugin makes the following changes to your WordPress installation:
 *   1 database item named 'miqro_shortlinks' added to the options table.
 *
 * The included uninstall.php script automatically removes all changes
 * if the plugin is uninstalled by clicking the Delete link in the
 * plugins administration page.
 *
 * Plugin URI: http://www.miqrogroove.com/pro/software/
 * Author URI: http://www.miqrogroove.com/
 *
 * @author: Robert Chapin (miqrogroove)
 * @version: 1.6.1
 * @copyright Copyright © 2009-2012 by Robert Chapin
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

if (!function_exists('get_bloginfo')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

add_action('wp', 'miqro_shortlink_status', 10, 0);
add_filter('pre_get_shortlink', 'miqro_shortlink_query', 10, 3); //see wp-includes/link-template.php
add_action('admin_menu', 'miqro_shortlink_add_page', 10, 0);
register_activation_hook(__FILE__, 'miqro_shortlink_install');


/* Template Functions */

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

    } elseif (is_singular()) {

        if (empty($title)) $title = esc_attr(single_post_title('', FALSE));

    } else {

        //  Tag GUIDs are not supported.  See http://core.trac.wordpress.org/ticket/11711
    
        return;
    }
    
    $url = miqro_shortlink_query(0, 0, 'query');
    if (FALSE !== $url) echo "<a rel='shortlink' href='$url' title='$title'>$text</a>";
}


/* Plugin Functions */

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
        } elseif (is_category()) {
            $type = 'cat';
        } else {
            return FALSE;
        }
        $id = $wp_query->get_queried_object_id();
    } elseif ('post' == $context) {
        $type = 'post';
        if (0 == $id) {
            $post = get_post();
            if (isset($post->ID)) {
                $id = $post->ID;
            }
        }
    } elseif ('media' == $context) {
        $type = 'post';
    } else {
        return FALSE;
    }

    if ($id <= 0) return FALSE;

    return miqro_get_the_shortlink($id, $type);
}

/**
 * Create a shortlink given an ID from the posts table.
 *
 * @param int $pid ID number from the posts table.
 * @param string $type Optional.  Should be 'cat' or 'post' if specified.
 * @return string The short link absolute URL.
 */
function miqro_get_the_shortlink($pid, $type='post') {
    $pid = (int) $pid;
    $options = get_option('miqro_shortlinks');

    switch ($type) {
    case 'cat':
        $query = "?cat=$pid";
        break;
    case 'post':
    default:
        $query = "?p=$pid";
    }

    $output = home_url('/');

    if (!empty($options['remove_slash'])) $output = untrailingslashit($output);
    if (!empty($options['remove_www'])) $output = str_replace('//www.', '//', $output);

    $output .= $query;

    return $output;
}

/**
 * Check for any disabled settings.
 *
 * @since 1.6
 */
function miqro_shortlink_status() {
    $options = get_option('miqro_shortlinks');

    if (!empty($options['disable_pages']) and is_page()
     or !empty($options['disable_posts']) and is_single() and !is_attachment()
     or !empty($options['disable_cats']) and is_category()
     or !empty($options['disable_att']) and is_attachment() ) {

        miqro_shortlink_unhook('both');

    } elseif (isset($options['protocol'])) {

        if (1 == $options['protocol']) {
            miqro_shortlink_unhook('html');
        } elseif (2 == $options['protocol']) {
            miqro_shortlink_unhook('http');
        }

    }
}

/**
 * Disable shortlinks for this request.
 *
 * @since 1.3
 */
function miqro_shortlink_unhook($prot = 'both') {
    if ($prot == 'http' or $prot == 'both')
        remove_action('template_redirect', 'wp_shortlink_header', 11, 0); //see wp-includes/default-filters.php

    if ($prot == 'html' or $prot == 'both')
        remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
}

/**
 * Add a page to the Settings menu and do remaining admin tasks.
 *
 * @since 1.6
 */
function miqro_shortlink_add_page() {
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'miqro_shortlink_action_links', 10, 1);

    add_options_page('Short Link Settings', 'Short Links', 'manage_options', 'shortlinks', 'miqro_shortlink_page');
    register_setting( 'shortlink_settings', 'miqro_shortlinks', 'miqro_shortlink_settings_validate' );
    add_settings_section('shortlinks_main', 'Main Settings', 'shortlinks_section_text', 'shortlinks');
    add_settings_field('shortlinks_remove_www', 'Remove www Subdomain', 'miqro_shortlink_www_option_html', 'shortlinks', 'shortlinks_main');
    add_settings_field('shortlinks_remove_slash', 'Remove Extra Slash', 'miqro_shortlink_slash_option_html', 'shortlinks', 'shortlinks_main');
    add_settings_field('shortlinks_disable_pages', 'Disable for Pages', 'miqro_shortlink_pages_option_html', 'shortlinks', 'shortlinks_main');
    add_settings_field('shortlinks_disable_posts', 'Disable for Posts', 'miqro_shortlink_posts_option_html', 'shortlinks', 'shortlinks_main');
    add_settings_field('shortlinks_disable_cats', 'Disable for Categories', 'miqro_shortlink_cats_option_html', 'shortlinks', 'shortlinks_main');
    add_settings_field('shortlinks_disable_att', 'Disable for Attachments', 'miqro_shortlink_att_option_html', 'shortlinks', 'shortlinks_main');
    add_settings_field('shortlinks_protocol', 'Automatic Output', 'miqro_shortlink_protocol_option_html', 'shortlinks', 'shortlinks_main');
}

/**
 * Add a Settings link to the Plugins page.
 *
 * @since 1.6
 */
function miqro_shortlink_action_links($actions) {
    array_unshift($actions, '<a href="options-general.php?page=shortlinks" title="Go to settings for this plugin">Settings</a>');
    return $actions;
}

/**
 * Display the settings page.
 *
 * @since 1.6
 */
function miqro_shortlink_page() {
?>
<div class="wrap">
<?php screen_icon(); ?>
<h2>Short Link Settings</h2>
Options relating to the Simple Short Links plugin.
<form action="options.php" method="post">
<?php settings_fields('shortlink_settings'); ?>
<?php do_settings_sections('shortlinks'); ?>

<?php submit_button(); ?>
</form>
</div>
<?php
}

/**
 * Display the settings section text.
 *
 * @since 1.6
 */
function shortlinks_section_text() {
    echo '<p>These are advanced fine-tuning options.  The defaults will work just fine.</p>';
}

/**
 * Display the www domain option.
 *
 * @since 1.6
 */
function miqro_shortlink_www_option_html() {
    $options = get_option('miqro_shortlinks');
    if (!empty($options['remove_www'])) {
        $checked = "checked='checked'";
    } else {
        $checked = "";
    }
    echo "<input name='miqro_shortlinks[remove_www]' type='checkbox' value='true' $checked />";
    $site = parse_url(home_url());
    if (substr($site['host'], 0, 4) == 'www.') {
        echo ' ..... Short links like <code>', str_replace('//www.', '//', home_url('/?p=1')), '</code> instead of <code>', home_url('/?p=1'), '</code>';
        echo '<br />Take care with this setting to ensure it will not cause multiple redirects or canonicalization issues for your site.';
    } else {
        echo ' ..... This setting only applies to sites with "www" in the domain name.';
    }
}

/**
 * Display the null path option.
 *
 * @since 1.6
 */
function miqro_shortlink_slash_option_html() {
    $options = get_option('miqro_shortlinks');
    if (!empty($options['remove_slash'])) {
        $checked = "checked='checked'";
    } else {
        $checked = "";
    }
    echo "<input name='miqro_shortlinks[remove_slash]' type='checkbox' value='true' $checked />";
    echo ' ..... Short links like <code>', untrailingslashit(home_url('/')), '?p=1</code> instead of <code>', home_url('?p=1'), '</code>';
    $site = parse_url(home_url('/'));
    if ($site['path'] != '/') {
        echo '<br />Take care with this setting to ensure it will not cause multiple redirects for your site.';
    }
}

/**
 * Display the disable for pages option.
 *
 * @since 1.6
 */
function miqro_shortlink_pages_option_html() {
    $options = get_option('miqro_shortlinks');
    if (!empty($options['disable_pages'])) {
        $checked = "checked='checked'";
    } else {
        $checked = "";
    }
    echo "<input name='miqro_shortlinks[disable_pages]' type='checkbox' value='true' $checked />";
}

/**
 * Display the disable for posts option.
 *
 * @since 1.6
 */
function miqro_shortlink_posts_option_html() {
    $options = get_option('miqro_shortlinks');
    if (!empty($options['disable_posts'])) {
        $checked = "checked='checked'";
    } else {
        $checked = "";
    }
    echo "<input name='miqro_shortlinks[disable_posts]' type='checkbox' value='true' $checked />";
}

/**
 * Display the disable for categories option.
 *
 * @since 1.6
 */
function miqro_shortlink_cats_option_html() {
    $options = get_option('miqro_shortlinks');
    if (!empty($options['disable_cats'])) {
        $checked = "checked='checked'";
    } else {
        $checked = "";
    }
    echo "<input name='miqro_shortlinks[disable_cats]' type='checkbox' value='true' $checked />";
}

/**
 * Display the disable for attachments option.
 *
 * @since 1.6
 */
function miqro_shortlink_att_option_html() {
    $options = get_option('miqro_shortlinks');
    if (!empty($options['disable_att'])) {
        $checked = "checked='checked'";
    } else {
        $checked = "";
    }
    echo "<input name='miqro_shortlinks[disable_att]' type='checkbox' value='true' $checked />";
}

/**
 * Display the automatic output type option.
 *
 * @since 1.6
 */
function miqro_shortlink_protocol_option_html() {
    $options = get_option('miqro_shortlinks');
    $s0 = '';
    $s1 = '';
    $s2 = '';
    if (empty($options['protocol'])) {
        $s0 = "checked='checked'";
    } elseif ($options['protocol'] == 1) {
        $s1 = "checked='checked'";
    } else {
        $s2 = "checked='checked'";
    }
    echo "<label><input name='miqro_shortlinks[protocol]' type='radio' value='0' $s0 /> HTTP and HTML Headers <em>(default)</em></label>";
    echo "<br /><label><input name='miqro_shortlinks[protocol]' type='radio' value='1' $s1 /> HTTP Only <em>(not compatible with WP Super Cache)</em></label>";
    echo "<br /><label><input name='miqro_shortlinks[protocol]' type='radio' value='2' $s2 /> HTML Only</label>";
}

/**
 * Check user input.
 *
 * @since 1.6
 */
function miqro_shortlink_settings_validate($input) {
    $save = get_option('miqro_shortlinks');
    if (!is_array($save)) $save = array();
    $save['remove_www'] = !empty($input['remove_www']);
    $save['remove_slash'] = !empty($input['remove_slash']);
    $save['disable_pages'] = !empty($input['disable_pages']);
    $save['disable_posts'] = !empty($input['disable_posts']);
    $save['disable_cats'] = !empty($input['disable_cats']);
    $save['disable_att'] = !empty($input['disable_att']);
    $save['protocol'] = intval($input['protocol']);
    return $save;
}

/**
 * Initialize option storage.
 *
 * @since 1.6
 */
function miqro_shortlink_install() {
    if (!current_user_can('activate_plugins')) wp_die('Unexpected permissions fault in the Simple Short Links plugin.');

    $save['remove_www'] = FALSE;
    $save['remove_slash'] = FALSE;
    $save['disable_pages'] = FALSE;
    $save['disable_posts'] = FALSE;
    $save['disable_cats'] = FALSE;
    $save['disable_att'] = FALSE;
    $save['protocol'] = 0;

    $options = get_option('miqro_shortlinks');

    if (is_array($options))
        $save = array_merge($save, $options);

    update_option('miqro_shortlinks', $save);
}
?>
