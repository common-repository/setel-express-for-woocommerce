<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.setel.com/business/setel-express/
 * @since             1.0.0
 * @package           Setel_Express
 *
 * @wordpress-plugin
 * Plugin Name:       Setel Express
 * Description:       Setel Express integration for WooCommerce.
 * Version:           1.0.1
 * Author:            Setel Express
 * Author URI:        https://www.setel.com/business/setel-express/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       setel-express
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

const SETEL_EXPRESS_VERSION        = '1.0.1';
const SETEL_EXPRESS_API_BASE_URL   = 'https://api.logistika.com.my/api/';
const SETEL_EXPRESS_API_USER_AGENT = 'woo-plugin-client';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-setel-express-activator.php
 */
function activate_setel_express() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-setel-express-activator.php';
	Setel_Express_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-setel-express-deactivator.php
 */
function deactivate_setel_express() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-setel-express-deactivator.php';
	Setel_Express_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_setel_express' );
register_deactivation_hook( __FILE__, 'deactivate_setel_express' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-setel-express.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_setel_express() {
	new Setel_Express();
}

run_setel_express();
