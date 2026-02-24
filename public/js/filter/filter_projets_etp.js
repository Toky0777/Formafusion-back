function getDropdownItem() {
  var status = $('#filterStatut');
  var etps = $('#filterEntreprise');
  var types = $('#filterType');
  var periodes = $('#filterPeriode');
  var modules = $('#filterModule');
  var villes = $('#filterVille');
  var financements = $('#filterFinancement');
  var formateurs = $('#filterFormateur');

  $.ajax({
    type: "get",
    url: "/etp/projets/filter/getDropdownItem",
    dataType: "json",
    success: function (res) {
      console.log('DROP ITEM-->', res);
      status.html('');
      // etps.html('');
      types.html('');
      periodes.html('');
      modules.html('');
      villes.html('');
      financements.html('');
      formateurs.html('');

      if (res.status.length <= 0) {
        status.append(`<h3>Aucun résultat</h3>`);
      } else {
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
                            <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                          </div>
                        </li>`);
        });
      }

      // if (res.etps.length <= 0) {
      //   etps.append(`<h3>Aucun résultat</h3>`);
      // } else {
      //   $.each(res.etps, function (i, v) {
      //     etps.append(`<li class="etp_item_` + v.idEtp + `">
      //                     <div class="grid grid-cols-6">
      //                       <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.etp_name + `">
      //                         <span class="inline-flex items-center gap-2 px-2">
      //                           <input 
      //                             type="checkbox"
      //                             value="`+ v.idEtp + `"
      //                             class="etp_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
      //                           <p class="text-gray-500">`+ v.etp_name + `</p>
      //                         </span>
      //                       </div>
      //                       <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
      //                     </div>
      //                   </li>`);
      //   });
      // }

      if (res.types.length <= 0) {
        types.append(`<h3>Aucun résultat</h3>`);
      } else {
        $.each(res.types, function (i, v) {
          types.append(`<li class="type_item_` + v.project_type + `">
                          <div class="grid grid-cols-6">
                            <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                              <span class="inline-flex items-center gap-2 px-2">
                                <input 
                                  type="checkbox"
                                  value="`+ v.project_type + `"
                                  class="type_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                <p class="text-gray-500">`+ v.project_type + `</p>
                              </span>
                            </div>
                            <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                          </div>
                        </li>`);
        });
      }

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

      if (res.modules.length <= 0) {
        modules.append(`<h3>Aucun résultat</h3>`);
      } else {
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
                            <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                          </div>
                        </li>`);
        });
      }

      if (res.villes.length <= 0) {
        villes.append(`<h3>Aucun résultat</h3>`);
      } else {
        $.each(res.villes, function (i, v) {
          villes.append(`<li class="ville_item_` + v.idVille + `">
                          <div class="grid grid-cols-6">
                            <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                              <span class="inline-flex items-center gap-2 px-2">
                                <input 
                                  type="checkbox"
                                  value="`+ v.idVille + `"
                                  class="ville_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                <p class="text-gray-500">`+ v.ville + `</p>
                              </span>
                            </div>
                            <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                          </div>
                        </li>`);
        });
      }

      // if (res.financements.length <= 0) {
      //   financements.append(`<h3>Aucun résultat</h3>`);
      // } else {
      //   $.each(res.financements, function (i, v) {
      //     financements.append(`<li class="financement_item_` + v.idPaiement + `">
      //                     <div class="grid grid-cols-6">
      //                       <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.paiement + `">
      //                         <span class="inline-flex items-center gap-2 px-2">
      //                           <input 
      //                             type="checkbox"
      //                             value="`+ v.idPaiement + `"
      //                             class="financement_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
      //                           <p class="text-gray-500">`+ v.paiement + `</p>
      //                         </span>
      //                       </div>
      //                       <div class="grid cols-span-1 items-center justify-end nb_proj_financement_`+ v.idPaiement + `">` + v.projet_nb + `</div>
      //                     </div>
      //                   </li>`);
      //   });
      // }

      if (res.formateurs) {
        if (res.formateurs.length <= 0) {
          formateurs.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.formateurs, function (i, v) {
            formateurs.append(`<li class="formateur_item_` + v.idFormateur + `">
                      <div class="grid grid-cols-6">
                        <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.form_name + ` ` + v.form_firstname + `">
                          <span class="inline-flex items-center gap-2 px-2">
                            <input 
                              type="checkbox"
                              value="`+ v.idFormateur + `"
                              class="formateur_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                            <p class="text-gray-500">`+ v.form_name + ` ` + v.form_firstname + `</p>
                          </span>
                        </div>
                        <div class="grid cols-span-1 items-center justify-end nb_proj_formateur_`+ v.idFormateur + `">` + v.projet_nb + `</div>
                      </div>
                    </li>`);
          });
        }
      }
      if (res.periodePrev3 != null) {
        $('#p_vp_3').val(res.periodePrev3.p_id_periode);
        $('.p_np_3').text(res.periodePrev3.projet_nb);
      } else {
        $('#p_vp_3').val(0);
        $('.p_np_3').text(0);
      }

      if (res.periodePrev6 != null) {
        $('#p_vp_6').val(res.periodePrev6.p_id_periode);
        $('.p_np_6').text(res.periodePrev6.projet_nb);
      } else {
        $('#p_vp_6').val(0);
        $('.p_np_6').text(0);
      }

      if (res.periodePrev12 != null) {
        $('#p_vp_12').val(res.periodePrev12.p_id_periode);
        $('.p_np_12').text(res.periodePrev12.projet_nb);
      } else {
        $('#p_vp_12').val(0);
        $('.p_np_12').text(0);
      }

      if (res.periodeNext3 != null) {
        $('#p_vn_3').val(res.periodeNext3.p_id_periode);
        $('.p_nn_3').text(res.periodeNext3.projet_nb);
      } else {
        $('#p_vn_3').val(0);
        $('.p_nn_3').text(0);
      }

      if (res.periodeNext6 != null) {
        $('#p_vn_6').val(res.periodeNext6.p_id_periode);
        $('.p_nn_6').text(res.periodeNext6.projet_nb);
      } else {
        $('#p_vn_6').val(0);
        $('.p_nn_6').text(0);
      }

      if (res.periodeNext12 != null) {
        $('#p_vn_12').val(res.periodeNext12.p_id_periode);
        $('.p_nn_12').text(res.periodeNext12.projet_nb);
      } else {
        $('#p_vn_12').val(0);
        $('.p_nn_12').text(0);
      }


      $('.closeDropdown').click(function (e) {
        e.preventDefault();
        closDrop();
      });

      $($('.statut_item_checkbox, .etp_item_checkbox, .type_item_checkbox, .periode_item_checkbox, .module_item_checkbox, .ville_item_checkbox, .financement_item_checkbox, .formateur_item_checkbox')).change(function () {
        var idStatus = [];
        var idEtps = [];
        var idTypes = [];
        var idPeriodes;
        var idModules = [];
        var idVilles = [];
        var idFinancements = [];
        var idFormateurs = [];

        var sumStatus = 0;
        var sumEtps = 0;
        var sumTypes = 0;
        var sumModules = 0;
        var sumVilles = 0;
        var sumFinancements = 0;
        var sumFormateurs = 0;

        // ================ STATUT =====================
        $($('.statut_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idStatus.push($(this).val());
            for (let i = 0; i < res.status.length; i++) {
              if ($(this).val() == res.status[i].project_status) {
                sumStatus += res.status[i].projet_nb;
              }
            }
          }
        });

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

        $('.resetClick_statut').click(function () {
          refresh('statut', null, idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), idFormateurs.toString());
        });

        $('.iconClose-statut').click(function () {
          getDropdownItem();
          refresh('statut', null, idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), idFormateurs.toString());
          $('.selectedFilter_statut').addClass('hidden');
          $('.unselectedFilter_statut').removeClass('hidden');
        });


        // ================ ENTREPRISE =====================
        $($('.etp_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idEtps.push($(this).val());
            for (let i = 0; i < res.etps.length; i++) {
              if ($(this).val() == res.etps[i].idEtp) {
                sumEtps += res.etps[i].projet_nb;
              }
            }
          }
        });

        $('.countSelected_entreprise').text(sumEtps);
        $('.countedButton_entreprise').click(function (e) {
          e.preventDefault();
          if (sumEtps > 0) {
            $('.selectedFilter_entreprise').removeClass('hidden');
            $('.unselectedFilter_entreprise').addClass('hidden');
          } else {
            $('.selectedFilter_entreprise').addClass('hidden');
            $('.unselectedFilter_entreprise').removeClass('hidden');
          }
          closDrop();
        });

        $('.resetClick_entreprise').click(function () {
          refresh('entreprise', idStatus.toString(), null, idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), idFormateurs.toString());
        });

        $('.iconClose-entreprise').click(function () {
          getDropdownItem();
          refresh('entreprise', idStatus.toString(), null, idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), idFormateurs.toString());
          $('.selectedFilter_entreprise').addClass('hidden');
          $('.unselectedFilter_entreprise').removeClass('hidden');
        });


        // ================ TYPE =====================
        $($('.type_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idTypes.push($(this).val());
            for (let i = 0; i < res.types.length; i++) {
              if ($(this).val() == res.types[i].project_type) {
                sumTypes += res.types[i].projet_nb;
              }
            }
          }
        });

        $('.countSelected_type').text(sumTypes);
        $('.countedButton_type').click(function (e) {
          e.preventDefault();
          if (sumTypes > 0) {
            $('.selectedFilter_type').removeClass('hidden');
            $('.unselectedFilter_type').addClass('hidden');
          } else {
            $('.selectedFilter_type').addClass('hidden');
            $('.unselectedFilter_type').removeClass('hidden');
          }
          closDrop();
        });

        $('.resetClick_type').click(function () {
          refresh('type', idStatus.toString(), idEtps.toString(), null, idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), idFormateurs.toString());
        });

        $('.iconClose-type').click(function () {
          getDropdownItem();
          refresh('type', idStatus.toString(), idEtps.toString(), null, idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), idFormateurs.toString());
          $('.selectedFilter_type').addClass('hidden');
          $('.unselectedFilter_type').removeClass('hidden');
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
          refresh('periode', idStatus.toString(), idEtps.toString(), idTypes.toString(), null, idModules.toString(), idVilles.toString(), idFinancements.toString(), idFormateurs.toString());
          $('.selectedFilter_periode').addClass('hidden');
          $('.unselectedFilter_periode').removeClass('hidden');
        });


        // ================ MODULE =====================
        $($('.module_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idModules.push($(this).val());
            for (let i = 0; i < res.modules.length; i++) {
              if ($(this).val() == res.modules[i].idModule) {
                sumModules += res.modules[i].projet_nb;
              }
            }
          }
        });

        $('.countSelected_cours').text(sumModules);
        $('.countedButton_cours').click(function (e) {
          e.preventDefault();
          if (sumModules > 0) {
            $('.selectedFilter_cours').removeClass('hidden');
            $('.unselectedFilter_cours').addClass('hidden');
          } else {
            $('.selectedFilter_cours').addClass('hidden');
            $('.unselectedFilter_cours').removeClass('hidden');
          }
          closDrop();
        });

        $('.resetClick_cours').click(function () {
          refresh('cours', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, null, idVilles.toString(), idFinancements.toString(), idFormateurs.toString());
        });

        $('.iconClose-cours').click(function () {
          getDropdownItem();
          refresh('cours', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, null, idVilles.toString(), idFinancements.toString(), idFormateurs.toString());
          $('.selectedFilter_cours').addClass('hidden');
          $('.unselectedFilter_cours').removeClass('hidden');
        });


        // ================ VILLE =====================
        $($('.ville_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idVilles.push($(this).val());
            for (let i = 0; i < res.villes.length; i++) {
              if ($(this).val() == res.villes[i].idVille) {
                sumVilles += res.villes[i].projet_nb;
              }
            }
          }
        });

        $('.countSelected_ville').text(sumVilles);
        $('.countedButton_ville').click(function (e) {
          e.preventDefault();
          if (sumVilles > 0) {
            $('.selectedFilter_ville').removeClass('hidden');
            $('.unselectedFilter_ville').addClass('hidden');
          } else {
            $('.selectedFilter_ville').addClass('hidden');
            $('.unselectedFilter_ville').removeClass('hidden');
          }
          closDrop();
        });

        $('.resetClick_ville').click(function () {
          refresh('ville', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), null, idFinancements.toString(), idFormateurs.toString());
        });

        $('.iconClose-ville').click(function () {
          getDropdownItem();
          refresh('ville', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), null, idFinancements.toString(), idFormateurs.toString());
          $('.selectedFilter_ville').addClass('hidden');
          $('.unselectedFilter_ville').removeClass('hidden');
        });


        // ================ FINANCEMENT =====================
        $($('.financement_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idFinancements.push($(this).val());
            for (let i = 0; i < res.financements.length; i++) {
              if ($(this).val() == res.financements[i].idPaiement) {
                sumFinancements += res.financements[i].projet_nb;
              }
            }
          }
        });

        $('.countSelected_financement').text(sumFinancements);
        $('.countedButton_financement').click(function (e) {
          e.preventDefault();
          if (sumFinancements > 0) {
            $('.selectedFilter_financement').removeClass('hidden');
            $('.unselectedFilter_financement').addClass('hidden');
          } else {
            $('.selectedFilter_financement').addClass('hidden');
            $('.unselectedFilter_financement').removeClass('hidden');
          }
          closDrop();
        });

        $('.resetClick_financement').click(function () {
          refresh('financement', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), null, idFormateurs.toString());
        });

        $('.iconClose-financement').click(function () {
          getDropdownItem();
          refresh('financement', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), null, idFormateurs.toString());
          $('.selectedFilter_financement').addClass('hidden');
          $('.unselectedFilter_financement').removeClass('hidden');
        });

        // ================ FORMATEUR =====================
        $($('.formateur_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idFormateurs.push($(this).val());
            for (let i = 0; i < res.formateurs.length; i++) {
              if ($(this).val() == res.formateurs[i].idFormateur) {

                sumFormateurs += res.formateurs[i].projet_nb;
                // sumFormateurs = parseInt(sessionStorage.getItem('projet_nb'));
              }
            }
            // sumFormateurs = parseInt(sessionStorage.getItem('projet_nb'));

          }
        });

        $('.countSelected_formateur').text(sumFormateurs);
        $('.countedButton_formateur').click(function (e) {
          e.preventDefault();
          if (sumFormateurs > 0) {
            $('.selectedFilter_formateur').removeClass('hidden');
            $('.unselectedFilter_formateur').addClass('hidden');
          } else {
            $('.selectedFilter_formateur').addClass('hidden');
            $('.unselectedFilter_formateur').removeClass('hidden');
          }
          closDrop();
        });

        $('.resetClick_formateur').click(function () {
          refresh('formateur', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), null);
        });

        $('.iconClose-formateur').click(function () {
          getDropdownItem();
          refresh('formateur', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), null);
          $('.selectedFilter_formateur').addClass('hidden');
          $('.unselectedFilter_formateur').removeClass('hidden');
        });

        // ================ FIN =====================

        filterItems(idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString());
        //affiche pour intra...
        filterItem(idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), idFormateurs.toString());
      });
    }
  });
}

function filterItems(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement, idFormateur) {
  var showResult = $('.showResult');
  var status = $('#filterStatut');
  var etps = $('#filterEntreprise');
  var types = $('#filterType');
  var periodes = $('#filterPeriode');
  var modules = $('#filterModule');
  var villes = $('#filterVille');
  var financements = $('#filterFinancement');
  var formateurs = $('#filterFormateur');

  $.ajax({
    type: "get",
    url: "/etp/projets/filter/items",
    data: {
      idStatut: idStatut,
      idEtp: idEtp,
      idType: idType,
      idPeriode: idPeriode,
      idModule: idModule,
      idVille: idVille,
      idFinancement: idFinancement,
      idFormateur: idFormateur,
    },
    dataType: "json",
    beforeSend: function () {
      var content_grid_project = $('#headDate');
      content_grid_project.html('');
      content_grid_project.append(lineProgress());
      const $progressBar = $('#progress-bar');
      let progress = 0;
      const interval = setInterval(() => {
        if (progress >= 98) {
          clearInterval(interval);
        } else {
          progress += 1;
          $progressBar.css('width', `${progress}%`);
        }
      }, 8); //8ms
    },
    complete: function () {
      $('.loadingProjectFilttered').hide();
    },
    success: function (res) {

      console.log('ITEMS-->', res)

      showResult.html('');
      if ($('.statut_item_checkbox').is(':checked')) {
        etps.empty();
        types.empty();
        periodes.empty();
        modules.empty();
        villes.empty();
        financements.empty();
        formateurs.empty();

        // if (res.etps.length <= 0) {
        //   etps.append(`<h3>Aucun résultat</h3>`);
        // } else {
        //   $.each(res.etps, function (i, v) {
        //     etps.append(`<li class="etp_item_` + v.idEtp + `">
        //                       <div class="grid grid-cols-6">
        //                         <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.etp_name + `">
        //                           <span class="inline-flex items-center gap-2 px-2">
        //                             <input 
        //                               type="checkbox"
        //                               value="`+ v.idEtp + `"
        //                               class="etp_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
        //                             <p class="text-gray-500">`+ v.etp_name + `</p>
        //                           </span>
        //                         </div>
        //                         <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
        //                       </div>
        //                     </li>`);
        //   });
        // }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          console.log('RES-ITEMS-STATUT-->', res);
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="type_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="ville_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        // if (res.financements.length <= 0) {
        //   financements.append(`<h3>Aucun résultat</h3>`);
        // } else {
        //   $.each(res.financements, function (i, v) {
        //     financements.append(`<li class="financement_item_` + v.idPaiement + `">
        //                       <div class="grid grid-cols-6">
        //                         <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.paiement + `">
        //                           <span class="inline-flex items-center gap-2 px-2">
        //                             <input 
        //                               type="checkbox"
        //                               value="`+ v.idPaiement + `"
        //                               class="financement_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
        //                             <p class="text-gray-500">`+ v.paiement + `</p>
        //                           </span>
        //                         </div>
        //                         <div class="grid cols-span-1 items-center justify-end nb_proj_financement_`+ v.idPaiement + `">` + v.projet_nb + `</div>
        //                       </div>
        //                     </li>`);
        //   });
        // }
        if (res.formateurs) {
          if (res.formateurs.length <= 0) {
            formateurs.append(`<h3>Aucun résultat</h3>`);
          } else {
            $.each(res.formateurs, function (i, v) {
              formateurs.append(`<li class="formateur_item_` + v.idFormateur + `">
                                <div class="grid grid-cols-6">
                                  <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.form_name + `` + v.form_firstname + `">
                                    <span class="inline-flex items-center gap-2 px-2">
                                      <input 
                                        type="checkbox"
                                        value="`+ v.idFormateur + `"
                                        class="formateur_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                      <p class="text-gray-500">`+ v.form_name + ` ` + v.form_firstname + `</p>
                                    </span>
                                  </div>
                                  <div class="grid cols-span-1 items-center justify-end nb_proj_formateur_`+ v.idFormateur + `">` + v.projet_nb + `</div>
                                </div>
                              </li>`);
            });
          }
        }

        if (res.periodePrev3 != null) {
          $('#p_vp_3').val(res.periodePrev3.p_id_periode);
          $('.p_np_3').text(res.periodePrev3.projet_nb);
        } else {
          $('#p_vp_3').val(0);
          $('.p_np_3').text(0);
        }

        if (res.periodePrev6 != null) {
          $('#p_vp_6').val(res.periodePrev6.p_id_periode);
          $('.p_np_6').text(res.periodePrev6.projet_nb);
        } else {
          $('#p_vp_6').val(0);
          $('.p_np_6').text(0);
        }

        if (res.periodePrev12 != null) {
          $('#p_vp_12').val(res.periodePrev12.p_id_periode);
          $('.p_np_12').text(res.periodePrev12.projet_nb);
        } else {
          $('#p_vp_12').val(0);
          $('.p_np_12').text(0);
        }

        if (res.periodeNext3 != null) {
          $('#p_vn_3').val(res.periodeNext3.p_id_periode);
          $('.p_nn_3').text(res.periodeNext3.projet_nb);
        } else {
          $('#p_vn_3').val(0);
          $('.p_nn_3').text(0);
        }

        if (res.periodeNext6 != null) {
          $('#p_vn_6').val(res.periodeNext6.p_id_periode);
          $('.p_nn_6').text(res.periodeNext6.projet_nb);
        } else {
          $('#p_vn_6').val(0);
          $('.p_nn_6').text(0);
        }

        if (res.periodeNext12 != null) {
          $('#p_vn_12').val(res.periodeNext12.p_id_periode);
          $('.p_nn_12').text(res.periodeNext12.projet_nb);
        } else {
          $('#p_vn_12').val(0);
          $('.p_nn_12').text(0);
        }

      } else if ($('.etp_item_checkbox').is(':checked')) {
        status.empty();
        types.empty();
        periodes.empty();
        modules.empty();
        villes.empty();
        financements.empty();
        formateurs.empty();

        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {

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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          console.log('RES-ITEMS-TYPE-->', res);
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="type_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="ville_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        // if (res.financements.length <= 0) {
        //   financements.append(`<h3>Aucun résultat</h3>`);
        // } else {
        //   $.each(res.financements, function (i, v) {
        //     financements.append(`<li class="financement_item_` + v.idPaiement + `">
        //                       <div class="grid grid-cols-6">
        //                         <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.paiement + `">
        //                           <span class="inline-flex items-center gap-2 px-2">
        //                             <input 
        //                               type="checkbox"
        //                               value="`+ v.idPaiement + `"
        //                               class="financement_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
        //                             <p class="text-gray-500">`+ v.paiement + `</p>
        //                           </span>
        //                         </div>
        //                         <div class="grid cols-span-1 items-center justify-end nb_proj_financement_`+ v.idPaiement + `">` + v.projet_nb + `</div>
        //                       </div>
        //                     </li>`);
        //   });
        // }
        if (res.formateurs.length <= 0) {
          formateurs.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.formateurs, function (i, v) {
            formateurs.append(`<li class="formateur_item_` + v.idFormateur + `">
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.form_name + ` ` + v.form_firstname + `">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.idFormateur + `"
                                    class="formateur_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">`+ v.form_name + ` ` + v.form_firstname + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_proj_formateur_`+ v.idFormateur + `">` + v.projet_nb + `</div>
                            </div>
                          </li>`);
          });
        }

        if (res.periodePrev3 != null) {
          $('#p_vp_3').val(res.periodePrev3.p_id_periode);
          $('.p_np_3').text(res.periodePrev3.projet_nb);
        } else {
          $('#p_vp_3').val(0);
          $('.p_np_3').text(0);
        }

        if (res.periodePrev6 != null) {
          $('#p_vp_6').val(res.periodePrev6.p_id_periode);
          $('.p_np_6').text(res.periodePrev6.projet_nb);
        } else {
          $('#p_vp_6').val(0);
          $('.p_np_6').text(0);
        }

        if (res.periodePrev12 != null) {
          $('#p_vp_12').val(res.periodePrev12.p_id_periode);
          $('.p_np_12').text(res.periodePrev12.projet_nb);
        } else {
          $('#p_vp_12').val(0);
          $('.p_np_12').text(0);
        }

        if (res.periodeNext3 != null) {
          $('#p_vn_3').val(res.periodeNext3.p_id_periode);
          $('.p_nn_3').text(res.periodeNext3.projet_nb);
        } else {
          $('#p_vn_3').val(0);
          $('.p_nn_3').text(0);
        }

        if (res.periodeNext6 != null) {
          $('#p_vn_6').val(res.periodeNext6.p_id_periode);
          $('.p_nn_6').text(res.periodeNext6.projet_nb);
        } else {
          $('#p_vn_6').val(0);
          $('.p_nn_6').text(0);
        }

        if (res.periodeNext12 != null) {
          $('#p_vn_12').val(res.periodeNext12.p_id_periode);
          $('.p_nn_12').text(res.periodeNext12.projet_nb);
        } else {
          $('#p_vn_12').val(0);
          $('.p_nn_12').text(0);
        }

      } else if ($('.type_item_checkbox').is(':checked')) {
        status.empty();
        etps.empty();
        periodes.empty();
        modules.empty();
        villes.empty();
        financements.empty();
        formateurs.empty();

        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {
          console.log('ITEMS STATUS-->', res.status)
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.etps.length <= 0) {
          etps.append(`<h3>Aucun résultat</h3>`);
        } else {
          console.log('ITEMS ETP-->', res.etps)
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
          console.log('ITEMS MODULES-->', res.modules)
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          console.log('ITEMS VILLE-->', res.villes)
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="ville_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        // if (res.financements.length <= 0) {
        //   financements.append(`<h3>Aucun résultat</h3>`);
        // } else {
        //   console.log('ITEMS FINANCEMENT-->',res.financements)
        //   $.each(res.financements, function (i, v) {
        //     financements.append(`<li class="financement_item_` + v.idPaiement + `">
        //                       <div class="grid grid-cols-6">
        //                         <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.paiement + `">
        //                           <span class="inline-flex items-center gap-2 px-2">
        //                             <input 
        //                               type="checkbox"
        //                               value="`+ v.idPaiement + `"
        //                               class="financement_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
        //                             <p class="text-gray-500">`+ v.paiement + `</p>
        //                           </span>
        //                         </div>
        //                         <div class="grid cols-span-1 items-center justify-end nb_proj_financement_`+ v.idPaiement + `">` + v.projet_nb + `</div>
        //                       </div>
        //                     </li>`);
        //   });
        // }

        if (res.formateurs.length <= 0) {
          formateurs.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.formateurs, function (i, v) {
            formateurs.append(`<li class="formateur_item_` + v.idFormateur + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.form_name + ` ` + v.form_firstname + ` ">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idFormateur + `"
                                      class="formateur_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.form_name + ` ` + v.form_firstname + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_formateur_`+ v.idFormateur + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }


        if (res.periodePrev3 != null) {
          $('#p_vp_3').val(res.periodePrev3.p_id_periode);
          $('.p_np_3').text(res.periodePrev3.projet_nb);
        } else {
          $('#p_vp_3').val(0);
          $('.p_np_3').text(0);
        }

        if (res.periodePrev6 != null) {
          $('#p_vp_6').val(res.periodePrev6.p_id_periode);
          $('.p_np_6').text(res.periodePrev6.projet_nb);
        } else {
          $('#p_vp_6').val(0);
          $('.p_np_6').text(0);
        }

        if (res.periodePrev12 != null) {
          $('#p_vp_12').val(res.periodePrev12.p_id_periode);
          $('.p_np_12').text(res.periodePrev12.projet_nb);
        } else {
          $('#p_vp_12').val(0);
          $('.p_np_12').text(0);
        }

        if (res.periodeNext3 != null) {
          $('#p_vn_3').val(res.periodeNext3.p_id_periode);
          $('.p_nn_3').text(res.periodeNext3.projet_nb);
        } else {
          $('#p_vn_3').val(0);
          $('.p_nn_3').text(0);
        }

        if (res.periodeNext6 != null) {
          $('#p_vn_6').val(res.periodeNext6.p_id_periode);
          $('.p_nn_6').text(res.periodeNext6.projet_nb);
        } else {
          $('#p_vn_6').val(0);
          $('.p_nn_6').text(0);
        }

        if (res.periodeNext12 != null) {
          $('#p_vn_12').val(res.periodeNext12.p_id_periode);
          $('.p_nn_12').text(res.periodeNext12.projet_nb);
        } else {
          $('#p_vn_12').val(0);
          $('.p_nn_12').text(0);
        }

      } else if ($('.periode_item_checkbox').is(':checked')) {
        status.empty();
        etps.empty();
        types.empty();
        modules.empty();
        villes.empty();
        financements.empty();
        formateurs.empty();

        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="type_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="ville_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        // if (res.financements.length <= 0) {
        //   financements.append(`<h3>Aucun résultat</h3>`);
        // } else {
        //   $.each(res.financements, function (i, v) {
        //     financements.append(`<li class="financement_item_` + v.idPaiement + `">
        //                       <div class="grid grid-cols-6">
        //                         <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.paiement + `">
        //                           <span class="inline-flex items-center gap-2 px-2">
        //                             <input 
        //                               type="checkbox"
        //                               value="`+ v.idPaiement + `"
        //                               class="financement_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
        //                             <p class="text-gray-500">`+ v.paiement + `</p>
        //                           </span>
        //                         </div>
        //                         <div class="grid cols-span-1 items-center justify-end nb_proj_financement_`+ v.idPaiement + `">` + v.projet_nb + `</div>
        //                       </div>
        //                     </li>`);
        //   });
        // }
        if (res.formateurs.length <= 0) {
          formateurs.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.formateurs, function (i, v) {
            formateurs.append(`<li class="formateur_item_` + v.idFormateur + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.form_name + ` ` + v.form_firstname + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idFormateur + `"
                                      class="formateur_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.form_name + ` ` + v.form_firstname + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_formateur_`+ v.idFormateur + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

      } else if ($('.module_item_checkbox').is(':checked')) {
        status.empty();
        etps.empty();
        types.empty();
        periodes.empty();
        villes.empty();
        financements.empty();
        formateurs.empty();

        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="type_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="ville_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        // if (res.financements.length <= 0) {
        //   financements.append(`<h3>Aucun résultat</h3>`);
        // } else {
        //   $.each(res.financements, function (i, v) {
        //     financements.append(`<li class="financement_item_` + v.idPaiement + `">
        //                       <div class="grid grid-cols-6">
        //                         <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.paiement + `">
        //                           <span class="inline-flex items-center gap-2 px-2">
        //                             <input 
        //                               type="checkbox"
        //                               value="`+ v.idPaiement + `"
        //                               class="financement_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
        //                             <p class="text-gray-500">`+ v.paiement + `</p>
        //                           </span>
        //                         </div>
        //                         <div class="grid cols-span-1 items-center justify-end nb_proj_financement_`+ v.idPaiement + `">` + v.projet_nb + `</div>
        //                       </div>
        //                     </li>`);
        //   });
        // }
        if (res.formateurs.length <= 0) {
          formateurs.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.formateurs, function (i, v) {
            formateurs.append(`<li class="formateur_item_` + v.idFormateur + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.form_name + ` ` + v.form_firstname + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idFormateur + `"
                                      class="formateur_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.form_name + ` ` + v.form_firstname + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_formateur_`+ v.idFormateur + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.periodePrev3 != null) {
          $('#p_vp_3').val(res.periodePrev3.p_id_periode);
          $('.p_np_3').text(res.periodePrev3.projet_nb);
        } else {
          $('#p_vp_3').val(0);
          $('.p_np_3').text(0);
        }

        if (res.periodePrev6 != null) {
          $('#p_vp_6').val(res.periodePrev6.p_id_periode);
          $('.p_np_6').text(res.periodePrev6.projet_nb);
        } else {
          $('#p_vp_6').val(0);
          $('.p_np_6').text(0);
        }

        if (res.periodePrev12 != null) {
          $('#p_vp_12').val(res.periodePrev12.p_id_periode);
          $('.p_np_12').text(res.periodePrev12.projet_nb);
        } else {
          $('#p_vp_12').val(0);
          $('.p_np_12').text(0);
        }

        if (res.periodeNext3 != null) {
          $('#p_vn_3').val(res.periodeNext3.p_id_periode);
          $('.p_nn_3').text(res.periodeNext3.projet_nb);
        } else {
          $('#p_vn_3').val(0);
          $('.p_nn_3').text(0);
        }

        if (res.periodeNext6 != null) {
          $('#p_vn_6').val(res.periodeNext6.p_id_periode);
          $('.p_nn_6').text(res.periodeNext6.projet_nb);
        } else {
          $('#p_vn_6').val(0);
          $('.p_nn_6').text(0);
        }

        if (res.periodeNext12 != null) {
          $('#p_vn_12').val(res.periodeNext12.p_id_periode);
          $('.p_nn_12').text(res.periodeNext12.projet_nb);
        } else {
          $('#p_vn_12').val(0);
          $('.p_nn_12').text(0);
        }

      } else if ($('.ville_item_checkbox').is(':checked')) {
        status.empty();
        etps.empty();
        types.empty();
        periodes.empty();
        modules.empty();
        financements.empty();
        formateurs.empty();

        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="type_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        // if (res.financements.length <= 0) {
        //   financements.append(`<h3>Aucun résultat</h3>`);
        // } else {
        //   $.each(res.financements, function (i, v) {
        //     financements.append(`<li class="financement_item_` + v.idPaiement + `">
        //                       <div class="grid grid-cols-6">
        //                         <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.paiement + `">
        //                           <span class="inline-flex items-center gap-2 px-2">
        //                             <input 
        //                               type="checkbox"
        //                               value="`+ v.idPaiement + `"
        //                               class="financement_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
        //                             <p class="text-gray-500">`+ v.paiement + `</p>
        //                           </span>
        //                         </div>
        //                         <div class="grid cols-span-1 items-center justify-end nb_proj_financement_`+ v.idPaiement + `">` + v.projet_nb + `</div>
        //                       </div>
        //                     </li>`);
        //   });
        // }
        if (res.formateurs.length <= 0) {
          formateurs.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.formateurs, function (i, v) {
            formateurs.append(`<li class="formateur_item_` + v.idFormateur + `">
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.form_name + ` ` + v.form_firstname + ` ">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.idFormateur + `"
                                    class="formateur_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">`+ v.form_name + ` ` + v.form_firstname + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_proj_formateur_`+ v.idFormateur + `">` + v.projet_nb + `</div>
                            </div>
                          </li>`);
          });
        }

        if (res.periodePrev3 != null) {
          $('#p_vp_3').val(res.periodePrev3.p_id_periode);
          $('.p_np_3').text(res.periodePrev3.projet_nb);
        } else {
          $('#p_vp_3').val(0);
          $('.p_np_3').text(0);
        }

        if (res.periodePrev6 != null) {
          $('#p_vp_6').val(res.periodePrev6.p_id_periode);
          $('.p_np_6').text(res.periodePrev6.projet_nb);
        } else {
          $('#p_vp_6').val(0);
          $('.p_np_6').text(0);
        }

        if (res.periodePrev12 != null) {
          $('#p_vp_12').val(res.periodePrev12.p_id_periode);
          $('.p_np_12').text(res.periodePrev12.projet_nb);
        } else {
          $('#p_vp_12').val(0);
          $('.p_np_12').text(0);
        }

        if (res.periodeNext3 != null) {
          $('#p_vn_3').val(res.periodeNext3.p_id_periode);
          $('.p_nn_3').text(res.periodeNext3.projet_nb);
        } else {
          $('#p_vn_3').val(0);
          $('.p_nn_3').text(0);
        }

        if (res.periodeNext6 != null) {
          $('#p_vn_6').val(res.periodeNext6.p_id_periode);
          $('.p_nn_6').text(res.periodeNext6.projet_nb);
        } else {
          $('#p_vn_6').val(0);
          $('.p_nn_6').text(0);
        }

        if (res.periodeNext12 != null) {
          $('#p_vn_12').val(res.periodeNext12.p_id_periode);
          $('.p_nn_12').text(res.periodeNext12.projet_nb);
        } else {
          $('#p_vn_12').val(0);
          $('.p_nn_12').text(0);
        }

      } else if ($('.financement_item_checkbox').is(':checked')) {
        status.empty();
        etps.empty();
        types.empty();
        periodes.empty();
        modules.empty();
        villes.empty();
        formateurs.empty();

        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="type_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="ville_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.formateurs.length <= 0) {
          formateurs.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.formateurs, function (i, v) {
            formateurs.append(`<li class="formateur_item_` + v.idFormateur + `">
                            <div class="grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.form_name + ` ` + v.form_firstname + ` ">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.idFormateur + `"
                                    class="formateur_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                  <p class="text-gray-500">`+ v.form_name + ` ` + v.form_firstname + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_proj_formateur_`+ v.idFormateur + `">` + v.projet_nb + `</div>
                            </div>
                          </li>`);
          });
        }

        if (res.periodePrev3 != null) {
          $('#p_vp_3').val(res.periodePrev3.p_id_periode);
          $('.p_np_3').text(res.periodePrev3.projet_nb);
        } else {
          $('#p_vp_3').val(0);
          $('.p_np_3').text(0);
        }

        if (res.periodePrev6 != null) {
          $('#p_vp_6').val(res.periodePrev6.p_id_periode);
          $('.p_np_6').text(res.periodePrev6.projet_nb);
        } else {
          $('#p_vp_6').val(0);
          $('.p_np_6').text(0);
        }

        if (res.periodePrev12 != null) {
          $('#p_vp_12').val(res.periodePrev12.p_id_periode);
          $('.p_np_12').text(res.periodePrev12.projet_nb);
        } else {
          $('#p_vp_12').val(0);
          $('.p_np_12').text(0);
        }

        if (res.periodeNext3 != null) {
          $('#p_vn_3').val(res.periodeNext3.p_id_periode);
          $('.p_nn_3').text(res.periodeNext3.projet_nb);
        } else {
          $('#p_vn_3').val(0);
          $('.p_nn_3').text(0);
        }

        if (res.periodeNext6 != null) {
          $('#p_vn_6').val(res.periodeNext6.p_id_periode);
          $('.p_nn_6').text(res.periodeNext6.projet_nb);
        } else {
          $('#p_vn_6').val(0);
          $('.p_nn_6').text(0);
        }

        if (res.periodeNext12 != null) {
          $('#p_vn_12').val(res.periodeNext12.p_id_periode);
          $('.p_nn_12').text(res.periodeNext12.projet_nb);
        } else {
          $('#p_vn_12').val(0);
          $('.p_nn_12').text(0);
        }

      } else if ($('.formateur_item_checkbox').is(':checked')) {
        status.empty();
        etps.empty();
        types.empty();
        periodes.empty();
        modules.empty();
        villes.empty();
        // mois.empty();

        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="type_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

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

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
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
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="ville_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
                                    <p class="text-gray-500">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </div>
                            </li>`);
          });
        }
        /******************************************************************************************************************************************** */
        // if (res.months.length <= 0) {
        //   mois.append(`<h3>Aucun résultat</h3>`);
        // } else {
        //   $.each(res.months, function (i, v) {
        //     mois.append(`<li class="mois_item_` + v.idMois + `">
        //                     <div class="grid grid-cols-6">
        //                       <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.headDate + `">
        //                         <span class="inline-flex items-center gap-2 px-2">
        //                           <input 
        //                             type="checkbox"
        //                             value="`+ v.idMois + `"
        //                             class="mois_item_checkbox appearance-none w-4 h-4 cursor-pointer duration-200 border-[1px] border-gray-300 rounded-sm checked:ring-1 ring-offset-1 checked:ring-[#A462A4] checked:bg-[#A462A4]">
        //                           <p class="text-gray-500">`+ v.headDate + `</p>
        //                         </span>
        //                       </div>
        //                       <div class="grid cols-span-1 items-center justify-end nb_proj_mois_`+ v.idMois + `">` + v.projet_nb + `</div>
        //                     </div>
        //                   </li>`);
        //   });
        // }

        if (res.periodePrev3 != null) {
          $('#p_vp_3').val(res.periodePrev3.p_id_periode);
          $('.p_np_3').text(res.periodePrev3.projet_nb);
        } else {
          $('#p_vp_3').val(0);
          $('.p_np_3').text(0);
        }

        if (res.periodePrev6 != null) {
          $('#p_vp_6').val(res.periodePrev6.p_id_periode);
          $('.p_np_6').text(res.periodePrev6.projet_nb);
        } else {
          $('#p_vp_6').val(0);
          $('.p_np_6').text(0);
        }

        if (res.periodePrev12 != null) {
          $('#p_vp_12').val(res.periodePrev12.p_id_periode);
          $('.p_np_12').text(res.periodePrev12.projet_nb);
        } else {
          $('#p_vp_12').val(0);
          $('.p_np_12').text(0);
        }

        if (res.periodeNext3 != null) {
          $('#p_vn_3').val(res.periodeNext3.p_id_periode);
          $('.p_nn_3').text(res.periodeNext3.projet_nb);
        } else {
          $('#p_vn_3').val(0);
          $('.p_nn_3').text(0);
        }

        if (res.periodeNext6 != null) {
          $('#p_vn_6').val(res.periodeNext6.p_id_periode);
          $('.p_nn_6').text(res.periodeNext6.projet_nb);
        } else {
          $('#p_vn_6').val(0);
          $('.p_nn_6').text(0);
        }

        if (res.periodeNext12 != null) {
          $('#p_vn_12').val(res.periodeNext12.p_id_periode);
          $('.p_nn_12').text(res.periodeNext12.projet_nb);
        } else {
          $('#p_vn_12').val(0);
          $('.p_nn_12').text(0);
        }
      } else {
        getDropdownItem();
      }

      $($('.statut_item_checkbox, .etp_item_checkbox, .type_item_checkbox, .periode_item_checkbox, .module_item_checkbox, .ville_item_checkbox, .financement_item_checkbox, .formateur_item_checkbox')).change(function () {
        var idStatus = [];
        var idEtps = [];
        var idTypes = [];
        var idPeriodes;
        var idModules = [];
        var idVilles = [];
        var idFinancements = [];
        var idFormateurs = [];

        var nb_proj_Status = [];
        var nb_proj_Etps = [];
        var nb_proj_Types = [];
        var nb_proj_Modules = [];
        var nb_proj_Villes = [];
        var nb_proj_Financements = [];
        var nb_proj_Formateurs = [];

        var sumStatus = 0;
        var sumEtps = 0;
        var sumTypes = 0;
        var sumModules = 0;
        var sumVilles = 0;
        var sumFinancements = 0;
        var sumFormateurs = 0;

        // ================== STATUT =================
        $($('.statut_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idStatus.push($(this).val());
            var nb_proj = $('.nb_proj_statut_' + $(this).val());
            nb_proj_Status.push(parseInt(nb_proj.text()));
          } else {
            nb_proj_Status.push(0);
          }
        });

        for (let i = 0; i < nb_proj_Status.length; i++) {
          sumStatus += nb_proj_Status[i];
        }

        $('.resetClick_statut').click(function (e) {
          e.preventDefault();
          const array = $('.statut_item_checkbox');
          for (let i = 0; i < array.length; i++) {
            array[i].checked = false;
            sumStatus = 0;
            $('.countSelected_statut').text(sumStatus);
          }
        });

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

        // ================== ENTREPRISE =================
        $($('.etp_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idEtps.push($(this).val());
            var nb_proj = $('.nb_proj_etp_' + $(this).val());
            nb_proj_Etps.push(parseInt(nb_proj.text()));
          } else {
            nb_proj_Etps.push(0);
          }
        });

        for (let i = 0; i < nb_proj_Etps.length; i++) {
          sumEtps += nb_proj_Etps[i];
        }

        $('.resetClick_entreprise').click(function (e) {
          e.preventDefault();
          const array = $('.etp_item_checkbox');
          for (let i = 0; i < array.length; i++) {
            array[i].checked = false;
            sumEtps = 0;
            $('.countSelected_entreprise').text(sumEtps);
          }
        });

        $('.countSelected_entreprise').text(sumEtps);
        $('.countedButton_entreprise').click(function (e) {
          e.preventDefault();
          if (sumEtps > 0) {
            $('.selectedFilter_entreprise').removeClass('hidden');
            $('.unselectedFilter_entreprise').addClass('hidden');
          } else {
            $('.selectedFilter_entreprise').addClass('hidden');
            $('.unselectedFilter_entreprise').removeClass('hidden');
          }
          closDrop();
        });


        // ================== TYPE =================
        $($('.type_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idTypes.push($(this).val());
            var nb_proj = $('.nb_proj_type_' + $(this).val());
            nb_proj_Types.push(parseInt(nb_proj.text()));
          } else {
            nb_proj_Types.push(0);
          }
        });

        for (let i = 0; i < nb_proj_Types.length; i++) {
          sumTypes += nb_proj_Types[i];
        }

        $('.resetClick_type').click(function (e) {
          e.preventDefault();
          const array = $('.type_item_checkbox');
          for (let i = 0; i < array.length; i++) {
            array[i].checked = false;
            sumTypes = 0;
            $('.countSelected_type').text(sumTypes);
          }
        });

        $('.countSelected_type').text(sumTypes);
        $('.countedButton_type').click(function (e) {
          e.preventDefault();
          if (sumTypes > 0) {
            $('.selectedFilter_type').removeClass('hidden');
            $('.unselectedFilter_type').addClass('hidden');
          } else {
            $('.selectedFilter_type').addClass('hidden');
            $('.unselectedFilter_type').removeClass('hidden');
          }
          closDrop();
        });

        // ================== PERIODE =================
        $($('.periode_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idPeriodes = $(this).val();
          }

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
        });


        // ================== MODULE =================
        $($('.module_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idModules.push($(this).val());
            var nb_proj = $('.nb_proj_module_' + $(this).val());
            nb_proj_Modules.push(parseInt(nb_proj.text()));
          } else {
            nb_proj_Modules.push(0);
          }
        });

        for (let i = 0; i < nb_proj_Modules.length; i++) {
          sumModules += nb_proj_Modules[i];
        }

        $('.resetClick_cours').click(function (e) {
          e.preventDefault();
          const array = $('.module_item_checkbox');
          for (let i = 0; i < array.length; i++) {
            array[i].checked = false;
            sumModules = 0;
            $('.countSelected_cours').text(sumModules);
          }
        });

        $('.countSelected_cours').text(sumModules);
        $('.countedButton_cours').click(function (e) {
          e.preventDefault();
          if (sumModules > 0) {
            $('.selectedFilter_cours').removeClass('hidden');
            $('.unselectedFilter_cours').addClass('hidden');
          } else {
            $('.selectedFilter_cours').addClass('hidden');
            $('.unselectedFilter_cours').removeClass('hidden');
          }
          closDrop();
        });

        // ================== VILLE =================
        $($('.ville_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idVilles.push($(this).val());
            var nb_proj = $('.nb_proj_ville_' + $(this).val());
            nb_proj_Villes.push(parseInt(nb_proj.text()));
          } else {
            nb_proj_Villes.push(0);
          }
        });

        for (let i = 0; i < nb_proj_Villes.length; i++) {
          sumVilles += nb_proj_Villes[i];
        }

        $('.resetClick_ville').click(function (e) {
          e.preventDefault();
          const array = $('.ville_item_checkbox');
          for (let i = 0; i < array.length; i++) {
            array[i].checked = false;
            sumVilles = 0;
            $('.countSelected_ville').text(sumVilles);
          }
        });

        $('.countSelected_ville').text(sumVilles);
        $('.countedButton_ville').click(function (e) {
          e.preventDefault();
          if (sumVilles > 0) {
            $('.selectedFilter_ville').removeClass('hidden');
            $('.unselectedFilter_ville').addClass('hidden');
          } else {
            $('.selectedFilter_ville').addClass('hidden');
            $('.unselectedFilter_ville').removeClass('hidden');
          }
          closDrop();
        });

        // ================== FINANCEMENT =================
        $($('.financement_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idFinancements.push($(this).val());
            var nb_proj = $('.nb_proj_financement_' + $(this).val());
            nb_proj_Financements.push(parseInt(nb_proj.text()));
          } else {
            nb_proj_Financements.push(0);
          }
        });

        for (let i = 0; i < nb_proj_Financements.length; i++) {
          sumFinancements += nb_proj_Financements[i];
        }

        $('.resetClick_financement').click(function (e) {
          e.preventDefault();
          const array = $('.financement_item_checkbox');
          for (let i = 0; i < array.length; i++) {
            array[i].checked = false;
            sumFinancements = 0;
            $('.countSelected_financement').text(sumFinancements);
          }
        });

        $('.countSelected_financement').text(sumFinancements);
        $('.countedButton_financement').click(function (e) {
          e.preventDefault();
          if (sumFinancements > 0) {
            $('.selectedFilter_financement').removeClass('hidden');
            $('.unselectedFilter_financement').addClass('hidden');
          } else {
            $('.selectedFilter_financement').addClass('hidden');
            $('.unselectedFilter_financement').removeClass('hidden');
          }
          closDrop();
        });
        console.log("IDstatus-->", idStatus)

        // ================== FORMATEUR =================
        $($('.formateur_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idFormateurs.push($(this).val());
            var nb_proj = $('.nb_proj_formateur_' + $(this).val());
            nb_proj_Formateurs.push(parseInt(nb_proj.text()));
          } else {
            nb_proj_Formateurs.push(0);
          }
        });

        for (let i = 0; i < nb_proj_Formateurs.length; i++) {
          sumFormateurs += nb_proj_Formateurs[i];
        }
        // sumFormateurs = parseInt(sessionStorage.getItem('projet_nb'));
        $('.resetClick_formateur').click(function (e) {
          e.preventDefault();
          const array = $('.formateur_item_checkbox');
          for (let i = 0; i < array.length; i++) {
            array[i].checked = false;
            sumFormateurs = 0;
            $('.countSelected_formateur').text(sumFormateurs);
          }
        });
        $('.countSelected_formateur').text(sumFormateurs);
        $('.countedButton_formateur').click(function (e) {
          e.preventDefault();
          if (sumFormateurs > 0) {
            $('.selectedFilter_formateur').removeClass('hidden');
            $('.unselectedFilter_formateur').addClass('hidden');
          } else {
            $('.selectedFilter_formateur').addClass('hidden');
            $('.unselectedFilter_formateur').removeClass('hidden');
          }
          closDrop();
        });

        $('.resetClick_formateur').click(function () {
          refresh('formateur', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), null);
        });

        $('.iconClose-formateur').click(function () {
          getDropdownItem();
          refresh('formateur', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), null);
          $('.selectedFilter_formateur').addClass('hidden');
          $('.unselectedFilter_formateur').removeClass('hidden');
        });

        filterItem(idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString()), idFormateurs.toString();
      });

      if (res.projets <= 0) {
        showResult.append(`<p>Aucun résultat</p>`);
      } else {

        _showProjet(res)
        var count_card_filter = $('.count_card_filter');
        var profile_card = $('.profile_card');
        var profile_card_table = [];
        var nombreDeI = 0;

        $.each(profile_card, function () {
          profile_card_table.push('i');
        });

        $.each(profile_card_table, function (index, valeur) {
          // Vérifier si la valeur est égale à 'i'
          if (valeur === 'i') {
            nombreDeI++;  // Incrémenter le compteur si c'est le cas
            count_card_filter.text(nombreDeI + " projet(s) correspond à votre recherche");
          }
        });
      }
    }
  });
}

function filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement, idFormateur) {
  var showResult = $('.showResult');

  $.ajax({
    type: "get",
    url: "/etp/projets/filter/item",
    data: {
      idStatut: idStatut,
      idEtp: idEtp,
      idType: idType,
      idPeriode: idPeriode,
      idModule: idModule,
      idVille: idVille,
      idFinancement: idFinancement,
      idFormateur: idFormateur,
    },
    dataType: "json",
    beforeSend: function () {
      var content_grid_project = $('#headDate');
      content_grid_project.html('');
      content_grid_project.append(lineProgress());
      const $progressBar = $('#progress-bar');
      let progress = 0;
      const interval = setInterval(() => {
        if (progress >= 98) {
          clearInterval(interval);
        } else {
          progress += 1;
          $progressBar.css('width', `${progress}%`);
        }
      }, 8); //8ms
    },
    complete: function () {
      $('.loadingProjectFilttered').hide();
    },
    success: function (res) {
      showResult.empty();
      console.log('ITEM-->', res)
      if (res.projets <= 0) {
        showResult.append(`<p>Aucun résultat</p>`);
      } else {

        _showProjet(res);

        var count_card_filter = $('.count_card_filter');
        var profile_card = $('.profile_card');
        var profile_card_table = [];
        var nombreDeI = 0;

        $.each(profile_card, function () {
          profile_card_table.push('i');
        });

        $.each(profile_card_table, function (index, valeur) {
          // Vérifier si la valeur est égale à 'i'
          if (valeur === 'i') {
            nombreDeI++;  // Incrémenter le compteur si c'est le cas
            count_card_filter.text(nombreDeI + " projet(s) correspond à votre recherche");
          }
        });
      }
    }
  });
}

function refresh(itemClicked, idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement, idFormateur) {
  switch (itemClicked) {
    case "statut":
      $('.statut_item_checkbox').prop('checked', false);
      $('.countSelected_statut').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement, idFormateur)
      break;
    case "entreprise":
      $('.etp_item_checkbox').prop('checked', false);
      $('.countSelected_entreprise').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement, idFormateur)
      break;
    case "type":
      $('.type_item_checkbox').prop('checked', false);
      $('.countSelected_type').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement, idFormateur)
      break;
    case "periode":
      $('.periode_item_checkbox').prop('checked', false);
      $('.countSelected_periode').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement, idFormateur)
      break;
    case "cours":
      $('.module_item_checkbox').prop('checked', false);
      $('.countSelected_cours').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement, idFormateur)
      break;
    case "ville":
      $('.ville_item_checkbox').prop('checked', false);
      $('.countSelected_ville').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement, idFormateur)
      break;
    case "financement":
      $('.financement_item_checkbox').prop('checked', false);
      $('.countSelected_financement').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement, idFormateur)
      break;
    case "formateur":
      $('.formateur_item_checkbox').prop('checked', false);
      $('.countSelected_formateur').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement, idFormateur)
      break;

    default:
      break;
  }

  // location.reload();
}