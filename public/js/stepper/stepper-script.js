function mainSearch(id, table, type = "tr") {
    var value = $('#' + id);
    $(`#${table} ${type}`).filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value.val().toLowerCase()) > -1)
    });
}

function salleSearch(inputId, listClass) {
    // Récupère la valeur du champ de recherche
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();

    // Sélectionne tous les éléments de la liste
    const listItems = document.querySelectorAll(`.${listClass} li`);

    // Parcourt chaque élément pour vérifier s'il correspond au filtre
    listItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(filter)) {
            item.style.display = ''; // Affiche l'élément s'il correspond
        } else {
            item.style.display = 'none'; // Masque l'élément s'il ne correspond pas
        }
    });
}

function mainStoreProject(idType) {
    let ref = "";
    let title = "";
    let descritption = "";

    if (idType == 1) {
        ref = "main_project_rerefence";
        title = "main_project_title";
        descritption = "main_project_description";
    } else if (idType == 2) {
        ref = "main_project_rerefence_inter";
        title = "main_project_title_inter";
        descritption = "main_project_description_inter";
    }

    $.ajax({
        type: "post",
        url: "/cfp/projets",
        data: {
            project_reference: $("#" + ref).val(),
            project_title: $("#" + title).val(),
            project_description: $("#" + descritption).val(),
            idTypeProjet: idType
        },
        dataType: "json",
        success: function (res) {
            if (res.status == 200) {
                $('#main_project_get_id').val(res.idProjet);
                if (idType == 1) {
                    $('#smartwizard-intra').smartWizard("next");
                    mainGetAllEtps(res.idProjet);
                } else if (idType == 2) {
                    mainGetFirstModules($('#main_project_get_id').val(), 2);
                    $('#smartwizard').smartWizard("next");
                }
            } else {
                console.log(res.error);
                $('#erreurProjet').append(`<div class="border-[1px] border-red-400 bg-red-100 rounded-md w-full flex items-center gap-3 py-2">
                                    <i class="fa-solid fa-circle-info text-red-600 text-lg pl-4"></i>
                                    <p id="msgErrorProjet" class="text-red-900"></p>
                                  </div>`);
                $('#msgErrorProjet').text(res.error)
                $("#error_" + title).text(res.error.project_title);
                $("#" + title).addClass("border-red-500");
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
        url: "/cfp/invites/etp/getAllEtps",
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
                                        <span id="main_photo_etp_` + val.idEtp + `">
                                          <div class="w-20 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-lg uppercase">
                                          ` + val.etp_initial_name + `</div>
                                        </span>
                                        <div class="flex flex-col gap-0">
                                          <p class="font-normal text-base text-gray-700">` + val.etp_name + `</p>
                                          <p class="text-sm text-gray-400 lowercase">` + val.etp_email + `</p>
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
                                        <span id="main_photo_etp_` + val.idEtp + `">
                                          <div class="w-20 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-lg uppercase">
                                          ` + val.etp_initial_name + `</div>
                                        </span>
                                        <div class="flex flex-col gap-0">
                                          <p class="font-normal text-base text-gray-700">` + val.etp_name + `</p>
                                          <p class="text-sm text-gray-400 lowercase">` + val.etp_email + `</p>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="grid col-span-1 items-center justify-center w-full">
                                      <div onclick="mainEtpAssign(` + idProjet + `,` + val.idEtp + `)"
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
                                        <span id="main_photo_etp_` + val.idEtp + `">
                                          <div class="w-20 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-lg uppercase">
                                          ` + val.etp_initial_name + `</div>
                                        </span>
                                        <div class="flex flex-col gap-0">
                                          <p class="font-normal text-base text-gray-700">` + val.etp_name + `</p>
                                          <p class="text-sm text-gray-400 lowercase">` + val.etp_email + `</p>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="grid col-span-1 items-center justify-center w-full">
                                      <div onclick="mainEtpAssign(` + idProjet + `,` + val.idEtp + `)"
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
                            `<div  class="w-20 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-lg uppercase">` +
                            val.etp_initial_name + `</div>`);
                    } else {
                        photo_etp.append(`<img src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/entreprises/` + val.etp_logo + `" alt="Avatar" class="w-20 h-10 rounded-lg mr-4 object-cover">`);
                    }
                });
            }
        }
    });
}

function mainEtpAssign(idProjet, idEtp) {
    $.ajax({
        type: "patch",
        url: "/cfp/projets/" + idProjet + "/" + idEtp + "/etp/assign",
        dataType: "json",
        success: function (res) {
            if (res.success) {
                toastr.success(res.success, 'Succès', { timeOut: 1500 });
                mainGetIdEtp(idProjet, idEtp);
                mainGetAllEtps(idProjet);
            }
        },
        error: function (error) {
            console.log(error);
        }
    });
}

function mainGetIdEtp(idProjet, idEtp) {
    $.ajax({
        type: "get",
        url: "/cfp/projets/" + idProjet + "/mainGetIdEtp",
        dataType: "json",
        success: function (res) {
            $('#main_etp_get_id').val(idEtp);
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
            url: "/cfp/invites/etp/getRcs/" + rcs,
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
                                          <p onclick="mainShowEtpDetailProject(` + val.idCustomer + `)" style="padding: 10px 4px; margin-bottom: 2px; background: #d9e7cb; border-radius: 4px; cursor: pointer">` + val.customer_name + " - " + val.customer_rcs + `</p>
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
                                        <input type="text" class="` + form_control + ` etp_name">
                                      </div>
                                      <div class="mb-1">
                                        <label for="etp_rcs" class="form-label">Numéro RCS</label>
                                        <input type="text" value="` + rcsVal + `" class="` + form_control + ` etp_rcs">
                                      </div>
                                      <div class="mb-1">
                                        <label for="main_etp_email" class="form-label">E-mail</label>
                                        <input type="email" class="` + form_control + ` etp_email">
                                      </div>
                                      <div class="mb-1">
                                        <label for="etp_referent_name" class="form-label">Nom du responsable</label>
                                        <input type="text" class="` + form_control + ` etp_referent_name">
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
        url: "/cfp/invites/etp/getEtpDetail/" + idEtp,
        dataType: "json",
        success: function (res) {
            const form_control = `bg-white w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400`;
            $('#main_rcs_search_project').val('');
            $('.main_rcs_to_append_project').html('');
            $('.main_rcs_to_append_project').append(`<input type="hidden" class="id_etp_hidden_project" value="` + res.etp.idCustomer + `"/>
                                      <span class="main_loading_send"></span>
                                      <div class="mb-1">
                                        <label for="etp_name" class="form-label">Nom de l'entreprise</label>
                                        <input type="text" class="` + form_control + ` etp_name" value="` + res.etp.customer_name + `">
                                      </div>
                                      <div class="mb-1">
                                        <label for="etp_rcs" class="form-label">RCS</label>
                                        <input type="text" class="` + form_control + ` etp_rcs" value="` + res.etp.customer_rcs + `">
                                      </div>
                                      <div class="mb-1">
                                        <label for="main_etp_email" class="form-label">E-mail</label>
                                        <input type="email" class="` + form_control + ` etp_email" value="` + res.etp.customer_email + `">
                                      </div>
                                      <div class="mb-1">
                                        <label for="etp_referent_name" class="form-label">Nom du responsable</label>
                                        <input type="text" class="` + form_control + ` etp_referent_name" value="` + res.etp.customer_name + `">
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
        url: "/cfp/invites/etp",
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
        url: "/cfp/modules/firstModule",
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

function mainGetFirstModules(idProjet, type) {
    $.ajax({
        type: "get",
        url: "/cfp/modules/get/firstModule",
        dataType: "json",
        success: function (res) {

            let all_module = '';
            let module_selected = '';

            if (type == 1) {
                all_module = 'main_get_all_module_projects';
                module_selected = 'main_get_all_module_projects_selected';
            } else if (type == 2) {
                all_module = 'main_get_all_module_projects_inter';
                module_selected = 'main_get_all_module_projects_selected_inter';
            }

            var get_all_modules = $('#' + all_module);
            get_all_modules.html('');

            var get_all_modules_selected = $('#' + module_selected);
            get_all_modules_selected.html('');

            if (res.modules.length <= 0) {
                get_all_modules.append(`<p>Aucun résultat</p>`);
            } else {
                var val_idModule = $('#main_module_get_id').val();
                $.each(res.modules, function (key, val) {
                    if (val_idModule != null) {
                        if (val_idModule == val.idModule) {
                            get_all_modules_selected.append(`<li
                                              class="list grid grid-cols-5 w-full gap-2 justify-between px-3 py-2 border-[1px] border-gray-100 cursor-pointer hover:bg-gray-50 duration-200 rounded-md !bg-white">
                                              <div class="col-span-4">
                                                <div class="inline-flex items-center gap-2">
                                                  <span id="main_photo_cours_` + val.idModule + `">
                                                    <div class="w-10 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-md uppercase">
                                                    ` + val.module_name[0] + `</div>
                                                  </span>
                                                  <div class="flex flex-col gap-0">
                                                    <p class="font-normal text-base text-gray-700">` + val.module_name + `</p>
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
                                        <span id="main_photo_cours_` + val.idModule + `">
                                          <div class="w-10 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-md   uppercase">
                                          ` + val.module_name[0] + `</div>
                                        </span>
                                        <div class="flex flex-col gap-0">
                                          <p class="font-normal text-base text-gray-700">` + val.module_name + `</p>
                                          <p class="text-sm text-gray-400 lowercase">--</p>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="grid col-span-1 items-center justify-center w-full">
                                      <div onclick="mainModuleAssign(` + idProjet + `, ` + val.idModule + `, ` + type + `)"
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
                                        <span id="main_photo_cours_` + val.idModule + `">
                                          <div class="w-10 h-10 flex items-center justify-center text-gray-500 bg-gray-200 rounded-md   uppercase">
                                          ` + val.module_name[0] + `</div>
                                        </span>
                                        <div class="flex flex-col gap-0">
                                          <p class="font-normal text-base text-gray-700">` + val.module_name + `</p>
                                          <p class="text-sm text-gray-400 lowercase">--</p>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="grid col-span-1 items-center justify-center w-full">
                                      <div onclick="mainModuleAssign(` + idProjet + `, ` + val.idModule + `, ` + type + `)"
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
                                      src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/modules/` + val.module_image + `"
                                      alt="logo_module" class="w-10 h-10 rounded-full mr-4 object-cover">`);
                    }
                });
            }
        }
    });
}

function mainGetIdModule(idProjet, idModule) {
    $.ajax({
        type: "get",
        url: "/cfp/projets/" + idProjet + "/mainGetIdModule",
        dataType: "json",
        success: function (res) {
            $('#main_module_get_id').val(idModule);
        },
        error: function (error) {
            console.log(error);
        }
    });
}

function mainModuleAssign(idProjet, idModule, type) {
    $.ajax({
        type: "patch",
        url: "/cfp/projets/" + idProjet + "/" + idModule + "/module/assign",
        dataType: "json",
        success: function (res) {
            if (res.success) {
                toastr.success("Module selectionné avec succès", 'Succès', { timeOut: 1500 });
                mainGetIdModule(idProjet, idModule)
                mainGetFirstModules(idProjet, type)
            } else if (res.error) {
                toastr.error(res.error, 'Erreur', { timeOut: 1500 });
            }
        }
    });
}

function mainUpdateDateProject(idProjet, type, view = 0) {
    let main_reserve = null;
    let main_date_deb = '';
    let main_date_fin = '';
    if (type == 1) {
        main_reserve = 'main_project_reservation';
        main_date_deb = 'main_date_debut_project';
        main_date_fin = 'main_date_fin_project';
    } else if (type == 2) {
        main_reserve = 'main_project_reservation_inter';
        main_date_deb = 'main_date_debut_project_inter';
        main_date_fin = 'main_date_fin_project_inter';
    }
    if ($('#' + main_reserve).is(':checked')) {
        var reservation = 1;
    } else {
        var reservation = 0;
    }

    $.ajax({
        type: "patch",
        url: "/cfp/projets/" + idProjet + "/date/assign",
        data: {
            dateDebut: $('#' + main_date_deb).val(),
            dateFin: $('#' + main_date_fin).val(),
            project_reservation: reservation
        },
        dataType: "json",
        beforeSend: function () {
            var saveView = $('.save_view');

            saveView.html('');
            saveView.append(`<span class="loading loading-spinner text-[#A462A4]"></span> Sauvegarde en cours`)
        },

        success: function (res) {
            if (res.success) {
                toastr.success("Succès !", 'Succès', { timeOut: 1500 });
                sessionStorage.removeItem('modalState');

                // if (view == 0) {
                //     location.reload();
                // } else {
                window.location.href = `/cfp/projets/${idProjet}/detail`;
                // }
            } else {
                toastr.error(res.error, 'Erreur, veuillez verifiez les dates !', { timeOut: 1500 });
            }
        }
    });
}

function getVille() {
    $.ajax({
        type: "get",
        url: "/cfp/projets/getVille",
        dataType: "json",
        success: function (res) {
            console.log(res)
            $.each(res.villes, function (i, v) {
                let selected = '';
                if (v.idVille == 1) {
                    selected = 'selected';
                }
                $('#idVille').append(`<option value='` + v.idVille + `' ` + selected + `>` + v.ville +
                    `</option>`);
            });
        }
    });
}

function getModalite(idTypeProjet) {
    $.ajax({
        type: "get",
        url: "/cfp/projets/getModalite",
        dataType: "json",
        success: function (res) {
            console.log(res)
            $.each(res.modalites, function (i, v) {
                let selected = '';
                if (v.idModalite == 1) {
                    selected = 'selected';
                }

                if (idTypeProjet == 1) {
                    $('#idModalite_intra').append(`<option value='` + v.idModalite + `' ` + selected + `>` + v.modalite +
                        `</option>`);
                } else if (idTypeProjet == 2) {
                    $('#idModalite_inter').append(`<option value='` + v.idModalite + `' ` + selected + `>` + v.modalite +
                        `</option>`);
                }
            });
        }
    });
}

function updateVille(idProjet) {
    $.ajax({
        type: "patch",
        url: "/cfp/projets/" + idProjet,
        data: {
            idVilleCoded: $('#idVille').val(),
        },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                toastr.success("Succès !", res.success, { timeOut: 1500 });
            } else {
                toastr.error('Erreur', res.error, { timeOut: 1500 });
            }
        }
    });
}

function updateModalite(idProjet, idTypeProjet) {
    if (idTypeProjet == 1) {
        var idModalite = $('#idModalite_intra').val();
    } else if (idTypeProjet == 2) {
        var idModalite = $('#idModalite_inter').val();
    }

    $.ajax({
        type: "patch",
        url: "/cfp/projets/" + idProjet + "/update/modalite",
        data: {
            idModalite: idModalite
        },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                toastr.success(res.success, 'Succès', {
                    timeOut: 1500
                });
            } else {
                toastr.error(res.error, 'Erreur', {
                    timeOut: 1500
                });
            }
        },
        error: function (error) {
            console.log(error);
        }
    });
}

function getNombreDocumentStepper(idDossier, callback) {
    $.ajax({
        type: "GET",
        url: "/cfp/dossier/nombreDossier/" + idDossier,
        dataType: "json",
        success: function (response) {
            callback(response.nombreDocument
                .nombreDocument);
        },
        error: function (xhr, status, error) {
            console.error('Erreur lors de la récupération du nombre de documents:', error);
            callback(0);
        }
    });
}

function getNombreProjetStepper(idDossier, callback) {
    $.ajax({
        type: "GET",
        url: "/cfp/dossier/getNombreProjet/" + idDossier,
        dataType: "json",
        success: function (response) {
            callback(response.projet_count);
        },
        error: function (xhr, status, error) {
            console.error('Erreur lors de la récupération du nombre de documents:', error);
            callback(0);
        }
    });
}

var dossierId = null;
let isLoadingStepper = false;


function getDossierStepper(idProjet = $('#main_project_get_id').val(), year) {

    if (isLoadingStepper) return;
    isLoadingStepper = true;
    var fileTableBody = $('.fileTable tbody');
    fileTableBody.empty();

    $.ajax({
        type: "GET",
        url: "/cfp/dossier/showAllDossier/",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            year: year,
            idProjet: idProjet,
        },
        dataType: "json",
        beforeSend: function () {
            for (let i = 0; i < 5; i++) {
                fileTableBody.append(`
                                  <tr class="hover:bg-gray-50 animate-pulse">
                                      <td class="py-4 px-4 border-b border-gray-300">
                                          <div class="flex items-center">
                                              <div class="w-8 h-8 bg-gray-200 rounded-full mr-2"></div>
                                              <div class="w-1/2 h-6 bg-gray-200 rounded"></div>
                                          </div>
                                      </td>
                                      <td class="py-4 px-4 border-b border-gray-300 text-right">
                                          <div class="w-16 h-6 bg-gray-200 rounded"></div>
                                      </td>
                                      <td class="py-4 px-4 border-b border-gray-300 text-right">
                                          <div class="w-16 h-6 bg-gray-200 rounded"></div>
                                      </td>
                                      <td class="py-4 px-4 border-b border-gray-300 text-center">
                                          <div class="w-8 h-8 bg-gray-200 rounded-full mx-auto"></div>
                                      </td>
                                  </tr>
                              `);
            }
        },
        success: function (response) {
            if (response.dossiers && response.dossiers.length > 0) {
                fileTableBody.empty();
                $('.pagination').removeClass('hidden');

                fileTableBody.append(response.allFilesList);

            } else {
                fileTableBody.empty();
                $('.pagination').addClass('hidden');
                fileTableBody.append(`
                                  <tr>
                                      <td colspan="4" class="py-4 px-4 border-b border-gray-300 text-gray-600 text-center text-lg">
                                          Pas de dossier pour le moment
                                      </td>
                                  </tr>
                              `);
                isLoadingStepper = false;
            }
        },
        error: function (xhr, status, error) {
            $('.fileTable tbody').append(
                '<tr><td colspan="4" class="py-4 px-4 border-b border-gray-300 text-red-500 text-center">Une erreur est survenue lors du chargement des dossiers.</td></tr>'
            );
            isLoadingStepper = false;
        }
    });
}

function ajoutProjetInFolderStepper(idDossier, idProjet) {
    $.ajax({
        type: "post",
        url: `/cfp/dossier/document/ajouter/${idDossier}/${idProjet}`,
        dataType: "json",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
        },
        success: function (res) {
            if (res.success) {
                toastr.success(res.success, "Succès", {
                    timeOut: 1600,
                });
                getDossierStepper();
                getDossierSelected(idProjet);
                $('#main_next_btn_dossier').attr('data-dossier', true);
                $('#main_next_btn_dossier_inter').attr('data-dossier', true);
            } else {
                toastr.error(res.error, "Erreur", {
                    timeOut: 1600,
                });
                console.log(res.error);
            }
        }
    });
}

function getDossierSelected(idProjet) {

    var fileTableSelected = $('.fileTableSelected');
    fileTableSelected.html('');

    $.ajax({
        type: "get",
        url: `/cfp/dossier/showSelected/${idProjet}`,
        dataType: "json",
        success: function (res) {
            if (res.dossiersProject != null) {
                getNombreDocumentStepper(res.dossiersProject.idDossier, function (nombreDocument) {
                    getNombreProjetStepper(res.dossiersProject.idDossier, function (nombreProjet) {
                        fileTableSelected.append(`
                            <tr class="hover:bg-gray-50">
                                <td>
                                    <i class="fa-solid fa-folder text-yellow-500 mr-2"></i>${res.dossiersProject.nomDossier}
                                </td>
                                <td class="text-right">${nombreDocument}</td>
                                <td class="text-right">${nombreProjet}</td>
                                <td>
                                </td>
                            </tr>
                        `);

                    });
                });
            } else {
                fileTableSelected.append(
                    `
                        <tr>
                            <td>
                                Pas de dossier pour l'instant
                            </td>
                        </tr>
                    `);
                $('#main_next_btn_dossier').attr('data-dossier', false);
            }
        }
    });
}

function addRowDossier(id) {
    // Sélectionne le tbody correspondant à l'ID donné
    var table = $(`.${id} tbody:first`);
    var btn = $(`.btn_${id}`);

    var idProjet = $('#main_project_get_id').val()

    btn.attr('disabled', 'disabled');

    // Contenu HTML de la nouvelle ligne
    var content = `
    <tr>
      <td><input name="dossier" id="newFile_input" class="input input-bordered" placeholder="Nom du dossier"></td>
      <td>0</td>
      <td>0</td>
      <td>
        <button onclick="newFile(${idProjet})" class="btn btn-sm btn-ghost opacity-50">
          <i class="fa-solid fa-plus"></i>
        </button>
      </td>
    </tr>
  `;

    // Ajoute la nouvelle ligne au début du tbody
    table.prepend(content);
}

function newFile(id) {
    var dossier = $('#newFile_input').val();

    $.ajax({
        type: "post",
        url: "/cfp/dossier/ajouter",
        data: {
            dossier: dossier
        },
        success: function (res) {
            if (res.success) {
                isLoadingStepper = false;

                getDossierStepper();

                // Sélectionner automatiquement le dossier créé
                if (res.idDossier) {
                    ajoutProjetInFolderStepper(res.idDossier, id)
                }
            } else {
                toastr.error(res.error, "Erreur", {
                    timeOut: 1600,
                });
            }
        }
    });
}