<?php if ( ! defined( 'ABSPATH' ) ) exit;

class G2inf_Merges{
    
    public function __construct(){
        add_action( 'wp_ajax_get_form_fields' , array( $this , 'get_form_fields' ) );
    }

    public function get_form_fields() {
        if( isset( $_POST['data'] ) ):
            $form_id = $_POST['data']['form_id'];

            $form = GFAPI::get_form( $form_id );
            $fields = array();

            foreach ( $form['fields'] as $key => $field) {
                if( $field['type'] == 'radio' || $field['type'] == 'quiz' || $field['type'] == 'survey' ){
                    foreach ( $field['choices'] as $key => $choice) {
                        $fields[] = array(
                            'field_id'      => $field['id'].'.'.($key+1).'-'.$choice['value'].'-'.$field['type'],
                            'type'          => $field['type'],
                            'label'         => $field['label'].'-'.$choice['value'],
                            'orig_field_id' => $field['id']
                        );
                    }
                }
                elseif( $field['type'] == 'checkbox' ){
                    foreach ( $field['choices'] as $key => $choice) {
                        $mapkey = ($key >= 9) ? ($key+2) : ($key+1);
                        $fields[] = array(
                            'field_id'      => $field['id'].'.'. $mapkey.'-'.$choice['value'].'-'.$field['type'],
                            'type'          => $field['type'],
                            'label'         => $field['label'].'-'.$choice['value'],
                            'orig_field_id' => $field['id']
                        );
                    }
                }
                elseif( $field['type'] == 'date' ){
                    $format = '';
                    if ($field['dateFormat'] == 'mdy') {
                        $format = 'm/d/Y';
                    }
                    elseif ($field['dateFormat'] == 'dmy') {
                        $format = 'd/m/Y';
                    }
                    elseif ($field['dateFormat'] == 'dmy_dash') {
                        $format = 'dmy_dash';
                    }
                    elseif ($field['dateFormat'] == 'dmy_dot') {
                        $format = 'd.m.Y';
                    }
                    elseif ($field['dateFormat'] == 'ymd_slash') {
                        $format = 'Y/m/d';
                    }
                    elseif ($field['dateFormat'] == 'ymd_slash') {
                        $format = 'Y/m/d';
                    }
                    elseif ($field['dateFormat'] == 'ymd_dash') {
                        $format = 'ymd_dash';
                    }
                    elseif ($field['dateFormat'] == 'ymd_dot') {
                        $format = 'Y.m.d';
                    }
                    $fields[] = array(
                            'field_id'  => $field['id'].'-@-'.$field['type'].'-'.$format,
                            'type'      => $field['type'],
                            'label'     => $field['label']
                        );
                }
                else {
                    $fields[] = array(
                            'field_id'  => $field['id'].'-@-'.$field['type'],
                            'type'      => $field['type'],
                            'label'     => $field['label']
                        );
                }
            }

            echo json_encode($fields);
            die();
        endif;
    }
}

new G2inf_Merges;