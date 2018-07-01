<?php if ( ! defined( 'ABSPATH' ) ) exit;

session_start();

class G2inf_Products{
    
    public function __construct(){
        add_action('wp_ajax_set_infusion_products_use_status', array($this, 'set_infusion_products_use_status_ajax'));
        add_action('gform_pre_submission', array($this, 'pre_submission_handler'));
        add_action('wp', array($this, 'check_mapped_email_on_post_load'));
        add_action('wp_ajax_createContactToIS', array($this, 'createContactToIS_ajax'));
        add_action('wp_ajax_createContactCCardToIS', array($this, 'createContactCCardToIS_ajax'));
    }

    public function licenseKeyValidAndActivated() {
        $g2inf_licenses = get_option('g2inf_licenses');
        if(!empty($g2inf_licenses)) {
            foreach($g2inf_licenses as $key => $val) {
                if (strpos($key, '_license_key') !== false) {
                    $key = str_replace("_license_key", "_license_active" , $key);
                    if(get_option($key)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function refreshISToken() {
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

        return $infusionsoft;
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
            var multi_posts = null;
            var validate_IS_ccard_modal = null;
            var contact_id = null;
            var valid_license_key = true;
        </script><?php

        if (!is_admin()) {
            if($this->licenseKeyValidAndActivated()) {
                $checkISForCurrentUser = $this->checkISifUserExists();
                $cid = null;
                if(isset($checkISForCurrentUser[0]['Id'])) {
                    $cid = $checkISForCurrentUser[0]['Id'];
                }
                $modal = file_get_contents(G2INF_PATH_INCLUDES . '/g2inf-createIScustomer-modal-form.php');
                $checkout_modal = file_get_contents(G2INF_PATH_INCLUDES . '/g2inf-validateIScreditcard-modal-form.php');

                ?>
                    <script>
                        validate_IS_ccard_modal = <?php echo json_encode($checkout_modal) ?>;
                        contact_id = <?php echo json_encode($cid) ?>;
                    </script>
                <?php

                if(!$checkISForCurrentUser && $checkISForCurrentUser != 'ignore') {
                    $mapped_email = $this->getMappedContactEmail();
                    ?>
                        <script>
                            create_IS_customer_modal = <?php echo json_encode($modal) ?>;
                            mapped_email = <?php echo json_encode($mapped_email) ?>;
                        </script>
                    <?php
                }

                if($checkISForCurrentUser == 'ignore') {
                    global $posts;

                    $multi_posts = [];
                    foreach($posts as $p) {
                        $mapped_email = $this->getMappedContactEmail($p);
                        $checkISForCurrentUser = $this->checkISifUserExists($mapped_email);

                        if(isset($checkISForCurrentUser[0]['Id'])) {
                            $checkISForCurrentUser = $checkISForCurrentUser[0]['Id'];
                        } else {
                            $checkISForCurrentUser = 'No IS record found.';
                        }

                        $arr = [];
                        $arr['id'] = $p->ID;
                        $arr['mapped_email'] = $mapped_email;
                        $arr['is_id'] = $checkISForCurrentUser;
                        $multi_posts[] = $arr;
                    }

                    ?>
                        <script>
                            create_IS_customer_modal = <?php echo json_encode($modal) ?>;
                            multi_posts = <?php echo json_encode($multi_posts) ?>;
                        </script>
                    <?php
                }
            } else {
                ?>
                    <script>
                        valid_license_key = false;
                    </script>
                <?php
            }
        }
    }

    public function pre_submission_handler( $form ) {
        $ISkey = 'Infusion Product ID:';
        $ISproducts = [];
        $fquantities = explode(',', $_SESSION["field_vals"]);
        $ISquantities = [];

        $i = 0;
        foreach($form['fields'] as $field) {
            if (strpos($field->type, $ISkey) !== FALSE) {
                $exp = explode($ISkey, $field->type); 
                $ISproducts[] = (int) $exp[1];
                $ISquantities[] = (int) $fquantities[$i];
            }
            $i++;
        }

        if(count($ISproducts) > 0) {
            $checkISForCurrentUser = $this->checkISifUserExists();

            if($checkISForCurrentUser) {
                if(count($checkISForCurrentUser) == 1) {
                    if(isset($_SESSION["is_ccid"])) {
                        $this->addProductToUser($checkISForCurrentUser[0]['Id'], $ISproducts, $ISquantities, $_SESSION["is_ccid"]);
                        session_unset(); 
                    } else {
                        die();
                    }
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

    public function getMappedContactEmail($passed_post=null) {
        global $post;
        global $posts;

        if($passed_post) {
            $post = $passed_post;
        }

        if(count($posts) == 1 || $passed_post) {
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
    }

    public function checkISifUserExists($passed_mapped_email=null) {

        if($passed_mapped_email) {
            $mapped_email = $passed_mapped_email;
        } else {
            $mapped_email = $this->getMappedContactEmail();
        }

        if($mapped_email) {
            try{
                $infusionsoft = $this->refreshISToken();
                $contact_details = $infusionsoft->contacts('xml')->findByEmail($mapped_email, array());

                if ( empty($contact_details) ) {
                    return false;
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

    public function createContactCCardToIS_ajax() {
        $cardType = $_POST['data'][0]['value'];
        $contactID = (int)$_POST['data'][1]['value'];
        $cardNumber = $_POST['data'][2]['value'];
        $expirationMonth = $_POST['data'][3]['value'];
        $expirationYear = $_POST['data'][4]['value'];
        $securityCode = $_POST['data'][5]['value'];

        $infusionsoft = $this->refreshISToken();
        $res = $infusionsoft->invoices()->validateCreditCard($cardType, $contactID, $cardNumber, $expirationMonth, $expirationYear, $securityCode);

        if($res['Valid'] == true) {
            $values = [
                'CardType' => $cardType, 
                'ContactId' => $contactID, 
                'CardNumber' => $cardNumber,
                'ExpirationMonth' => $expirationMonth,
                'ExpirationYear' => $expirationYear,
                'CVV2' => $securityCode
            ];
            $res = $infusionsoft->data()->add('CreditCard', $values);
            $_SESSION["is_ccid"] = $res;
            $_SESSION["field_vals"] = $_POST['data'][6]['value'];
            echo json_encode(['result' => true, 'message' => 'Success']);
        } else {
            echo json_encode(['result' => false, 'message' => 'Credit Card not valid.']);
        }

        die();
    }

    public function createContactToIS_ajax() {
        $contact = [
            'FirstName' => $_POST['data'][0]['value'], 
            'LastName' => $_POST['data'][1]['value'], 
            'Email' => $_POST['data'][2]['value']
        ];

        $infusionsoft = $this->refreshISToken();
        $create_contact_result = $infusionsoft->contacts('xml')->addWithDupCheck($contact, 'Email');

        echo json_encode(['result' => true, 'message' => 'Success']);

        die();
    }

    public function addProductToUser($contact, $passed_products, $quantities, $creditCardID) {
        $g2inf_settings = get_option('g2inf_settings');
        $products = isset($g2inf_settings['products']) ? $g2inf_settings['products'] : '';
        $arr_prod = (array) json_decode($products);

        $infusionsoft = $this->refreshISToken();

        if(count($passed_products) == 1) {
            foreach($arr_prod as $prod) {
                if($prod->id == $passed_products[0]) {

                    /*
                    * invoice item types
                    * 4 - product
                    * 9 - subscription plan
                    */

                    /*if($prod->subscription_only) {
                        foreach($prod->subscription_plans as $plans) {
                            //print_r($plans);
                        }
                    } else {*/
                        $contact_id = (int) $contact;
                        $datetime = new \DateTime('now',new \DateTimeZone('America/New_York'));
                        $price = (double) $prod->product_price;
                        $invoiceID = $infusionsoft->invoices()->createBlankOrder(
                            $contact_id, 
                            'Purchased from Gravity Infusionsoft Form', 
                            $datetime, 
                            2, 
                            2
                        );
                        $order = $infusionsoft->invoices()->addOrderItem($invoiceID, $prod->id, 4, $price, $quantities[0], '', '');
                        if($order == true) {
                            $notes = '';
                            $merchantAccountID = 1;
                            $creditCardID = (int) $creditCardID;
                            $bypassComissions = false;
                            $infusionsoft->invoices()->chargeInvoice($invoiceID, $notes, $creditCardID, $merchantAccountID, $bypassComissions);
                        }
                    //}
                }
            }
        } else {
            $contact_id = (int) $contact;
            $datetime = new \DateTime('now',new \DateTimeZone('America/New_York'));
            $invoiceID = $infusionsoft->invoices()->createBlankOrder(
                $contact_id, 
                'Purchased from Gravity Infusionsoft Form', 
                $datetime, 
                2, 
                2
            );
            $order_res = null;

            foreach($arr_prod as $prod) {
                $i = 0;
                foreach($passed_products as $passed_prod) {
                    if($prod->id == $passed_prod) {
                        $price = (double) $prod->product_price;
                        $order_res = $infusionsoft->invoices()->addOrderItem($invoiceID, $prod->id, 4, $price, $quantities[$i], '', '');
                    }
                    $i++;
                }
            }

            if($order_res == true) {
                $notes = '';
                $merchantAccountID = 1;
                $creditCardID = (int) $creditCardID;
                $bypassComissions = false;
                $infusionsoft->invoices()->chargeInvoice($invoiceID, $notes, $creditCardID, $merchantAccountID, $bypassComissions);
            }
        }
    }

}

new G2inf_Products;