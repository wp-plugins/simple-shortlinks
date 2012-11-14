<?php
/**
 * Simple Short Links Uninstaller
 *
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
 
if (!function_exists('get_bloginfo')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

if (WP_UNINSTALL_PLUGIN != plugin_basename(dirname(__FILE__).'/shortlinks.php')
 or !current_user_can('activate_plugins')) {

    wp_die('Unexpected permissions fault at the Simple Short Links uninstaller.');

}

delete_option('miqro_shortlinks');
?>
