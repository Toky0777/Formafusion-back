$(document).ready(function () {
    $('#smartwizard').smartWizard({
      theme: 'arrows',
      toolbar: {
        position: 'bottom', // none|top|bottom|both
        showNextButton: false, // show/hide a Next button
        showPreviousButton: false, // show/hide a Previous button
      },
      anchor: {
        enableNavigation: true, // Enable/Disable anchor navigation 
        enableNavigationAlways: false, // Activates all anchors clickable always
        enableDoneState: true, // Add done state on visited steps
        markPreviousStepsAsDone: true, // When a step selected by url hash, all previous steps are marked done
        unDoneOnBackNavigation: false, // While navigate back, done state will be cleared
        enableDoneStateNavigation: true // Enable/Disable the done state navigation
      },
    });
  
    // $('#smartwizard-intra').smartWizard({
    //   theme: 'arrows',
    //   toolbar: {
    //     position: 'bottom', // none|top|bottom|both
    //     showNextButton: false, // show/hide a Next button
    //     showPreviousButton: false, // show/hide a Previous button
    //   },
    //   anchor: {
    //     enableNavigation: true, // Enable/Disable anchor navigation 
    //     enableNavigationAlways: false, // Activates all anchors clickable always
    //     enableDoneState: true, // Add done state on visited steps
    //     markPreviousStepsAsDone: true, // When a step selected by url hash, all previous steps are marked done
    //     unDoneOnBackNavigation: false, // While navigate back, done state will be cleared
    //     enableDoneStateNavigation: true // Enable/Disable the done state navigation
    //   },
    // });
    // function mainGetIdProject() {
    //     $.ajax({
    //       type: "get",
    //       url: "/etp/employes/getIdEtp",
    //       dataType: "json",
    //       success: function (res) {
    //         $('#main_project_get_id').val( res[0].idCustomer ) ; 
    //         console.log('ici')  
    //       }
    //     });
    //   }
    // Bouton Next
    // var btnNextProject = $('#main_next_btn_project');
    // btnNextProject.on('click', function () {
    //   mainStoreProject();
    //   console.log('STORE!!!')
    // });
  
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





    var btnNextFormation = $('#main_next_btn_project_inter');
    btnNextFormation.on('click', function () {
      //var mainGetFirstModules = $('#smartwizard').smartWizard("next");
      console.log('btnNextFormation activé-->')

      mainStoreProject();

    
    });




    // Bouton Prev
    $('.prevBtn').on('click', function () {
      console.log('prevBtn activé-->');
      $('#smartwizard').smartWizard("prev");
    });

    // Entreprise
    $('#ajoutEtp').click(function (e) {
      console.log('ajoutEtp-->');
      e.preventDefault();
      $('#formEtp').toggleClass(`h-max`, `h-0`);
    });
  
    // Cours
    $('#ajoutModule').click(function (e) {
      console.log('ajoutModule-->');
      e.preventDefault();
      $('#formModule').toggleClass(`h-max`, `h-0`);
    });
  
    // Formateur
    $('#ajoutForm').click(function (e) {
      console.log('ajoutForm-->');
      e.preventDefault();
      $('#formFormateur').toggleClass(`h-max`, `h-0`);
    });
  
  });
  
  function mainSearchCours(id, ul) {
    var value = $('#' + id);
    $("#" + ul + " li").filter(function () {
      
      $(this).toggle($(this).text().toLowerCase().indexOf(value.val().toLowerCase()) > - 1)
    });
  }

  function mainSearch(id, table) {
    var value = $('#' + id);
    $("#" + table + " tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value.val().toLowerCase()) > - 1)
    });
  }
  
  // $(function () {
  //   // SmartWizard initialize
  
  // });
  
  function mainStoreProject() {
    // if ($('#main_project_reservation').is(':checked')) {
    //   var reservation = 1;
    // } else {
    //   var reservation = 0;
    // }
  
    $.ajax({
      type: "post",
      url: "/etp/projets",
      data: {
        project_reference: $('#main_project_rerefence_inter').val(),
        project_title: $('#main_project_title_inter').val(),
        project_description: $('#main_project_description_inter').val(),
      },
      dataType: "json",
      success: function (res) {
        if (res.status == 200) {
          var main_project_get_id = $('#main_project_get_id').val(res.idProjet);
          console.log('main_project_get_id-->',main_project_get_id);
          mainGetAllEtps(res.idProjet);
          $('#smartwizard').smartWizard("next");
        
                  //load Modules internes...
          mainGetFirstModules($('#main_project_get_id').val());
          console.log('mainGetFirstModules load-->');

        } else {
          console.log(res.error);
          $('#erreurProjet').append(`<div class="border-[1px] border-red-400 bg-red-100 rounded-md w-full flex items-center gap-3 py-2">
                                    <i class="fa-solid fa-circle-info text-red-600 text-lg pl-4"></i>
                                    <p id="msgErrorProjet" class="text-red-900"></p>
                                  </div>`);
        $('#msgErrorProjet').text(res.error)

          $('#smartwizard').smartWizard("prev");
        

          $("#error_project_title").text(res.error.project_title);
          $(".main_project_title").addClass("border-red-500");
          toastr.error("Erreur !", 'Erreur', { timeOut: 1500 });
        }
      },
      error: function (error) {
        console.log(error);
      }
    });
  }
  
  function mainGetAllEtps(idProjet) {
    $.ajax({
      type: "get",
      url: "/etp/invites/etp/getAllEtps",
      dataType: "json",
      success: function (res) {
        var get_all_etps = $('#main_get_all_etps');
        get_all_etps.html('');
  
        var get_all_etps_selected = $('#main_get_all_etps_selected');
        get_all_etps_selected.html('');
  
        if (res.etps.length <= 0) {
          get_all_etps.append(`<x-no-data texte="Aucun résultat"></x-no-data>`);
        } else {
          var val_idEtp = $('#main_etp_get_id').val();
          $.each(res.etps, function (key, val) {
            if (val_idEtp != null) {
              if (val_idEtp == val.idEtp) {
                get_all_etps_selected.append(`<li
                                      class="list grid grid-cols-5 w-full gap-2 justify-between px-3 py-2 border-[1px] border-gray-100 cursor-pointer hover:bg-gray-50 duration-200 rounded-md !bg-white">
                                      <div class="col-span-4">
                                        <div class="inline-flex items-center gap-2">
                                          <span id="main_photo_etp_`+ val.idEtp + `">
                                            <div class="w-10 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-full uppercase">
                                            `+ val.etp_initial_name + `</div>
                                          </span>
                                          <div class="flex flex-col gap-0">
                                            <p class="font-normal text-base text-gray-700">`+ val.etp_name + `</p>
                                            <p class="text-sm text-gray-400 lowercase">`+ val.etp_email + `</p>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="grid col-span-1 items-center justify-center w-full">
                                      </div>
                                    </li>`);
              } else {
                get_all_etps.append(`<li
                                      class="list grid grid-cols-5 w-full gap-2 justify-between px-3 py-2 border-[1px] border-gray-100 cursor-pointer hover:bg-gray-50 duration-200 rounded-md !bg-white">
                                      <div class="col-span-4">
                                        <div class="inline-flex items-center gap-2">
                                          <span id="main_photo_etp_`+ val.idEtp + `">
                                            <div class="w-10 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-full uppercase">
                                            `+ val.etp_initial_name + `</div>
                                          </span>
                                          <div class="flex flex-col gap-0">
                                            <p class="font-normal text-base text-gray-700">`+ val.etp_name + `</p>
                                            <p class="text-sm text-gray-400 lowercase">`+ val.etp_email + `</p>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="grid col-span-1 items-center justify-center w-full">
                                        <div onclick="mainEtpAssign(`+ idProjet + `,` + val.idEtp + `)"
                                          class="icon w-10 h-10 rounded-full flex items-center justify-center bg-green-100 cursor-pointer hover:bg-green-50 group/icon duration-150">
                                          <i class="fa-solid fa-plus text-green-500 text-sm group-hover/icon:text-green-600 duration-150"></i>
                                        </div>
                                      </div>
                                    </li>`);
              }
            } else {
              get_all_etps.append(`<li
                                      class="list grid grid-cols-5 w-full gap-2 justify-between px-3 py-2 border-[1px] border-gray-100 cursor-pointer hover:bg-gray-50 duration-200 rounded-md !bg-white">
                                      <div class="col-span-4">
                                        <div class="inline-flex items-center gap-2">
                                          <span id="main_photo_etp_`+ val.idEtp + `">
                                            <div class="w-10 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-full uppercase">
                                            `+ val.etp_initial_name + `</div>
                                          </span>
                                          <div class="flex flex-col gap-0">
                                            <p class="font-normal text-base text-gray-700">`+ val.etp_name + `</p>
                                            <p class="text-sm text-gray-400 lowercase">`+ val.etp_email + `</p>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="grid col-span-1 items-center justify-center w-full">
                                        <div onclick="mainEtpAssign(`+ idProjet + `,` + val.idEtp + `)"
                                          class="icon w-10 h-10 rounded-full flex items-center justify-center bg-green-100 cursor-pointer hover:bg-green-50 group/icon duration-150">
                                          <i class="fa-solid fa-plus text-green-500 text-sm group-hover/icon:text-green-600 duration-150"></i>
                                        </div>
                                      </div>
                                    </li>`);
            }
  
            var photo_etp = $('#main_photo_etp_' + val.idEtp);
            photo_etp.html('');
  
            if (val.etp_logo == "" || val.etp_logo == null) {
              photo_etp.append(
                `<div  class="w-10 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-full uppercase">` +
                val.etp_initial_name + `</div>`);
            } else {
              photo_etp.append(`<img src="/img/entreprises/` + val.etp_logo + `" alt="Avatar" class="w-10 h-10 rounded-full mr-4 object-cover">`);
            }
          });
        }
      }
    });
  }
  
  function mainEtpAssign(idProjet, idEtp) {
    $.ajax({
      type: "patch",
      url: "/etp/projets/" + idProjet + "/" + idEtp + "/etp/assign",
      dataType: "json",
      success: function (res) {
        if (res.success) {
          toastr.success(res.success, 'Succès', { timeOut: 1500 });
          mainGetIdEtp(idProjet);
         // mainGetAllEtps(idProjet);
        }
      },
      error: function (error) {
        console.log(error);
      }
    });
  }
  
  function mainGetIdEtp(idProjet) {
    $.ajax({
      type: "get",
      url: "/etp/projets/" + idProjet + "/mainGetIdEtp",
      dataType: "json",
      success: function (res) {
        var main_etp_get_id =  $('#main_etp_get_id').val(res.projet.idEtp);
        console.log("main_etp_get_id",main_etp_get_id);
      },
      error: function (error) {
        console.log(error);
      }
    });
  }
  
  function mainGetRcsProject() {
    var rcs = $('#main_rcs_search_project').val();
    if (rcs.length !== 0) {
      $.ajax({
        type: "get",
        url: "/etp/invites/etp/getRcs/" + rcs,
        dataType: "json",
        success: function (res) {
          $('.main_rcs_to_append_project').html('');
          if (res.rcs.length <= 0) {
            $('.main_rcs_to_append_project').append(`<button
                                                      type="button"
                                                      onclick="mainAddNewEtpProject()"
                                                      class="w-full py-1 flex justify-center items-center text-lg text-gray-400 border-[1px] border-gray-200 rounded-md gap-2 bg-gray-100">
                                                      <i class="fa-solid fa-plus"></i>
                                                      Ajouter une entreprise
                                                    </button>`);
          } else {
            $.each(res.rcs, function (key, val) {
              $('.main_rcs_to_append_project').append(`<div class="flex flex-col w-full gap-1">
                                            <p onclick="mainShowEtpDetailProject(`+ val.idCustomer + `)" style="padding: 10px 4px; margin-bottom: 2px; background: #d9e7cb; border-radius: 4px; cursor: pointer">` + val.customer_name + " - " + val.customer_rcs + `</p>
                                          </div>`);
            });
          }
        }
      });
    } else {
      $('.main_rcs_to_append_project').html('');
    }
  }
  
  function mainAddNewEtpProject(rcsVal) {
    const form_control = `bg-white w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400`;
    $('.main_rcs_to_append_project').html('');
    $('.main_rcs_to_append_project').append(`<hr>
                                        <span class="main_loading_send"></span>
                                        <div class="mb-1">
                                          <label for="etp_name" class="form-label">Nom de l'entreprise</label>
                                          <input type="text" class="`+ form_control + ` etp_name">
                                        </div>
                                        <div class="mb-1">
                                          <label for="etp_rcs" class="form-label">Numéro RCS</label>
                                          <input type="text" value="`+ rcsVal + `" class="` + form_control + ` etp_rcs">
                                        </div>
                                        <div class="mb-1">
                                          <label for="main_etp_email" class="form-label">E-mail</label>
                                          <input type="email" class="`+ form_control + ` etp_email">
                                        </div>
                                        <div class="mb-1">
                                          <label for="etp_referent_name" class="form-label">Nom du responsable</label>
                                          <input type="text" class="`+ form_control + ` etp_referent_name">
                                        </div>
                                        <div class="w-full inline-flex justify-end pt-2">
                                          <button 
                                            type="button"
                                            class="focus:outline-none px-3 bg-gray-200 py-2 ml-3 rounded-md text-gray-600 hover:bg-gray-300/80 transition duration-200 text-sm">Annuler</button>
                                          <button 
                                            type="button" 
                                            onclick="mainSendInvitationProject()" 
                                            class="focus:outline-none px-3 bg-gray-700 py-2 ml-3 rounded-md text-white hover:bg-gray-700/90 transition duration-200 text-base">Ajouter</button>
                                        </div>`);
  }
  
  function mainShowEtpDetailProject(idEtp) {
    $.ajax({
      type: "get",
      url: "/etp/invites/etp/getEtpDetail/" + idEtp,
      dataType: "json",
      success: function (res) {
        const form_control = `bg-white w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400`;
        $('#main_rcs_search_project').val('');
        $('.main_rcs_to_append_project').html('');
        $('.main_rcs_to_append_project').append(`<input type="hidden" class="id_etp_hidden_project" value="` + res.etp.idCustomer + `"/>
                                        <span class="main_loading_send"></span>
                                        <div class="mb-1">
                                          <label for="etp_name" class="form-label">Nom de l'entreprise</label>
                                          <input type="text" class="`+ form_control + ` etp_name" value="` + res.etp.customer_name + `">
                                        </div>
                                        <div class="mb-1">
                                          <label for="etp_rcs" class="form-label">RCS</label>
                                          <input type="text" class="`+ form_control + ` etp_rcs" value="` + res.etp.customer_rcs + `">
                                        </div>
                                        <div class="mb-1">
                                          <label for="main_etp_email" class="form-label">E-mail</label>
                                          <input type="email" class="`+ form_control + ` etp_email" value="` + res.etp.customer_email + `">
                                        </div>
                                        <div class="mb-1">
                                          <label for="etp_referent_name" class="form-label">Nom du responsable</label>
                                          <input type="text" class="`+ form_control + ` etp_referent_name" value="` + res.etp.customer_name + `">
                                        </div>
                                        <div class="w-full inline-flex justify-end pt-2">
                                          <button 
                                            type="button"
                                            class="focus:outline-none px-3 bg-gray-200 py-2 ml-3 rounded-md text-gray-600 hover:bg-gray-300/80 transition duration-200 text-sm">Annuler</button>
                                          <button 
                                            type="button" 
                                            onclick="mainSendInvitationProject()" 
                                            class="focus:outline-none px-3 bg-gray-700 py-2 ml-3 rounded-md text-white hover:bg-gray-700/90 transition duration-200 text-base">Ajouter</button>
                                        </div>`);
      }
    });
  }
  
  function mainSendInvitationProject() {
    $.ajax({
      type: "post",
      url: "/etp/invites/etp",
      data: {
        idEtp: $('.id_etp_hidden_project').val(),
        etp_rcs: $('.etp_rcs').val(),
        etp_name: $('.etp_name').val(),
        etp_email: $('.etp_email').val(),
        etp_referent_name: $('.etp_referent_name').val()
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
        if (res.error) {
          toastr.error(res.error, 'Erreur', {
            timeOut: 1500
          });
        } else {
          $('.main_rcs_to_append_project').html('');
          $('#main_rcs_search_project').val('');
          mainGetAllEtps($('#main_project_get_id').val());
        }
      }
    });
  }
  
  function mainStoreCours() {
    var module_reference = $('.main_module_reference_project');
    var module_name = $('.main_module_name_project');
    $.ajax({
      type: "post",
      url: "/etp/modules/firstModule",
      data: {
        module_reference: module_reference.val(),
        module_name: module_name.val()
      },
      dataType: "json",
      success: function (res) {
        if (res.success) {
          module_reference.val('');
          module_name.val('');
          toastr.success("Succès !", 'Succès', { timeOut: 1500 });
          mainGetFirstModules($('#main_project_get_id').val());
        } else {
          toastr.error(res.error, 'Erreur', { timeOut: 1500 });
        }
      }
    });
  }
  
  function mainGetFirstModules(idProjet) {
    $.ajax({
      type: "get",
      url: "/etp/modules/get/firstModule",
      dataType: "json",
      success: function (res) {
  
        var get_all_modules = $('#main_get_all_module_projects_inter');
        get_all_modules.html('');
  
        var get_all_modules_selected = $('#main_get_all_module_projects_selected_inter');
        get_all_modules_selected.html('');
  
        if (res.modules.length <= 0) {
          get_all_modules.append(`<p>Aucun résultat</p>`);
        } else {
          var val_idModule = $('#main_module_get_id').val();
          console.log(res)
          console.log('val_idModule',val_idModule)
          $.each(res.modules, function (key, val) {
            if (val_idModule != null) {
              if (val_idModule == val.idModule) {
                get_all_modules_selected.append(`<li
                                                class="list grid grid-cols-5 w-full gap-2 justify-between px-3 py-2 border-[1px] border-gray-100 cursor-pointer hover:bg-gray-50 duration-200 rounded-md !bg-white">
                                                <div class="col-span-4">
                                                  <div class="inline-flex items-center gap-2">
                                                    <span id="main_photo_cours_`+ val.idModule + `">
                                                      <div class="w-10 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-md uppercase">
                                                      `+ val.module_name[0] + `</div>
                                                    </span>
                                                    <div class="flex flex-col gap-0">
                                                      <p class="font-normal text-base text-gray-700">`+ val.module_name + `</p>
                                                      <p class="text-sm text-gray-400 lowercase">--</p>
                                                    </div>
                                                  </div>
                                                </div>
                                                <div class="grid col-span-1 items-center justify-center w-full">
                                                </div>
                                              </li>`);
              } else {
                get_all_modules.append(`<li
                                      class="list grid grid-cols-5 w-full gap-2 justify-between px-3 py-2 border-[1px] border-gray-100 cursor-pointer hover:bg-gray-50 duration-200 rounded-md !bg-white">
                                      <div class="col-span-4">
                                        <div class="inline-flex items-center gap-2">
                                          <span id="main_photo_cours_`+ val.idModule + `">
                                            <div class="w-10 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-md   uppercase">
                                            `+ val.module_name[0] + `</div>
                                          </span>
                                          <div class="flex flex-col gap-0">
                                            <p class="font-normal text-base text-gray-700">`+ val.module_name + `</p>
                                            <p class="text-sm text-gray-400 lowercase">--</p>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="grid col-span-1 items-center justify-center w-full">
                                        <div onclick="mainModuleAssign(`+ idProjet + `, ` + val.idModule + `)"
                                          class="icon w-10 h-10 rounded-full flex items-center justify-center bg-green-100 cursor-pointer hover:bg-green-50 group/icon duration-150">
                                          <i class="fa-solid fa-plus text-green-500 text-sm group-hover/icon:text-green-600 duration-150"></i>
                                        </div>
                                      </div>
                                    </li>`);
              }
            } else {
              get_all_modules.append(`<li
                                      class="list grid grid-cols-5 w-full gap-2 justify-between px-3 py-2 border-[1px] border-gray-100 cursor-pointer hover:bg-gray-50 duration-200 rounded-md !bg-white">
                                      <div class="col-span-4">
                                        <div class="inline-flex items-center gap-2">
                                          <span id="main_photo_cours_`+ val.idModule + `">
                                            <div class="w-10 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-md   uppercase">
                                            `+ val.module_name[0] + `</div>
                                          </span>
                                          <div class="flex flex-col gap-0">
                                            <p class="font-normal text-base text-gray-700">`+ val.module_name + `</p>
                                            <p class="text-sm text-gray-400 lowercase">--</p>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="grid col-span-1 items-center justify-center w-full">
                                        <div onclick="mainModuleAssign(`+ idProjet + `, ` + val.idModule + `)"
                                          class="icon w-10 h-10 rounded-full flex items-center justify-center bg-green-100 cursor-pointer hover:bg-green-50 group/icon duration-150">
                                          <i class="fa-solid fa-plus text-green-500 text-sm group-hover/icon:text-green-600 duration-150"></i>
                                        </div>
                                      </div>
                                    </li>`);
            }
  
            var photo_cours = $('#main_photo_cours_' + val.idModule);
            photo_cours.html('');
  
            if (val.module_image == "" || val.module_image == null) {
              photo_cours.append(
                `<div  class="w-10 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-full uppercase">` +
                val.module_name[0] + `</div>`);
            } else {
              photo_cours.append(`<img
                                        src="/img/modules/` + val.module_image + `"
                                        alt="logo_module" class="w-10 h-10 rounded-full mr-4 object-cover">`);
            }
          });
        }
      }
    });
  }
  
  function mainGetIdModule(idProjet) {
    $.ajax({
      type: "get",
      url: "/etp/projets/" + idProjet + "/mainGetIdModule",
      dataType: "json",
      success: function (res) {
        $('#main_module_get_id').val(res.projet.idModule);
      },
      error: function (error) {
        console.log(error);
      }
    });
  }
  
  function mainModuleAssign(idProjet, idModule) {
    $.ajax({
      type: "patch",
      url: "/etp/projets/" + idProjet + "/" + idModule + "/module/assign",
      dataType: "json",
      success: function (res) {
        if (res.success) {
          toastr.success("Module selectionné avec succès", 'Succès', { timeOut: 1500 });
          mainGetIdModule(idProjet)
          mainGetFirstModules(idProjet)
        } 
        else if (res.error) {
          toastr.error(res.error, 'Erreur', { timeOut: 1500 });
        }
      }
    });
  }
  
  function mainUpdateDateProject(idProjet) {
    if ($('#main_project_reservation').is(':checked')) {
      var reservation = 1;
    } else {
      var reservation = 0;
    }
  
    $.ajax({
      type: "patch",
      url: "/etp/projets/" + idProjet + "/date/assign",
      data: {
        dateDebut: $('#main_date_debut_project').val(),
        dateFin: $('#main_date_fin_project').val(),
        project_reservation: reservation
      },
      dataType: "json",
      success: function (res) {
        console.log("AJOUT DATA--->",res)
        if (res.success) {
          toastr.success("Succès !", 'Succès', { timeOut: 1500 });
          sessionStorage.removeItem('modalState');
          location.reload();
        } else {
          toastr.error(res.error, 'Erreur, veuillez verifiez les dates !', { timeOut: 1500 });
        }
      }
    });
  }