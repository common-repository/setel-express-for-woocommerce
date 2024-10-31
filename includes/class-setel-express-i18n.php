<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://logistika.com.my/
 * @since      1.0.0
 *
 * @package    Setel_Express
 * @subpackage Setel_Express/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Setel_Express
 * @subpackage Setel_Express/includes
 * @author     Setel Express <support@logistika.com.my>
 */
class Setel_Express_i18n {

	public function __construct() {
		$this->define_hooks();
	}

	protected function define_hooks() {
		add_action( 'plugins_loaded', [ $this, 'load_plugin_textdomain' ] );
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'setel-express',
			false,
			dirname( plugin_basename( __FILE__ ), 2 ) . '/languages/'
		);
	}


}
