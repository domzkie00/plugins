<?php if ( ! defined( 'ABSPATH' ) ) exit;

class G2inf_Products{
    
    public function __construct(){
        add_action('wp_ajax_set_infusion_products_use_status', array($this, 'set_infusion_products_use_status_ajax'));
        add_action('gform_pre_submission', array($this, 'pre_submission_handler'));
    }

    public function set_infusion_products_use_status_ajax() {
        $g2inf_settings = get_option('g2inf_settings');
        $g2inf_settings['products_use_status'] = $_POST['data'];
        update_option( 'g2inf_settings', $g2inf_settings );
        echo $_POST['data'];
        die();
    }

    public function pre_submission_handler( $form ) {
        $ISkey = 'Infusion Product ID:';
        $ISproducts = [];
        foreach($form['fields'] as $field) {
            if (strpos($field->type, $ISkey) !== FALSE) {
                $exp = explode($ISkey, $field->type); 
                $ISproducts[] = (int) $exp[1];
            }
        }

        if(count($ISproducts) > 0) {
            $current_user = wp_get_current_user();
            $email = $this->getMappedContactEmail();
            $checkISForCurrentUser = $this->checkISifUserExists($email);

            if($checkISForCurrentUser) {
                if(count($checkISForCurrentUser) == 1) {
                    $this->addProductToUser($checkISForCurrentUser[0]['Id'], $ISproducts);
                }
            } else {
                echo 'Email not existing in IS. Better create IS customer.';
            }
        }
    }

    public function getMappedContactEmail() {
        $args  =  array('post_type' => 'birchtree_g2inf');
        $posts_array = get_posts( $args );

        $g2inf_post_id = null;
        foreach($posts_array as $post_map){
            $g2inf_post_id = $post_map->ID;
        }

        $mapped_email = get_post_meta($g2inf_post_id, '_contact_email_text', true );

        return $mapped_email;
    }

    public function checkISifUserExists($user_email) {
        try{
            $g2inf_settings = get_option('g2inf_settings');
            $client_id            = isset($g2inf_settings['client_id']) ? $g2inf_settings['client_id'] : '';
            $client_secret        = isset($g2inf_settings['client_secret']) ? $g2inf_settings['client_secret'] : '';
            $token                = isset($g2inf_settings['token']) ? $g2inf_settings['token'] : '';

            if (empty($client_id) || empty($client_secret))
                return;

            $infusionsoft = new \Infusionsoft\Infusionsoft(array(
                'clientId'     => $client_id,
                'clientSecret' => $client_secret,
                'redirectUri'  => admin_url( '' ),
            ));

            $infusionsoft->setToken(unserialize($token));
            $infusionsoft->refreshAccessToken();
            $g2inf_settings['token'] = serialize($infusionsoft->getToken());
            update_option( 'g2inf_settings', $g2inf_settings );
            $contact_details = $infusionsoft->contacts('xml')->findByEmail($user_email, array());

            if ( empty($contact_details) ) {
                return false;
                //$contact_details = $this->createContactToIS();
            } else {
                return $contact_details;
            }
        }catch( Exception $e ){
            error_log($e);
        }
    }

    public function createContactToIS() {
        //$infusionsoft->contacts()->addWithDupCheck($data, 'Email');
    }

    public function checkIfUserSetCreditCard($infusionsoft, $contactId) {
        //$user_credit_card = $infusionsoft->invoices()->locateExistingCard($contactID, $lastFour);
        $contact = $infusionsoft->contacts('xml')->load($contactId, array());
        print_r($contact);
        die();
    }

    public function addProductToUser($contact, $passed_products) {
        $g2inf_settings = get_option('g2inf_settings');
        $client_id            = isset($g2inf_settings['client_id']) ? $g2inf_settings['client_id'] : '';
        $client_secret        = isset($g2inf_settings['client_secret']) ? $g2inf_settings['client_secret'] : '';
        $token                = isset($g2inf_settings['token']) ? $g2inf_settings['token'] : '';
        $products = isset($g2inf_settings['products']) ? $g2inf_settings['products'] : '';
        $arr_prod = (array) json_decode($products);

        $infusionsoft = new \Infusionsoft\Infusionsoft(array(
            'clientId'     => $client_id,
            'clientSecret' => $client_secret,
            'redirectUri'  => admin_url( '' ),
        ));

        $infusionsoft->setToken(unserialize($token));
        $infusionsoft->refreshAccessToken();
        $g2inf_settings['token'] = serialize($infusionsoft->getToken());
        update_option( 'g2inf_settings', $g2inf_settings );

        if(count($passed_products) == 1) {
            foreach($arr_prod as $prod) {
                if($prod->id == $passed_products[0]) {

                    /*
                    * invoice item types
                    * 4 - product
                    * 9 - subscription plan
                    */
                    if($prod->subscription_only) {
                        foreach($prod->subscription_plans as $plans) {
                            //print_r($plans);
                        }
                    } else {
                        /*$place_order = $infusionsoft->orders('xml')->placeOrder($contact);
                        print_r($place_order);*/

                        $contact_id = (int) $contact;
                        $datetime = new \DateTime('now',new \DateTimeZone('America/New_York'));
                        $price = (double) $prod->product_price;
                        $invoiceId = $infusionsoft->invoices()->createBlankOrder(
                            $contact_id, 
                            'Purchased from Gravity Infusionsoft Form', 
                            $datetime, 
                            2, 
                            2
                        );
                        $order = $infusionsoft->invoices()->addOrderItem($invoiceId, $prod->id, 4, $price, 1, '', '');
                        if($order == true) {
                            //success
                            echo 'created invoice: '.$order;
                        }
                    }
                }
            }
        } else {
        	echo 'multiple IS product';
        }
    }

}

new G2inf_Products;