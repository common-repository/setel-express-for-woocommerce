module.exports = {
  content: [
    './includes/class-setel-express-api-data.php',
    './includes/class-setel-express-shipment.php',
    './admin/partials/shipments-create.php',
    './admin/partials/shipments-details.php',
  ],
  theme: {
    extend: {
      colors: {
        'status-default': '#525B65',
        'status-default-alt': '#EAEBEE',
        'status-info': '#0171F4',
        'status-info-alt': '#E6F1FF',
        'status-success': '#2ECC71',
        'status-success-alt': '#D5F6E3',
        'status-warning': '#FF7A00',
        'status-warning-alt': '#FFF2E6',
      },
      letterSpacing: {
        px: '1px'
      }
    },
  },
  plugins: [],
  prefix: 'tw-',
  important: true
}
