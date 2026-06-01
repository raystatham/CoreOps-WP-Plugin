<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="wrap">
  <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

  <form method="post" action="options.php">
    <?php
      settings_fields( 'coreops-booking' );
      do_settings_sections( 'coreops-booking' );
      submit_button();
    ?>
  </form>

  <hr />
  <h2><?php esc_html_e( 'Connection Test', 'coreops-booking' ); ?></h2>
  <p><?php esc_html_e( 'Verify the API URL and token are working.', 'coreops-booking' ); ?></p>
  <button type="button" id="coreops-test-connection" class="button button-secondary">
    <?php esc_html_e( 'Test Connection', 'coreops-booking' ); ?>
  </button>
  <span id="coreops-test-result" style="margin-left:12px;"></span>
</div>
