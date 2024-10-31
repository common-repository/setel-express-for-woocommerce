<?php

class Setel_Express_Settings {
	protected array $options;

	public function __construct() {
		$this->options = array_merge( [
			'api_username'                => '',
			'api_password'                => '',
			'sender_address_name'         => '',
			'sender_address_phone_number' => '',
			'sender_address_address1'     => '',
			'sender_address_address2'     => '',
			'sender_address_postcode'     => '',
			'sender_address_city'         => '',
			'sender_address_state'        => '',
		], get_option( 'woocommerce_setel-express_settings', [] ) );
	}

	public function api_username() {
		return $this->options['api_username'];
	}

	public function api_password() {
		return $this->options['api_password'];
	}

	public function sender_address_name() {
		return $this->options['sender_address_name'];
	}

	public function sender_address_phone_number() {
		return $this->options['sender_address_phone_number'];
	}

	public function sender_address_address1() {
		return $this->options['sender_address_address1'];
	}

	public function sender_address_address2() {
		return $this->options['sender_address_address2'];
	}

	public function sender_address_postcode() {
		return $this->options['sender_address_postcode'];
	}

	public function sender_address_city() {
		return $this->options['sender_address_city'];
	}

	public function sender_address_state() {
		return $this->options['sender_address_state'];
	}

	public function configured(): bool {
		return $this->api_username() && $this->api_password();
	}

	public static function make() {
		return new self();
	}
}