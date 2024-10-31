import $ from 'jquery'

$(document).ready(() => {
  const $body = $('body')
  if ($body.is('.setel-express_page_setel-express-shipments-details')) {
    initConfirmCancelShipment()
  }

  function initConfirmCancelShipment () {
    const $form = $('#setel-express-shipments-details-cancel-form')
    $form.on('submit', (event) => {
      return confirm(wp.i18n.__('Are you sure you want to cancel this shipment? Take a good look, this action cannot be reversed.'))
    })
  }
})