<?php

class Setel_Express_Authenticate {

	protected Setel_Express_Api $api;

	protected Setel_Express_Settings $settings;

	public function __construct(
		Setel_Express_Api $api,
		Setel_Express_Settings $settings
	) {
		$this->api      = $api;
		$this->settings = $settings;
	}

	public function is_credential_valid(
		string $username,
		string $password
	): bool {
		return (bool) $this->login( $username, $password );
	}

	public function login(
		string $username,
		string $password
	) {
		try {
			$response = $this->api->login( $username, $password );

			return [
				'bearer_token' => $response['id_token'],
				'expired_at'   => DateTime::createFromFormat( 'U', $response['expiredIn'] ),
			];
		} catch ( Setel_Express_Api_Exception $exception ) {
			if ( $exception->getCode() === 400 ) {
				return false;
			}

			throw $exception;
		}
	}

	public function has_bearer_token(): bool {
		return (bool) $this->get_bearer_token();
	}

	public function get_bearer_token(
		bool $refresh = false
	) {
		if ( ! $this->settings->configured() ) {
			return false;
		}

		if ( ! $refresh ) {
			$bearer_token = get_transient( 'setel_express_bearer_token' );
			if ( $bearer_token ) {
				return $bearer_token;
			}
		}

		$login = $this->login( $this->settings->api_username(), $this->settings->api_password() );
		if ( ! $login ) {
			return false;
		}

		set_transient(
			'setel_express_bearer_token',
			$login['bearer_token'],
			$login['expired_at']->getTimestamp() - time()
		);

		return $login['bearer_token'];
	}

	public static function make() {
		return new self(
			Setel_Express_Api::make(),
			Setel_Express_Settings::make()
		);
	}
}