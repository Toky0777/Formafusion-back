// new Choices(document.querySelector(".choices-single"));

function mainGetEntreprises() {
  $.ajax({
    type: "get",
    url: "/form/apprenants/entreprises",
    dataType: "json",
    success: function (res) {      
      var etp = $('#main_appr_idEntreprise');
      // etp.html(`<option class="text-gray-400"></option>`);
      etp.html("");
      etp.append(
        `<option value="0" class="text-gray-400" selected disabled>--selectionnez une entreprise--</option>`);
      $.each(res.etps, function (key, val) {
        etp.append(`<option value="` + val.idEtp + `" class="text-gray-400">` + val.etp_name + `</option>`);
      });
    }
  });
}

function mainAddApprenant() {
  $.ajax({
    type: "post",
    url: "/form/apprenants",
    data: {
      idEntreprise: $('#main_appr_idEntreprise').val(),
      emp_matricule: $('#main_appr_matricule').val(),
      emp_name: $('#main_appr_name').val(),
      emp_firstname: $('#main_appr_firstname').val(),
      emp_phone: $('#main_appr_phone').val(),
      emp_email: $('#main_appr_email').val(),
      // emp_fonction: $('#main_appr_fonction').val()
    },
    dataType: "json",
    success: function (res) {
      console.log(res);
      
      if (res.status == 200) {
        $('#main_appr_matricule').val("");
        $('#main_appr_name').val("");
        $('#main_appr_firstname').val("");
        $('#main_appr_phone').val("");
        $('#main_appr_email').val("");
        // $('#main_appr_fonction').val("");
        toastr.success(res.message, 'Apprenant ajouté avec succès', {
          timeOut: 1600
        });
        sessionStorage.removeItem('modalApprenantState');
        location.reload();
      } else if (res.status == 422) {
        $('#error_main_appr_matricule').text(res.message.emp_matricule);
        $('#error_main_appr_name').text(res.message.emp_name);
        $('#error_main_appr_idEntreprise').text(res.message.idEntreprise);
        // $('#error_main_appr_fonction').text(res.error.emp_fonction);

        $('.main_appr_matricule').addClass('border-red-500');
        $('.main_appr_name').addClass('border-red-500');
        $('.main_appr_idEntreprise').addClass('border-red-500');
        // $('.main_appr_fonction').addClass('border-red-500');
        toastr.error(res.message, 'Error', {
          timeOut: 1600
        });
      }
    }
  });
}

function closeApprMain() {
  $("#screenApprenant").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
  $("#modalApprenant").hide();
  sessionStorage.removeItem('modalApprenantState');
}

function Str_Random(length = 10) {
  let result = '';
  const characters = '0123456789';

  // Loop to generate characters for the specified length
  for (let i = 0; i < length; i++) {
    const randomInd = Math.floor(Math.random() * characters.length);
    result += characters.charAt(randomInd);
  }
  // return result;
  if ($('#matRandom').is(':checked')) {
    $('#main_appr_matricule').val(result + '--Provisoire');
  } else {
    $('#main_appr_matricule').val('');
  }
}
