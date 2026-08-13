<?php
/**
 * Csqita admin base
 *
 * @author  : csqita
 * @license : GPLv3
 * */

namespace Csqita\App;

class Base {
    use Singleton;
    
    public function __construct() {
        add_action( 'admin_init', [$this, 'plugin_redirect'] );
    }

    /**
     * Redirects to the Csqita admin page if the Csqita_redirection option is set and DOING_AJAX is not defined.
     * This function checks if the Csqita_redirection option is set to true. If it is, it deletes the option and redirects
     * to the admin.php?page=Csqita URL using wp_redirect function. It then exits the script execution.
     *
     * @return void
     */
    public function plugin_redirect() {
        if ( ! defined( "DOING_AJAX" ) && get_option( 'csqita_redirection', false ) ) {
            delete_option( 'csqita_redirection' );
            exit( wp_redirect( admin_url("admin.php?page=Csqita") ) );
        }
    }

    /**
     * Activates the plugin by setting up a temporary redirection key.
     * The user will be redirected to the Plugin Page on installation.
     * The temporary redirection key is removed as soon as it's called for the first time.
     *
     * @return void
     */
    public function activate() {
        /**
         * We want to take the user to the Plugin Page on installation.
         * Hence setting up a temporary redirection key.
         * It gets removed as soon as it's called for the first time.
         * Ussage at : plugin_redirect, and called with admin_init
         */

        if(function_exists('csqita_clear_all_caches'))   {
            csqita_clear_all_caches();
        }


        if ( ! defined( "DOING_AJAX" ) ) {
            add_option( 'csqita_redirection', true );
        }

    }


    public function deactivate() {
        if(function_exists('csqita_clear_all_caches'))   {
            csqita_clear_all_caches();
        }

        delete_option( 'csqita_redirection' );
    }
}
