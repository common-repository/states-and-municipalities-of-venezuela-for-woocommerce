<?php

/*
 * Plugin Name: States and Municipalities of Venezuela for WooCommerce 
 * Plugin URI: https://wordpress.org/plugins/states-and-municipalities-of-venezuela-for-woocommerce/
 * Description: This plugin allows you to choose the States and Municipalities of Venezuela in the WooCommerce address forms.
 * Author: Yordan Soares
 * Author URI: https://yordansoar.es/ 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: states-and-municipalities-of-venezuela-for-woocommerce
 * Domain Path: /languages
 * Version: 1.2
 * Requires at least: 4.6
 * Requires PHP: 7.0
 * WC requires at least: 3.0.x
 * WC tested up to: 6.2
*/

// Exit if file is open directly
if (!defined('ABSPATH')) {
	exit;
}

// Check if WooCommerce is active
function smvw_is_woocommerce_active() {
	$active_plugins = (array) get_option('active_plugins', array());
	// Check if the WP install is multisite
	if (is_multisite()) {
		$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	}
	return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) || class_exists('WooCommerce');
}

// Check if States, Cities, and Places for WooCommerce is active
function smvw_is_states_cities_and_places_wc_active() {
	if (in_array('states-cities-and-places-for-woocommerce/states-cities-and-places-for-woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
		$is_active = true;
	}
	return $is_active;
}

if (smvw_is_woocommerce_active() && !smvw_is_states_cities_and_places_wc_active() ) {
	// Prepare the init function
	function smvw_init() {
		// Define the constants for plugin URL and PATH
		define('SMVW_PLUGIN_URL', plugin_dir_url(__FILE__));
		define('SMVW_PLUGIN_PATH', plugin_dir_path(__FILE__));

		// Load text domain for internationalitation
		load_plugin_textdomain('states-and-municipalities-of-venezuela-for-woocommerce', FALSE,	dirname(plugin_basename(__FILE__)) . '/languages');

		// Get the Class WC_Venezuelan_Municipalities_Select
		require_once('includes/class-wc-venezuelan-municipalities-select.php');

		// Instantiate the WC_Venezuelan_Municipalities_Select class in $_GLOBALS variable
		$GLOBALS['wc_municipality_select'] = new WC_Venezuelan_Municipalities_Select(__FILE__);

		// Get the States of Venezuela
		require_once('states/VE.php');

		// Insert the States into WooCommerce Options
		add_filter('woocommerce_states', 'smvw_venezuelan_states');
		
		// Instantiate the WC_Venezuela_Custom_Locale() class
		require_once('includes/class-wc-venezuela-locale.php');
	}	
	// Fires the init function
	add_action('plugins_loaded', 'smvw_init', 10);
	
	// if States, Cities, and Places for WooCommerce is active
} elseif ( smvw_is_states_cities_and_places_wc_active() ) {
	
	function smvw_is_states_cities_and_places_wc_deactivation() 	{
		// ...shows a notice explaining why the plugin deactivates
		echo '
		<div class="notice notice-error is-dismissible">
		<p>' . wp_sprintf(
			// translators: 1. <strong>, 1. </strong>
			__('%1$sStates and Municipalities of Venezuela for WooCommerce%2$s is not necessary if %1$sStates, Cities, and Places for WooCommerce%2$s is installed, as this includes the locations of Venezuela. Therefore, the plugin has been deactivated.', 'states-and-municipalities-of-venezuela-for-woocommerce'), '<strong>', '</strong>' ) . '</p>
			</div>
			';
			// And deactivate the plugin until WooCommerce is active
			deactivate_plugins(plugin_basename(__FILE__));
		}
		add_action('admin_notices', 'smvw_is_states_cities_and_places_wc_deactivation');

	} else {

	// If WooCommerce isn't active...
	function smvw_woocommerce_required() 	{
		// ...shows a notice to asking for WooCommerce activation
		echo '
		<div class="notice notice-error is-dismissible">
		<p>' . wp_sprintf(
			// translators: 1. <strong>, 1. </strong>
			__('%1$sStates and Municipalities of Venezuela for WooCommerce%2$s requires %1$sWooCommerce%2$s activated. The plugin was deactivated until you active %1$sWooCommerce%2$s', 'states-and-municipalities-of-venezuela-for-woocommerce'), '<strong>', '</strong>' ) . '</p>
		</div>
		';
		// And deactivate the plugin until WooCommerce is active
		deactivate_plugins(plugin_basename(__FILE__));
	}
	add_action('admin_notices', 'smvw_woocommerce_required');

}
