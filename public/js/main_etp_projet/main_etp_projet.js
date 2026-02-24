function mainGetIdProject() {
    $.ajax({
      type: "get",
      url: "/etp/employes/getIdEtp",
      dataType: "json",
      success: function (res) {
        //$('#main_project_get_id').val( res[0].idCustomer ) ; 
        $('#main_etp_get_id').val( res[0].idCustomer ) ; 
        console.log('ici',res[0].idCustomer);
      }
    });
  }

  
  // var btnNextEtp = $('#main_next_btn_etp');
  // btnNextEtp.on('click', function () {
  //    mainGetFirstModules($('#main_project_get_id').val());
  //    console.log('btnNextEtp-->',btnNextEtp);
  //   $('#smartwizard-intra').smartWizard("next");
  // });

  // var btnNextCours = $('#main_next_btn_cours');
  // btnNextCours.on('click', function () {
  //   var mainGetFirstModules = $('#smartwizard-intra').smartWizard("next");
  //   console.log('btnNextCours-->',mainGetFirstModules);
  // });