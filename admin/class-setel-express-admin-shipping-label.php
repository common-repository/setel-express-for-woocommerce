<?php

class Setel_Express_Admin_Shipping_Label {
	protected Setel_Express_Shipment $shipment;

	public function __construct() {
		$this->shipment = Setel_Express_Shipment::make();

		$this->define_hooks();
	}

	public function define_hooks() {
		add_action( 'admin_post_setel_express_print_shipping_label', [ $this, 'print' ] );
	}

	public function print() {
		if ( empty( $_REQUEST['shipments'] ) ) {
			throw new Exception( 'No shipments selected for printing shipping label.' );
		}

		$shipments           = array_map( [ $this->shipment, 'get' ], wc_clean( $_REQUEST['shipments'] ) ?? [] );
		$printable_shipments = array_filter( $shipments, function ( WP_Post $shipment ) {
			return $this->shipment->is_printable( $shipment );
		} );

		if ( empty ( $printable_shipments ) ) {
			setel_express_add_admin_notice(
				__( 'No shipment printed. You can only print shipment with non-cancelled status.', 'setel-express' ),
				'warning'
			);

			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$shipping_labels = $this->shipment->print( $printable_shipments );

		$filename = 'setel-express-shipping-labels.pdf';
		if ( count( $printable_shipments ) === 1 ) {
			$tracking_number = $this->shipment->get_tracking_number( reset( $printable_shipments ) );

			$filename = "setel-express-{$tracking_number}-shipping-label.pdf";
		}

		header( 'Content-type: application/pdf' );
		header( 'Expires: -1' );
		header( "Content-Disposition:attachment; filename={$filename}" );

		echo setel_express_value( $shipping_labels );

		exit;
	}
}