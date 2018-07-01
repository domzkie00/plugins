<div class="wrap wrap-<?= $_GET['tab'] ?>">

    <h2>Gravity 2 Infusionsoft Settings</h2>

    <h2 class="nav-tab-wrapper">

        <a href="<?= admin_url( 'edit.php?post_type=birchtree_g2inf&page=g2inf-settings' ); ?>" class="nav-tab <?= ($_GET['page'] == 'g2inf-settings' && !isset($_GET['tab'])) ? 'nav-tab-active' : '' ?>"><?php _e( 'Settings', 'gravity-to-infusionsoft' ) ?></a>

        <a href="<?= admin_url( 'edit.php?post_type=birchtree_g2inf&page=g2inf-settings&tab=licenses' ); ?>" class="nav-tab <?= ($_GET['page'] == 'g2inf-settings' && $_GET['tab'] == 'licenses') ? 'nav-tab-active' : '' ?>"><?php _e( 'Licenses', 'gravity-to-infusionsoft' ) ?></a>

        <!-- <a href="<?= admin_url( 'edit.php?post_type=birchtree_g2inf&page=g2inf-settings&tab=addons' ); ?>" class="nav-tab <?= ($_GET['page'] == 'g2inf-settings' && $_GET['tab'] == 'addons') ? 'nav-tab-active' : '' ?>"><?php _e( 'Add-ons', 'gravity-to-infusionsoft' ) ?></a>

        <a href="<?= admin_url( 'edit.php?post_type=birchtree_g2inf&page=g2inf-settings&tab=system-check' ); ?>" class="nav-tab <?= ($_GET['page'] == 'g2inf-settings' && $_GET['tab'] == 'system-check') ? 'nav-tab-active' : '' ?>"><?php _e( 'System Check', 'gravity-to-infusionsoft' ) ?></a> -->

    </h2>

    <form method="post" action="options.php">
        <?php
            if (isset($_GET['tab']) && $_GET['tab'] == 'licenses') {
                settings_fields( 'g2inf_licenses' );
                do_settings_sections( 'g2inf_licenses' );
                include_once(G2INF_PATH_INCLUDES . '/g2inf-licenses.php');
            } else {
                settings_fields( 'g2inf_settings' );
                do_settings_sections( 'g2inf_settings' );
                include_once(G2INF_PATH_INCLUDES . '/g2inf-settings.php');
            }
        ?>
        <?php submit_button(); ?>
    </form>

</div>