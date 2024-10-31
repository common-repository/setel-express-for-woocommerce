<?php

$setel_express_settings = Setel_Express_Settings::make();
$setel_express_shipment = Setel_Express_Shipment::make();

$shipment_post = $setel_express_shipment->get( wc_clean( $_GET['shipment'] ) );

$print_label_url = add_query_arg( [
    'shipments' => [
        $shipment_post->ID,
    ],
], admin_url( 'admin-post.php?action=setel_express_print_shipping_label' ) );

$edit_url = add_query_arg( [
    'shipment' => $shipment_post->ID,
], admin_url( 'admin.php?page=setel-express-shipments-edit' ) );

$cancel_url = add_query_arg( [
    'shipment' => $shipment_post->ID,
], admin_url( 'admin-post.php?action=setel_express_cancel_shipment' ) );

?>
<div
    id="setel-express-shipments-details-wrap"
    class="wrap"
>
    <h1 class="wp-heading-inline"><?php echo esc_html__( 'Shipment',
                'setel-express' ) . ' ' . $setel_express_shipment->get_tracking_number( $shipment_post ) ?></h1>
    <?php if ( $setel_express_shipment->is_printable( $shipment_post ) ): ?>
        <a href="<?php echo esc_url( $print_label_url ) ?>" class="page-title-action"><?php echo esc_html__( 'Print Shipping Label',
                'setel-express' ) ?></a>
    <?php endif ?>
    <?php if ( $setel_express_shipment->is_editable( $shipment_post ) ): ?>
        <a href="<?php echo esc_url( $edit_url ) ?>" class="page-title-action"><?php echo esc_html__( 'Edit Shipment', 'setel-express' ) ?></a>
    <?php endif ?>
    <?php if ( $setel_express_shipment->is_cancellable( $shipment_post ) ): ?>
        <form
            id="setel-express-shipments-details-cancel-form"
            class="tw-inline"
            action="<?php echo esc_url( $cancel_url ) ?>"
            method="post"
        >
            <button type="submit" class="page-title-action"><?php echo esc_html__( 'Cancel Shipment', 'setel-express' ) ?></button>
        </form>
    <?php endif ?>
    <hr class="wp-header-end">
    <div id="poststuff">
        <section class="postbox">
            <div class="postbox-header">
                <h2><?php echo esc_html__( 'Shipment', 'setel-express' ) ?></h2>
            </div>
            <div class="inside">
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Shipment ID', 'setel-express' ) ?></th>
                        <td><?php echo esc_html( $setel_express_shipment->get_tracking_number( $shipment_post ) ) ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Order', 'setel-express' ) ?></th>
                        <td>
                            <?php
                            $order = $setel_express_shipment->get_order( $shipment_post );
                            if ( $order ) {
                                ?>
                                <a href="<?php echo esc_url( $order->get_edit_order_url() ) ?>">
                                    <?php echo esc_html( '#' . $order->get_id() ) ?>
                                </a>
                                <?php
                            } else {
                                echo esc_html( '#' . $shipment_post->ID );
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Status', 'setel-express' ) ?></th>
                        <td><?php echo setel_express_value( $setel_express_shipment->get_status_badge( $shipment_post ) ) ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Created On', 'setel-express' ) ?></th>
                        <td><?php echo esc_html( wp_date( 'j M Y, g:i A', strtotime( $shipment_post->post_date_gmt ) ) ) ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <section class="postbox">
            <div class="postbox-header">
                <h2><?php echo esc_html__( 'Receiver', 'setel-express' ) ?></h2>
            </div>
            <div class="inside">
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Name', 'setel-express' ) ?></th>
                        <td><?php echo esc_html( $setel_express_shipment->get_receiver_name( $shipment_post ) ) ?></td>
                    </tr>
                    <tbody>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Phone number', 'setel-express' ) ?></th>
                        <td><?php echo esc_html( $setel_express_shipment->get_receiver_phone_number( $shipment_post ) ) ?></td>
                    </tr>
                    <tbody>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Address', 'setel-express' ) ?></th>
                        <td>
                            <?php echo esc_html( $setel_express_shipment->get_receiver_address1( $shipment_post ) ) ?><br>
                            <?php
                            if ( $setel_express_shipment->get_receiver_address2( $shipment_post ) ): ?>
                                <?php echo esc_html( $setel_express_shipment->get_receiver_address2( $shipment_post ) ) ?><br>
                            <?php
                            endif; ?>
                            <?php echo esc_html( $setel_express_shipment->get_receiver_postcode( $shipment_post ) ) ?> <?php echo esc_html( $setel_express_shipment->get_receiver_city( $shipment_post ) ) ?>
                            <br>
                            <?php echo esc_html( $setel_express_shipment->get_receiver_state( $shipment_post ) ) ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Delivery instructions', 'setel-express' ) ?></th>
                        <td><?php echo esc_html( $setel_express_shipment->get_instructions( $shipment_post ) ) ?: '-' ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <section class="postbox">
            <div class="postbox-header">
                <h2><?php echo esc_html__( 'Parcel', 'setel-express' ) ?></h2>
            </div>
            <div class="inside">
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Number of parcels', 'setel-express' ) ?></th>
                        <td><?php echo esc_html( $setel_express_shipment->get_no_of_parcels( $shipment_post ) ) ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Weight (kg)', 'setel-express' ) ?></th>
                        <td><?php echo esc_html( number_format( $setel_express_shipment->get_parcel_weight( $shipment_post ), 2 ) ) ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Parcel size', 'setel-express' ) ?></th>
                        <td><?php echo esc_html( $setel_express_shipment->get_parcel_size( $shipment_post ) ) ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <section class="postbox">
            <div class="postbox-header">
                <h2><?php echo esc_html__( 'Pickup option', 'setel-express' ) ?></h2>
            </div>
            <div class="inside">
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Pickup type', 'setel-express' ) ?></th>
                        <td><?php echo esc_html( $setel_express_shipment->get_parcel_size( $shipment_post ) ) ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Name', 'setel-express' ) ?></th>
                        <td><?php echo esc_html( $setel_express_shipment->get_sender_name( $shipment_post ) ) ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Phone number', 'setel-express' ) ?></th>
                        <td><?php echo esc_html( $setel_express_shipment->get_sender_phone_number( $shipment_post ) ) ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Address', 'setel-express' ) ?></th>
                        <td>
                            <?php echo esc_html( $setel_express_shipment->get_sender_address1( $shipment_post ) ) ?><br>
                            <?php
                            if ( $setel_express_shipment->get_sender_address2( $shipment_post ) ): ?>
                                <?php echo esc_html( $setel_express_shipment->get_sender_address2( $shipment_post ) ) ?><br>
                            <?php
                            endif; ?>
                            <?php echo esc_html( $setel_express_shipment->get_sender_postcode( $shipment_post ) ) ?> <?php echo esc_html( $setel_express_shipment->get_sender_city( $shipment_post ) ) ?>
                            <br>
                            <?php echo esc_html( $setel_express_shipment->get_sender_state( $shipment_post ) ) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>