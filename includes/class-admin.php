<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Admin settings page — Settings → CoreOps Booking.
 */
class CoreOps_Booking_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_menu(): void {
        add_options_page(
            __( 'CoreOps Booking', 'coreops-booking' ),
            __( 'CoreOps Booking', 'coreops-booking' ),
            'manage_options',
            'coreops-booking',
            array( $this, 'render_page' )
        );
    }

    public function register_settings(): void {
        // ── Connection ────────────────────────────────────────────────────────
        add_settings_section( 'coreops_connection', __( 'CoreOps-Base Connection', 'coreops-booking' ), '__return_false', 'coreops-booking' );

        register_setting( 'coreops-booking', 'coreops_booking_api_url', array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        ) );
        add_settings_field( 'coreops_booking_api_url', __( 'API Base URL', 'coreops-booking' ), array( $this, 'field_api_url' ), 'coreops-booking', 'coreops_connection' );

        register_setting( 'coreops-booking', 'coreops_booking_pat', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ) );
        add_settings_field( 'coreops_booking_pat', __( 'Personal Access Token', 'coreops-booking' ), array( $this, 'field_pat' ), 'coreops-booking', 'coreops_connection' );

        // ── Display ───────────────────────────────────────────────────────────
        add_settings_section( 'coreops_display', __( 'Display Options', 'coreops-booking' ), '__return_false', 'coreops-booking' );

        register_setting( 'coreops-booking', 'coreops_booking_default_view', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'dayGridMonth',
        ) );
        add_settings_field( 'coreops_booking_default_view', __( 'Default Calendar View', 'coreops-booking' ), array( $this, 'field_default_view' ), 'coreops-booking', 'coreops_display' );
    }

    // ── Field renderers ───────────────────────────────────────────────────────

    public function field_api_url(): void {
        $value = esc_attr( get_option( 'coreops_booking_api_url', '' ) );
        echo '<input type="url" name="coreops_booking_api_url" value="' . $value . '" class="regular-text" placeholder="https://your-coreops-instance.com" />';
        echo '<p class="description">' . esc_html__( 'Base URL of your CoreOps-Base instance (no trailing slash).', 'coreops-booking' ) . '</p>';
    }

    public function field_pat(): void {
        $value = esc_attr( get_option( 'coreops_booking_pat', '' ) );
        echo '<input type="password" name="coreops_booking_pat" value="' . $value . '" class="regular-text" autocomplete="off" />';
        echo '<p class="description">' . esc_html__( 'Personal Access Token from CoreOps-Base → Profile → API Tokens. Never exposed to the browser.', 'coreops-booking' ) . '</p>';
    }

    public function field_default_view(): void {
        $value = get_option( 'coreops_booking_default_view', 'dayGridMonth' );
        $views = array(
            'dayGridMonth' => __( 'Month grid', 'coreops-booking' ),
            'timeGridWeek' => __( 'Week (time grid)', 'coreops-booking' ),
            'listWeek'     => __( 'Agenda list', 'coreops-booking' ),
        );
        echo '<select name="coreops_booking_default_view">';
        foreach ( $views as $key => $label ) {
            printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $value, $key, false ), esc_html( $label ) );
        }
        echo '</select>';
    }

    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) { return; }
        include COREOPS_BOOKING_DIR . 'admin/views/settings.php';
    }
}
