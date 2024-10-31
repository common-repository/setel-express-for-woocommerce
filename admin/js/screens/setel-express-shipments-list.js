import $ from 'jquery'

$(document).ready(() => {
  const $body = $('body')
  if ($body.is('.edit-php.post-type-setel_shipment')) {
    initConfirmCancelShipmentExtraBulkAction()
  }

  function initConfirmCancelShipmentExtraBulkAction () {
    const $form = $('#posts-filter')
    const $cancelButton = $form.find('[name="action"][value="cancel"]')

    $cancelButton.on('click', (event) => {
      return confirm(wp.i18n.__('Are you sure you want to cancel this shipment? Take a good look, this action cannot be reversed.'))
    })
  }
})