require('../css/easy_admin.scss')

const $ = jQuery
// ALERT('Hep-hey!')
$(() => {
    $('[data-edit-url]').on('click', function () {
        document.location.href = this.dataset.editUrl
    })
})
