<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'G2Inf_License_Handler' ) ) :

class G2Inf_License_Handler {
    private $file;
    private $license;
    private $item_name;
    private $item_id;
    private $item_shortname;
    private $version;
    private $author;
    private $api_url = 'http://beta.gravity2pdf.com/';

    public function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null, $_item_id = null ) {
        $this->file = $_file;
        $this->item_name = $_item_name;

        if ( is_numeric( $_item_id ) ) {
            $this->item_id = absint( $_item_id );
        }

        $this->item_shortname = 'ninja2pdf_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
        $this->version        = $_version;
        $this->license        = trim( $this->item_shortname . '_license_key');
        $this->author         = $_author;
        $this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

        // Setup hooks
        $this->includes();
        $this->hooks();
    }

    private function includes() {
        if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) )  {
            require_once(G2INF_PATH_CLASS . '/EDD_SL_Plugin_Updater.php');
        }
    }

    private function hooks() {
        // Register settings
        add_filter( 'ninja2pdf_settings_licenses', array( $this, 'settings' ), 1 );

        // Activate license key on settings save
        add_action( 'admin_init', array( $this, 'activate_license' ) );

        // Deactivate license key
        add_action( 'admin_init', array( $this, 'deactivate_license' ) );

        // Check that license is valid once per week
        add_action( 'g2inf_weekly_scheduled_events', array( $this, 'weekly_license_check' ) );

        // Updater
        add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );

        // Display notices to admins
        add_action( 'admin_notices', array( $this, 'notices' ) );
    }

    public function settings( $settings ) {
        $edd_license_settings = array(
            array(
                'id'      => $this->item_shortname . '_license_key',
                'name'    => sprintf( __( '%1$s', 'ninja-pdf' ), $this->item_name ),
                'desc'    => '',
                'type'    => 'license_key',
                'options' => array( 'is_valid_license_option' => $this->item_shortname . '_license_active' ),
                'size'    => 'regular'
            )
        );

        return array_merge( $settings, $edd_license_settings );
    }

    public function activate_license() {

        if ( ! isset( $_POST['ninja2pdf_settings'] ) ) {
            return;
        }

        if ( ! isset( $_REQUEST[ $this->item_shortname . '_license_key-nonce'] ) || ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {

            return;

        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( empty( $_POST['ninja2pdf_settings'][ $this->item_shortname . '_license_key'] ) ) {

            delete_option( $this->item_shortname . '_license_active' );

            return;

        }

        foreach ( $_POST as $key => $value ) {
            if( false !== strpos( $key, 'license_key_deactivate' ) ) {
                // Don't activate a key when deactivating a different key
                return;
            }
        }

        $details = get_option( $this->item_shortname . '_license_active' );

        if ( is_object( $details ) && 'valid' === $details->license ) {
            return;
        }

        $license = sanitize_text_field( $_POST['ninja2pdf_settings'][ $this->item_shortname . '_license_key'] );

        if( empty( $license ) ) {
            return;
        }

        // Data to send to the API
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => $license,
            'item_name'  => urlencode( $this->item_name ),
            'url'        => home_url()
        );

        // Call the API
        $response = wp_remote_post(
            $this->api_url,
            array(
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $api_params
            )
        );

        // Make sure there are no errors
        if ( is_wp_error( $response ) ) {
            return;
        }

        // Tell WordPress to look for updates
        set_site_transient( 'update_plugins', null );

        // Decode license data
        $license_data = json_decode( wp_remote_retrieve_body( $response ) );

        update_option( $this->item_shortname . '_license_active', $license_data );
    }

    public function deactivate_license() {

        if ( ! isset( $_POST['ninja2pdf_settings'] ) )
            return;

        if ( ! isset( $_POST['ninja2pdf_settings'][ $this->item_shortname . '_license_key'] ) )
            return;

        if( ! wp_verify_nonce( $_REQUEST[ $this->item_shortname . '_license_key-nonce'], $this->item_shortname . '_license_key-nonce' ) ) {

            wp_die( __( 'Nonce verification failed', 'ninja-pdf' ), __( 'Error', 'ninja-pdf' ), array( 'response' => 403 ) );

        }

        if( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Run on deactivate button press
        if ( isset( $_POST[ $this->item_shortname . '_license_key_deactivate'] ) ) {

            // Data to send to the API
            $api_params = array(
                'edd_action' => 'deactivate_license',
                'license'    => $this->license,
                'item_name'  => urlencode( $this->item_name ),
                'url'        => home_url()
            );

            // Call the API
            $response = wp_remote_post(
                $this->api_url,
                array(
                    'timeout'   => 15,
                    'sslverify' => false,
                    'body'      => $api_params
                )
            );

            // Make sure there are no errors
            if ( is_wp_error( $response ) ) {
                return;
            }

            // Decode the license data
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            delete_option( $this->item_shortname . '_license_active' );

        }
    }

    public function weekly_license_check() {

        if( ! empty( $_POST['ninja2pdf_settings'] ) ) {
            return; // Don't fire when saving settings
        }

        if( empty( $this->license ) ) {
            return;
        }

        // data to send in our API request
        $api_params = array(
            'edd_action'=> 'check_license',
            'license'   => $this->license,
            'item_name' => urlencode( $this->item_name ),
            'url'       => home_url()
        );

        // Call the API
        $response = wp_remote_post(
            $this->api_url,
            array(
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $api_params
            )
        );

        // make sure the response came back okay
        if ( is_wp_error( $response ) ) {
            return false;
        }

        $license_data = json_decode( wp_remote_retrieve_body( $response ) );

        update_option( $this->item_shortname . '_license_active', $license_data );

    }

    public function auto_updater() {
        $args = array(
            'version'   => $this->version,
            'license'   => $this->license,
            'author'    => $this->author
        );

        if( ! empty( $this->item_id ) ) {
            $args['item_id']   = $this->item_id;
        } else {
            $args['item_name'] = $this->item_name;
        }

        // Setup the updater
        $edd_updater = new EDD_SL_Plugin_Updater(
            $this->api_url,
            $this->file,
            $args
        );
    }

    public function notices() {

        static $showed_invalid_message;

        if( empty( $this->license ) ) {
            return;
        }

        if( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $messages = array();

        $license = get_option( $this->item_shortname . '_license_active' );

        if( is_object( $license ) && 'valid' !== $license->license && empty( $showed_invalid_message ) ) {

            if( empty( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {

                $messages[] = sprintf(
                    __( 'You have invalid or expired license keys for Ninja Forms to PDF. Please go to the <a href="%s">Licenses page</a> to correct this issue.', 'easy-digital-downloads' ),
                    admin_url( 'edit.php?post_type=ninja_merge&page=ninja2pdf-settings&tab=licenses' )
                );

                $showed_invalid_message = true;

            }

        }

        if( ! empty( $messages ) ) {

            foreach( $messages as $message ) {

                echo '<div class="error">';
                    echo '<p>' . $message . '</p>';
                echo '</div>';

            }

        }

    }
}

endif;