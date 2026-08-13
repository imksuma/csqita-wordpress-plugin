<?php 
/**
 * Csqita internal user APIs
 *
 * @author  : Csqita
 * @license : GPLv3
 * */

namespace Csqita\App;
/**
 * @since 1.0.0
 * Create an internal API group for users by extending Api class 
 */ 
class User extends Api
{
    use Singleton;
    
    public function config() {
        // this prefix will used in api endpoint - example: /csqita/v1/user
        $this->prefix = 'user';
    }

    /**
     * @method POST
     * @api /csqita/v1/user/save 
     * 
     * Save user current user data. Initiall it receives user identifier and token
     */ 
    public function post_save() {

        $params          = $this->request->get_params();
        $user_identifier = sanitize_text_field( isset( $params['user_identifier'] ) ? $params['user_identifier'] : '' );
        $token           = sanitize_text_field( isset( $params['token'] ) ? $params['token'] : '' );
        $name            = sanitize_text_field( isset( $params['name'] ) ? $params['name'] : '' );
        $nonce           = sanitize_text_field( isset( $params['nonce'] ) ? $params['nonce'] : '' );
        $widgetToken     = sanitize_text_field( isset( $params['widgetToken'] ) ? $params['widgetToken'] : '' );
        if (!wp_verify_nonce($nonce, 'csqita_plugin_nonce')) {
            return [
                'code'    => 401,
                'message' => 'error',
            ]; 
        }
        
        // clear the cache of the user is new
        if (function_exists('csqita_clear_all_caches')) {
            csqita_clear_all_caches();
        }

        // delete all data
        if ( ! empty( $widgetToken ) ) {
            // save user identifier and token to DB
            update_option( 'csqita_widget_token', $widgetToken );
            $success = true;
        }
        $success = false;
        if ( ! empty( $user_identifier ) ) {
            // save user identifier and token to DB
            update_option( 'csqita_user_identifier', $user_identifier );
            $success = true;
        }

        if ( ! empty( $name ) ) {
            // save user identifier and token to DB
            update_option( 'csqita_user_name', $name );
            $success = true;
        }

        if ( ! empty( $token ) ) {
            delete_option( 'csqita_has_auth_error' );
            update_option( 'csqita_token', $token );
            $success = true;
        }

        if ($success) {
            return [
                'code'    => 200,
                'message' => 'success',
            ];
        }
        return [
            'code'    => 401,
            'message' => 'error',
        ]; 
    }

    /**
     * @method GET
     * @api /csqita/v1/user/logout 
     * 
     * Remote everything related to the current user from DB
     */ 
    public function get_logout() {
        // ExternalApi::csqita_logout();
        User::clear_csqita_keys();
        if (function_exists('csqita_clear_all_caches')) {
            csqita_clear_all_caches();
        }
        return [
            'code'    => 200,
            'message' => 'success',
        ];
    }

    /**
     * Retrieves the unread messages count from an external API and caches it as a transient.
     *
     * @return array An associative array containing the count of unread messages ('count') and a status code ('code').
     */
    public function get_count() {
        delete_transient( 'csqita_unread_messages_count' );
        $count = ExternalApi::get_unread_messages_count();
        set_transient( 'csqita_unread_messages_count', $count, 5*60 );
        return ['count' => $count, 'code' => 200];
    }

    /**
     * Removes all Csqita-related options from the WordPress options table.
     *
     * @return void
     * Method does not return any value.
     */
    static function clear_csqita_keys() {
        delete_option( 'csqita_redirection' );
        delete_option( 'csqita_user_identifier' );
        delete_option( 'csqita_api_secret_license_key' );
        delete_option( 'csqita_token' );
        delete_option( 'csqita_wp_plugin_version' );
        delete_option( 'csqita_secret_key' );
        delete_option( 'csqita_has_auth_error' );
        delete_option( 'csqita_user_name' );
        delete_option( 'csqita_widget_token' );
    }
}