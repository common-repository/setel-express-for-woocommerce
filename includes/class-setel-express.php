<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://logistika.com.my/
 * @since      1.0.0
 *
 * @package    Setel_Express
 * @subpackage Setel_Express/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Setel_Express
 * @subpackage Setel_Express/includes
 * @author     Setel Express <support@logistika.com.my>
 */
class Setel_Express {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	protected function load_dependencies() {
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-setel-express-i18n.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-setel-express-api.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-setel-express-api-exception.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-setel-express-api-data.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-setel-express-shipment.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-setel-express-settings.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-setel-express-authenticate.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/helpers.php';

		require_once plugin_dir_path( __DIR__ ) . 'admin/class-setel-express-admin.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-setel-express-admin-api-data.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-setel-express-admin-shipment.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-setel-express-admin-shipping-label.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-setel-express-admin-wc-order.php';
	}

	protected function set_locale() {
		new Setel_Express_i18n();
	}

	protected function define_admin_hooks() {
		new Setel_Express_Admin();
		new Setel_Express_Admin_Api_Data();
		new Setel_Express_Admin_Shipment();
		new Setel_Express_Admin_Shipping_Label();
		new Setel_Express_Admin_WC_Order();
	}

}
