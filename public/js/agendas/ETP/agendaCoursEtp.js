document.addEventListener('DOMContentLoaded', (e) => {

  getIdCustomer();

  // dpCours.init();

  //     cours.getResourceCours();

  //     cours.loadAllEventsCours();
  appCours.init();

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
  eventClickHandling: "Disabled",
  eventHoverHandling: "Bubble",
  treeEnabled: true,

  durationBarVisible: true,
  /***************************************END OPTION DAYPILOT******************************************************************* */
  onRowFilter: function (args) {

    const hideEmpty = args.filter.hideEmpty;

    if (hideEmpty && args.row.events.isEmpty()) {
      args.visible = false;
    }

  },
  // onBeforeEventRender: function (args) {

  //   args.data.areas = [
  //     {
  //       right: 11,
  //       top: 4,
  //       width: 4,
  //       height: 2,
  //       icon: "fas fa-chevron-down",
  //       cssClass: "event-action",
  //     },
  //   ]


  // },
  contextMenu: new DayPilot.Menu({
    items: [
      {
        //Dynamic...
        text: "Ouvrir ce projet",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          window.location.replace(`/etp/projets/${idProjet}/detail`);
        },
      },

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
  eventClickHandling: "Disabled",
  eventHoverHandling: "Bubble",
  treeEnabled: true,

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
      const colorStatus = appAnnuel.setColorStatutRessource(e.data.statut);
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
      
      console.log(tabForm);

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
                      <p class="text-sm text-gray-400">CFP : <span
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

const cours = {
  getResourceCours() {
    const url = '/etp/agendas/events_resources_agenda';
    fetch(url, { method: "GET" }).then(response => response.json())
      .then(data => {
        console.log('Chargement cours...');
        const ressource_table_cours = [
          {
            name: "Cours", id: "MD", expanded: true, children: data.modules
          },
          {
            name: "Cours_externes", id: "MD", expanded: true, children: data.module_externes
          },

        ];
        dpCours.resources = ressource_table_cours;
        dpCours.update();

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
        text: ` REf:  ${obj.reference ?? '  -- '} \n - ${obj.module} - \n ${obj.codePostal}- ${obj.ville} - \n ${obj.nameCfp ? obj.nameCfp : '--'}`,  // Ajoutez le text
        height: 70,
        idProjet: obj.idProjet,
        idCalendar: obj.idCalendar,
        module: obj.module,
        salle: obj.salle,
        ville: obj.ville,
        typeProjet: obj.typeProjet,
        prenom_form: obj.prenom_form ? [...obj.prenom_form]:[],
        prenom_form_interne: obj.prenom_form_interne? [...obj.prenom_form_interne]:[],
        nameEtp: obj.nameEtp,
        nameEtps: [...obj.nameEtps],
        nameCfp: obj.nameCfp,
        statut: obj.statut,
        paiement: obj.paiement,
        barColor: setColorProject.barColor(obj.idProjet),
        barBackColor: setColorProject.barBackColor(obj.idProjet),
        imgModule: obj.imgModule,
        nb_appr: obj.nb_appr,
        materiels: obj.materiels ? [...obj.materiels] : [],
        codePostal: obj.codePostal,
        reference: obj.reference,
        quartier: obj.quartier,
        idModule: obj.idModule,
        idDossier: obj.idDossier,
        modalite: obj.modalite,
        nb_seances: obj.nb_seances,
        // barColor: obj.barColor,
        // backColor: obj.backColor,
        html: '<div class="w-full ml-1 flex flex-col items-start text-xs">' +
        ' <span class="px-2 py-1 text-sm bg-pink-100 text-slate-700 rounded-md ">' +
     
        DayPilot.Util.escapeHtml(obj.module) +
        ' </span>' +
        '<span class="w-full ml-1 flex flex-col bg-grey-100 items-start italic text-xs rounded-md " > Ref: ' +
        `${obj.reference ? DayPilot.Util.escapeHtml(obj.reference) : "--"}  
                 </span>`

        + ((obj.typeProjet != 'Interne') ?
          '<span class="px-3 py-1 text-sm bg-white text-slate-700 rounded-md">' +
   
          DayPilot.Util.escapeHtml(obj.nameCfp) +
          '</span>'
          :
          '<span class="px-3 py-1 text-sm bg-white text-slate-700 rounded-md">' +
    
          DayPilot.Util.escapeHtml(obj.nameEtp) +
          '</span>') +

        '<span class="px-2 py-1 text-sm bg-pink-100 text-slate-700 rounded-md">' +
   
        DayPilot.Util.escapeHtml(obj.codePostal) + ':' + DayPilot.Util.escapeHtml(obj.ville) +
        '</span>'

      })

    }
    return cours;
  },

  getAllCoursEvents() {
    const cours = [];
    const url = `/etp/agendas/getEvents`; // <=======  AgendaEtpController.php  
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
            height: 20,
            idProjet: evnt.idProjet,
            idCalendar: evnt.idCalendar,
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
    cellWidth: document.querySelector("#cellwidthCours"),

  },

  addEventHandlers() {
    appCoursWidth.elements.cellWidth.addEventListener("input", (ev) => {
      const cellWidth = parseInt(appCoursWidth.elements.cellWidth.value);
      const start = dpCours.getViewPort().start;

      dpCours.update({
        cellWidth: cellWidth,
        scrollTo: start
      });
      //appFormWidth.elements.label.innerText = cellWidth;
    });
  }
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
      //dpCours.events.filter(null);
      dpFormateurs.events.filter(null);
      //dpClients.events.filter(null);

      // appWidth.elements.subdropForm.textContent = "Filtrer par ...";
      // appElements.elements.subdropForm.textContent = "Filtrer par ...";
      // appFormElements.elements.subdropForm.textContent = "Filtrer par ...";    
      //appCoursElements.elements.subdropForm.textContent = "Filtrer par ...";
      //appClientElements.elements.subdropForm.textContent = "Filtrer par ...";

      appWidth.elements.subdropForm.innerHTML = '<i class="fas fa-filter"><span class="text-sx"> par formateur</span></i>';
      appElements.elements.subdropForm.innerHTML = '<i class="fas fa-filter"><span class="text-sx"> par formateur</span></i>';
      appFormElements.elements.subdropForm.innerHTML = '<i class="fas fa-filter"><span class="text-sx"> par formateur</span></i>';

    });

  },
}

$(document).ready(function () {

  $("#export-button-formateur").click(function (ev) {
    ev.preventDefault();

    dpFormateurs.exportAs("jpeg",
      {
        area: 'viewport',
        quality: 0.95,
        scale: 0.7,

      }).print();

  });
});

appCoursWidth.addEventHandlers();    