<?php

class Setel_Express_Admin_Api_Data {

	protected Setel_Express_Api_Data $api_data;

	public function __construct() {
		$this->api_data = Setel_Express_Api_Data::make();

		$this->define_hooks();
	}

	public function define_hooks() {
		add_action( 'wp_ajax_setel-express-api-data-supported-receiver-postcodes', [ $this, 'get_supported_receiver_postcodes' ] );
	}

	public function get_supported_receiver_postcodes() {
		check_admin_referer( 'setel-express-api-data-supported-receiver-postcodes-nonce', '_nonce' );

		try {
			$supported_receiver_postcodes = $this->api_data->supported_receiver_postcodes( wc_clean( $_REQUEST['senderPostcode'] ) );
			wp_send_json_success( $supported_receiver_postcodes );
		} catch ( Exception $exception ) {
			wp_send_json_error( [
				'message' => $exception->getMessage(),
			] );
		}

		wp_die();
	}
}