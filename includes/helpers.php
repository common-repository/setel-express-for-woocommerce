<?php

function setel_express_add_admin_notice( $notice, $type ) {
	if ( ! is_array( $notice ) ) {
		$notice = [
			'message' => $notice,
		];
	}

	if ( empty( $notice['message'] ) ) {
		throw new \RuntimeException( 'notice message is missing.' );
	}

	$notice['message'] = wp_kses_post( $notice['message'] );

	$notice = array_merge( [
		'title'   => '',
		'message' => '',
		'list'    => [],
	], $notice );

	$notice['type'] = $type;

	$notices   = setel_express_get_admin_notices();
	$notices[] = $notice;

	setel_express_set_admin_notices( $notices );
}

function setel_express_get_admin_notices() {
	return get_option( 'setel_express_admin_notices', [] );
}

/**
 * @param $notices
 *
 * @return bool
 */
function setel_express_set_admin_notices( $notices ) {
	return update_option( 'setel_express_admin_notices', $notices );
}

/**
 * Clean admin notices
 */
function setel_express_clear_admin_notices() {
	delete_option( 'setel_express_admin_notices' );
}

function setel_express_is_shipment_post(
	WP_Post $post
): bool {
	return $post->post_type === 'setel_shipment';
}

function setel_express_wc_state_name_map( $state ) {
	return [
		'JHR' => __( 'Johor', 'setel-express' ),
		'KDH' => __( 'Kedah', 'setel-express' ),
		'KTN' => __( 'Kelantan', 'setel-express' ),
		'LBN' => __( 'Labuan', 'setel-express' ),
		'MLK' => __( 'Melaka', 'setel-express' ),
		'NSN' => __( 'Negeri Sembilan', 'setel-express' ),
		'PHG' => __( 'Pahang', 'setel-express' ),
		'PNG' => __( 'Pulau Pinang', 'setel-express' ),
		'PRK' => __( 'Perak', 'setel-express' ),
		'PLS' => __( 'Perlis', 'setel-express' ),
		'SBH' => __( 'Sabah', 'setel-express' ),
		'SWK' => __( 'Sarawak', 'setel-express' ),
		'SGR' => __( 'Selangor', 'setel-express' ),
		'TRG' => __( 'Terengganu', 'setel-express' ),
		'PJY' => __( 'Putrajaya', 'setel-express' ),
		'KUL' => __( 'Kuala Lumpur', 'setel-express' ),
	][ $state ];
}

function setel_express_wc_order_total_weight( WC_Order $order ) {
	$weights = array_map( function ( WC_Order_Item $item ) {
		$product = $item->get_product();
		if ( ! $product ) {
			return 0;
		}

		$product_weight = $product->get_weight();
		if ( ! $product_weight ) {
			return 0;
		}

		return $product_weight * $item->get_quantity();
	}, $order->get_items() );

	return array_sum( $weights );
}

function setel_express_db_transaction(
	Closure $callback
) {
	wc_transaction_query( 'start' );

	try {
		$result = $callback();

		wc_transaction_query( 'commit' );;

		return $result;
	} catch ( Exception $exception ) {
		wc_transaction_query( 'rollback' );;
		throw $exception;
	}
}

function setel_express_api_log(
	string $message,
	string $level = WC_Log_Levels::NOTICE
): void {
	( new WC_Logger )->add( 'setel-express-api', $message, $level );
}

function setel_express_webhook_log(
	string $message,
	string $level = WC_Log_Levels::NOTICE
): void {
	( new WC_Logger )->add( 'setel-express-webhook', $message, $level );
}

function setel_express_value(
	$value,
	...$args
) {
	return $value instanceof Closure ? $value( ...$args ) : $value;
}