$(document).ready(function () {
    getDomainFormations();
  });
  
  function getDomainFormations() {
    $.ajax({
      type: "get",
      url: "/etp/modules/domaine/getDomainFormations",
      dataType: "json",
      success: function (res) {
        var domaine = $('#id_domaine_formation_main');
        domaine.html('');
        domaine.append(`<option value="null" selected disabled>Sélectionner une domaine</option>`);
        $.each(res.domaineFormations, function (key, val) {
          domaine.append(`<option value="` + val.idDomaine + `">` + val.nomDomaine + `</option>`);
        });
  
      }
    });
  }
  
  function mainModuleStore() {
    $.ajax({
      type: "post",
      url:"/etp/modules",
      data: {
        module_reference: $('.module_reference').val(),
        module_name: $('.module_name').val(),
        module_subtitle: $('.module_subtitle').val(),
        module_tag: $('.module_tag').val(),
        module_dureeH: $('.module_dureeH').val(),
        module_dureeJ: $('.module_dureeJ').val(),
        module_min_appr: $('.module_min_appr').val(),
        module_max_appr: $('.module_max_appr').val(),
        id_domaine_formation: $('#id_domaine_formation_main').val()
      },
      dataType: "json",
      success: function (res) {
        if (res.success) {
          sessionStorage.removeItem('modalCoursState');
          window.location.replace('/etp/modules');
        } else {
          $('#error_id_domaine_formation_main').text(res.id_domaine_formation);
          $('#error_module_name').text(res.module_name);
          $('#error_module_subtitle').text(res.module_subtitle);
  
          $('.id_domaine_formation_main').addClass('border-red-500');
          $('.module_name').addClass('border-red-500');
          $('.module_subtitle').addClass('border-red-500');
          console.log(res);
        }
      }
    });
  }
  
  function clearFields() {
    $('.module_reference').val();
    $('.module_name').val();
    $('.module_subtitle').val();
    $('.module_price').val();
    $('.module_prix_groupe').val();
    $('.module_dureeH').val();
    $('.module_dureeJ').val();
    $('.module_min_appr').val();
    $('.module_max_appr').val();
  }
  
  function closeCoursMain() {
    $("#screenCours").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
    $("#modalCours").hide();
    sessionStorage.removeItem('modalCoursState');
  }