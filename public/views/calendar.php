<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
// $atts is set by the shortcode handler in class-plugin.php.
$view    = esc_attr( $atts['view']    ?? 'dayGridMonth' );
$user_id = esc_attr( $atts['user_id'] ?? '' );
?>
<div class="coreops-calendar"
     data-view="<?php echo $view; ?>"
     data-user-id="<?php echo $user_id; ?>"></div>
