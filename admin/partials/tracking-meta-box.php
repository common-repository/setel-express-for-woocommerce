<?php

global $post;

$setel_express_shipment = Setel_Express_Shipment::make();

$shipment_post = $setel_express_shipment->get_by_order( $post );
?>
<p>
    <strong><?php echo esc_html__( 'Shipment ID', 'setel-express' ) ?></strong><br>
    <a href="<?php echo esc_url( $setel_express_shipment->get_view_url( $shipment_post ) ) ?>">
        <?php echo esc_html( $setel_express_shipment->get_tracking_number( $shipment_post ) ) ?>
    </a>
</p>
<p>
    <strong><?php echo esc_html__( 'Status', 'setel-express' ) ?></strong><br>
    <?php echo setel_express_value( $setel_express_shipment->get_status_badge( $shipment_post ) ) ?>
</p>
