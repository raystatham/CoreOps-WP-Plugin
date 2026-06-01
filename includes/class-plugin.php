<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Core plugin class — singleton that wires all hooks on init.
 */
class CoreOps_Booking_Plugin {

    private static ?self $instance = null;

    public static function get_instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init',               array( $this, 'load_textdomain' ) );
        add_action( 'init',               array( $this, 'register_blocks' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
        add_shortcode( 'coreops_calendar',     array( $this, 'shortcode_calendar' ) );
        add_shortcode( 'coreops_availability', array( $this, 'shortcode_availability' ) );
        add_shortcode( 'coreops_booking',      array( $this, 'shortcode_booking' ) );

        // Boot sub-components.
        new CoreOps_Booking_Admin();
        new CoreOps_Booking_REST_API();
    }

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'coreops-booking',
            false,
            dirname( plugin_basename( COREOPS_BOOKING_FILE ) ) . '/languages'
        );
    }

    public function register_blocks(): void {
        // Registers all blocks whose block.json lives under build/blocks/*.
        $blocks_dir = COREOPS_BOOKING_DIR . 'build/blocks';
        if ( ! is_dir( $blocks_dir ) ) { return; }
        foreach ( (array) glob( $blocks_dir . '/*/block.json' ) as $block_json ) {
            register_block_type( dirname( $block_json ) );
        }
    }

    public function enqueue_public_assets(): void {
        wp_enqueue_style(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css',
            array(),
            '6.1.11'
        );
        wp_enqueue_script(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js',
            array(),
            '6.1.11',
            true
        );
        wp_enqueue_script(
            'coreops-booking-public',
            COREOPS_BOOKING_URL . 'public/js/calendar.js',
            array( 'fullcalendar' ),
            COREOPS_BOOKING_VERSION,
            true
        );

        // Pass REST base and nonce to JS so the browser never sees the PAT.
        wp_localize_script( 'coreops-booking-public', 'coreopsBooking', array(
            'restUrl' => esc_url_raw( rest_url( 'coreops/v1/' ) ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        ) );
    }

    public function shortcode_calendar( array $atts ): string {
        $atts = shortcode_atts( array( 'view' => 'dayGridMonth', 'user_id' => '' ), $atts );
        ob_start();
        include COREOPS_BOOKING_DIR . 'public/views/calendar.php';
        return ob_get_clean();
    }

    public function shortcode_availability( array $atts ): string {
        $atts = shortcode_atts( array( 'user_id' => '' ), $atts );
        ob_start();
        include COREOPS_BOOKING_DIR . 'public/views/availability.php';
        return ob_get_clean();
    }

    public function shortcode_booking( array $atts ): string {
        $atts = shortcode_atts( array( 'user_id' => '' ), $atts );
        ob_start();
        include COREOPS_BOOKING_DIR . 'public/views/booking-form.php';
        return ob_get_clean();
    }

    public static function activate(): void {
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }
}
