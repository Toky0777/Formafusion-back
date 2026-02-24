$(document).ready(function () {
  // getListSalle();
});

//let NOM_MODULE;
let SALLES;

async function getLastFieldVueSeance() {
  const urlAxios = `/seancesEmp/getLastFieldVueSeances`;
  const { data } = await axios.get(urlAxios);
  const seance = data.seance;
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
  const url = '/employe/salles/list';
  await fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => { SALLES = data.salles })
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
  } else {
    backColor = "#32CD32";                //#32CD32 <===(couleur vert : Terminée)
  }

  return backColor;
}

async function getFirstNameModules() {
  const url = '/employe/modules/getAllModules';
  const response = await axios.get(url);
  NOM_MODULE = response.data.modules[2].name;
  return NOM_MODULE;
}

function getToken() {
  const metas = document.getElementsByTagName("meta");
  for (let i = 0; i < metas.length; i++) {
    const meta = metas[i];
    if (meta.name === "csrf-token") return meta.content;
  }
}


async function getIdNamePaiementStatusEtp(idProjet) //<======= Une Seule fonction pour récuperer les "data"
{
  let donnee;
  const url = `/projetsEmp/${idProjet}/detailEmp`;
  await fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      donnee = {
        idEtp: data.project.idEtp,
        nameEtp: data.project.etp_name,
        paiementEtp: data.project.paiement,
        statut: data.project.project_status,
        imgModule: data.project.module_image,   //<===============modifier...
        forms: [...data.forms],
        apprenants: data.apprenants.length
      }
    })
    .catch(error => { console.error(error) });
  console.log('Liste des donnees FORMS-->', donnee) //<===== objet                             
  return donnee;
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
  // for(let holiday of holidays)
  // {dp.events.add(holiday);}
  dp.events.list = holidays;
  return dp;
}

function getAllSeances(dp)            //<======= liste des séances avec affichage sur le Daypilot
{
  const idProjet = document.getElementById('project_id_hidden').value;

  let dataEvnts;

  const url = `/employe/seancesEmp/${idProjet}/getAllSeances`;
  fetch(url, { method: "GET" })
    .then(response => response.json())
    .then(data => {
      dataEvnts = data.seances ? data.seances : null;
      SEANCES = data.seances;
      console.log('DATA-->', data)
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
            // idCalendar: evnt.idCalendar,
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

function updateEventMoved(args) {
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
    "id_google_seance": idCalendar
  };
  const url = `/employe/seance/${idSeance}/update`;
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

      const dataInStoreEventsResources = this.getStore('ACCESS_EVENTS_RESOURCE_' + idCustomer);
      const dataInStoreEventsDetails = this.getStore('ACCESS_EVENTS_DETAILS_' + idCustomer);

      if (dataInStoreEventsDetails && dataInStoreEventsResources) {
        /*------------------------- DETAIL--------------------------------------*/
        for (let i = 0; i < dataInStoreEventsDetails.length; i++) {
          if (dataInStoreEventsDetails[i].idSeance === idSeance) {
            dataInStoreEventsDetails[i].start = start;
            dataInStoreEventsDetails[i].end = end;
            break;
          }
        }
        /*--------------------------RESOURCE--------------------------------------*/
        for (let i = 0; i < dataInStoreEventsResources.length; i++) {
          if (dataInStoreEventsResources[i].idSeance === idSeance) {
            dataInStoreEventsResources[i].start = start;
            dataInStoreEventsResources[i].end = end;
          }
        }
        this.setStore('ACCESS_EVENTS_DETAILS_' + idCustomer, dataInStoreEventsDetails);
        this.setStore('ACCESS_EVENTS_RESOURCE_' + idCustomer, dataInStoreEventsResources);
      }
      // })

    })
    .catch(function (error) {
      console.log('Erreur inconnue !');
    });
}

function deleteEvent(args) {
  const e = args.e;
  const idSeance = e.data.idSeance;
  const url = `/employe/${idSeance}/delete`;
  const token = getToken();
  const idCalendar = e.data.idCalendar;

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
      const dataInStoreEventsResources = this.getStore('ACCESS_EVENTS_RESOURCE_' + idCustomer);
      const dataInStoreEventsDetails = this.getStore('ACCESS_EVENTS_DETAILS_' + idCustomer);

      const newDataInStoreEventsResources = dataInStoreEventsResources.filter(data => data.idSeance !== idSeance);        //<===== Si utilisateur GOOGLE j'utilise idCalendar comme réference...
      const newDataInStoreEventsDetails = dataInStoreEventsDetails.filter(data => data.idSeance !== idSeance);            //<===== Si utilisateur GOOGLE j'utilise idCalendar comme réference...

      this.setStore('ACCESS_EVENTS_RESOURCE_' + idCustomer, newDataInStoreEventsResources);
      this.setStore('ACCESS_EVENTS_DETAILS_' + idCustomer, newDataInStoreEventsDetails);
    })

    //})
    .catch(error => {
      toastr.error("Erreur inconnue !", 'Erreur', { timeOut: 1500 });
    });
  dp.update();
}

function updateEventResized(args) {
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
    //"id_google" :   id,   
  };
  const url = `/employe/seance/${idSeance}/update`;
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

      const dataInStoreEventsResources = this.getStore('ACCESS_EVENTS_RESOURCE_' + idCustomer);
      const dataInStoreEventsDetails = this.getStore('ACCESS_EVENTS_DETAILS_' + idCustomer);

      alert('HEURE A ETE MODIFIE!!!');


      if (dataInStoreEventsDetails && dataInStoreEventsResources) {

        /*------------------------- DETAIL--------------------------------------*/
        for (let i = 0; i < dataInStoreEventsDetails.length; i++) {
          if (dataInStoreEventsDetails[i].idSeance === idSeance) {
            dataInStoreEventsDetails[i].start = start;
            dataInStoreEventsDetails[i].end = end;
            break;
          }
        }
        /*--------------------------RESOURCE--------------------------------------*/
        for (let i = 0; i < dataInStoreEventsResources.length; i++) {
          if (dataInStoreEventsResources[i].idSeance === idSeance) {
            dataInStoreEventsResources[i].start = start;
            dataInStoreEventsResources[i].end = end;
          }
        }
        this.setStore('ACCESS_EVENTS_DETAILS_' + idCustomer, dataInStoreEventsDetails);
        this.setStore('ACCESS_EVENTS_RESOURCE_' + idCustomer, dataInStoreEventsResources);
      }
      //  })

    })
    .catch(function (error) { console.log('RESIZE probleme!', error) });
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
        args.cell.disabled = true; // Désactiver la cellule
        /*args.cell.properties.areas = [
          {
            left: 3,
            width: 15,
            bottom: 5,
            height: 20,
            fontColor: "#f1c232",
            html: "&#10006;",
          },
          {
            left: 0,
            right: 0,
            bottom: 5,
            height: 20,
            text: item.name,
            horizontalAlignment: "center"
          },
          {
            left: 0,
            right: 0,
            bottom: 0,
            height: 5,
            backColor: "#f1c232"
          },
        ];*/
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
    /********************************************AJOUT D'UN EVENNEMENT**************************************************************** */
    onTimeRangeSelected: async (args) => {
      //const formModal = [
      // {
      //   type: 'title',
      //   name: 'Ajouter une nouvelle séance:',
      // },

      // {
      //   type: 'select',
      //   id: 'salle',
      //   name: 'Salle',
      //   options: SALLES,
      // }
      //];

      const dateStart = args.start.value;
      const dateEnd = args.end.value;
      const title = getFirstNameModules().NOM_MODULE;

      const salle = [];
      const entreprise = [];
      const module = [];
      const formateur = [];
      const detailEvent = [];
      const newIdCalendar = '';// <===== à Récuperer si c'est un google user...

      const seanceData = {
        'start': dateStart,
        'end': dateEnd,
        'title': title,
        'formateur': 'Inconnu'
      }

      //await DayPilot.Modal.form(formModal).then(function (modal) {
      if (modal.canceled) {
        args.preventDefault();
        dp.clearSelection();
        return;
      }

      const idProjet = document.getElementById('project_id_hidden').value;
      console.log('ID PROJET==>', idProjet)
      //Ajout à la BD
      // const url = '/cfp/seances'
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
        console.log('DATA==>', data)
        console.log('TOKEN==>', token)
        console.log('res==>', response)
        /*************************************** POUR LES RESOURCES ******************************************************************/
        getLastFieldVueSeance().then(seance => {
          getIdNamePaiementStatusEtp(seance.idProjet).then(object => {
            console.log('Last object-->', object);
            const tabForm = [...object.forms]
            const start = seance.dateSeance + 'T' + seance.heureDebut;
            const end = seance.dateSeance + 'T' + seance.heureFin;
            salle.push({
              start: start,
              end: end,
              id: DayPilot.guid(),
              idSeance: seance.idSeance,
              resource: 'SL_' + seance.idSalle,
              height: 20,
              idProjet: seance.idProjet,
              idCalendar: newIdCalendar,
              module: seance.module_name,
              salle: seance.salle_name,
              ville: seance.ville,
              typeProjet: seance.typeProjet,
              prenom_form: tabForm.map(formateur => formateur.form_firstname),
              name_etp: object.nameEtp,
              paiement: object.paiementEtp,
              imgModule: object.imgModule,  //<========================== modifié...
              barColor: setColorStatutRessource(object.statut),
              backColor: setColorStatutRessource(object.statut),
            })
            // Ajout sur les lignes clients (Entreprise) ...   
            entreprise.push({
              start: start,
              end: end,
              id: DayPilot.guid(),
              idSeance: seance.idSeance,
              resource: 'ETP_' + object.idEtp,      //<== à modifier (Controller:seanceController...)
              height: 20,
              idProjet: seance.idProjet,
              idCalendar: newIdCalendar,
              module: seance.module_name,
              salle: seance.salle_name,
              ville: seance.ville,
              typeProjet: seance.typeProjet,
              prenom_form: tabForm.map(formateur => formateur.form_firstname),
              name_etp: object.nameEtp,
              paiement: object.paiementEtp,
              imgModule: object.imgModule,  //<========================== modifié...
              barColor: setColorStatutRessource(object.statut),
              backColor: setColorStatutRessource(object.statut),
            })
            // Ajout sur les lignes modules ...      
            module.push({
              start: start,
              end: end,
              id: DayPilot.guid(),
              idSeance: seance.idSeance,
              resource: 'MD_' + seance.idModule,
              height: 20,
              idProjet: seance.idProjet,
              idCalendar: newIdCalendar,
              module: seance.module_name,
              salle: seance.salle_name,
              ville: seance.ville,
              typeProjet: seance.typeProjet,
              prenom_form: tabForm.map(formateur => formateur.form_firstname),
              name_etp: object.nameEtp,
              paiement: object.paiementEtp,
              imgModule: object.imgModule,  //<========================== modifié...
              barColor: setColorStatutRessource(object.statut),
              backColor: setColorStatutRessource(object.statut),
            })
            // Ajout sur les lignes formateurs ...                                       
            formateur.push({
              start: start,
              end: end,
              id: DayPilot.guid(),
              idSeance: seance.idSeance,
              resource: 'FM_' + seance.idFormateur,
              height: 20,
              idProjet: seance.idProjet,
              idCalendar: newIdCalendar,
              module: seance.module_name,
              salle: seance.salle_name,
              ville: seance.ville,
              typeProjet: seance.typeProjet,
              prenom_form: tabForm.map(formateur => formateur.form_firstname),
              name_etp: object.nameEtp,
              paiement: object.paiementEtp,
              imgModule: object.imgModule,  //<========================== modifié...
              barColor: setColorStatutRessource(object.statut),
              backColor: setColorStatutRessource(object.statut),
            })

            const oneEvent = [...salle, ...entreprise, ...module, ...formateur];
            let newDataInStoreEventsResources = [];
            //on récupère le dernier sessionStorage
            //getIdCustomer().then(idCustomer => {
            let idCustomer;
            if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
              idCustomer = sessionStorage.getItem('ID_CUSTOMER');
            }
            sessionStorage.setItem('UPDATED_STORAGE', true);
            const lastDataInStoreEventsResources = this.getStore('ACCESS_EVENTS_RESOURCE_' + idCustomer);
            if (lastDataInStoreEventsResources == null) {
              newDataInStoreEventsResources = [];
            } else {
              newDataInStoreEventsResources = [...lastDataInStoreEventsResources, ...oneEvent];
            }
            this.setStore('ACCESS_EVENTS_RESOURCE_' + idCustomer, newDataInStoreEventsResources);

            // })
          })
        });

        /*************************************** POUR LES DETAILS ******************************************************************/
        getLastFieldVueSeance().then(seance => {
          getIdNamePaiementStatusEtp(seance.idProjet).then(object => {
            const start = seance.dateSeance + 'T' + seance.heureDebut;
            const end = seance.dateSeance + 'T' + seance.heureFin;
            const tabForm = [...object.forms];
            const HTMLDATA = `<div class="flex items-start gap-2 bg-white p-4 w-full h-full">
                                    <div class="flex flex-col">
                                      <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
                                        <div class="inline-flex items-center gap-2">
                                          <div class="flex flex-col">
                                            <p class="text-lg font-semibold text-gray-700"> ${seance.module_name || 'non assigné'}
                                            </p>
                                            <p class="text-sm text-gray-400">Client : <span
                                                class="text-sm text-gray-500">${object.nameEtp || 'non assigné'}</span>
                                            </p>
                                            <p class="text-sm text-gray-400">Projet : <span
                                                class="text-sm text-gray-500">${seance.typeProjet || 'non assigné'},</span>
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
              start: start,
              end: end,
              idSeance: seance.idSeance,        //<===============
              idProjet: seance.idProjet,
              idCalendar: newIdCalendar,
              html: HTMLDATA,
              //  resource: evnt.resource,
              title: seance.project_title,
              module: seance.module_name,
              salle: seance.salle_name,
              ville: seance.ville,
              typeProjet: seance.typeProjet,
              prenom_form: tabForm.map(formateur => formateur.form_firstname),
              name_etp: object.nameEtp,
              paiement: object.paiementEtp,
              statut: object.statut,
              nb_appr: object.apprenants,
              imgModule: object.imgModule,  //<========================== modifié...
              height: 20,
              barColor: setColorStatutRessource(object.statut),
            })

            dp.events.add({
              start: start,
              end: end,
              idSeance: seance.idSeance,        //<===============
              idProjet: seance.idProjet,
              idCalendar: newIdCalendar,
              html: HTMLDATA,
              //  resource: evnt.resource,
              title: seance.project_title,
              module: seance.module_name,
              salle: seance.salle_name,
              ville: seance.ville,
              typeProjet: seance.typeProjet,
              prenom_form: tabForm.map(formateur => formateur.form_firstname),
              name_etp: object.nameEtp,
              paiement: object.paiementEtp,
              statut: object.statut,
              nb_appr: object.apprenants,
              imgModule: object.imgModule,  //<========================== modifié...
              height: 20,
              barColor: setColorStatutRessource(object.statut),
            });

            //on récupère le dernier sessionStorage
            //getIdCustomer().then(idCustomer => {
            let idCustomer;
            if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
              idCustomer = sessionStorage.getItem('ID_CUSTOMER');
            }
            sessionStorage.setItem('UPDATED_STORAGE', true);
            const lastDataInStoreEventDetails = this.getStore('ACCESS_EVENTS_DETAILS_' + idCustomer);
            //console.log('Tokony tab DETAILS-->', lastDataInStoreEventDetails);
            const newDataInStoreEventsDetails = [...lastDataInStoreEventDetails, ...detailEvent];
            this.setStore('ACCESS_EVENTS_DETAILS_' + idCustomer, newDataInStoreEventsDetails);

            // })
          })
        });

        toastr.success("Session ajoutée avec succès", 'Succès', { timeOut: 1500 });
        dp.update();

      });
      let idCustomer;
      if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
        idCustomer = sessionStorage.getItem('ID_CUSTOMER');
      }
      //getIdCustomer().then(idCustomer => {
      // console.log('idCustomer-->', idCustomer);
      sessionStorage.setItem('UPDATED_STORAGE', true);

      // })

      // }).catch(function (error) {console.log(error) });
    },

    onEventMoved: (args) => {
      updateEventMoved(args);
    },
    onEventDelete: (args) => {
      if (!confirm("Voulez-vous vraiment supprimer cette séance?")) {
        args.preventDefault();
      }
    },
    onEventDeleted: (args) => {
      deleteEvent(args);
    },
    onEventResized: (args) => {
      updateEventResized(args);
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

}

function today(dp) {
  dp.startDate = DayPilot.Date.today();
  dp.update();
}

function yesterday(dp) {
  dp.startDate = dp.startDate.addDays(-7);
  dp.update();
}

function tomorrow(dp) {
  dp.startDate = dp.startDate.addDays(7);
  dp.update();
}

const first = DayPilot.Date.today().firstDayOfWeek("en-us").addDays(1);