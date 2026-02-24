$(document).ready(function () {
    if (typeof toastr !== 'undefined' && window.sessionMessages) {
        if (window.sessionMessages.success) {
            toastr.success(window.sessionMessages.success)
            1500;
        }
        if (window.sessionMessages.error) {
            toastr.error(window.sessionMessages.error)
            1500;
        }
        if (window.sessionMessages.info) {
            toastr.info(window.sessionMessages.info)
            1500;
        }
        if (window.sessionMessages.warning) {
            toastr.warning(window.sessionMessages.warning)
            1500;
        }
    }
});
