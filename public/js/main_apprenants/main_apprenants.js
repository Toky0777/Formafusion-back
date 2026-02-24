// new Choices(document.querySelector(".choices-single"));

function mainGetEtpApprs() {
  $.ajax({
    type: "get",
    url: "/cfp/apprenants/getEtps",
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
    url: "/cfp/apprenants",
    data: {
      idEntreprise: $('#main_appr_idEntreprise').val(),
      emp_matricule: $('#main_appr_matricule').val(),
      emp_name: $('#main_appr_name').val(),
      emp_firstname: $('#main_appr_firstname').val(),
      emp_phone: $('#main_appr_phone').val(),
      emp_email: $('#main_appr_email').val()
    },
    dataType: "json",
    beforeSend: function () {
      $('.main_loading_part').append(`<div id="main_img_loading_part" class="spinner-grow text-primary" role="status">
          <span class="sr-only">Loading...</span>
      </div>`);
    },
    complete: function(){
      $('#main_img_loading_part').remove();
    },
    success: function (res) {
      if (res.status == 200) {
        toastr.success(res.message, 'Apprenant ajouté avec succès', {
          timeOut: 1600
        });

        location.reload();
      } else if (res.status == 422) {
        $('#error_main_appr_matricule').text(res.message.emp_matricule);
        $('#error_main_appr_name').text(res.message.emp_name);
        $('#error_main_appr_idEntreprise').text(res.message.idEntreprise);
        $('.main_appr_matricule').addClass('border-red-500');
        $('.main_appr_name').addClass('border-red-500');
        $('.main_appr_idEntreprise').addClass('border-red-500');
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

// new Choices(document.querySelector(".choices-single"));
function mainGetEtpApprsExcel() {

  $.ajax({
    type: "get",
    url: "/cfp/apprenants/getEtpsExcel",
    dataType: "json",
    success: function (res) {
      var etp = $('#main_appr_idEntreprise_excel');

      etp.html("");
      etp.append(
        `<option value="0" class="text-gray-400" selected disabled>--selectionnez une entreprise--</option>`);
      $.each(res.etps, function (key, val) {
        etp.append(`<option value="` + val.idEtp + `" class="text-gray-400">` + val.etp_name + `</option>`);
      });
    }
  });
}

function mainAddEmplsExcel() {
  const formData = new FormData();
  const fileInput = document.getElementById('dataExcel');
  const file = fileInput.files[0];

  if (!file) {
    toastr.error('Veuillez sélectionner un fichier', 'Erreur', {
      timeOut: 1600
    });
    return;
  }

  formData.append('data', file);
  formData.append('idEntrepriseExcel', $('#main_appr_idEntreprise_excel').val());
  formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

  $.ajax({
    type: "post",
    url: "/cfp/apprenants/addEmpExcel",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    beforeSend: function () {
      $('.main_loading_part').append(`<div class="spinner-grow text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>`);
    },
    success: function (res) {
      toastr.success(res.success, 'Ajout employé avec succès', {
        timeOut: 1600
      });
      window.location.replace("/cfp/apprenants");
    },
    error: function (jqXHR) {
      console.error('ERREUR AJAX:', jqXHR);

      if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
        toastr.error(jqXHR.responseJSON.error, 'Erreur', { timeOut: 4000 });
      } else {
        toastr.error('Une erreur est survenue lors de la soumission', 'Erreur', { timeOut: 1600 });
      }
      // location.reload();
    }

  });
}