<?php if ( ! defined( 'ABSPATH' ) ) exit;

class G2inf{
    
    private static $instance;

    public static function get_instance()
    {
        if( null == self::$instance ) {
            self::$instance = new G2inf();
        }

        return self::$instance;
    }

    function __construct(){
        add_action('admin_enqueue_scripts', array( $this, 'admin_scripts' ));
        add_action('wp_enqueue_scripts', array($this, 'public_scripts'));
    }

    public function admin_scripts($hook){
        global $post;
        global $post_type;

        wp_enqueue_script( 'jquery' );
        wp_register_script('g2inf-vue-main', G2INF_URL . '/assets/js/vue.min.js', '1.0', true, true );
        wp_enqueue_script('g2inf-vue-main');

        wp_enqueue_media();
        wp_enqueue_script( 'media-upload' );

        $g2inf_settings = get_option('g2inf_settings');
        if ( ($hook == 'post-new.php' || (isset($_GET['action']) && $_GET['action'] == 'edit')) && $post_type == 'birchtree_g2inf') {
            wp_register_script('vue-scripts', G2INF_URL . '/assets/js/vue-script-v2.js', '1.0', true, true );
            $g2inf_local = array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'meta'  => get_post_meta($post->ID),
                'custom_fields' => isset($g2inf_settings['custom_fields']) ? $g2inf_settings['custom_fields'] : []
            );
            wp_localize_script('vue-scripts', 'g2infdata', $g2inf_local );
            wp_enqueue_script('vue-scripts');
        }

        wp_register_style('g2inf-admin-style', G2INF_URL . '/assets/css/g2inf-admin-style.css', '1.0', true );
        wp_enqueue_style('g2inf-admin-style');
    }

    public function public_scripts(){

    }
}