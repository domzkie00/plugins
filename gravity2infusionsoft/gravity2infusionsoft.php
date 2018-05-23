<?php
/**
 * Plugin Name: Gravity to Infusionsoft
 * Plugin URI:  https://www.gravity2pdf.com
 * Description: Update Infusionsoft contact data through gravity forms
 * Version:     1.0
 * Author:      Birch Tree Digital
 * Author URI:  https://github.com/raphcadiz
 * Text Domain: gravity-to-infusionsoft
 */

if (!class_exists('g2inf')):

    define( 'G2INF_PATH', dirname( __FILE__ ) );
    define( 'G2INF_PATH_INCLUDES', dirname( __FILE__ ) . '/includes' );
    define( 'G2INF_PATH_CLASS', dirname( __FILE__ ) . '/class' );
    define( 'G2INF_PATH_INTEGRATIONS', dirname( __FILE__ ) . '/integrations' );
    define( 'G2INF_FOLDER', basename( G2INF_PATH ) );
    define( 'G2INF_URL', plugins_url() . '/' . G2INF_FOLDER );
    define( 'G2INF_URL_INCLUDES', G2INF_URL . '/includes' );
    define( 'G2INF_URL_CLASS', G2INF_URL . '/class' );
    define( 'G2INF_URL_INTEGRATIONS', G2INF_URL . '/integrations' );
    define( 'G2INF_VERSION', 1.0 );

    register_activation_hook( __FILE__, 'g2inf_activation' );
    function g2inf_activation(){

        if ( ! class_exists('GFForms') ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die('Sorry, but this plugin requires the Gravity Forms to be installed and active.');
        }

        if ( ! wp_next_scheduled( 'g2inf_weekly_scheduled_events' ) ) {
            wp_schedule_event( current_time( 'timestamp', true ), 'weekly', 'g2inf_weekly_scheduled_events' );
        }

    }

    add_action( 'admin_init', 'g2inf_activate' );
    function g2inf_activate(){
        if ( ! class_exists('GFForms') ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
        }
    }

    function g2inf_registration_redirect( $plugin ) {
        if( $plugin == plugin_basename( __FILE__ ) && !get_option('g2inf_plugin_first_time_activate')) {
            update_option( 'g2inf_plugin_first_time_activate', 1 );
            exit( wp_redirect( admin_url( 'admin.php?page=g2inf-settings&g2infregistration=true' ) ) );
        }
    }
    add_action( 'activated_plugin', 'g2inf_registration_redirect' );

    require_once('vendor/autoload.php');

    /*
     * include necessary files
     */
    require_once(G2INF_PATH_CLASS . '/g2inf-main.class.php');
    require_once(G2INF_PATH_CLASS . '/g2inf-post-type.class.php');
    require_once(G2INF_PATH_CLASS . '/g2inf-settings.class.php');
    require_once(G2INF_PATH_CLASS . '/g2inf-merges.class.php');
    require_once(G2INF_PATH_CLASS . '/g2inf-meta-boxes.class.php');
    require_once(G2INF_PATH_CLASS . '/g2inf-processing.class.php');
    require_once(G2INF_PATH_CLASS . '/g2inf-license-handler.class.php');
    require_once(G2INF_PATH_INCLUDES . '/functions.php');

    /* Intitialize licensing
     * for this plugin.
     */
    if( class_exists( 'G2Inf_License_Handler' ) ) {
        $g2inf = new G2Inf_License_Handler( __FILE__, 'Gravity to Infusionsoft', G2INF_VERSION, 'Birch Tree Digital');
    }

    /*
     * register default integrations
     */


    add_action( 'plugins_loaded', array( 'g2inf', 'get_instance' ) );
endif;

/** TO DO:
  * 1. company
  * 2. relationships
  * 3. email_addresses
  **/
