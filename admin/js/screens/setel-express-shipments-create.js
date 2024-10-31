import $ from 'jquery'
import { createApp } from 'vue/dist/vue.esm-bundler'
import useVuelidate from '@vuelidate/core'
import { helpers, required, integer, minLength, minValue, numeric } from '@vuelidate/validators'

$(document).ready(() => {
  const $body = $('body')
  if ($body.is('.setel-express_page_setel-express-shipments-create')) {
    initForm()
  }
})

function initForm () {
  const $wrap = $('#setel-express-shipments-create-wrap')
  const $form = $('#setel-express-shipments-create-form')

  window.app1 = createApp({
    setup () {
      return { v$: useVuelidate() }
    },
    data () {
      return {
        input: {},
        supportedSenderPostcodes: [],
        supportedReceiverPostcodes: [],
        phoneCountries: []
      }
    },
    validations () {
      return {
        input: {
          initialPickupType: { required },
          senderName: { required },
          senderPhoneNumber: {
            required,
            validatePhoneCountryCode: validatePhoneCountryCode(this),
            minLength: minLength(11)
          },
          senderAddress1: { required },
          senderAddress2: {},
          senderPostcode: {
            required,
            includes: (value) => !helpers.req(value) || this.supportedSenderPostcodes.includes(value)
          },
          senderCity: { required },
          senderState: { required },
          shipments: {
            $each: helpers.forEach({
              orderId: { required },
              receiverName: { required },
              receiverPhoneNumber: {
                required,
                validatePhoneCountryCode: validatePhoneCountryCode(this),
                minLength: minLength(11)
              },
              receiverAddress1: { required },
              receiverAddress2: {},
              receiverPostcode: {
                required,
                includes: (value) => !helpers.req(value) || this.supportedReceiverPostcodes.includes(value)
              },
              receiverCity: { required },
              receiverState: { required },
              noOfParcels: { required, integer, min: minValue(1) },
              parcelWeight: { required, numeric, min: minValue(0.0001) },
              parcelSize: { required },
              instructions: {},
            })
          }
        }
      }
    },
    computed: {
      isPickup () {
        return this.input.initialPickupType === 'Pickup'
      }
    },
    methods: {
      async handleSubmit (event) {
        const isFormValid = await this.v$.$validate()
        if (isFormValid) {
          event.target.submit()
        }
      },
      handleSenderPostcodeOnBlur (event) {
        this.loadSupportedReceiverPostcode()
      },
      loadSupportedReceiverPostcode () {
        $.post(window.ajaxurl, {
          action: 'setel-express-api-data-supported-receiver-postcodes',
          senderPostcode: this.input.senderPostcode,
          _nonce: $('#setel-express-api-data-supported-receiver-postcodes-nonce').val()
        }, function (response) {
          this.supportedReceiverPostcodes = response.success ? response.data : []
        })
      }
    },
    mounted () {
      this.input = $form.data('input')
      this.supportedSenderPostcodes = $form.data('supportedSenderPostcodes')
      this.supportedReceiverPostcodes = $form.data('supportedReceiverPostcodes')
      this.phoneCountries = $form.data('phoneCountries')
    }
  }).mount($wrap[0])

}

function validatePhoneCountryCode (vm) {
  return (value) => !helpers.req(value) || vm.phoneCountries.some((phoneCountry) => value.startsWith(phoneCountry.prefixed_code))
}