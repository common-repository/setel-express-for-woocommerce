<?php

class Setel_Express_Api_Exception extends Exception {

	public array $response;

	public function __construct(
		array $response
	) {
		parent::__construct(
			wp_remote_retrieve_response_message( $response ),
			wp_remote_retrieve_response_code( $response )
		);

		$this->response = $response;
	}
}