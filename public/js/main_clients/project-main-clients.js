function projectMainRcs() {
  var rcs = $('#main_etp_rcs_search').val();

  if (rcs.length !== 0) {
    $.ajax({
      type: "get",
      url: "/cfp/invites/etp/getRcs/" + rcs,
      dataType: "json",
      success: function (res) {
        console.log(res);

        $('.main_rcs_to_append').html('');
        if (res.rcs.length <= 0) {
          $('.main_rcs_to_append').append(`<button
                                              type="button"
                                              onclick="projectMainAddNewEtp('`+ rcs + `')"
                                              class="w-full py-1 flex justify-center items-center text-lg text-gray-400 border-[1px] border-gray-200 rounded-md gap-2 bg-gray-100">
                                              <i class="fa-solid fa-plus"></i>
                                              Ajouter une entreprise
                                            </button>`);
        } else {
          $.each(res.rcs, function (key, val) {
            $('.main_rcs_to_append').append(`<div class="flex flex-col w-full gap-1">
                                            <p onclick="projectMainShowEtpDetail(`+ val.idCustomer + `)" style="padding: 10px 4px; margin-bottom: 2px; background: #d9e7cb; border-radius: 4px; cursor: pointer">` + val.customer_name + " - " + val.customer_rcs + `</p>
                                          </div>`);
          });
        }
      }
    });
  } else {
    $('.main_rcs_to_append').html('');
  }
}

function projectMainShowEtpDetail(idEtp) {
  $.ajax({
    type: "get",
    url: "/cfp/invites/etp/getEtpDetail/" + idEtp,
    dataType: "json",
    success: function (res) {
      const form_control = `bg-white w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400`;
      $('#main_etp_rcs_search').val('');
      $('.main_rcs_to_append').html('');
      $('.main_rcs_to_append').append(`<input type="hidden" class="id_etp_hidden" value="` + res.etp.idCustomer + `"/>
                                      <span class="main_loading_send"></span>
                                      <div class="flex flex-col gap-1 w-full">
                                        <label for="etp_name" class="text-gray-600">Nom de l'entreprise</label>
                                        <input type="text" class="`+ form_control + ` etp_name" value="` + res.etp.customer_name + `">
                                      </div>
                                      <div class="flex flex-col gap-1 w-full">
                                        <label for="etp_rcs" class="text-gray-600">Numéro NIF</label>
                                        <input type="text" class="`+ form_control + ` etp_rcs" value="` + res.etp.customer_rcs + `">
                                      </div>
                                      <div class="flex flex-col gap-1 w-full">
                                        <label for="main_etp_email" class="text-gray-600">E-mail</label>
                                        <input type="email" class="`+ form_control + ` etp_email" value="` + res.etp.customer_email + `">
                                      </div>
                                      <div class="flex flex-col gap-1 w-full">
                                        <label for="etp_referent_name" class="text-gray-600">Nom du responsable</label>
                                        <input type="text" class="`+ form_control + ` etp_referent_name" value="` + res.etp.customer_name + `">
                                      </div>
                                      <div class="flex flex-col gap-1 w-full">
                                        <label for="etp_referent_firstname" class="text-gray-600">Prénom du responsable</label>
                                        <input type="text" class="`+ form_control + ` etp_referent_firstname">
                                      </div>
                                      <div class="w-full inline-flex justify-end pt-2">
                                        <button 
                                          type="button" onclick="hideForm()"
                                          class="focus:outline-none px-3 bg-gray-200 py-2 ml-3 rounded-md text-gray-600 hover:bg-gray-300/80 transition duration-200 text-sm">Annuler</button>
                                        <button 
                                          type="button" 
                                          onclick="projectMainSendInvitation()" 
                                          class="focus:outline-none px-3 bg-[#A462A4] py-2 ml-3 rounded-md text-white hover:bg-[#A462A4]/90 transition duration-200 text-base">Ajouter ce client</button>
                                      </div>`);
    }
  });
}

function projectMainAddNewEtp(rcsVal) {
  const form_control = `bg-white w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400`;
  $('.main_rcs_to_append').html('');
  $('.main_rcs_to_append').append(`<span class="main_loading_send"></span>
                                      <div class="flex flex-col gap-1 w-full">
                                        <label for="etp_name" class="text-gray-600 after:content-['*'] after:ml-0.5 after:text-red-500">Nom de l'entreprise</label>
                                        <input type="text" class="`+ form_control + ` etp_name">
                                        <div id="error_etp_name" class="text-sm text-red-500"></div>
                                      </div>
                                      <div class="flex flex-col gap-1 w-full">
                                        <label for="etp_rcs" class="text-gray-600 after:content-['*'] after:ml-0.5 after:text-red-500">Numéro NIF</label>
                                        <input type="text" value="`+ rcsVal + `" class="` + form_control + ` etp_rcs">
                                        <div id="error_etp_rcs" class="text-sm text-red-500"></div>
                                      </div>
                                      <div class="flex flex-col gap-1 w-full">
                                        <label for="main_etp_email" class="text-gray-600 after:content-['*'] after:ml-0.5 after:text-red-500">E-mail</label>
                                        <input type="email" class="`+ form_control + ` etp_email">
                                        <div id="error_main_etp_email" class="text-sm text-red-500"></div>
                                      </div>
                                      <div class="flex flex-col gap-1 w-full">
                                        <label for="etp_referent_name" class="text-gray-600">Nom du responsable</label>
                                        <input type="text" class="`+ form_control + ` etp_referent_name">
                                      </div>
                                      <div class="flex flex-col gap-1 w-full">
                                      <label for="etp_referent_firstname" class="text-gray-600">Prénom du responsable</label>
                                      <input type="text" class="`+ form_control + ` etp_referent_firstname">
                                    </div>
                                    <div class="w-full inline-flex justify-end pt-2">
                                      <button 
                                        type="button" onclick="hideForm()"
                                        class="focus:outline-none px-3 bg-gray-200 py-2 ml-3 rounded-md text-gray-600 hover:bg-gray-300/80 transition duration-200 text-sm">Annuler</button>
                                      <button 
                                        type="button" 
                                        onclick="projectMainSendInvitation()" 
                                        class="focus:outline-none px-3 bg-[#A462A4] py-2 ml-3 rounded-md text-white hover:bg-[#A462A4]/90 transition duration-200 text-base">Ajouter ce client</button>
                                    </div>`);
}

function projectMainSendInvitation() {
  $.ajax({
    type: "post",
    url: "/cfp/invites/etp",
    data: {
      idEtp: $('.id_etp_hidden').val(),
      etp_rcs: $('.etp_rcs').val(),
      etp_name: $('.etp_name').val(),
      etp_email: $('.etp_email').val(),
      etp_referent_name: $('.etp_referent_name').val(),
      etp_referent_firstname: $('.etp_referent_firstname').val()
    },
    dataType: "json",
    beforeSend: function () {
      $('.main_loading_send').append(`<div id="main_img_loading" class="spinner-grow text-primary" role="status">
                                  <span class="sr-only">Loading...</span>
                                </div>`);
    },
    complete: function () {
      $('#main_img_loading').remove();
    },
    success: function (res) {
      if (res.success) {
        $('.main_rcs_to_append').html('');
        $('#main_etp_rcs_search').val('');
        toastr.success("Client ajouté avec succès", 'Succès', { timeOut: 1500 });
        mainGetAllEtps($('input[name="main_project_get_id"]').val());
        hideForm();
      } else {
        $('#error_etp_name').text(res.error.etp_name);
        $('#error_etp_rcs').text(res.error.etp_rcs);
        $('#error_main_etp_email').text(res.error.etp_email);

        $('.etp_name').addClass('border-red-500');
        $('.etp_rcs').addClass('border-red-500');
        $('.etp_email').addClass('border-red-500');
        toastr.error(res.error, 'Erreur', { timeOut: 1500 });
      }
    },
    error: function (error) {
      console.log(error);
    }
  });
}

function closeClientMain() {
  $("#screenClient").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
  $("#modalClient").hide();
  sessionStorage.removeItem('modalClientState');
}