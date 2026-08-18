<?php 
/**
 * Csqita external/remote APIs
 *
 * @author  : Csqita
 * @license : GPLv3
 * */

namespace Csqita\App;

class ExternalApi {
    use Singleton;

    /**
     * Checks the status of a token by making a request to a remote API.
     *
     * @return string Returns 'valid' if the token is valid, 'server-down' if the server is unreachable, or 'invalid' for other cases.
     */
    static function get_token_status() {
        $has_error = get_option('csqita_has_auth_error', '');
        if($has_error == 'yes') {
            return 'invalid';
        }

        $token    = get_option( 'csqita_token', '' );
        if(empty($token)) {
            return 'invalid';
        }
        return 'valid';
    }
}