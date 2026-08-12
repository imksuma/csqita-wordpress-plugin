<?php
/**
 * Csqita admin assets enqueue
 *
 * @author  : Csqita
 * @license : GPLv3
 * */

namespace Csqita\App;

use Csqita\App\ExternalApi;

class Assets {
    use Singleton;
    
    public function __construct() {
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_assets'] );
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_csqita'] );
    }

    /**
     * Enqueues the Csqita script if the user identifier option is not empty.
     *
     * @return void
     */
    public function enqueue_csqita() {
        $user_identifier = get_option( 'csqita_user_identifier', '' );
        if ( ! empty( $user_identifier ) ) :
            $dependencies = \Csqita::include_once( 'assets/js/app.asset.php' );
            wp_enqueue_script( "csqita-script", esc_url( Url::widget_script( $user_identifier ) ), [], $dependencies['version'] , true );
            $userId = is_user_logged_in() ? get_current_user_id(): '';
            $emailId = is_user_logged_in() ? sanitize_email( wp_get_current_user()->user_email ) : '';
            $siteUrl = get_site_url();
            $userName = '';
            if ( is_user_logged_in() ) {
                $current_user = wp_get_current_user();
                $userName = trim($current_user->user_firstname.' '.$current_user->user_lastname);
            }
            $token = '';
            if(!empty($userId) && !empty($emailId) && !empty($siteUrl)) {
                $secret_key = ExternalApi::get_csqita_secret_key();
                $data = [
                    'id'     => esc_attr($userId),
                    'email'  => esc_attr($emailId),
                ];
                $token = hash_hmac(
                    'sha256',
                    json_encode($data),
                    esc_attr($secret_key)
                );
            }
            $data = [
                'widgetId' => $user_identifier,
                'emailId'  => $emailId,
                'userId'  => $userId,
                'token' => $token,
                'userName' => $userName,
                'themeName' => wp_get_theme()->get('Name'),
            ];
            wp_localize_script( 'csqita-script', 'wpCsqitaSettings',  $data );
        endif;
    }

    public function enqueue_admin_assets() {
        /**
         * prepare dynamic dependencies 
         */
        if (!current_user_can('manage_options')) {
            return;
        }

        $file_path = \Csqita::require( 'assets/js/app.asset.php', true );
        if( file_exists( $file_path ) ) {
            $file = require $file_path;
            $version = $file['version'];
            $dependencies = $file['dependencies'];
            $dependencies[] = 'jquery';

            /**
             * enqueue admin assets 
             */ 
            wp_enqueue_style( 'csqita-fonts', \Csqita::url( 'assets/css/fonts.css' ), [], \Csqita::version(), false );
            wp_enqueue_script(
                'csqita-app', \Csqita::url( 'assets/js/app.js' ), $dependencies, $version, [
                    'in_footer' => true,
                    'strategy'  => 'defer'
                ] 
            );
            wp_enqueue_style( 'csqita-app', \Csqita::url( 'assets/css/app.css' ), [], $dependencies, false );

            wp_localize_script(
                'csqita-app', 'csqita', [
                    'images'           => \Csqita::url( 'assets/images/' ),
                    'dashboardUrl'     => Url::admin_url(),
                    'fullScreenUrl'    => Url::full_screen_url(),
                    'internalEndpoint' => Url::internal_api(),
                    'remoteEndpoint'   => Url::remote_api(),
                    'landingPage'      => Url::landing_page(),
                    "termsOfService"   => Url::terms_of_service(),
                    "privacyPolicy"    => Url::privacy_policy(),
                    'siteUrl'          => get_site_url(),
                    'nonce'            => wp_create_nonce('csqita_plugin_nonce')
                ] 
            );
        } 
    }
}
