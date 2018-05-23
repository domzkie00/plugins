<div class="form-grp">
    <div class="half first">
        <label>Gravity Form</label> <br />
        <?php
        $forms = GFAPI::get_forms();
        ?>
        <select name="_g2inf_gravity_form_id" v-model="form_id" style="min-width:150px;" @change="formChange">
            <option>---</option>
            <?php
                foreach ($forms as $key => $form) {
                    echo '<option value="'.$form['id'].'">'.$form['title'].'</option>';
                }
            ?>
        </select>
    </div>
    <div class="half">
        <label>Contact Email</label> <br />
        <select name="_contact_email" v-model="contact_email">
            <option value="custom">Custom</option>
            <option v-for="form_email in form_emails" :value="form_email.field_id">{{form_email.label}}</option>
        </select>
        <input type="email" required="required" name="_contact_email_text" placeholder="Enter Email" v-model="contact_email_text" v-if="contact_email === 'custom'" />
    </div>
</div>



