function addMobileMoneyAcount() {
    $.ajax({
        type: "post",
        url: "/cfp/mobilemoneyAcount",
        data: {
            mm_idCustomer: $('#mm_idCustomer').val(),
            mm_phone: $('#main_mm_phone').val(),
            mm_operateur: $('#main_mm_operateur').val(),
            mm_titulaire: $('#main_mm_titulaire').val(),
        },
        dataType: "json",
        success: function (res) {
            toastr.success(res.success, 'Succès', { timeOut: 1500 });
            location.reload();
        },
        error: function (xhr) {
            // Gérer les erreurs de validation
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                $('.error_mm_titulaire').text(errors.mm_titulaire);
                $('.error_mm_operateur').text(errors.mm_operateur);
                $('.error_mm_phone').text(errors.mm_phone);
            } else {
                toastr.error(
                    "Une erreur est survenue lors de la création de votre compte mobile money.",
                    'Erreur', {
                    timeOut: 1500
                });
            }
        }
    });
}