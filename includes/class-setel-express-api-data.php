<?php

class Setel_Express_Api_Data {

	protected Setel_Express_Api $api;

	protected Setel_Express_Authenticate $authenticate;

	public function __construct(
		Setel_Express_Api $api,
		Setel_Express_Authenticate $authenticate
	) {
		$this->api          = $api;
		$this->authenticate = $authenticate;
	}

	public function pickup_types(): array {
		return [
			[
				'code'         => Setel_Express_Shipment::PICKUP_TYPE_PICKUP,
				'webhook_code' => Setel_Express_Shipment::WEBHOOK_PICKUP_TYPE_PICKUP,
				'name'         => __( 'Pickup', 'setel-express' ),
				'description'  => __( 'Our driver will collect from your location.', 'setel-express' ),
			],
			[
				'code'         => Setel_Express_Shipment::PICKUP_TYPE_DROP_OFF,
				'webhook_code' => Setel_Express_Shipment::WEBHOOK_PICKUP_TYPE_DROP_OFF,
				'name'         => __( 'Drop off', 'setel-express' ),
				'description'  => __( 'Walk in and drop off at a Setel Express Hub near you.', 'setel-express' ),
			],
			[
				'code'         => Setel_Express_Shipment::PICKUP_TYPE_DECIDE_LATER,
				'webhook_code' => Setel_Express_Shipment::WEBHOOK_PICKUP_TYPE_DECIDE_LATER,
				'name'         => __( 'Decide later', 'setel-express' ),
				'description'  => __( 'Complete shipment details and choose pickup type later.', 'setel-express' ),
			],
		];
	}

	public function resolve_pickup_type_from_webhook(
		string $webhook_pickup_type
	): ?string {
		foreach ( $this->pickup_types() as $pickup_type ) {
			if ( $pickup_type['webhook_code'] === $webhook_pickup_type ) {
				return $pickup_type['code'];
			}
		}

		return null;
	}

	public function shipment_statuses(): array {
		return [
			[
				'name'           => __( 'Created', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_CREATED,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_CREATED,
				'color'          => 'tw-text-status-default tw-bg-status-default-alt',
			],
			[
				'name'           => __( 'Pending pickup', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_PENDING_PICKUP,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_PENDING_PICKUP,
				'color'          => 'tw-text-status-default tw-bg-status-default-alt',
			],
			[
				'name'           => __( 'Pending drop off', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_PENDING_DROP_OFF,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_PENDING_DROP_OFF,
				'color'          => 'tw-text-status-default tw-bg-status-default-alt',
			],
			[
				'name'           => __( 'In transit', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_IN_TRANSIT,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_IN_TRANSIT,
				'color'          => 'tw-text-status-info tw-bg-status-info-alt',
			],
			[
				'name'           => __( 'Out for delivery', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_OUT_FOR_DELIVERY,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_OUT_FOR_DELIVERY,
				'color'          => 'tw-text-status-info tw-bg-status-info-alt',
			],
			[
				'name'           => __( 'Ready for collection', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_READY_FOR_COLLECTION,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_READY_FOR_COLLECTION,
				'color'          => 'tw-text-status-info tw-bg-status-info-alt',
			],
			[
				'name'           => __( 'Completed', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_COMPLETED,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_COMPLETED,
				'color'          => 'tw-text-status-success tw-bg-status-success-alt',
			],
			[
				'name'           => __( 'Pending return', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_PENDING_RETURN,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_PENDING_RETURN,
				'color'          => 'tw-text-status-default tw-bg-status-default-alt',
			],
			[
				'name'           => __( 'Returned', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_RETURNED,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_RETURNED,
				'color'          => 'tw-text-status-default tw-bg-status-default-alt',
			],
			[
				'name'           => __( 'Delivery failed', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_DELIVERY_FAILED,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_DELIVERY_FAILED,
				'color'          => 'tw-text-status-warning tw-bg-status-warning-alt',
			],
			[
				'name'           => __( 'Cancelled', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_CANCELLED,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_CANCELLED,
				'color'          => 'tw-text-status-warning tw-bg-status-warning-alt',
			],
			[
				'name'           => __( 'On hold', 'setel-express' ),
				'status'         => Setel_Express_Shipment::STATUS_ON_HOLD,
				'webhook_status' => Setel_Express_Shipment::WEBHOOK_STATUS_ON_HOLD,
				'color'          => 'tw-text-status-warning tw-bg-status-warning-alt',
			],
		];
	}

	public function resolve_shipment_status_from_webhook(
		string $webhook_shipment_status
	): ?string {
		foreach ( $this->shipment_statuses() as $shipment_status ) {
			if ( $shipment_status['webhook_status'] === $webhook_shipment_status ) {
				return $shipment_status['status'];
			}
		}

		return null;
	}

	public function phone_countries(): array {
		return [
			[ 'country' => 'Malaysia', 'code' => '60', 'prefixed_code' => '+60' ],
			[ 'country' => 'Thailand', 'code' => '65', 'prefixed_code' => '+65' ],
			[ 'country' => 'Singapore', 'code' => '66', 'prefixed_code' => '+66' ],
		];
	}

	public function states() {
		$states = get_transient( 'setel_express_states' );
		if ( $states ) {
			return $states;
		}

		$states = $this->api->get_states( $this->authenticate->get_bearer_token() );

		set_transient( 'setel_express_states', $states, WEEK_IN_SECONDS );

		return $states;
	}

	public function supported_sender_postcodes(): array {
		$supported_sender_postcodes = get_transient( 'setel_express_sender_postcodes' );
		if ( $supported_sender_postcodes ) {
			return $supported_sender_postcodes;
		}

		$supported_sender_postcodes = $this->api->get_supported_sender_postcodes( $this->authenticate->get_bearer_token() );

		set_transient( 'setel_express_sender_postcodes', $supported_sender_postcodes, WEEK_IN_SECONDS );

		return $supported_sender_postcodes;
	}

	public function supported_receiver_postcodes(
		string $sender_postcode
	): array {
		$supported_receiver_postcodes = get_transient( "setel_express_receiver_postcodes_{$sender_postcode}" );
		if ( $supported_receiver_postcodes ) {
			return $supported_receiver_postcodes;
		}

		$supported_receiver_postcodes = $this->api->get_supported_receiver_postcodes(
			$sender_postcode,
			$this->authenticate->get_bearer_token()
		);

		set_transient( "setel_express_receiver_postcodes_{$sender_postcode}", $supported_receiver_postcodes, DAY_IN_SECONDS );

		return $supported_receiver_postcodes;
	}

	public function parcel_sizes() {
		$parcel_sizes = get_transient( 'setel_express_parcel_sizes' );
		if ( $parcel_sizes ) {
			return $parcel_sizes;
		}

		$parcel_sizes         = $this->api->get_parcel_sizes( $this->authenticate->get_bearer_token() );
		$visible_parcel_sizes = array_values( array_filter( $parcel_sizes, function ( array $parcel_size ) {
			return $parcel_size['visible'] === true;
		} ) );

		set_transient( 'setel_express_parcel_sizes', $visible_parcel_sizes, WEEK_IN_SECONDS );

		return $visible_parcel_sizes;
	}

	public static function make() {
		return new self(
			Setel_Express_Api::make(),
			Setel_Express_Authenticate::make()
		);
	}
}