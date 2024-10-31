<?php

class WC_Shipping_Setel_Express extends WC_Shipping_Method {

	protected Setel_Express_Api_Data $api_data;

	protected Setel_Express_Authenticate $authenticate;

	public function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );

		$this->id                 = 'setel-express';
		$this->method_title       = __( 'Setel Express', 'setel-express' );
		$this->method_description = __(
			'To start creating Setel Express shipments, please fill in your user credentials as shown in your Setel Express user portal.',
			'setel-express'
		);

		$this->api          = Setel_Express_Api::make();
		$this->api_data     = Setel_Express_Api_Data::make();
		$this->authenticate = Setel_Express_Authenticate::make();

		$this->init();
	}

	/**
	 * Initialize gdex shipping.
	 */
	public function init() {
		$this->init_settings();
		$this->init_form_fields();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'display_errors' ] );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'init_settings' ] );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'init_form_fields' ] );
	}

	public function init_form_fields() {
		$this->form_fields = [];
		$this->form_fields += $this->init_api_form_fields();

		if ( $this->authenticate->has_bearer_token() ) {
			$this->form_fields += $this->init_sender_address_form_fields();
		}
	}

	public function init_api_form_fields() {
		$fields['api'] = [
			'title'       => __( 'Merchant Login', 'setel-express' ),
			'type'        => 'title',
			'description' => __( 'Configure your access towards Setel Express APIs by means of authentication.', 'setel-express' ),
		];

		$fields['api_username'] = [
			'title'             => __( 'Username', 'setel-express' ),
			'type'              => 'text',
			'custom_attributes' => [ 'required' => 'required' ],
		];

		$fields['api_password'] = [
			'title'             => __( 'Password', 'setel-express' ),
			'type'              => 'password',
			'custom_attributes' => [ 'required' => 'required' ],
		];

		if ( ! $this->authenticate->has_bearer_token() ) {
			$register_description = __( '
				Please register 
				<a href="https://www.setel.com/business/setel-express/sign-up" target="_blank">here</a> 
				if you would to use our services and our team will be in touch with you.
			', 'setel-express' );

			$fields['api_password']['description'] = $register_description;
		}

		return $fields;
	}

	public function init_sender_address_form_fields() {
		$state_options = [];
		foreach ( $this->api_data->states() as $state ) {
			$state_options[ $state ] = $state;
		};

		$fields['sender_address'] = [
			'title' => __( 'Pickup address', 'setel-express' ),
			'type'  => 'title',
		];

		$fields['sender_address_name'] = [
			'title'             => __( 'Name', 'setel-express' ),
			'type'              => 'text',
			'custom_attributes' => [ 'required' => 'required' ],
		];

		$fields['sender_address_phone_number'] = [
			'title'             => __( 'Phone number', 'setel-express' ),
			'type'              => 'tel',
			'custom_attributes' => [ 'required' => 'required' ],
			'description'       => 'Phone number in international format: +60XXXXXXX.',
		];

		$fields['sender_address_address1'] = [
			'title'             => __( 'Address 1', 'setel-express' ),
			'type'              => 'text',
			'custom_attributes' => [ 'required' => 'required' ],
		];

		$fields['sender_address_address2'] = [
			'title' => __( 'Address2', 'setel-express' ),
			'type'  => 'text',
			//            'custom_attributes' => [ 'required' => 'required' ],
		];

		$fields['sender_address_postcode'] = [
			'title'             => __( 'Postcode', 'setel-express' ),
			'type'              => 'text',
			'custom_attributes' => [ 'required' => 'required', 'maxlength' => 5 ],
		];

		$fields['sender_address_city'] = [
			'title'             => __( 'City', 'setel-express' ),
			'type'              => 'text',
			'custom_attributes' => [ 'required' => 'required' ],
		];

		$fields['sender_address_state'] = [
			'title'             => __( 'State', 'setel-express' ),
			'type'              => 'select',
			'options'           => $state_options,
			'custom_attributes' => [ 'required' => 'required' ],
		];

		return $fields;
	}

	public function validate_api_username_field( $key, $username ) {
		$password = wc_clean( $_POST['woocommerce_setel-express_api_password'] );
		if ( ! $password ) {
			return $username;
		}

		$is_credential_valid = $this->authenticate->is_credential_valid( $username, $password );
		if ( ! $is_credential_valid ) {
			throw new Exception( __( 'Invalid Setel Express merchant username or password.', 'setel-express' ) );
		}

		return $username;
	}

	public function validate_sender_address_phone_number_field( $key, $phone_number ) {
		$country_codes = array_column( $this->api_data->phone_countries(), 'prefixed_code' );
		foreach ( $country_codes as $country_code ) {
			if ( str_starts_with( $phone_number, $country_code ) ) {
				return $phone_number;
			}
		}

		$joined_country_codes = implode( ', ', $country_codes );

		throw new Exception( __( "The sender phone number must be start with one of following: {$joined_country_codes}", 'setel-express' ) );
	}

	public function validate_sender_address_postcode_field( $key, $postcode ) {
		$is_in = in_array( $postcode, $this->api_data->supported_sender_postcodes() );
		if ( ! $is_in ) {
			throw new Exception( __( 'The selected sender postcode is invalid.', 'setel-express' ) );
		}

		return $postcode;
	}

	public function process_admin_options() {
		$saved = parent::process_admin_options();

		if ( ! $this->instance_id ) {
			$this->authenticate->get_bearer_token( true );
		}

		return $saved;
	}
}