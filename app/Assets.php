<?php
/**
 * Csqita admin assets enqueue
 *
 * @author  : Csqita
 * @license : GPLv3
 * */

namespace Csqita\App;

use Csqita\App\URL;

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
        $widgetid = get_option( 'csqita_widget_token', '' );
        if ( ! empty( $widgetid ) ) :
            $dependencies = \Csqita::include_once( 'assets/js/app.asset.php' );
            $qstring = URL::iframe_src(http_build_query(['id' => $widgetid]));
            echo '<div id="module-csqita" ></div>';
            wp_enqueue_script( "csqita-script", esc_url($qstring), [], $dependencies['version'] , true );
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
                    'internalEndpoint' => Url::internal_api(),
                    'remoteEndpoint'   => Url::remote_api(),
                    'landingPage'      => Url::landing_page(),
                    "termsOfService"   => Url::terms_of_service(),
                    "privacyPolicy"    => Url::privacy_policy(),
                    'siteUrl'          => get_site_url(),
                    'nonce'            => wp_create_nonce('csqita_plugin_nonce'),
                    'token'            => get_option( 'csqita_token', '' )
                ]
            );
        } 
    }
}
