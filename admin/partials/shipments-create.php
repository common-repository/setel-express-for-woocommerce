<?php

/**
 * @var \WC_Order[] $shippable_orders
 */

$setel_express_api_data = Setel_Express_Api_Data::make();
$setel_express_settings = Setel_Express_Settings::make();

$form_data_input = [
    'initialPickupType' => 'Pickup',
    'senderName'        => $setel_express_settings->sender_address_name(),
    'senderPhoneNumber' => $setel_express_settings->sender_address_phone_number(),
    'senderAddress1'    => $setel_express_settings->sender_address_address1(),
    'senderAddress2'    => $setel_express_settings->sender_address_address2(),
    'senderPostcode'    => $setel_express_settings->sender_address_postcode(),
    'senderCity'        => $setel_express_settings->sender_address_city(),
    'senderState'       => $setel_express_settings->sender_address_state(),
    'shipments'         => array_map( function ( WC_Order $order ) {
        $order_total_weight = setel_express_wc_order_total_weight( $order );

        return [
            'orderId'             => $order->get_id(),
            'receiverName'        => $order->get_formatted_shipping_full_name(),
            'receiverPhoneNumber' => $order->get_shipping_phone() ?: $order->get_billing_phone(),
            'receiverAddress1'    => $order->get_shipping_address_1(),
            'receiverAddress2'    => $order->get_shipping_address_2(),
            'receiverPostcode'    => $order->get_shipping_postcode(),
            'receiverCity'        => $order->get_shipping_city(),
            'receiverState'       => setel_express_wc_state_name_map( $order->get_shipping_state() ),
            'noOfParcels'         => 1,
            'parcelWeight'        => $order_total_weight,
            'parcelSize'          => $order_total_weight >= 3 ? Setel_Express_Shipment::PARCEL_SIZE_M : Setel_Express_Shipment::PARCEL_SIZE_S,
            'instructions'        => $order->get_customer_note(),
        ];
    }, $shippable_orders ),
];

$form_data_supported_sender_postcodes   = $setel_express_api_data->supported_sender_postcodes();
$form_data_supported_receiver_postcodes = $setel_express_api_data->supported_receiver_postcodes( $form_data_input['senderPostcode'] );
$form_data_phone_countries              = $setel_express_api_data->phone_countries();

$imploded_phone_country_prefixed_codes = implode( ', ', array_map( function ( $phone_country ) {
    return $phone_country['prefixed_code'];
}, $form_data_phone_countries ) );

$form_action = admin_url( 'admin-post.php?action=setel_express_create_shipments' );
?>
<div
    id="setel-express-shipments-create-wrap"
    class="wrap"
>
    <h1 class="wp-heading-inline"><?php echo esc_html__( 'New Shipments', 'setel-express' ) ?></h1>
    <hr class="wp-header-end">

    <form
        v-cloak
        id="setel-express-shipments-create-form"
        action="<?php echo esc_url( $form_action ) ?>"
        method="post"
        data-input='<?php echo esc_attr( json_encode( $form_data_input ) ) ?>'
        data-supported-sender-postcodes='<?php echo esc_attr( json_encode( $form_data_supported_sender_postcodes ) ) ?>'
        data-supported-receiver-postcodes='<?php echo esc_attr( json_encode( $form_data_supported_receiver_postcodes ) ) ?>'
        data-phone-countries='<?php echo esc_attr( json_encode( $form_data_phone_countries ) ) ?>'
        @submit.prevent="handleSubmit"
    >
        <div
            id="poststuff"
            class="tw-space-y-4"
        >
            <div class="tw-grid lg:tw-grid-cols-2 tw-gap-4">
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>Pickup Type</h2>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tbody>
                            <?php foreach ( $setel_express_api_data->pickup_types() as $pickup_type ): ?>
                                <tr>
                                    <th scope="row">
                                        <label>
                                            <input
                                                v-model="v$.input.initialPickupType.$model"
                                                type="radio"
                                                name="initial_pickup_type"
                                                value="<?php echo esc_attr( $pickup_type['code'] ) ?>"
                                            >
                                            <?php echo esc_html( $pickup_type['name'] ) ?>
                                        </label>
                                    </th>
                                    <td>
                                        <?php echo esc_html( $pickup_type['description'] ) ?>
                                        <?php if ( $pickup_type['code'] === Setel_Express_Shipment::PICKUP_TYPE_DROP_OFF ): ?>
                                            <a
                                                href="https://www.setel.com/business/setel-express/our-hubs"
                                                target="_blank"
                                                rel="noreferrer"
                                            >
                                                <?php echo esc_html__( 'View a list of all Setel Express hubs', 'setel-express' ) ?>
                                            </a>
                                        <?php endif ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>
                            {{ isPickup ? '<?php echo esc_html__('Pickup address', 'setel - express') ?>' : '<?php echo esc_html__('Return address', 'setel - express') ?>'}}</h2>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="setel-express-shipments-create-form-sender-name">
                                        <?php echo esc_html__( 'Name', 'setel-express' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <input
                                        v-model="v$.input.senderName.$model"
                                        type="text"
                                        id="setel-express-shipments-create-form-sender-name"
                                        class="regular-text"
                                        name="sender_name"
                                        required
                                    >
                                    <small
                                        v-if="v$.input.senderName.required.$invalid"
                                        class="tw-block tw-text-red-500"
                                    >
                                        <?php echo esc_html__( 'Name is required.', 'setel-express' ) ?>
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="setel-express-shipments-create-form-sender-phone-number">
                                        <?php echo esc_html__( 'Phone number', 'setel-express' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <input
                                        v-model="v$.input.senderPhoneNumber.$model"
                                        type="tel"
                                        id="setel-express-shipments-create-form-sender-phone-number"
                                        class="regular-text"
                                        name="sender_phone_number"
                                        required
                                        minlength="11"
                                        maxlength="13"
                                    >
                                    <small
                                        v-if="v$.input.senderPhoneNumber.required.$invalid"
                                        class="tw-block tw-text-red-500"
                                    >
                                        <?php echo esc_html__( 'Phone number is required.', 'setel-express' ) ?>
                                    </small>
                                    <small
                                        v-else-if="v$.input.senderPhoneNumber.validatePhoneCountryCode.$invalid"
                                        class="tw-block tw-text-red-500"
                                    >
                                        <?php echo sprintf(
                                            esc_html__( 'Phone number must be starts with one of following country codes: %s', 'setel-express' ),
                                            $imploded_phone_country_prefixed_codes
                                        ) ?>
                                    </small>
                                    <small
                                        v-else-if="v$.input.senderPhoneNumber.minLength.$invalid"
                                        class="tw-block tw-text-red-500"
                                    >
                                        <?php echo esc_html__( 'Phone number is invalid.', 'setel-express' ) ?>
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="setel-express-shipments-create-form-sender-address1">
                                        <?php echo esc_html__( 'Address 1', 'setel-express' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <input
                                        v-model="v$.input.senderAddress1.$model"
                                        type="text"
                                        id="setel-express-shipments-create-form-sender-address1"
                                        class="regular-text"
                                        name="sender_address1"
                                        required
                                    >
                                    <small
                                        v-if="v$.input.senderAddress1.required.$invalid"
                                        class="tw-block tw-text-red-500"
                                    >
                                        <?php echo esc_html__( 'Address 1 is required.', 'setel-express' ) ?>
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="setel-express-shipments-create-form-sender-address2">
                                        <?php echo esc_html__( 'Address 2', 'setel-express' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <input
                                        v-model="v$.input.senderAddress2.$model"
                                        type="text"
                                        id="setel-express-shipments-create-form-sender-address2"
                                        class="regular-text"
                                        name="sender_address2"
                                    >
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="setel-express-shipments-create-form-sender-postcode">
                                        <?php echo esc_html__( 'Postcode', 'setel-express' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <input
                                        v-model="v$.input.senderPostcode.$model"
                                        type="text"
                                        id="setel-express-shipments-create-form-sender-postcode"
                                        class="regular-text"
                                        name="sender_postcode"
                                        required
                                        maxlength="5"
                                        @blur="handleSenderPostcodeOnBlur"
                                    >
                                    <small
                                        v-if="v$.input.senderPostcode.required.$invalid"
                                        class="tw-block tw-text-red-500"
                                    >
                                        <?php echo esc_html__( 'Postcode is required.', 'setel-express' ) ?>
                                    </small>
                                    <small
                                        v-if="v$.input.senderPostcode.includes.$invalid"
                                        class="tw-block tw-text-red-500"
                                    >
                                        <?php echo esc_html__( 'Postcode is not supported.', 'setel-express' ) ?>
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="setel-express-shipments-create-form-sender-city">
                                        <?php echo esc_html__( 'City', 'setel-express' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <input
                                        v-model="v$.input.senderCity.$model"
                                        type="text"
                                        id="setel-express-shipments-create-form-sender-city"
                                        class="regular-text"
                                        name="sender_city"
                                        required
                                    >
                                    <small
                                        v-if="v$.input.senderCity.required.$invalid"
                                        class="tw-block tw-text-red-500"
                                    >
                                        <?php echo esc_html__( 'City is required.', 'setel-express' ) ?>
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="setel-express-shipments-create-form-sender-state">
                                        <?php echo esc_html__( 'State', 'setel-express' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <select
                                        v-model="v$.input.senderState.$model"
                                        id="setel-express-shipments-create-form-sender-state"
                                        name="sender_state"
                                        required
                                    >
                                        <?php foreach ( $setel_express_api_data->states() as $state ): ?>
                                            <option value="<?php echo esc_attr( $state ) ?>">
                                                <?php echo esc_html( $state, 'setel-express' ) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                    <small
                                        v-if="v$.input.senderState.required.$invalid"
                                        class="tw-block tw-text-red-500"
                                    >
                                        <?php echo esc_html( 'State is required.', 'setel-express' ) ?>
                                    </small>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="postbox">
                <div class="postbox-header">
                    <h2><?php echo esc_html__( 'Shipments', 'setel-express' ) ?></h2>
                </div>
                <div class="inside tw-m-0 tw-p-0">
                    <table
                        class="
                            wp-list-table widefat fixed striped posts
                            tw-border-0
                        "
                    >
                        <thead>
                        <tr>
                            <th class="manage-column tw-w-16">
                                #
                            </th>
                            <th class="manage-column">
                                <?php echo esc_html__( 'Parcel details' ) ?>
                            </th>
                            <th class="manage-column">
                                <?php echo esc_html__( 'Receiver details' ) ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(shipment, index) in input.shipments" :key="index">
                            <td>{{ index + 1 }}</td>
                            <td>
                                <table class="form-table tw-m-0">
                                    <tbody>
                                    <tr>
                                        <th class="tw-py-2">
                                            <label :for="`setel-express-shipments-create-form-shipments-${index}-order-id`">
                                                <?php echo esc_html__( 'Order', 'setel-express' ) ?>
                                            </label>
                                        </th>
                                        <td class="tw-py-2">
                                            <input
                                                v-model="shipment.orderId"
                                                type="text"
                                                :id="`setel-express-shipments-create-form-shipments-${index}-order-id`"
                                                class="regular-text"
                                                :name="`shipments[${index}][order_id]`"
                                                required
                                                readonly
                                            >
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="tw-py-2">
                                            <label :for="`setel-express-shipments-create-form-shipments-${index}-no-of-parcels`">
                                                <?php echo esc_html__( 'Number of parcels', 'setel-express' ) ?>
                                            </label>
                                        </th>
                                        <td class="tw-py-2">
                                            <input
                                                v-model.number="shipment.noOfParcels"
                                                type="number"
                                                :id="`setel-express-shipments-create-form-shipments-${index}-no-of-parcels`"
                                                class="regular-text"
                                                :name="`shipments[${index}][no_of_parcels]`"
                                                min="1"
                                                required
                                            >
                                            <small
                                                v-if="!v$.input.shipments.$each.$response.$data[index].noOfParcels.required"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html__( 'Number of parcels is required.', 'setel-express' ) ?>
                                            </small>
                                            <small
                                                v-if="!v$.input.shipments.$each.$response.$data[index].noOfParcels.min"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html__( 'Number of parcels must be at least 1.', 'setel-express' ) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="tw-py-2">
                                            <label :for="`setel-express-shipments-create-form-shipments-${index}-parcel-weight`">
                                                <?php echo esc_html__( 'Parcel weight (Kg)', 'setel-express' ) ?>
                                            </label>
                                        </th>
                                        <td class="tw-py-2">
                                            <input
                                                v-model.number="shipment.parcelWeight"
                                                type="number"
                                                :id="`setel-express-shipments-create-form-shipments-${index}-parcel-weight`"
                                                class="regular-text"
                                                :name="`shipments[${index}][parcel_weight]`"
                                                step="any"
                                                required
                                            >
                                            <small
                                                v-if="!v$.input.shipments.$each.$response.$data[index].parcelWeight.required"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html__( 'Parcel weight is required.', 'setel-express' ) ?>
                                            </small>
                                            <small
                                                v-if="!v$.input.shipments.$each.$response.$data[index].parcelWeight.min"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html__( 'Parcel weight must be at least 0.001 kg.', 'setel-express' ) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="tw-py-2">
                                            <?php echo esc_html__( 'Parcel Size', 'setel-express' ) ?><br>
                                            <a href="https://www.setel.com/business/setel-express/faq" target="_blank" rel="noreferrer">
                                                <?php echo esc_html__( 'Learn more', 'setel-express' ) ?>
                                            </a>
                                        </th>
                                        <td class="tw-py-2">
                                            <fieldset>
                                                <legend class="screen-reader-text">
                                                    <span><?php echo esc_html__( 'Parcel Size', 'setel-express' ) ?></span>
                                                </legend>
                                                <?php foreach ( $setel_express_api_data->parcel_sizes() as $parcel_size ): ?>
                                                    <label
                                                        class="tw-block"
                                                        title="<?php echo esc_attr( $parcel_size['parcelSize'] ) ?>"
                                                    >
                                                        <input
                                                            v-model="shipment.parcelSize"
                                                            type="radio"
                                                            :name="`shipments[${index}][parcel_size]`"
                                                            value="<?php echo esc_attr( $parcel_size['parcelSize'] ) ?>"
                                                            required
                                                        >
                                                        <span>
                                                            <?php echo esc_html( $parcel_size['parcelSize'], 'setel-express' ) ?>
                                                            —
                                                            <?php echo esc_html( $parcel_size['description'], 'setel-express' ) ?>
                                                        </span>
                                                    </label>
                                                <?php endforeach ?>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="tw-py-2">
                                            <label :for="`setel-express-shipments-create-form-shipments-${index}-instructions`">
                                                <?php echo esc_html__( 'Delivery instruction', 'setel-express' ) ?>
                                            </label>
                                        </th>
                                        <td class="tw-py-2">
                                                <textarea
                                                    v-model="shipment.instructions"
                                                    :id="`setel-express-shipments-create-form-shipments-${index}-instructions`"
                                                    class="regular-text"
                                                    :name="`shipments[${index}][instructions]`"
                                                    rows="3"
                                                    placeholder="Optional"
                                                >
                                                </textarea>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td>
                                <table class="form-table tw-m-0">
                                    <tbody>
                                    <tr>
                                        <th class="tw-py-2">
                                            <label :for="`setel-express-shipments-create-form-shipments-${index}-receiver-name`">
                                                <?php echo esc_html__( 'Receiver name', 'setel-express' ) ?>
                                            </label>
                                        </th>
                                        <td class="tw-py-2">
                                            <input
                                                v-model="shipment.receiverName"
                                                type="text"
                                                :id="`setel-express-shipments-create-form-shipments-${index}-receiver-name`"
                                                class="regular-text"
                                                :name="`shipments[${index}][receiver_name]`"
                                                required
                                            >
                                            <small
                                                v-if="!v$.input.shipments.$each.$response.$data[index].receiverName.required"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html__( 'Receiver name is required.', 'setel-express' ) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="tw-py-2">
                                            <label :for="`setel-express-shipments-create-form-shipments-${index}-receiver-phone-number`">
                                                <?php echo esc_html__( 'Phone number', 'setel-express' ) ?>
                                            </label>
                                        </th>
                                        <td class="tw-py-2">
                                            <input
                                                v-model="shipment.receiverPhoneNumber"
                                                type="text"
                                                :id="`setel-express-shipments-create-form-shipments-${index}-receiver-phone-number`"
                                                class="regular-text"
                                                :name="`shipments[${index}][receiver_phone_number]`"
                                                required
                                                minlength="11"
                                                maxlength="13"
                                            >
                                            <small
                                                v-if="!v$.input.shipments.$each.$response.$data[index].receiverPhoneNumber.required"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html__( 'Phone number is required.', 'setel-express' ) ?>
                                            </small>
                                            <small
                                                v-else-if="!v$.input.shipments.$each.$response.$data[index].receiverPhoneNumber.validatePhoneCountryCode"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo sprintf(
                                                    esc_html__( 'Phone number must be starts with one of following country codes: %s',
                                                        'setel-express' ),
                                                    $imploded_phone_country_prefixed_codes
                                                ) ?>
                                            </small>
                                            <small
                                                v-else-if="!v$.input.shipments.$each.$response.$data[index].receiverPhoneNumber.minLength"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html__( 'Phone number is invalid.', 'setel-express' ) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="tw-py-2">
                                            <label :for="`setel-express-shipments-create-form-shipments-${index}-receiver-address1`">
                                                <?php echo esc_html__( 'Address 1', 'setel-express' ) ?>
                                            </label>
                                        </th>
                                        <td class="tw-py-2">
                                            <input
                                                v-model="shipment.receiverAddress1"
                                                type="text"
                                                :id="`setel-express-shipments-create-form-shipments-${index}-receiver-address1`"
                                                class="regular-text"
                                                :name="`shipments[${index}][receiver_address1]`"
                                                required
                                            >
                                            <small
                                                v-if="!v$.input.shipments.$each.$response.$data[index].receiverAddress1.required"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html__( 'Address 1 is required.', 'setel-express' ) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="tw-py-2">
                                            <label :for="`setel-express-shipments-create-form-shipments-${index}-receiver-address2`">
                                                <?php echo esc_html__( 'Address 2', 'setel-express' ) ?>
                                            </label>
                                        </th>
                                        <td class="tw-py-2">
                                            <input
                                                v-model="shipment.receiverAddress2"
                                                type="text"
                                                :id="`setel-express-shipments-create-form-shipments-${index}-receiver-address2`"
                                                class="regular-text"
                                                :name="`shipments[${index}][receiver_address2]`"
                                            >
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label :for="`setel-express-shipments-create-form-shipments-${index}-postcode`">
                                                <?php echo esc_html__( 'Postcode', 'setel-express' ) ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input
                                                v-model="shipment.receiverPostcode"
                                                type="text"
                                                :id="`setel-express-shipments-create-form-shipments-${index}-postcode`"
                                                class="regular-text"
                                                :name="`shipments[${index}][receiver_postcode]`"
                                                required
                                                maxlength="5"
                                            >
                                            <small
                                                v-if="!v$.input.shipments.$each.$response.$data[index].receiverPostcode.required"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html__( 'Postcode is required.', 'setel-express' ) ?>
                                            </small>
                                            <small
                                                v-if="!v$.input.shipments.$each.$response.$data[index].receiverPostcode.includes"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html__( 'Postcode is not supported.', 'setel-express' ) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label :for="`setel-express-shipments-create-form-shipments-${index}-receiver-city`">
                                                <?php echo esc_html__( 'City', 'setel-express' ) ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input
                                                v-model="shipment.receiverCity"
                                                type="text"
                                                :id="`setel-express-shipments-create-form-shipments-${index}-receiver-city`"
                                                class="regular-text"
                                                :name="`shipments[${index}][receiver_city]`"
                                                required
                                            >
                                            <small
                                                v-if="!v$.input.shipments.$each.$response.$data[index].receiverCity.required"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html__( 'City is required.', 'setel-express' ) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label :for="`setel-express-shipments-create-form-shipments-${index}-receiver-state`">
                                                <?php echo esc_html__( 'State', 'setel-express' ) ?>
                                            </label>
                                        </th>
                                        <td>
                                            <select
                                                v-model="shipment.receiverState"
                                                :id="`setel-express-shipments-create-form-shipments-${index}-receiver-state`"
                                                :name="`shipments[${index}][receiver_state]`"
                                                required
                                            >
                                                <?php
                                                foreach ( $setel_express_api_data->states() as $state ): ?>
                                                    <option value="<?php echo esc_attr( $state ) ?>">
                                                        <?php echo esc_html( $state, 'setel-express' ) ?>
                                                    </option>
                                                <?php
                                                endforeach ?>
                                            </select>
                                            <small
                                                v-if="!v$.input.shipments.$each.$response.$data[index].receiverState.required"
                                                class="tw-block tw-text-red-500"
                                            >
                                                <?php echo esc_html( 'State is required.', 'setel-express' ) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <input
            type="hidden"
            id="setel-express-shipments-create-form-nonce"
            name="_nonce"
            value="<?php echo esc_attr( wp_create_nonce( 'setel-express-shipments-create-form' ) ) ?>"
        >

        <input
            type="hidden"
            id="setel-express-api-data-supported-receiver-postcodes-nonce"
            value="<?php echo esc_attr( wp_create_nonce( 'setel-express-api-data-supported-receiver-postcodes-nonce' ) ) ?>"
        >

        <p class="submit">
            <button
                type="submit"
                class="button button-primary"
            >
                Create
            </button>
        </p>
    </form>
</div>