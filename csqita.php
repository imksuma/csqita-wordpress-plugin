<?php
/**
 * Plugin Name: CSQita
 * Description: Customer Service platform for live chat, call, and AI chatbot.
 * Version:     1.0.0
 * Author:      Ilham
 * Author URI:        https://csqita.com
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       csqita
 * Domain Path:       /languages
 */

class Csqita {
    function __construct() {
        add_action( 'plugins_loaded', [$this, 'boot'] );
    }

    /**
     * @source csqita.php
     * You need to change version from 4 different places. 
     * 1. csqita.php comment section 
     * 2. csqita.php version() method 
     * 3. Gruntfile.js version property
     * 4. readme.txt Stable tag
     */ 
    public static function version() {
        return '1.0.0';
    }

    /**
     * Retrieves the plugin's base name.
     *
     * @return string The base name of the plugin.
     */
    public static function plugin_base() {
        return plugin_basename(__FILE__);
    }

    public function boot() {
        $this->add_textdomain();
        new Csqita\App\Assets();
        new Csqita\App\View();
        new Csqita\App\User();
    }

    private function add_textdomain() {
        load_plugin_textdomain( 'csqita', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Get the path or require any file
     * @param string $file_or_dir takes the path based on the root dir
     * @param boolean $path_only (optional, default: false) if you want the path in return make the value true 
     */ 
    public static function require( $file_or_dir = '', $path_only = false ) { 
        if ( ! $path_only ) {
            require trailingslashit( plugin_dir_path( __FILE__ ) ) . $file_or_dir;
        } else {
            return trailingslashit( plugin_dir_path( __FILE__ ) ) . $file_or_dir;
        }
    }

    /**
     * Include once or include once and get the path
     * @param string $file takes the path based on the root dir
     * @param boolean $no_return (optional, default: false) if you want the path in return make the value true 
     */ 
    public static function include_once( $file = '', $no_return = false ) {
        if ( ! $no_return ) {
            return include_once( self::require( $file, true ) );
        } else {
            include_once( self::require( $file, true ) );
        }
    }

    /**
     * Get the url of any assets file like css, js, images, fonts etc.
     * @param string $file - Define the file path based on the plugin root directory 
     */ 
    public static function url( $file = '' ) {
        return esc_url( trailingslashit( plugins_url( '/', __FILE__ ) ) . $file );
    }
}

/**
 * Autoloader 
 */ 
require_once( 'autoloader.php' );
require_once( 'inc/clear-all-cache.php' );
$loader = new Csqita\AutoLoader();
$loader->register();
/**
 * register the namespace
 * 
 * @param {1} will take the namespace
 * @param {2} path of the folder 
 */ 
$loader->add_namespace( 'Csqita\App', Csqita::require( 'app', true ) );

/**
 * Register the activation and deactivation hook 
 */ 
$csqitaBase = new Csqita\App\Base();
register_activation_hook( __FILE__, [ $csqitaBase, 'activate' ] );
register_deactivation_hook( __FILE__, [ $csqitaBase, 'deactivate' ] );

// add_action('wp_footer', 'csqita_script');
// function csqita_script() {
//     echo '<script id=\'module-csqita\' async=\'true\' src=\'https://api.csqita.com/public/noauth/widget?id=A0ELTukEjG5yqvvNd3MUy1XpWUlV0zWg9xNS%2BEuvuQXIQhfRzJCNX0Ve%2F0HUYooaEqOh927ZJihfVtZQWFUfNX1rpsS1FOVSGN%2B1YqZFgdUC2ONUaZAh4sR0qkELXL%2Bm3nDQ44WAVvAIyitoIkZFgqoeH1rcsTmvBqFAnFzeVSvqbslAL%2BIoHcjcdE0mDT44Z36QCfnzwMFW\'></script>';
// }

/**
 * Initialize the plugin 
 */ 
new Csqita();
