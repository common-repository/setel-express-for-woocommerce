<div class="notice notice-<?php echo esc_attr( $notice['type'] ) ?> setel-express-notice">
    <?php
    if ( $notice['title'] ): ?>
        <h4><?php echo esc_html( $notice['title'] ) ?></h4>
    <?php
    endif; ?>

    <p><?php echo esc_html( $notice['message'] ) ?></p>

    <?php
    if ( $notice['list'] ): ?>
        <ul>
            <?php
            foreach ( $notice['list'] as $item ): ?>
                <li><?php echo esc_html( $item ) ?></li>
            <?php
            endforeach; ?>
        </ul>
    <?php
    endif; ?>
</div>