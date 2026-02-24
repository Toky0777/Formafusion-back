

function showMiniCV(idFormateur, experiences, diplomes, competences, langues, form) {

  var get_exp_form = $('.get_exp_form_' + idFormateur);
  get_exp_form.html('');

  var get_dp_form = $('.get_dp_form_' + idFormateur);
  get_dp_form.html('');

  var get_cp_form = $('.get_cp_form_' + idFormateur);
  get_cp_form.html('');

  var get_lg_form = $('.get_lg_form_' + idFormateur);
  get_lg_form.html('');

  var photo_formateur_cv = $('.photo_formateur_cv_' + idFormateur);
  photo_formateur_cv.html('');

  var get_name_form = $('.get_name_form_' + idFormateur);
  get_name_form.html('');

  var get_email_form = $('.get_email_form_' + idFormateur);
  get_email_form.html('');

  var get_tel_form = $('.get_tel_form_' + idFormateur);
  get_tel_form.html('');

  if (form.firstName != null) {
    get_name_form.text(form.name + " " + form.firstName);
  } else {
    get_name_form.text(form.name);
  }

  if (form.photo == "" || form.photo == null) {
    photo_formateur_cv.append(
      `<div class="w-full h-full rounded-full text-gray-500 font-bold text-center flex items-center justify-center bg-gray-100 relative"> <i class="fa-solid fa-user-graduate text-4xl text-gray-600"></i></div>`);
  } else {
    photo_formateur_cv.append(
      `<img src="${endpoint}/${bucket}/img/formateurs/${form.photo}" alt="profil" class="object-cover w-full h-full rounded-full">`
    );
  }

  if (form.email != null) {
    get_email_form.text(form.email);
  } else {
    get_email_form.text("--");
  }

  if (form.phone != null) {
    get_tel_form.text(form.phone);
  } else {
    get_tel_form.text("--");
  }

  if (experiences.length > 0) {
    experiences.forEach(exp => {
      const debut = formatDate(exp.Date_debut);
      const fin = formatDate(exp.Date_fin);
      get_exp_form.append(`<li class="w-full text-base text-gray-400">
                      <div class="inline-flex items-center justify-between w-full">
                        <div class="inline-flex items-center gap-1">
                          <span>${exp.Fonction}</span>
                          <span>- ${exp.Lieu_de_stage}</span>
                        </div>
                        <span class="text-right">${debut} - ${fin}</span>
                      </div>
                    </li>`);
    });
  } else {
    get_exp_form.append(`<li class="w-full text-base text-gray-400">Aucune expérience</li>`);
  }

  if (diplomes.length > 0) {
    diplomes.forEach(dp => {
      const debut = formatDate(dp.Date_debut);
      const fin = formatDate(dp.Date_fin);
      get_dp_form.append(`<li class="w-full text-base text-gray-400">
                      <div class="inline-flex items-center justify-between w-full">
                        <div class="inline-flex items-center gap-1">
                          <span>${dp.Diplome}</span>
                          <span>- ${dp.Ecole}</span>
                        </div>
                        <span class="text-right">${debut} - ${fin}</span>
                      </div>
                    </li>`);
    });
  } else {
    get_dp_form.append(`<li class="w-full text-base text-gray-400">Aucun diplôme</li>`);
  }


  if (competences.length > 0) {
    competences.forEach(cpc => {
      get_cp_form.append(`<li class="w-full text-base text-gray-400">
                          <div class="inline-flex items-center justify-between w-full">
                            <div class="inline-flex items-center gap-1">
                              <span>${cpc.Competence}</span>
                            </div>
                            <span id="cpc_rating_${cpc.id}" data-rate="cpc_rating_${cpc.id}" class="inline-flex items-center text-right">
                            </span>
                          </div>
                        </li>`);

      var cpc_rating = $('#cpc_rating_' + cpc.id).attr('data-rate');
      ratyNotation(cpc_rating, cpc.note);
    });
  } else {
    get_cp_form.append(`<li class="w-full text-base text-gray-400">Aucune compétence</li>`);
  }

  if (langues.length > 0) {
    langues.forEach(lg => {
      get_lg_form.append(`<li class="w-full text-base text-gray-400">
                          <div class="inline-flex items-center justify-between w-full">
                            <div class="inline-flex items-center gap-1">
                              <span>${lg.Langue}</span>
                            </div>
                            <span id="lg_rating_${lg.id}" data-rate="lg_rating_${lg.id}" class="inline-flex items-center text-right">
                            </span>
                          </div>
                        </li>`);

      var lg_rating = $('#lg_rating_' + lg.id).attr('data-rate');
      ratyNotation(lg_rating, lg.note);
    });
  } else {
    get_lg_form.append(`<li class="w-full text-base text-gray-400">Aucune compétence linguistique</li>`);
  }
}

function ratyNotation(id, score) {
  $(`#${id}`).html('');
  $(`#${id}`).raty({
    score: score,
    space: false,
    readOnly: true
  });

  $(`#${id} img`).addClass(`w-5 h-5`);
}

function showDetailModule(idModule, details, objectifs, programmes) {

  console.log(details);
  var photo_formation = $('.photo_formation_' + idModule);
  photo_formation.html('');

  var get_name_module = $('.get_name_module_' + idModule);
  get_name_module.html('');

  var get_description_module = $('.get_description_module_' + idModule);
  get_description_module.html('');

  var get_ref_module = $('.get_ref_module_' + idModule);
  get_ref_module.html('');

  var get_domaine_module = $('.get_domaine_module_' + idModule);
  get_domaine_module.html('');

  var get_duree_module = $('.get_duree_module_' + idModule);
  get_duree_module.html('');

  var get_nb_appr_module = $('.get_nb_appr_module_' + idModule);
  get_nb_appr_module.html('');

  var get_objectif_module = $('.get_objectif_module_' + idModule);
  get_objectif_module.html('');

  var get_programme_module = $('.get_programme_module_' + idModule);
  get_programme_module.html('');

  get_name_module.text(details.moduleName ? details.moduleName : 'Non renseigné');
  get_description_module.text(details.description ? details.description : 'Non renseigné');
  get_ref_module.text(details.reference ? details.reference : 'Non renseigné');
  get_domaine_module.text(details.nomDomaine ? details.nomDomaine : 'Non renseigné');

  if (details.dureeJ && details.dureeH) {
    get_duree_module.text(details.dureeH + 'Heures ' + '(' + details.dureeJ + 'Jours)');
  } else if (details.dureeJ) {
    get_duree_module.text(details.dureeJ + 'Jours');
  } else {
    get_duree_module.text(details.dureeH + 'Heures');
  }

  if (details.minApprenant && details.maxApprenant) {
    get_nb_appr_module.text(details.minApprenant + ' à ' + details.maxApprenant + ' Apprenants');
  } else if (details.minApprenant) {
    get_nb_appr_module.text(details.minApprenant + ' Apprenants');
  } else {
    get_nb_appr_module.text(details.maxApprenant + ' Apprenants');
  }

  if (details.module_image == "" || details.module_image == null) {
    photo_formation.append(
      `<div class="w-full h-full rounded-xl text-gray-500 font-bold text-center flex items-center justify-center bg-gray-100 relative"> <i class="fa-solid fa-puzzle-piece text-3xl text-gray-600"></i></div>`);
  } else {
    photo_formation.append(
      `<img src="${endpoint}/${bucket}/img/modules/${details.module_image}" alt="profil" class="object-cover w-full h-full rounded-xl">`
    );
  }

  if (objectifs.length > 0) {
    objectifs.forEach(obj => {
      get_objectif_module.append(`<li class="w-full text-base text-gray-400">${obj.objectif}</li>`);
    });
  } else {
    get_objectif_module.append(`<li class="w-full text-base text-gray-400">Objectif non renseigné</li>`);
  }

  if (programmes.length > 0) {
    var i = 1;
    programmes.forEach(pgr => {
      var j = i++

      get_programme_module.append(`<div class="accordion py-2" id="accordionExample">
            <div class="accordion-item">
              <h2 class="accordion-header" id="heading_${j}">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_${j}" aria-expanded="true" aria-controls="collapse_${j}">
                  Module ` + j + `
                </button>
              </h2>
              <div id="collapse_${j}" class="accordion-collapse collapse visible" aria-labelledby="heading_${j}" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                 <h5 class="text-[#A462A4] text-xl font-semibold mb-2">` + pgr.program_title + `</h5>
                  <div class="text-gray-500">` + pgr.program_description + `</div>
                </div>
              </div>
            </div>
          </div>`);
    });
  } else {
    get_programme_module.append(`<li class="w-full text-base text-gray-400">Programme non renseigné</li>`);
  }
}

// Fonction pour formater la date
function formatDate(dateString, format = 'DD MMM YYYY', oldFormat = 'YYYY-MM-DD') {
  // Convertir la chaîne de date en un objet Date
  var date = moment(dateString.replace(/-/g, '/'), oldFormat);

  // Formater la date selon le format souhaité
  var formattedDate = date.format(format);

  return formattedDate;
}