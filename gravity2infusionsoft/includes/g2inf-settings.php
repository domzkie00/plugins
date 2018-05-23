<br />
<?php
    $g2inf_settings = get_option('g2inf_settings');
    $client_id            = isset($g2inf_settings['client_id']) ? $g2inf_settings['client_id'] : '';
    $client_secret        = isset($g2inf_settings['client_secret']) ? $g2inf_settings['client_secret'] : '';
    $token                = isset($g2inf_settings['token']) ? $g2inf_settings['token'] : '';
    $tags                = isset($g2inf_settings['tags']) ? $g2inf_settings['tags'] : '';
    $custom_fields                = isset($g2inf_settings['custom_fields']) ? $g2inf_settings['custom_fields'] : '';
    $products                = isset($g2inf_settings['products']) ? $g2inf_settings['products'] : '';
?>
<table class="form-table">
    <tbody>
        <tr class="form-field form-required term-name-wrap">
            <th scope="row">
                <label>Client ID</label>
            </th>
            <td>
                <input type="text" name="g2inf_settings[client_id]" size="40" width="40" value="<?= $client_id ?>">
            </td>
        </tr>
        <tr class="form-field form-required term-name-wrap">
            <th scope="row">
                <label>Client Secret</label>
            </th>
            <td>
                <input type="text" name="g2inf_settings[client_secret]" size="40" width="40" value="<?= $client_secret ?>">
            </td>
        </tr>
        <tr class="form-field form-required term-name-wrap">
            <th scope="row">
                <label>Token</label>
            </th>
            <td>
                <textarea rows="5" readonly="" name="g2inf_settings[token]"><?= $token ?></textarea>
            </td>
        </tr>
        <tr class="form-field form-required term-name-wrap">
            <th scope="row">
                <label>Tags</label>
            </th>
            <td>
                <textarea rows="5" readonly="" name="g2inf_settings[tags]"><?= $tags ?></textarea>
            </td>
        </tr>
        <tr class="form-field form-required term-name-wrap">
            <th scope="row">
                <label>Custom Fields</label>
            </th>
            <td>
                <textarea rows="5" readonly="" name="g2inf_settings[custom_fields]"><?= $custom_fields ?></textarea>
            </td>
        </tr>
        <tr class="form-field form-required term-name-wrap">
            <th scope="row">
                <label>Products</label>
            </th>
            <td>
                <textarea rows="5" readonly="" name="g2inf_settings[products]"><?= $products ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p>
    <?php if (!empty($client_id) && !empty($client_secret)): ?>
    <a href="<?= admin_url( 'admin.php?page=g2inf-settings&g2infsettingsaction=getaccesstoken' ); ?>" class="button button-secondary">Get Access Token</a>

        <?php if (!empty($token)): ?>
        <a href="<?= admin_url( 'admin.php?page=g2inf-settings&g2infsettingsaction=syncdata' ); ?>" class="button button-secondary">Sync Tags</a>
        <?php endif; ?>

        <?php if (!empty($token)): ?>
        <a href="<?= admin_url( 'admin.php?page=g2inf-settings&g2infsettingsaction=synccustomfields' ); ?>" class="button button-secondary">Sync Custom Fields</a>
        <?php endif; ?>

        <?php if (!empty($token)): ?>
        <a href="<?= admin_url( 'admin.php?page=g2inf-settings&g2infsettingsaction=syncproducts' ); ?>" class="button button-secondary">Sync Products</a>
        <?php endif; ?>

    <?php endif; ?>
</p>