let $ = jQuery;

let meta_data = g2infdata.meta

let default_inf_fields = ['country_code-addresses', 'field-addresses', 'line1-addresses', 'line2-addresses', 'locality-addresses', 'postal_code-addresses', 'region-addresses', 'zip_code-addresses', 'zip_four-addresses', 'birthday', 'contact_type', 'date_created', 'family_name', 'given_name', 'job_title', 'last_updated', 'lead_source_id', 'middle_name', 'notes', 'opt_in_reason', 'preferred_name', 'prefix', 'suffix', 'fax_numbers', 'phone_numbers']

let custom_fields_meta = JSON.parse(g2infdata.custom_fields)
let temp_custom_fields = []
for (var key in custom_fields_meta) {
  let value = custom_fields_meta[key].label + '-custom_fields-' + JSON.stringify(custom_fields_meta[key])
  temp_custom_fields.push(value)
}
let new_inf_fields = default_inf_fields.concat(temp_custom_fields)

let store = {
  data: {
    form_id             : (meta_data.hasOwnProperty('_g2inf_gravity_form_id')) ? meta_data._g2inf_gravity_form_id[0] : 0,
    contact_email       : (meta_data.hasOwnProperty('_contact_email')) ? meta_data._contact_email[0] : '',
    contact_email_text  : (meta_data.hasOwnProperty('_contact_email_text')) ? meta_data._contact_email_text[0] : '',
    inf_fields          : new_inf_fields,
    form_fields         : [] ,
    mapped_form_fields  : (meta_data.hasOwnProperty('_mapped_form_fields') && meta_data._mapped_form_fields != '') ? JSON.parse(meta_data._mapped_form_fields): [],
    mapped_inf_fields   : (meta_data.hasOwnProperty('_mapped_inf_fields')) ? JSON.parse(meta_data._mapped_inf_fields) : []
  }
}

Vue.component('g2inf-mapping', {
  data () {
    return {
      gf_field_selected: '',
      inf_field_selected: ''
    }
  },
  props: {
    gfFields: {
      type: Array
    },
    infFields: {
      type: Array
    },
    gfFieldValue: '',
    infFieldValue: ''
  },
  mounted () {
    this.gf_field_selected = (this.gfFieldValue) ? this.gfFieldValue : this.gfFields[0].form_id
    this.inf_field_selected = (this.infFieldValue) ? this.infFieldValue : this.infFields[0]
  },
  template: `<div class="mapping-wrapper">
              <div class="pdf-field-select">
                <select class="form-control" name="_mapped_form_fields[]" v-model="gf_field_selected">
                  <option v-for="(gfField, index) in gfFields" :value="gfField.field_id">{{gfField.label}}</option>
                </select>
              </div>
              <div class="form-field-select">
                <select class="form-control" name="_mapped_inf_fields[]" v-model="inf_field_selected">
                  <option v-for="(infField, index) in infFields" :value="infField">{{infField.split('-')[0].replace('_', ' ')}}</option>
                </select>
              </div>
            </div>`
})

// general info vue handle
new Vue({
  el: '#g2inf_general_info',

  data() {
    return {
      shared: store,
      form_id: 0,
      contact_email: 'custom',
      contact_email_text: '',
      form_emails: [],
      // inf_fields: [ 'addresses', 'birthday','company' ]
    }
  },

  created() {
    if (this.shared.data.form_id) {
      this.form_id = this.shared.data.form_id
      this.formChange();
    }
    this.contact_email = (this.shared.data.contact_email) ? this.shared.data.contact_email : 'custom'
    this.contact_email_text = this.shared.data.contact_email_text
  },

  watch: {
    form_id: function(newVal) {
      store.data.form_id = newVal
    }
  },

  methods: {
    formChange () {
      let self = this
      let id = this.form_id

      $.post(
        g2infdata.ajaxurl,
        { 
        data: { 
          'form_id' : id, 
        },
        action : 'get_form_fields'
        }, 
        function( result, textStatus, xhr ) {
          store.data.form_fields = JSON.parse(result);

          let tempEmailArr = [];
          let fields = JSON.parse(result);
          for (var key in fields) {
            if (fields[key].type === 'email') {
              tempEmailArr.push(fields[key])
            }
          }
          self.form_emails = tempEmailArr
          // end
        }).fail(function() {
          // 
      });
    }
  }
})

// mapping vue handle
new Vue({
  el: '#g2inf_mapping',

  data() {
    return {
      shared: store,
      mappings: [],
      mapping_count: 0
    }
  },

  created() {
    for (var key in this.shared.data.mapped_form_fields) {
      let mapping = {
        'mapped_form_field': this.shared.data.mapped_form_fields[key],
        'mapped_inf_field': this.shared.data.mapped_inf_fields[key]
      }
      this.mappings.push(mapping)
    }
  },

  methods: {
    addMapping (pdffield, formfield) {
      let mapping = {
        'mapped_form_field': pdffield.field_id,
        'mapped_inf_field': formfield
      }
      this.mappings.push(mapping)
    },
    removeMapping (event, index) {
      var self = event.currentTarget;
      $(self).parent().remove();
    }
  }
})