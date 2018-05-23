<?php if ( ! defined( 'ABSPATH' ) ) exit;

class G2inf_Processing{
    
    public function __construct(){
        add_action('gform_after_submission', array($this, 'process_merge'));
    }

    public function process_merge($entry) {
        $merges = $this->get_merges_by_form_id($entry['form_id']);

        foreach ($merges as $key => $merge) {
            $mapped_form_fields = json_decode(get_post_meta($merge->ID, '_mapped_form_fields', true));
            $mapped_inf_fields = json_decode(get_post_meta($merge->ID, '_mapped_inf_fields', true));

            $g2inf_settings = get_option('g2inf_settings');
            $client_id            = isset($g2inf_settings['client_id']) ? $g2inf_settings['client_id'] : '';
            $client_secret        = isset($g2inf_settings['client_secret']) ? $g2inf_settings['client_secret'] : '';
            $token                = isset($g2inf_settings['token']) ? $g2inf_settings['token'] : '';

            if (!$client_id || !$client_secret)
                return;

            try {
                $infusionsoft = new \Infusionsoft\Infusionsoft(array(
                    'clientId'     => $client_id,
                    'clientSecret' => $client_secret,
                    'redirectUri'  => admin_url( 'admin.php?page=g2inf-settings&g2infsettingsaction=getaccesstoken' ),
                ));
                $infusionsoft->setToken(unserialize($token));
                $g2inf_settings['token'] = serialize($infusionsoft->refreshAccessToken());
                update_option( 'g2inf_settings', $g2inf_settings );

                if (get_post_meta($merge->ID, '_contact_email', true) == 'custom') {
                    $user_email = get_post_meta($merge->ID, '_contact_email_text', true);
                } else {
                    $user_email = rgar( $entry, explode("-", get_post_meta($merge->ID, '_contact_email', true) )[0]);
                }

                $contact_details = $infusionsoft->contacts('xml')->findByEmail($user_email, array());

                // initialize empty arrays
                $address_temp_array = array();
                $custom_fields_array = array();
                $mapping = array();

                // define the user ID first
                $mapping['id'] = $contact_details[0]['Id'];

                foreach ($mapped_form_fields as $key => $mapped_form_field) {
                    $temp_array = array();
                    $field_id = explode('-', $mapped_form_field)[0];
                    $mapped_inf = explode('-', $mapped_inf_fields[$key]);

                    if (array_key_exists(1, $mapped_inf) && $mapped_inf[1] == 'addresses') {
                        $address_temp_array[$mapped_inf[0]] = rgar( $entry, $field_id);
                    } 
                    else if (array_key_exists(1, $mapped_inf) && $mapped_inf[1] == 'custom_fields') {
                        $c_field = json_decode($mapped_inf[2]);
                        $c_field_map = array(
                            'content' => rgar( $entry, $field_id),
                            'id'    => $c_field->id
                        );

                        array_push($custom_fields_array, $c_field_map);
                    }
                    else {
                        $temp_array[$mapped_inf_fields[$key]] = rgar( $entry, $field_id);
                    }

                    if (!empty($address_temp_array)) {
                        $address_temp_array['field'] = 'BILLING';
                        $temp_array['addresses'] = [$address_temp_array];
                    }

                    if (!empty($custom_fields_array)) {
                        $temp_array['custom_fields'] = $custom_fields_array;
                    }

                    
                    $mapping = array_merge($mapping, $temp_array);
                }

                // echo '<pre>';
                // wp_die(print_r($mapping));

                $infusionsoft->contacts()->create($mapping);

                $add_tags = json_decode(get_post_meta($merge->ID, '_add_tags', true));
                if( !empty($add_tags) ) {
                    foreach($add_tags as $tag_id) {
                        $infusionsoft->contacts('xml')->addToGroup($contact_details[0]['Id'], $tag_id);
                    }
                }

                $remove_tags = json_decode(get_post_meta($merge->ID, '_remove_tags', true));
                if( !empty($remove_tags) ) {
                    foreach($remove_tags as $tag_id) {
                        $infusionsoft->contacts('xml')->removeFromGroup($contact_details[0]['Id'], $tag_id);
                    }
                }

            } catch (Exception $e) {
                error_log($e);
            }

        } //end foreach

        return;
    
    }

    private function get_merges_by_form_id($form_id) {
        $args = array(
            'post_type' => 'birchtree_g2inf',
            'posts_per_page' => 500,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_g2inf_gravity_form_id',
                    'value' =>  $form_id,
                    'compare' => '==',
                ),
            ),
        );

        $merges = get_posts($args);

        return $merges;
    }

}

new G2inf_Processing;