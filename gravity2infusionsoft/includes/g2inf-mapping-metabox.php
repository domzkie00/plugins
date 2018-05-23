<div v-if="!shared.data.form_id">
    <div class="select-warning">
        Please select a form to start mapping.
    </div>
</div>
<div v-else>
    <div id="mapping-labels">
        <div class="pdf-fields-label">
            <label>Form Fields</label>
        </div>
        <div class="form-fields-label">
            <label style="margin-left: -29px;">Infusionsoft Fields</label>
        </div>
    </div>
    <div v-for="(mapping, index) in mappings">
        <g2inf-mapping :gf-fields="shared.data.form_fields" :gf-field-value="mapping.mapped_form_field" :inf-fields="shared.data.inf_fields"  :inf-field-value="mapping.mapped_inf_field"></g2inf-mapping>
        <button class="button button-secondary mapping-remove" @click.prevent="removeMapping($event, index)">
            <span class="dashicons dashicons-minus"></span>
        </button>
    </div>

    <button class="button button-secondary" @click.prevent="addMapping(shared.data.form_fields[0], shared.data.inf_fields[0])">Add Mapping</button>
</div>