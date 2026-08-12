<?php
/**
 * Form markup
 *
 * @author  : Csqita
 * @license : GPLv3
 * */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'csqita' ) );
}
?>
<!-- generate markup using @wordpress/element -->
<div id="csqita-wp-plugin-root" class="csqita-page"></div>