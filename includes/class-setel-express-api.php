<?php

class Setel_Express_Api {
	protected const ENDPOINT_AUTHENTICATE_LOGIN = 'authenticate/login';
	protected const ENDPOINT_HUBS_GET_POSTCODES = 'hubs/postal-code';
	protected const ENDPOINT_HUBS_GET_SUPPORTED_SENDER_POSTCODES = 'hubs/postal-code/support-sender';
	protected const ENDPOINT_HUBS_GET_SUPPORTED_RECEIVER_POSTCODES = 'hubs/postal-code/support-receiver/:sender-postcode';
	protected const ENDPOINT_HUBS_GET_STATES = 'hubs/postal-code/states';
	protected const ENDPOINT_MERCHANT_DETAILS_BY_USER_ID = 'merchant/info/merchant-detail';
	protected const ENDPOINT_MERCHANT_LIST_PARCEL_SIZE = 'merchant/parcel-size';
	protected const ENDPOINT_CONSIGNMENT_SHIPMENT_LIST = 'consignment/shipments';
	protected const ENDPOINT_CONSIGNMENT_CREATE_SHIPMENT = 'consignment/shipments';
	protected const ENDPOINT_CONSIGNMENT_SHIPMENT_BY_ID = 'consignment/shipments/:id';
	protected const ENDPOINT_CONSIGNMENT_UPDATE_SHIPMENT = 'consignment/shipments/:ids';
	protected const ENDPOINT_CONSIGNMENT_CANCEL_SHIPMENT = 'consignment/shipments/cancel/:ids';
	protected const ENDPOINT_CONSIGNMENT_SHIPMENTS_PRINT = 'consignment/shipments/print/:ids';

	protected function headers(
		string $token = null
	): array {
		$headers                 = [];
		$headers['Content-Type'] = 'application/json';

		if ( $token ) {
			$headers['Authorization'] = "Bearer {$token}";
		}

		return $headers;
	}

	protected function endpoint(
		string $url,
		array $replace = []
	): string {
		if ( $replace ) {
			$url = strtr( $url, $replace );
		}

		return SETEL_EXPRESS_API_BASE_URL . $url;
	}

	public function login(
		string $username,
		string $password
	): array {
		$payload = [
			'username' => $username,
			'password' => $password,
		];

		return $this->handle_response( wp_remote_post(
			$this->endpoint( self::ENDPOINT_AUTHENTICATE_LOGIN ),
			[
				'headers' => $this->headers(),
				'body'    => json_encode( $payload ),
			]
		) );
	}

	public function get_states(
		string $bearer_token
	): array {
		return $this->handle_response( wp_remote_get(
			$this->endpoint( self::ENDPOINT_HUBS_GET_STATES ),
			[
				'headers' => $this->headers( $bearer_token ),
			]
		) );
	}

	public function get_supported_sender_postcodes(
		string $bearer_token
	): array {
		return $this->handle_response( wp_remote_get(
			$this->endpoint( self::ENDPOINT_HUBS_GET_SUPPORTED_SENDER_POSTCODES ),
			[
				'headers' => $this->headers( $bearer_token ),
			]
		) );
	}

	public function get_supported_receiver_postcodes(
		string $sender_postcode,
		string $bearer_token
	): array {
		return $this->handle_response( wp_remote_get(
			$this->endpoint( self::ENDPOINT_HUBS_GET_SUPPORTED_RECEIVER_POSTCODES, [ ':sender-postcode' => $sender_postcode ] ),
			[
				'headers' => $this->headers( $bearer_token ),
			]
		) );
	}

	public function get_parcel_sizes(
		string $bearer_token
	): array {
		return $this->handle_response( wp_remote_get(
			$this->endpoint( self::ENDPOINT_MERCHANT_LIST_PARCEL_SIZE ),
			[
				'headers' => $this->headers( $bearer_token ),
			]
		) );
	}

	public function get_merchant_details(
		string $bearer_token
	): array {
		return $this->handle_response( wp_remote_get(
			$this->endpoint( self::ENDPOINT_MERCHANT_DETAILS_BY_USER_ID ),
			[
				'headers' => $this->headers( $bearer_token ),
			]
		) );
	}

	public function create_shipment(
		array $shipments,
		string $bearer_token
	): array {
		$payload = [];
		foreach ( $shipments as $shipment ) {
			$payload['data'][] = array_intersect_key( $shipment, array_flip( [
				'initialPickupType',
				'merchant',
				//'merchantPhoneNumber',
				'senderPhoneNumber',
				'senderAddress1',
				'senderAddress2',
				'senderPostalCode',
				'senderCity',
				'senderState',
				//'senderCountry',
				'merchantReferenceId',
				'noOfParcels',
				'parcelWeight',
				'parcelSize',
				'receiverName',
				'receiverPhoneNumber',
				//'receiverEmail',
				//'receiverAddress',
				'receiverAddress1',
				'receiverAddress2',
				'receiverPostalCode',
				'receiverCity',
				'receiverState',
				//'isReceiverOfficeAddress',
				'instructions',
				//'workflowStatus',
				'webhookURL',
			] ) );
		}

		return $this->handle_response( wp_remote_post(
			$this->endpoint( self::ENDPOINT_CONSIGNMENT_CREATE_SHIPMENT ),
			[
				'headers' => $this->headers( $bearer_token ),
				'body'    => json_encode( $payload ),
			]
		) )['shipments'];
	}

	public function update_shipment(
		string $id,
		array $shipment,
		string $bearer_token
	) {
		$shipments = $this->update_shipments( [ $id ], $shipment, $bearer_token );

		return reset( $shipments );
	}

	public function update_shipments(
		array $ids,
		array $shipment,
		string $bearer_token
	) {
		$payload = array_intersect_key( $shipment, array_flip( [
			'initialPickupType',
			'merchant',
			//'merchantPhoneNumber',
			'senderPhoneNumber',
			'senderAddress1',
			'senderAddress2',
			'senderPostalCode',
			'senderCity',
			'senderState',
			//'senderCountry',
			'merchantReferenceId',
			'noOfParcels',
			'parcelWeight',
			'parcelSize',
			'receiverName',
			'receiverPhoneNumber',
			//'receiverEmail',
			//'receiverAddress',
			'receiverAddress1',
			'receiverAddress2',
			'receiverPostalCode',
			'receiverCity',
			'receiverState',
			//'isReceiverOfficeAddress',
			'instructions',
			//'workflowStatus',
			//'webhookURL',
			'isUI',
		] ) );

		return $this->handle_response( wp_remote_post(
			$this->endpoint( self::ENDPOINT_CONSIGNMENT_UPDATE_SHIPMENT, [ ':ids' => implode( ',', $ids ) ] ),
			[
				'method'  => 'PUT',
				'headers' => $this->headers( $bearer_token ),
				'body'    => json_encode( $payload ),
			]
		) );
	}

	public function cancel_shipment(
		array $ids,
		string $bearer_token
	) {
		return $this->handle_response( wp_remote_request(
			$this->endpoint( self::ENDPOINT_CONSIGNMENT_CANCEL_SHIPMENT, [ ':ids' => implode( ',', $ids ) ] ),
			[
				'method'  => 'PUT',
				'headers' => $this->headers( $bearer_token ),
			]
		) );
	}

	public function print_shipping_labels(
		array $ids,
		string $bearer_token
	) {
		return $this->handle_response( wp_remote_get(
			$this->endpoint( self::ENDPOINT_CONSIGNMENT_SHIPMENTS_PRINT, [ ':ids' => implode( ',', $ids ) ] ),
			[
				'headers' => $this->headers( $bearer_token ),
			]
		) );
	}

	protected function handle_response(
		$response
	) {
		$this->throw_response_if_error( $response );

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $response_body['data'] ?? $response_body;
	}

	protected function throw_response_if_error( $response ) {
		if ( is_wp_error( $response ) ) {
			/**
			 * @var \WP_Error $response
			 */
			throw new Exception( $response->get_error_message(), $response->get_error_code() );
		}

		if ( $this->is_response_failed( $response ) ) {
			throw new Setel_Express_Api_Exception( $response );
		}
	}

	protected function is_response_failed(
		$response
	): bool {
		$response_code = (int) wp_remote_retrieve_response_code( $response );

		return $response_code >= 400 && $response_code <= 599;
	}

	public function log_api_response( $response, string $context, string $class, array $parsed_args, string $url ) {
		if ( ! $this->is_setel_express_api_request( $url ) ) {
			return;
		}

		if ( is_wp_error( $response ) ) {
			var_dump( __METHOD__, $response );
			exit;
		}

		setel_express_api_log( print_r( [
			'request'  => [
				'url'     => $url,
				'headers' => $parsed_args['headers'],
				'data'    => $parsed_args['body'],
				'type'    => $parsed_args['method'],
			],
			'response' => [
				'status_code' => $response['response']['code'],
				'body'        => $response['body'],
			],
		], true ) );
	}

	public function increase_http_request_timeout( $timeout, $url ) {
		return $this->is_setel_express_api_request( $url ) ? 30 : $timeout;
	}

	public function set_http_headers_useragent(
		string $user_agent,
		string $url
	) {
		return $this->is_setel_express_api_request( $url ) ? SETEL_EXPRESS_API_USER_AGENT : $user_agent;
	}

	public static function make() {
		return new self();
	}

	protected function is_setel_express_api_request( $url ) {
		return strpos( $url, SETEL_EXPRESS_API_BASE_URL ) === 0;
	}
}