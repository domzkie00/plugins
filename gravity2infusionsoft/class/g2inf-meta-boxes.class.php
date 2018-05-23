<?php if ( ! defined( 'ABSPATH' ) ) exit;

class G2inf_Metas {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'g2inf_meta_boxes'));
    }

    public function g2inf_meta_boxes() {
        add_meta_box(
            'g2inf_general_info',
            __( 'Merge Information', 'gravity-to-infusionsoft' ), 
            array( $this, 'general_info_metabox' ),
            'birchtree_g2inf',
            'normal',
            'high'
        );

        add_meta_box(
            'g2inf_mapping',
            __( 'Merge Mapping', 'gravity-to-infusionsoft' ), 
            array( $this, 'g2inf_mapping_metabox' ),
            'birchtree_g2inf',
            'normal',
            'high'
        );

        add_meta_box(
            'g2inf_tags',
            __( 'Tags', 'gravity-to-infusionsoft' ), 
            array( $this, 'g2inf_tags_metabox' ),
            'birchtree_g2inf',
            'normal',
            'high'
        );
    }

    public function general_info_metabox() {
        global $post;
        include_once(G2INF_PATH_INCLUDES . '/g2inf-general-info-metabox.php');
    }

    public function g2inf_mapping_metabox() {
        global $post;
        include_once(G2INF_PATH_INCLUDES . '/g2inf-mapping-metabox.php');
    }

    public function g2inf_tags_metabox() {
        global $post;
        $g2inf_settings = get_option('g2inf_settings');
        $tags = isset($g2inf_settings['tags']) ? unserialize($g2inf_settings['tags']) : '';
        include_once(G2INF_PATH_INCLUDES . '/g2inf-tags-metabox.php');
    }

}

new G2inf_Metas;