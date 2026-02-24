function addReferent() {
  $.ajax({
    type: "post",
    url: "/etp/referents",
    data: {
      emp_matricule: $('#main_ref_matricule').val(),
      emp_name: $('#main_ref_name').val(),
      emp_firstname: $('#main_ref_firstname').val(),
      emp_email: $('#main_ref_email').val(),
      emp_phone: $('#main_ref_phone').val()
    },
    dataType: "json",
    beforeSend: function () {

    },
    success: function () {

    },
    success: function (res) {
      if (res.status == 200) {
        toastr.success(res.message, 'Succès', {
          timeOut: 1500
        });
        location.reload();
      } else if (res.status == 411) {
        console.log(res);
        
        $('#erreurRef').append(`<div class="border-[1px] border-red-400 bg-red-100 rounded-md w-full flex items-center gap-3 py-2">
                                    <i class="fa-solid fa-circle-info text-red-600 text-lg pl-4"></i>
                                    <p id="msgErrorRef" class="text-red-900"></p>
                                  </div>`);
        $('#msgErrorRef').text(res.error)
        $('#error_main_ref_matricule').text(res.error.emp_matricule);
        $('#error_main_ref_name').text(res.error.emp_name);
        $('#error_main_ref_email').text(res.error.emp_email);

        $('.main_ref_matricule').addClass('border-red-500');
        $('.main_ref_name').addClass('border-red-500');
        $('.main_ref_email').addClass('border-red-500');
        toastr.error('Erreur inconnue !', 'Erreur', {
          timeOut: 1500
        });
      }
    }
  });
}

function mainEditReferent(idEmploye) {
  $.ajax({
    type: "get",
    url: "/etp/referents/" + idEmploye + "/edit",
    dataType: "json",
    success: function (res) {
      var photoReferent = $('.main_referent_photo_detail');
      photoReferent.html('');
      if (res.emp.photo != null) {
        photoReferent.append(`<img class="w-24 h-24 object-cover rounded-full" src="${digitalOcean}/img/referents/` + res.emp.photo + `" alt="" width="50px" height="50px">`);
      } else {
        photoReferent.append(`<div class="w-24 h-24 rounded-full bg-gray-200 text-gray-600 font-semibold flex items-center text-2xl justify-center relative">
                                      `+ res.emp.initialName + `
                                      </div>`);
      }

      $('.main_referent_name_detail').text(res.emp.name);
      $('.main_referent_firstname_detail').text(res.emp.firstName);
      $('.main_referent_email_detail').text(res.emp.email);
      $('.main_referent_phone_detail').text(res.emp.phone);
      $('.main_referent_matricule_detail').text(res.emp.matricule);
      $('.main_referent_fonction_detail').text(res.emp.fonction);

      $('.main_ref_id_edit').val(res.emp.idEmploye);
      $('#main_ref_matricule_edit').val(res.emp.matricule);
      $('#main_ref_name_edit').val(res.emp.name);
      $('#main_ref_firstname_edit').val(res.emp.firstName);
      $('#main_ref_email_edit').val(res.emp.email);
      $('#main_ref_phone_edit').val(res.emp.phone);
      $('#main_ref_fonction_edit').val(res.emp.fonction);
      $('#main_ref_addrlot_edit').val(res.emp.user_addr_lot);
      $('#main_ref_addrqrt_edit').val(res.emp.user_addr_quartier);
      // $('#main_ref_addrville_edit').val(res.emp.fonction);
      $('#main_ref_addrcp_edit').val(res.emp.user_addr_code_postal);
    }
  });
}

function mainUpdateReferent() {
  var idEmploye = $('.main_ref_id_edit').val();
  console.log(idEmploye);

  $.ajax({
    type: "patch",
    url: "/etp/referents/" + idEmploye,
    data: {
      emp_matricule: $('#main_ref_matricule_edit').val(),
      emp_name: $('#main_ref_name_edit').val(),
      emp_firstname: $('#main_ref_firstname_edit').val(),
      emp_email: $('#main_ref_email_edit').val(),
      emp_phone: $('#main_ref_phone_edit').val(),
      emp_fonction: $('#main_ref_fonction_edit').val(),
      emp_lot: $('#main_ref_addrlot_edit').val(),
      emp_quartier: $('#main_ref_addrqrt_edit').val(),
      emp_code_postal: $('#main_ref_addrcp_edit').val()
    },
    dataType: "json",
    success: function (res) {
      if (res.success) {
        toastr.success(res.success, 'Succès', {
          timeOut: 1500
        });
        location.reload();
      } else if (res.error) {
        toastr.error(res.error, 'Erreur', {
          timeOut: 1500
        });
      }
    }
  });
}

function mainUpdateReferentPassword() {
  var idEmploye = $('.main_ref_id_edit').val();

  $.ajax({
    type: "PATCH",
    url: "/cfp/referents/updatePassword/" + idEmploye,
    data: {
      password: $('#main_ref_password_edit').val(),
      emp_email: $('#main_ref_email_edit').val(),
      _token: $('meta[name="csrf-token"]').attr('content')
    },
    dataType: "json",
    beforeSend: function () {
      $('.loading_now').append(`<div id="main_img_loading_part" class="spinner-grow text-primary" role="status">
          <span class="sr-only">Loading...</span>
      </div>`);
    },
    success: function (res) {
      if (res.success) {
        toastr.success(res.success, 'Succès', {
          timeOut: 1500
        });
        location.reload();
      } else if (res.error) {
        toastr.error(res.error, 'Erreur', {
          timeOut: 1500
        });
      }
    },
    error: function (xhr, status, error) {
      toastr.error('Une erreur interne est survenue.', 'Erreur', {
        timeOut: 1500
      });
      console.error(xhr.responseText);
    }
  });
}

function closeReferentMain() {
  $("#screenReferent").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
  $("#modalReferent").hide();
  sessionStorage.removeItem('modalReferentState');
}