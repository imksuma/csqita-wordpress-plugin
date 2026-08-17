<?php 
/**
 * Csqita reusable urls
 *
 * @author  : Csqita
 * @license : GPLv3
 * */

namespace Csqita\App;

class Url {
    use Singleton;
    public static $environment = 'prod'; // dev | prod | local

    /**
     * Generates the full base URL for a given key and appends the endpoint to it.
     *
     * @param string $key The key representing a specific URL (e.g., 'api', 'app', or 'widget').
     * @param string $endpoint The endpoint to append to the base URL.
     * @return string The full URL constructed by combining the base URL and the provided endpoint.
     */
    private static function base_url($key, $endpoint ) {
        $urls = [];
        if( self::$environment === 'dev' ) {
            $urls = [
                'api' => 'http://localhost:8081',
                'widget' => 'http://localhost:8080'
            ];
        } else {
            $urls = [
                'api' => 'https://api-public.csqita.com',
                'widget' => 'https://api.csqita.com'
            ];
        }

        return $urls[$key] . $endpoint;
    }

    /**
     * Constructs the iframe source URL by appending a tokenized query parameter to the base app URL.
     *
     * @param string $token An optional token to be appended as a query parameter in the iframe source URL.
     * @return string The fully constructed iframe source URL.
     */
    public static function iframe_src($token = '' ) {
        return self::base_url( 'widget', '/public/noauth/widget?' . $token );
    }

    /**
     * Constructs the full API URL by appending the specified endpoint to the base API URL.
     *
     * @param string $endpoint The endpoint to append to the API base URL. Defaults to an empty string.
     * @return string The complete API URL.
     */
    public static function remote_api($endpoint = '' ) {
        return self::base_url( 'api', $endpoint );
    }

    /**
     * Builds and returns the complete internal API endpoint.
     *
     * @param string $endpoint Optional specific endpoint to append to the base API path.
     * @return string The fully constructed API endpoint path.
     */
    public static function internal_api($endpoint = '' ) {
        return 'csqita/v1' . $endpoint;
    }

    /**
     * Constructs and returns the complete site URL with the optional slug appended.
     *
     * @param string $slug Optional slug to append to the base site URL.
     * @return string The fully constructed site URL.
     */
    public static function site_url($slug = '' ) {
        return get_site_url() . '/' . $slug;
    }

    /**
     * Constructs and returns the complete admin URL.
     *
     * @param string $route Optional specific route to append to the base admin URL.
     * @return string The fully constructed admin URL with the specified route.
     */
    public static function admin_url($route = '' ) {
        return admin_url( 'admin.php?page=csqita' ) . $route;
    }

    /**
     * Generates and returns the URL of the landing page.
     *
     * @return string The URL of the landing page.
     */
    public static function landing_page() {
        return "https://csqita.com";
    }

    /**
     * Generates and returns the URL of the terms of service page.
     *
     * @return string The URL of the terms of service page.
     */
    public static function terms_of_service() {
        return "https://csqita.com/terms";
    }

    /**
     * Generates and returns the URL of the privacy policy page.
     *
     * @return string The URL of the privacy policy page.
     */
    public static function privacy_policy() {
        return "https://csqita.com/privacy";
    }

    /**
     * Generates and returns the URL of the contact us page.
     *
     * @return string The URL of the contact us page.
     */
    public static function contact_us() {
        return 'https://csqita.com';
    }
}