function addBankAcount() {
  $.ajax({
    type: "post",
    url: "/cfp/bankAcount",
    data: {
      ba_idCustomer: $('#ba_idCustomer').val(),
      ba_account_number: $('#main_ba_account_number').val(),
      ba_name: $('#main_ba_name').val(),
      ba_idPostal: $('#main_ba_idPostal').val(),
      ba_quartier: $('#main_ba_quartier').val(),
      ba_titulaire: $('#main_ba_titulaire').val(),
    },
    dataType: "json",
    success: function (res) {
      toastr.success(res.success, 'Succès', { timeOut: 1500 });
      sessionStorage.removeItem('modalBankAccountState');
      location.reload();
    },
    error: function (xhr) {
      // Gérer les erreurs de validation
      if (xhr.status === 422) {
        const errors = xhr.responseJSON.errors;
        $('.error_ba_titulaire').text(errors.ba_titulaire);
        $('.error_ba_name').text(errors.ba_name);
        $('.error_ba_quartier').text(errors.ba_quartier);
        $('.error_ba_idPostal').text(errors.ba_idPostal);
        $('.error_ba_account_number').text(errors.ba_account_number);
      } else {
        toastr.error(
          "Une erreur est survenue lors de la création de votre compte bancaire.",
          'Erreur', {
          timeOut: 1500
        });
      }
    }
  });
}