<div class="wrap wrap-<?= $_GET['tab'] ?>">

    <h2>Gravity 2 Infusionsoft Add-Ons</h2>

    <h2 class="nav-tab-wrapper">

        <a href="<?= admin_url( 'edit.php?post_type=birchtree_g2inf&page=g2inf-addons' ); ?>" class="nav-tab <?= ($_GET['page'] == 'g2inf-addons' && !isset($_GET['tab'])) ? 'nav-tab-active' : '' ?>"><?php _e( 'Products', 'gravity-to-infusionsoft' ) ?></a>

    </h2>

    <?php 
        $client_id = isset($g2inf_settings['client_id']) ? $g2inf_settings['client_id'] : '';
        $client_secret = isset($g2inf_settings['client_secret']) ? $g2inf_settings['client_secret'] : '';
        $token = isset($g2inf_settings['token']) ? $g2inf_settings['token'] : '';
        $products = isset($g2inf_settings['products']) ? $g2inf_settings['products'] : '';
        $use_products = isset($g2inf_settings['products_use_status']) ? $g2inf_settings['products_use_status'] : '';
        $arr_prod = (array) json_decode($products);

        $license_is_active = false;
        if(!empty($g2inf_licenses)) {
            foreach($g2inf_licenses as $key => $val) {
                if (strpos($key, '_license_key') !== false) {
                    $key = str_replace("_license_key", "_license_active" , $key);
                    if(get_option($key)) {
                        $license_is_active = true;
                    }
                }
            }
        }
    ?>

    <br>

    <?php 
        if(!$client_id || !$client_secret || !$token) {
    ?>
            <h1>Please configure the Infusionsoft integration properly.</h1>
            <h3><a href="<?= admin_url( 'edit.php?post_type=birchtree_g2inf&page=g2inf-settings' ); ?>"><?php _e( 'Go to Settings', 'gravity-to-infusionsoft' ) ?></a></h3>
    <?php
        } else {
            if($license_is_active) {

                if(!$products) { ?>
                    <h1>No Infusion Product(s) retrieved! Please synchronize product data.</h1>
                    <h3><a href="<?= admin_url( 'edit.php?post_type=birchtree_g2inf&page=g2inf-settings' ); ?>"><?php _e( 'Go to Settings', 'gravity-to-infusionsoft' ) ?></a></h3>
    <?php
                } else {
    ?>
                    <div class="toggle-infusion-products">
                        <label class="switch">
                            <input type="checkbox" <?= ($use_products == 'true') ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                        <b>Use Infusion products to Gravity Form</b>
                    </div>

                    <table id="infusionProductsTable">
                        <thead>
                            <tr>
                                <th align="left">Name</th>
                                <th align="left">Description</th>
                                <th align="left">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                foreach($arr_prod as $prod) { 
                                    $allSpaces = false;
                                    if (strlen(trim($prod->product_desc)) == 0) {
                                        $allSpaces = true;
                                    }
                            ?>
                            <tr>
                                <td><?= $prod->product_name ?></td>
                                <td><?= ($prod->product_desc && $allSpaces == false) ? $prod->product_desc : $prod->product_short_desc ?></td>
                                <td><?= $prod->product_price ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
    <?php
                } 
            } else {
    ?>
                <h1>Please supply and activate a valid license key to be able to use Gravity to InfusionSoft Add-Ons.</h1>
                <h3><a href="<?= admin_url( 'edit.php?post_type=birchtree_g2inf&page=g2inf-settings&tab=licenses' ); ?>"><?php _e( 'Go to Settings > Licenses Tab', 'gravity-to-infusionsoft' ) ?></a></h3>
    <?php
            }
        } 
    ?>

</div>