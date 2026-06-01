<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
$user_id = esc_attr( $atts['user_id'] ?? '' );
?>
<div class="coreops-availability" data-user-id="<?php echo $user_id; ?>">
  <form class="coreops-availability-form">
    <label><?php esc_html_e( 'From', 'coreops-booking' ); ?>
      <input type="date" name="from" required />
    </label>
    <label><?php esc_html_e( 'To', 'coreops-booking' ); ?>
      <input type="date" name="to" required />
    </label>
    <button type="submit"><?php esc_html_e( 'Check Availability', 'coreops-booking' ); ?></button>
  </form>
  <div class="coreops-availability-result" aria-live="polite"></div>
</div>
