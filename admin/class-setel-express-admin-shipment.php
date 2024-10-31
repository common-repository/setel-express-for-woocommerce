<?php

class Setel_Express_Admin_Shipment {

    protected Setel_Express_Shipment $shipment;

    protected Setel_Express_Api_Data $api_data;

    public function __construct() {
        $this->shipment = Setel_Express_Shipment::make();
        $this->api_data = Setel_Express_Api_Data::make();

        $this->define_hooks();
    }

    public function define_hooks() {
        add_action( 'init', [ $this, 'register_shipment_post_type' ] );
        add_filter( 'gutenberg_can_edit_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
        add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );

        add_action( 'admin_menu', [ $this, 'add_create_menu' ] );
        add_action( 'admin_menu', [ $this, 'add_details_menu' ] );
        add_action( 'admin_menu', [ $this, 'add_edit_menu' ] );
        add_action( 'admin_menu', [ $this, 'print_admin_notices' ] );

        add_filter( 'admin_title', [ $this, 'set_details_title' ], 10, 2 );

        add_filter( 'views_edit-setel_shipment', [ $this, 'reset_views' ] );

        add_filter( 'bulk_actions-edit-setel_shipment', [ $this, 'reset_bulk_actions' ], 20 );
        add_filter( 'bulk_actions-edit-setel_shipment', [ $this, 'add_change_pickup_option_bulk_actions' ], 20 );
        add_action( 'manage_posts_extra_tablenav', [ $this, 'add_extra_print_shipping_label_bulk_action' ] );
        add_action( 'manage_posts_extra_tablenav', [ $this, 'add_extra_cancel_bulk_action' ] );
        add_filter( 'handle_bulk_actions-edit-setel_shipment', [ $this, 'handle_change_pickup_option_bulk_action' ], 10, 3 );
        add_filter( 'handle_bulk_actions-edit-setel_shipment', [ $this, 'handle_print_shipping_label_bulk_action' ], 10, 3 );
        add_filter( 'handle_bulk_actions-edit-setel_shipment', [ $this, 'handle_cancel_bulk_action' ], 10, 3 );

        add_action( 'restrict_manage_posts', [ $this, 'add_shipment_status_filter' ], 10, 2 );
        add_filter( 'request', [ $this, 'add_shipment_status_request_query' ] );
        add_filter( 'disable_months_dropdown', [ $this, 'disable_months_dropdown' ], 10, 2 );
        add_action( 'restrict_manage_posts', [ $this, 'add_shipment_created_on_filter' ], 10, 2 );
        add_filter( 'request', [ $this, 'add_shipment_created_on_request_query' ] );

        add_filter( 'manage_setel_shipment_posts_columns', [ $this, 'set_listing_columns' ] );
        add_action( 'manage_setel_shipment_posts_custom_column', [ $this, 'render_listing_shipment_id_column' ], 10, 2 );
        add_action( 'manage_setel_shipment_posts_custom_column', [ $this, 'render_listing_order_column' ], 10, 2 );
        add_action( 'manage_setel_shipment_posts_custom_column', [ $this, 'render_listing_status_column' ], 10, 2 );
        add_action( 'manage_setel_shipment_posts_custom_column', [ $this, 'render_listing_receiver_column' ], 10, 2 );
        add_action( 'manage_setel_shipment_posts_custom_column', [ $this, 'render_listing_created_on_column' ], 10, 2 );
        add_filter( 'post_row_actions', [ $this, 'set_row_actions' ], 10, 2 );

        add_filter( 'manage_shop_order_posts_columns', [ $this, 'add_tracking_column' ], 20 );
        add_filter( 'manage_shop_order_posts_custom_column', [ $this, 'render_tracking_column' ], 10, 2 );
        add_action( 'add_meta_boxes', [ $this, 'add_tracking_meta_box' ] );

        add_action( 'admin_post_setel_express_create_shipments', [ $this, 'create' ] );
        add_action( 'admin_post_setel_express_edit_shipment', [ $this, 'edit' ] );
        add_action( 'admin_post_setel_express_cancel_shipment', [ $this, 'cancel' ] );

        add_action( 'woocommerce_api_setel_express_update_shipment', [ $this, 'handle_update_shipment_webhook' ] );
    }

    public function register_shipment_post_type() {
        $labels = [
            'name'                  => _x( 'Shipments', 'Post Type General Name', 'setel-express' ),
            'singular_name'         => _x( 'Shipment', 'Post Type Singular Name', 'setel-express' ),
            'menu_name'             => _x( 'Shipments', 'Admin Menu text', 'setel-express' ),
            'name_admin_bar'        => _x( 'Shipment', 'Add New on Toolbar', 'setel-express' ),
            'archives'              => __( 'Shipment Archives', 'setel-express' ),
            'attributes'            => __( 'Shipment Attributes', 'setel-express' ),
            'parent_item_colon'     => __( 'Parent Shipment:', 'setel-express' ),
            'all_items'             => __( ' Shipments', 'setel-express' ),
            'add_new_item'          => __( 'Add New Shipment', 'setel-express' ),
            'add_new'               => __( 'Add New', 'setel-express' ),
            'new_item'              => __( 'New Shipment', 'setel-express' ),
            'edit_item'             => __( 'Edit Shipment', 'setel-express' ),
            'update_item'           => __( 'Update Shipment', 'setel-express' ),
            'view_item'             => __( 'View Shipment', 'setel-express' ),
            'view_items'            => __( 'View Shipments', 'setel-express' ),
            'search_items'          => __( 'Search Shipment', 'setel-express' ),
            'not_found'             => __( 'Not found', 'setel-express' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'setel-express' ),
            'featured_image'        => __( 'Featured Image', 'setel-express' ),
            'set_featured_image'    => __( 'Set featured image', 'setel-express' ),
            'remove_featured_image' => __( 'Remove featured image', 'setel-express' ),
            'use_featured_image'    => __( 'Use as featured image', 'setel-express' ),
            'insert_into_item'      => __( 'Insert into Shipment', 'setel-express' ),
            'uploaded_to_this_item' => __( 'Uploaded to this Shipment', 'setel-express' ),
            'items_list'            => __( 'Shipments list', 'setel-express' ),
            'items_list_navigation' => __( 'Shipments list navigation', 'setel-express' ),
            'filter_items_list'     => __( 'Filter Shipments list', 'setel-express' ),
        ];

        $args = [
            'label'               => __( 'Shipment', 'setel-express' ),
            'description'         => __( '', 'setel-express' ),
            'labels'              => $labels,
            'menu_icon'           => '',
            'supports'            => [],
            'taxonomies'          => [],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'setel-express',
            'menu_position'       => 60,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => false,
            'has_archive'         => false,
            'hierarchical'        => false,
            'exclude_from_search' => false,
            'show_in_rest'        => true,
            'publicly_queryable'  => false,
            'capability_type'     => 'post',
        ];

        register_post_type( 'setel_shipment', $args );
    }

    public function disable_gutenberg( $can_edit, $post_type ) {
        return $post_type === 'setel_shipment' ? false : $can_edit;
    }

    public function add_create_menu() {
        add_submenu_page(
            'setel-express',
            __( 'Add new shipments', 'setel-express' ),
            __( 'Add new shipments', 'setel-express' ),
            'manage_woocommerce',
            'setel-express-shipments-create',
            [ $this, 'show_create_page' ]
        );

        add_action( 'admin_head', function () {
            remove_submenu_page( 'setel-express', 'setel-express-shipments-create' );
        } );
    }

    public function add_details_menu() {
        add_submenu_page(
            'setel-express',
            __( 'View shipment', 'setel-express' ),
            __( 'View shipment', 'setel-express' ),
            'manage_woocommerce',
            'setel-express-shipments-details',
            [ $this, 'show_details_page' ]
        );

        add_action( 'admin_head', function () {
            remove_submenu_page( 'setel-express', 'setel-express-shipments-details' );
        } );
    }

    public function add_edit_menu() {
        add_submenu_page(
            'setel-express',
            __( 'Edit shipment', 'setel-express' ),
            __( 'Edit shipment', 'setel-express' ),
            'manage_woocommerce',
            'setel-express-shipments-edit',
            [ $this, 'show_edit_page' ]
        );

        add_action( 'admin_head', function () {
            remove_submenu_page( 'setel-express', 'setel-express-shipments-edit' );
        } );
    }

    public function print_admin_notices() {
        foreach ( setel_express_get_admin_notices() as $notice ) {
            include __DIR__ . '/partials/admin-notice.php';
        }

        setel_express_clear_admin_notices();
    }

    public function reset_views(
        array $views
    ): array {
        return [];
    }

    public function reset_bulk_actions(
        array $actions
    ): array {
        return [];
    }

    public function add_change_pickup_option_bulk_actions(
        array $actions
    ): array {
        $actions['change_pickup_option_pickup']       = __( 'Change pickup option to Pickup', 'setel-express' );
        $actions['change_pickup_option_drop_off']     = __( 'Change pickup option to Drop off', 'setel-express' );
        $actions['change_pickup_option_decide_later'] = __( 'Change pickup option to Decide later', 'setel-express' );

        return $actions;
    }

    public function add_extra_print_shipping_label_bulk_action(
        string $which
    ) {
        global $pagenow, $typenow;

        if ( $pagenow !== 'edit.php' ) {
            return;
        }

        if ( $typenow !== 'setel_shipment' ) {
            return;
        }

        if ( $which !== 'top' ) {
            return;
        }

        ?>
        <div class="alignleft actions">
            <button type="submit" class="button" name="action" value="print_shipping_label">
                <?php echo esc_html__( 'Print shipping label', 'setel-express' ) ?>
            </button>
        </div>
        <?php
    }

    public function add_extra_cancel_bulk_action(
        string $which
    ) {
        global $pagenow, $typenow;

        if ( $pagenow !== 'edit.php' ) {
            return;
        }

        if ( $typenow !== 'setel_shipment' ) {
            return;
        }

        if ( $which !== 'top' ) {
            return;
        }
        ?>
        <div class="alignleft actions">
            <button type="submit" class="button" name="action" value="cancel">
                <?php echo esc_html__( 'Cancel shipment', 'setel-express' ) ?>
            </button>
        </div>
        <?php
    }

    public function handle_change_pickup_option_bulk_action(
        string $redirect_url,
        string $action,
        array $shipment_ids
    ) {
        $initial_pickup_type_maps = [
            'change_pickup_option_pickup'       => Setel_Express_Shipment::PICKUP_TYPE_PICKUP,
            'change_pickup_option_drop_off'     => Setel_Express_Shipment::PICKUP_TYPE_DROP_OFF,
            'change_pickup_option_decide_later' => Setel_Express_Shipment::PICKUP_TYPE_DECIDE_LATER,
        ];

        if ( ! array_key_exists( $action, $initial_pickup_type_maps ) ) {
            return $redirect_url;
        }

        $shipment_posts = $this->shipment->update_all( $shipment_ids, [
            'initial_pickup_type' => $initial_pickup_type_maps[ $action ],
        ] );

        if ( $shipment_posts ) {
            $notice_message = __( 'Shipments\' pick option changed.', 'setel-express' );
            $notice_type    = 'success';
        } else {
            $notice_message = __( 'No shipments\' pickup option changed. You can only edit pickup option for shipment status with: Created, Pending pickup, Pending drop off.',
                'setel-express' );
            $notice_type    = 'warning';
        }

        setel_express_add_admin_notice( $notice_message, $notice_type );

        return $redirect_url;
    }

    public function handle_print_shipping_label_bulk_action(
        string $redirect_url,
        string $action,
        array $shipment_ids
    ) {
        if ( $action !== 'print_shipping_label' ) {
            return $redirect_url;
        }

        wp_safe_redirect( add_query_arg( [
            'shipments' => $shipment_ids,
        ], admin_url( 'admin-post.php?action=setel_express_print_shipping_label' ) ) );
        exit;
    }

    public function handle_cancel_bulk_action(
        string $redirect_url,
        string $action,
        array $shipment_ids
    ): string {
        if ( $action !== 'cancel' ) {
            return $redirect_url;
        }

        $shipment_posts = $this->shipment->cancel( $shipment_ids );

        if ( $shipment_posts ) {
            $notice_message = __( 'Shipments cancelled.', 'setel-express' );
            $notice_type    = 'success';
        } else {
            $notice_message = __( 'No shipments cancelled. You can only cancel shipments for shipment status with: Created, Pending pickup, Pending drop off.',
                'setel-express' );
            $notice_type    = 'warning';
        }

        setel_express_add_admin_notice( $notice_message, $notice_type );

        return $redirect_url;
    }

    public function disable_months_dropdown(
        bool $is_disable,
        string $post_type
    ): bool {
        if ( $post_type !== 'setel_shipment' ) {
            return $is_disable;
        }

        return true;
    }

    public function add_shipment_status_filter(
        string $post_type,
        string $which
    ) {
        if ( $post_type !== 'setel_shipment' ) {
            return;
        }

        if ( $which !== 'top' ) {
            return;
        }

        $selected_shipment_status = wc_clean( $_GET['shipment_status'] ?? '' );
        ?>
        <select name="shipment_status" id="filter-by-shipment_status">
            <option><?php echo esc_html__( 'All statuses', 'setel-express' ) ?></option>
            <?php foreach ( $this->api_data->shipment_statuses() as $shipment_status ): ?>
                <option
                    <?php echo selected( $selected_shipment_status, $shipment_status['status'], false ); ?>
                    value="<?php echo esc_attr( $shipment_status['status'] ) ?>"
                >
                    <?php echo esc_html( $shipment_status['name'], 'setel-express' ) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function add_shipment_status_request_query(
        array $query_vars
    ): array {
        global $typenow;

        if ( $typenow !== 'setel_shipment' ) {
            return $query_vars;
        }

        $shipment_status = wc_clean( $_GET['shipment_status'] ?? '' );
        if ( ! $shipment_status ) {
            return $query_vars;
        }

        $query_vars['meta_query'][] = [
            'key'   => 'status',
            'value' => $shipment_status,
        ];

        return $query_vars;
    }

    public function add_shipment_created_on_filter(
        string $post_type,
        string $which
    ) {
        if ( $post_type !== 'setel_shipment' ) {
            return;
        }

        if ( $which !== 'top' ) {
            return;
        }

        $shipment_created_on = (int) wc_clean( $_GET['shipment_created_on'] ?? 0 );
        ?>
        <select name="shipment_created_on" id="filter-by-shipment_created_on">
            <option <?php echo selected( $shipment_created_on, 0, false ); ?> value="0">
                <?php echo esc_html__( 'Any dates' ) ?>
            </option>
            <option <?php echo selected( $shipment_created_on, 1, false ); ?> value="1">
                <?php echo esc_html__( 'Today', 'setel-express' ) ?>
            </option>
            <option <?php echo selected( $shipment_created_on, 2, false ); ?> value="2">
                <?php echo esc_html__( 'Yesterday', 'setel-express' ) ?>
            </option>
            <option <?php echo selected( $shipment_created_on, 7, false ); ?> value="7">
                <?php echo esc_html__( 'Last 7 days', 'setel-express' ) ?>
            </option>
            <option <?php echo selected( $shipment_created_on, 30, false ); ?> value="30">
                <?php echo esc_html__( 'Last 30 days', 'setel-express' ) ?>
            </option>
        </select>
        <?php
    }

    public function add_shipment_created_on_request_query(
        array $query_vars
    ): array {
        global $typenow;

        if ( $typenow !== 'setel_shipment' ) {
            return $query_vars;
        }

        $date_range = (int) wc_clean( $_GET['shipment_created_on'] ?? 0 );
        if ( ! $date_range ) {
            return $query_vars;
        }

        switch ( $date_range ) {
            case 1:
                $date_query = [
                    'year'  => wp_date( 'Y' ),
                    'month' => wp_date( 'n' ),
                    'day'   => wp_date( 'j' ),
                ];
                break;

            case 2:
                $date_query = [
                    'year'  => wp_date( 'Y', strtotime( '-1 day' ) ),
                    'month' => wp_date( 'n', strtotime( '-1 day' ) ),
                    'day'   => wp_date( 'j', strtotime( '-1 day' ) ),
                ];
                break;

            case 7:
            case 30:
                $date_query = [
                    'after'     => [
                        'year'  => wp_date( 'Y', strtotime( "-{$date_range} days" ) ),
                        'month' => wp_date( 'n', strtotime( "-{$date_range} days" ) ),
                        'day'   => wp_date( 'j', strtotime( "-{$date_range} days" ) ),
                    ],
                    'inclusive' => false,
                ];
                break;
        }

        if ( isset( $date_query ) ) {
            $query_vars['date_query'] = $date_query;
        }

        return $query_vars;
    }

    public function set_listing_columns(
        array $columns
    ): array {
        return [
            'cb'          => $columns['cb'],
            'shipment_id' => __( 'Shipment ID', 'setel-express' ),
            'order'       => __( 'Order', 'setel-express' ),
            'status'      => __( 'Status', 'setel-express' ),
            'receiver'    => __( 'Receiver', 'setel-express' ),
            'created_on'  => __( 'Created On', 'setel-express' ),
        ];
    }

    public function render_listing_shipment_id_column(
        $column,
        $post_id
    ) {
        if ( $column !== 'shipment_id' ) {
            return;
        }

        $shipment_id = $this->shipment->get_tracking_number( $post_id );
        $view_link   = $this->shipment->get_view_url( $post_id );

        ?>
        <strong>
            <a class="row-title" href="<?php echo esc_url( $view_link ) ?>">
                <?php echo esc_html( $shipment_id ) ?>
            </a>
        </strong>
        <?php
    }

    public function render_listing_order_column(
        $column,
        $post_id
    ) {
        if ( $column !== 'order' ) {
            return;
        }

        $order = $this->shipment->get_order( $post_id );
        if ( $order ) {
            ?>
            <a href="<?php echo esc_url( $order->get_edit_order_url() ) ?>">
                <?php echo esc_html( '#' . $order->get_id() ) ?>
            </a>
            <?php
        } else {
            echo esc_html( '#' . $post_id );
        }
    }

    public function render_listing_status_column(
        $column,
        $post_id
    ) {
        if ( $column !== 'status' ) {
            return;
        }

        echo setel_express_value( $this->shipment->get_status_badge( $post_id ) );
    }

    public function render_listing_receiver_column(
        $column,
        $post_id
    ) {
        if ( $column !== 'receiver' ) {
            return;
        }

        echo esc_html( $this->shipment->get_receiver_name( $post_id ) );
    }

    public function render_listing_created_on_column(
        $column,
        $post_id
    ) {
        if ( $column !== 'created_on' ) {
            return;
        }

        $shipment_post = $this->shipment->get( $post_id );

        echo esc_html( wp_date( 'j M Y, g:i A', strtotime( $shipment_post->post_date_gmt ) ) );
    }

    public function set_row_actions(
        $actions,
        WP_Post $shipment_post
    ) {
        if ( ! setel_express_is_shipment_post( $shipment_post ) ) {
            return $actions;
        }

        return [];
    }

    public function show_create_page() {
        $orders = array_map( function ( $order_id ) {
            return wc_get_order( $order_id );
        }, wc_clean( $_GET['orders'] ) );

        $shippable_orders = array_values( array_filter( $orders, function ( WC_Order $order ) {
            return $order->has_shipping_address();
        } ) );

        if ( ! $shippable_orders ) {
            setel_express_add_admin_notice( __( 'Shipment cannot be created. You can only create shipment for order that has shipping address.' ), 'error' );
            wp_safe_redirect( wp_get_referer() );
            exit;
        }

        include_once dirname( __FILE__ ) . '/partials/shipments-create.php';
    }

    public function create() {
        check_admin_referer( 'setel-express-shipments-create-form', '_nonce' );

        try {
            /**
             * @var \WP_Post[] $shipment_posts
             */
            $shipment_posts = $this->shipment->create( $this->normalize_create_inputs( wc_clean( $_POST ) ) );
        } catch ( Exception $exception ) {
            var_dump( __METHOD__, $exception );
            wp_die();
        }

        if ( count( $shipment_posts ) > 1 ) {
            wp_safe_redirect( admin_url( 'edit.php?post_type=setel_shipment' ) );
            exit;
        }

        $shipment_post = reset( $shipment_posts );

        wp_safe_redirect( $this->shipment->get_view_url( $shipment_post ) );
        exit;
    }

    protected function normalize_create_inputs(
        array $input
    ): array {
        $shipment_inputs = $input['shipments'] ?? [];

        return array_map( function ( $shipment_input ) use ( $input ) {
            return [
                'order_id'              => sanitize_text_field( $shipment_input['order_id'] ),
                'initial_pickup_type'   => sanitize_text_field( $input['initial_pickup_type'] ),
                'sender_name'           => sanitize_text_field( $input['sender_name'] ),
                'sender_phone_number'   => sanitize_text_field( $input['sender_phone_number'] ),
                'sender_address1'       => sanitize_text_field( $input['sender_address1'] ),
                'sender_address2'       => sanitize_text_field( $input['sender_address2'] ?? '' ),
                'sender_postcode'       => sanitize_text_field( $input['sender_postcode'] ),
                'sender_city'           => sanitize_text_field( $input['sender_city'] ),
                'sender_state'          => sanitize_text_field( $input['sender_state'] ),
                'receiver_name'         => sanitize_text_field( $shipment_input['receiver_name'] ),
                'receiver_phone_number' => sanitize_text_field( $shipment_input['receiver_phone_number'] ),
                'receiver_address1'     => sanitize_text_field( $shipment_input['receiver_address1'] ),
                'receiver_address2'     => sanitize_text_field( $shipment_input['receiver_address2'] ?? '' ),
                'receiver_postcode'     => sanitize_text_field( $shipment_input['receiver_postcode'] ),
                'receiver_city'         => sanitize_text_field( $shipment_input['receiver_city'] ),
                'receiver_state'        => sanitize_text_field( $shipment_input['receiver_state'] ),
                'no_of_parcels'         => sanitize_text_field( $shipment_input['no_of_parcels'] ),
                'parcel_weight'         => sanitize_text_field( $shipment_input['parcel_weight'] ),
                'parcel_size'           => sanitize_text_field( $shipment_input['parcel_size'] ),
                'instructions'          => sanitize_textarea_field( $shipment_input['instructions'] ),
            ];
        }, $shipment_inputs );
    }

    public function show_details_page() {
        include_once dirname( __FILE__ ) . '/partials/shipments-details.php';
    }

    public function set_details_title(
        $admin_title,
        $title
    ) {
        global $current_screen;

        if ( $current_screen->base !== 'setel-express_page_setel-express-shipments-details' ) {
            return $admin_title;
        }

        $shipment_post = $this->shipment->get( wc_clean( $_GET['shipment'] ) );

        $screen_title = sprintf( __( '%1$s &#8220;%2$s&#8221;' ), $title, $this->shipment->get_tracking_number( $shipment_post ) );

        if ( is_network_admin() ) {
            $admin_title = sprintf( __( 'Network Admin: %s' ), get_network()->site_name );
        } elseif ( is_user_admin() ) {
            $admin_title = sprintf( __( 'User Dashboard: %s' ), get_network()->site_name );
        } else {
            $admin_title = get_bloginfo( 'name' );
        }

        return sprintf( __( '%1$s &lsaquo; %2$s &#8212; WordPress' ), $screen_title, $admin_title );
    }

    public function show_edit_page() {
        $shipment_post = $this->shipment->get( wc_clean( $_GET['shipment'] ) );
        if ( ! $this->shipment->is_editable( $shipment_post ) ) {
            setel_express_add_admin_notice( __( 'Shipment cannot be edited. You can only edit shipment for shipment status with: Created, Pending pickup, Pending drop off.' ),
                'error' );
            wp_safe_redirect( $this->shipment->get_view_url( $shipment_post ) );
            exit;
        }

        include_once dirname( __FILE__ ) . '/partials/shipments-edit.php';
    }

    public function edit() {
        check_admin_referer( 'setel-express-shipments-edit-form', '_nonce' );

        $shipment_post = $this->shipment->get( wc_clean( $_GET['shipment'] ) );
        if ( ! $this->shipment->is_editable( $shipment_post ) ) {
            setel_express_add_admin_notice( __( 'Shipment cannot be edited. You can only edit shipment for shipment status with: Created, Pending pickup, Pending drop off.' ),
                'error' );
            wp_safe_redirect( $this->shipment->get_view_url( $shipment_post ) );
            exit;
        }

        try {
            $this->shipment->update( $shipment_post, $this->normalize_edit_input( wc_clean( $_POST ) ) );
        } catch ( Exception $exception ) {
            var_dump( __METHOD__, $exception );
            wp_die();
        }

        setel_express_add_admin_notice( __( 'Shipment is updated. Please print the shipping label again.' ), 'success' );
        wp_safe_redirect( $this->shipment->get_view_url( $shipment_post ) );
        exit;
    }

    protected function normalize_edit_input(
        array $input
    ): array {
        return [
            'order_id'              => sanitize_text_field( $input['order_id'] ),
            'initial_pickup_type'   => sanitize_text_field( $input['initial_pickup_type'] ),
            'sender_name'           => sanitize_text_field( $input['sender_name'] ),
            'sender_phone_number'   => sanitize_text_field( $input['sender_phone_number'] ),
            'sender_address1'       => sanitize_text_field( $input['sender_address1'] ),
            'sender_address2'       => sanitize_text_field( $input['sender_address2'] ?? '' ),
            'sender_postcode'       => sanitize_text_field( $input['sender_postcode'] ),
            'sender_city'           => sanitize_text_field( $input['sender_city'] ),
            'sender_state'          => sanitize_text_field( $input['sender_state'] ),
            'receiver_name'         => sanitize_text_field( $input['receiver_name'] ),
            'receiver_phone_number' => sanitize_text_field( $input['receiver_phone_number'] ),
            'receiver_address1'     => sanitize_text_field( $input['receiver_address1'] ),
            'receiver_address2'     => sanitize_text_field( $input['receiver_address2'] ?? '' ),
            'receiver_postcode'     => sanitize_text_field( $input['receiver_postcode'] ),
            'receiver_city'         => sanitize_text_field( $input['receiver_city'] ),
            'receiver_state'        => sanitize_text_field( $input['receiver_state'] ),
            'no_of_parcels'         => sanitize_text_field( $input['no_of_parcels'] ),
            'parcel_weight'         => sanitize_text_field( $input['parcel_weight'] ),
            'parcel_size'           => sanitize_text_field( $input['parcel_size'] ),
            'instructions'          => sanitize_textarea_field( $input['instructions'] ),
        ];
    }

    public function add_tracking_column(
        array $columns
    ): array {
        $new_columns = [];

        foreach ( $columns as $column_name => $column_info ) {
            $new_columns[ $column_name ] = $column_info;

            if ( 'order_total' === $column_name ) {
                $new_columns['setel_express_tracking'] = __( 'Setel Express Tracking', 'setel-express' );
            }
        }

        return $new_columns;
    }

    public function render_tracking_column(
        $column,
        $post_id
    ) {
        if ( $column !== 'setel_express_tracking' ) {
            return;
        }

        $shipment_post = $this->shipment->get_by_order( $post_id );
        if ( ! $shipment_post ) {
            return;
        }

        $tracking_number = $this->shipment->get_tracking_number( $shipment_post );
        $status_badge    = $this->shipment->get_status_badge( $shipment_post );
        $view_link       = $this->shipment->get_view_url( $shipment_post );
        ?>
        <a href="<?php echo esc_url( $view_link ) ?>">
            #<?php echo esc_html( $tracking_number ) ?>
        </a>
        <?php echo setel_express_value( $status_badge ) ?>
        <?php
    }

    public function add_tracking_meta_box() {
        global $post;

        $order = wc_get_order( $post );
        if ( ! $order ) {
            return;
        }

        $shipment_post = $this->shipment->get_by_order( $order );

        if ( $shipment_post ) {
            add_meta_box(
                'setel-express-tracking-meta-box',
                __( 'Setel Express Tracking', 'setel-express' ),
                [ $this, 'render_tracking_meta_box' ],
                'shop_order',
                'side',
            );
        }
    }

    public function render_tracking_meta_box() {
        include_once dirname( __FILE__ ) . '/partials/tracking-meta-box.php';
    }

    public function cancel() {
        $shipment_post = $this->shipment->get( wc_clean( $_REQUEST['shipment'] ) );
        if ( $this->shipment->is_cancellable( $shipment_post ) ) {
            $this->shipment->cancel( [ $shipment_post ] );

            $notice_message = __( 'Shipment cancelled.', 'setel-express' );
            $notice_type    = 'success';
        } else {
            $notice_message = __( 'Shipment cannot be cancelled. You can only cancel shipment for shipment status with: Created, Pending pickup, Pending drop off.',
                'setel-express' );
            $notice_type    = 'error';
        }

        setel_express_add_admin_notice( $notice_message, $notice_type );

        wp_safe_redirect( $this->shipment->get_view_url( $shipment_post ) );
        exit;
    }

    public function handle_update_shipment_webhook() {
        $input = json_decode( file_get_contents( 'php://input' ), true );

        setel_express_webhook_log( print_r( [
            'webhook' => 'update_shipment',
            'request' => $input,
        ], true ) );

        $webhook_shipment = $input['data']['object'];

        $shipment_post = $this->shipment->get_by_api_id( $webhook_shipment['id'] );
        if ( ! $shipment_post ) {
            return;
        }

        $this->shipment->sync_from_webhook( $shipment_post, $webhook_shipment );
    }
}