<?php
/*
Plugin Name: Softaculous
Plugin URI: https://softaculous.com/wordpress-plugin/
Description: Softaculous provides a centralized panel where you can manage all your WordPress websites singularly, unitedly as well as efficiently.
Version: 2.2.2
Author: Softaculous
Author URI: https://softaculous.com
License: LGPL v2.1
License URI: https://www.gnu.org/licenses/old-licenses/lgpl-2.1.en.html
Text Domain: Softaculous
*/

/*
 * This file belongs to the softaculous plugin.
 *
 * (c) Softaculous <sales@softaculous.com>
 *
 * You can view the LICENSE file that was distributed with this source code
 * for copywright and license information.
 */

if (!defined('ABSPATH')){
    exit;
}

// Is the old plugin loaded ?
$softaculous_active_plugins = get_option('active_plugins');
if(in_array('wp-central/wpcentral.php', $softaculous_active_plugins)){
	deactivate_plugins(array('wp-central/wpcentral.php'));
}

define('SOFTACULOUS_VERSION', '2.2.2');
define('SOFTACULOUS_BASE', 'softaculous/softaculous.php');
define('SOFTACULOUS_PANEL', 'cloud.softaculous.com');
define('SOFTACULOUS_PANEL_IP', '138.201.40.162');
define('SOFTACULOUS_WWW_URL', 'https://softaculous.com/');
define('SOFTACULOUS_ADDSITE', 'https://cloud.softaculous.com/index.php?act=wpc_addsite&auth_type=wordpress');
define('SOFTACULOUS_PLUGIN_URL', plugin_dir_url(__FILE__));

include_once(dirname(__FILE__).'/functions.php');

// Activation Hook
register_activation_hook(__FILE__, 'softaculous_activation_hook');

// De-activation Hook
register_deactivation_hook(__FILE__, 'softaculous_deactivation_hook');

add_action('plugins_loaded', 'softaculous_load_plugin');
