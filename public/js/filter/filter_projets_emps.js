
function getDropdownItem() {
  var status = $('#filterStatut');
  var etps = $('#filterEntreprise');
  var types = $('#filterType');
  var periodes = $('#filterPeriode');
  var modules = $('#filterModule');
  var villes = $('#filterVille');
  var mois = $('#filterMois');


  $.ajax({
    type: "get",
    url: "/employe/filter/getDropdownItem",
    dataType: "json",
    success: function (res) {
      status.html('');
      etps.html('');
      types.html('');
      periodes.html('');
      modules.html('');
      villes.html('');
      mois.html('');

      if (res.status.length <= 0) {
        status.append(`<h3>Aucun résultat</h3>`);
      } else {

        $.each(res.status, function (i, v) {
          status.append(`<li class="statut_item_` + v.project_status + `">
                          <label class="p-1 label cursor-pointer grid grid-cols-6">
                            <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_status + `">
                              <span class="inline-flex items-center gap-2 px-2">
                                <input 
                                  type="checkbox"
                                  value="`+ v.project_status + `"
                                  class="checkbox statut_item_checkbox">
                                <p class="text-gray-500 label-text">`+ v.project_status + `</p>
                              </span>
                            </div>
                            <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                          </label>
                        </li>`);
        });
      }

      if (res.etps.length <= 0) {
        etps.append(`<h3>Aucun résultat</h3>`);
      } else {
        $.each(res.etps, function (i, v) {
          etps.append(`<li class="etp_item_` + v.idEtp + `">
                          <label class="p-1 label cursor-pointer grid grid-cols-6">
                            <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.etp_name + `">
                              <span class="inline-flex items-center gap-2 px-2">
                                <input 
                                  type="checkbox"
                                  value="`+ v.idEtp + `"
                                  class="checkbox etp_item_checkbox">
                                <p class="text-gray-500 label-text">`+ v.etp_name + ` </p>
                              </span>
                            </div>
                            <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                          </label>
                        </li>`);
        });
      }

      if (res.types.length <= 0) {
        types.append(`<h3>Aucun résultat</h3>`);
      } else {
        $.each(res.types, function (i, v) {
          types.append(`<li class="type_item_` + v.project_type + `">
                          <label class="p-1 label cursor-pointer grid grid-cols-6">
                            <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                              <span class="inline-flex items-center gap-2 px-2">
                                <input 
                                  type="checkbox"
                                  value="`+ v.project_type + `"
                                  class="checkbox type_item_checkbox">
                                <p class="text-gray-500 label-text">`+ v.project_type + `</p>
                              </span>
                            </div>
                            <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                          </label>
                        </li>`);
        });
      }

      periodes.append(`<li>
        <label class="p-1 label cursor-pointer grid grid-cols-6">
          <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
            <span class="inline-flex items-center gap-2 px-2">
              <input 
                id="p_vp_3"
                value=""
                type="radio" 
                name="period_radio"
                autocomplete="off"
                class="periode_item_checkbox radio">
              <p class="text-gray-500 label-text">3 derniers mois</p>
            </span>
          </div>
          <div class="p_np_3 periode_p_3 grid cols-span-1 items-center justify-end"></div>
        </label>
      </li>
      <li>
        <label class="p-1 label cursor-pointer grid grid-cols-6">
          <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
            <span class="inline-flex items-center gap-2 px-2">
              <input 
                id="p_vp_6"
                value=""
                type="radio" 
                name="period_radio"
                autocomplete="off"
                class="periode_item_checkbox radio">
              <p class="text-gray-500 label-text">6 derniers mois</p>
            </span>
          </div>
          <div class="p_np_6 grid cols-span-1 items-center justify-end"></div>
        </label>
      </li>
      <li>
        <label class="p-1 label cursor-pointer grid grid-cols-6">
          <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
            <span class="inline-flex items-center gap-2 px-2">
              <input 
                id="p_vp_12"
                value=""
                type="radio" 
                name="period_radio"
                autocomplete="off"
                class="periode_item_checkbox radio">
              <p class="text-gray-500 label-text">12 derniers mois</p>
            </span>
          </div>
          <div class="p_np_12 grid cols-span-1 items-center justify-end"></div>
        </label>
      </li>
      <hr class="border-[1px] border-gray-200 my-2">
      <li>
        <label class="p-1 label cursor-pointer grid grid-cols-6">
          <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
            <span class="inline-flex items-center gap-2 px-2">
              <input 
                id="p_vn_3"
                value=""
                type="radio" 
                name="period_radio"
                autocomplete="off"
                class="periode_item_checkbox radio">
              <p class="text-gray-500 label-text">3 prochains mois</p>
            </span>
          </div>
          <div class="p_nn_3 grid cols-span-1 items-center justify-end"></div>
        </label>
      </li>
      <li>
        <label class="p-1 label cursor-pointer grid grid-cols-6">
          <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
            <span class="inline-flex items-center gap-2 px-2">
              <input 
                id="p_vn_6"
                value=""
                type="radio" 
                name="period_radio"
                autocomplete="off"
                class="periode_item_checkbox radio">
              <p class="text-gray-500 label-text">6 prochains mois</p>
            </span>
          </div>
          <div class="p_nn_6 grid cols-span-1 items-center justify-end"></div>
        </label>
      </li>
      <li>
        <label class="p-1 label cursor-pointer grid grid-cols-6">
          <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
            <span class="inline-flex items-center gap-2 px-2">
              <input 
                id="p_vn_12"
                value=""
                type="radio" 
                name="period_radio"
                autocomplete="off"
                class="periode_item_checkbox radio">
              <p class="text-gray-500 label-text">12 prochains mois</p>
            </span>
          </div>
          <div class="p_nn_12 grid cols-span-1 items-center justify-end"></div>
        </label>
      </li>`);

      if (res.modules.length <= 0) {
        modules.append(`<h3>Aucun résultat</h3>`);
      } else {
        $.each(res.modules, function (i, v) {
          modules.append(`<li class="module_item_` + v.idModule + `">
          <label class="p-1 label cursor-pointer grid grid-cols-6">
            <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.module_name + `">
              <span class="inline-flex items-center gap-2 px-2">
                <input 
                  type="checkbox"
                  value="`+ v.idModule + `"
                  class="checkbox module_item_checkbox">
                <p class="text-gray-500 label-text">`+ v.module_name + `</p>
              </span>
            </div>
            <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
          </label>
        </li>`);
        });
      }

      if (res.villes.length <= 0) {
        villes.append(`<h3>Aucun résultat</h3>`);
      } else {
        $.each(res.villes, function (i, v) {
          villes.append(`<li class="ville_item_` + v.idVille + `">
          <label class="p-1 label cursor-pointer grid grid-cols-6">
            <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
              <span class="inline-flex items-center gap-2 px-2">
                <input 
                  type="checkbox"
                  value="`+ v.idVille + `"
                  class="checkbox ville_item_checkbox">
                <p class="text-gray-500 label-text">`+ v.ville + `</p>
              </span>
            </div>
            <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
          </label>
        </li>`);
        });
      }

      if (res.months.length <= 0) {
        mois.append(`<h3>Aucun résultat</h3>`);
      } else {
        $.each(res.months, function (i, v) {
          mois.append(`<li class="mois_item_` + v.idMois + `">
                          <label class="p-1 label cursor-pointer grid grid-cols-6">
                            <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.headDate + `">
                              <span class="inline-flex items-center gap-2 px-2">
                                <input 
                                  type="checkbox"
                                  value="`+ v.idMois + `"
                                  class="checkbox mois_item_checkbox">
                                <p class="text-gray-500 label-text">`+ v.headDate + `</p>
                              </span>
                            </div>
                            <div class="grid cols-span-1 items-center justify-end nb_proj_mois_`+ v.idMois + `">` + v.projet_nb + `</div>
                          </label>
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


      $('.closeDropdown').click(function (e) {
        e.preventDefault();
        closDrop();
      });

      $($('.statut_item_checkbox, .etp_item_checkbox, .type_item_checkbox, .periode_item_checkbox, .module_item_checkbox, .ville_item_checkbox, .mois_item_checkbox')).change(function () {
        var idStatus = [];
        var idEtps = [];
        var idTypes = [];
        var idPeriodes;
        var idModules = [];
        var idVilles = [];
        var idMois = [];

        var sumStatus = 0;
        var sumEtps = 0;
        var sumTypes = 0;
        var sumModules = 0;
        var sumVilles = 0;
        var sumMois = 0;

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
          refresh('statut', null, idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idMois.toString());
        });

        $('.iconClose-statut').click(function () {
          getDropdownItem();
          refresh('statut', null, idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idMois.toString());
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
          refresh('entreprise', idStatus.toString(), null, idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), idFormateurs.toString(), idMois.toString());
        });

        $('.iconClose-entreprise').click(function () {
          getDropdownItem();
          refresh('entreprise', idStatus.toString(), null, idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idFinancements.toString(), idFormateurs.toString(), idMois.toString());
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
          refresh('type', idStatus.toString(), null, idPeriodes, idModules.toString(), idVilles.toString(), idMois.toString());
        });

        $('.iconClose-type').click(function () {
          getDropdownItem();
          refresh('type', idStatus.toString(), null, idPeriodes, idModules.toString(), idVilles.toString(), idMois.toString());
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
          refresh('periode', idStatus.toString(), idTypes.toString(), null, idModules.toString(), idVilles.toString(), idMois.toString());
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
          refresh('cours', idStatus.toString(), idTypes.toString(), idPeriodes, null, idVilles.toString(), idMois.toString());
        });

        $('.iconClose-cours').click(function () {
          getDropdownItem();
          refresh('cours', idStatus.toString(), idTypes.toString(), idPeriodes, null, idVilles.toString(), idMois.toString());
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
          refresh('ville', idStatus.toString(), idTypes.toString(), idPeriodes, idModules.toString(), null, idMois.toString());
        });

        $('.iconClose-ville').click(function () {
          getDropdownItem();
          refresh('ville', idStatus.toString(), idTypes.toString(), idPeriodes, idModules.toString(), null, idMois.toString());
          $('.selectedFilter_ville').addClass('hidden');
          $('.unselectedFilter_ville').removeClass('hidden');
        });

        // ================ MOIS =====================
        $($('.mois_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idMois.push($(this).val());
            for (let i = 0; i < res.months.length; i++) {
              if ($(this).val() == res.months[i].idMois) {

                sumMois += res.months[i].projet_nb;
              }
            }

          }
        });

        $('.countSelected_mois').text(sumMois);
        $('.countedButton_mois').click(function (e) {
          e.preventDefault();
          if (sumMois > 0) {
            $('.selectedFilter_mois').removeClass('hidden');
            $('.unselectedFilter_mois').addClass('hidden');
          } else {
            $('.selectedFilter_mois').addClass('hidden');
            $('.unselectedFilter_mois').removeClass('hidden');
          }
          closDrop();
        });

        $('.resetClick_mois').click(function () {
          refresh('mois', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), null);
        });

        $('.iconClose-mois').click(function () {
          getDropdownItem();
          refresh('mois', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), null);
          $('.selectedFilter_mois').addClass('hidden');
          $('.unselectedFilter_mois').removeClass('hidden');
        });


        // ================ FIN =====================

        filterItems(idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idMois.toString());
      });
    }
  });
}

function filterItems(idStatut, idEtp, idType, idPeriode, idModule, idVille, idMois) {
  var showResult = $('.showResult');
  var status = $('#filterStatut');
  var etps = $('#filterEntreprise');
  var types = $('#filterType');
  var periodes = $('#filterPeriode');
  var modules = $('#filterModule');
  var villes = $('#filterVille');
  var mois = $('#filterMois');

  $.ajax({
    type: "get",
    url: "/employe/filter/items",
    data: {
      idStatut: idStatut,
      idEtp: idEtp,
      idType: idType,
      idPeriode: idPeriode,
      idModule: idModule,
      idVille: idVille,
      idMois: idMois,
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
      showResult.html('');
      if ($('.statut_item_checkbox').is(':checked')) {
        etps.empty();
        types.empty();
        periodes.empty();
        modules.empty();
        villes.empty();
        mois.empty();

        if (res.etps.length <= 0) {
          etps.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.etps, function (i, v) {
            etps.append(`<li class="etp_item_` + v.idEtp + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.etp_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idEtp + `"
                                      class="checkbox etp_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.etp_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="checkbox type_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        periodes.append(`<li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_3 periode_p_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <hr class="border-[1px] border-gray-200 my-2">
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>`);

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.modules, function (i, v) {
            modules.append(`<li class="module_item_` + v.idModule + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.module_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idModule + `"
                                      class="checkbox module_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.module_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="checkbox ville_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }
        if (res.months.length <= 0) {
          months.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.months, function (i, v) {
            mois.append(`<li class="mois_item_` + v.idMois + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.headDate + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idMois + `"
                                      class="checkbox mois_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.headDate + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_formateur_`+ v.idMois + `">` + v.projet_nb + `</div>
                              </label>
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

      } else if ($('.etp_item_checkbox').is(':checked')) {
        status.empty();
        types.empty();
        periodes.empty();
        modules.empty();
        villes.empty();
        mois.empty();

        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.status, function (i, v) {
            status.append(`<li class="statut_item_` + v.project_status + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_status + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_status + `"
                                      class="checkbox statut_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_status + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="checkbox type_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        periodes.append(`<li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_3 periode_p_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <hr class="border-[1px] border-gray-200 my-2">
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>`);

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.modules, function (i, v) {
            modules.append(`<li class="module_item_` + v.idModule + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.module_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idModule + `"
                                      class="checkbox module_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.module_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="checkbox ville_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.months.length <= 0) {
          mois.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.months, function (i, v) {
            mois.append(`<li class="mois_item_` + v.idMois + `">
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.headDate + `">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.idMois + `"
                                    class="checkbox mois_item_checkbox">
                                  <p class="text-gray-500 label-text">`+ v.headDate + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_proj_mois_`+ v.idMois + `">` + v.projet_nb + `</div>
                            </label>
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
        mois.empty();

        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.status, function (i, v) {
            status.append(`<li class="statut_item_` + v.project_status + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_status + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_status + `"
                                      class="checkbox statut_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_status + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.etps.length <= 0) {
          etps.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.etps, function (i, v) {
            etps.append(`<li class="etp_item_` + v.idEtp + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.etp_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idEtp + `"
                                      class="checkbox etp_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.etp_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        periodes.append(`<li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_3 periode_p_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <hr class="border-[1px] border-gray-200 my-2">
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>`);

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.modules, function (i, v) {
            modules.append(`<li class="module_item_` + v.idModule + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.module_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idModule + `"
                                      class="checkbox module_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.module_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="checkbox ville_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }
        if (res.months.length <= 0) {
          mois.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.months, function (i, v) {
            mois.append(`<li class="mois_item_` + v.idMois + `">
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.headDate + `">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.idMois + `"
                                    class="checkbox mois_item_checkbox">
                                  <p class="text-gray-500 label-text">`+ v.headDate + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_proj_mois_`+ v.idMois + `">` + v.projet_nb + `</div>
                            </label>
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
      }
      else if ($('.periode_item_checkbox').is(':checked')) {
        status.empty();
        etps.empty();
        types.empty();
        modules.empty();
        villes.empty();
        mois.empty();

        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.status, function (i, v) {
            status.append(`<li class="statut_item_` + v.project_status + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_status + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_status + `"
                                      class="checkbox statut_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_status + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.etps.length <= 0) {
          etps.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.etps, function (i, v) {
            etps.append(`<li class="etp_item_` + v.idEtp + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.etp_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idEtp + `"
                                      class="checkbox etp_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.etp_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="checkbox type_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.modules, function (i, v) {
            modules.append(`<li class="module_item_` + v.idModule + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.module_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idModule + `"
                                      class="checkbox module_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.module_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="checkbox ville_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.months.length <= 0) {
          mois.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.months, function (i, v) {
            mois.append(`<li class="mois_item_` + v.idMois + `">
                          <label class="p-1 label cursor-pointer grid grid-cols-6">
                            <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.headDate + `">
                              <span class="inline-flex items-center gap-2 px-2">
                                <input 
                                  type="checkbox"
                                  value="`+ v.idMois + `"
                                  class="checkbox mois_item_checkbox">
                                <p class="text-gray-500 label-text">`+ v.headDate + `</p>
                              </span>
                            </div>
                            <div class="grid cols-span-1 items-center justify-end nb_proj_mois_`+ v.idMois + `">` + v.projet_nb + `</div>
                          </label>
                        </li>`);
          });
        }

      } else if ($('.module_item_checkbox').is(':checked')) {
        status.empty();
        etps.empty();
        types.empty();
        periodes.empty();
        villes.empty();
        mois.empty();


        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.status, function (i, v) {
            status.append(`<li class="statut_item_` + v.project_status + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_status + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_status + `"
                                      class="checkbox statut_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_status + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.etps.length <= 0) {
          etps.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.etps, function (i, v) {
            etps.append(`<li class="etp_item_` + v.idEtp + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.etp_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idEtp + `"
                                      class="checkbox etp_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.etp_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="checkbox type_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        periodes.append(`<li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_3 periode_p_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <hr class="border-[1px] border-gray-200 my-2">
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>`);

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="checkbox ville_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.months.length <= 0) {
          mois.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.months, function (i, v) {
            mois.append(`<li class="mois_item_` + v.idMois + `">
                          <label class="p-1 label cursor-pointer grid grid-cols-6">
                            <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.headDate + `">
                              <span class="inline-flex items-center gap-2 px-2">
                                <input 
                                  type="checkbox"
                                  value="`+ v.idMois + `"
                                  class="checkbox mois_item_checkbox">
                                <p class="text-gray-500 label-text">`+ v.headDate + `</p>
                              </span>
                            </div>
                            <div class="grid cols-span-1 items-center justify-end nb_proj_mois_`+ v.idMois + `">` + v.projet_nb + `</div>
                          </label>
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
        mois.empty();


        if (res.status.length <= 0) {
          status.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.status, function (i, v) {
            status.append(`<li class="statut_item_` + v.project_status + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_status + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_status + `"
                                      class="checkbox statut_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_status + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.etps.length <= 0) {
          etps.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.etps, function (i, v) {
            etps.append(`<li class="etp_item_` + v.idEtp + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.etp_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idEtp + `"
                                      class="checkbox etp_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.etp_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="checkbox type_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        periodes.append(`<li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_3 periode_p_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <hr class="border-[1px] border-gray-200 my-2">
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>`);

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.modules, function (i, v) {
            modules.append(`<li class="module_item_` + v.idModule + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.module_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idModule + `"
                                      class="checkbox module_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.module_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }
        if (res.months.length <= 0) {
          mois.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.months, function (i, v) {
            mois.append(`<li class="mois_item_` + v.idMois + `">
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.headDate + `">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    type="checkbox"
                                    value="`+ v.idMois + `"
                                    class="checkbox mois_item_checkbox">
                                  <p class="text-gray-500 label-text">`+ v.headDate + `</p>
                                </span>
                              </div>
                              <div class="grid cols-span-1 items-center justify-end nb_proj_mois_`+ v.idMois + `">` + v.projet_nb + `</div>
                            </label>
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

      } else if ($('.mois_item_checkbox').is(':checked')) {
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
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_status + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_status + `"
                                      class="checkbox statut_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_status + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_statut_`+ v.project_status + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.etps.length <= 0) {
          etps.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.etps, function (i, v) {
            etps.append(`<li class="etp_item_` + v.idEtp + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.etp_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idEtp + `"
                                      class="checkbox etp_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.etp_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_etp_`+ v.idEtp + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.types.length <= 0) {
          types.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.types, function (i, v) {
            types.append(`<li class="type_item_` + v.project_type + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.project_type + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.project_type + `"
                                      class="checkbox type_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.project_type + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_type_`+ v.project_type + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        periodes.append(`<li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_3 periode_p_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vp_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 derniers mois</p>
                                </span>
                              </div>
                              <div class="p_np_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <hr class="border-[1px] border-gray-200 my-2">
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_3"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">3 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_3 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_6"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">6 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_6 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>
                          <li>
                            <label class="p-1 label cursor-pointer grid grid-cols-6">
                              <div class="grid grid-cols-subgrid col-span-5 truncate" title="ACEP MADAGASCAR">
                                <span class="inline-flex items-center gap-2 px-2">
                                  <input 
                                    id="p_vn_12"
                                    value=""
                                    type="radio" 
                                    name="period_radio"
                                autocomplete="off"
                                class="periode_item_checkbox radio">
                                  <p class="text-gray-500 label-text">12 prochains mois</p>
                                </span>
                              </div>
                              <div class="p_nn_12 grid cols-span-1 items-center justify-end"></div>
                            </label>
                          </li>`);

        if (res.modules.length <= 0) {
          modules.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.modules, function (i, v) {
            modules.append(`<li class="module_item_` + v.idModule + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.module_name + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idModule + `"
                                      class="checkbox module_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.module_name + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_module_`+ v.idModule + `">` + v.projet_nb + `</div>
                              </label>
                            </li>`);
          });
        }

        if (res.villes.length <= 0) {
          villes.append(`<h3>Aucun résultat</h3>`);
        } else {
          $.each(res.villes, function (i, v) {
            villes.append(`<li class="ville_item_` + v.idVille + `">
                              <label class="p-1 label cursor-pointer grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-5 truncate" title="`+ v.ville + `">
                                  <span class="inline-flex items-center gap-2 px-2">
                                    <input 
                                      type="checkbox"
                                      value="`+ v.idVille + `"
                                      class="checkbox ville_item_checkbox">
                                    <p class="text-gray-500 label-text">`+ v.ville + `</p>
                                  </span>
                                </div>
                                <div class="grid cols-span-1 items-center justify-end nb_proj_ville_`+ v.idVille + `">` + v.projet_nb + `</div>
                              </label>
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
      }
      else {
        getDropdownItem();
      }

      $($('.statut_item_checkbox, .etp_item_checkbox, .type_item_checkbox, .periode_item_checkbox, .module_item_checkbox, .ville_item_checkbox, .mois_item_checkbox')).change(function () {
        var idStatus = [];
        var idEtps = [];
        var idTypes = [];
        var idPeriodes;
        var idModules = [];
        var idVilles = [];
        var idMois = [];


        var nb_proj_Status = [];
        var nb_proj_Etps = [];
        var nb_proj_Types = [];
        var nb_proj_Modules = [];
        var nb_proj_Villes = [];
        var nb_proj_Mois = [];


        var sumStatus = 0;
        var sumEtps = 0;
        var sumTypes = 0;
        var sumModules = 0;
        var sumVilles = 0;
        var sumMois = 0;


        // ================== STATUT =================
        $($('.statut_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idStatus.push($(this).val());

            let statutCheck = "";
            if ($(this).val() == "En préparation") {
              statutCheck = "En";
            } else {
              statutCheck = $(this).val();
            }

            var nb_proj = $('.nb_proj_statut_' + statutCheck);
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
        // ================== MOIS =================
        $($('.mois_item_checkbox')).each(function () {
          if ($(this).is(':checked')) {
            idMois.push($(this).val());
            var nb_proj = $('.nb_proj_mois_' + $(this).val());
            nb_proj_Mois.push(parseInt(nb_proj.text()));
          } else {
            nb_proj_Mois.push(0);
          }
        });

        for (let i = 0; i < nb_proj_Mois.length; i++) {
          sumMois += nb_proj_Mois[i];
        }
        // sumFormateurs = parseInt(sessionStorage.getItem('projet_nb'));
        $('.resetClick_mois').click(function (e) {
          e.preventDefault();
          const array = $('.mois_item_checkbox');
          for (let i = 0; i < array.length; i++) {
            array[i].checked = false;
            sumMois = 0;
            $('.countSelected_mois').text(sumMois);
          }
        });
        $('.countSelected_mois').text(sumMois);
        $('.countedButton_mois').click(function (e) {
          e.preventDefault();
          if (sumMois > 0) {
            $('.selectedFilter_mois').removeClass('hidden');
            $('.unselectedFilter_mois').addClass('hidden');
          } else {
            $('.selectedFilter_mois').addClass('hidden');
            $('.unselectedFilter_mois').removeClass('hidden');
          }
          closDrop();
        });

        $('.resetClick_mois').click(function () {
          refresh('mois', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idMois.toString(), null);
        });

        $('.iconClose-mois').click(function () {
          getDropdownItem();
          refresh('mois', idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idMois.toString(), null);
          $('.selectedFilter_mois').addClass('hidden');
          $('.unselectedFilter_mois').removeClass('hidden');
        });

        filterItem(idStatus.toString(), idEtps.toString(), idTypes.toString(), idPeriodes, idModules.toString(), idVilles.toString(), idMois.toString());
      });

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
            sessionStorage.setItem('projet_nb', nombreDeI);
            count_card_filter.text(nombreDeI + " projet(s) correspond à votre recherche");
          }
        });
      }
    }
  });
}

function filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idMois) {
  var showResult = $('.showResult');

  $.ajax({
    type: "get",
    url: "/employe/filter/item",
    data: {
      idStatut: idStatut,
      idEtp: idEtp,
      idType: idType,
      idPeriode: idPeriode,
      idModule: idModule,
      idVille: idVille,
      idMois: idMois,
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
            sessionStorage.setItem('projet_nb', nombreDeI);
            count_card_filter.text(nombreDeI + " projet(s) correspond à votre recherche");
          }
        });
      }
    }
  });
}

function refresh(itemClicked, idStatut, idEtp, idType, idPeriode, idModule, idVille, idMois) {
  switch (itemClicked) {
    case "statut":
      $('.statut_item_checkbox').prop('checked', false);
      $('.countSelected_statut').text("");
      filterItem(idStatut, idType, idPeriode, idModule, idVille, idMois)
      break;
    case "entreprise":
      $('.etp_item_checkbox').prop('checked', false);
      $('.countSelected_entreprise').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idMois)
      break;
    case "type":
      $('.type_item_checkbox').prop('checked', false);
      $('.countSelected_type').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idMois)
      break;
    case "periode":
      $('.periode_item_checkbox').prop('checked', false);
      $('.countSelected_periode').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idMois)
      break;
    case "cours":
      $('.module_item_checkbox').prop('checked', false);
      $('.countSelected_cours').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idMois)
      break;
    case "ville":
      $('.ville_item_checkbox').prop('checked', false);
      $('.countSelected_ville').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idMois)
      break;
    case "mois":
      $('.mois_item_checkbox').prop('checked', false);
      $('.countSelected_financement').text("");
      filterItem(idStatut, idEtp, idType, idPeriode, idModule, idVille, idMois)
      break;

    default:
      break;
  }

  // location.reload();
}