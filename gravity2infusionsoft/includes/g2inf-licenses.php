<?php
$licenses = apply_filters('g2inf_settings_licenses', array());
?>
<br />
<table class="form-table">
    <tbody>
        <?php foreach($licenses as $key => $license): ?>
            <tr>
                <th scope="row"><?= $license['name'] ?></th>
                <td>
                    <?php g2infusion_license_key_callback($license); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>