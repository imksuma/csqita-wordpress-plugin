<?php
/**
 * Csqita dashboard
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

<section class="csqita-dashboard">
</section>