document.addEventListener('DOMContentLoaded', (e) => {

  // dpDetail.init();
  // let listEvents = getAllSeancesDetailJson2();
  // dpDetail.events.list = listEvents;
  // dpDetail.update({listEvents});

  /************************************************************PREVIOUS-TODAY-NEXT***************************************************************************/
  const weekDisplay = document.getElementById('weekDisplay');
  const prevLink = document.getElementById('prevLink');
  const nextLink = document.getElementById('nextLink');
  const todayLink = document.getElementById('todayLink');

  let currentWeekNumber = calculateWeekNumber(new Date());

  updateWeekDisplay(currentWeekNumber);

  prevLink.addEventListener('click', function (e) {
    e.preventDefault(); // Empêcher le comportement par défaut du lien
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
    currentWeekNumber = calculateWeekNumber(new Date());
    updateWeekDisplay(currentWeekNumber);
    dpDetail.startDate = DayPilot.Date.today();
    dpDetail.update();

  });

}, true);
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
  const url = "/homeEtp/customer";
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

function loadAllEventsDetail() {    //<======== Fonction permettant de charger les détails d'une séance si la sessionStorage existe

  const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
  // console.log('LOCAL_STORAGE DETAILS-->',sessionStorage.getItem('ACCESS_EVENTS_DETAILS_'+idCustomer) );
  const objectEvents = JSON.parse(
    sessionStorage.getItem("ACCESS_EVENTS_DETAILS_" + idCustomer)
  );
  console.log('Objets Events DEtail-->NOTHING', objectEvents);

  dpDetail.clearSelection();
  console.log('after init...');

  dpDetail.events.list = objectEvents;
  dpDetail.update(objectEvents);

}

function getNbAppr(typeProjet,evnt){

  switch(typeProjet) {
    case 'Intra':
      return evnt.apprCountIntra;
      break;
    
    case 'Inter':
      return evnt.apprCountInter;
      break;
      
    case 'Interne': 
      return evnt.apprCountInterne;
  }


}

function getAllSeancesDetailJson2() {
  //<======== Fonction permettant de récuperer les "data" des details d'une seance en format JSON
  const detailEvents = [];
  const item = 0;

  const url = `/etp/agendas/getEvents`;
  fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      const dataEvnts = (data.seances) ? data.seances : [];
      console.log('LISTE SEANCES LOADING...dataEvnts-->', dataEvnts);
      let idCustomer;
      if (sessionStorage.getItem('IDCUSTOMER_ETP') !== null) {
        idCustomer = sessionStorage.getItem('IDCUSTOMER_ETP');
      }
      for (const evnt of dataEvnts) {
        //console.log(evnt);
        HTMLDATA = `
        <div class="flex items-start gap-2 bg-white p-4 w-full h-full">
          <div class="flex flex-col">
            <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
              <div class="inline-flex items-center gap-2">
                <div class="flex flex-col ">                         
                 
                    <p class="text-base  text-gray-700"> ${evnt.module || 'non assigné'}
                    </p>

                  <span class="flex flex-row flex-wrap items-center divide-x divide-gray-200 gap-2">

                      <p class="text-sm text-gray-400"> <span
                        class="text-base text-white px-2 rounded-md bg-[${this.setColorProjet(evnt.typeProjet)}] ">${evnt.typeProjet || 'non assigné'}</span>
                      </p>               
                                  
                      <p class="text-sm text-gray-400"> <span
                          class="text-sm text-black rounded-md bg-white-100 ">${(evnt.typeProjet == 'Interne') ? evnt.nameEtp : evnt.nameCfp[0].etp_name}</span>
                      </p>

                      <p class="text-sm text-gray-500 rounded-md bg-yellow-100 ">${evnt.salle || 'non assigné'} </p>

                  </span>

                    <p class="text-base  text-gray-700"> <i class="fa-solid fa-user-graduate"></i> : ${(evnt.typeProjet == 'Interne') ? evnt.formateur_internes.map(form => form.form_firstname):evnt.formateurs.map(form => form.form_firstname)}
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
          idEtp: evnt.idEtp,
          start: evnt.start,
          end: evnt.end,
          idSeance: evnt.idSeance,
          idModule: evnt.idModule,
          idProjet: evnt.idProjet,
          idSalle: evnt.idSalle,
          idCalendar: evnt.idCalendar,
          idCustomer: evnt.idCustomer,
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
              idFormateur: idForm,
              prenom: prenom_form,
            };
          }),
          prenom_form_interne: evnt.formateur_internes.map(formateur => {
            const idForm = formateur.idFormateur;
            const prenom_form = formateur.form_firstname;
            return {
              idFormateur: idForm,
              prenom: prenom_form,
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
          nb_appr: getNbAppr(evnt.typeProjet,evnt),
          imgModule: evnt.imgModule,
          nameCfp: evnt.nameCfp.map(e => e.etp_name),
          codePostal: evnt.codePostal,
          reference: evnt.reference,
          barColor: appAnnuel.setColorStatutRessource(evnt.statut),
          backColor: appAnnuel.setColorStatutRessource(evnt.statut),
          quartier: evnt.quartier,
          modalite: evnt.modalite
        })
      }

      sessionStorage.setItem('ACCESS_EVENTS_DETAILS_' + idCustomer, JSON.stringify(detailEvents))
      //loadAllEventDetails();

    }).catch((error) => {
      console.error(error);
    });

  console.log("Liste detailEvents-->", detailEvents);
  console.log("Valeur de item-->", item);

  return detailEvents;

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

  //autoRefreshInterval: 60 * 1000, // Rafraîchissement toutes les 60 secondes (1 minute)
  // columnWidth: 500,

  columnResizeHandling: "Update",

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

  onEventFilter: (args) => {

    if (args.filter.idFormateur && args.e.data && args.e.data.prenom_form && args.e.data.prenom_form.length > 0) {
      const idForm_filter = args.filter.idFormateur;
      const idFormateur_filter = args.e.data.prenom_form[0]?.idFormateur || 0;
      args.visible = idForm_filter === idFormateur_filter;
    }
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
    items: [
      {
        //Dynamic...
        text: "Ouvrir ce projet",
        icon: "fa-solid fa-tarp",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          window.location.replace(`/etp/projets/${idProjet}/detail`);
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
          showLieuDeReperage(`/etp/planreperage-drawer/${idProjet}`);
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
          showSessions(`/etp/session-drawer/${idProjet}`,idProjet);
        }
      },

      {
        text: "Accéder au formateur",
        icon: "fa-solid fa-user-graduate",
        onClick: (args) => {
          const e = args.source;
          console.log(e);
          const idFormateur = (e.data.prenom_form.length>0)?  DayPilot.Util.escapeHtml(e.data.prenom_form[0].idFormateur): [];
          const idFormInterne = (e.data.prenom_form_interne.length>0)? DayPilot.Util.escapeHtml(e.data.prenom_form_interne[0].idFormateur):[];
          const typeProjet = DayPilot.Util.escapeHtml(e.data.typeProjet);
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          (typeProjet == 'Interne')? viewMiniCV(idFormInterne,idProjet) : viewMiniCV(idFormateur,idProjet);

        }
      },

      {
        text: "Accéder aux participants",
        icon: "fa-solid fa-users",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          showApprenants(`/etp/apprenant-drawer/${idProjet}`,idProjet);

        }
      },

      // {
      //   text: "Accéder à l'entreprise",
      //   icon: "fa-solid fa-building",
      //   onClick: (args) => {
      //     const e = args.source;
      //     const idEtp = DayPilot.Util.escapeHtml(e.data.idEtp);
      //     showCustomer(idEtp, `/cfp/etp-drawer/`);
      //   }
      // },

      {
        text: "Accéder au dossier",
        icon: "fa-sharp fa-solid fa-folders",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          showDossiers(`/etp/dossier-drawer/${idProjet}`);

        }
      },

    ],
  }),



  bubble: new DayPilot.Bubble({
    onLoad: (args) => {
      const e = args.source;
      const module = DayPilot.Util.escapeHtml(e.data.module);
      const salle = DayPilot.Util.escapeHtml(e.data.salle);
      const ville = DayPilot.Util.escapeHtml(e.data.ville);
      const tabForm = [...e.data.prenom_form];
      const tabFormInterne = [...e.data.prenom_form_interne];
      const modalite = DayPilot.Util.escapeHtml(e.data.modalite);
      const nameEtp = DayPilot.Util.escapeHtml(e.data.nameCfp);
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
                                src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/modules/${img_module}"
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

                      <div class="w-[66%] inline-flex items-center justify-center m-2">
                        <div class="px-4 py-1 text-sm bg-[${colorStatus}] text-white rounded-md">${statut}</div>
                      </div>

                    </div>   

                  </div>
                  <div class="flex flex-col w-full">
                    <div class="inline-flex items-start gap-x-2">
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
                        <p class="text-base text-gray-500">${(typeProjet=='Interne')? tabFormInterne.map(f => f.prenom) : tabForm.map(f => f.prenom)
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
                        <p class="text-base text-gray-400">Apprenants :</p>
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

          const idFormateur = Number(ev.target.id);
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
    filterDetailDisplay: document.querySelector("#formDetailDisplay"),
    subdropForm: document.querySelector("#subdropFormWeek")

  },

  init() {

    this.elements.clearD.addEventListener("click", function (ev) {
      ev.preventDefault();
      const date = new Date();
      const year = date.getFullYear();

      const weekNumber = calculateWeekNumber(date);

      console.log("WEEK-->", weekNumber);
      console.log("Date-->", year);

      appWidth.elements.filter.value = "";

      dpAnnuaire.events.filter(null);
      dpDetail.events.filter(null);
      dpFormateurs.events.filter(null);
      // dpCours.events.filter(null);

      // dpClients.events.filter(null);

      // appWidth.elements.subdropForm.textContent = "Filtrer par ...";
      // appElements.elements.subdropForm.textContent = "Filtrer par ...";
      // appFormElements.elements.subdropForm.textContent = "Filtrer par ...";    
      // appCoursElements.elements.subdropForm.textContent = "Filtrer par ...";
      //  appClientElements.elements.subdropForm.textContent = "Filtrer par ...";

      appWidth.elements.subdropForm.innerHTML = '<i class="fas fa-filter"><span class="text-sx"> par formateur</span></i>';
      appElements.elements.subdropForm.innerHTML = '<i class="fas fa-filter"><span class="text-sx"> par formateur</span></i>';
      appFormElements.elements.subdropForm.innerHTML = '<i class="fas fa-filter"><span class="text-sx"> par formateur</span></i>';

      //Met input value='tous' à true
      const tous = document.querySelector("input[type=radio]:checked").value;
      const radioElement = document.querySelector("input[type=radio]:checked");

      if (tous === 'Tous') {

        tous.checked = "checked";
      }

    });

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
