function getDropdownItem() {
  var etps = $('#filterEntreprise');
  var fonctions = $('#filterFonction');
  var villes = $('#filterVille');
  var status = $('#filterStatut');
  var modalites = $('#filterModalite');
  var modules = $('#filterModule');
  var periodes = $('#filterPeriode');

  $.ajax({
    type: "get",
    url: "/cfp/apprenants/filter/getDropdownItem",
    dataType: "json",
    success: function (res) {
      etps.html('');
      fonctions.html('');
      villes.html('');
      status.html('');
      modalites.html('');
      modules.html('');
      periodes.html('');

      if (res.etps.length <= 0) {
        etps.append(`<h3>Aucun résultat</h3>`);
      } else {
        $.each(res.etps, function (i, v) {
          etps.append(`<li class="etp_item_` + v.idEtp + `">
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.etp_name + `">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.idEtp + `"
                                    class="etp_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">`+ v.etp_name + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_emp_etp_`+ v.idEtp + `">` + v.emp_nb + `</div>
                            </div>
                          </li>`);
        });

        $.each(res.fonctions, function (i, v) {
          fonctions.append(`<li class="fonction_item_` + v.idFonction + `">
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.idFonction + `"
                                    class="fonction_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">`+ v.emp_fonction + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_emp_fonction_`+ v.idFonction + `">` + v.emp_nb + `</div>
                            </div>
                          </li>`);
        });

        $.each(res.villes, function (i, v) {
          villes.append(`<li class="ville_item_` + v.project_id_ville + `">
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.project_id_ville +  `"
                                    class="ville_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">`+ v.ville + ( v.project_code_postal  ) + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_emp_ville_`+ v.project_id_ville + `">` + v.emp_nb + `</div>
                            </div>
                          </li>`);
        });

        $.each(res.status, function (i, v) {
          status.append(`<li class="statut_item_` + v.project_status + `">
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_status + `">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.project_status + `"
                                    class="statut_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">`+ v.project_status + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_emp_status_`+ v.project_status + `">` + v.emp_nb + `</div>
                            </div>
                          </li>`);
        });

        $.each(res.modalites, function (i, v) {
          modalites.append(`<li class="modalite_item_` + v.project_modality + `">
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_status + `">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.project_modality + `"
                                    class="modalite_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">`+ v.project_modality + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_emp_modalite_`+ v.project_modality + `">` + v.emp_nb + `</div>
                            </div>
                          </li>`);
        });

        $.each(res.modules, function (i, v) {
          modules.append(`<li class="module_item_` + v.idModule + `">
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.module_name + `">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.idModule + `"
                                    class="module_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">`+ v.module_name + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_emp_module_`+ v.idModule + `">` + v.emp_nb + `</div>
                            </div>
                          </li>`);
        });

        periodes.append(`<li>
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_3"
                                    value=""
                                    type="radio"
                                    name="period_radio"
                                    autocomplete="off"
                                    class="periode_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-full checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">3 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_3 periode_p_3 grid cols-span-1 items-center justify-end"></div>
                            </div>
                          </li>
                          <li>
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_6"
                                    value=""
                                    type="radio"
                                    name="period_radio"
                                    autocomplete="off"
                                    class="periode_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-full checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">6 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_6 grid cols-span-1 items-center justify-end"></div>
                            </div>
                          </li>
                          <li>
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_12"
                                    value=""
                                    type="radio"
                                    name="period_radio"
                                    autocomplete="off"
                                    class="periode_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-full checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">12 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_12 grid cols-span-1 items-center justify-end"></div>
                            </div>
                          </li>
                          <hr class="border-[1px] border-gray-200 my-2">
                          <li>
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_3"
                                    value=""
                                    type="radio"
                                    name="period_radio"
                                    autocomplete="off"
                                    class="periode_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-full checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">3 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_3 grid cols-span-1 items-center justify-end"></div>
                            </div>
                          </li>
                          <li>
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_6"
                                    value=""
                                    type="radio"
                                    name="period_radio"
                                    autocomplete="off"
                                    class="periode_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-full checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">6 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_6 grid cols-span-1 items-center justify-end"></div>
                            </div>
                          </li>
                          <li>
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_12"
                                    value=""
                                    type="radio"
                                    name="period_radio"
                                    autocomplete="off"
                                    class="periode_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-full checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">12 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_12 grid cols-span-1 items-center justify-end"></div>
                            </div>
                          </li>`);

        if (res.periodePrev3 != null) {
          $('#p_vp_3').val(res.periodePrev3.p_id_periode);
          $('.p_np_3').text(res.periodePrev3.emp_nb);
        } else {
          $('#p_vp_3').val(0);
          $('.p_np_3').text(0);
        }

        if (res.periodePrev6 != null) {
          $('#p_vp_6').val(res.periodePrev6.p_id_periode);
          $('.p_np_6').text(res.periodePrev6.emp_nb);
        } else {
          $('#p_vp_6').val(0);
          $('.p_np_6').text(0);
        }

        if (res.periodePrev12 != null) {
          $('#p_vp_12').val(res.periodePrev12.p_id_periode);
          $('.p_np_12').text(res.periodePrev12.emp_nb);
        } else {
          $('#p_vp_12').val(0);
          $('.p_np_12').text(0);
        }

        if (res.periodeNext3 != null) {
          $('#p_vn_3').val(res.periodeNext3.p_id_periode);
          $('.p_nn_3').text(res.periodeNext3.emp_nb);
        } else {
          $('#p_vn_3').val(0);
          $('.p_nn_3').text(0);
        }

        if (res.periodeNext6 != null) {
          $('#p_vn_6').val(res.periodeNext6.p_id_periode);
          $('.p_nn_6').text(res.periodeNext6.emp_nb);
        } else {
          $('#p_vn_6').val(0);
          $('.p_nn_6').text(0);
        }

        if (res.periodeNext12 != null) {
          $('#p_vn_12').val(res.periodeNext12.p_id_periode);
          $('.p_nn_12').text(res.periodeNext12.emp_nb);
        } else {
          $('#p_vn_12').val(0);
          $('.p_nn_12').text(0);
        }

        $('.closeDropdown').click(function (e) {
          e.preventDefault();
          closDrop();
        });



        $($('.etp_item_checkbox')).add($('.fonction_item_checkbox')).add($('.ville_item_checkbox')).add($('.statut_item_checkbox')).add($('.modalite_item_checkbox')).add($('.module_item_checkbox')).add($('.periode_item_checkbox')).change(function () {


          var idEtps = [];
          var idFonctions = [];
          var idVilles = [];
          var idStatus = [];
          var idModalites = [];
          var idModules = [];
          var idPeriodes;

          var nb_emp_Etp = new Set();
          var nb_emp_Fonctions = new Set();
          var nb_emp_Villes = new Set();
          var nb_emp_Status = new Set();
          var nb_emp_Modalites = new Set();
          var nb_emp_Modules = new Set();

          let sumEtp = 0;
          let sumFonction = 0;
          let sumVille = 0;
          let sumStatus = 0;
          let sumModalite = 0;
          let sumModule = 0;

          // ================ ENTREPRISE =====================
          $($('.etp_item_checkbox')).each(function () {
            if ($(this).is(':checked')) {
              nb_emp_Etp.add($(this).val());
              for (let i = 0; i < res.etps.length; i++) {
                if ($(this).val() == res.etps[i].idEtp) {
                  sumEtp += res.etps[i].emp_nb;
                }
              }
            }
          });
          idEtps = Array.from(nb_emp_Etp);

          $('.resetClick_entreprise').click(function () {
            refresh('entreprise', null, idFonctions.toString(), idVilles.toString(), idStatus.toString(), idModalites.toString(), idModules.toString(), idPeriodes);
          });

          $('.iconClose-entreprise').click(function () {
            getDropdownItem();
            refresh('entreprise', null, idFonctions.toString(), idVilles.toString(), idStatus.toString(), idModalites.toString(), idModules.toString(), idPeriodes);
            $('.selectedFilter_entreprise').addClass('hidden');
            $('.unselectedFilter_entreprise').removeClass('hidden');
          });


          // ===================== Button Afficher dans Entreprise =======================
          $('.countSelected_entreprise').text(sumEtp);
          $('.countedButton_entreprise').click(function (e) {
            e.preventDefault();
            if (sumEtp > 0) {
              $('.selectedFilter_entreprise').removeClass('hidden');
              $('.unselectedFilter_entreprise').addClass('hidden');
            } else {
              $('.selectedFilter_entreprise').addClass('hidden');
              $('.unselectedFilter_entreprise').removeClass('hidden');
            }
            closDrop();
          });

          // ================ FONCTION =====================
          $($('.fonction_item_checkbox')).each(function () {
            if ($(this).is(':checked')) {
              nb_emp_Fonctions.add($(this).val());
              for (let i = 0; i < res.fonctions.length; i++) {
                if ($(this).val() == res.fonctions[i].idFonction) {
                  sumFonction += res.fonctions[i].emp_nb;
                }
              }
            }
          });
          idFonctions = Array.from(nb_emp_Fonctions);

          $('.resetClick_fonction').click(function () {
            refresh('fonction', idEtps.toString(), null, idVilles.toString(), idStatus.toString(), idModalites.toString(), idModules.toString(), idPeriodes);
          });

          $('.iconClose-fonction').click(function () {
            getDropdownItem();
            refresh('fonction', idEtps.toString(), null, idVilles.toString(), idStatus.toString(), idModalites.toString(), idModules.toString(), idPeriodes);
            $('.selectedFilter_fonction').addClass('hidden');
            $('.unselectedFilter_fonction').removeClass('hidden');
          });

          // ================ VILLE =====================
          $($('.ville_item_checkbox')).each(function () {
            if ($(this).is(':checked')) {
              nb_emp_Villes.add($(this).val());
              for (let i = 0; i < res.villes.length; i++) {
                if ($(this).val() == res.villes[i].project_id_ville) {
                  sumVille += res.villes[i].emp_nb;
                }
              }
            }
          });
          idVilles = Array.from(nb_emp_Villes);

          $('.resetClick_ville').click(function () {
            refresh('ville', idEtps.toString(), idFonctions.toString(), null, idStatus.toString(), idModalites.toString(), idModules.toString(), idPeriodes);
          });

          $('.iconClose-ville').click(function () {
            getDropdownItem();
            refresh('ville', idEtps.toString(), idFonctions.toString(), null, idStatus.toString(), idModalites.toString(), idModules.toString(), idPeriodes);
            $('.selectedFilter_ville').addClass('hidden');
            $('.unselectedFilter_ville').removeClass('hidden');
          });

          // ================ STATUT =====================
          $($('.statut_item_checkbox')).each(function () {
            if ($(this).is(':checked')) {
              nb_emp_Status.add($(this).val());
              for (let i = 0; i < res.status.length; i++) {
                if ($(this).val() == res.status[i].project_status) {
                  sumStatus += res.status[i].emp_nb;
                }
              }
            }
          });
          idStatus = Array.from(nb_emp_Status);

          $('.resetClick_statut').click(function () {
            refresh('statut', idEtps.toString(), idFonctions.toString(), idVilles.toString(), null, idModalites.toString(), idModules.toString(), idPeriodes);
          });

          $('.iconClose-statut').click(function () {
            getDropdownItem();
            refresh('statut', idEtps.toString(), idFonctions.toString(), idVilles.toString(), null, idModalites.toString(), idModules.toString(), idPeriodes);
            $('.selectedFilter_statut').addClass('hidden');
            $('.unselectedFilter_statut').removeClass('hidden');
          });

          // ================ MODALITE =====================
          $($('.modalite_item_checkbox')).each(function () {
            if ($(this).is(':checked')) {
              nb_emp_Modalites.add($(this).val());
              for (let i = 0; i < res.modalites.length; i++) {
                if ($(this).val() == res.modalites[i].project_modality) {
                  sumModalite += res.modalites[i].emp_nb;
                }
              }
            }
          });
          idModalites = Array.from(nb_emp_Modalites);

          $('.resetClick_modalite').click(function () {
            refresh('modalite', idEtps.toString(), idFonctions.toString(), idVilles.toString(), idStatus.toString(), null, idModules.toString(), idPeriodes);
          });

          $('.iconClose-modalite').click(function () {
            getDropdownItem();
            refresh('modalite', idEtps.toString(), idFonctions.toString(), idVilles.toString(), idStatus.toString(), null, idModules.toString(), idPeriodes);
            $('.selectedFilter_modalite').addClass('hidden');
            $('.unselectedFilter_modalite').removeClass('hidden');
          });

          // ================ MODULE =====================
          $($('.module_item_checkbox')).each(function () {
            if ($(this).is(':checked')) {
              nb_emp_Modules.add($(this).val());
              for (let i = 0; i < res.modules.length; i++) {
                if ($(this).val() == res.modules[i].idModule) {
                  sumModule += res.modules[i].emp_nb;
                }
              }
            }
          });
          idModules = Array.from(nb_emp_Modules);

          $('.resetClick_cours').click(function () {
            refresh('cours', idEtps.toString(), idFonctions.toString(), idVilles.toString(), idStatus.toString(), idModalites.toString(), null, idPeriodes);
          });

          $('.iconClose-cours').click(function () {
            getDropdownItem();
            refresh('cours', idEtps.toString(), idFonctions.toString(), idVilles.toString(), idStatus.toString(), idModalites.toString(), null, idPeriodes);
            $('.selectedFilter_cours').addClass('hidden');
            $('.unselectedFilter_cours').removeClass('hidden');
          });

          // ================ PERIODE =====================
          $($('.periode_item_checkbox')).each(function () {
            if ($(this).is(':checked')) {
              idPeriodes = $(this).val();
            }
          });
          $('.countedButton_periode').click(function (e) {
            e.preventDefault();
            if ($('.periode_item_checkbox').is(':checked')) {
              $('.selectedFilter_periode').removeClass('hidden');
              $('.unselectedFilter_periode').addClass('hidden');
            } else {
              $('.selectedFilter_periode').addClass('hidden');
              $('.unselectedFilter_periode').removeClass('hidden');
            }
            closDrop();
          });

          $('.iconClose-periode').click(function () {
            getDropdownItem();
            refresh('periode', idEtps.toString(), idFonctions.toString(), idVilles.toString(), idStatus.toString(), idModalites.toString(), idModules.toString(), null);
            $('.selectedFilter_periode').addClass('hidden');
            $('.unselectedFilter_periode').removeClass('hidden');
          });


          filterItems(idEtps.toString(), idFonctions.toString(), idVilles.toString(), idStatus.toString(), idModalites.toString(), idModules.toString(), idPeriodes);

          // ===================== Button Afficher dans Fonction =======================
          $('.countSelected_fonction').text(sumFonction);
          $('.countedButton_fonction').click(function (e) {
            e.preventDefault();
            if (sumFonction > 0) {
              $('.selectedFilter_fonction').removeClass('hidden');
              $('.unselectedFilter_fonction').addClass('hidden');
            } else {
              $('.selectedFilter_fonction').addClass('hidden');
              $('.unselectedFilter_fonction').removeClass('hidden');
            }
            closDrop();
          });

          // ===================== Button Afficher dans Ville =======================
          $('.countSelected_ville').text(sumVille);
          $('.countedButton_ville').click(function (e) {
            e.preventDefault();
            if (sumVille > 0) {
              $('.selectedFilter_ville').removeClass('hidden');
              $('.unselectedFilter_ville').addClass('hidden');
            } else {
              $('.selectedFilter_ville').addClass('hidden');
              $('.unselectedFilter_ville').removeClass('hidden');
            }
            closDrop();
          });

          // ===================== Button Afficher dans Statut =======================
          $('.countSelected_statut').text(sumStatus);
          $('.countedButton_statut').click(function (e) {
            e.preventDefault();
            if (sumStatus > 0) {
              $('.selectedFilter_statut').removeClass('hidden');
              $('.unselectedFilter_statut').addClass('hidden');
            } else {
              $('.selectedFilter_statut').addClass('hidden');
              $('.unselectedFilter_statut').removeClass('hidden');
            }
            closDrop();
          });

          // ===================== Button Afficher dans Modalite =======================
          $('.countSelected_modalite').text(sumModalite);
          $('.countedButton_modalite').click(function (e) {
            e.preventDefault();
            if (sumModalite > 0) {
              $('.selectedFilter_modalite').removeClass('hidden');
              $('.unselectedFilter_modalite').addClass('hidden');
            } else {
              $('.selectedFilter_modalite').addClass('hidden');
              $('.unselectedFilter_modalite').removeClass('hidden');
            }
            closDrop();
          });

          // ===================== Button Afficher dans Cours =======================
          $('.countSelected_cours').text(sumModule);
          $('.countedButton_cours').click(function (e) {
            e.preventDefault();
            if (sumModule > 0) {
              $('.selectedFilter_cours').removeClass('hidden');
              $('.unselectedFilter_cours').addClass('hidden');
            } else {
              $('.selectedFilter_cours').addClass('hidden');
              $('.unselectedFilter_cours').removeClass('hidden');
            }
            closDrop();
          });
        });
      }
    }
  });
}

function refresh(itemClicked, idEtps, idFonctions, idVilles, idStatus, idModalites, idModules, idPeriodes) {
  switch (itemClicked) {
    case "entreprise":
      $('.etp_item_checkbox').prop('checked', false);
      $('.countSelected_entreprise').text("");
      filterItem(idEtps, idFonctions, idVilles, idStatus, idModalites, idModules, idPeriodes);
      break;
    case "fonction":
      $('.fonction_item_checkbox').prop('checked', false);
      $('.countSelected_fonction').text("");
      filterItem(idEtps, idFonctions, idVilles, idStatus, idModalites, idModules, idPeriodes);
      break;
    case "periode":
      $('.periode_item_checkbox').prop('checked', false);
      $('.countSelected_periode').text("");
      filterItem(idEtps, idFonctions, idVilles, idStatus, idModalites, idModules, idPeriodes);
      break;
    case "module":
      $('.module_item_checkbox').prop('checked', false);
      $('.countSelected_module').text("");
      filterItem(idEtps, idFonctions, idVilles, idStatus, idModalites, idModules, idPeriodes);
      break;
    case "ville":
      $('.ville_item_checkbox').prop('checked', false);
      $('.countSelected_ville').text("");
      filterItem(idEtps, idFonctions, idVilles, idStatus, idModalites, idModules, idPeriodes);
      break;
    case "modalite":
      $('.modalite_item_checkbox').prop('checked', false);
      $('.countSelected_modalite').text("");
      filterItem(idEtps, idFonctions, idVilles, idStatus, idModalites, idModules, idPeriodes);
      break;
    case "statut":
      $('.statut_item_checkbox').prop('checked', false);
      $('.countSelected_statut').text("");
      filterItem(idEtps, idFonctions, idVilles, idStatus, idModalites, idModules, idPeriodes);
      break;
    default:
      break;
  }
}
