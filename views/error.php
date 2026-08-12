<?php
use Chatway\App\Url;
/**
 * Csqita error
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
    <div class="server-down-error">
        <div>
            <h1>An Error Occurred!</h1>
            <p>Oops! We encountered an error, please try again in a few minutes</p>
            
            <div class="button-group">
                <a href="<?php echo esc_url( Url::admin_url() ) ?>">Refresh</a>
            </div>
        </div>
    </div>
</section>