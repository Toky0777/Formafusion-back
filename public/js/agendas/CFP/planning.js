$(document).ready(function () {
  //getListSalle();
  //addEventSelectMonth(dp);
  //updateTokenClient();
  //listUpcomingEvents();

});

//let NOM_MODULE;
let SALLES;

function setColorProjet(typeProjet) {
  let color = "";
  if (typeProjet == 'Intra') {
    color = '#1565c0';
  } else if (typeProjet == 'Inter') {
    color = '#7209b7';

  } else if (typeProjet == 'Interne') {
    color = '#7F055F';
  }
  return color;
}

function getAllSeancesGroupByJson() {
  let dataEvnts;
  const detailEvents = [];
  const url = `/cfp/agendas/getEventsGroupBy`;
  fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      dataEvnts = (data.seances) ? data.seances : [];
      let idCustomer;
      if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
        idCustomer = sessionStorage.getItem('ID_CUSTOMER');
      }
      for (const evnt of dataEvnts) {
        const HTMLDATA = `
        <div class="flex items-start gap-2 bg-white p-4 w-full h-full">
          <div class="flex flex-col">
            <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
              <div class="inline-flex items-center gap-2">
                <div class="flex flex-col ">                         
                 
                    <p class="text-base  text-gray-700"> ${evnt.module || 'non assigné'}
                    </p>

                  <span class="flex flex-row flex-wrap items-center divide-x divide-gray-200 gap-2">

                      <p class="text-sm text-gray-400"> <span
                        class="text-base text-white px-2 rounded-md bg-[${setColorProjet(evnt.typeProjet)}] ">${evnt.typeProjet || 'non assigné'}</span>
                      </p>               
                                  
                      <p class="text-sm text-gray-400"> <span
                          class="text-sm text-black rounded-md bg-white-100 ">${!(evnt.nameEtp) ? evnt.nameEtps.map(etp => etp.etp_name) : evnt.nameEtp}</span>
                      </p>

                      <p class="text-sm text-gray-500 rounded-md bg-yellow-100 ">${evnt.salle || 'non assigné'} </p>

                  </span>

                    <p class="text-base  text-gray-700"> Formateur(s): ${evnt.formateurs.map(form => form.form_firstname)}
                    </p>

                </div>
              </div>
            </div>
                     
          </div>
        </div>`;

        const TEXTE = `\n\n REF: ${evnt.reference ?? '  -- '} \n\n - ${evnt.module} - \n\n ${evnt.codePostal}- ${evnt.ville} - ${evnt.nameEtp}

     Formateur:
        ${evnt.formateurs[0]?.form_firstname} , ${evnt.formateurs[1]?.form_firstname ?? ''}      
        ` ;

        detailEvents.push({
          id: crypto.randomUUID(),
          start: evnt.start,
          end: evnt.end,
          idEtp: evnt.idEtp,
          idSeance: evnt.idSeance,
          idModule: evnt.idModule,
          idProjet: evnt.idProjet,
          idSalle: evnt.idSalle,
          idCalendar: evnt.idCalendar,
          idCustomer: evnt.idCustomer,
          idFormateur: evnt.idFormateur.map(form => {
            const idFormateur = form.idFormateur;
            return {
              idFormateur: idFormateur,
            };
          }),
          html: HTMLDATA,
          text: TEXTE,
          resource: evnt.resource,
          title: evnt.text,
          module: evnt.module,
          salle: evnt.salle,
          ville: evnt.ville,
          typeProjet: evnt.typeProjet,
          prenom_form: evnt.formateurs.map(formateur => {
            const idForm = formateur.idFormateur;
            const prenom_form = formateur.form_firstname;
            return {
              idFormateur: idForm ? idForm : 0,
              prenom: prenom_form ? prenom_form : 'vide',
            };
          }
          ),
          nameEtp: evnt.nameEtp,
          nameEtps: evnt.nameEtps.map(etp => {
            const nameEtp = etp.etp_name;
            return {
              name: nameEtp,
            };
          }),
          materiels: evnt.materiels.map(mat => {
            const nameMateriel = mat.prestation_name;
            return { name: nameMateriel, };
          }),
          paiement: evnt.paiementEtp,
          statut: evnt.statut,
          nb_appr: (evnt.typeProjet == 'Intra') ? evnt.apprCountIntra : evnt.apprCountInter,
          imgModule: evnt.imgModule,
          codePostal: evnt.codePostal,
          reference: evnt.reference,
          //height: 20,
          // barColor: this.setColorStatutRessource(evnt.statut),
          // backColor: this.setColorStatutRessource(evnt.statut),
          backColor: evnt.backColor,
          quartier: evnt.quartier,
          modalite: evnt.modalite,
          nb_seances: evnt.nb_seances,
        })

      }
      sessionStorage.setItem('ACCESS_EVENTS_GROUP_BY_' + idCustomer, JSON.stringify(detailEvents))

    }).catch(error => { console.error(error) });
}

async function getLastFieldVueSeance() {
  const urlAxios = `/cfp/seances/getLastFieldVueSeances`;
  const { data } = await axios.get(urlAxios);
  const seance = data.seance;
  return seance;
}

async function getSeanceAndTotalTime(idProjet) {
  const urlAxios = `/cfp/seances/${idProjet}/getSeanceAndTotalTime`;
  const { data } = await axios.get(urlAxios);
  return data;
}

async function getFieldVueSeanceOfId(idSeance) {
  const urlAxios = `/cfp/seances/${idSeance}/getFieldVueSeanceOfId`;
  const { data } = await axios.get(urlAxios);
  let seance = data.seance;
  return seance;
}

function getStore(cleAccess) {
  const data = sessionStorage.getItem(cleAccess);
  return JSON.parse(data);
}

function setStore(cleAccess, newVal) {
  sessionStorage.setItem(cleAccess, JSON.stringify(newVal));
}

async function getListSalle() {
  const url = '/cfp/salles/list';
  await fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      //console.log("SALLES---->", data.salles)
      SALLES = data.salles
    })
    .catch(error => { console.error(error) });
}

function setColorStatutRessource(statut) {

  let backColor = "";

  if (statut === 'Réservé') {
    backColor = "#33303D";                //Gris :Réservation
  } else if (statut === 'Annulé') {
    backColor = "#FF6347";                //Rouge Orangé: Annulé 
  } else if (statut === 'En préparation') {
    backColor = "#66CDAA";                //(vert foncé) : En préparation
  } else if (statut === 'Reporté') {
    backColor = "#2E705A";                //(vert foncé) : Reporté
  } else if (statut === 'Planifié') {
    backColor = "#2552BA";                //(Bleu marine) : Planifié
  } else if (statut === 'En cours') {
    backColor = "#1E90FF";                //#1E90FF <===(couleur bleu )
  } else if (statut === 'Cloturé') {
    backColor = "#828282";                //#828282 <===(couleur gris )  
  } else {
    backColor = "#32CD32";                //#32CD32 <===(couleur vert : Terminée)
  }

  return backColor;
}

function getToken() {
  const metas = document.getElementsByTagName("meta");
  for (let i = 0; i < metas.length; i++) {
    const meta = metas[i];
    if (meta.name === "csrf-token") return meta.content;
  }
}

async function getIdNamePaiementStatusEtp(idProjet) //<======= Une Seule fonction pour récuperer les "data" (BACKEND function:detailjson($idProjet))
{
  let donnee;
  const url = `/cfp/projets/${idProjet}/details`;
  await fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      donnee = {
        idEtp: data.project.idEtp,
        nameEtp: data.project.etp_name,
        paiementEtp: data.project.paiement,
        statut: data.project.project_status,
        typeProjet: data.project.project_type,
        imgModule: data.project.module_image,   //<===============modifier...
        forms: [...data.forms],
        apprenants: data.apprenants.length,
        materiels: [...data.materiels],
        nameEtps: [...data.nameEtps],
        reference: data.reference,
        codePostal: data.codePostal,
        modalite: data.modalite,
        quartier: data.quartier,
        nb_seance: data.seanceCount,
        nameCfp: data.nameCfp,
      }
    })
    .catch(error => { console.error(error) });
  console.log('Liste des donnees FORMS-->', donnee) //<===== objet                             
  return donnee;
}

async function listUpcomingEvents() {
  let response;
  let events = [];
  let event_holidays = [];

  // console.log('DATE NOW ==>',(new Date()).toISOString());
  try {
    const request = {
      'calendarId': 'fr.mg#holiday@group.v.calendar.google.com',  //<=== JOURS FERIERS MADA
      'timeMin': '2024-01-01T07:20:27.800Z',
      'showDeleted': false,
      'singleEvents': true,
      'maxResults': 40,
      'orderBy': 'startTime',
    };
    response = await gapi.client.calendar.events.list(request);
  } catch (err) {
    console.log("erreur api google==>", err.message);
    return;
  }

  console.log('Response api google ==>', response)

  events = response.result.items;

  console.log('EVENTS ==>', events);

  for (let holiday of events) {
    event_holidays.push({
      id: holiday.id,
      deleteDisabled: true,
      allday: true,
      start: holiday.start.date,
      end: holiday.end.date,
      text: holiday.summary,
    });
  }


  console.log('HOLIDAYS ==>', event_holidays);

  return event_holidays;


}

function loadDaysHoliday(dp) {

  const holidays = [
    // Ajoutez ici d'autres journées fériées
    { id: 1, deleteDisabled: true, allday: true, start: "2024-01-01T10:00:00", end: "2024-01-01T12:00:00", text: "Jour de l'an" },
    { id: 2, deleteDisabled: true, allday: true, start: "2024-03-08T10:00:00", end: "2024-03-08T12:00:00", text: "Journée internationale de la femme" },
    { id: 3, deleteDisabled: true, allday: true, start: "2024-03-11T10:00:00", end: "2024-03-11T12:00:00", text: "Ramadan" },
    { id: 4, deleteDisabled: true, allday: true, start: "2024-03-29T10:00:00", end: "2024-03-29T12:00:00", text: "Jour des Martyrs" },
    { id: 5, deleteDisabled: true, allday: true, start: "2024-03-31T10:00:00", end: "2024-03-31T12:00:00", text: "Pâques" },
    { id: 6, deleteDisabled: true, allday: true, start: "2024-04-01T10:00:00", end: "2024-04-01T12:00:00", text: "lundi de Pâques" },
    { id: 7, deleteDisabled: true, allday: true, start: "2024-04-10T10:00:00", end: "2024-04-10T12:00:00", text: "Aïd el-Fitr" },
    { id: 8, deleteDisabled: true, allday: true, start: "2024-05-01T10:00:00", end: "2024-05-01T12:00:00", text: "Fête du Travail" },
    { id: 9, deleteDisabled: true, allday: true, start: "2024-05-09T10:00:00", end: "2024-05-09T12:00:00", text: "Ascension" },
    { id: 10, deleteDisabled: true, allday: true, start: "2024-05-19T10:00:00", end: "2024-05-19T12:00:00", text: "Pentecôte" },
    { id: 11, deleteDisabled: true, allday: true, start: "2024-05-20T10:00:00", end: "2024-05-20T12:00:00", text: "Lundi de Pentecôte" },
    { id: 12, deleteDisabled: true, allday: true, start: "2024-06-17T10:00:00", end: "2024-06-17T12:00:00", text: "Aïd el-Kebir" },
    { id: 13, deleteDisabled: true, allday: true, start: "2024-06-26T10:00:00", end: "2024-06-26T12:00:00", text: "Fête de l'Indépendance" },
    { id: 14, deleteDisabled: true, allday: true, start: "2024-06-15T10:00:00", end: "2024-06-15T12:00:00", text: "Assomption" },
    { id: 15, deleteDisabled: true, allday: true, start: "2024-11-01T10:00:00", end: "2024-11-01T12:00:00", text: "Toussaint" },
    { id: 16, deleteDisabled: true, allday: true, start: "2024-12-25T10:00:00", end: "2024-12-25T12:00:00", text: "Noël" },
    { id: 17, deleteDisabled: true, allday: true, start: "2024-12-31T10:00:00", end: "2024-12-31T12:00:00", text: "la Saint-Sylvestre" },
    { id: 18, deleteDisabled: true, allday: true, start: "2025-01-01T10:00:00", end: "2025-01-01T12:00:00", text: "Jour de l'an" },
  ];

  // let events = [];
  // const holidays = listUpcomingEvents();
  // for(let holiday of holidays){
  //  events.push({
  //     id:holiday.id,
  //     deleteDisabled:true,
  //     allday:true,
  //     start:holiday.start.date,
  //     end:holiday.end.date,
  //     text:holiday.summary,
  //  });
  // }

  //console.log('EVENTS==>',events);

  dp.events.list = holidays;
  return dp;
}

function getAllSeances(dp)            //<======= liste des séances avec affichage sur le Daypilot
{
  const idProjet = document.getElementById('project_id_hidden').value;

  let dataEvnts;

  const url = `/cfp/seances/${idProjet}/getAllSeances`;
  fetch(url, { method: "GET" })
    .then(response => response.json())
    .then(data => {
      dataEvnts = data.seances ? data.seances : null;
      SEANCES = data.seances;
      if (dataEvnts != null) {
        for (const evnt of dataEvnts) {
          const endTime = evnt.end.split('T')[1];
          const startTime = evnt.start.split('T')[1];
          //getIdNamePaiementStatusEtp(idProjet).then(object => {
          //  const tabForm = [...object.forms];
          HTMLDATA = `<div class="flex items-start gap-2 bg-white p-4 w-full h-full">
                          <div class="flex flex-col">
                            <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
                              <div class="inline-flex items-center gap-2">
                                <div class="flex flex-col">
                                  <p class="text-lg font-semibold text-gray-700">${evnt.module || 'non assigné'}
                                  </p>
                                  <p class="text-sm text-gray-400">Client : <span
                                      class="text-sm text-gray-500">${!(evnt.nameEtp) ? evnt.nameEtps.map(etp => etp.etp_name) : evnt.nameEtp}</span>
                                  </p>
                                  <p class="text-sm text-gray-400">Projet : <span
                                      class="text-sm text-gray-500">${evnt.typeProjet || 'non assigné'}</span>
                                  </p>
                                </div>
                              </div>
                            </div>
                            <div class="flex flex-col">
                              <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                <p class="text-sm text-gray-400">Lieu :</p>
                                <p class="text-sm text-gray-500">  ${evnt.ville || 'non assigné'}</p>
                              </div>
                              <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                <p class="text-sm text-gray-400">Salle :</p>
                                <p class="text-sm text-gray-500">${evnt.salle || 'non assigné'} </p>
                              </div>
                              <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                <p class="text-sm text-gray-400">Financement :</p>
                                <p class="text-sm text-gray-500">${evnt.paiementEtp || 'non assigné'}</p>
                              </div>
                              <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                <p class="text-sm text-gray-400">Matériel :</p>
                                <p class="text-sm text-gray-500">1 Ordinateur</p>
                              </div>
                              <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                <p class="text-sm text-gray-400">Formateur :</p>
                                <p class="text-sm text-gray-500">${evnt.formateurs.map(formateur => formateur.form_firstname) || 'non assigné'}</p>
                              </div>
                              <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                <p class="text-sm text-gray-400">Apprenants :</p>
                                <p class="text-sm text-gray-500">${evnt.apprCount || 'non assigné'} Apprenant(s)...</p>
                              </div>
                              <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                <p class="text-sm text-gray-400">Heures :</p>
                                <p class="text-sm text-gray-500">${startTime.split(':')[0]}H${startTime.split(':')[1]} à ${endTime.split(':')[0]}H${endTime.split(':')[1]}</p>  
                              </div>
                            </div>
                          </div>
                        </div>`;

          dp.events.add({
            start: evnt.start,
            end: evnt.end,
            idSeance: evnt.idSeance,   //<===== idSeance
            idSalle: evnt.idSalle,
            idModule: evnt.idModule,
            idProjet: evnt.idProjet,
            idCalendar: evnt.idCalendar,
            html: HTMLDATA,
            resource: evnt.resource,
            title: evnt.text,
            module: evnt.module,
            salle: evnt.salle,
            ville: evnt.ville,
            typeProjet: evnt.typeProjet,
            prenom_form: evnt.formateurs.map(formateur => {
              const idForm = formateur.idFormateur;
              const prenom_form = formateur.form_firstname;
              return {
                idFormateur: idForm,
                prenom: prenom_form,
              };
            }
            ),
            paiement: evnt.paiementEtp,
            imgModule: evnt.imgModule,  //<========================== modifié...
            height: 20,
            barColor: setColorStatutRessource(evnt.statut),
            //allday:true,
          });

          //})
        }
      }
    }).catch(error => { console.error(error) });
}

// function intervalleCalculator(start, end) {
// }

function updateEventMoved(args, dp) {
  const token = getToken();
  const idSeance = args.e.data.idSeance;
  const title = args.e.data.title;
  const idSalle = args.e.data.resource;
  const email_form = args.e.data.email_form;
  const prenom_form = args.e.data.prenom_form;
  const idCalendar = args.e.data.idCalendar;

  const end = args.e.data.end.toStringSortable("yyyy-M-dTh:mm:tt");
  const start = args.e.data.start.toStringSortable("yyyy-M-dTh:mm:tt");

  const dateString = start.split('T')[0];
  const startString = start.split('T')[1];
  const endString = end.split('T')[1];

  // Récupérer la différence de temps entre start et end
  const timeStart = Date.parse(start);
  const timeEnd = Date.parse(end);
  const mins = (timeEnd - timeStart) / (1000 * 60);

  // getting the hours.
  const hrs = Math.floor(mins / 60);
  // getting the minutes.
  let min = mins % 60;

  strHrs = hrs < 2 ? 'heure' : 'heures';
  // formatting the minutes.
  min = min < 10 ? '0' + min : min;
  strMin = min < 2 ? 'minute' : 'minutes';
  // returning them as a string.

  const intervalle = hrs + ' ' + strHrs + ' ' + min + ' ' + strMin;
  console.log(intervalle);

  const data = {
    "idSeance": idSeance,
    'dateSeance': dateString,
    "heureDebut": startString,
    "heureFin": endString,
    "intervalle": intervalle,
    "idSalle": idSalle,
    "id_google_seance": idCalendar,
    "startTime": start,
    "endTime": end,
  };
  const url = `/cfp/seances/${idSeance}/update`;
  fetch(url, {
    method: 'PATCH', headers: {
      "Content-Type": "application/json",
      "X-CSRF-Token": token
    }, body: JSON.stringify(data)
  })
    .then(function (response) {
      toastr.success("Session modifiée avec succès", 'Succès', { timeOut: 1500 });
      console.log("DATA-->", data)
      //getIdCustomer().then(idCustomer => {
      let idCustomer;
      if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
        idCustomer = sessionStorage.getItem('ID_CUSTOMER');
      }
      sessionStorage.setItem('UPDATED_STORAGE', true);

      let dataInStoreEventsDetails = getStore('ACCESS_EVENTS_DETAILS_' + idCustomer);
      let dataInStoreEventsGroupBy = getStore('ACCESS_EVENTS_GROUP_BY_' + idCustomer);

      if (dataInStoreEventsDetails && dataInStoreEventsGroupBy) {
        /*------------------------- DETAIL--------------------------------------*/
        for (let i = 0; i < dataInStoreEventsDetails.length; i++) {
          if (dataInStoreEventsDetails[i].idSeance === idSeance) {
            dataInStoreEventsDetails[i].start = start;
            dataInStoreEventsDetails[i].end = end;
            break;
          }
        }
        /*--------------------------GROUP BY--------------------------------------*/
        for (let i = 0; i < dataInStoreEventsGroupBy.length; i++) {
          if (dataInStoreEventsGroupBy[i].idSeance === idSeance) {
            dataInStoreEventsGroupBy[i].start = start;
            dataInStoreEventsGroupBy[i].end = end;
          }
        }
        this.setStore('ACCESS_EVENTS_DETAILS_' + idCustomer, dataInStoreEventsDetails);
        this.setStore('ACCESS_EVENTS_GROUP_BY_' + idCustomer, dataInStoreEventsGroupBy);
      }

      /** MODIFIER L'AGENDA GOOGLE */
      const storage = localStorage.getItem('ACCESS_TOKEN');
      const idAgenda = localStorage.getItem('ID_AGENDA');
      storage ? executeUpdateGoogle(idCalendar, dp, data, idAgenda) : '';

    })
    .catch(function (error) {
      console.log('Erreur inconnue !=>', error);
    });
}

function deleteEvent(args, dp) {
  const e = args.e;
  const idSeance = e.data.idSeance;
  const url = `/cfp/seances/${idSeance}/delete`;
  const token = getToken();
  const idCalendar = e.data.idCalendar;

  console.log('IDCALENDAR==>', idCalendar);

  fetch(url, {
    method: 'DELETE', headers: {
      "Content-Type": "application/json",
      "X-CSRF-Token": token
    }
  })
    .then(response => {
      toastr.success("Session suprimée avec succès", 'Succès', { timeOut: 1500 });

      console.log('Réponse DELETE :', response.json());
      let idCustomer;
      if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
        idCustomer = sessionStorage.getItem('ID_CUSTOMER');
      }
      //getIdCustomer().then(idCustomer => {
      sessionStorage.setItem('UPDATED_STORAGE', true);
      //let dataInStoreEventsResources = this.getStore('ACCESS_EVENTS_RESOURCE_' + idCustomer);
      const dataInStoreEventsGroupby = getStore('ACCESS_EVENTS_GROUP_BY_' + idCustomer);
      const dataInStoreEventsDetails = getStore('ACCESS_EVENTS_DETAILS_' + idCustomer);

      // const newDataInStoreEventsResources = dataInStoreEventsResources.filter(data => data.idSeance !== idSeance);        //<===== Si utilisateur GOOGLE j'utilise idCalendar comme réference...
      const newDataInStoreEventsDetails = dataInStoreEventsDetails.filter(data => data.idSeance !== idSeance);            //<===== Si utilisateur GOOGLE j'utilise idCalendar comme réference...
      const newDataInstoreEventsGroupBy = dataInStoreEventsGroupby.filter(data => data.idSeance !== idSeance);

      //setStore('ACCESS_EVENTS_RESOURCE_' + idCustomer, newDataInStoreEventsResources);
      setStore('ACCESS_EVENTS_DETAILS_' + idCustomer, newDataInStoreEventsDetails);
      setStore('ACCESS_EVENTS_GROUP_BY_' + idCustomer, newDataInstoreEventsGroupBy);

      const storage = localStorage.getItem('ACCESS_TOKEN');
      const idAgenda = localStorage.getItem('ID_AGENDA');
      console.log('ID_AGENDA', idAgenda)
      storage ? executeDeleteOnGoogle(idCalendar, dp, idAgenda) : '';
      // if(storage){
      //   getIdCalendar('Formations').then((result)=>{
      //     console.log('deleteIdCalendar result ==>',result);
      //     // executeAddOnGoogle(dp, seance, object,idCalendar);
      //      if (result.isNew) {

      //        showAlert('Calendrier créé avec succès', 'success');
      //      } else {

      //        console.log('RES ID==>',result.id)
      //        showAlert('Calendrier existant récupéré', 'info');
      //        const idAgenda= result.id;
      //        executeDeleteOnGoogle(dp,idCalendar,idAgenda);
      //      }

      //   });
      // }

    })

    .catch(error => {
      toastr.error("Erreur inconnue !", 'Erreur', { timeOut: 1500 });
    });
  dp.update();
}

/************************** FONCTION APPEL A LA SUPPRESSION SUR GOOGLE CALENDAR ********************************************************* */
function executeDeleteOnGoogle(idCalendar, dp, idAgenda) {
  updateTokenClient(); //On actualise le TOKKEN du client(l'App n'accepte que l'access_token de oauth2 de google!)     
  return gapi.client.calendar.events.delete({
    //"calendarId": "c_192015d161bd1c90744d5a85afed0d9b5d0d408a49c1835b02da95b9c03756a4@group.calendar.google.com",
    "calendarId": idAgenda,
    "eventId": idCalendar,//champ de la table Sessions dans la BD...
  })
    .then(function (response) {
      // Handle the results here (response.result has the parsed body).
      console.log("Response", response);
      dp.message("Event delete on GOOGLE ", { cssClass: "shadow-2xl rounded-md " });
    },
      function (err) { console.error("Execute error", err); });
}
/*************************************************************************************************************** */
/****************Modifie la date de l'evennement deb et fin de GOOGLE********************************************************** */
function executeUpdateGoogle(idCalendar, dp, data, idAgenda) {
  updateTokenClient(); //On actualise le TOKKEN du client(l'App n'accepte que l'access_token de oauth2 de google!)     
  return gapi.client.calendar.events.patch({
    //"calendarId": "c_192015d161bd1c90744d5a85afed0d9b5d0d408a49c1835b02da95b9c03756a4@group.calendar.google.com",
    "calendarId": idAgenda,
    "eventId": idCalendar,

    "resource": {
      "end": {
        "dateTime": data.endTime,
        "timeZone": "Africa/Nairobi"
      },
      "start": {
        "dateTime": data.startTime,
        "timeZone": "Africa/Nairobi"
      }
    }
  })
    .then(function (response) {
      // Handle the results here (response.result has the parsed body).
      console.log("Response", response);
      dp.message("Event update on GOOGLE ", { cssClass: "shadow-2xl rounded-md " });
    },
      function (err) { console.error("Execute error", err); console.error("ERROR UPDATE GOOGLE!!!"); });
}
/********************************************************************************************************************** */
function updateEventResized(args, dp) {
  const token = getToken();
  const idSeance = args.e.data.idSeance;
  const title = args.e.data.title;
  const end = args.e.data.end.toStringSortable("yyyy-M-dTh:mm:tt");
  const start = args.e.data.start.toStringSortable("yyyy-M-dTh:mm:tt");
  const idSalle = args.e.data.resource;
  const idCalendar = args.e.data.idCalendar;

  const dateString = start.split('T')[0];
  const startString = start.split('T')[1];
  const endString = end.split('T')[1];

  // Récupérer la différence de temps entre start et end
  const timeStart = Date.parse(start);
  const timeEnd = Date.parse(end);
  const mins = (timeEnd - timeStart) / (1000 * 60);

  // getting the hours.
  const hrs = Math.floor(mins / 60);
  // getting the minutes.
  let min = mins % 60;

  strHrs = hrs < 2 ? 'heure' : 'heures';
  // formatting the minutes.
  min = min < 10 ? '0' + min : min;
  strMin = min < 2 ? 'minute' : 'minutes';
  // returning them as a string.

  const intervalle = hrs + ' ' + strHrs + ' ' + min + ' ' + strMin;
  console.log(intervalle);

  const data = {
    "idSeance": idSeance,
    'dateSeance': dateString,
    "heureDebut": startString,
    "heureFin": endString,
    "intervalle": intervalle,
    "idSalle": idSalle,
    "id_google_seance": idCalendar,
    "startTime": start,
    "endTime": end,
  };
  const url = `/cfp/seances/${idSeance}/update`;
  fetch(
    url,
    {
      method: 'PATCH',
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": token
      },
      body: JSON.stringify(data)
    })
    .then(function (response) {
      toastr.success("Session modifiée avec succès", 'Succès', { timeOut: 1500 });
      dp.update();

      //getIdCustomer().then(idCustomer => {
      let idCustomer;
      if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
        idCustomer = sessionStorage.getItem('ID_CUSTOMER');
      }
      sessionStorage.setItem('UPDATED_STORAGE', true);

      const dataInStoreEventsDetails = this.getStore('ACCESS_EVENTS_DETAILS_' + idCustomer);
      const dataInStoreEventsGroupBy = this.getStore('ACCESS_EVENTS_GROUP_BY_' + idCustomer);

      if (dataInStoreEventsDetails && dataInStoreEventsGroupBy) {

        /*------------------------- DETAIL--------------------------------------*/
        for (let i = 0; i < dataInStoreEventsDetails.length; i++) {
          if (dataInStoreEventsDetails[i].idSeance === idSeance) {
            dataInStoreEventsDetails[i].start = start;
            dataInStoreEventsDetails[i].end = end;
            break;
          }
        }

        /*--------------------------GROUP BY--------------------------------------*/
        for (let i = 0; i < dataInStoreEventsGroupBy.length; i++) {
          if (dataInStoreEventsGroupBy[i].idSeance === idSeance) {
            dataInStoreEventsGroupBy[i].start = start;
            dataInStoreEventsGroupBy[i].end = end;
          }
        }

        setStore('ACCESS_EVENTS_DETAILS_' + idCustomer, dataInStoreEventsDetails);
        setStore('ACCESS_EVENTS_GROUP_BY_' + idCustomer, dataInStoreEventsGroupBy);

      }

      /** MODIFIER L'AGENDA GOOGLE */
      const idAgenda = localStorage.getItem('ID_AGENDA');
      idCalendar ? executeUpdateGoogle(idCalendar, dp, data, idAgenda) : '';

    })
    .catch(function (error) { console.log('RESIZE probleme!', error) });
}

function displayVille(ville) {

  switch (ville) {
    case 'Antananarivo':
      return '3GJ5+5P';
    case 'Toamasina':
      return '5XC6+75';
    case 'Mahajanga':
      return '56J6+F3';
    case 'Antsirabe':
      return '47HP+9Q';
    case 'Fianarantsoa':
      return 'R2C7+V2';
  }

}

function openSession(id) {
  const dp = new DayPilot.Calendar(id, {
    locale: "fr-fr",
    startDate: DayPilot.Date.today(),
    viewType: "Week",
    showAllDayEvents: true,
    scrollLabelsVisible: true,
    timeRangeSelectingStartEndEnabled: true,
    headerDateFormat: "dddd dd/MM/yyyy",
    eventDeleteHandling: "Update",
    eventMoveHandling: "Update",
    eventClickHandling: "Disabled",
    eventHoverHandling: "Disabled",
    eventDoubleClickHandling: "Update",


    onBeforeCellRender: args => {
      const holidays = [
        // Ajoutez ici d'autres journées fériées
        { date: "2024-01-01", name: "Jour de l'an" },
        { date: "2024-03-08", name: "Journée internationale de la femme" },
        { date: "2024-03-11", name: "Ramadan" },
        { date: "2024-03-29", name: "Jour des Martyrs" },
        { date: "2024-03-31", name: "Pâques" },
        { date: "2024-04-01", name: "lundi de Pâques" },
        { date: "2024-04-10", name: "Aïd el-Fitr" },
        { date: "2024-05-01", name: "Fête du Travail" },
        { date: "2024-05-09", name: "Ascension" },
        { date: "2024-05-19", name: "Pentecôte" },
        { date: "2024-05-20", name: "Lundi de Pentecôte" },
        { date: "2024-06-17", name: "Aïd el-Kebir" },
        { date: "2024-06-26", name: "Fête de l'Indépendance" },
        { date: "2024-06-15", name: "Assomption" },
        { date: "2024-11-01", name: "Toussaint" },
        { date: "2024-12-25", name: "Noël" },
        { date: "2024-12-31", name: "la Saint-Sylvestre" },
        { date: "2025-01-01", name: "Jour de l'an" },
      ];
      // Vérifier si la cellule correspond à un jour férié
      const item = holidays.find(function (range) {
        const start = new DayPilot.Date(range.date);
        const end = new DayPilot.Date(range.date).addDays(1); // Ajouter un jour pour inclure la fin du jour férié
        return DayPilot.Util.overlaps(start, end, args.cell.start, args.cell.end);
      });
      //jour férié ...
      if (item) {
        args.cell.backColor = "#fff2cc"; // Personnaliser la couleur de fond
        //args.cell.disabled = true; // Désactiver la cellule

      }

      //Desactive les "weekend" 
      if (args.cell.start.getDayOfWeek() === 6 || args.cell.start.getDayOfWeek() === 0) {
        //args.cell.disabled = true;
        args.cell.backColor = "#d9ead3";
      }
      //Desactive de midi à midi 30
      if (args.cell.start.getHours() === 12 && args.cell.start.getMinutes() >= 0 && args.cell.start.getMinutes() <= 30) {
        //args.cell.disabled = true; // Désactive la cellule
        args.cell.backColor = "#ccc"; // Change la couleur de fond pour indiquer la cellule désactivée
      }
    },

    onBeforeEventRender: args => {
      args.data.cssClass = "shadow-2xl rounded-md";
    },

    /********************************************AJOUT D'UN EVENNEMENT**************************************************************** */
    onTimeRangeSelected: async (args) => {

      const dateStart = args.start.value;
      const dateEnd = args.end.value;
      console.log('args AJOUT-->', args);

      const detailEvent = [];
      const newIdCalendar = '';// <===== à Récuperer si c'est un google user...

      const idProjet = document.getElementById('project_id_hidden').value;
      console.log('ID PROJET==>', idProjet)
      //Ajout à la BD
      const url = '/cfp/seances'
      const date = dateStart.split('T')[0];
      const start = dateStart.split('T')[1];
      const end = dateEnd.split('T')[1];


      // Récupérer la différence de temps entre start et end
      const timeStart = Date.parse(dateStart);
      const timeEnd = Date.parse(dateEnd);
      const mins = (timeEnd - timeStart) / (1000 * 60);

      // getting the hours.
      const hrs = Math.floor(mins / 60);
      // getting the minutes.
      let min = mins % 60;

      strHrs = hrs < 2 ? 'heure' : 'heures';
      // formatting the minutes.
      min = min < 10 ? '0' + min : min;
      strMin = min < 2 ? 'minute' : 'minutes';
      // returning them as a string.

      const intervalle = hrs + ' ' + strHrs + ' ' + min + ' ' + strMin;
      console.log(intervalle);

      const idSalle = 1;    //modal.result.salle;
      const token = getToken();

      const data =
      {
        'dateSeance': date,
        'heureDebut': start,
        'heureFin': end,
        //'idSalle': idSalle,
        'idProjet': idProjet,
        'intervalle': intervalle,
      }

      fetch(
        url,
        {
          method: 'POST',
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": token
          },
          body: JSON.stringify(data)
        }
      ).then(function (response) {
        /*************************************** POUR LES DETAILS ******************************************************************/
        getLastFieldVueSeance().then(seance => {
          getIdNamePaiementStatusEtp(seance.idProjet).then(object => {
            const start = seance.dateSeance + 'T' + seance.heureDebut;
            const end = seance.dateSeance + 'T' + seance.heureFin;
            const tabForm = [...object.forms];
            const tabMat = [...object.materiels];
            const tabEtp = [...object.nameEtps];

            const HTMLDATA = `<div class="flex items-start gap-2 bg-white p-4 w-full h-full">
                                    <div class="flex flex-col">
                                      <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
                                        <div class="inline-flex items-center gap-2">
                                          <div class="flex flex-col">
                                            <p class="text-lg font-semibold text-gray-700"> ${seance.module_name || 'non assigné'}
                                            </p>
                                            <p class="text-sm text-gray-400">Client : <span
                                                class="text-sm text-gray-500">${object.nameEtp ? object.nameEtp : object.nameEtps.map(e => e.etp_name)}</span>
                                            </p>
                                            <p class="text-sm text-gray-400">Projet : <span
                                                class="text-sm text-gray-500">${object.typeProjet || 'non assigné'},</span>
                                            </p>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="flex flex-col">
                                        <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                          <p class="text-sm text-gray-400">Lieu :</p>
                                          <p class="text-sm text-gray-500">  ${seance.ville || 'non assigné'}</p>
                                        </div>
                                        <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                          <p class="text-sm text-gray-400">Salle :</p>
                                          <p class="text-sm text-gray-500">${seance.salle_name || 'non assigné'} </p>
                                        </div>
                                        <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                          <p class="text-sm text-gray-400">Financement :</p>
                                          <p class="text-sm text-gray-500">${object.paiementEtp || 'non assigné'}</p>
                                        </div>
                                        <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                          <p class="text-sm text-gray-400">Matériel :</p>
                                          <p class="text-sm text-gray-500">1 Ordinateur</p>
                                        </div>
                                        <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                          <p class="text-sm text-gray-400">Formateur :</p>
                                          <p class="text-sm text-gray-500">${tabForm.map(formateur => formateur.form_firstname) || 'non assigné'}</p>
                                        </div>
                                        <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                                          <p class="text-sm text-gray-400">Apprenants :</p>
                                          <p class="text-sm text-gray-500">${object.apprenants || 'non assigné'}</p>
                                        </div>
                                      </div>
                                    </div>
                                  </div>`;

            detailEvent.push({
              id: DayPilot.guid(),
              idEtp: object.idEtp,
              start: start,
              end: end,
              idSeance: seance.idSeance,
              idProjet: seance.idProjet,
              idCalendar: newIdCalendar,
              html: HTMLDATA,
              //  resource: evnt.resource,
              title: seance.project_title,
              module: seance.module_name,
              salle: seance.salle_name,
              ville: seance.ville,
              typeProjet: object.typeProjet,
              prenom_form: tabForm.map(formateur => {
                return {
                  idFormateur: formateur.idFormateur,
                  prenom: formateur.form_firstname,
                  email: formateur.email,
                }
              }
              ),
              nameEtp: object.nameEtp,
              paiement: object.paiementEtp,
              statut: object.statut,
              nb_appr: object.apprenants,
              imgModule: object.imgModule,
              materiels: tabMat.map(mat => {
                return { name: mat.prestation_name }
              }
              ),
              nameEtps: object.nameEtps ? object.nameEtps.map(etp => etp.etp_name) : [],

              codePostal: object.codePostal,
              reference: object.reference,
              quartier: object.quartier,
              modalite: object.modalite,
              nb_seance: object.nb_seance,
              idFormateur: tabForm.map(form => {
                return { idFormateur: form.idFormateur }
              }),

              height: 20,
              barColor: setColorStatutRessource(object.statut),
            })

            dp.events.add({
              id: DayPilot.guid(),
              idEtp: object.idEtp,
              start: start,
              end: end,
              idSeance: seance.idSeance,
              idProjet: seance.idProjet,
              idCalendar: newIdCalendar,
              html: HTMLDATA,
              //  resource: evnt.resource,
              title: seance.project_title,
              module: seance.module_name,
              salle: seance.salle_name,
              ville: seance.ville,
              typeProjet: object.typeProjet,
              nameEtp: object.nameEtp,
              paiement: object.paiementEtp,
              statut: object.statut,
              nb_appr: object.apprenants,
              imgModule: object.imgModule,
              materiels: tabMat.map(mat => {
                return { name: mat.prestation_name }
              }
              ),
              nameEtps: object.nameEtps ? object.nameEtps.map(etp => etp.etp_name) : [],

              codePostal: object.codePostal,
              reference: object.reference,
              quartier: object.quartier,
              modalite: object.modalite,
              nb_seance: object.nb_seance,
              prenom_form: tabForm.map(formateur => {
                return {
                  idFormateur: formateur.idFormateur,
                  prenom: formateur.form_firstname
                }
              }
              ),

              height: 20,
              barColor: setColorStatutRessource(object.statut),
              // backColor: obj.backColor,
            });


            //on récupère le dernier sessionStorage
            let idCustomer;
            if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
              idCustomer = sessionStorage.getItem('ID_CUSTOMER');
            }
            /****************** MODIFICATION ACCESS_EVENTS_DETAILS_*******************************/
            sessionStorage.setItem('UPDATED_STORAGE', true);
            const lastDataInStoreEventDetails = (this.getStore('ACCESS_EVENTS_DETAILS_' + idCustomer)) ? this.getStore('ACCESS_EVENTS_DETAILS_' + idCustomer) : [];
            const newDataInStoreEventsDetails = [...lastDataInStoreEventDetails, ...detailEvent];
            this.setStore('ACCESS_EVENTS_DETAILS_' + idCustomer, newDataInStoreEventsDetails);

            /*******************MODIFICATION ACCESS_EVENTS_GROUP_BY_******************************/
            getAllSeancesGroupByJson();

            /*******************MODIFICATION  SUR GOOGLE CALENDAR_********************************/
            const storage_token = localStorage.getItem('ACCESS_TOKEN');
            const storage_idAgenda = localStorage.getItem('ID_AGENDA');
            console.log('STORAGE LOCAL==>', storage_idAgenda);
            (storage_token && storage_idAgenda) ? executeAddOnGoogle(dp, args, seance, object, storage_idAgenda) : '';

          })
        }).catch(function (error) { console.log(error) });

        getSeanceAndTotalTime(data.idProjet).then(event => {
          $('#head_session').text(`Vous avez ${event.nbSeance} sessions d'une durée total de ${event.totalSession.sumHourSession}`);
        })
        toastr.success("Session ajoutée avec succès", 'Succès', { timeOut: 1500 });
        dp.update();
      });
    },

    onEventMoved: (args) => {
      updateEventMoved(args, dp);
    },
    onEventDelete: (args) => {
      if (!confirm("Voulez-vous vraiment supprimer cette séance?")) {
        args.preventDefault();
      }
    },
    onEventDeleted: (args) => {
      const idCalendar = args.e.data.idCalendar;

      console.log('IDCALENDAR FAKE==>', args.e.data);
      deleteEvent(args, dp);
      getSeanceAndTotalTime(args.e.data.idProjet).then(event => {
        console.log('TOTAL event ==>', event);
        $('#head_session').text(`Vous avez ${event.nbSeance} sessions d'une durée total de ${event.totalSession.sumHourSession}`);
        // executeDeleteOnGoogle(idCalendar);

      })
    },
    onEventResized: (args) => {
      updateEventResized(args, dp);
    },

    eventClickHandling: "Disabled",
    eventHoverHandling: "Disabled",
  });

  dp.init();

  tdy = $('#dp_today');
  yst = $('#dp_yesterday');
  tmr = $('#dp_tomorrow');

  tdy.click(function () {
    today(dp);
  });

  yst.click(function () {
    yesterday(dp);
  });

  tmr.click(function () {
    tomorrow(dp);
  });

  getAllSeances(dp);
  loadDaysHoliday(dp);
  addEventSelectMonth(dp);
}
/**************************************************************************************************************** */
function today(dp) {
  dp.startDate = DayPilot.Date.today();
  const monthDaypilot = DayPilot.Date.today().value;
  const date = new Date(monthDaypilot);
  const month = date.getUTCMonth();
  const monthSelectorWeek = document.getElementById('monthSelectorWeek');
  const monthSelectorWeekEdit = document.getElementById('monthSelectorWeekEdit');

  console.log('monthSelectorWeek-->', monthSelectorWeek);
  console.log('monthSelectorWeekEdit-->', monthSelectorWeekEdit);

  monthSelectorWeek ? monthSelectorWeek.options[month].selected = true : null;

  monthSelectorWeekEdit ? monthSelectorWeekEdit.options[month].selected = true : null;

  dp.update();
}

function yesterday(dp) {
  dp.startDate = dp.startDate.addDays(-7);
  const monthDaypilot = dp.startDate.value;
  const date = new Date(monthDaypilot);
  const month = date.getUTCMonth();
  document.getElementById('monthSelectorWeek').options[month].selected = true;
  dp.update();
}

function tomorrow(dp) {
  dp.startDate = dp.startDate.addDays(7);
  const monthDaypilot = dp.startDate.value;
  const date = new Date(monthDaypilot);
  const month = date.getUTCMonth();
  document.getElementById('monthSelectorWeek').options[month].selected = true;
  dp.update();
}

function addEventSelectMonth(dp) {
  //Choix du mois dans detail...
  document.querySelectorAll("select").forEach(function (el) {
    el.addEventListener("change", function () {
      const selectedMonth = parseInt(this.value);
      dp.startDate = DayPilot.Date.today();
      dp.startDate = dp.startDate.addMonths(selectedMonth - 1);
      dp.update();
    })
  })

}

async function getIdGoogle(idCalendar) {
  return idCalendar;
}

function updateIdCalendarSession(idCalendar) {
  const token = getToken();
  const data = {
    //'idCalendar': 'nruoe7djoldtvdkessm07dci7g',
    'idCalendar': idCalendar
  };
  const url = `/cfp/seances/idCalendarLastSession/updateId`;
  fetch(
    url,
    {
      method: 'PATCH',
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": token
      },
      body: JSON.stringify(data)
    }
  ).then((response) => {
    console.log(response);
    response.json();
    toastr.success("Session modifiée avec succès", 'Succès', { timeOut: 1500 });
  }
  ).then(data => { console.log(data) })
    .catch(err => console.log(err));
}

function updateIdListCalendarSession(idSeance, idGoogle) {
  const token = getToken();
  const data = {
    'idSeance': idSeance,
    'idGoogle': idGoogle,
  };
  console.log('update DATA =+>', data);
  const url = `/cfp/seances/idListCalendarSession/updateIDs`;
  fetch(
    url,
    {
      method: 'PATCH',
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": token
      },
      body: JSON.stringify(data)
    }
  ).then((response) => {
    console.log(response);
    response.json();
    toastr.success("Session modifiée avec succès", 'Succès', { timeOut: 1500 });
  }
  ).then(data => { console.log(data) })
    .catch(err => console.log(err));
}


function updateIdCalendarOpportunity(idCalendar, idOpportunity) {
  const token = getToken();
  const data = {
    'idCalendar': idCalendar
  };
  const url = `/cfp/prospection/${idOpportunity}/updateId`;
  fetch(
    url,
    {
      method: 'PATCH',
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": token
      },
      body: JSON.stringify(data)
    }
  ).then((response) => {
    console.log(response);
    response.json();
    toastr.success("Opportunité modifiée avec succès", 'Succès', { timeOut: 1500 });
  }
  ).then(data => { console.log(data) })
    .catch(err => console.log(err));
}

// Nouvelle fonction pour gérer la mise à jour du token
function updateTokenClient() {
  return new Promise((resolve, reject) => {
    if (typeof gapi === 'undefined' || !gapi.client) {
      console.error('GAPI n\'est pas encore chargé');
      reject(new Error('GAPI n\'est pas encore chargé'));
      return;
    }

    try {
      const refresh_token = localStorage.getItem('ACCESS_TOKEN');

      if (!refresh_token) {
        console.warn('Aucun token trouvé dans localStorage');
        resolve(false);
        return;
      }

      gapi.client.setToken({
        access_token: refresh_token,
        authuser: '0',
        expires_in: 5055,
        prompt: 'consent',
        scope: SCOPES,
        token_type: 'Bearer'
      });

      console.log('Token mis à jour avec succès');
      resolve(true);
    } catch (error) {
      console.error('Erreur lors de la mise à jour du token:', error);
      reject(error);
    }
  });
}

// Nouvelle fonction pour essayer de restaurer le token
async function tryRestoreToken() {
  if (gapiInited && gisInited) {
    try {
      updateTokenClient();
      // Si le token a été restauré avec succès, on peut mettre à jour l'interface
      document.getElementById('signout_button').style.visibility = 'visible';
      document.getElementById('authorize_button').innerText = 'Refresh';
    } catch (error) {
      console.error('Erreur lors de la restauration du token:', error);
    }
  }
}

async function getIdCalendar(calendarName) {
  try {
    // Vérifiez d'abord si le calendrier existe
    updateTokenClient();
    const calendarList = await gapi.client.calendar.calendarList.list();

    for (const calendarItem of calendarList.result.items) {
      if (calendarItem.summary === calendarName) {
        // Le calendrier existe déjà, retournez son ID
        return {
          id: calendarItem.id,
          isNew: false
        };
      }
    }

    // Le calendrier n'existe pas, créez-le
    // const newCalendar = await this.calendar.calendars.insert({
    //   requestBody: {
    //     summary: calendarName,
    //     timeZone: 'Africa/Nairobi' // Adaptez à votre fuseau horaire
    //   }
    // });

    // showAlert(`Calendrier "${calendarName}" créé avec succès`, 'success');

    // return {
    //   id: newCalendar.data.id,
    //   isNew: true
    // };
  } catch (error) {
    console.error('Erreur lors de la création/récupération du calendrier:', error);
    throw error;
  }
}

//--  AJOUT DES OPPORTUNITES SUR GOOGLE  --
function executeAddOpportunityOnGoogle(dp, args, idAgendaOpportunity) {
  updateTokenClient(); //On actualise le TOKEN du client(l'App n'accepte que l'access_token de oauth2 de google!)

  const dateStart = args.source.data.startDate;
  const dateEnd = args.source.data.endDate;
  const idOpportunity = args.source.data.idOpportunity;
  const dataGoogle = {
    "calendarId": idAgendaOpportunity,
    "resource": {
      "end": {
        "dateTime": dateEnd,
        "timeZone": "Africa/Nairobi"
      },
      "start": {
        "dateTime": dateStart,
        "timeZone": "Africa/Nairobi"
      },
      "summary": args.source.data.module,
      //"description": args.source.data.html,
      "description":  `<h2>Opportunité:</h2>        
        <strong><i>${args.source.data.nameEtp}</i></strong>  vous invite à participer à la formation en   
        <strong>${args.source.data.module}</strong>,     
        <strong>Détails de la formation :</strong>  
        <strong>Statut :</strong> ${args.source.data.statut}, 
        <strong>Remarque :</strong> ${args.source.data.remarque} 
        <strong>Nombre d'apprenants :</strong> ${args.source.data.nb_appr? args.source.data.nb_appr: 0}  
       `,
      "colorId": "4",
      'location': "4HF2+H33, Tananarive",// ex:4HF2+H33, Tananarive

    }
  };
  console.log("DATA GOOGLE ==>", dataGoogle);
  return gapi.client.calendar.events.insert(dataGoogle)
    .then(function (response) {
      console.log("Response =>", response);
      const idCalendarOpportunity = response.result.id;

      console.log('ID_CALENDAR GOOGLE ==>', idCalendarOpportunity);
      sessionStorage.setItem('ID_CALENDAR_OPP', idCalendarOpportunity);

      dp.message("Created on your GOOGLE CALENDAR!!!", { cssClass: "shadow-2xl rounded-md " });
      updateIdCalendarOpportunity(idCalendarOpportunity, idOpportunity);
      const allEvents = dpAnnuaire.events.list;
      const selectedEvents = allEvents.filter(evnt => evnt.idOpportunity == idOpportunity);
      selectedEvents.forEach(e => {
        e.backColor = "rgba(29, 188, 220, 0.5)";
        e.idCalendar = idCalendarOpportunity;
        dpAnnuaire.events.update(e);
      })
      dp.update();
    },
      function (err) { console.error("Execute error CREATE...", err); });
}

function executeDeleteOpportunityOnGoogle(idCalendar, dp, idAgenda, args) {
  updateTokenClient(); //On actualise le TOKKEN du client(l'App n'accepte que l'access_token de oauth2 de google!)   
  const idOpportunity = args.source.data.idOpportunity;
  return gapi.client.calendar.events.delete({
    "calendarId": idAgenda,
    "eventId": idCalendar,//champ de la table Sessions dans la BD...
  })
    .then(function (response) {
      // Handle the results here (response.result has the parsed body).
      console.log("Response", response);
      dp.message("Event delete on GOOGLE ", { cssClass: "shadow-2xl rounded-md " });
      updateIdCalendarOpportunity(idCalendar, idOpportunity);
      const allEvents = dpAnnuaire.events.list;
      const selectedEvents = allEvents.filter(evnt => evnt.idOpportunity == idOpportunity);
      console.log('allEvents==>', allEvents);
      console.log('selectedEvents==>', selectedEvents);
      selectedEvents.forEach(e => {
        e.backColor = "rgba(244, 220, 176, 0.54)";
        dpAnnuaire.events.update(e);
      })
      dp.update();
    },
      function (err) { console.error("Execute error", err); });
}

/*************************************************** FONCTIONS API GOOGLE FORMATIONS *******************************************************************************/
function executeAddOnGoogle(dp, args, seance, object, idAgenda) {
  updateTokenClient(); //On actualise le TOKEN du client(l'App n'accepte que l'access_token de oauth2 de google!)
  const tabForm = [...object.forms];
  const dateStart = args.start.value;
  const dateEnd = args.end.value;
  const dataGoogle = {
    "calendarId": idAgenda,
    "resource": {
      "end": {
        "dateTime": dateEnd,
        "timeZone": "Africa/Nairobi"
      },
      "start": {
        "dateTime": dateStart,
        "timeZone": "Africa/Nairobi"
      },
      "summary": seance.module_name,
      "description": `<h2>Invitation à la Formation</h2>        
            <strong><i>${object.nameEtp}</i></strong> vous invite à participer à la formation en   
            <strong>${seance.module_name}</strong>, dispensée par <strong><i>${object.nameCfp}</i></strong>.      
            <strong>Détails de la formation :</strong>  
            <strong>Lieu :</strong> ${seance.li_name},<strong> salle:</strong> ${seance.salle_name} 
            <strong>Type de projet :</strong> ${object.typeProjet} 
            <strong>Nombre d'apprenants :</strong> ${object.apprenants}  
            <strong>Formateur(s) :</strong> ${tabForm.map(form => `${form.form_name} ${form.form_firstname}`).join(', ')}`,
      "colorId": "3",  // Couleur Raisin
      'location': displayVille(seance.ville) + `,${seance.ville} `,// ex:4HF2+H33, Tananarive
      // "attendees": [
      //   {
      //     "email":       'rktsoandry@gmail.com',
      //     "displayName": 'Andry'
      //   }
      // ],
      // "image":  
      //   {  
      //     "fileUrl": `https://formafusionmg.ams3.digitaloceanspaces.com/formafusionmg/img/modules/${object.imgModule}` ,  
      //     "title": seance.module_name,  
      //     "mimeType": "image/webp"  
      //   },
      'htmlLink': true,
      "attendees": tabForm.map(form => ({
        "email": form.email,
        "displayName": form.form_firstname
      }))
    }
  };

  console.log("DATA GOOGLE ==>", dataGoogle);

  return gapi.client.calendar.events.insert(dataGoogle)
    .then(function (response) {
      const idCalendar = response.result.id;
      sessionStorage.setItem('idCalendar', idCalendar);
      dp.message("Created on your GOOGLE CALENDAR!!!", { cssClass: "shadow-2xl rounded-md " });
      const filteredEvents = dp.events.list.filter(e => e.idSeance === seance.idSeance);
      if (filteredEvents.length > 0) {
        filteredEvents.forEach(event => {
          event.idCalendar = idCalendar; // Modifier la propriété idCalendar
          dp.events.update(event); // Appliquer la mise à jour dans DayPilot
        });
        dp.update(); // Rafraîchir l'affichage si nécessaire
        console.log("Mise à jour effectuée :", filteredEvents);
      } else {
        console.log("Aucun événement trouvé avec idCalendar =", idCalendar);
      }

      updateIdCalendarSession(idCalendar);

    },
      function (err) { console.error("Execute error CREATE...", err); });
}

function executeAddListOnGoogle(dp, seances, idAgenda) {
  updateTokenClient();

  // Vérification des données reçues
  if (!seances || !Array.isArray(seances) || seances.length === 0) {
    console.error('Aucune séance valide reçue:', seances);
    return;
  }
  console.log('Séances reçues:', seances);
  const addEventPromises = seances.map(evnt => {
    // Vérification des données requises pour chaque événement
    if (!evnt || !evnt.start || !evnt.end || !evnt.module_name) {
      console.error('Données invalides pour l\'événement:', evnt);
      return Promise.reject('Données invalides');
    }

    // Correction de l'accès aux propriétés nameEtp/nameEtps
    const entrepriseName = evnt.nameEtp
      ? evnt.nameEtp
      : (evnt.nameEtps && Array.isArray(evnt.nameEtps)
        ? evnt.nameEtps.map(etp => etp.name).join(' et ')
        : 'Non spécifié');

    // Récupérer l'idSeance pour l'utiliser plus tard
    const idSeance = evnt.idSeance;

    let dataGoogle = {
      'calendarId': idAgenda,
      "resource": {
        "end": {
          "dateTime": evnt.end,
          "timeZone": "Africa/Nairobi"
        },
        "start": {
          "dateTime": evnt.start,
          "timeZone": "Africa/Nairobi"
        },
        "summary": evnt.module_name,
        "description": `<h2>Invitation à la Formation</h2>
          <strong><i>${entrepriseName}</i></strong> vous invite(nt) à participer à la formation en
          <strong>${evnt.module_name}</strong>, dispensée par <strong><i>${evnt.nameCfp || '----'}</i></strong>.
          <strong>Détails de la formation :</strong>
          <strong>Lieu :</strong> ${evnt.ville || 'Non spécifié'},<strong> salle:</strong> ${evnt.salle || 'Non spécifiée'}
          <strong>Type de projet :</strong> ${evnt.typeProjet || 'Non spécifié'}
          <strong>Nombre d'apprenants :</strong> ${evnt.nb_appr || 0} 
          <strong>Formateur(s) :</strong> ${Array.isArray(evnt.formateurs) ? evnt.formateurs.map(form => `${form.prenom}`).join(', ') : 'Non spécifié'}`,
        "colorId": "3",
        'location': displayVille(evnt.ville) + `,${evnt.ville}`,
        'htmlLink': true,
        "attendees": Array.isArray(evnt.formateurs) ? evnt.formateurs.map(form => ({
          "email": form.email || '',
          "displayName": form.prenom || ''
        })) : []
      },
    }

    return gapi.client.calendar.events.insert(dataGoogle)
      .then(response => {
        console.log(`Événement ajouté: ${response.result.summary}, ID: ${response.result.id}`);
        // Retourner à la fois l'ID Google et l'ID de séance
        return {
          idGoogle: response.result.id,
          idSeance: idSeance
        };
      })
      .catch(error => {
        console.error("Erreur lors de l'ajout de l'événement:", error);
        throw error;
      });
  });

  return Promise.all(addEventPromises)
    .then(results => {
      console.log('Tous les événements ont été ajoutés avec les associations suivantes:', results);

      // Vous pouvez maintenant mettre à jour votre base de données avec ces associations
      results.forEach(result => {
        if (result && result.idGoogle && result.idSeance) {
          updateIdListCalendarSession(result.idSeance, result.idGoogle);
        }
      });

      dp.message("Événements créés sur GOOGLE CALENDAR!", { cssClass: "shadow-2xl rounded-md" });
      return results;
    })
    .catch(error => {
      console.error('Erreur lors de l\'ajout des événements:', error);
      dp.message("Erreur lors de la création des événements", { cssClass: "shadow-2xl rounded-md text-red-500" });
    });
}

function executeDeleteListOnGoogle( dp, idAgenda,seances) {
  updateTokenClient(); //On actualise le TOKKEN du client(l'App n'accepte que l'access_token de oauth2 de google!)
  seances.map(evnt =>{

  return gapi.client.calendar.events.delete({
    //"calendarId": "c_192015d161bd1c90744d5a85afed0d9b5d0d408a49c1835b02da95b9c03756a4@group.calendar.google.com",
    "calendarId": idAgenda,
    "eventId": evnt.idCalendar,//champ de la table Sessions dans la BD...
  })
    .then(function (response) {
      // Handle the results here (response.result has the parsed body).
      console.log("Response", response);
      dp.message("Events delete on GOOGLE ", { cssClass: "shadow-2xl rounded-md " });
    },
      function (err) { console.error("Execute error", err); });
  })     
}

const first = DayPilot.Date.today().firstDayOfWeek("en-us").addDays(1);


