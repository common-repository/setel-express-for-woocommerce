<?php

class Setel_Express_Shipment {
	public const PICKUP_TYPE_DROP_OFF = 'Drop Off';
	public const PICKUP_TYPE_PICKUP = 'Pickup';
	public const PICKUP_TYPE_DECIDE_LATER = 'Decide later';

	public const WEBHOOK_PICKUP_TYPE_DROP_OFF = 'dropOff';
	public const WEBHOOK_PICKUP_TYPE_PICKUP = 'pickup';
	public const WEBHOOK_PICKUP_TYPE_DECIDE_LATER = 'decideLater';

	public const PARCEL_SIZE_S = 'S';
	public const PARCEL_SIZE_M = 'M';
	public const PARCEL_SIZE_L = 'L';
	public const PARCEL_SIZE_XL = 'XL';

	public const STATUS_CREATED = 'Created';
	public const STATUS_PENDING_PICKUP = 'Pending Pickup';
	public const STATUS_PENDING_DROP_OFF = 'Pending drop Off';
	public const STATUS_IN_TRANSIT = 'In Transit';
	public const STATUS_OUT_FOR_DELIVERY = 'Out for delivery';
	public const STATUS_READY_FOR_COLLECTION = 'Ready for collection';
	public const STATUS_COMPLETED = 'Completed';
	public const STATUS_PENDING_RETURN = 'Pending Return';
	public const STATUS_RETURNED = 'Returned';
	public const STATUS_DELIVERY_FAILED = 'Delivery failed';
	public const STATUS_CANCELLED = 'Cancelled';
	public const STATUS_ON_HOLD = 'On hold';

	public const WEBHOOK_STATUS_CREATED = 'created';
	public const WEBHOOK_STATUS_PENDING_PICKUP = 'pendingPickup';
	public const WEBHOOK_STATUS_PENDING_DROP_OFF = 'pendingDropOff';
	public const WEBHOOK_STATUS_IN_TRANSIT = 'inTransit';
	public const WEBHOOK_STATUS_OUT_FOR_DELIVERY = 'outForDelivery';
	public const WEBHOOK_STATUS_READY_FOR_COLLECTION = 'readyForCollection';
	public const WEBHOOK_STATUS_COMPLETED = 'completed';
	public const WEBHOOK_STATUS_PENDING_RETURN = 'pendingReturn';
	public const WEBHOOK_STATUS_RETURNED = 'returned';
	public const WEBHOOK_STATUS_DELIVERY_FAILED = 'deliveryFailed';
	public const WEBHOOK_STATUS_CANCELLED = 'cancelled';
	public const WEBHOOK_STATUS_ON_HOLD = 'onHold';

	protected Setel_Express_Api $api;

	protected Setel_Express_Api_Data $api_data;

	protected Setel_Express_Settings $settings;

	protected Setel_Express_Authenticate $authenticate;

	public function __construct(
		Setel_Express_Api $api,
		Setel_Express_Api_Data $api_data,
		Setel_Express_Settings $settings,
		Setel_Express_Authenticate $authenticate
	) {
		$this->api          = $api;
		$this->api_data     = $api_data;
		$this->settings     = $settings;
		$this->authenticate = $authenticate;
	}

	public function create(
		array $inputs
	): array {
		return setel_express_db_transaction( function () use ( $inputs ) {
			$api_shipments = $this->api->create_shipment(
				$this->prepare_create_payload( $inputs ),
				$this->authenticate->get_bearer_token()
			);

			return array_map( function ( array $api_shipment ) use ( $inputs ) {
				$shipment_post_id = wp_insert_post( [
					'post_title'     => "Shipment #{$api_shipment['shipmentId']}",
					'post_type'      => 'setel_shipment',
					'post_status'    => 'publish',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'meta_input'     => [
						'id'                    => $api_shipment['id'],
						'order_id'              => $api_shipment['merchantReferenceId'],
						'tracking_number'       => $api_shipment['shipmentId'],
						'pickup_type'           => $api_shipment['initialPickupType'],
						'sender_name'           => $api_shipment['merchant'],
						'sender_phone_number'   => $api_shipment['senderPhoneNumber'],
						'sender_address1'       => $api_shipment['senderAddress1'],
						'sender_address2'       => $api_shipment['senderAddress2'] ?: null,
						'sender_postcode'       => $api_shipment['senderPostalCode'],
						'sender_city'           => $api_shipment['senderCity'],
						'sender_state'          => $api_shipment['senderState'],
						'receiver_name'         => $api_shipment['receiverName'],
						'receiver_phone_number' => $api_shipment['receiverPhoneNumber'],
						'receiver_address1'     => $api_shipment['receiverAddress1'],
						'receiver_address2'     => $api_shipment['receiverAddress2'] ?: null,
						'receiver_postcode'     => $api_shipment['receiverPostalCode'],
						'receiver_city'         => $api_shipment['receiverCity'],
						'receiver_state'        => $api_shipment['receiverState'],
						'no_of_parcels'         => (int) $api_shipment['noOfParcels'],
						'parcel_weight'         => (float) $api_shipment['parcelWeight'],
						'parcel_size'           => $api_shipment['parcelSize'] ?: null,
						'instructions'          => $api_shipment['instructions'] ?: null,
						'status'                => $api_shipment['workflowStatusForUser'],
					],
				] );

				return $this->get( $shipment_post_id );
			}, $api_shipments );
		} );
	}

	protected function prepare_create_payload(
		array $inputs
	): array {
		return array_map( function ( $input ) {
			return [
				'initialPickupType'   => $input['initial_pickup_type'],
				'merchant'            => $input['sender_name'],
				'senderPhoneNumber'   => $input['sender_phone_number'],
				'senderAddress1'      => $input['sender_address1'],
				'senderAddress2'      => $input['sender_address2'] ?: '',
				'senderPostalCode'    => $input['sender_postcode'],
				'senderCity'          => $input['sender_city'],
				'senderState'         => $input['sender_state'],
				'merchantReferenceId' => (string) $input['order_id'],
				'receiverName'        => $input['receiver_name'],
				'receiverPhoneNumber' => $input['receiver_phone_number'],
				'receiverAddress1'    => $input['receiver_address1'],
				'receiverAddress2'    => $input['receiver_address2'] ?: '',
				'receiverPostalCode'  => $input['receiver_postcode'],
				'receiverCity'        => $input['receiver_city'],
				'receiverState'       => $input['receiver_state'],
				'noOfParcels'         => $input['no_of_parcels'],
				'parcelWeight'        => $input['parcel_weight'],
				'parcelSize'          => $input['parcel_size'],
				'instructions'        => $input['instructions'] ?: '',
				'webhookURL'          => site_url( 'wc-api/setel_express_update_shipment' ),
			];
		}, $inputs );
	}

	public function get(
		$shipment_id
	): WP_Post {
		$shipment = get_post( $shipment_id );
		if ( ! $shipment || $shipment->post_type !== 'setel_shipment' ) {
			throw new InvalidArgumentException( __( 'Invalid shipment post', 'setel-express' ) );
		}

		return $shipment;
	}

	public function get_all(
		array $shipment_ids
	): array {
		return array_map( [ $this, 'get' ], $shipment_ids );
	}

	public function get_by_order(
		$order_id
	) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$shipment_posts = get_posts( [
			'post_type'      => 'setel_shipment',
			'posts_per_page' => - 1,
			'meta_query'     => [
				[
					'key'   => 'order_id',
					'value' => $order->get_id(),
				],
			],
		] );

		return reset( $shipment_posts );
	}

	public function get_by_api_id(
		$api_id
	) {
		$shipment_posts = get_posts( [
			'post_type'      => 'setel_shipment',
			'posts_per_page' => - 1,
			'meta_query'     => [
				[
					'key'   => 'id',
					'value' => $api_id,
				],
			],
		] );

		return reset( $shipment_posts );
	}

	public function get_api_id(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'id', true );
	}

	public function get_tracking_number(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'tracking_number', true );
	}

	public function get_order_id(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'order_id', true );
	}

	public function get_order(
		$shipment_id
	) {
		return wc_get_order( $this->get_order_id( $shipment_id ) );
	}

	public function get_status(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'status', true );
	}

	public function get_status_name(
		$shipment_id
	): string {
		$status_code = $this->get_status( $shipment_id );

		foreach ( $this->api_data->shipment_statuses() as $shipment_status ) {
			if ( $shipment_status['status'] === $status_code ) {
				return $shipment_status['name'];
			}
		}

		throw new OutOfRangeException( "Undefined shipment status: {$status_code}" );
	}

	public function get_status_badge(
		$shipment_id
	): string {
		$status_code = $this->get_status( $shipment_id );

		foreach ( $this->api_data->shipment_statuses() as $shipment_status ) {
			if ( $shipment_status['status'] === $status_code ) {
				return '
					<span class="
						tw-inline-flxe tw-items-center
						tw-px-2 tw-py-1
						tw-text-xs tw-leading-4 tw-tracking-px tw-font-bold tw-uppercase
						' . esc_attr( $shipment_status['color'] ) . '
						tw-rounded
					">'
				       . esc_html( $shipment_status['name'] ) .
				       '</span>';
			}
		}

		throw new OutOfRangeException( "Undefined shipment status: {$status_code}" );
	}

	public function get_pickup_type(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'pickup_type', true );
	}

	public function get_sender_name(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'sender_name', true );
	}

	public function get_sender_phone_number(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'sender_phone_number', true );
	}

	public function get_sender_address1(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'sender_address1', true );
	}

	public function get_sender_address2(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'sender_address2', true );
	}

	public function get_sender_postcode(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'sender_postcode', true );
	}

	public function get_sender_city(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'sender_city', true );
	}

	public function get_sender_state(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'sender_state', true );
	}

	public function get_receiver_name(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'receiver_name', true );
	}

	public function get_receiver_phone_number(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'receiver_phone_number', true );
	}

	public function get_receiver_address1(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'receiver_address1', true );
	}

	public function get_receiver_address2(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'receiver_address2', true );
	}

	public function get_receiver_postcode(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'receiver_postcode', true );
	}

	public function get_receiver_city(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'receiver_city', true );
	}

	public function get_receiver_state(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'receiver_state', true );
	}

	public function get_no_of_parcels(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'no_of_parcels', true );
	}

	public function get_parcel_weight(
		$shipment_id
	): float {
		return (float) get_post_meta( $this->get( $shipment_id )->ID, 'parcel_weight', true );
	}

	public function get_parcel_size(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'parcel_size', true );
	}

	public function get_instructions(
		$shipment_id
	): string {
		return get_post_meta( $this->get( $shipment_id )->ID, 'instructions', true );
	}

	public function get_view_url(
		$shipment_id
	): string {
		$shipment_post = $this->get( $shipment_id );

		return add_query_arg( [
			'shipment' => $shipment_post->ID,
		], admin_url( 'admin.php?page=setel-express-shipments-details' ) );
	}

	public function is_editable(
		$shipment_id
	): bool {
		return in_array( $this->get_status( $shipment_id ), [
			self::STATUS_CREATED,
			self::STATUS_PENDING_PICKUP,
			self::STATUS_PENDING_DROP_OFF,
		] );
	}

	public function is_cancellable(
		$shipment_id
	): bool {
		return $this->is_editable( $shipment_id );
	}

	public function is_printable(
		$shipment_id
	): bool {
		return ! $this->is_cancelled( $shipment_id );
	}

	public function is_cancelled(
		$shipment_id
	): bool {
		return $this->get_status( $shipment_id ) === self::STATUS_CANCELLED;
	}

	public function update(
		$shipment_id,
		array $input
	): WP_Post {
		$shipment_post = $this->update_all( [ $shipment_id ], $input );

		return reset( $shipment_post );
	}

	public function update_all(
		array $shipment_ids,
		array $input
	): array {
		$shipment_posts = $this->get_all( $shipment_ids );

		$editable_shipment_posts = array_filter( $shipment_posts, [ $this, 'is_editable' ] );
		if ( empty( $editable_shipment_posts ) ) {
			return [];
		}

		return setel_express_db_transaction( function () use ( $editable_shipment_posts, $input ) {
			$api_shipments = $this->api->update_shipments(
				array_map( [ $this, 'get_api_id' ], $editable_shipment_posts ),
				$this->prepare_update_payload( $input ),
				$this->authenticate->get_bearer_token()
			);

			return array_map( function ( $api_shipment ) {
				$shipment_post_id = wp_update_post( [
					'ID'         => $this->get_by_api_id( $api_shipment['id'] )->ID,
					'meta_input' => [
						'id'                    => $api_shipment['id'],
						'order_id'              => $api_shipment['merchantReferenceId'],
						'tracking_number'       => $api_shipment['shipmentId'],
						'pickup_type'           => $api_shipment['initialPickupType'],
						'sender_name'           => $api_shipment['merchant'],
						'sender_phone_number'   => $api_shipment['senderPhoneNumber'],
						'sender_address1'       => $api_shipment['senderAddress1'],
						'sender_address2'       => $api_shipment['senderAddress2'] ?: null,
						'sender_postcode'       => $api_shipment['senderPostalCode'],
						'sender_city'           => $api_shipment['senderCity'],
						'sender_state'          => $api_shipment['senderState'],
						'receiver_name'         => $api_shipment['receiverName'],
						'receiver_phone_number' => $api_shipment['receiverPhoneNumber'],
						'receiver_address1'     => $api_shipment['receiverAddress1'],
						'receiver_address2'     => $api_shipment['receiverAddress2'] ?: null,
						'receiver_postcode'     => $api_shipment['receiverPostalCode'],
						'receiver_city'         => $api_shipment['receiverCity'],
						'receiver_state'        => $api_shipment['receiverState'],
						'no_of_parcels'         => (int) $api_shipment['noOfParcels'],
						'parcel_weight'         => (float) $api_shipment['parcelWeight'],
						'parcel_size'           => $api_shipment['parcelSize'] ?: null,
						'instructions'          => $api_shipment['instructions'] ?: null,
						'status'                => $api_shipment['workflowStatusForUser'],
					],
				] );

				return $this->get( $shipment_post_id );
			}, $api_shipments );
		} );
	}

	protected function prepare_update_payload(
		array $input
	): array {
		$placeholder = new stdClass;

		return array_filter( [
			'initialPickupType'   => $input['initial_pickup_type'] ?? $placeholder,
			'merchant'            => $input['sender_name'] ?? $placeholder,
			'senderPhoneNumber'   => $input['sender_phone_number'] ?? $placeholder,
			'senderAddress1'      => $input['sender_address1'] ?? $placeholder,
			'senderAddress2'      => $input['sender_address2'] ?? $placeholder,
			'senderPostalCode'    => $input['sender_postcode'] ?? $placeholder,
			'senderCity'          => $input['sender_city'] ?? $placeholder,
			'senderState'         => $input['sender_state'] ?? $placeholder,
			'receiverName'        => $input['receiver_name'] ?? $placeholder,
			'receiverPhoneNumber' => $input['receiver_phone_number'] ?? $placeholder,
			'receiverAddress1'    => $input['receiver_address1'] ?? $placeholder,
			'receiverAddress2'    => $input['receiver_address2'] ?? $placeholder,
			'receiverPostalCode'  => $input['receiver_postcode'] ?? $placeholder,
			'receiverCity'        => $input['receiver_city'] ?? $placeholder,
			'receiverState'       => $input['receiver_state'] ?? $placeholder,
			'noOfParcels'         => $input['no_of_parcels'] ?? $placeholder,
			'parcelWeight'        => $input['parcel_weight'] ?? $placeholder,
			'parcelSize'          => $input['parcel_size'] ?? $placeholder,
			'instructions'        => $input['instructions'] ?? $placeholder,
			'isUI'                => true, // sync to TigerSheet
		], function ( $value ) use ( $placeholder ) {
			return $value !== $placeholder;
		} );
	}

	public function print(
		array $shipment_ids
	) {
		$shipment_api_ids = array_map( [ $this, 'get_api_id' ], $shipment_ids );

		$shippingLabels = $this->api->print_shipping_labels(
			$shipment_api_ids,
			$this->authenticate->get_bearer_token()
		);

		return base64_decode( $shippingLabels );
	}

	public function cancel(
		array $shipment_ids
	) {
		$shipment_posts = $this->get_all( $shipment_ids );

		$cancellable_shipment_posts = array_filter( $shipment_posts, [ $this, 'is_cancellable' ] );

		if ( empty( $cancellable_shipment_posts ) ) {
			return [];
		}

		return setel_express_db_transaction( function () use ( $cancellable_shipment_posts ) {
			foreach ( $cancellable_shipment_posts as $shipment_post ) {
				update_post_meta( $shipment_post->ID, 'status', self::STATUS_CANCELLED );
			}

			$shipment_api_ids = array_map( [ $this, 'get_api_id' ], $cancellable_shipment_posts );

			$this->api->cancel_shipment( $shipment_api_ids, $this->authenticate->get_bearer_token() );

			return $cancellable_shipment_posts;
		} );
	}

	public function sync_from_webhook(
		WP_Post $shipment_post,
		array $webhook_shipment
	): WP_Post {
		return setel_express_db_transaction( function () use ( $shipment_post, $webhook_shipment ) {
			$meta = [];

			if ( isset( $webhook_shipment['initialPickupType'] ) ) {
				$meta['pickup_type'] = $this->api_data->resolve_pickup_type_from_webhook( $webhook_shipment['initialPickupType'] );
			}

			if ( isset( $webhook_shipment['senderName'] ) ) {
				$meta['sender_name'] = $webhook_shipment['senderName'];
			}

			if ( isset( $webhook_shipment['senderPhoneNumber'] ) ) {
				$meta['sender_phone_number'] = $webhook_shipment['senderPhoneNumber'];
			}

			if ( isset( $webhook_shipment['senderAddress1'] ) ) {
				$meta['sender_address1'] = $webhook_shipment['senderAddress1'];
			}

			if ( isset( $webhook_shipment['senderAddress2'] ) ) {
				$meta['sender_postcode'] = $webhook_shipment['senderAddress2'] ?: null;
			}

			if ( isset( $webhook_shipment['senderPostalCode'] ) ) {
				$meta['sender_postcode'] = $webhook_shipment['senderPostalCode'];
			}

			if ( isset( $webhook_shipment['senderCity'] ) ) {
				$meta['sender_city'] = $webhook_shipment['senderCity'];
			}

			if ( isset( $webhook_shipment['senderState'] ) ) {
				$meta['sender_state'] = $webhook_shipment['senderState'];
			}

			if ( isset( $webhook_shipment['receiverName'] ) ) {
				$meta['receiver_name'] = $webhook_shipment['receiverName'];
			}

			if ( isset( $webhook_shipment['receiverPhoneNumber'] ) ) {
				$meta['receiver_phone_number'] = $webhook_shipment['receiverPhoneNumber'];
			}

			if ( isset( $webhook_shipment['receiverAddress1'] ) ) {
				$meta['receiver_address1'] = $webhook_shipment['receiverAddress1'];
			}

			if ( isset( $webhook_shipment['receiverAddress2'] ) ) {
				$meta['receiver_address2'] = $webhook_shipment['receiverAddress2'] ?: null;
			}

			if ( isset( $webhook_shipment['receiverPostalCode'] ) ) {
				$meta['receiver_postcode'] = $webhook_shipment['receiverPostalCode'];
			}

			if ( isset( $webhook_shipment['receiverCity'] ) ) {
				$meta['receiver_city'] = $webhook_shipment['receiverCity'];
			}

			if ( isset( $webhook_shipment['receiverState'] ) ) {
				$meta['receiver_state'] = $webhook_shipment['receiverState'];
			}

			if ( isset( $webhook_shipment['noOfParcels'] ) ) {
				$meta['no_of_parcels'] = (int) $webhook_shipment['noOfParcels'];
			}

			if ( isset( $webhook_shipment['parcelWeight'] ) ) {
				$meta['parcel_weight'] = (float) $webhook_shipment['parcelWeight'];
			}

			if ( isset( $webhook_shipment['parcelSize'] ) ) {
				$meta['parcel_size'] = $webhook_shipment['parcelSize'] ?: null;
			}

			if ( isset( $webhook_shipment['instructions'] ) ) {
				$meta['instructions'] = $webhook_shipment['instructions'] ?: null;
			}

			if ( isset( $webhook_shipment['status'] ) ) {
				$meta['status'] = $this->api_data->resolve_shipment_status_from_webhook( $webhook_shipment['status'] );
			}

			$shipment_post_id = wp_update_post( [
				'ID'         => $this->get_by_api_id( $webhook_shipment['id'] )->ID,
				'meta_input' => $meta,
			] );

			return $this->get( $shipment_post_id );
		} );
	}

	public static function make() {
		return new self(
			Setel_Express_Api::make(),
			Setel_Express_Api_Data::make(),
			Setel_Express_Settings::make(),
			Setel_Express_Authenticate::make()
		);
	}
}