document.addEventListener('DOMContentLoaded', (e) => {

  getIdCustomer();

  // dpFormateurs.init();

  //     formateurs.getResourceFormateurs();

  //     formateurs.loadAllEventsFormateurs();

  // appFormateur.init();

  // appFormElements.init();

  // dpFormateurs.loadingStart({delay:100, text:"Veuillez patienter ...",block: true});

}, true);

function filterForm() {

  dpFormateurs.rows.filter({
    // query:      $("#filter").val(),
    hideEmpty: $("#hideEmptyForm").is(":checked"),

  });
}


$("#hideEmptyForm").change(function () {
  filterForm();
});

const dpFormateurs = new DayPilot.Scheduler("dpFormateurs", {
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
  durationBarVisible: false,

  eventHeight: 20,
  timeRangeSelectedHandling: "Enabled",
  crosshairType: "Full",
  eventMoveHandling: "Update",
  eventResizeHandling: "Update",

  dynamicLoading: true,
  eventClickHandling: "Disabled",
  eventHoverHandling: "Bubble",
  treeEnabled: true,

  /***************************************END OPTION DAYPILOT******************************************************************* */

  onScroll: async args => {
    args.async = true;

    args.events = formateurs.getEventData();  // charge sessionStorage("EVENTS_DETAILS")

    args.loaded();
  },

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
      {
        text: 'Highlight',
        onClick: (args) => {

          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          //Recupère tous les projets de mème ID...
          const allEvents = dpFormateurs.events.list;

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
              dpFormateurs.events.update(e);

            });


          }
        }
      }

    ],
  }),

  onRowFilter: function (args) {

    const hideEmpty = args.filter.hideEmpty;

    if (hideEmpty && args.row.events.isEmpty()) {
      args.visible = false;
    }

  },

  onEventFilter: (args) => {

    if (args.filter.idFormateur && args.e.data && args.e.data.prenom_form && args.e.data.prenom_form.length > 0) {
      const idForm_filter = args.filter.idFormateur;
      const idFormateur_filter = args.e.data.prenom_form[0]?.idFormateur || 0;
      args.visible = idForm_filter === idFormateur_filter;
    }

  },

  onEventMove: (args) => {
    if (!confirm("Voulez-vous vraiment déplacer cette séance?")) {
      args.preventDefault();
    }
  },
  onEventMoved: async (args) => {
    args.control.message("Vous avez déplacé un évènement.");
    //const e = args.source;
    args.async = true;

    const resource = args.e.data.id;
    const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
    const event = args.e;
    // Accéder à la ressource associée à l'événement
    const resourceCode = event.resource();// ex:FM_21(Formateur num:21)
    const idProjet = event.data.idProjet;
    const start = event.data.start;
    const end = event.data.end;
    const newIdSeance = event.data.idSeance;
    const newIdFormateur = resourceCode.split('_')[1];
    const dateStart = DayPilot.Util.escapeHtml(start);
    const dateEnd = DayPilot.Util.escapeHtml(end);
    const dateSeance = dateStart.split('T')[0];
    const startTime = dateStart.split('T')[1];
    const endTime = dateEnd.split('T')[1];
    const apiUrl = '/etp/agendas/events_updateForm_agenda';
    const newObjData = {
      "idProjet": idProjet,
      "date": dateSeance,
      "start": startTime,
      "end": endTime,
      "idFormateur": newIdFormateur,
      "idSeance": newIdSeance,
    };
    axios.patch(apiUrl,
      newObjData,
      { headers: { "Content-Type": "application/json" } }
    )
      .then(function (response) {
        console.log(response);
        //dp.message(`La session a été modifiée ... `); 
        toastr.success(response.success, 'Opération effectuée avec succès', {
          timeOut: 1500
        });
        sessionStorage.removeItem('ACCESS_EVENTS_RESOURCE_' + idCustomer);
        sessionStorage.removeItem('ACCESS_EVENTS_DETAILS_' + idCustomer);

        console.log('new object-->', newObjData);
        args.events = formateurs.loadAllEventsFormateurs();
        args.loaded();
        //location.reload();           
      })

      .catch(function (error) {
        console.log(error);
        console.log('PROBLEME sur le déplacement de la séssion!');
      });
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
      const tabform = [...e.data.prenom_form];
      const nameEtp = DayPilot.Util.escapeHtml(e.data.nameEtp);
      const modalite = DayPilot.Util.escapeHtml(e.data.modalite);
      const tabNameEtp = [...e.data.nameEtps];
      const colorStatus = appAnnuel.setColorStatutRessource(e.data.statut);
      const paiement = DayPilot.Util.escapeHtml(e.data.paiement);
      const typeProjet = DayPilot.Util.escapeHtml(e.data.typeProjet);
      const colors = setColorProjet(typeProjet);
      const statut = DayPilot.Util.escapeHtml(e.data.statut);
      const materiels = [...e.data.materiels];
      const colorModalite = setModaliteColor(modalite);
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
                        <p class="text-sm text-gray-400">Matériel(s) :</p>
                    </div>    
                    <div class="w-[66%] inline-flex items-center justify-start">
                        <p class="text-sm text-gray-500">${materiels.map(mat => mat.name) || 'non assigné'}</p>
                    </div>    
                </div>


              <div class="inline-flex items-start gap-x-2 w-full">
                <div class="w-[32%] inline-flex items-center justify-end">
                  <p class="text-base text-gray-400">Lieu :</p>
                </div>
                <div class="w-[66%] inline-flex items-center justify-start">
                  <p class="text-base text-gray-500">${ville ? ville : '--'}</p>
                </div>
              </div>
              <div class="inline-flex items-start gap-x-2 w-full">
                <div class="w-[32%] inline-flex items-center justify-end">
                  <p class="text-base text-gray-400">Salle :</p>
                </div>
                <div class="w-[66%] inline-flex items-center justify-start">
                  <p class="text-base text-gray-500">${salle ? salle : '--'}</p>
                </div>
              </div>


            <div class="inline-flex items-start gap-x-2">
                <div class="w-[32%] inline-flex items-center justify-end">
                <p class="text-sm text-gray-400">Quartier :</p>
                </div>
                <div class="w-[66%] inline-flex items-center justify-start">
                  <p class="text-base text-gray-500">${quartier ? quartier : "--"
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

});

const formateurs = {
  getResourceEtpFormateurs() {
    const url = '/etp/agendas/events_resources_agenda';
    fetch(url, { method: "GET" }).then(response => response.json())
      .then(data => {

        const ressource_table_formateurs = [
          {
            name: "Formateurs", id: "FM", expanded: true, children: data?.formateurs
          },

        ];
        dpFormateurs.resources = ressource_table_formateurs;
        dpFormateurs.update();

        dpFormateurs.loadingStop();
      }).catch(error => { console.error(error) });
    return dpFormateurs;
  },

  loadAllEventsEtpFormateurs() {
    const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
    const objectEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_DETAILS_' + idCustomer));
    console.log('AFTER FORMATEUR...objectEvents==>',objectEvents)
    dpFormateurs.events.list = objectEvents;
    dpFormateurs.update({ objectEvents });

    return objectEvents;

  },

  getEventData() {
    const formateurs = [];
    const formsFilter = [];
    const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
    const resourceEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_DETAILS_' + idCustomer));
    const objectEvents = resourceEvents ? resourceEvents : this.getAllResourceEvents();
    //let objectEvents =  resourceEvents;//<== àcompleter ;
    for (const evnt of objectEvents) {
      // Ajout sur les lignes formateurs ...  
      // console.log('evnt OBJ FORMS ETP S-->', evnt)
      //if(evnt.idEtp == idCustomer){
      evnt.prenom_form.forEach(form => {  
        formateurs.push({
          start: evnt.start,
          end: evnt.end,
          id: crypto.randomUUID(),
          idEtp: evnt.idEtp,
          idSeance: evnt.idSeance,
          idSalle: evnt.idSalle,
          idModule: evnt.idModule,
          //resource: 'FM_' + [...evnt.prenom_form.idFormateur],
          text: `   ${evnt.nameEtp ? evnt.nameEtp : evnt.nameEtps ? evnt.nameEtps.map(etp => etp.name).join(' et ') : '--'} \n - ${evnt.module ?? '  -- '} - \n ${evnt.quartier ?? '  -- '} - ${evnt.ville ?? '  -- '} - ${evnt.codePostal ?? '  -- '} `,  // Ajoutez le texte 
          resource: 'FM_' + form.idFormateur,
          height: 70,
          idProjet: evnt.idProjet,
          idCalendar: evnt.idCalendar,
          module: evnt.module,
          salle: evnt.salle,
          ville: evnt.ville,
          typeProjet: evnt.typeProjet,
          prenom_form: [...evnt.prenom_form],
          materiels: evnt.materiels ? [...evnt.materiels] : [],
          nameEtp: evnt.nameEtp,
          nameEtps: evnt.nameEtps ? [...evnt.nameEtps] : [],
          statut: evnt.statut,
          paiement: evnt.paiement,
          barColor: setColorProject.barColor(evnt.idProjet),
          barBackColor: setColorProject.barBackColor(evnt.idProjet),
          imgModule: evnt.imgModule,
          nb_appr: evnt.nb_appr,
          materiels: evnt.materiels ? [...evnt.materiels] : [],
          codePostal: evnt.codePostal,
          reference: evnt.reference,
          quartier: evnt.quartier,
          idModule: evnt.idModule,
          idDossier: evnt.idDossier,
          modalite: evnt.modalite,
          nb_seances: evnt.nb_seances,
          html: `<div class= "flex items-start gap-1 bg-white  w-full h-full">
        <div class="flex flex-col">
          <span class="px-2 py-1 text-sm bg-pink-100 text-slate-700 rounded-md">
           
            ${evnt.nameEtp ? evnt.nameEtp : evnt.nameEtps.map(etp => etp.name).join(' et ')}
          </span>

          <span class="ipx-3 py-1 text-sm bg-white text-slate-700 rounded-md">
           
            ${evnt.module}
          </span>

          <span class="ipx-3 py-1 text-sm bg-white text-slate-700 rounded-md">
          
            ${evnt.quartier ?? '--'},${evnt.ville ?? '--'}(${evnt.codePostal ?? '--'})
          </span>

        </div>  
            </div > `,


        })
  
      });
    //}
    }
     
    //formsFilter = formateurs.filter(data => data.idEtp != idCustomer);
    //console.log('FORMS FILTER-->', formsFilter);

    return formateurs;
  },

  getAllResourceEvents() {
    // A completer...
    const formateurs = [];
    const url = `/etp/agendas/getEvents`; // <=======  AgendaEtppController.php  
    let formEvents = [];
    fetch(url, { method: "GET" }).then(response => response.json())
      .then(data => {
        dataEvnts = (data.seances) ? data.seances : [];
        let idCustomer;
        if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
          idCustomer = sessionStorage.getItem('ID_CUSTOMER');
        }
        //if (sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_' + idCustomer) === null) {

        for (const evnt of dataEvnts) {
          const nbEvents = dataEvnts.length;
          //console.log('nbEvents-->',nbEvents);
          // Ajout sur les lignes formateurs ...  
          evnt.formateurs.forEach(form => {
            formateurs.push({
              start: evnt.start,
              end: evnt.end,
              id: crypto.randomUUID(),
              idEtp: evnt.idEtp,
              idSeance: evnt.idSeance,
              idSalle: evnt.idSalle,
              idModule: evnt.idModule,
              resource: 'FM_' + form.idFormateur,
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
              quartier: evnt.quartier,
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
          });
          formEvents = [...formateurs];

        }

        //Mise en memoire(LocalSorage | SessionStorage) de tous les évennements...
        sessionStorage.setItem('ACCESS_EVENTS_DETAILS_' + idCustomer, JSON.stringify(formEvents));

        //}
      }).catch(error => { console.error(error) });
    dpFormateurs.events.list = formEvents;
    dpFormateurs.update({ formEvents });
    return formEvents;
  },



}

const appFormWidth = {
  elements: {
    cellWidth: document.querySelector("#cellwidthForm"),

  },

  addEventHandlers() {
    appFormWidth.elements.cellWidth.addEventListener("input", (ev) => {
      const cellWidth = parseInt(appFormWidth.elements.cellWidth.value);
      const start = dpFormateurs.getViewPort().start;

      dpFormateurs.update({
        cellWidth: cellWidth,
        scrollTo: start
      });
      //appFormWidth.elements.label.innerText = cellWidth;
    });
  }
}

const appFormateur = {
  init() {
    this.addEventInputHandlers();
  },

  addEventInputHandlers() {

    const dropdownFormFormateur = $('#dropdownFormFormateur');

    dropdownFormFormateur.ready(function () {
      document.querySelectorAll('li').forEach(function (el) {
        el.addEventListener("click", function (ev) {

          const idFormateur = Number(ev.target.id);

          dpFormateurs.events.filter({
            idFormateur: idFormateur
          })
        })
      })
    });
  }
}

const appFormElements = {
  elements: {

    clearF: document.querySelector("#clearForm"),
    subdropForm: document.querySelector("#subdropFormFormateur")

  },

  init() {

    this.elements.clearF.addEventListener("click", function (ev) {
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

appFormWidth.addEventHandlers();