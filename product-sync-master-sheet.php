<?php
/**
 * Plugin Name: Sync Master Sheet - Product Sync with Google Sheet for WooCommerce
 * Requires Plugins: woocommerce
 * Plugin URI: https://codeastrology.com/how-to-setup-product-sync-master-plugin/
 * Description: Help you to connect your WooCommerce website with Google Sheet as well as Manage your Stock easy from one menu with Advance Filter
 * Author: CodeAstrology Team
 * Author URI: https://profiles.wordpress.org/codersaiful/#content-plugins
 * Tags: stock sync with google sheet, google sheet sync, bulk edit product
 * 
 * Version: 1.0.7
 * Requires at least:    4.0.0
 * Requires PHP:         7.2
 * Tested up to:         6.7.0
 * WC requires at least: 5.0.0
 * WC tested up to: 	 9.3.3
 * 
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * @package PSSG_Sync_Sheet
 * 
 * Text Domain: product-sync-master-sheet
 * Domain Path: /languages/
 * 
 * 
 * Product Stock Sync with Google Sheet for WooCommerce is free software: 
 * you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * Product Stock Sync with Google Sheet for WooCommerce is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 */

 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

if( ! defined( 'PSSG_DEV_VERSION' ) ){
    define( "PSSG_DEV_VERSION", '1.0.7.1' );
}
if( ! defined( 'PSSG_PLUGIN_NAME' ) ){
    define( "PSSG_PLUGIN_NAME", __( 'Product Stock Sync with Google Sheet for WooCommerce', 'product-sync-master-sheet' ) );
}

if( ! defined( 'PSSG_BASE_URL' ) ){
    define( "PSSG_BASE_URL", plugins_url() . '/'. plugin_basename( dirname( __FILE__ ) ) . '/' );
}

if( ! defined( 'PSSG_BASE_FILE' ) ){
    define( "PSSG_BASE_FILE", plugin_basename( __FILE__ ) );
}
if( ! defined( 'PSSG_BASE_FOLDER_NAME' ) ){
    define( "PSSG_BASE_FOLDER_NAME", plugin_basename(__DIR__) );
}

if( ! defined( 'PSSG_ASSETS_URL' ) ){
    define( "PSSG_ASSETS_URL", PSSG_BASE_URL . 'assets/' );
}

if( ! defined( 'PSSG_DIR_BASE' ) ){
    define( "PSSG_DIR_BASE", dirname( __FILE__ ) . '/' );
}
if( ! defined( 'PSSG_BASE_DIR' ) ){
    define( "PSSG_BASE_DIR", str_replace( '\\', '/', PSSG_DIR_BASE ) );
}
if( ! defined( 'PSSG_PREFIX' ) ){
    define( "PSSG_PREFIX", 'pssg' );
}

class PSSG_Init
{
    public static $instance;
    public $textdomain_load = false;
    public static function instance()
    {
        if( is_null( self::$instance ) ){
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'init']);

        //Text domain to be load before init() method call.
        add_action('plugins_loaded', [$this, 'load_textdomain'], 0);
    }
    public function init()
    {

        $is_woocommerce = class_exists( 'WooCommerce' );

        if ( ! $is_woocommerce ) {
            add_action('admin_notices', [$this, 'missing_wc']);
            return;
        }
        

        // Declare compatibility with custom order tables for WooCommerce.
        add_action( 'before_woocommerce_init', function(){
            if ( class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil') ) {
                    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
                }
            }
        );
        include_once PSSG_BASE_DIR . 'autoloader.php';
        include_once PSSG_BASE_DIR . 'app/functions.php';

        do_action( 'pssg_init' );
        //Not only Admin/But also frontend handle also added over there, such: API - it's not for login user.
        $admin = new PSSG_Sync_Sheet\Admin\Admin_Loader();
        $admin->init();
        
        do_action( 'pssg_loaded' );
    }

    public function load_textdomain() {
        if( $this->textdomain_load ) return;
        load_plugin_textdomain( 'product-sync-master-sheet', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
        $this->textdomain_load = true;
    }

    /**
     * Displaying Error Notice for 
     * If WooCoommerce is not installed
     *
     * @since 1.0.0.20
     * @author Saiful Islam <codersaiful@gmail.com>
     * 
     * @return void
     */
    public function missing_wc()
    {

        
        $message = sprintf(
                esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'product-sync-master-sheet'),
                '<strong>' . PSSG_PLUGIN_NAME . '</strong>',
                '<strong><a href="' . esc_url('https://wordpress.org/plugins/woocommerce/') . '" target="_blank">' . esc_html__('WooCommerce', 'product-sync-master-sheet') . '</a></strong>'
        );

        ?>
        <div class="notice notice-error"><p><?php echo wp_kses_post( $message ) ?></p></div>
        <?php
    }
}

PSSG_Init::instance();

