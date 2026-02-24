document.addEventListener('DOMContentLoaded', (e) => {

  //dpDetail.init();

  // let listEvents = getAllSeancesDetailJson2();
  //   dpDetail.events.list = listEvents;
  //   dpDetail.update({listEvents});

  // appDetail.init();

  /************************************************************PREVIOUS-TODAY-NEXT***************************************************************************/
  const weekDisplay = document.getElementById('weekDisplay');
  const prevLink = document.getElementById('prevLink');
  const nextLink = document.getElementById('nextLink');
  const todayLink = document.getElementById('todayLink');

  let currentWeekNumber = calculateWeekNumber(new Date());

  updateWeekDisplay(currentWeekNumber);

  prevLink.addEventListener('click', function (e) {
    e.preventDefault(); // Empêcher le comportement par défaut du lien
    const monthDaypilot = dpDetail.startDate.value;
    const date = new Date(monthDaypilot);
    const month = date.getUTCMonth();
    document.getElementById('monthSelectorWeek').options[month].selected = true;

    currentWeekNumber -= 1;
    if (currentWeekNumber < 1) {
      currentWeekNumber = 52; // Rouler vers la fin de l'année
    }
    updateWeekDisplay(currentWeekNumber);
    dpDetail.startDate = dpDetail.startDate.addDays(-7);
    dpDetail.update();
  });

  nextLink.addEventListener('click', function (e) {
    e.preventDefault(); // Empêcher le comportement par défaut du lien
    const monthDaypilot = dpDetail.startDate.value;
    const date = new Date(monthDaypilot);
    const month = date.getUTCMonth();
    document.getElementById('monthSelectorWeek').options[month].selected = true;

    currentWeekNumber += 1;
    if (currentWeekNumber > 52) {
      currentWeekNumber = 1; // Rouler vers le début de l'année
    }
    updateWeekDisplay(currentWeekNumber);
    dpDetail.startDate = dpDetail.startDate.addDays(+7);
    dpDetail.update();

  });

  todayLink.addEventListener('click', function (e) {
    e.preventDefault(); // Empêcher le comportement par défaut du lien
    const monthDaypilot = DayPilot.Date.today().value;;
    const date = new Date(monthDaypilot);
    const month = date.getUTCMonth();
    document.getElementById('monthSelectorWeek').options[month].selected = true;

    currentWeekNumber = calculateWeekNumber(new Date());
    updateWeekDisplay(currentWeekNumber);
    dpDetail.startDate = DayPilot.Date.today();
    dpDetail.update();

  });

}, true);

//Choix du mois dans detail...
document.querySelectorAll("select").forEach(function(el){
  el.addEventListener("change",function(){
    const selectedMonth = parseInt(this.value);
    dpDetail.startDate = DayPilot.Date.today();
    dpDetail.startDate = dpDetail.startDate.addMonths(selectedMonth-1);
    dpDetail.update();
  })
}) 

/************************************************************FONCTIONS ***************************************************************************/
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

async function getIdCustomer() {
  let idCustomer;
  const url = "/home/customer";
  await fetch(url, { method: "GET" })
    .then((response) => response.json())
    .then((data) => {
      idCustomer = data.idCustomer;
    })
    .catch((error) => {
      console.error(error);
    });
  return idCustomer;
}

function getToken() {
  const metas = document.getElementsByTagName("meta");
  for (let i = 0; i < metas.length; i++) {
    const meta = metas[i];
    if (meta.name === "csrf-token") return meta.content;
  }
}

function updateTokenClient() {
  const TOKEN = document
    .querySelector('meta[name="csrf-token-google"]')
    .getAttribute("content"); //tiré de la page (layouts)master.blade.php
  gapi.client.setToken({
    //!!! access_token sera modifié à chaque connexion...
    access_token: TOKEN,
    authuser: "0",
    expires_in: 5055, //==>84 mn et 15s
    prompt: "consent",
    scope: SCOPES,
    token_type: "Bearer",
  });
}

function setStore(cleAccess, newVal) {
  sessionStorage.setItem(cleAccess, JSON.stringify(newVal));
}

function getStore(cleAccess) {
  const data = sessionStorage.getItem(cleAccess);
  return JSON.parse(data);
}

function confirmProject(args, dp) {
  const e = args.source;
  const idProjet = e.data.idProjet;
  const url = `/cfp/projets/${idProjet}/confirm`;
  const token = getToken();

  fetch(url, {
    method: 'PATCH', headers: {
      "Content-Type": "application/json",
      "X-CSRF-Token": token
    }
  })
    .then(response => {
      toastr.success("Le projet est validé avec succès", 'Succès', { timeOut: 1500 });

      console.log('Réponse VALIDER :', response.json());
      let idCustomer;
      if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
        idCustomer = sessionStorage.getItem('ID_CUSTOMER');
      }

    })

    //})
    .catch(error => {
      toastr.error("Erreur inconnue !", error, { timeOut: 1500 });
    });
  dp.update();

}

function deleteEvent(args, dp) {
  const e = args.source;
  const idSeance = e.data.idSeance;
  const url = `/cfp/seances/${idSeance}/delete`;
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
      console.log('ID_CUSTOMER-->', idCustomer);
      sessionStorage.setItem('UPDATED_STORAGE', true);

      const dataInStoreEventsDetails = getStore('ACCESS_EVENTS_DETAILS_' + idCustomer);

      //<===== Si utilisateur GOOGLE j'utilise idCalendar comme réference...
      const newDataInStoreEventsDetails = dataInStoreEventsDetails.filter(data => data.idSeance !== idSeance);            //<===== Si utilisateur GOOGLE j'utilise idCalendar comme réference...


      this.setStore('ACCESS_EVENTS_DETAILS_' + idCustomer, newDataInStoreEventsDetails);
    })

    //})
    .catch(error => {
      toastr.error("Erreur inconnue !", error, { timeOut: 1500 });
    });
  dp.update();
}

function setColorStatutRessource(statut) {

  let backColor = "";

  if (statut === 'Réservé') {
    backColor = "#33303D";                //Gris :Réservation
  } else if (statut === 'Annulé') {
    backColor = "#DE324C";                //Rouge Orangé: Annulé 
  } else if (statut === 'En préparation') {
    backColor = "#F8E16F";                //(JAUNE) : En préparation
  } else if (statut === 'Reporté') {
    backColor = "#2E705A";                //(vert foncé) : Reporté
  } else if (statut === 'Planifié') {
    backColor = "#CBABD1";                //(Bleu marine) : Planifié
  } else if (statut === 'En cours') {
    backColor = "#369ACC";                //#1E90FF <===(couleur BLEU )
  } else if (statut === 'Cloturé') {
    backColor = "#6F1926";                //#828282 <===(couleur gris )
  } else {
    backColor = "#95CF92";                //#32CD32 <===(couleur vert : Terminée)
  }

  return backColor;
}

function loadAllEventsDetail() {    //<======== Fonction permettant de charger les détails d'une séance si la sessionStorage existe

  const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
  // console.log('LOCAL_STORAGE DETAILS-->',sessionStorage.getItem('ACCESS_EVENTS_DETAILS_'+idCustomer) );
  const objectEvents = JSON.parse(
    sessionStorage.getItem("ACCESS_EVENTS_DETAILS_" + idCustomer)
  );
  //console.log('Objets Events DEtail-->NOTHING', objectEvents);

  //dpDetail.clearSelection();
  // console.log('after init...');

  dpDetail.events.list = objectEvents;
  dpDetail.update(objectEvents);

}

// function getAllSeancesDetailJson2() {
//   //<======== Fonction permettant de récuperer les "data" des details d'une seance en format JSON
//   let detailEvents = [];
//   let item = 0;

//   const url = `/cfp/agendas/getEvents`;
//   fetch(url, { method: "GET" }).then(response => response.json())
//     .then(data => {
//       let dataEvnts = (data.seances) ? data.seances : [];
//       //console.log('LISTE SEANCES LOADING...dataEvnts-->', dataEvnts);
//       let idCustomer;
//       if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
//         idCustomer = sessionStorage.getItem('ID_CUSTOMER');
//       }

//       for (let evnt of dataEvnts) {

//         HTMLDATA = `
//         <div class="flex items-start gap-2 bg-white p-4 w-full h-full">
//           <div class="flex flex-col">
//             <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
//               <div class="inline-flex items-center gap-2">
//                 <div class="flex flex-col ">                         
                 
//                     <p class="text-base  text-gray-700"> ${evnt.module || 'non assigné'}
//                     </p>

//                   <span class="flex flex-row flex-wrap items-center divide-x divide-gray-200 gap-2">

//                       <p class="text-sm text-gray-400"> <span
//                         class="text-base text-white px-2 rounded-md bg-[${this.setColorProjet(evnt.typeProjet)}] ">${evnt.typeProjet || 'non assigné'}</span>
//                       </p>               
                                  
//                       <p class="text-sm text-gray-400"> <span
//                           class="text-sm text-black rounded-md bg-white-100 ">${!(evnt.nameEtp) ? evnt.nameEtps.map(etp => etp.etp_name) : evnt.nameEtp}</span>
//                       </p>

//                       <p class="text-sm text-gray-500 rounded-md bg-yellow-100 ">${evnt.salle || 'non assigné'} </p>

//                   </span>

//                    <p class="text-base  text-gray-700"> <i class="fa-solid fa-user-graduate"></i> : ${evnt.formateurs.map(form => form.form_firstname)}
//                     </p>

//                 </div>
//               </div>
//             </div>
                     
//           </div>
//         </div>`;

//         let TEXTE = `\n\n REF: ${evnt.reference ?? '  -- '} \n\n - ${evnt.module} - \n\n ${evnt.codePostal}- ${evnt.ville} - ${evnt.nameEtp}

//         Formateur:
//           ${evnt.formateurs[0]?.form_firstname} , ${evnt.formateurs[1]?.form_firstname ?? ''}  
//         ` ;

//         detailEvents.push({
//           id: crypto.randomUUID(),
//           idEtp: evnt.idEtp,
//           start: evnt.start,
//           end: evnt.end,
//           idSeance: evnt.idSeance,
//           idModule: evnt.idModule,
//           idProjet: evnt.idProjet,
//           idSalle: evnt.idSalle,
//           idCalendar: evnt.idCalendar,
//           idCustomer: evnt.idCustomer,
//           html: HTMLDATA,
//           text: TEXTE,
//           //resource: evnt.resource,
//           //idFormateur: evnt.idFormateur
//           title: evnt.text,
//           module: evnt.module,
//           salle: evnt.salle,
//           ville: evnt.ville,
//           typeProjet: evnt.typeProjet,
//           prenom_form: evnt.formateurs.map(formateur => {
//             let idForm = formateur.idFormateur;
//             let prenom_form = formateur.form_firstname;
//             return {
//               idFormateur: idForm ? idForm : 0,
//               prenom: prenom_form ? prenom_form : 'vide',
//             };
//           }),
//           nameEtp: evnt.nameEtp,
//           nameEtps: evnt.nameEtps.map(etp => {
//             let nameEtp = etp.etp_name;
//             return {
//               name: nameEtp,
//             };
//           }),
//           materiels: evnt.materiels.map(mat => {
//             let nameMateriel = mat.prestation_name;
//             return { name: nameMateriel, };
//           }),
//           codePostal: evnt.codePostal,
//           paiement: evnt.paiementEtp,
//           statut: evnt.statut,
//           nb_appr: (evnt.typeProjet == 'Intra') ? evnt.apprCountIntra : evnt.apprCountInter,
//           imgModule: evnt.imgModule,
//           codePostal: evnt.codePostal,
//           reference: evnt.reference,
//           height: 20,
//           barColor: appAnnuel.setColorStatutRessource(evnt.statut),
//           backColor: appAnnuel.setColorStatutRessource(evnt.statut),
//           quartier: evnt.quartier,
//           modalite: evnt.modalite
//         })
//       }

//       sessionStorage.setItem('ACCESS_EVENTS_DETAILS_' + idCustomer, JSON.stringify(detailEvents))
//       //loadAllEventDetails();

//     }).catch((error) => {
//       console.error(error);
//     });

//   return detailEvents;

// }

function toggleWeekends() {
  var showWeekends = !dpDetail.showWeekend;

  // Mettre à jour la propriété showWeekend
  dpDetail.showWeekend = showWeekends;

  //

  // Mettre à jour l'affichage du calendrier
  dpDetail.update();
  alert('ok...');
}

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
    //"id_google" :   id,   
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

      let idCustomer;
      if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
        idCustomer = sessionStorage.getItem('ID_CUSTOMER');
      }
      sessionStorage.setItem('UPDATED_STORAGE', true);

      const dataInStoreEventsDetails = getStore('ACCESS_EVENTS_DETAILS_' + idCustomer);

      alert('HEURE A ETE MODIFIE!!!');


      if (dataInStoreEventsDetails) {

        /*------------------------- DETAIL--------------------------------------*/
        for (let i = 0; i < dataInStoreEventsDetails.length; i++) {
          if (dataInStoreEventsDetails[i].idSeance === idSeance) {
            dataInStoreEventsDetails[i].start = start;
            dataInStoreEventsDetails[i].end = end;
            break;
          }
        }

        setStore('ACCESS_EVENTS_DETAILS_' + idCustomer, dataInStoreEventsDetails);

      }

    })
    .catch(function (error) { console.log('RESIZE probleme!', error) });
}

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

      const dataInStoreEventsDetails = getStore('ACCESS_EVENTS_DETAILS_' + idCustomer);

      if (dataInStoreEventsDetails) {
        /*------------------------- DETAIL--------------------------------------*/
        for (let i = 0; i < dataInStoreEventsDetails.length; i++) {
          if (dataInStoreEventsDetails[i].idSeance === idSeance) {
            dataInStoreEventsDetails[i].start = start;
            dataInStoreEventsDetails[i].end = end;
            break;
          }
        }
        this.setStore('ACCESS_EVENTS_DETAILS_' + idCustomer, dataInStoreEventsDetails);
      }

    })
    .catch(function (error) { console.log('DEPL probleme!', error) });
}

function calculateWeekNumber(date) {
  date = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
  date.setUTCDate(date.getUTCDate() + 4 - (date.getUTCDay() || 7));
  var yearStart = new Date(Date.UTC(date.getUTCFullYear(), 0, 1));
  return Math.ceil((((date - yearStart) / 86400000) + 1) / 7);
}

/*****************************************************FIN FONCTION********************************************************************** */
const dpDetail = new DayPilot.Calendar("dpDetail", {
  locale: "fr-fr",
  startDate: DayPilot.Date.today(),
  viewType: "Week",
  headerDateFormat: "ddd d MMM yyyy",
  businessBeginsHour: 7,
  dayBeginsHour: 7,
  showEventStartEnd: false,
  scrollLabelsVisible: true,
  timeRangeSelectingStartEndEnabled: true,
  autoRefreshEnabled: true,
  eventArrangement: "SideBySide",
  cellWidth: "Auto",

  dynamicLoading: true,

  // autoRefreshInterval: 60 * 1000, // Rafraîchissement toutes les 60 secondes (1 minute)
  // columnWidth: 500,

  columnResizeHandling: "Update",

  onEventFilter: (args) => {

    if (args.filter.idFormateur && args.e.data && args.e.data.prenom_form && args.e.data.prenom_form.length > 0) {
      const idForm_filter = args.filter.idFormateur;
      const idFormateur_filter = args.e.data.prenom_form[0]?.idFormateur || 0;
      args.visible = idForm_filter === idFormateur_filter;
    }
  },

  onBeforeEventRender: (args) => {
    args.data.areas = [
      {
        right: 5,
        top: 5,
        width: 16,
        height: 16,
        icon: "fas fa-chevron-down",
        cssClass: "event-action",
        action: "ContextMenu",
      },
    ];
    args.data.cssClass = "shadow-lg rounded-md";
  },

  onHeaderClick: (args) => {

    const { column, header } = args;
    if (column.data.name === header.name) {
      if (dpDetail.viewType === "Week") {
        dpDetail.viewType = "Day";
        dpDetail.startDate = header.start.value;
        dpDetail.update();

      } else {
        dpDetail.viewType = 'Week';
        dpDetail.startDate = header.start.value;
        dpDetail.update();
      }
    }
  },

  onEventDelete: (args) => {
    if (!confirm("Voulez-vous vraiment supprimer cette séance?")) {
      args.preventDefault();
    }
  },

  onEventResize: (args) => {
    if (!confirm("Voulez-vous vraiment changer l'horaire de cette séance?")) {
      args.preventDefault();
    }
  },

  onEventMove: (args) => {
    if (!confirm("Voulez-vous vraiment déplacer cette séance?")) {
      args.preventDefault();
    }
  },

  onEventResized: (args) => {
    updateEventResized(args, dpDetail);
  },

  onEventMoved: (args) => {

    updateEventMoved(args);
  },

  contextMenu: new DayPilot.Menu({
  onShow: (args) => {
    const eventData = args.source.data;
    console.log('eventDataDETAILS==>',eventData);
   args.menu.items = [
      {
        //Dynamic...
        text: "Ouvrir ce projet",
        icon: "fa-solid fa-tarp",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          window.location.replace(`/cfp/projets/${idProjet}/detail`);
        },
      },
      {
        text: "Cacher",
        icon: "fa-solid fa-eye-slash",
        onClick: (args) => {
          dpDetail.message("L'Evenement est caché: ");
          dpDetail.update();
          dpDetail.events.remove(args.source);
          //dpDetail.message("...");
        },
      },
      {
        text: "Valider ce projet",
        icon: "fa-solid fa-folder",
        onClick: (args) => {

          confirmProject(args, dpDetail);

          dpDetail.message("Le projet est validé: ");
          dpDetail.update();
          dpDetail.events.remove(args.source);
          //dpDetail.message("...");
        },
      },
      {

        text: "Supprimer une session",
        icon: "fa-solid fa-trash-can",
        onClick: (args) => {
          if (!confirm('Etes-vous sur de supprimer cette session?')) {
            args.preventDefault();
            return
          }

          deleteEvent(args, dpDetail);

          dpDetail.message("Session supprimée: ");
          dpDetail.update();
          dpDetail.events.remove(args.source);
          //dpDetail.message("...");
        },
      },
      {

        text: "Supprimer toutes les sessions d'un projet",
        icon: "fa-solid fa-trash-can",
        onClick: (args) => {
          // dpDetail.message("Toutes les sessions sont supprimées: ");
          // dpDetail.update();
          // dpDetail.events.remove(args.source);
          //dpDetail.message("...");
        },
      },

      {
        text: "Accéder au lieu",
        icon: "fa-solid fa-location-dot",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          showLieuDeReperage(`/cfp/planreperage-drawer/${idProjet}`);
        }
      },

      {
        text: "Accéder au formation",
        icon: "fa-solid fa-puzzle-piece",
        onClick: (args) => {
          const e = args.source;
          const idModule = DayPilot.Util.escapeHtml(e.data.idModule);
          showFormation(idModule);
        }
      },

      {
        text: "Accéder aux sessions",
        icon: "fa-solid fa-list",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          showSessions(`/cfp/session-drawer/${idProjet}`,idProjet);
        }
      },

      {
        text: "Accéder au formateur",
        icon: "fa-solid fa-user-graduate",
        onClick: (args) => {
          const e = args.source;
          const idFormateur = DayPilot.Util.escapeHtml(e.data.prenom_form[0].idFormateur);
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          viewMiniCV(idFormateur,idProjet);

        }
      },

      {
        text: "Accéder aux participants",
        icon: "fa-solid fa-users",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          showApprenants(`/cfp/apprenant-drawer/${idProjet}`,idProjet);

        }
      },

      {
        text: "Accéder à l'entreprise",
        icon: "fa-solid fa-building",
        onClick: (args) => {
          const e = args.source;
          const idEtp = DayPilot.Util.escapeHtml(e.data.idEtp);
          showCustomer(idEtp, `/cfp/etp-drawer/`);
        }
      },

      {
        text: "Accéder au dossier",
        icon: "fa-sharp fa-solid fa-folders",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          showDossiers(`/cfp/dossier-drawer/${idProjet}`);

        }
      },
      {
        text: eventData.idCalendar ? "Formation liée à GOOGLE":"Formation non liée à GOOGLE",
        //icon: "fa-solid text-sm fa-thumbs-up text-green-500",
      },
      {
        text: !eventData.idCalendar ? "Ajouter les sessions à google Calendar" : "Enlever les sessions à google Calendar",
        icon: !eventData.idCalendar ? "fa-solid text-sm fa-thumbs-up text-green-500" : "fa-solid text-sm fa-thumbs-down text-red-500",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          const storedToken = localStorage.getItem('ACCESS_TOKEN');
          const storage_idAgenda = localStorage.getItem('ID_AGENDA');
       
          //Recupère tous les projets de mème ID...
          const allEvents = dpDetail.events.list;
          let selectedEvents = [];
          let unSelectedEvents = [];
          selectedEvents = allEvents.filter(evnt => evnt.idProjet == idProjet);
          unSelectedEvents = allEvents.filter(evnt => evnt.idProjet != idProjet);
          console.log('selectedEvents-->', selectedEvents);
          //console.log('unSelectedEvents-->', unSelectedEvents);

          if (selectedEvents.length > 0) {
            selectedEvents.forEach(e => {
              if (e.selected == false) {
                e.backColor = "rgba(254, 207, 1,0.5)";
                e.selected = true;
                dpDetail.events.update(e);
              }
          
            });
          }

          console.log('selectedEvents GOOGLE-->', selectedEvents);

          let newSelectedEvents = selectedEvents.map(evnt =>{
            return {
              idSeance: evnt.idSeance,
              idCalendar: evnt.idCalendar,
              start:evnt.start.value,
              end: evnt.end.value,
              formateurs:[...evnt.prenom_form],
              module_name: evnt.module,
              imgModule: evnt.imgModule,
              quartier: evnt.quartier,
              salle:  evnt.salle,
              typeProjet: evnt.typeProjet,
              ville: evnt.ville,
              nb_appr: evnt.nb_appr,
              nameEtp: evnt.nameEtp,
              nameEtps: evnt.nameEtps,
              nameCfp: evnt.nameCfp,              
 
            }
          })
          console.log('NEW selectedEvents==>',newSelectedEvents)
          if (selectedEvents.length > 0) {
            selectedEvents.forEach(e => {         
              if (e.selected == false) {
                e.backColor = "rgba(254, 207, 1,0.5)";
                dpDetail.events.update(e);
              }       
            });
          }
        if(eventData.idCalendar ){
          if (!storedToken) {
            handleAuthClick();
          }else{          
            console.log('NEWSELECT ==>',newSelectedEvents);
            executeDeleteListOnGoogle(dpDetail,storage_idAgenda,newSelectedEvents);
            newSelectedEvents.map(evnt =>{
              updateIdListCalendarSession(evnt.idSeance,null);
            })         
          }           
        }else{
          if (!storedToken) {
            handleAuthClick();
            }else{
            executeAddListOnGoogle(dpDetail,newSelectedEvents,storage_idAgenda);
          }
        }
      },
     }
    ]
  }
  }),

  bubble: new DayPilot.Bubble({
    onLoad: (args) => {
      const e = args.source;
      const module = DayPilot.Util.escapeHtml(e.data.module);
      const salle = DayPilot.Util.escapeHtml(e.data.salle);
      const ville = DayPilot.Util.escapeHtml(e.data.ville);
      const tabForm = [...e.data.prenom_form];
      const nameEtp = DayPilot.Util.escapeHtml(e.data.nameEtp);
      const modalite = DayPilot.Util.escapeHtml(e.data.modalite);
      const materiels = [...e.data.materiels];
      const tabNameEtp = [...e.data.nameEtps];
      const color = DayPilot.Util.escapeHtml(e.data.barColor);
      const statut = DayPilot.Util.escapeHtml(e.data.statut);
      const paiement = DayPilot.Util.escapeHtml(e.data.paiement);
      const nb_appr = DayPilot.Util.escapeHtml(e.data.nb_appr);
      const typeProjet = DayPilot.Util.escapeHtml(e.data.typeProjet);
      const colors = setColorProjet(typeProjet);
      const colorModalite = setModaliteColor(modalite);
      const img_module = DayPilot.Util.escapeHtml(e.data.imgModule);
      const start = e.start().toString("dd/MM/yyyy");
      const endTime = e.end().toString("h:mm tt");
      const startTime = e.start().toString("h:mm tt");
      const quartier = DayPilot.Util.escapeHtml(e.data.quartier);

      const colorStatus = appAnnuel.setColorStatutRessource(e.data.statut);

      const htd = ` <div class="flex items-center gap-2 h-full w-[28em] bg-white p-4">
              <div class="flex flex-col w-full">
                <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
                  <div class="inline-flex items-start gap-2">
                    <div class="w-16 h-16 rounded-md flex items-center justify-center p-2">
                        <img
                          src="https://formafusionmg.ams3.digitaloceanspaces.com/formafusionmg/img/modules/${img_module}"
                          alt="" class="w-full h-auto object-cover">
                    </div>
                    <div class="flex flex-col">
                      <p class="text-lg font-semibold text-gray-700">${module || "non assigné"
        }
                      </p>
                      <p class="text-sm text-gray-400">Client : <span
                          class="text-sm text-gray-500"> ${!nameEtp ? tabNameEtp.map(etp => etp.name) : nameEtp} </span> 
                      </p>

                      <p class="text-sm text-gray-400">Projet : <span
                          class="text-base text-white px-2 rounded-md bg-[${colors}] ">${typeProjet}</span>
                          <span
                          class="text-base text-white px-2 rounded-md bg-[${colorModalite}] ">${modalite}</span>
                      </p>

                      <div class="w-[76%] inline-flex items-center justify-center m-2">
                        <div class="px-4 py-1 text-sm bg-[${colorStatus}] text-white rounded-md">${statut}</div>
                      </div>

                    </div>              
                
                </div>
                <div class="flex flex-col w-full">
                  <div class="inline-flex items-start ">
                    <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-sm text-gray-400">Le :</p>
                    </div>

                      <div class="w-[66%] inline-flex items-center justify-start">
                        <p class="text-base text-gray-500">${start}</p>
                      </div>
                
                  </div>
                  <div class="inline-flex items-start gap-x-2">
                    <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-sm text-gray-400">De :</p>
                    </div>
                    <div class="w-[66%] inline-flex items-center justify-start">
                      <p class="text-base text-gray-500">${startTime} à ${endTime}</p>
                    </div>
                  </div>
                  <div class="inline-flex items-start gap-x-2">
                    <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-sm text-gray-400">Financé par :</p>
                    </div>
                    <div class="w-[66%] inline-flex items-center justify-start">
                      <p class="text-base text-gray-500">${paiement}</p>
                    </div>
                  </div>
                  <div class="inline-flex items-start gap-x-2">
                    <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-sm text-gray-400">Lieu :</p>
                    </div>
                    <div class="w-[66%] inline-flex items-center justify-start">
                      <p class="text-base text-grayS-500">${ville || "non assigné"
        }</p>
                    </div>
                  </div>

                  <div class="inline-flex items-start gap-x-2">
                    <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-sm text-gray-400">Salle :</p>
                    </div>
                    <div class="w-[66%] inline-flex items-center justify-start">
                      <p class="text-base text-gray-500">${salle || "non assigné"
        }</p>
                    </div>  
                  </div>


                  <div class="inline-flex items-start gap-x-2">
                    <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-sm text-gray-400">Quartier :</p>
                    </div>
                    <div class="w-[66%] inline-flex items-center justify-start">
                      <p class="text-base text-gray-500">${quartier !== null ? quartier : "--"
        }</p>
                    </div>  
                  </div>


                  <div class="inline-flex items-start gap-x-2">
                    <div class="w-[32%] inline-flex items-center justify-end">
                      <p class="text-base text-gray-400">Formateur(s) :</p>
                    </div>
                    <div class="w-[66%] inline-flex items-center justify-start">
                      <p class="text-base text-gray-500">${tabForm.map(f => f.prenom) || "non assigné"
        }</p>
                    </div>
                  </div>
                  <div class="inline-flex items-start gap-x-2">
                    <div class="w-[32%] inline-flex items-center justify-end">
                     <p class="text-base text-gray-400">Matériel(s) :</p>
                    </div>
                    <div class="w-[66%] inline-flex items-center justify-start">
                      <p class="text-base text-gray-500">${materiels.map(m => m.name) || 'non assigné'}</p>
                    </div>
                  </div>
                  <div class="inline-flex items-start gap-x-2">
                    <div class="w-[32%] inline-flex items-center justify-end">
                      <p class="text-base text-gray-400">Apprenant(s) :</p>
                    </div>
                    <div class="w-[66%] inline-flex items-center justify-start">
                      <p class="text-base text-gray-500">${nb_appr || "non assigné"
        }   </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>`;
      args.html = htd;
    },
  }),
});

const appDetail = {

  init() {
    this.addEventInputHandlers();
  },


  addEventInputHandlers() {

    const dropdownFormWeek = $('#dropdownFormWeek');

    dropdownFormWeek.ready(function () {
      document.querySelectorAll('li').forEach(function (el) {
        el.addEventListener("click", function (ev) {
          const idForm = ev.target.id
          const newId = idForm.split('_')[0];
          const idFormateur = Number(newId);
          const prenom = ev.target.innerText;

          dpDetail.events.filter({
            idFormateur: idFormateur
          })
        })
      })
    });
  }
}

const appElements = {
  elements: {
    clearD: document.querySelector("#clearDetail"),
    tousD: document.querySelector("#tousDetail"),
    filterDetailDisplay: document.querySelector("#formDetailDisplay"),
    subdropForm: document.querySelector("#subdropFormWeek")
  },

  init() {
    this.elements.clearD.addEventListener("click",handleClearAction);
    this.elements.tousD.addEventListener("click", handleClearAction);
  },
}

function updateWeekDisplay(currentWeekNumber) {
  const date = new Date();
  date.setWeek(currentWeekNumber);
  weekDisplay.textContent = `S ${currentWeekNumber} `;
}


// Ajoute la méthode setWeek à la classe Date
Date.prototype.setWeek = function (week) {
  const start = new Date(this.getFullYear(), 0, 4);
  const dayOfWeek = (start.getDay() + 7 - start.getDate() % 7) % 7;
  const timeDifference = week * 7 * 24 * 60 * 60 * 1000;
  const ms = 24 * 60 * 60 * 1000;
  this.setTime(start.getTime() + ((dayOfWeek + week * 7 - start.getDay() + timeDifference) % ms));
};

$(document).ready(function () {

  $("#export-button-week").click(function (ev) {
    ev.preventDefault();
    //var area = $("#area").val();
    console.log('Clic EXport WEEK');
    dpDetail.exportAs("png",
      {
        area: 'viewport',
        quality: 0.95,
        scale: 0.7,

      }).print();


  });

});

appElements.init();