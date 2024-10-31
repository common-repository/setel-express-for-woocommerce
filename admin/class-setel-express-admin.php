<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://logistika.com.my/
 * @since      1.0.0
 *
 * @package    Setel_Express
 * @subpackage Setel_Express/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Setel_Express
 * @subpackage Setel_Express/admin
 * @author     Setel Express <support@logistika.com.my>
 */
class Setel_Express_Admin {

    public function __construct() {
        $this->define_hooks();
    }

    protected function define_hooks() {
        add_action( 'plugins_loaded', [ $this, 'check_woocommerce_activated' ] );

        add_action( 'admin_menu', [ $this, 'add_menu' ] );

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        add_filter( 'woocommerce_shipping_methods', [ $this, 'add_shipping_method' ] );

        add_action( 'http_api_debug', [ new Setel_Express_Api(), 'log_api_response' ], 10, 5 );
        add_filter( 'http_headers_useragent', [ new Setel_Express_Api(), 'set_http_headers_useragent' ], 10, 2 );
        add_filter( 'http_request_timeout', [ new Setel_Express_Api(), 'increase_http_request_timeout' ], 10, 2 );
    }

    public function check_woocommerce_activated() {
        if ( defined( 'WC_VERSION' ) ) {
            return;
        }

        add_action( 'admin_notices', [ $this, 'notice_woocommerce_required' ] );
    }

    public function notice_woocommerce_required() {
        ?>
        <div class="notice notice-error">
            <p><?php echo esc_html__( 'Setel Express requires WooCommerce to be installed and activated!', 'setel-express' ) ?></p>
        </div>
        <?php
    }

    public function add_menu() {
        add_menu_page(
            __( 'Setel Express', 'setel-express' ),
            __( 'Setel Express', 'setel-express' ),
            'manage_woocommerce',
            'setel-express',
            null,
            null,
            58
        );
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            'setel-express',
            plugin_dir_url( __FILE__ ) . 'css/setel-express-admin-dist.css',
            [],
            SETEL_EXPRESS_VERSION
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'setel-express',
            plugin_dir_url( __FILE__ ) . 'js/setel-express-admin-dist.js',
            [ 'jquery' ],
            SETEL_EXPRESS_VERSION
        );
    }

    public function add_shipping_method(
        array $methods
    ) {
        require_once plugin_dir_path( __DIR__ ) . 'includes/woocommerce/shipping/setel-express/class-wc-shipping-setel-express.php';

        $methods[] = WC_Shipping_Setel_Express::class;

        return $methods;
    }

}
