<?php if ( ! defined( 'ABSPATH' ) ) exit;

class GF_Field_Infusionsoft_Products extends GF_Field {

	public function __construct() {
		if(!empty($this->getInfusionProducts())) {
			add_filter( 'gform_add_field_buttons', array( $this, 'add_button' ));
		}
    }

    public function getInfusionProducts() {
    	$g2inf_settings = get_option('g2inf_settings');
		$products = isset($g2inf_settings['products']) ? $g2inf_settings['products'] : '';
		return $products;
    }

    /**
     * Adds the field button to the specified group.
     *
     * @param array $field_groups The field groups containing the individual field buttons.
     *
     * @return array
     */
    public function add_button( $field_groups ) {
        $field_groups = $this->maybe_add_field_group( $field_groups );
     
        return parent::add_button( $field_groups );
    }

    /**
     * Adds the custom field group if it doesn't already exist.
     *
     * @param array $field_groups The field groups containing the individual field buttons.
     *
     * @return array
     */
    public function maybe_add_field_group( $field_groups ) {
        foreach ( $field_groups as $field_group ) {
            if ( $field_group['name'] == 'infusionsoft_products_fields' ) {
     
                return $field_groups;
            }
        }

        $inf_products = json_decode($this->getInfusionProducts());
        $fields = array();

       	foreach($inf_products as $infprod) {
       		$prod = array(
       			'class' => 'button', 
       			'data-type' => 'product', 
       			'data-id' => $infprod->id, 
       			'value' => GFCommon::get_field_type_title( $infprod->product_name ));
       		array_push($fields, $prod);
       	}
     
        $field_groups[] = array(
            'name'   => 'infusionsoft_products_fields',
            'label'  => __( 'Infusionsoft Products', 'simplefieldaddon' ),
            'fields' => $fields,
        );
     
        return $field_groups;
    }

    /**
     * Assign the field button to the custom group.
     *
     * @return array
     */
    public function get_form_editor_button() {
        return null;
    }
}

new GF_Field_Infusionsoft_Products;