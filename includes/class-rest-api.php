<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Registers WP REST proxy endpoints under /wp-json/coreops/v1/.
 * All requests are forwarded to CoreOps-Base with the stored PAT — the
 * browser only ever calls these WP endpoints, never CoreOps directly.
 */
class CoreOps_Booking_REST_API {

    private CoreOps_Booking_API_Client $client;

    public function __construct() {
        $this->client = new CoreOps_Booking_API_Client();
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes(): void {
        $ns = 'coreops/v1';

        // GET /wp-json/coreops/v1/events
        register_rest_route( $ns, '/events', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_events' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'from'    => array( 'required' => true,  'sanitize_callback' => 'sanitize_text_field' ),
                'to'      => array( 'required' => true,  'sanitize_callback' => 'sanitize_text_field' ),
                'user_id' => array( 'required' => false, 'sanitize_callback' => 'absint' ),
            ),
        ) );

        // GET /wp-json/coreops/v1/availability
        register_rest_route( $ns, '/availability', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_availability' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'from'    => array( 'required' => true,  'sanitize_callback' => 'sanitize_text_field' ),
                'to'      => array( 'required' => true,  'sanitize_callback' => 'sanitize_text_field' ),
                'user_id' => array( 'required' => false, 'sanitize_callback' => 'absint' ),
            ),
        ) );

        // POST /wp-json/coreops/v1/book
        register_rest_route( $ns, '/book', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'post_booking' ),
            'permission_callback' => array( $this, 'verify_nonce' ),
        ) );
    }

    // ── Callbacks ─────────────────────────────────────────────────────────────

    public function get_events( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $result = $this->client->get_events(
            $request->get_param( 'from' ),
            $request->get_param( 'to' ),
            (string) $request->get_param( 'user_id' )
        );
        return is_wp_error( $result ) ? $result : new WP_REST_Response( $result, 200 );
    }

    public function get_availability( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $result = $this->client->get_availability(
            $request->get_param( 'from' ),
            $request->get_param( 'to' ),
            (string) $request->get_param( 'user_id' )
        );
        return is_wp_error( $result ) ? $result : new WP_REST_Response( $result, 200 );
    }

    public function post_booking( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $data   = array(
            'name'     => sanitize_text_field( $request->get_param( 'name' ) ),
            'email'    => sanitize_email( $request->get_param( 'email' ) ),
            'start_at' => sanitize_text_field( $request->get_param( 'start_at' ) ),
            'end_at'   => sanitize_text_field( $request->get_param( 'end_at' ) ),
            'notes'    => sanitize_textarea_field( $request->get_param( 'notes' ) ),
        );
        $result = $this->client->post_booking( $data );
        return is_wp_error( $result ) ? $result : new WP_REST_Response( $result, 201 );
    }

    // Nonce check for the booking POST — prevents CSRF from non-WP callers.
    public function verify_nonce( WP_REST_Request $request ): bool {
        return (bool) wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' );
    }
}
