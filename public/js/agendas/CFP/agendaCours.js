document.addEventListener('DOMContentLoaded', (e) => {

  getIdCustomer();

  dpCours.init();

  cours.getResourceCours();

  cours.loadAllEventsCours();

  appCours.init();

  appCoursElements.init();

  dpCours.loadingStart({ delay: 100, text: "Veuillez patienter ...", block: true });

}, true);

function filterCours() {

  dpCours.rows.filter({
    // query:      $("#filter").val(),
    hideEmpty: $("#hideEmptyCours").is(":checked"),

  });
}


$("#hideEmptyCours").change(function () {
  filterCours();
});

const dpCours = new DayPilot.Scheduler("dpCours", {
  /*************************************** OPTIONS DAYPILOT******************************************************************* */
  cellWidthSpec: "Fixed",
  cellWidth: 60,
  timeHeaders: [
    {
      groupBy: "Month",
    },
    {
      groupBy: "Day",
      format: "d",
    },
    {
      groupBy: "Cell",
      format: "tt",
    },
  ],
  scale: "CellDuration",
  cellDuration: 720,

  days: DayPilot.Date.today().daysInYear(),
  startDate: DayPilot.Date.today().firstDayOfMonth(),
  eventHeight: 20,
  timeRangeSelectedHandling: "Enabled",
  crosshairType: "Full",
  eventMoveHandling: "Update",
  eventResizeHandling: "Update",
  dynamicLoading: true,

  durationBarVisible: false,
  treeEnabled: true,
  eventClickHandling: "Disabled",
  eventHoverHandling: "Bubble",
  /***************************************END OPTION DAYPILOT******************************************************************* */
  onRowFilter: function (args) {

    const hideEmpty = args.filter.hideEmpty;

    if (hideEmpty && args.row.events.isEmpty()) {
      args.visible = false;
    }

  },

  onBeforeEventRender: function (args) {

    // args.data.areas = [
    //   {
    //     right: 11,
    //     top: 4,
    //     width: 4,
    //     height: 2,
    //     icon: "fas fa-chevron-down",
    //     cssClass: "event-action",
    //   },
    // ]


  },
  contextMenu: new DayPilot.Menu({
    items: [
      {
        //Dynamic...
        text: "Ouvrir ce projet",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          window.location.replace(`/cfp/projets/${idProjet}/detail`);
        },
      },
      {
        text: 'Highlight',
        onClick: (args) => {

          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          //Recupère tous les projets de mème ID...
          const allEvents = dpCours.events.list;

          let selectedEvents = [];
          selectedEvents = allEvents.filter(evnt => evnt.idProjet == idProjet);

          if (selectedEvents.length > 0) {
            selectedEvents.forEach(e => {

              e.backColor = "rgba(254, 207, 1,0.5)";
              e.areas = [
                {

                  right: 11,
                  top: 4,
                  width: 5,
                  height: 3,
                  icon: "fas fa-solid fa-square-check",
                  cssClass: "event-action",

                }
              ]
              dpCours.events.update(e);

            });


          }
        }
      }

    ],
  }),

  onScroll: async args => {
    args.async = true;

    args.events = cours.getEventData();

    args.loaded();
  },

  onEventResized: (args) => {
    args.control.message("Vous avez modifié la date d'un évènement. ");
  },

  bubble: new DayPilot.Bubble({
    onLoad: (args) => {
      const e = args.source;
      const module = DayPilot.Util.escapeHtml(e.data.module);
      const salle = DayPilot.Util.escapeHtml(e.data.salle);
      const ville = DayPilot.Util.escapeHtml(e.data.ville);
      const nameEtp = DayPilot.Util.escapeHtml(e.data.nameEtp);
      const tabNameEtp = [...e.data.nameEtps];
      const tabform = [...e.data.prenom_form];
      const paiement = DayPilot.Util.escapeHtml(e.data.paiement);
      const typeProjet = DayPilot.Util.escapeHtml(e.data.typeProjet);
      const colors = setColorProjet(typeProjet);
      const quartier = DayPilot.Util.escapeHtml(e.data.quartier);

      const text = DayPilot.Util.escapeHtml(e.data.title);
      const start = e.start().toString("dd/MM/yyyy");

      const endTime = e.end().toString('h:mm tt');
      const startTime = e.start().toString('h:mm tt');
      const img_module = DayPilot.Util.escapeHtml(e.data.imgModule);
      const bubbleHtml = `
          <div class="flex items-center gap-2 h-full w-[28em] bg-white p-4">
            <div class="flex flex-col w-full">
              <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
                <div class="inline-flex items-start gap-2">
                  <div class="w-16 h-16 rounded-md flex items-center justify-center p-2">
                      <img
                      src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/modules/${img_module}"
    
                        alt="" class="w-full h-auto object-cover">
                  </div>
                  <div class="flex flex-col">
                    <p class="text-lg font-semibold text-gray-700">${module || 'non assigné'}
                    </p>
                    <p class="text-base text-gray-400">Client : <span
                        class="text-base text-gray-500">${!nameEtp ? tabNameEtp.map(etp => etp.name) : nameEtp}</span>
                    </p>
                    <p class="text-base text-gray-400">Projet : <span
                        class="text-base text-white px-2 rounded-md bg-[${colors}] "> ${typeProjet || 'non assigné'}</span>
                    </p>
                  </div>
                </div>
              </div>
              <div class="flex flex-col w-full">
                <div class="inline-flex items-start gap-x-2 w-full">
                  <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-base text-gray-400">Le :</p>
                  </div>
                  <div class="w-[66%] inline-flex items-center justify-start">
                    <p class="text-base text-gray-500">${start}</p>
                  </div>
                </div>
                <div class="inline-flex items-start gap-x-2 w-full">
                  <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-base text-gray-400">De :</p>
                  </div>
                  <div class="w-[66%] inline-flex items-center justify-start">
                    <p class="text-base text-gray-500">${startTime} à ${endTime}</p>
                  </div>
                </div>
                <div class="inline-flex items-start gap-x-2 w-full">
                  <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-base text-gray-400">Formateur(s) :</p>
                  </div>
                  <div class="w-[66%] inline-flex items-center justify-start">
                    <p class="text-base text-gray-500">${tabform.map(f => f.prenom) || 'non assigné'}</p>
                  </div>
                </div>
                <div class="inline-flex items-start gap-x-2 w-full">
                  <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-base text-gray-400">Cours :</p>
                  </div>
                  <div class="w-[66%] inline-flex items-center justify-start">
                    <p class="text-base text-gray-500">${module || 'non assigné'}</p>
                  </div>
                </div>
                <div class="inline-flex items-start gap-x-2 w-full">
                  <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-base text-gray-400">Financé par :</p>
                  </div>
                  <div class="w-[66%] inline-flex items-center justify-start">
                    <p class="text-base text-gray-500">${paiement}</p>
                  </div>
                </div>
                <div class="inline-flex items-start gap-x-2 w-full">
                  <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-base text-gray-400">Lieu :</p>
                  </div>
                  <div class="w-[66%] inline-flex items-center justify-start">
                    <p class="text-base text-gray-500">${ville || 'non assigné'}</p>
                  </div>
                </div>
                <div class="inline-flex items-start gap-x-2 w-full">
                  <div class="w-[32%] inline-flex items-center justify-end">
                    <p class="text-base text-gray-400">Salle :</p>
                  </div>
                  <div class="w-[66%] inline-flex items-center justify-start">
                    <p class="text-base text-gray-500">${salle || 'non assigné'}</p>
                  </div>
                </div>


            <div class="inline-flex items-start gap-x-2">
                <div class="w-[32%] inline-flex items-center justify-end">
                <p class="text-sm text-gray-400">Quartier :</p>
                </div>
                <div class="w-[66%] inline-flex items-center justify-start">
                  <p class="text-base text-gray-500">${quartier == null ? quartier : "--"
        }</p>
                </div>  
              </div>
            </div>
              </div>
            </div>
          </div>
          `;
      // if the event object doesn't specify "bubbleHtml" property
      // this onLoad handler will be called to provide the bubble HTML
      args.html = bubbleHtml;
    }
  }),

  onEventFilter: (args) => {

    //console.log('FORM COURS-->',args);
    // Ajout du filtre par statut si nécessaire
    // console.log('TOUS args-->',args.e.data)  ;

    if (args.filter.idFormateur && args.e.data && args.e.data.prenom_form && args.e.data.prenom_form.length > 0) {
      const idForm_filter = args.filter.idFormateur;
      const idFormateur_filter = args.e.data.prenom_form[0]?.idFormateur || 0;
      args.visible = idForm_filter === idFormateur_filter;
    }

  },


});

const cours = {
  getResourceCours() {
    const url = '/cfp/agendas/events_resources_agenda';
    fetch(url, { method: "GET" }).then(response => response.json())
      .then(data => {

        const ressource_table_cours = [
          {
            name: "Cours", id: "MD", expanded: true, children: data.modules
          },

        ];
        dpCours.resources = ressource_table_cours;
        dpCours.update();

        dpCours.loadingStop();

      }).catch(error => { console.error(error) });
    return dpCours;
  },

  getEventData() {
    const cours = [];
    const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
    const coursEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_DETAILS_' + idCustomer));
    const objectEvents = coursEvents ? coursEvents : this.getAllCoursEvents();
    for (const obj of objectEvents) {
      cours.push({
        start: obj.start,
        end: obj.end,
        id: DayPilot.guid(),
        idEtp: obj.idEtp,
        idSeance: obj.idSeance,
        idProjet: obj.idProjet,
        resource: 'MD_' + obj.idModule,
        height: 50,
        idProjet: obj.idProjet,
        idCalendar: obj.idCalendar,
        module: obj.module,
        salle: obj.salle,
        ville: obj.ville,
        typeProjet: obj.typeProjet,
        prenom_form: [...obj.prenom_form],
        nameEtp: obj.nameEtp,
        nameEtps: [...obj.nameEtps],
        paiement: obj.paiement,
        imgModule: obj.imgModule,
        barColor: obj.barColor,
        backColor: obj.backColor,
        quartier: obj.quartier,
        html: `<div class="flex items-start gap-1 bg-white  w-full h-full">
              <div class="flex flex-col">             
                <span class="px-2 py-1 text-sm bg-pink-100 text-slate-700 rounded-md">
                   ${obj.nameEtp}
                </span>

                <span class="ipx-3 py-1 text-sm bg-white text-slate-700 rounded-md">
                   ${obj.prenom_form ? obj.prenom_form.map(pers => pers.prenom) : 'Non Assigné!'}
                </span>

              </div>  
            </div>`,

      })

    }
    return cours;
  },

  getAllCoursEvents() {
    const cours = [];
    const url = `/cfp/agendas/getEvents`; // <=======  AgendaCfpController.php  
    let coursEvents = [];
    fetch(url, { method: "GET" }).then(response => response.json())
      .then(data => {
        const dataEvnts = (data.seances) ? data.seances : [];
        let idCustomer;
        if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
          idCustomer = sessionStorage.getItem('ID_CUSTOMER');
        }
        //if (sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_' + idCustomer) === null) {

        for (const evnt of dataEvnts) {
          const nbEvents = dataEvnts.length;
          //console.log('nbEvents-->',nbEvents);
          // Ajout sur les lignes formateurs ...  

          cours.push({
            start: evnt.start,
            end: evnt.end,
            id: crypto.randomUUID(),
            idEtp: evnt.idEtp,
            idSeance: evnt.idSeance,
            idSalle: evnt.idSalle,
            idModule: evnt.idModule,
            resource: 'MD_' + evnt.idModule,
            height: 50,
            idProjet: evnt.idProjet,
            idCalendar: evnt.idCalendar,
            module: evnt.module,
            salle: evnt.salle,
            ville: evnt.ville,
            typeProjet: evnt.typeProjet,
            quartier: evnt.quartier,
            prenom_form: evnt.formateurs.map(formateur => {
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
            nb_appr: (evnt.typeProjet == 'Intra') ? evnt.apprCountIntra : evnt.apprCountInter,
            imgModule: evnt.imgModule,
            barColor: setColorStatutRessource(evnt.statut),
            backColor: setColorStatutRessource(evnt.statut),
          })

          coursEvents = [...cours];

          //Mise en memoire(LocalSorage | SessionStorage) de tous les évennements...

          sessionStorage.setItem('ACCESS_EVENTS_DETAILS_' + idCustomer, JSON.stringify(coursEvents));

        }

        //}
      }).catch(error => { console.error(error) });
    dpCours.events.list = coursEvents;
    dpCours.update({ coursEvents });
    return coursEvents;

  },

  loadAllEventsCours() {
    const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
    const objectEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_DETAILS_' + idCustomer));
    //console.log('AFTER objectEvents==>',objectEvents)
    dpCours.events.list = objectEvents;
    dpCours.update({ objectEvents });
    return objectEvents;

  },
}


const appCoursWidth = {
  elements: {
    cellwidthCours: document.querySelector("#cellwidthCours"),
    clear: document.querySelector("#clearCours"),

  },

  init() {
    this.elements.clear.addEventListener("click", function (ev) {
      ev.preventDefault();
      appWidth.elements.filter.value = "";

      dpAnnuaire.events.filter(null);
      dpDetail.events.filter(null);
      //dpCours.events.filter(null);
      dpFormateurs.events.filter(null);

      appWidth.elements.subdropForm.textContent = "Filtrer par ...";
      appElements.elements.subdropForm.textContent = "Filtrer par ...";
      appFormElements.elements.subdropForm.textContent = "Filtrer par ...";
      //appCoursElements.elements.subdropForm.textContent = "Filtrer par ...";
      //appClientElements.elements.subdropForm.textContent = "Filtrer par ...";

    });

    this.addEventHandlers();

  },

  addEventHandlers() {

    appCoursWidth.elements.cellwidthCours.addEventListener("input", (ev) => {
      const cellWidth = parseInt(appCoursWidth.elements.cellwidthCours.value);
      const start = dpCours.getViewPort().start;

      dpCours.update({
        cellWidth: cellWidth,
        scrollTo: start
      });
      appCoursWidth.elements.label.innerText = cellWidth;
    });
  },
}

const appCours = {

  init() {
    this.addEventInputHandlers();
  },

  addEventInputHandlers() {

    const dropdownFormCours = $('#dropdownFormCours');

    dropdownFormCours.ready(function () {
      document.querySelectorAll('li').forEach(function (el) {
        el.addEventListener("click", function (ev) {

          console.log('Clic sur li avec ID COURS:', ev);

          const idFormateur = Number(ev.target.id);

          dpCours.events.filter({
            idFormateur: idFormateur
          })
        })
      })
    });
  }
}

const appCoursElements = {

  elements: {

    clearC: document.querySelector("#clearCours"),
    subdropForm: document.querySelector("#subdropFormCours")

  },

  init() {

    this.elements.clearC.addEventListener("click", function (ev) {
      ev.preventDefault();

      appWidth.elements.filter.value = "";

      dpAnnuaire.events.filter(null);
      dpDetail.events.filter(null);
      dpCours.events.filter(null);

      dpClients.events.filter(null);

      appWidth.elements.subdropForm.textContent = "Filtrer par ...";
      appElements.elements.subdropForm.textContent = "Filtrer par ...";
      appFormElements.elements.subdropForm.textContent = "Filtrer par ...";
      appCoursElements.elements.subdropForm.textContent = "Filtrer par ...";
      appClientElements.elements.subdropForm.textContent = "Filtrer par ...";


      //Met input value='tous' à true
      const tous = document.querySelector("input[type=radio]:checked").value;

      if (tous === 'Tous') {
        tous.checked = "checked";
      }

    });

  },

}


appCoursWidth.init();

// dpCours.init();
// dpCours.events.list = cours.getEventData();
// dpCours.update(); 
