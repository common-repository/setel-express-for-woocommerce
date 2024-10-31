<?php

class Setel_Express_Admin_WC_Order {

	public function __construct() {
		$this->define_hooks();
	}

	public function define_hooks() {
		add_filter( 'bulk_actions-edit-shop_order', [ $this, 'add_create_bulk_action' ], 20 );
		add_filter( 'handle_bulk_actions-edit-shop_order', [ $this, 'handle_create_bulk_action' ], 10, 3 );

		add_filter( 'woocommerce_order_actions', [ $this, 'add_create_action' ] );
		add_filter( 'woocommerce_order_action_setel_express_create_shipments', [ $this, 'handle_create_action' ] );
	}

	public function add_create_bulk_action(
		array $actions
	): array {
		$actions['setel_express_create_shipments'] = __( 'Setel Express - Create shipments', 'setel-express' );

		return $actions;
	}

	public function handle_create_bulk_action(
		string $redirect_url,
		string $action,
		array $order_ids
	) {
		if ( $action !== 'setel_express_create_shipments' ) {
			return $redirect_url;
		}

		return add_query_arg( [
			'orders' => $order_ids,
		], admin_url( 'admin.php?page=setel-express-shipments-create' ) );
	}

	public function add_create_action( array $actions ): array {
		$actions['setel_express_create_shipments'] = __( 'Setel Express - Create shipment', 'setel-express' );

		return $actions;
	}

	public function handle_create_action(
		WC_Order $order
	) {
		wp_safe_redirect( add_query_arg( [
			'orders' => [ $order->get_id() ],
		], admin_url( 'admin.php?page=setel-express-shipments-create' ) ) );
		exit;
	}
}