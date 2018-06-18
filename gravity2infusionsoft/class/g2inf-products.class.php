<?php if ( ! defined( 'ABSPATH' ) ) exit;

class G2inf_Products{
    
    public function __construct(){
        add_action('wp_ajax_set_infusion_products_use_status', array($this, 'set_infusion_products_use_status_ajax'));
        add_action('gform_pre_submission', array($this, 'pre_submission_handler'));
        add_action('wp', array($this, 'check_mapped_email_on_post_load'));
        add_action('wp_ajax_createContactToIS', array($this, 'createContactToIS_ajax'));
    }

    public function set_infusion_products_use_status_ajax() {
        $g2inf_settings = get_option('g2inf_settings');
        $g2inf_settings['products_use_status'] = $_POST['data'];
        update_option( 'g2inf_settings', $g2inf_settings );
        echo $_POST['data'];
        die();
    }

    public function check_mapped_email_on_post_load () {
        ?><script> 
            var create_IS_customer_modal = null; 
            var mapped_email = null;
        </script><?php

        if (!is_admin()) {
            $checkISForCurrentUser = $this->checkISifUserExists();

            if(!$checkISForCurrentUser) {
                $modal = file_get_contents(G2INF_PATH_INCLUDES . '/g2inf-createIScustomer-modal-form.php');
                $mapped_email = $this->getMappedContactEmail();
                ?>
                    <script>
                        create_IS_customer_modal = <?php echo json_encode($modal) ?>;
                        mapped_email = <?php echo json_encode($mapped_email) ?>;
                    </script>
                <?php
            }
        }
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
            $checkISForCurrentUser = $this->checkISifUserExists();

            if($checkISForCurrentUser) {
                if(count($checkISForCurrentUser) == 1) {
                    echo 'email exists - pre submit';
                    $this->createContactCreditCard($checkISForCurrentUser[0]['Id']);
                    die();
                    //$this->addProductToUser($checkISForCurrentUser[0]['Id'], $ISproducts);
                }
            } else {
                echo 'Email not existing in IS. Better create IS customer.';
            }
        }
    }

    function GetBetween($content,$start,$end){ 
        $r = explode($start, $content); 

        if (isset($r[1])){ 
            $r = explode($end, $r[1]); return $r[0]; 
        } 

        return ''; 
    }

    public function getMappedContactEmail() {
        global $post;

        $innerCode = $this->GetBetween($post->post_content, '[', ']');
        $innerCodeParts = explode(' ', $innerCode);

        $gform_shortcode_exists = false;
        foreach($innerCodeParts as $scValues) {
            if (strpos($scValues, 'gravityform') !== false) {
                $gform_shortcode_exists = true;
            }
        }

        $gform_id = null;
        if($gform_shortcode_exists == true) { //if shortcode used is gform
            foreach($innerCodeParts as $scValues) {
                if (strpos($scValues, 'id=') !== false) {
                    $getID = explode('id=', $scValues);
                    $stringID = str_replace('"', '', $getID[1]);
                    $gform_id = (int)$stringID;
                }
            }
        }

        if($gform_id != null) { //check if shortcode exists in post_content
            $args  =  array(
                'post_type' => 'birchtree_g2inf', 
                'meta_key' => '_g2inf_gravity_form_id',
                'meta_value' => $gform_id
            );
            $posts_array = get_posts( $args );

            if(count($posts_array) == 1) { //if returns only one, means query gets the specific GFrom used
                $g2inf_post_id = null;
                foreach($posts_array as $post_map){
                    $g2inf_post_id = $post_map->ID;
                }

                $mapped_email = get_post_meta($g2inf_post_id, '_contact_email_text', true );

                return $mapped_email;
            }
        }
    }

    public function checkISifUserExists() {
        $mapped_email = $this->getMappedContactEmail();

        if($mapped_email) {
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
                $contact_details = $infusionsoft->contacts('xml')->findByEmail($mapped_email, array());

                if ( empty($contact_details) ) {
                    return false;
                    //$contact_details = $this->createContactToIS();
                } else {
                    return $contact_details;
                }
            }catch( Exception $e ){
                error_log($e);
            }
        } else {
            return 'ignore';
        }
    }

    public function createContactToIS_ajax() {
        $contact = [
            'FirstName' => $_POST['data'][0]['value'], 
            'LastName' => $_POST['data'][1]['value'], 
            'Email' => $_POST['data'][2]['value']
        ];

        $g2inf_settings = get_option('g2inf_settings');
        $client_id            = isset($g2inf_settings['client_id']) ? $g2inf_settings['client_id'] : '';
        $client_secret        = isset($g2inf_settings['client_secret']) ? $g2inf_settings['client_secret'] : '';
        $token                = isset($g2inf_settings['token']) ? $g2inf_settings['token'] : '';

        if (empty($client_id) || empty($client_secret)) {
            echo json_encode(['result' => false, 'message' => 'Something went wrong.']);
        } else {
            $infusionsoft = new \Infusionsoft\Infusionsoft(array(
                'clientId'     => $client_id,
                'clientSecret' => $client_secret,
                'redirectUri'  => admin_url( '' ),
            ));

            $infusionsoft->setToken(unserialize($token));
            $infusionsoft->refreshAccessToken();
            $g2inf_settings['token'] = serialize($infusionsoft->getToken());
            update_option( 'g2inf_settings', $g2inf_settings );
            $create_contact_result = $infusionsoft->contacts('xml')->addWithDupCheck($contact, 'Email');

            echo json_encode(['result' => true, 'message' => 'Success']);
        }

        die();
    }

    public function createContactCreditCard($contactId) {
        //$user_credit_card = $infusionsoft->invoices()->locateExistingCard($contactID, $lastFour);
        //$contact = $infusionsoft->contacts('xml')->load($contactId, array());
        print_r($contactId);
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
                            //echo 'created invoice: '.$invoiceId;
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