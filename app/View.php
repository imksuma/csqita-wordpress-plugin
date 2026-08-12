<?php
/**
 * Csqita view
 *
 * @author  : Csqita
 * @license : GPLv3
 * */

namespace Csqita\App;

class View {
    use Singleton;
    
    public function __construct() {
        add_action( 'admin_menu', [$this, 'dashboard_screen'] );
    }

    public function screen() {
        $status = ExternalApi::get_token_status();

        switch ( $status ) {
            case 'valid':
                \Csqita::include_once( 'views/dashboard.php' );
                break;
            case 'invalid': 
                \Csqita::include_once( 'views/auth.php' );
                break;
            case 'server-down':
                \Csqita::include_once( 'views/error.php' );
                break;
        }
    }

    /**
     * Adds a Support submenu page to the Csqita plugin in the WordPress admin menu.
     *
     * This method registers a submenu under the Csqita admin menu, providing access to
     * the Support section where users can find assistance or additional resources related
     * to the plugin.
     *
     * @return void
     */
    public function csqita_support_submenu() {
        add_submenu_page(
            'csqita',
            esc_html__( "Support", 'csqita' ),
            esc_html__( "Support", 'csqita' ),
            'manage_options',
            'csqita-need-help',
            [$this, 'screen']
        );
    }

    /**
     * Registers the dashboard and submenu pages for the Csqita plugin in the WordPress admin menu.
     *
     * This method adds the main Csqita dashboard menu and its associated submenus,
     * including Live Chat, Full-Screen View, and Logout options. Conditional logic
     * is applied to show specific submenus based on the user's authentication status.
     *
     * @return void
     */
    public function dashboard_screen() {
        add_menu_page(
            esc_html__( "Csqita Dashboard", 'csqita' ), 
            esc_html__( "csqita", 'csqita' ), 
            'manage_options', 
            'csqita', 
            [$this, 'screen'], 
        );
    }
}