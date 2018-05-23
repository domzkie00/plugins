<?php if ( ! defined( 'ABSPATH' ) ) exit;

class G2inf_Settings {

    public function __construct() {
        add_action('admin_menu', array( $this, 'admin_menus'), 10 );
        add_action('admin_init', array( $this, 'register_settings' ));
        add_action('admin_init', array( $this, 'get_access_token' ));
        add_action('admin_init', array( $this, 'get_infusionsoft_tags' ));
        add_action('admin_init', array( $this, 'get_custom_fields' ));
    }

    public function register_settings() {
        register_setting( 'g2inf_settings', 'g2inf_settings', '' );
    }

    public function admin_menus(){
        add_submenu_page ( 'edit.php?post_type=birchtree_g2inf' , 'Settings' , 'Settings' , 'manage_options' , 'g2inf-settings' , array( $this , 'g2inf_settings_page' ));
    }

    public function g2inf_settings_page() {
        if (isset($_GET['g2infregistration']) && $_GET['g2infregistration'] == 'true') {
            include_once(G2INF_PATH_INCLUDES . '/g2inf_register_account.php');
        } else {
            $g2inf_settings = get_option('g2inf_settings');
            include_once(G2INF_PATH_INCLUDES . '/settings.php');
        }
    }

    public function get_access_token() {
        if (isset($_GET['g2infsettingsaction']) && $_GET['g2infsettingsaction'] == 'getaccesstoken') {
            $g2inf_settings = get_option('g2inf_settings');
            $client_id            = isset($g2inf_settings['client_id']) ? $g2inf_settings['client_id'] : '';
            $client_secret        = isset($g2inf_settings['client_secret']) ? $g2inf_settings['client_secret'] : '';
            $token                = isset($g2inf_settings['token']) ? $g2inf_settings['token'] : '';

            if (!$client_id || !$client_secret)
                return;

            $infusionsoft = new \Infusionsoft\Infusionsoft(array(
                'clientId'     => $client_id,
                'clientSecret' => $client_secret,
                'redirectUri'  => admin_url( 'admin.php?page=g2inf-settings&g2infsettingsaction=getaccesstoken' ),
            ));

            if (!isset($_GET['code'])) {
                // update_option( 'infusionsoft_request_token', 1 );
                $authorizationUrl = $infusionsoft->getAuthorizationUrl();
                header('Location: ' . $authorizationUrl);
            } else {
                update_option( 'infusionsoft_token_key', 0 );
                $g2inf_settings['token'] = serialize($infusionsoft->requestAccessToken($_GET['code']));
                $this->get_infusionsoft_tags();

                update_option( 'g2inf_settings', $g2inf_settings );
                header('Location: ' . admin_url( 'admin.php?page=g2inf-settings' ));
            }
        }
    }

    public function get_infusionsoft_tags() {
        if (isset($_GET['g2infsettingsaction']) && $_GET['g2infsettingsaction'] == 'syncdata'):
            $g2inf_settings = get_option('g2inf_settings');
            $client_id            = isset($g2inf_settings['client_id']) ? $g2inf_settings['client_id'] : '';
            $client_secret        = isset($g2inf_settings['client_secret']) ? $g2inf_settings['client_secret'] : '';
            $token                = isset($g2inf_settings['token']) ? $g2inf_settings['token'] : '';

            if( !empty($client_id) && !empty($client_secret) ){
                $infusionsoft = new \Infusionsoft\Infusionsoft(array(
                    'clientId'     => $client_id,
                    'clientSecret' => $client_secret,
                    'redirectUri'  => admin_url()
                ));

                $infusionsoft->setToken(unserialize($token));

                $tags = [];
                $page = 0;

                do {
                    $result = $infusionsoft
                        ->data
                        ->query('ContactGroup', 1000, $page, ['id' => '%'], ['id', 'GroupName', 'GroupCategoryId'], 'GroupName', true);

                    $tags = array_merge($tags, $result);

                } while (count($result) === 1000);

                $g2inf_settings['tags'] = serialize($tags);
                
                update_option( 'g2inf_settings', $g2inf_settings );
                header('Location: ' . admin_url( 'admin.php?page=g2inf-settings' ));
            }
        endif;
    }

    public function get_custom_fields() {
        if (isset($_GET['g2infsettingsaction']) && $_GET['g2infsettingsaction'] == 'synccustomfields'):
            $g2inf_settings = get_option('g2inf_settings');
            $client_id            = isset($g2inf_settings['client_id']) ? $g2inf_settings['client_id'] : '';
            $client_secret        = isset($g2inf_settings['client_secret']) ? $g2inf_settings['client_secret'] : '';
            $token                = isset($g2inf_settings['token']) ? $g2inf_settings['token'] : '';

            if (!$client_id || !$client_secret)
                return;

            $infusionsoft = new \Infusionsoft\Infusionsoft(array(
                'clientId'     => $client_id,
                'clientSecret' => $client_secret,
                'redirectUri'  => admin_url( 'admin.php?page=g2inf-settings&g2infsettingsaction=getaccesstoken' ),
            ));

            $serialized_token = unserialize($token);
            $customfield_api_url = $infusionsoft->customfields()->full_url;
            $response = wp_remote_get(
                $customfield_api_url.'?access_token='.$serialized_token->accessToken,
                array(
                    'timeout' => 120,
                    'httpversion' => '1.1' 
                )
            );
        
            $g2inf_settings['custom_fields'] = $response['body'];
                
            update_option( 'g2inf_settings', $g2inf_settings );
            header('Location: ' . admin_url( 'admin.php?page=g2inf-settings' ));

        endif;
    }
}

new G2inf_Settings;