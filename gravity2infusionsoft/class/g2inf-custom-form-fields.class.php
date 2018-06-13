<?php if ( ! defined( 'ABSPATH' ) ) exit;

class GF_Field_Infusionsoft_Products extends GF_Field {

	public function __construct() {
		if(!empty($this->getInfusionProducts())) {
			add_filter( 'gform_add_field_buttons', array( $this, 'add_button' ));
            add_action( 'gform_editor_js_set_default_values', array( $this, 'set_defaults'));
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
            $allSpaces = false;
            if (strlen(trim($infprod->product_desc)) == 0) {
                $allSpaces = true;
            }

       		$prod = array(
       			'class' => 'button infprodBTN', 
       			'data-type' => 'product', 
       			'data-id' => $infprod->id,
                'data-pname' => $infprod->product_name,
                'data-pdesc' => ($infprod->product_desc && $allSpaces == false) ? $infprod->product_desc : $infprod->product_short_desc,
                'data-price' => $infprod->product_price,
                'data-infprod' => true,
                "onclick" => "StartAddField('Infusion Product ID:".$infprod->id."');",
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

    public function set_defaults(){
        $inf_products = json_decode($this->getInfusionProducts());
        $fields = array();

        foreach($inf_products as $infprod) {
            ?>
            //this hook is fired in the middle of a switch statement,
            //so we need to add a case for our new field type
            case "Infusion Product ID:" + <?php echo $infprod->id ?> :
                var pid = $('.infprodBTN.selected').attr('data-id');
                var pname = $('.infprodBTN.selected').attr('data-pname');
                var pprice = $('.infprodBTN.selected').attr('data-price');
                var pdesc = $('.infprodBTN.selected').attr('data-pdesc');

                field.label = 'Infusion: '+pname;
                field.basePrice = pprice;
                field.isRequired = true;
                field.description = pdesc;
                field.inputs = null;

                if (!field.inputType)
                    field.inputType = "singleproduct";

                if (field.inputType == "singleproduct" || field.inputType == "hiddenproduct" || field.inputType == "calculation") {
                    //convert field id to a number so it isn't treated as a string
                    //caused concatenation below instead of addition
                    field_id = parseFloat(field.id);
                    field.inputs = [
                        new Input(field_id + 0.1, <?php echo json_encode( esc_html__( 'Name', 'gravityforms' ) ); ?>), 
                        new Input(field_id + 0.2, <?php echo json_encode( esc_html__( 'Price', 'gravityforms' ) ); ?>), 
                        new Input(field_id + 0.3, <?php echo json_encode( esc_html__( 'Quantity', 'gravityforms' ) ); ?>)
                    ];
                    field.enablePrice = null;
                }
                $('.infprodBTN').removeClass('selected');

            break;
            <?php
        }
    }
}

new GF_Field_Infusionsoft_Products;