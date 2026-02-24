function mainStoreProject() {
  if ($('#main_project_reservation').is(':checked')) {
    var reservation = 1;
  } else {
    var reservation = 0;
  }

  $.ajax({
    type: "post",
    url: "/cfp/projets",
    data: {
      project_reference: $('#main_project_rerefence').val(),
      project_title: $('#main_project_title').val(),
      project_description: $('#main_project_description').val(),
      project_reservation: reservation
    },
    dataType: "json",
    success: function (res) {
      console.log(res);
      if (res.error) {
        toastr.error('Erreur voalohany !', 'Erreur', { timeOut: 1500 });
      } else {
        sessionStorage.removeItem('modalState');
        window.location.replace('/cfp/projets');
        // stp.next();
      }
    }
  });
}