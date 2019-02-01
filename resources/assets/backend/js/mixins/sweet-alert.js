import swal from 'sweetalert2'

export default {
  name: 'swal',
  methods: {
    swalInit () {
      return swal.mixin({
        position: 'top-end',
        toast: true
      })
    },
    swalNotification (type, title) {
      this.swalInit().fire({
        type: type,
        title: title,
        showConfirmButton: false,
        timer: 4000
      })
    },
    swalSaveWarning () {
      return swal.fire({
        title: 'Some work was left unsaved.',
        text: 'Please confirm you\'d like to leave the page without saving.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Leave without saving'
      })
    },
    swalDeleteWarning (title, text, confirmationButtonText) {
      return swal.fire({
        title: title,
        text: text,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmationButtonText
      })
    }
  }
}
