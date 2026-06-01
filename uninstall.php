<?php
// Only run when WordPress triggers uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove all plugin options from wp_options.
delete_option( 'coreops_booking_api_url' );
delete_option( 'coreops_booking_pat' );
delete_option( 'coreops_booking_settings' );
