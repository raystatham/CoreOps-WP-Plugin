<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
$user_id = esc_attr( $atts['user_id'] ?? '' );
?>
<div class="coreops-booking-form" data-user-id="<?php echo $user_id; ?>">
  <form class="coreops-booking" novalidate>

    <div class="coreops-field">
      <label for="coreops-name"><?php esc_html_e( 'Name', 'coreops-booking' ); ?> <span aria-hidden="true">*</span></label>
      <input type="text" id="coreops-name" name="name" required autocomplete="name" />
    </div>

    <div class="coreops-field">
      <label for="coreops-email"><?php esc_html_e( 'Email', 'coreops-booking' ); ?> <span aria-hidden="true">*</span></label>
      <input type="email" id="coreops-email" name="email" required autocomplete="email" />
    </div>

    <div class="coreops-field">
      <label for="coreops-start"><?php esc_html_e( 'Start', 'coreops-booking' ); ?> <span aria-hidden="true">*</span></label>
      <input type="datetime-local" id="coreops-start" name="start_at" required />
    </div>

    <div class="coreops-field">
      <label for="coreops-end"><?php esc_html_e( 'End', 'coreops-booking' ); ?></label>
      <input type="datetime-local" id="coreops-end" name="end_at" />
    </div>

    <div class="coreops-field">
      <label for="coreops-notes"><?php esc_html_e( 'Notes', 'coreops-booking' ); ?></label>
      <textarea id="coreops-notes" name="notes" rows="4"></textarea>
    </div>

    <!-- Honeypot — bots fill this; humans leave it empty -->
    <div style="display:none" aria-hidden="true">
      <input type="text" name="website" tabindex="-1" autocomplete="off" />
    </div>

    <button type="submit"><?php esc_html_e( 'Book Now', 'coreops-booking' ); ?></button>

  </form>
  <div class="coreops-booking-result" aria-live="polite"></div>
</div>
