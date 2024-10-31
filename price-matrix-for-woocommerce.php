<?php
/**
 * Plugin Name: Price Matrix for Woocommerce Lite
 * Plugin URI: https://azmarket.net/item/price-matrix-for-woocommerce
 * Description: Price Matrix For WooCommerce helps to show the price of variable products become easier and more intuitive under price list.
 * Version: 1.2.2
 * Author: AzMarket
 * Author URI: https://azmarket.net
 *
 * Text Domain: bh_pricematrix
 * Domain Path: /languages/
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! defined( 'WPPM_FILE' ) ) {
	define( 'WPPM_FILE', __FILE__ );
}

if ( ! defined( 'WPPM_PATH' ) ) {
	define( 'WPPM_PATH', plugin_dir_path( WPPM_FILE ) );
}

if ( ! defined( 'WPPM_BASENAME' ) ) {
	define( 'WPPM_BASENAME', plugin_basename( WPPM_FILE ) );
}

if ( ! defined( 'WPPM_URL' ) ) {
    define( 'WPPM_URL', plugin_dir_url( WPPM_FILE ) );
}

if ( ! defined( 'WPPM_PLUGIN' ) ) {
	define( 'WPPM_PLUGIN', plugin_basename( __FILE__ ) );
}


class WPPM_Price_Matrix {

    protected $prefix_class = 'NPC_';
    protected $prefix_file = 'class.npc-';
    static $plugin_id = 'bh_pricematrix';
    static $settings_page = 'bh-wppm';
	static $settings_other = true;

    /**
     * @var null
     *
     * @since 0.0.1
     */
    private static $instance = null;


    /**
    * Active plugin.
    *
    * Create the mandatory for the user in order to avoid
    * issues with people thinking the plugin isn't working.
    *
    * @since  1.0.0
    * @return void
    */
    static function wppm_activate() {


    }

    /**
    * Deactive plugin.
    *
    * Create the mandatory for the user in order to avoid
    * issues with people thinking the plugin isn't working.
    *
    * @since  1.0.0
    * @return void
    */
    static function wppm_deactivate(){

    }

    /**
     * Get instance.
     *
     * @since 0.0.1
     *
     * @return null|WPPM_Price_Matrix
     */
    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * WPPM_Price_Matrix constructor.
     *
     * @since 0.0.1
     */
    private function __construct() {

        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            add_action( 'admin_notices', array( __CLASS__, 'install_woocommerce_admin_notice') );
        }else{
            add_action( 'admin_notices', array( __CLASS__, 'upgrade_notice') );
			add_action( 'init', array( __CLASS__, 'load_textdomain') );
            if( ! class_exists('BH_Plugins') ) {
                require_once WPPM_PATH . 'inc/plugins.php';
            }
            
        	require_once( WPPM_PATH . 'inc/functions.php' );
        	require_once( WPPM_PATH . 'inc/class-singleton.php' );
            require_once( WPPM_PATH . 'admin/class-admin-init.php' );
            require_once( WPPM_PATH . 'admin/class-admin-list-table.php' );
            require_once( WPPM_PATH . 'admin/class-admin-ajax.php' );
            require_once( WPPM_PATH . 'admin/class-admin-pointers.php' );

        	if ( is_admin() ) {
				add_action( 'plugins_loaded', array( __CLASS__, 'wppm_admin_init'), 15 );
        	}else {
                require_once( WPPM_PATH . 'frontend/class-frontend-ajax.php' );
                require_once( WPPM_PATH . 'frontend/class-frontend-init.php' );
                
            }
        }
    }

    /**
     * Method Featured.
     *
     * @return  array
     */
    public static function install_woocommerce_admin_notice() {?>
        <div class="error">
            <p><?php _e( 'WooCommerce plugin is not activated. Please install and activate it to use for plugin <strong>Price Matrix for WooCommerce</strong>.', 'wppm-price-matrix' ); ?></p>
        </div>
        <?php    
    }
	
	public static function load_textdomain() {
		load_plugin_textdomain( self::$plugin_id, false, WPPM_PATH . 'languages' ); 
    }
    
    public static function upgrade_notice() {
        ?>
        <div id="message" class="updated woocommerce-message woocommerce-no-shipping-methods-notice" style="background: #efd9ea;">
            <p class="main" style="margin-bottom: 0;text-transform: uppercase;"><strong><?php _e( 'Recommended Plugin', self::$plugin_id ); ?></strong></p>
            <p style="margin-top: 0;">You're using <strong>Price Matrix for WooCommerce Lite</strong> only support two attributes. You can use plugin <strong>Price Matrix for WooCommerce PRO</strong> to unlimited products and attributes.<br />
            <a class="nbd-notice-action" href="http://bit.ly/2CzPObN" target="_blank">Click here to buy plugin <strong>Price Matrix for WooCommerce PRO</strong> </a></p>

        </div>

        <?php
    }

    public static function wppm_admin_init() {
		new WPPM_Admin();
	}

}

/**
* Load pro plugin for dokan
*
* @since 2.5.3
*
* @return void
**/
add_action( 'plugins_loaded', array( 'WPPM_Price_Matrix', 'instance' ) );

register_activation_hook( __FILE__, array( 'WPPM_Price_Matrix', 'wppm_activate' ) );
register_deactivation_hook( __FILE__, array( 'WPPM_Price_Matrix', 'wppm_deactivate' ) );