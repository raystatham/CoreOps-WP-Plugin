<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Handles all HTTP calls from WordPress to the CoreOps-Base REST API.
 * The PAT is read from wp_options and attached as a Bearer token — it
 * never leaves the server.
 */
class CoreOps_Booking_API_Client {

    private string $base_url;
    private string $pat;

    public function __construct() {
        $this->base_url = rtrim( (string) get_option( 'coreops_booking_api_url', '' ), '/' );
        $this->pat      = (string) get_option( 'coreops_booking_pat', '' );
    }

    /** Fetch calendar events for a date range. */
    public function get_events( string $from, string $to, string $user_id = '' ): array|WP_Error {
        $params = array( 'from' => $from, 'to' => $to );
        if ( $user_id ) { $params['user_id'] = $user_id; }
        return $this->get( '/api/calendar-events', $params );
    }

    /** Check availability for a date range. */
    public function get_availability( string $from, string $to, string $user_id = '' ): array|WP_Error {
        $params = array( 'from' => $from, 'to' => $to );
        if ( $user_id ) { $params['user_id'] = $user_id; }
        return $this->get( '/api/availability', $params );
    }

    /** Submit a new booking. */
    public function post_booking( array $data ): array|WP_Error {
        return $this->post( '/api/bookings', $data );
    }

    /** Health check — used by the settings page connection test button. */
    public function health_check(): bool {
        $response = $this->get( '/api/health' );
        return ! is_wp_error( $response );
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function get( string $path, array $params = [] ): array|WP_Error {
        if ( empty( $this->base_url ) ) {
            return new WP_Error( 'no_url', __( 'CoreOps API URL is not configured.', 'coreops-booking' ) );
        }
        $url = $this->base_url . $path;
        if ( $params ) { $url = add_query_arg( $params, $url ); }
        return $this->parse_response( wp_remote_get( $url, $this->request_args() ) );
    }

    private function post( string $path, array $body ): array|WP_Error {
        if ( empty( $this->base_url ) ) {
            return new WP_Error( 'no_url', __( 'CoreOps API URL is not configured.', 'coreops-booking' ) );
        }
        $args = array_merge( $this->request_args(), array(
            'body'    => wp_json_encode( $body ),
            'headers' => array_merge( $this->auth_headers(), array( 'Content-Type' => 'application/json' ) ),
        ) );
        return $this->parse_response( wp_remote_post( $this->base_url . $path, $args ) );
    }

    private function request_args(): array {
        return array( 'headers' => $this->auth_headers(), 'timeout' => 15 );
    }

    private function auth_headers(): array {
        return array( 'Authorization' => 'Bearer ' . $this->pat );
    }

    private function parse_response( array|WP_Error $response ): array|WP_Error {
        if ( is_wp_error( $response ) ) { return $response; }
        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( $code < 200 || $code >= 300 ) {
            return new WP_Error( 'api_error', $body['error'] ?? __( 'CoreOps API error.', 'coreops-booking' ), array( 'status' => $code ) );
        }
        return is_array( $body ) ? $body : array();
    }
}
