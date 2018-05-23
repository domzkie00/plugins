<?php if ( ! defined( 'ABSPATH' ) ) exit;

class G2inf_Post_Type {

    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'disable_supports'));
        add_action('save_post', array($this, 'save_field' ));
    }

    public function register_post_type() {
        register_post_type( 'birchtree_g2inf', array(
            'labels' => array(
                'name' => __('Gravity 2 Infusionsoft', 'gravity-to-infusionsoft'),
                'singular_name' => __('Mapping', 'gravity-to-infusionsoft'),
                'add_new' => _x('New Mapping', 'Mapping', 'gravity-to-infusionsoft' ),
                'add_new_item' => __('Add New Mapping', 'gravity-to-infusionsoft' ),
                'edit_item' => __('Edit Mapping', 'gravity-to-infusionsoft' ),
                'new_item' => __('New Mapping', 'gravity-to-infusionsoft' ),
                'view_item' => __('View Mapping', 'gravity-to-infusionsoft' ),
                'search_items' => __('Search Mappings', 'gravity-to-infusionsoft' ),
                'not_found' =>  __('No mappings found', 'gravity-to-infusionsoft' ),
                'not_found_in_trash' => __('No mappings found in Trash', 'gravity-to-infusionsoft' ),
            ),
            'description' => __('g2inf mappings', 'gravity-to-infusionsoft'),
            'public' => false,
            'publicly_queryable' => true,
            'query_var' => true,
            'rewrite' => true,
            'exclude_from_search' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 60, // probably have to change, many plugins use this
            'menu_icon' => 'dashicons-media-document',
            'supports' => array(
                'title'
            ),
        ));

    }

    public function disable_supports() {
        remove_post_type_support( 'birchtree_g2inf', 'comments' );
    }

    public function save_field( $post_id ){
        // Avoid autosaves
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $slug = 'birchtree_g2inf';
        if ( ! isset( $_POST['post_type'] ) || $slug != $_POST['post_type'] ) {
            return;
        }

        if ( isset( $_POST['_g2inf_gravity_form_id']  ) ) 
            update_post_meta( $post_id, '_g2inf_gravity_form_id', $_POST['_g2inf_gravity_form_id']);

        if ( isset( $_POST['_contact_email']  ) ) 
            update_post_meta( $post_id, '_contact_email', $_POST['_contact_email']);

        if ( isset( $_POST['_contact_email_text']  ) ) 
            update_post_meta( $post_id, '_contact_email_text', $_POST['_contact_email_text']);

        if ( isset( $_POST['_mapped_form_fields']  ) ) 
            update_post_meta( $post_id, '_mapped_form_fields',  json_encode($_POST['_mapped_form_fields']));
        else
            update_post_meta( $post_id, '_mapped_form_fields',  json_encode([]));

        if ( isset( $_POST['_mapped_inf_fields']  ) ) 
            update_post_meta( $post_id, '_mapped_inf_fields',  json_encode($_POST['_mapped_inf_fields']));
        else
            update_post_meta( $post_id, '_mapped_inf_fields',  json_encode([]));

        if ( isset( $_POST['_add_tags']  ) ) 
            update_post_meta( $post_id, '_add_tags',  json_encode($_POST['_add_tags']));
        else
            update_post_meta( $post_id, '_add_tags',  json_encode([]));

        if ( isset( $_POST['_remove_tags']  ) ) 
            update_post_meta( $post_id, '_remove_tags',  json_encode($_POST['_remove_tags']));
        else
            update_post_meta( $post_id, '_remove_tags',  json_encode([]));

        

        
    }

}

new G2inf_Post_Type;