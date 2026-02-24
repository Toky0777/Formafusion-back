function addEmpGrp() {
  $.ajax({
    type: "post",
    url: "/etp/groupes/addEmp",
    data: {
      emp_matricule: $("#emp_matricule").val(),
      emp_name: $("#emp_name").val(),
      emp_firstname: $("#emp_firstname").val(),
      emp_email: $("#emp_email").val(),
      emp_phone: $("#emp_phone").val(),
    },
    dataType: "json",
    success: function (res) {
      if (res.status == 200) {
        toastr.success(res.message, "Succès", {
          timeOut: 1600,
        });
        location.reload();
      } else if (res.status == 401) {
        toastr.error(res.message, "Erreur", {
          timeOut: 1600,
        });
      }
    },
    error: function (error) {
      console.log(error);
    },
  });
}
