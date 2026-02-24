function getDropdownItem() {
  var status = $('#filterStatut');
  var etps = $('#filterEntreprise');
  var types = $('#filterType');
  var periodes = $('#filterPeriode');
  var modules = $('#filterModule');
  var villes = $('#filterVille');
  var financements = $('#filterFinancement');

  $.ajax({
    type: "get",
    url: "/formInterne/filter/getDropdownItem",
    dataType: "json",
    success: function (res) {
      status.html('');
      etps.html('');
      types.html('');
      periodes.html('');
      modules.html('');
      villes.html('');
      financements.html('');

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

      $($('.statut_item_checkbox, .etp_item_checkbox, .type_item_checkbox, .periode_item_checkbox, .module_item_checkbox, .ville_item_checkbox, .financement_item_checkbox')).change(function () {
        var idStatus = [];
        var idEtps = [];
        var idTypes = [];
        var idPeriodes;
        var idModules = [];
        var idVilles = [];
        var idFinancements = [];

        var sumStatus = 0;
        var sumEtps = 0;
        var sumTypes = 0;
        var sumModules = 0;
        var sumVilles = 0;
        var sumFinancements = 0;

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
          refresh('statut', null, idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString());
        });

        $('.iconClose-statut').click(function () {
          getDropdownItem();
          refresh('statut', null, idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString());
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
          refresh('entreprise', idStatus.toString(), null, idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString());
        });

        $('.iconClose-entreprise').click(function () {
          getDropdownItem();
          refresh('entreprise', idStatus.toString(), null, idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString());
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
          refresh('type', idStatus.toString(), idEtps.toString(), null, idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString());
        });

        $('.iconClose-type').click(function () {
          getDropdownItem();
          refresh('type', idStatus.toString(), idEtps.toString(), null, idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString());
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
          refresh('periode', idStatus.toString(), idEtps.toString(), idTypes.toString(), null, idModules.toString(), idVilles.toString(), idFinancements.toString());
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
          refresh('cours', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, null, idVilles.toString(), idFinancements.toString());
        });

        $('.iconClose-cours').click(function () {
          getDropdownItem();
          refresh('cours', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, null, idVilles.toString(), idFinancements.toString());
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
          refresh('ville', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), null, idFinancements.toString());
        });

        $('.iconClose-ville').click(function () {
          getDropdownItem();
          refresh('ville', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), null, idFinancements.toString());
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
          refresh('financement', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), null);
        });

        $('.iconClose-financement').click(function () {
          getDropdownItem();
          refresh('financement', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), null);
          $('.selectedFilter_financement').addClass('hidden');
          $('.unselectedFilter_financement').removeClass('hidden');
        });

        // ================ FIN =====================

        // filterItems(idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString());
        //affiche pour intra...
        filterItem(idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString());
      });
    }
  });
}

function filterItems(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement) {
  var showResult = $('.showResult');
  var status = $('#filterStatut');
  var etps = $('#filterEntreprise');
  var types = $('#filterType');
  var periodes = $('#filterPeriode');
  var modules = $('#filterModule');
  var villes = $('#filterVille');
  var financements = $('#filterFinancement');

  $.ajax({
    type: "get",
    url: "/formInterne/filter/items",
    data: {
      idStatut: idStatut,
      idEtp: idEtp,
      idType: idType,
      idPeriode: idPeriode,
      idModule: idModule,
      idVille: idVille,
      idFinancement: idFinancement
    },
    dataType: "json",
    beforeSend: function () {
      showResult.append(`<div class="loadingProjectFilttered spinner-grow text-primary" role="status">
                              <span class="visually-hidden">Loading...</span>
                            </div>`);
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

      } else if ($('.module_item_checkbox').is(':checked')) {
        status.empty();
        etps.empty();
        types.empty();
        periodes.empty();
        villes.empty();
        financements.empty();

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

      $($('.statut_item_checkbox, .etp_item_checkbox, .type_item_checkbox, .periode_item_checkbox, .module_item_checkbox, .ville_item_checkbox, .financement_item_checkbox')).change(function () {
        var idStatus = [];
        var idEtps = [];
        var idTypes = [];
        var idPeriodes;
        var idModules = [];
        var idVilles = [];
        var idFinancements = [];

        var nb_proj_Status = [];
        var nb_proj_Etps = [];
        var nb_proj_Types = [];
        var nb_proj_Modules = [];
        var nb_proj_Villes = [];
        var nb_proj_Financements = [];

        var sumStatus = 0;
        var sumEtps = 0;
        var sumTypes = 0;
        var sumModules = 0;
        var sumVilles = 0;
        var sumFinancements = 0;

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
        filterItem(idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString());
      });

      if (res.projets <= 0) {
        showResult.append(`<p>Aucun résultat</p>`);
      } else {
        $.each(res.projectDates, function (i, v) {
          showResult.append(`<button class="p_head_date_` + v.headMonthDebut + ` accordion text-3xl font-extrabold text-gray-700 my-2 uppercase w-full text-left px-4 py-2 bg-gray-50 hover:bg-gray-100 duration-200 cursor-pointer"></button>
                                <ul class="project_detail_`+ v.headMonthDebut + ` w-full flex flex-col gap-3 mt-4"></ul>`);

          if (v.headDate != null) {
            $('.p_head_date_' + v.headMonthDebut).text(v.headDate);
          } else {
            $('.p_head_date_' + v.headMonthDebut).text('--');
          }

          var project_detail = $('.project_detail_' + v.headMonthDebut);
          project_detail.html('');

          $.each(res.projets, function (key, val) {
            if (v.headDate == val.headDate) {
              project_detail.append(`<div class="min-[350px]:hidden md:hidden lg:hidden xl:block profile_card">
                                      <li id="" class="shadow-md border-[1px] border-gray-100 rounded-md">
                                        <div class="grid grid-cols-6 gap-4">
                                          <div class="grid grid-cols-3 gap-2 col-span-2">
                                            <div
                                              class="p_statut_`+ val.idProjet + ` grid col-span-1 gap-2 bg-gradient-to-br relative overflow-hidden text-white p-3 rounded-l-md">
                                              <div
                                                class="p_type_`+ val.idProjet + ` px-2 py-1 text-white text-sm text-center w-36 absolute -left-10 top-3 -rotate-45 shadow-sm">
                                                <p class="text-white text-sm">`+ val.project_type + `</p>
                                              </div>
                                              <div class="flex flex-col justify-center ml-8 mt-2">
                                                <h5 class="p_date_year_`+ val.idProjet + ` text-white text-xl font-medium"></h5>
                                                <div class="inline-flex items-end gap-2">
                                                  <h5 class="p_date_jour_debut_`+ val.idProjet + ` text-white text-4xl font-semibold"></h5>
                                                  <h5 class="p_date_jour_fin_`+ val.idProjet + ` text-white text-xl"></h5>
                                                </div>
                                                <div class="inline-flex items-end gap-4">
                                                  <h5 class="p_date_mois_debut_`+ val.idProjet + ` text-white text-xl font-semibold"></h5>
                                                  <h5 class="p_date_mois_fin_`+ val.idProjet + ` text-white text-lg"></h5>
                                                </div>
                                              </div>
                                            </div>
                                            <div class="grid grid-cols-subgrid col-span-2 gap-4 p-3">
                                              <div class="grid grid-cols-1 gap-2">
                                                <div class="w-full">
                                                  <span class="p_module_`+ val.idProjet + `"></span>
                                                </div>
                                                <div class="flex flex-col gap-2">
                                                  <div class="grid grid-cols-6 items-center gap-x-6">
                                                    <span class="p_etp_initial_`+ val.idProjet + ` grid grid-cols-subgrid col-span-2"></span>
                                                    <span class="grid grid-cols-subgrid col-span-4">
                                                      <p class="p_nameCfp_`+ val.idProjet + ` text-gray-600"></p>
                                                    </span>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="grid grid-cols-subgrid gap-4 col-span-4 p-3">
                                            <div class="grid grid-cols-5 gap-2">
                                              <div class="grid grid-cols-subgrid gap-4 col-span-2">
                                                <div class="w-full">
                                                  <div class="flex flex-col">
                                                    <h5 class="text-gray-400">Lieu</h5>
                                                    <p class="p_lieu_`+ val.idProjet + ` text-gray-600">
                                                      <span class="p_salle_name_`+ val.idProjet + `"></span>
                                                      <span class="p_salle_quartier_`+ val.idProjet + `"></span> -
                                                      <span class="p_salle_ville_`+ val.idProjet + `"></span> -
                                                      <span class="p_salle_code_postal_`+ val.idProjet + `"></span>
                                                    </p>
                                                  </div>
                                                </div>
                                                <div class="grid grid-cols-3 gap-3">
                                                  <div class="w-full">
                                                    <div class="flex flex-col">
                                                      <p class="text-gray-600">`+ val.seanceCount + ` <span class="text-gray-400">Sessions</span></p>
                                                    </div>
                                                  </div>
                                                  <div class="w-full">
                                                  </div>
                                                  <div class="w-full">
                                                    <div class="flex flex-col">
                                                      <p class="text-gray-600">`+ val.apprCount + ` <span class="text-gray-400">Apprenants</span></p>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid grid-cols-1 gap-3">
                                                <div class="w-full">
                                                  <div class="flex flex-col">
                                                    <h5 class="text-gray-400">Statut</h5>
                                                    <span
                                                      class="p_statut_string_`+ val.idProjet + ` inline-flex items-center gap-2 px-2 py-1 text-sm text-white w-[90px] justify-center">
                                                        `+ val.project_status + `
                                                    </span>
                                                  </div>
                                                </div>
                                                <div class="w-full">
                                                  <div class="flex flex-col">
                                                    <h5 class="text-gray-400">Formateur</h5>
                                                    <p class="p_formateur_`+ val.idProjet + ` text-gray-600 flex flex-row items-center gap-2"></p>
                                                  </div>
                                                </div>
                                              </div>

                                              <div class="grid grid-cols-1 items-start">
                                                <div class="w-full inline-flex items-center gap-1">
                                                  <div class="w-[18px]">
                                                    <i class="fa-solid fa-sack-dollar text-gray-400 text-sm"></i>
                                                  </div>
                                                  <p class="text-gray-500 font-medium">`+ val.paiement + `</p>
                                                </div>

                                                <div class="inline-flex items-center gap-1">
                                                  <div class="w-[18px]">
                                                    <i class="fa-solid fa-dollar text-gray-400 fa-sack-dollar text-sm"></i>
                                                  </div>
                                                  <p class="p_price_`+ val.idProjet + ` text-gray-500 font-medium"></p>
                                                </div>

                                                <div class="inline-flex items-center gap-1">
                                                  <div class="w-[18px]">
                                                    <i class="fa-solid text-gray-400 fa-file-invoice text-sm"></i>
                                                  </div>
                                                  <div class="inline-flex items-center gap-2">
                                                    <p class="text-gray-500 font-medium">20% - Facture n°: #347</p>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid grid-cols-1 gap-3">
                                                <div class="h-full w-full flex items-center gap-1 justify-end">
                                                  <span class="inline-flex items-center gap-2 px-2 py-1 text-blue-500 w-[90px] justify-center">
                                                    Présentielle
                                                  </span>
                                                  <div class="btn-group h-max">
                                                    <button type="button" title="Cliquer pour afficher le menu"
                                                      class="w-8 h-8 bg-[#A462A4] rounded-md hover:bg-[#A462A4]/90 duration-150 cursor-pointer shadow-sm shadow-purple-500"
                                                      data-bs-toggle="dropdown" aria-expanded="false">
                                                      <span class=""><i class="fa-solid fa-bars-staggered text-white"></i></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li class="duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                                          <a class="dropdown-item duration-150 cursor-pointer inline-flex w-full items-center gap-2 text-gray-500"
                                                            href="/etp/projets/`+ val.idProjet + `/detail">
                                                          <i class="fa-solid fa-eye text-base"></i>Aperçu</a>
                                                        </li>
                                                        <span class="p_btn_finish_`+ val.idProjet + `"></span>
                                                    </ul>
                                                  </div>
                                                </div>
                                                <div class="inline-flex items-center justify-end gap-1">
                                                  <i class="fa-solid fa-star text-gray-600 text-sm"></i>
                                                  <p class="text-gray-500 font-medium">4.5</p>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </li>
                                    </div>
                                    <div class="min-[350px]:hidden md:hidden lg:block xl:hidden">
                                      <li id="" class="shadow-md border-[1px] border-gray-100 rounded-md">
                                        <div class="grid grid-cols-1">
                                          <div class="grid col-span-1">
                                            <div class="grid grid-cols-7">
                                              <div
                                                class="p_statut_`+ val.idProjet + ` grid col-span-1 gap-2 bg-gradient-to-br relative rounded-md overflow-hidden text-white p-3 rounded-l-md">
                                                <div
                                                  class="p_type_`+ val.idProjet + ` px-2 py-1 text-white text-sm text-center w-36 absolute -left-10 top-3 -rotate-45 shadow-sm">
                                                  <p class="text-white text-sm">`+ val.project_type + `</p>
                                                </div>
                                                <div class="flex flex-col justify-center ml-8 mt-2">
                                                  <h5 class="p_date_year_`+ val.idProjet + ` text-white text-xl font-medium"></h5>
                                                  <div class="inline-flex items-end gap-2">
                                                    <h5 class="p_date_jour_debut_`+ val.idProjet + ` text-white text-4xl font-semibold"></h5>
                                                    <h5 class="p_date_jour_fin_`+ val.idProjet + ` text-white text-xl"></h5>
                                                  </div>
                                                  <div class="inline-flex items-end gap-4">
                                                    <h5 class="p_date_mois_debut_`+ val.idProjet + ` text-white text-xl font-semibold"></h5>
                                                    <h5 class="p_date_mois_fin_`+ val.idProjet + ` text-white text-lg"></h5>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid grid-cols-subgrid col-span-2 gap-4 p-3">
                                                <div class="grid grid-cols-1 gap-2">
                                                  <div class="w-full">
                                                    <h1 class="p_module_`+ val.idProjet + ` text-gray-600 text-xl font-medium"></h1>
                                                  </div>
                                                  <div class="flex flex-col gap-2">
                                                    <div class="grid grid-cols-6 items-center gap-x-6">
                                                      <span class="p_etp_initial_`+ val.idProjet + ` grid grid-cols-subgrid col-span-2"></span>
                                                      <span class="grid grid-cols-subgrid col-span-4">
                                                        <p class="p_nameCfp_`+ val.idProjet + ` text-gray-600"></p>
                                                      </span>
                                                    </div>
                                                  </div>
                                                  <div class="inline-flex items-center gap-4">
                                                    <div class="">
                                                      <p class="text-gray-600">`+ val.seanceCount + ` <span class="text-gray-400">Sessions</span></p>
                                                    </div>
                                                    <div class="">
                                                    </div>
                                                    <div class="">
                                                      <p class="text-gray-600">`+ val.apprCount + `  <span class="text-gray-400">Apprenants</span></p>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid grid-cols-subgrid col-span-3 p-3">
                                                <div class="flex flex-col gap-2">
                                                  <div class="w-full">
                                                    <div class="inline-flex items-center">
                                                      <div class="w-[24px] flex justify-center items-center">
                                                      </div>
                                                      <span
                                                        class="p_statut_string_`+ val.idProjet + ` inline-flex items-center gap-2 px-2 py-1 text-sm text-white w-[90px] justify-center">
                                                        `+ val.project_status + `
                                                      </span>
                                                    </div>
                                                  </div>

                                                  <div class="w-full">
                                                    <div class="inline-flex items-center">
                                                      <div class="w-[24px] flex justify-center items-center">
                                                        <i class="fa-solid fa-location-dot text-gray-400"></i>
                                                      </div>
                                                      <p class="p_lieu_`+ val.idProjet + ` text-gray-600">
                                                        <span class="p_salle_name_`+ val.idProjet + `"></span>
                                                        <span class="p_salle_quartier_`+ val.idProjet + `"></span> -
                                                        <span class="p_salle_ville_`+ val.idProjet + `"></span> -
                                                        <span class="p_salle_code_postal_`+ val.idProjet + `"></span>
                                                      </p>
                                                    </div>
                                                  </div>

                                                  <div class="w-full">
                                                    <div class="inline-flex items-center">
                                                      <div class="w-[24px] flex justify-center items-center">
                                                        <i class="fa-solid fa-user-graduate text-gray-400"></i>
                                                      </div>
                                                      <p class="p_formateur_`+ val.idProjet + ` text-gray-600 flex flex-row items-center gap-2">
                                                      </p>
                                                    </div>
                                                  </div>

                                                  <div class="inline-flex items-center">
                                                    <div class="w-[24px] flex justify-center items-center">
                                                      <i class="fa-solid fa-sack-dollar text-gray-400"></i>
                                                    </div>
                                                    <p class="text-gray-500 font-medium">`+ val.paiement + `</p>
                                                  </div>

                                                  <div class="inline-flex items-center">
                                                    <div class="w-[24px] flex justify-center items-center">
                                                      <i class="fa-solid fa-dollar text-gray-400"></i>
                                                    </div>
                                                      <p class="p_price_`+ val.idProjet + ` text-gray-500 font-medium"></p>
                                                  </div>

                                                  <div class="inline-flex items-center">
                                                    <div class="w-[24px] flex justify-center items-center">
                                                      <i class="fa-solid text-gray-400 fa-file-invoice"></i>
                                                    </div>
                                                    <p class="text-gray-500 font-medium">
                                                      20% - Facture n°: #347
                                                    </p>
                                                  </div>

                                                </div>
                                              </div>
                                              <div class="grid col-span-1 p-3">
                                                <div class="h-full w-full flex items-center gap-1 justify-end">
                                                  <span class="inline-flex items-center gap-2 px-2 py-1 text-blue-500 w-[90px] justify-center">
                                                    Présentielle
                                                  </span>
                                                  <div class="btn-group h-max">
                                                    <button type="button" title="Cliquer pour afficher le menu"
                                                      class="w-8 h-8 bg-[#A462A4] rounded-md hover:bg-[#A462A4]/90 duration-150 cursor-pointer shadow-sm shadow-purple-500"
                                                      data-bs-toggle="dropdown" aria-expanded="false">
                                                      <span class=""><i class="fa-solid fa-bars-staggered text-white"></i></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li class="duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                                          <a class="dropdown-item duration-150 cursor-pointer inline-flex w-full items-center gap-2 text-gray-500"
                                                            href="/etp/projets/`+ val.idProjet + `/detail">
                                                          <i class="fa-solid fa-eye text-base"></i>Aperçu</a>
                                                        </li>
                                                        <span class="p_btn_finish_`+ val.idProjet + `"></span>
                                                    </ul>
                                                  </div>
                                                </div>
                                                <div class="inline-flex items-center justify-end gap-1">
                                                  <i class="fa-solid fa-star text-gray-600 text-sm"></i>
                                                  <p class="text-gray-500 font-medium">4.5</p>
                                                </div>
                                              </div>
                                            </div>
                                            <div class="grid grid-cols-6 gap-4 py-2">
                                              <div class="grid col-span-1"></div>
                                              <div class="grid grid-cols-subgrid col-span-5">
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </li>
                                    </div>
                                    <div class="min-[350px]:block lg:hidden xl:hidden">
                                      <li id="" class="shadow-md border-[1px] min-w-[550px] overflow-x-scroll border-gray-100 rounded-md">
                                        <div class="grid grid-cols-1">
                                          <div class="grid col-span-1">
                                            <div class="grid grid-cols-6">
                                              <div
                                                class="p_statut_`+ val.idProjet + ` grid col-span-2 gap-2 bg-gradient-to-br relative rounded-md overflow-hidden text-white p-3 rounded-l-md">
                                                <div
                                                  class="p_type_`+ val.idProjet + ` px-2 py-1 text-white text-sm text-center w-36 absolute -left-10 top-3 -rotate-45 shadow-sm">
                                                  <p class="text-white text-sm">`+ val.project_type + `</p>
                                                </div>
                                                <div class="flex flex-col justify-center ml-8 mt-2">
                                                  <h5 class="p_date_year_`+ val.idProjet + ` text-white text-xl font-medium"></h5>
                                                  <div class="inline-flex items-end gap-2">
                                                    <h5 class="p_date_jour_debut_`+ val.idProjet + ` text-white text-4xl font-semibold"></h5>
                                                    <h5 class="p_date_jour_fin_`+ val.idProjet + ` text-white text-xl"></h5>
                                                  </div>
                                                  <div class="inline-flex items-end gap-4">
                                                    <h5 class="p_date_mois_debut_`+ val.idProjet + ` text-white text-xl font-semibold"></h5>
                                                    <h5 class="p_date_mois_fin_`+ val.idProjet + ` text-white text-lg"></h5>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid grid-cols-subgrid col-span-3 gap-4 p-3">
                                                <div class="grid grid-cols-1 gap-2">
                                                  <div class="w-full">
                                                  <h1 class="p_module_`+ val.idProjet + ` text-gray-600 text-xl font-medium"></h1>
                                                  </div>
                                                  <div class="flex flex-col gap-2">
                                                    <div class="grid grid-cols-6 items-center gap-x-6">
                                                      <span class="p_etp_initial_`+ val.idProjet + ` grid grid-cols-subgrid col-span-2">
                                                      </span>
                                                      <span class="grid grid-cols-subgrid col-span-4">
                                                        <p class="p_nameCfp_`+ val.idProjet + ` text-gray-600"></p>
                                                      </span>
                                                    </div>
                                                  </div>
                                                  <div class="inline-flex items-center gap-4">
                                                    <div class="">
                                                    <p class="text-gray-600">`+ val.seanceCount + ` <span  class="text-gray-400">Sessions</span></p>
                                                    </div>
                                                    <div class="">
                                                    </div>
                                                    <div class="">
                                                    <p class="text-gray-600">`+ val.apprCount + `  <span class="text-gray-400">Apprenants</span></p>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid col-span-1 p-3">
                                                <div class="grid col-span-1 items-start h-full justify-end">
                                                  <div class="btn-group h-max">
                                                    <button type="button" title="Cliquer pour afficher le menu"
                                                      class="w-8 h-8 bg-[#A462A4] rounded-md hover:bg-[#A462A4]/90 duration-150 cursor-pointer shadow-sm shadow-purple-500"
                                                      data-bs-toggle="dropdown" aria-expanded="false">
                                                      <span class=""><i class="fa-solid fa-bars-staggered text-white"></i></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li class="duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                                          <a class="dropdown-item duration-150 cursor-pointer inline-flex w-full items-center gap-2 text-gray-500"
                                                            href="/etp/projets/`+ val.idProjet + `/detail">
                                                          <i class="fa-solid fa-eye text-base"></i>Aperçu</a>
                                                        </li>
                                                        <span class="p_btn_finish_`+ val.idProjet + `"></span>
                                                    </ul>
                                                  </div>
                                                </div>
                                                <div class="grid col-span-1 items-end justify-end">
                                                  <div class="inline-flex items-center justify-end gap-1">
                                                    <i class="fa-solid fa-star text-gray-600 text-sm"></i>
                                                    <p class="text-gray-500 font-medium">4.5</p>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="grid col-span-1 p-3">
                                            <hr class="border-[1px] border-gray-200 my-2">
                                            <div class="flex flex-col gap-1">
                                              <div class="w-full">
                                                <div class="grid grid-cols-3">
                                                  <div class="grid col-span-1">
                                                    <div class="inline-flex items-center">
                                                      <div class="w-[24px] flex justify-center items-center">
                                                      </div>
                                                      <span
                                                        class="p_statut_string_`+ val.idProjet + ` inline-flex items-center gap-2 px-2 py-1 text-sm text-white w-[90px] justify-center">` + val.project_status + `
                                                      </span>
                                                    </div>
                                                  </div>

                                                  <div class="inline-flex items-center">
                                                    <div class="w-[24px] flex justify-center items-center">
                                                    </div>
                                                    <span class="inline-flex items-center text-blue-500 justify-center">
                                                      Présentielle
                                                    </span>
                                                  </div>

                                                  <div class="grid col-span-1">
                                                    <div class="inline-flex items-center">
                                                      <div class="w-[24px] flex justify-center items-center">
                                                        <i class="fa-solid fa-user-graduate text-gray-400"></i>
                                                      </div>
                                                      <p class="p_formateur_`+ val.idProjet + ` text-gray-600 flex flex-row items-center gap-2"></p>
                                                    </div>
                                                  </div>

                                                </div>
                                              </div>
                                              <hr class="border-[1px] border-gray-200 my-2">

                                              <div class="w-full">
                                                <div class="inline-flex items-center">
                                                  <div class="w-[24px] flex justify-center items-center">
                                                    <i class="fa-solid fa-location-dot text-gray-400"></i>
                                                  </div>
                                                  <p class="p_lieu_`+ val.idProjet + ` text-gray-600">
                                                    <span class="p_salle_name_`+ val.idProjet + `"></span>
                                                    <span class="p_salle_quartier_`+ val.idProjet + `"></span> -
                                                    <span class="p_salle_ville_`+ val.idProjet + `"></span> -
                                                    <span class="p_salle_code_postal_`+ val.idProjet + `"></span>
                                                  </p>
                                                </div>
                                              </div>
                                              <hr class="border-[1px] border-gray-200 my-2">

                                              <div class="grid grid-cols-3 gap-2">
                                                <div class="inline-flex items-center">
                                                  <div class="w-[24px] flex justify-center items-center">
                                                    <i class="fa-solid fa-sack-dollar text-gray-400"></i>
                                                  </div>
                                                  <p class="text-gray-500 font-medium">`+ val.paiement + `</p>
                                                </div>

                                                <div class="inline-flex items-center">
                                                  <div class="w-[24px] flex justify-center items-center">
                                                    <i class="fa-solid fa-dollar text-gray-400"></i>
                                                  </div>
                                                  <p class="p_price_`+ val.idProjet + ` text-gray-500 font-medium"></p>
                                                </div>

                                                <div class="inline-flex items-center">
                                                  <div class="w-[24px] flex justify-center items-center">
                                                    <i class="fa-solid text-gray-400 fa-file-invoice"></i>
                                                  </div>
                                                  <p class="text-gray-500 font-medium">
                                                    20% - Facture n°: #347
                                                  </p>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </li>
                                    </div>


                                    <div class="modal fade" id="supprimerProjet`+ val.idProjet + `" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                                      aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                      <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content bg-white border-none justify-center gap-2 rounded-md" id="lottieAnimation">
                                          <div class="flex flex-col rounded items-center gap-1">
                                            <lottie-player src="/Animations/Delete.json" background="transparent" speed="1"
                                              style="width: 60px; height: 50px;" loop autoplay></lottie-player>
                                            <h1 class="text-gray-600 text-2xl font-semibold flex flex-1" id="staticBackdropLabel">
                                              Suppression ?
                                            </h1>
                                          </div>
                                          <p class="text-base text-gray-500 text-center px-4">Voulez-vous vraiment surpprimer ce projet ?</p>
                                          <div class="p-3 inline-flex gap-3 justify-center">
                                            <button type="button"
                                              class="border-[1px] border-gray-600 text-gray-600 text-base hover:text-gray-700 scale-95 hover:scale-100 rounded-md px-4 py-2 transition duration-200"
                                              data-bs-dismiss="modal" data-bs-dismiss="tooltip">Non,
                                              annuler</button>
                                            <button type="button" onclick="manageProject('delete', '/etp/projets/`+ val.idProjet + `/destroy')"
                                              class="bg-gray-700 text-white text-base rounded-md px-4 py-2 scale-95 hover:scale-100 hover:bg-gray-800 transition duration-200"
                                              data-bs-dismiss="modal">Oui, je confirme</button>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="modal fade" id="annulerProjet`+ val.idProjet + `" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                                      aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                      <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content bg-white border-none justify-center gap-2 rounded-md" id="lottieAnimation">
                                          <div class="flex flex-col rounded items-center gap-1">
                                            <lottie-player src="/Animations/Delete.json" background="transparent" speed="1"
                                              style="width: 60px; height: 50px;" loop autoplay></lottie-player>
                                            <h1 class="text-gray-600 text-2xl font-semibold flex flex-1" id="staticBackdropLabel">
                                              Annuler
                                            </h1>
                                          </div>
                                          <p class="text-base text-gray-500 text-center px-4">Voulez-vous vraiment annuler cette session ?</p>
                                          <div class="p-3 inline-flex gap-3 justify-center">
                                            <button type="button"
                                              class="border-[1px] border-gray-600 text-gray-600 text-base hover:text-gray-700 scale-95 hover:scale-100 rounded-md px-4 py-2 transition duration-200"
                                              data-bs-dismiss="modal" data-bs-dismiss="tooltip">Non,
                                              annuler</button>
                                            <button type="button" onclick="manageProject('patch', '/etp/projets/`+ val.idProjet + `/cancel')"
                                              class="bg-gray-700 text-white text-base rounded-md px-4 py-2 scale-95 hover:scale-100 hover:bg-gray-800 transition duration-200"
                                              data-bs-dismiss="modal">Oui, je confirme</button>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="modal fade" id="reporterProjet`+ val.idProjet + `" data-bs-backdrop="static" data-bs-keyboard="false"
                                      tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                      <div class="modal-dialog flex items-center justify-center">
                                        <div class="modal-content bg-white border-none w-[320px] justify-center gap-2 rounded-xl" id="lottieAnimation">
                                          <div class="h-full w-full flex flex-col gap-2 p-4">
                                            <label class="text-xl font-medium text-gray-500 text-center mb-4">A Reporter le</label>
                                            <div class="w-full inline-flex justify-center">
                                              <div id="nav"></div>
                                            </div>
                                            <div class="inline-flex items-center gap-2 mb-4">
                                              <div class="w-full inline-flex relative">
                                                <input type="date" id="dateDebutProjetDetail" name="dateDebutProjetDetail" class="outline-none bg-white w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400">
                                              </div>
                                              <div class="w-full inline-flex relative">
                                                <input type="date" id="dateFinProjetDetail" name="dateFinProjetDetail" class="outline-none bg-white w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400">
                                              </div>
                                            </div>
                                            <div class="w-full inline-flex items-center gap-2 justify-between">
                                              <button 
                                                type="button" 
                                                data-bs-dismiss="modal" 
                                                data-dismiss="modal"
                                                class="focus:outline-none px-3 bg-gray-200 py-2 ml-3 rounded-md text-gray-600 hover:bg-gray-300/80 transition duration-200 text-base">Annuler</button>
                                              <button 
                                                type="button" 
                                                onclick="repportProject(`+ val.idProjet + `)"
                                                class="focus:outline-none px-3 bg-[#A462A4] py-2 ml-3 rounded-md text-white hover:bg-[#A462A4]/90 transition duration-200 text-base">Oui, je confirme</button>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>`);

              var p_statut = $('.p_statut_' + val.idProjet);
              var p_type = $('.p_type_' + val.idProjet);
              var p_statut_string = $('.p_statut_string_' + val.idProjet);
              var p_date_year = $('.p_date_year_' + val.idProjet);
              var p_date_jour_debut = $('.p_date_jour_debut_' + val.idProjet);
              var p_date_jour_fin = $('.p_date_jour_fin_' + val.idProjet);
              var p_date_mois_debut = $('.p_date_mois_debut_' + val.idProjet);
              var p_date_mois_fin = $('.p_date_mois_fin_' + val.idProjet);
              var p_module = $('.p_module_' + val.idProjet);
              var p_etp_initial = $('.p_etp_initial_' + val.idProjet);

              var p_etp_name = $('.p_etp_name_' + val.idProjet);

              var p_nameCfp = $('.p_nameCfp_' + val.idProjet);


              var p_lieu = $('.p_lieu_' + val.idProjet);
              var p_salle_name = $('.p_salle_name_' + val.idProjet);
              var p_salle_quartier = $('.p_salle_quartier_' + val.idProjet);
              var p_salle_ville = $('.p_salle_ville_' + val.idProjet);
              var p_salle_code_postal = $('.p_salle_code_postal_' + val.idProjet);
              var p_price = $('.p_price_' + val.idProjet);
              var p_formateur = $('.p_formateur_' + val.idProjet);
              var p_btn_finish = $('.p_btn_finish_' + val.idProjet);

              switch (val.project_status) {
                case "En préparation":
                  p_statut.addClass('from-[#66CDAA] to-[#66CDAA]/50');
                  p_statut_string.addClass('bg-[#66CDAA]');
                  break;
                case "Réservé":
                  p_statut.addClass('from-[#33303D] to-[#33303D]/50');
                  p_statut_string.addClass('bg-[#33303D]');
                  break;
                case "En cours":
                  p_statut.addClass('from-[#1E90FF] to-[#1E90FF]/50');
                  p_statut_string.addClass('bg-[#1E90FF]');
                  break;
                case "Terminé":
                  p_statut.addClass('from-[#32CD32] to-[#32CD32]/50');
                  p_statut_string.addClass('bg-[#32CD32]');
                  break;
                case "Annulé":
                  p_statut.addClass('from-[#FF6347] to-[#FF6347]/50');
                  p_statut_string.addClass('bg-[#FF6347]');
                  break;
                case "Reporté":
                  p_statut.addClass('from-[#2E705A] to-[#2E705A]/50');
                  p_statut_string.addClass('bg-[#2E705A]');
                  break;
                case "Planifié":
                  p_statut.addClass('from-[#2552BA] to-[#2552BA]/50');
                  p_statut_string.addClass('bg-[#2552BA]');
                  break;
                default:
                  p_statut.addClass('from-[#66FDAA] to-[#66FDAA]/50')
                  break;
              }

              switch (val.project_type) {
                case "Intra":
                  p_type.addClass('bg-[#1565c0]');
                  break;
                case "Inter":
                  p_type.addClass('bg-[#7209b7]');
                case "Interne":
                  p_type.addClass('bg-[#7F055F]');
                  break;

                default:
                  break;
              }

              if (val.dateDebut != null) {
                p_date_year.text(val.headYear);
              } else {
                p_date_year.text('--');
              }

              if (val.dateDebut != null) {
                p_date_mois_debut.text(val.headMonthDebut);
              } else {
                p_date_mois_debut.text('--');
              }

              if (val.dateDebut != null) {
                p_date_mois_fin.text(val.headMonthFin);
              } else {
                p_date_mois_fin.text('--');
              }

              if (val.dateDebut != null) {
                p_date_jour_debut.text(val.headDayDebut);
              } else {
                p_date_jour_debut.text('--');
              }

              if (val.dateDebut != null) {
                p_date_jour_fin.text(val.headDayFin);
              } else {
                p_date_jour_fin.text('--');
              }

              p_module.html('');
              if (val.module_name != null && val.module_name != "Default module") {
                p_module.append(`<h1 class="text-gray-600 text-xl font-medium" title="` + val.module_name + `">` + val.module_name + `</h1>`);
              } else {
                p_module.append(`<span class="text-gray-600">--</span>`);
              }

              p_etp_initial.html('');

              // if (val.logoCfp != null) {
              //   p_etp_initial.append(`<div class="w-28 h-16 bg-gray-200 rounded-xl relative">
              //                             <x-icon-badge />
              //                             <img src="/img/entreprises/`+ val.logoCfp + `" alt="logo"
              //                               class="w-full h-full rounded-xl object-cover">
              //                           </div>`);
              // } else if (val.logoCfp == null && val.initialnameCfp != null) {
              //   p_etp_initial.append(`<span style="background: #e5e7eb; padding: 2px 8px; border-radius: 50%; cursor: pointer;">` + val.initialnameCfp + `</span>`);
              // } else {
              //   p_etp_initial.append(`<span class="text-gray-400">----</span>`);
              // }




              // if (val.etp_logo != null) {
              //   p_etp_initial.append(`<div class="w-28 h-16 bg-gray-200 rounded-xl relative">
              //                             <x-icon-badge />
              //                             <img src="/img/entreprises/`+ val.etp_logo + `" alt="logo"
              //                               class="w-full h-full rounded-xl object-cover">
              //                           </div>`);
              // } else if (val.etp_logo == null && val.etp_initial_name != null) {
              //   p_etp_initial.append(`<span style="background: #e5e7eb; padding: 2px 8px; border-radius: 50%; cursor: pointer;">` + val.etp_initial_name + `</span>`);
              // } else {
              //   p_etp_initial.append(`<span class="text-gray-400">--</span>`);
              //}





              if (val.nameCfp != null) {
                p_nameCfp.text(val.nameCfp);
              } else {
                p_nameCfp.text('--');
              }

              if (val.etp_name != null) {
                p_etp_name.text(val.etp_name);
              } else {
                p_etp_name.text('-----');
              }

              if (val.salle_name != null) {
                p_lieu.attr('title', val.salle_name);
              }

              if (val.salle_name != null) {
                p_salle_name.text(val.salle_name);
              } else {
                p_salle_name.text('--');
              }

              if (val.salle_quartier != null) {
                p_salle_quartier.text(val.salle_quartier);
              } else {
                p_salle_quartier.text('--');
              }

              if (val.ville != null) {
                p_salle_ville.text(val.ville);
              } else {
                p_salle_ville.text('--');
              }

              if (val.salle_code_postal != null) {
                p_salle_code_postal.text(val.salle_code_postal);
              } else {
                p_salle_code_postal.text('--');
              }

              if (val.projectTotalPrice != null) {
                p_price.text('Ar ' + val.projectTotalPrice + ' HT');
              } else {
                p_price.text('Ar -- HT');
              }

              p_formateur.html('');
              if (val.formateurs.length <= 0) {
                p_formateur.append(`<span class="text-gray-400">--</span>`);
              } else {
                $.each(val.formateurs, function (vForm, valForm) {
                  if (valForm.form_photo != null) {
                    p_formateur.append(`<img class="w-8 h-8 object-cover rounded-full cursor-pointer"
                                            src="/img/formateurs/`+ valForm.form_photo + `" alt="photo"
                                            title="`+ valForm.form_name + ` ` + valForm.form_firstname + `">`);
                  } else {
                    p_formateur.append(`<span title="` + valForm.form_name + ` ` + valForm.form_firstname + `"
                        style="background: #e5e7eb; padding: 2px 8px; border-radius: 50%; cursor: pointer;">`+ valForm.form_initial_name + `</span>`);
                  }
                });
              }

              p_btn_finish.html('');
              if (val.project_status != "Terminé") {
                p_btn_finish.append(`<hr class="border-[1px] border-gray-400 my-2">
                                        <li class="dropdown-item duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                          <span>
                                            <i class="fa-solid fa-check text-sm"></i>
                                          </span>
                                          <button type="button" data-bs-toggle="modal" data-bs-target="#confirmerProjet" class="">Valider le projet</button>
                                        </li>
                                        <li class="dropdown-item duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                          <span>
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                          </span>
                                          <button type="button" data-bs-toggle="modal" data-bs-target="#supprimerProjet`+ val.idProjet + `" class="">Supprimer</button>
                                        </li>
                                        <hr class="border-[1px] border-gray-400 my-2">
                                        <span class="p_btn_cancel_`+ val.idProjet + `"></span>
                                        <span class="p_btn_repport_`+ val.idProjet + `"></span>`);
              }

              $('.p_btn_cancel_' + val.idProjet).html('');
              if (val.project_status != "Annulé") {
                $('.p_btn_cancel_' + val.idProjet).append(`<li class="dropdown-item duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                                              <span>
                                                                <i class="fa-solid fa-xmark text-sm"></i>
                                                              </span>
                                                              <button type="button" data-bs-toggle="modal" data-bs-target="#annulerProjet`+ val.idProjet + `" class="">Annuler</button>
                                                            </li>`);
              }

              $('.p_btn_repport_' + val.idProjet).html('');
              if (val.project_status != "Reporté") {
                $('.p_btn_repport_' + val.idProjet).append(`<li class="dropdown-item duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                                              <span>
                                                                <i class="fa-solid fa-right-left text-sm"></i>
                                                              </span>
                                                              <button type="button" data-bs-toggle="modal" data-bs-target="#reporterProjet`+ val.idProjet + `" class="">Reporter</button>
                                                            </li>`);
              }
            }
          });
        });


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

function filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement) {
  var showResult = $('.showResult');

  $.ajax({
    type: "get",
    url: "/formInterne/filter/item",
    data: {
      idStatut: idStatut,
      idEtp: idEtp,
      idType: idType,
      idPeriode: idPeriode,
      idModule: idModule,
      idVille: idVille,
      idFinancement: idFinancement
    },
    dataType: "json",
    beforeSend: function () {
      showResult.append(`<div class="loadingProjectFilttered spinner-grow text-primary" role="status">
                              <span class="visually-hidden">Loading...</span>
                            </div>`);
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
        $.each(res.projectDates, function (i, v) {
          showResult.append(`<button class="p_head_date_` + v.headMonthDebut + ` accordion text-3xl font-extrabold text-gray-700 my-2 uppercase w-full text-left px-4 py-2 bg-gray-50 hover:bg-gray-100 duration-200 cursor-pointer"></button>
                                <ul class="project_detail_`+ v.headMonthDebut + ` w-full flex flex-col gap-3 mt-4"></ul>`);

          if (v.headDate != null) {
            $('.p_head_date_' + v.headMonthDebut).text(v.headDate);
          } else {
            $('.p_head_date_' + v.headMonthDebut).text('--');
          }

          var project_detail = $('.project_detail_' + v.headMonthDebut);
          project_detail.html('');

          $.each(res.projets, function (key, val) {
            console.log('val-->', val)
            if (v.headDate == val.headDate) {
              project_detail.append(`<div class="min-[350px]:hidden md:hidden lg:hidden xl:block profile_card">
                                      <li id="" class="shadow-md border-[1px] border-gray-100 rounded-md">
                                        <div class="grid grid-cols-6 gap-4">
                                          <div class="grid grid-cols-3 gap-2 col-span-2">
                                            <div
                                              class="p_statut_`+ val.idProjet + ` grid col-span-1 gap-2 bg-gradient-to-br relative overflow-hidden text-white p-3 rounded-l-md">
                                              <div
                                                class="p_type_`+ val.idProjet + ` px-2 py-1 text-white text-sm text-center w-36 absolute -left-10 top-3 -rotate-45 shadow-sm">
                                                <p class="text-white text-sm">`+ val.project_type + `</p>
                                              </div>
                                              <div class="flex flex-col justify-center ml-8 mt-2">
                                                <h5 class="p_date_year_`+ val.idProjet + ` text-white text-xl font-medium"></h5>
                                                <div class="inline-flex items-end gap-2">
                                                  <h5 class="p_date_jour_debut_`+ val.idProjet + ` text-white text-4xl font-semibold"></h5>
                                                  <h5 class="p_date_jour_fin_`+ val.idProjet + ` text-white text-xl"></h5>
                                                </div>
                                                <div class="inline-flex items-end gap-4">
                                                  <h5 class="p_date_mois_debut_`+ val.idProjet + ` text-white text-xl font-semibold"></h5>
                                                  <h5 class="p_date_mois_fin_`+ val.idProjet + ` text-white text-lg"></h5>
                                                </div>
                                              </div>
                                            </div>
                                            <div class="grid grid-cols-subgrid col-span-2 gap-4 p-3">
                                              <div class="grid grid-cols-1 gap-2">
                                                <div class="w-full">
                                                  <span class="p_module_`+ val.idProjet + `"></span>
                                                </div>
                                                <div class="flex flex-col gap-2">
                                                  <div class="grid grid-cols-6 items-center gap-x-6">
                                                    <span class="p_etp_initial_`+ val.idProjet + ` grid grid-cols-subgrid col-span-2"></span>
                                                    <span class="grid grid-cols-subgrid col-span-4">
                                                      <p class="p_nameCfp_`+ val.idProjet + ` text-gray-600"></p>
                                                    </span>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="grid grid-cols-subgrid gap-4 col-span-4 p-3">
                                            <div class="grid grid-cols-5 gap-2">
                                              <div class="grid grid-cols-subgrid gap-4 col-span-2">
                                                <div class="w-full">
                                                  <div class="flex flex-col">
                                                    <h5 class="text-gray-400">Lieu</h5>
                                                    <p class="p_lieu_`+ val.idProjet + ` text-gray-600">
                                                      <span class="p_salle_name_`+ val.idProjet + `"></span>
                                                      <span class="p_salle_quartier_`+ val.idProjet + `"></span> -
                                                      <span class="p_salle_ville_`+ val.idProjet + `"></span> -
                                                      <span class="p_salle_code_postal_`+ val.idProjet + `"></span>
                                                    </p>
                                                  </div>
                                                </div>
                                                <div class="grid grid-cols-3 gap-3">
                                                  <div class="w-full">
                                                    <div class="flex flex-col">
                                                      <p class="text-gray-600">`+ val.seanceCount + ` <span class="text-gray-400">Sessions</span></p>
                                                    </div>
                                                  </div>
                                                  <div class="w-full">
                                                  </div>
                                                  <div class="w-full">
                                                    <div class="flex flex-col">
                                                      <p class="text-gray-600">`+ val.apprCount + ` <span class="text-gray-400">Apprenants</span></p>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid grid-cols-1 gap-3">
                                                <div class="w-full">
                                                  <div class="flex flex-col">
                                                    <h5 class="text-gray-400">Statut</h5>
                                                    <span
                                                      class="p_statut_string_`+ val.idProjet + ` inline-flex items-center gap-2 px-2 py-1 text-sm text-white w-[90px] justify-center">
                                                        `+ val.project_status + `
                                                    </span>
                                                  </div>
                                                </div>
                                                <div class="w-full">
                                                  <div class="flex flex-col">
                                                    <h5 class="text-gray-400">Formateur</h5>
                                                    <p class="p_formateur_`+ val.idProjet + ` text-gray-600 flex flex-row items-center gap-2"></p>
                                                  </div>
                                                </div>
                                              </div>

                                              <div class="grid grid-cols-1 items-start">
                                                <div class="w-full inline-flex items-center gap-1">
                                                  <div class="w-[18px]">
                                                    <i class="fa-solid fa-sack-dollar text-gray-400 text-sm"></i>
                                                  </div>
                                                  <p class="text-gray-500 font-medium">`+ val.paiement + `</p>
                                                </div>

                                                <div class="inline-flex items-center gap-1">
                                                  <div class="w-[18px]">
                                                    <i class="fa-solid fa-dollar text-gray-400 fa-sack-dollar text-sm"></i>
                                                  </div>
                                                  <p class="p_price_`+ val.idProjet + ` text-gray-500 font-medium"></p>
                                                </div>

                                                <div class="inline-flex items-center gap-1">
                                                  <div class="w-[18px]">
                                                    <i class="fa-solid text-gray-400 fa-file-invoice text-sm"></i>
                                                  </div>
                                                  <div class="inline-flex items-center gap-2">
                                                    <p class="text-gray-500 font-medium">20% - Facture n°: #347</p>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid grid-cols-1 gap-3">
                                                <div class="h-full w-full flex items-center gap-1 justify-end">
                                                  <span class="inline-flex items-center gap-2 px-2 py-1 text-blue-500 w-[90px] justify-center">
                                                    Présentielle
                                                  </span>
                                                  <div class="btn-group h-max">
                                                    <button type="button" title="Cliquer pour afficher le menu"
                                                      class="w-8 h-8 bg-[#A462A4] rounded-md hover:bg-[#A462A4]/90 duration-150 cursor-pointer shadow-sm shadow-purple-500"
                                                      data-bs-toggle="dropdown" aria-expanded="false">
                                                      <span class=""><i class="fa-solid fa-bars-staggered text-white"></i></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li class="duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                                          <a class="dropdown-item duration-150 cursor-pointer inline-flex w-full items-center gap-2 text-gray-500"
                                                            href="/etp/projets/`+ val.idProjet + `/detail">
                                                          <i class="fa-solid fa-eye text-base"></i>Aperçu</a>
                                                        </li>
                                                        <span class="p_btn_finish_`+ val.idProjet + `"></span>
                                                    </ul>
                                                  </div>
                                                </div>
                                                <div class="inline-flex items-center justify-end gap-1">
                                                  <i class="fa-solid fa-star text-gray-600 text-sm"></i>
                                                  <p class="text-gray-500 font-medium">4.5</p>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </li>
                                    </div>
                                    <div class="min-[350px]:hidden md:hidden lg:block xl:hidden">
                                      <li id="" class="shadow-md border-[1px] border-gray-100 rounded-md">
                                        <div class="grid grid-cols-1">
                                          <div class="grid col-span-1">
                                            <div class="grid grid-cols-7">
                                              <div
                                                class="p_statut_`+ val.idProjet + ` grid col-span-1 gap-2 bg-gradient-to-br relative rounded-md overflow-hidden text-white p-3 rounded-l-md">
                                                <div
                                                  class="p_type_`+ val.idProjet + ` px-2 py-1 text-white text-sm text-center w-36 absolute -left-10 top-3 -rotate-45 shadow-sm">
                                                  <p class="text-white text-sm">`+ val.project_type + `</p>
                                                </div>
                                                <div class="flex flex-col justify-center ml-8 mt-2">
                                                  <h5 class="p_date_year_`+ val.idProjet + ` text-white text-xl font-medium"></h5>
                                                  <div class="inline-flex items-end gap-2">
                                                    <h5 class="p_date_jour_debut_`+ val.idProjet + ` text-white text-4xl font-semibold"></h5>
                                                    <h5 class="p_date_jour_fin_`+ val.idProjet + ` text-white text-xl"></h5>
                                                  </div>
                                                  <div class="inline-flex items-end gap-4">
                                                    <h5 class="p_date_mois_debut_`+ val.idProjet + ` text-white text-xl font-semibold"></h5>
                                                    <h5 class="p_date_mois_fin_`+ val.idProjet + ` text-white text-lg"></h5>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid grid-cols-subgrid col-span-2 gap-4 p-3">
                                                <div class="grid grid-cols-1 gap-2">
                                                  <div class="w-full">
                                                    <h1 class="p_module_`+ val.idProjet + ` text-gray-600 text-xl font-medium"></h1>
                                                  </div>
                                                  <div class="flex flex-col gap-2">
                                                    <div class="grid grid-cols-6 items-center gap-x-6">
                                                      <span class="p_etp_initial_`+ val.idProjet + ` grid grid-cols-subgrid col-span-2"></span>
                                                      <span class="grid grid-cols-subgrid col-span-4">
                                                        <p class="p_nameCfp_`+ val.idProjet + ` text-gray-600"></p>
                                                      </span>
                                                    </div>
                                                  </div>
                                                  <div class="inline-flex items-center gap-4">
                                                    <div class="">
                                                      <p class="text-gray-600">`+ val.seanceCount + ` <span class="text-gray-400">Sessions</span></p>
                                                    </div>
                                                    <div class="">
                                                    </div>
                                                    <div class="">
                                                      <p class="text-gray-600">`+ val.apprCount + `  <span class="text-gray-400">Apprenants</span></p>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid grid-cols-subgrid col-span-3 p-3">
                                                <div class="flex flex-col gap-2">
                                                  <div class="w-full">
                                                    <div class="inline-flex items-center">
                                                      <div class="w-[24px] flex justify-center items-center">
                                                      </div>
                                                      <span
                                                        class="p_statut_string_`+ val.idProjet + ` inline-flex items-center gap-2 px-2 py-1 text-sm text-white w-[90px] justify-center">
                                                        `+ val.project_status + `
                                                      </span>
                                                    </div>
                                                  </div>

                                                  <div class="w-full">
                                                    <div class="inline-flex items-center">
                                                      <div class="w-[24px] flex justify-center items-center">
                                                        <i class="fa-solid fa-location-dot text-gray-400"></i>
                                                      </div>
                                                      <p class="p_lieu_`+ val.idProjet + ` text-gray-600">
                                                        <span class="p_salle_name_`+ val.idProjet + `"></span>
                                                        <span class="p_salle_quartier_`+ val.idProjet + `"></span> -
                                                        <span class="p_salle_ville_`+ val.idProjet + `"></span> -
                                                        <span class="p_salle_code_postal_`+ val.idProjet + `"></span>
                                                      </p>
                                                    </div>
                                                  </div>

                                                  <div class="w-full">
                                                    <div class="inline-flex items-center">
                                                      <div class="w-[24px] flex justify-center items-center">
                                                        <i class="fa-solid fa-user-graduate text-gray-400"></i>
                                                      </div>
                                                      <p class="p_formateur_`+ val.idProjet + ` text-gray-600 flex flex-row items-center gap-2">
                                                      </p>
                                                    </div>
                                                  </div>

                                                  <div class="inline-flex items-center">
                                                    <div class="w-[24px] flex justify-center items-center">
                                                      <i class="fa-solid fa-sack-dollar text-gray-400"></i>
                                                    </div>
                                                    <p class="text-gray-500 font-medium">`+ val.paiement + `</p>
                                                  </div>

                                                  <div class="inline-flex items-center">
                                                    <div class="w-[24px] flex justify-center items-center">
                                                      <i class="fa-solid fa-dollar text-gray-400"></i>
                                                    </div>
                                                      <p class="p_price_`+ val.idProjet + ` text-gray-500 font-medium"></p>
                                                  </div>

                                                  <div class="inline-flex items-center">
                                                    <div class="w-[24px] flex justify-center items-center">
                                                      <i class="fa-solid text-gray-400 fa-file-invoice"></i>
                                                    </div>
                                                    <p class="text-gray-500 font-medium">
                                                      20% - Facture n°: #347
                                                    </p>
                                                  </div>

                                                </div>
                                              </div>
                                              <div class="grid col-span-1 p-3">
                                                <div class="h-full w-full flex items-center gap-1 justify-end">
                                                  <span class="inline-flex items-center gap-2 px-2 py-1 text-blue-500 w-[90px] justify-center">
                                                    Présentielle
                                                  </span>
                                                  <div class="btn-group h-max">
                                                    <button type="button" title="Cliquer pour afficher le menu"
                                                      class="w-8 h-8 bg-[#A462A4] rounded-md hover:bg-[#A462A4]/90 duration-150 cursor-pointer shadow-sm shadow-purple-500"
                                                      data-bs-toggle="dropdown" aria-expanded="false">
                                                      <span class=""><i class="fa-solid fa-bars-staggered text-white"></i></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li class="duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                                          <a class="dropdown-item duration-150 cursor-pointer inline-flex w-full items-center gap-2 text-gray-500"
                                                            href="/etp/projets/`+ val.idProjet + `/detail">
                                                          <i class="fa-solid fa-eye text-base"></i>Aperçu</a>
                                                        </li>
                                                        <span class="p_btn_finish_`+ val.idProjet + `"></span>
                                                    </ul>
                                                  </div>
                                                </div>
                                                <div class="inline-flex items-center justify-end gap-1">
                                                  <i class="fa-solid fa-star text-gray-600 text-sm"></i>
                                                  <p class="text-gray-500 font-medium">4.5</p>
                                                </div>
                                              </div>
                                            </div>
                                            <div class="grid grid-cols-6 gap-4 py-2">
                                              <div class="grid col-span-1"></div>
                                              <div class="grid grid-cols-subgrid col-span-5">
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </li>
                                    </div>
                                    <div class="min-[350px]:block lg:hidden xl:hidden">
                                      <li id="" class="shadow-md border-[1px] min-w-[550px] overflow-x-scroll border-gray-100 rounded-md">
                                        <div class="grid grid-cols-1">
                                          <div class="grid col-span-1">
                                            <div class="grid grid-cols-6">
                                              <div
                                                class="p_statut_`+ val.idProjet + ` grid col-span-2 gap-2 bg-gradient-to-br relative rounded-md overflow-hidden text-white p-3 rounded-l-md">
                                                <div
                                                  class="p_type_`+ val.idProjet + ` px-2 py-1 text-white text-sm text-center w-36 absolute -left-10 top-3 -rotate-45 shadow-sm">
                                                  <p class="text-white text-sm">`+ val.project_type + `</p>
                                                </div>
                                                <div class="flex flex-col justify-center ml-8 mt-2">
                                                  <h5 class="p_date_year_`+ val.idProjet + ` text-white text-xl font-medium"></h5>
                                                  <div class="inline-flex items-end gap-2">
                                                    <h5 class="p_date_jour_debut_`+ val.idProjet + ` text-white text-4xl font-semibold"></h5>
                                                    <h5 class="p_date_jour_fin_`+ val.idProjet + ` text-white text-xl"></h5>
                                                  </div>
                                                  <div class="inline-flex items-end gap-4">
                                                    <h5 class="p_date_mois_debut_`+ val.idProjet + ` text-white text-xl font-semibold"></h5>
                                                    <h5 class="p_date_mois_fin_`+ val.idProjet + ` text-white text-lg"></h5>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid grid-cols-subgrid col-span-3 gap-4 p-3">
                                                <div class="grid grid-cols-1 gap-2">
                                                  <div class="w-full">
                                                  <h1 class="p_module_`+ val.idProjet + ` text-gray-600 text-xl font-medium"></h1>
                                                  </div>
                                                  <div class="flex flex-col gap-2">
                                                    <div class="grid grid-cols-6 items-center gap-x-6">
                                                      <span class="p_etp_initial_`+ val.idProjet + ` grid grid-cols-subgrid col-span-2">
                                                      </span>
                                                      <span class="grid grid-cols-subgrid col-span-4">
                                                        <p class="p_nameCfp_`+ val.idProjet + ` text-gray-600"></p>
                                                      </span>
                                                    </div>
                                                  </div>
                                                  <div class="inline-flex items-center gap-4">
                                                    <div class="">
                                                    <p class="text-gray-600">`+ val.seanceCount + ` <span  class="text-gray-400">Sessions</span></p>
                                                    </div>
                                                    <div class="">
                                                    </div>
                                                    <div class="">
                                                    <p class="text-gray-600">`+ val.apprCount + `  <span class="text-gray-400">Apprenants</span></p>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                              <div class="grid col-span-1 p-3">
                                                <div class="grid col-span-1 items-start h-full justify-end">
                                                  <div class="btn-group h-max">
                                                    <button type="button" title="Cliquer pour afficher le menu"
                                                      class="w-8 h-8 bg-[#A462A4] rounded-md hover:bg-[#A462A4]/90 duration-150 cursor-pointer shadow-sm shadow-purple-500"
                                                      data-bs-toggle="dropdown" aria-expanded="false">
                                                      <span class=""><i class="fa-solid fa-bars-staggered text-white"></i></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li class="duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                                          <a class="dropdown-item duration-150 cursor-pointer inline-flex w-full items-center gap-2 text-gray-500"
                                                            href="/etp/projets/`+ val.idProjet + `/detail">
                                                          <i class="fa-solid fa-eye text-base"></i>Aperçu</a>
                                                        </li>
                                                        <span class="p_btn_finish_`+ val.idProjet + `"></span>
                                                    </ul>
                                                  </div>
                                                </div>
                                                <div class="grid col-span-1 items-end justify-end">
                                                  <div class="inline-flex items-center justify-end gap-1">
                                                    <i class="fa-solid fa-star text-gray-600 text-sm"></i>
                                                    <p class="text-gray-500 font-medium">4.5</p>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="grid col-span-1 p-3">
                                            <hr class="border-[1px] border-gray-200 my-2">
                                            <div class="flex flex-col gap-1">
                                              <div class="w-full">
                                                <div class="grid grid-cols-3">
                                                  <div class="grid col-span-1">
                                                    <div class="inline-flex items-center">
                                                      <div class="w-[24px] flex justify-center items-center">
                                                      </div>
                                                      <span
                                                        class="p_statut_string_`+ val.idProjet + ` inline-flex items-center gap-2 px-2 py-1 text-sm text-white w-[90px] justify-center">` + val.project_status + `
                                                      </span>
                                                    </div>
                                                  </div>

                                                  <div class="inline-flex items-center">
                                                    <div class="w-[24px] flex justify-center items-center">
                                                    </div>
                                                    <span class="inline-flex items-center text-blue-500 justify-center">
                                                      Présentielle
                                                    </span>
                                                  </div>

                                                  <div class="grid col-span-1">
                                                    <div class="inline-flex items-center">
                                                      <div class="w-[24px] flex justify-center items-center">
                                                        <i class="fa-solid fa-user-graduate text-gray-400"></i>
                                                      </div>
                                                      <p class="p_formateur_`+ val.idProjet + ` text-gray-600 flex flex-row items-center gap-2"></p>
                                                    </div>
                                                  </div>

                                                </div>
                                              </div>
                                              <hr class="border-[1px] border-gray-200 my-2">

                                              <div class="w-full">
                                                <div class="inline-flex items-center">
                                                  <div class="w-[24px] flex justify-center items-center">
                                                    <i class="fa-solid fa-location-dot text-gray-400"></i>
                                                  </div>
                                                  <p class="p_lieu_`+ val.idProjet + ` text-gray-600">
                                                    <span class="p_salle_name_`+ val.idProjet + `"></span>
                                                    <span class="p_salle_quartier_`+ val.idProjet + `"></span> -
                                                    <span class="p_salle_ville_`+ val.idProjet + `"></span> -
                                                    <span class="p_salle_code_postal_`+ val.idProjet + `"></span>
                                                  </p>
                                                </div>
                                              </div>
                                              <hr class="border-[1px] border-gray-200 my-2">

                                              <div class="grid grid-cols-3 gap-2">
                                                <div class="inline-flex items-center">
                                                  <div class="w-[24px] flex justify-center items-center">
                                                    <i class="fa-solid fa-sack-dollar text-gray-400"></i>
                                                  </div>
                                                  <p class="text-gray-500 font-medium">`+ val.paiement + `</p>
                                                </div>

                                                <div class="inline-flex items-center">
                                                  <div class="w-[24px] flex justify-center items-center">
                                                    <i class="fa-solid fa-dollar text-gray-400"></i>
                                                  </div>
                                                  <p class="p_price_`+ val.idProjet + ` text-gray-500 font-medium"></p>
                                                </div>

                                                <div class="inline-flex items-center">
                                                  <div class="w-[24px] flex justify-center items-center">
                                                    <i class="fa-solid text-gray-400 fa-file-invoice"></i>
                                                  </div>
                                                  <p class="text-gray-500 font-medium">
                                                    20% - Facture n°: #347
                                                  </p>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </li>
                                    </div>


                                    <div class="modal fade" id="supprimerProjet`+ val.idProjet + `" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                                      aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                      <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content bg-white border-none justify-center gap-2 rounded-md" id="lottieAnimation">
                                          <div class="flex flex-col rounded items-center gap-1">
                                            <lottie-player src="/Animations/Delete.json" background="transparent" speed="1"
                                              style="width: 60px; height: 50px;" loop autoplay></lottie-player>
                                            <h1 class="text-gray-600 text-2xl font-semibold flex flex-1" id="staticBackdropLabel">
                                              Suppression ?
                                            </h1>
                                          </div>
                                          <p class="text-base text-gray-500 text-center px-4">Voulez-vous vraiment surpprimer ce projet ?</p>
                                          <div class="p-3 inline-flex gap-3 justify-center">
                                            <button type="button"
                                              class="border-[1px] border-gray-600 text-gray-600 text-base hover:text-gray-700 scale-95 hover:scale-100 rounded-md px-4 py-2 transition duration-200"
                                              data-bs-dismiss="modal" data-bs-dismiss="tooltip">Non,
                                              annuler</button>
                                            <button type="button" onclick="manageProject('delete', '/etp/projets/`+ val.idProjet + `/destroy')"
                                              class="bg-gray-700 text-white text-base rounded-md px-4 py-2 scale-95 hover:scale-100 hover:bg-gray-800 transition duration-200"
                                              data-bs-dismiss="modal">Oui, je confirme</button>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="modal fade" id="annulerProjet`+ val.idProjet + `" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                                      aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                      <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content bg-white border-none justify-center gap-2 rounded-md" id="lottieAnimation">
                                          <div class="flex flex-col rounded items-center gap-1">
                                            <lottie-player src="/Animations/Delete.json" background="transparent" speed="1"
                                              style="width: 60px; height: 50px;" loop autoplay></lottie-player>
                                            <h1 class="text-gray-600 text-2xl font-semibold flex flex-1" id="staticBackdropLabel">
                                              Annuler
                                            </h1>
                                          </div>
                                          <p class="text-base text-gray-500 text-center px-4">Voulez-vous vraiment annuler cette session ?</p>
                                          <div class="p-3 inline-flex gap-3 justify-center">
                                            <button type="button"
                                              class="border-[1px] border-gray-600 text-gray-600 text-base hover:text-gray-700 scale-95 hover:scale-100 rounded-md px-4 py-2 transition duration-200"
                                              data-bs-dismiss="modal" data-bs-dismiss="tooltip">Non,
                                              annuler</button>
                                            <button type="button" onclick="manageProject('patch', '/etp/projets/`+ val.idProjet + `/cancel')"
                                              class="bg-gray-700 text-white text-base rounded-md px-4 py-2 scale-95 hover:scale-100 hover:bg-gray-800 transition duration-200"
                                              data-bs-dismiss="modal">Oui, je confirme</button>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="modal fade" id="reporterProjet`+ val.idProjet + `" data-bs-backdrop="static" data-bs-keyboard="false"
                                      tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                      <div class="modal-dialog flex items-center justify-center">
                                        <div class="modal-content bg-white border-none w-[320px] justify-center gap-2 rounded-xl" id="lottieAnimation">
                                          <div class="h-full w-full flex flex-col gap-2 p-4">
                                            <label class="text-xl font-medium text-gray-500 text-center mb-4">A Reporter le</label>
                                            <div class="w-full inline-flex justify-center">
                                              <div id="nav"></div>
                                            </div>
                                            <div class="inline-flex items-center gap-2 mb-4">
                                              <div class="w-full inline-flex relative">
                                                <input type="date" id="dateDebutProjetDetail" name="dateDebutProjetDetail" class="outline-none bg-white w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400">
                                              </div>
                                              <div class="w-full inline-flex relative">
                                                <input type="date" id="dateFinProjetDetail" name="dateFinProjetDetail" class="outline-none bg-white w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400">
                                              </div>
                                            </div>
                                            <div class="w-full inline-flex items-center gap-2 justify-between">
                                              <button 
                                                type="button" 
                                                data-bs-dismiss="modal" 
                                                data-dismiss="modal"
                                                class="focus:outline-none px-3 bg-gray-200 py-2 ml-3 rounded-md text-gray-600 hover:bg-gray-300/80 transition duration-200 text-base">Annuler</button>
                                              <button 
                                                type="button" 
                                                onclick="repportProject(`+ val.idProjet + `)"
                                                class="focus:outline-none px-3 bg-[#A462A4] py-2 ml-3 rounded-md text-white hover:bg-[#A462A4]/90 transition duration-200 text-base">Oui, je confirme</button>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>`);

              var p_statut = $('.p_statut_' + val.idProjet);
              var p_type = $('.p_type_' + val.idProjet);
              var p_statut_string = $('.p_statut_string_' + val.idProjet);
              var p_date_year = $('.p_date_year_' + val.idProjet);
              var p_date_jour_debut = $('.p_date_jour_debut_' + val.idProjet);
              var p_date_jour_fin = $('.p_date_jour_fin_' + val.idProjet);
              var p_date_mois_debut = $('.p_date_mois_debut_' + val.idProjet);
              var p_date_mois_fin = $('.p_date_mois_fin_' + val.idProjet);
              var p_module = $('.p_module_' + val.idProjet);
              var p_etp_initial = $('.p_etp_initial_' + val.idProjet);

              var p_nameCfp = $('.p_nameCfp_' + val.idProjet);
              var p_etp_name = $('.p_etp_name_' + val.idProjet);


              var p_lieu = $('.p_lieu_' + val.idProjet);
              var p_salle_name = $('.p_salle_name_' + val.idProjet);
              var p_salle_quartier = $('.p_salle_quartier_' + val.idProjet);
              var p_salle_ville = $('.p_salle_ville_' + val.idProjet);
              var p_salle_code_postal = $('.p_salle_code_postal_' + val.idProjet);
              var p_price = $('.p_price_' + val.idProjet);
              var p_formateur = $('.p_formateur_' + val.idProjet);
              var p_btn_finish = $('.p_btn_finish_' + val.idProjet);

              switch (val.project_status) {
                case "En préparation":
                  p_statut.addClass('from-[#66CDAA] to-[#66CDAA]/50');
                  p_statut_string.addClass('bg-[#66CDAA]');
                  break;
                case "Réservé":
                  p_statut.addClass('from-[#33303D] to-[#33303D]/50');
                  p_statut_string.addClass('bg-[#33303D]');
                  break;
                case "En cours":
                  p_statut.addClass('from-[#1E90FF] to-[#1E90FF]/50');
                  p_statut_string.addClass('bg-[#1E90FF]');
                  break;
                case "Terminé":
                  p_statut.addClass('from-[#32CD32] to-[#32CD32]/50');
                  p_statut_string.addClass('bg-[#32CD32]');
                  break;
                case "Annulé":
                  p_statut.addClass('from-[#FF6347] to-[#FF6347]/50');
                  p_statut_string.addClass('bg-[#FF6347]');
                  break;
                case "Reporté":
                  p_statut.addClass('from-[#2E705A] to-[#2E705A]/50');
                  p_statut_string.addClass('bg-[#2E705A]');
                  break;
                case "Planifié":
                  p_statut.addClass('from-[#2552BA] to-[#2552BA]/50');
                  p_statut_string.addClass('bg-[#2552BA]');
                  break;
                default:
                  p_statut.addClass('from-[#66FDAA] to-[#66FDAA]/50')
                  break;
              }

              switch (val.project_type) {
                case "Intra":
                  p_type.addClass('bg-[#1565c0]');
                  break;
                case "Inter":
                  p_type.addClass('bg-[#7209b7]');
                case "Interne":
                  p_type.addClass('bg-[#7F055F]');
                  break;

                default:
                  break;
              }

              if (val.dateDebut != null) {
                p_date_year.text(val.headYear);
              } else {
                p_date_year.text('--');
              }

              if (val.dateDebut != null) {
                p_date_mois_debut.text(val.headMonthDebut);
              } else {
                p_date_mois_debut.text('--');
              }

              if (val.dateDebut != null) {
                p_date_mois_fin.text(val.headMonthFin);
              } else {
                p_date_mois_fin.text('--');
              }

              if (val.dateDebut != null) {
                p_date_jour_debut.text(val.headDayDebut);
              } else {
                p_date_jour_debut.text('--');
              }

              if (val.dateDebut != null) {
                p_date_jour_fin.text(val.headDayFin);
              } else {
                p_date_jour_fin.text('--');
              }

              p_module.html('');
              if (val.module_name != null && val.module_name != "Default module") {
                p_module.append(`<h1 class="text-gray-600 text-xl font-medium" title="` + val.module_name + `">` + val.module_name + `</h1>`);
              } else {
                p_module.append(`<span class="text-gray-600">--</span>`);
              }

              p_etp_initial.html('');


              if (val.logoCfp != null && val.project_type == 'Intra') {
                p_etp_initial.append(`<div class="w-28 h-16 bg-gray-200 rounded-xl relative">
                                          <x-icon-badge />
                                          <img src="/img/entreprises/`+ val.logoCfp + `" alt="logo"
                                            class="w-full h-full rounded-xl object-cover">
                                        </div>`);
              }

              else if (val.etp_logo != null && val.project_type == 'Interne') {
                p_etp_initial.append(`<div class="w-28 h-16 bg-gray-200 rounded-xl relative">
                                               <x-icon-badge />
                                               <img src="/img/entreprises/`+ val.etp_logo + `" alt="logo"
                                                 class="w-full h-full rounded-xl object-cover">
                                            </div>`);
              }

              else if (val.logoCfp == null && val.initialnameCfp != null) {
                p_etp_initial.append(`<span style="background: #e5e7eb; padding: 2px 8px; border-radius: 50%; cursor: pointer;">` + val.initialnameCfp + `</span>`);
              }



              else {
                p_etp_initial.append(`<span class="text-gray-400">---</span>`);
              }


              // if (val.etp_logo != null) {
              //   p_etp_initial.append(`<div class="w-28 h-16 bg-gray-200 rounded-xl relative">
              //                             <x-icon-badge />
              //                             <img src="/img/entreprises/`+ val.etp_logo + `" alt="logo"
              //                               class="w-full h-full rounded-xl object-cover">
              //                           </div>`);
              // } else if (val.etp_logo == null && val.etp_initial_name != null) {
              //   p_etp_initial.append(`<span style="background: #e5e7eb; padding: 2px 8px; border-radius: 50%; cursor: pointer;">` + val.etp_initial_name + `</span>`);
              // } else {
              //   p_etp_initial.append(`<span class="text-gray-400">---</span>`);
              // }



              if (val.nameCfp != null) {
                p_nameCfp.text(val.nameCfp);
              } else {
                p_nameCfp.text('--');
              }

              if (val.salle_name != null) {
                p_lieu.attr('title', val.salle_name);
              }

              if (val.salle_name != null) {
                p_salle_name.text(val.salle_name);
              } else {
                p_salle_name.text('--');
              }

              if (val.salle_quartier != null) {
                p_salle_quartier.text(val.salle_quartier);
              } else {
                p_salle_quartier.text('--');
              }

              if (val.ville != null) {
                p_salle_ville.text(val.ville);
              } else {
                p_salle_ville.text('--');
              }

              if (val.salle_code_postal != null) {
                p_salle_code_postal.text(val.salle_code_postal);
              } else {
                p_salle_code_postal.text('--');
              }

              if (val.projectTotalPrice != null) {
                p_price.text('Ar ' + val.projectTotalPrice + ' HT');
              } else {
                p_price.text('Ar -- HT');
              }

              p_formateur.html('');
              if (val.formateurs.length <= 0) {
                p_formateur.append(`<span class="text-gray-400">--</span>`);
              } else {
                $.each(val.formateurs, function (vForm, valForm) {
                  if (valForm.form_photo != null) {
                    p_formateur.append(`<img class="w-8 h-8 object-cover rounded-full cursor-pointer"
                                            src="/img/formateurs/`+ valForm.form_photo + `" alt="photo"
                                            title="`+ valForm.form_name + ` ` + valForm.form_firstname + `">`);
                  } else {
                    p_formateur.append(`<span title="` + valForm.form_name + ` ` + valForm.form_firstname + `"
                        style="background: #e5e7eb; padding: 2px 8px; border-radius: 50%; cursor: pointer;">`+ valForm.form_initial_name + `</span>`);
                  }
                });
              }

              p_btn_finish.html('');
              if (val.project_status != "Terminé") {
                p_btn_finish.append(`<hr class="border-[1px] border-gray-400 my-2">
                                        <li class="dropdown-item duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                          <span>
                                            <i class="fa-solid fa-check text-sm"></i>
                                          </span>
                                          <button type="button" data-bs-toggle="modal" data-bs-target="#confirmerProjet" class="">Valider le projet</button>
                                        </li>
                                        <li class="dropdown-item duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                          <span>
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                          </span>
                                          <button type="button" data-bs-toggle="modal" data-bs-target="#supprimerProjet`+ val.idProjet + `" class="">Supprimer</button>
                                        </li>
                                        <hr class="border-[1px] border-gray-400 my-2">
                                        <span class="p_btn_cancel_`+ val.idProjet + `"></span>
                                        <span class="p_btn_repport_`+ val.idProjet + `"></span>`);
              }

              $('.p_btn_cancel_' + val.idProjet).html('');
              if (val.project_status != "Annulé") {
                $('.p_btn_cancel_' + val.idProjet).append(`<li class="dropdown-item duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                                              <span>
                                                                <i class="fa-solid fa-xmark text-sm"></i>
                                                              </span>
                                                              <button type="button" data-bs-toggle="modal" data-bs-target="#annulerProjet`+ val.idProjet + `" class="">Annuler</button>
                                                            </li>`);
              }

              $('.p_btn_repport_' + val.idProjet).html('');
              if (val.project_status != "Reporté") {
                $('.p_btn_repport_' + val.idProjet).append(`<li class="dropdown-item duration-150 cursor-pointer inline-flex items-center w-full gap-2 text-gray-500">
                                                              <span>
                                                                <i class="fa-solid fa-right-left text-sm"></i>
                                                              </span>
                                                              <button type="button" data-bs-toggle="modal" data-bs-target="#reporterProjet`+ val.idProjet + `" class="">Reporter</button>
                                                            </li>`);
              }
            }
          });
        });


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

function refresh(itemClicked, idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement) {
  switch (itemClicked) {
    case "statut":
      $('.statut_item_checkbox').prop('checked', false);
      $('.countSelected_statut').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement)
      break;
    case "entreprise":
      $('.etp_item_checkbox').prop('checked', false);
      $('.countSelected_entreprise').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement)
      break;
    case "type":
      $('.type_item_checkbox').prop('checked', false);
      $('.countSelected_type').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement)
      break;
    case "periode":
      $('.periode_item_checkbox').prop('checked', false);
      $('.countSelected_periode').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement)
      break;
    case "cours":
      $('.module_item_checkbox').prop('checked', false);
      $('.countSelected_cours').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement)
      break;
    case "ville":
      $('.ville_item_checkbox').prop('checked', false);
      $('.countSelected_ville').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement)
      break;
    case "financement":
      $('.financement_item_checkbox').prop('checked', false);
      $('.countSelected_financement').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idFinancement)
      break;

    default:
      break;
  }

  // location.reload();
}