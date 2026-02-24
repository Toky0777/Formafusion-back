function mainSendInvitationFormInterne() {
    var form_name = $('.main_form_name');
    var form_first_name = $('.main_form_first_name');
    var form_phone = $('.main_form_phone');
    var form_email = $('.main_form_email');
  
    $.ajax({
      type: "post",
      url: "/etp/formInternes/store",
      data: {
        form_name: form_name.val(),
        form_first_name: form_first_name.val(),
        form_phone: form_phone.val(),
        form_email: form_email.val(),
      },
      dataType: "json",
      beforeSend: function () {
        $('.main_loading_send_form').append(`<div id="main_img_loading_form" class="spinner-grow text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                      </div>`);
      },
      complete: function () {
        $('#main_img_loading_form').remove();
      },
      success: function (res) {
        if (res.error) {
          // console.log(res.error);
          $('#erreurForm').append(`<div class="border-[1px] border-red-400 bg-red-100 rounded-md w-full flex items-center gap-3 py-2">
                                    <i class="fa-solid fa-circle-info text-red-600 text-lg pl-4"></i>
                                    <p id="msgError" class="text-red-900"></p>
                                  </div>`);
          $('#msgError').text(res.error)
          $('#error_main_form_name').text(res.error.form_name);
          $('#error_main_form_email').text(res.error.form_email);
  
          $('.main_form_name').addClass('border-red-500');
          $('.main_form_email').addClass('border-red-500');
  
        } else if (res.success) {
          form_name.val('');
          form_first_name.val('');
          form_phone.val('');
          form_email.val('');
  
          sessionStorage.removeItem("modalFormateurState");
          window.location.replace("/etp/formInternes");
        }
      }
    });
  }
  
  function closeFormMain() {
    $("#screenFormateur").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
    $("#modalFormateur").hide();
    sessionStorage.removeItem('modalFormateurState');
  }