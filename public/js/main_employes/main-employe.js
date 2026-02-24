function mainGetEtpEmpls() {
    $.ajax({
      type: "get",
      url: "/etp/employes/getIdEtp",
      dataType: "json",
      success: function (res) {
        $('#idEntreprise').val( res[0].idCustomer ) ;   
      }
    });
  }
  
  function mainAddEmploye() {
    $.ajax({
      type: "post",
      url: "/etp/employes/addEmp",
      data: {
        idEntrepriseGrp: $('#main_append_etp').val(),
        idEntreprise: $('#idEntreprise').val(),
        emp_matricule: $('#main_empl_matricule').val(),
        emp_name: $('#main_empl_name').val(),
        emp_firstname: $('#main_empl_firstname').val(),
        emp_phone: $('#main_empl_phone').val(),
        emp_email: $('#main_empl_email').val(),
      },
      dataType: "json",
      success: function (res) {
        if (res.status == 200) {
          toastr.success(res.message, 'Succès', {
            timeOut: 1600
          });
          location.reload();
        } else if (res.status == 411) {
          console.log(res)
          $('#error_main_empl_matricule').text(res.error.emp_matricule);
          $('#error_main_empl_name').text(res.error.emp_name);
          $('.main_empl_matricule').addClass('border-red-500');
          $('.main_empl_name').addClass('border-red-500');
          $('.main_empl_idEntreprise').addClass('border-red-500');
          toastr.error("Erreur", 'Erreur', {
            timeOut: 1600
          });
        }
      }
    });
  }
  
function closeEmplMain () {
  $("#screenEmploye").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
  $("#modalEmploye").hide();
  sessionStorage.removeItem('modalEmployeState');
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
  formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

  $.ajax({
      type: "post",
      url: "/etp/employes/addEmpExcel",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (res) {
          if (res.success) {
              toastr.success(res.success, 'Employé ajouté avec succès', {
                  timeOut: 1600
              });
              location.reload();
          } else if (res.error) {
              console.log('ERREUR-->', res.error);
              toastr.error(res.error, 'Erreur', {
                  timeOut: 1600
              });
          }
      },
      error: function (jqXHR, textStatus, errorThrown) {
          console.error('ERREUR AJAX:', textStatus, errorThrown);
          toastr.error('Une erreur est survenue lors de la soumission', 'Erreur', {
              timeOut: 1600
          });
      }
  });
}