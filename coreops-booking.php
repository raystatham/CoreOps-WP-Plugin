<?php
/**
 * Plugin Name:       CoreOps Booking
 * Plugin URI:        https://github.com/your-org/coreops-wp-plugin
 * Description:       Calendar display, availability search, and booking form powered by the CoreOps-Base REST API.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Your Name
 * Author URI:        https://your-site.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       coreops-booking
 * Domain Path:       /languages
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants.
define( 'COREOPS_BOOKING_VERSION', '1.0.0' );
define( 'COREOPS_BOOKING_FILE',    __FILE__ );
define( 'COREOPS_BOOKING_DIR',     plugin_dir_path( __FILE__ ) );
define( 'COREOPS_BOOKING_URL',     plugin_dir_url( __FILE__ ) );
define( 'COREOPS_BOOKING_SLUG',    'coreops-booking' );

// Autoload includes.
require_once COREOPS_BOOKING_DIR . 'includes/class-plugin.php';
require_once COREOPS_BOOKING_DIR . 'includes/class-admin.php';
require_once COREOPS_BOOKING_DIR . 'includes/class-api-client.php';
require_once COREOPS_BOOKING_DIR . 'includes/class-rest-api.php';

// Activation / deactivation hooks.
register_activation_hook( __FILE__,   array( 'CoreOps_Booking_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CoreOps_Booking_Plugin', 'deactivate' ) );

// Boot the plugin.
CoreOps_Booking_Plugin::get_instance();
