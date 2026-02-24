document.addEventListener('DOMContentLoaded', (e) => {

  appAnnuel.getAllEventsGroupBy();    //<====SEANCES GROUPES(ANNUELLE)

  document.getElementById('vueSelector').selectedIndex = 1; // <== pointe vers formation...

  dpAnnuaire.events.filter({
    etat: 1 //etat 1=>"Formations"
  })

  appAnnuel.getEventsOpportunities(); //<===OPPORTUNITÉ

  appAnnuel.getEventDataFact();//<====FORMATEURS

  appAnnuel.init(); //<===== initialisation permettant le filtrage des formateurs

  /*****************************************************MISE A JOUR Détail(WEEK)*****************************************************************/

  dpAnnuaire.init();

  dpDetail.init();

  dpFormateurs.init();

  const listEventDetails = appAnnuel.getAllDetailEvents();

  dpDetail.events.list = listEventDetails;

  dpDetail.update({ listEventDetails });
  // dpFormateurs.update({listEventFormateur});

  appDetail.init(); //<===== initialisation permettant le filtrage des formateurs

  /*************************************************************************************************************************************/
  /*****************************************************MISE A JOUR Formateur**********************************************************************************/

  formateurs.getResourceFormateurs();

  appFormateur.init(); //<===== initialisation permettant le filtrage des formateurs

  appFormElements.init();

  dpFormateurs.loadingStart({ delay: 100, text: "Veuillez patienter ...", block: true });

  const w = window.innerWidth;
  const h = window.innerHeight;

  dpAnnuaire.update({
    cellWidth: calculateCellWidth(w),
    height: calculateHeight(w),
  });
  /*********************************************************************************************************************************************************/

  $('#tabAnnuaire').show();
  const width = window.innerWidth;

  dpAnnuaire.update({
    cellWidth: calculateCellWidth(width),
    //height:     calculateHeight(width),
  });

  $("#filterButtonStatus").click(function () {
    $("#toggleFilterStatus").toggle(); // toggle collapse
  });

  $("#toggleFilter").toggle(); //Cacher filtre par statut et par formateur...
  $("#toggleExport").toggle(); //Cacher Export...

  $("#filtertButton").click(function () {
    $("#toggleFilter").toggle(); // toggle collapse
  });

  $("#exportButton").click(function () {
    $("#toggleExport").toggle(); // toggle collapse
  });

  function displayForm(selectedContentId) {

    switch (selectedContentId) {
      case 'tabAnnuaire':
        return $('#tabAnnuaire').show();
      case 'tabDetail':
        return $('#tabDetail').show();
      case 'tabCours':
        return $('#tabCours').show();
      case 'tabFormateur':
        return $('#tabFormateur').show();
      case 'tabClient':
        return $('#tabClient').show();
    }

  }

  function setSelectTab(selectedContentId) {
    switch (selectedContentId) {
      case 'tabAnnuaire':
        return tabSelectorAnnuelle.value = selectedContentId;
      case 'tabDetail':
        return tabSelectorDetail.value = selectedContentId;
      case 'tabCours':
        return tabSelectorCours.value = selectedContentId;
      case 'tabFormateur':
        return tabSelectorFormateur.value = selectedContentId;
      case 'tabClient':
        return tabSelectorClient.value = selectedContentId;
    }
  }

  function hideForm() {
    $('#tabAnnuaire').hide();
    $('#tabDetail').hide();
    $('#tabCours').hide();
    $('#tabFormateur').hide();
    // $('#tabClient').hide();

  }

  function handleHideDisplayForm() {
    const selectedContentId = `${this.value}`;
    hideForm();
    displayForm(selectedContentId);
    setSelectTab(selectedContentId);
  }

  const tabSelectorAnnuelle = document.getElementById('tabSelectorAnnuelle');
  const tabSelectorDetail = document.getElementById('tabSelectorDetail');
  const tabSelectorCours = document.getElementById('tabSelectorCours');
  const tabSelectorFormateur = document.getElementById('tabSelectorFormateur');
  const tabSelectorClient = document.getElementById('tabSelectorClient');

  tabSelectorAnnuelle.addEventListener('change', handleHideDisplayForm)

  tabSelectorDetail.addEventListener('change', handleHideDisplayForm);

  tabSelectorCours.addEventListener('change', handleHideDisplayForm);

  tabSelectorFormateur.addEventListener('change', handleHideDisplayForm);

  tabSelectorClient.addEventListener('change', handleHideDisplayForm);

}, true)

// function getIdCustomer() {
//   let idCustomer;
//   const url = '/home/customer';
//   fetch(url, { method: "GET" }).then(response => response.json())
//     .then(data => {
//       idCustomer = data.idCustomer;
//       sessionStorage.setItem('ID_CUSTOMER', idCustomer);

//     }).catch(error => { console.error(error) });
//   return idCustomer;
// }

function getNameForm(name, id) {

  appWidth.elements.subdropForm.innerHTML = '<i class="fas fa-filter"></i> par ' + name;
  //appClientElements.elements.subdropForm.textContent = 'Filtrer par '+ name;
  //appCoursElements.elements.subdropForm.textContent = 'Filtrer par '+ name;
  appElements.elements.subdropForm.innerHTML = '<i class="fas fa-filter"></i> par ' + name;
  appFormElements.elements.subdropForm.innerHTML = '<i class="fas fa-filter"></i> par ' + name;
  const idAnnuel = id;
  const idWeek = id + "_w"; //<==id pour le weekend ?_w
  const idForm = id + "_f"; //<==id pour le view formateur  ?_f
  //Synchronise...
  const dropdownFormWeek = document.getElementsByName('formsWeek');

  const dropdownFormAnnuel = document.getElementsByName('formsAnnuel');

  const dropdownFormFormateur = document.getElementsByName('formsViewForms');

  //console.log('dropdownFormFormateur',dropdownFormFormateur);

  dropdownFormAnnuel.forEach(radio => {
    if (radio.id == idAnnuel)
      radio.checked = true;
  });

  dropdownFormWeek.forEach(radio => {
    if (radio.id == idWeek)
      radio.checked = true;
  });

  dropdownFormFormateur.forEach(radio => {
    if (radio.id == idForm)
      radio.checked = true;
  });

}

function setNameStatus(name) {
  appWidth.elements.subdropStatus.innerHTML = '<i class="fas fa-filter"></i>' + name;
}

function getDaysInMonth(year, month) {
  // Créez une date du premier jour du mois suivant
  const date = new Date(year, month + 1, 0);

  // Récupérez le jour du mois, qui est le nombre total de jours dans le mois
  return date.getDate();
}

function getYear() {
  const startDdate = new Date();
  const year = startDdate.getFullYear();
  return year;
}

function calculateHeight(width) {
  // Calculez la hauteur en fonction de la largeur
  // Par exemple, pour une largeur de 1920px, utilisez une hauteur de 950px
  return width < 1920 ? 500 : 950;
}

function calculateCellWidth(width) {
  return width < 1920 ? 50 : 68;
}

window.addEventListener('resize', () => {
  const width = window.innerWidth;
  const height = window.innerHeight;
  //  console.log('w =>',width);
  //  console.log('h =>',height);
  dpAnnuaire.update({
    cellWidth: calculateCellWidth(width),
    height: calculateHeight(width),
  });
});

function extractText(html) {
  var parser = new DOMParser();
  var doc = parser.parseFromString('<parser><![CDATA[' + html + ']]></parser>', 'text/html');
  var text = doc.body.textContent;

  // Nettoyez le texte pour supprimer les espaces en trop et les sauts de ligne
  return text.replace(/\s+/g, ' ').trim();
}

function isWeekendDate(date) {
  var holidays = ["2024-01-01", "2024-04-25", "2024-05-27", "2024-06-19", "2024-09-02", "2024-11-28"];

  return holidays.includes(date.format("yyyy-MM-dd"));
}

function getMonth(id) {

  const months = [
    "janvier", "février", "mars", "avril", "mai", "juin",
    "juillet", "août", "septembre", "octobre", "novembre", "décembre"
  ];
  switch (id) {
    case 1:
      return months[0];
    case 2:
      return months[1];
    case 3:
      return months[2];
    case 4:
      return months[3];
    case 5:
      return months[4];
    case 6:
      return months[5];
    case 7:
      return months[6];
    case 8:
      return months[7];
    case 9:
      return months[8];
    case 10:
      return months[9];
    case 11:
      return months[10];
    case 12:
      return months[11];
    default:
      return "Mois non valide";
  }

}

function setModaliteColor(modalite) {
  let color = "";
  switch (modalite) {
    case 'Présentielle':
      return color = "#00b4d8";
    case 'En ligne':
      return color = "#fca311";
    case 'Blended':
      return color = "#005f73";
  }

}

function handleClearAction() {

  // Mettre à jour l'état du filtre
  appWidth.elements.filter.value = "";

  // Filtrer les données
  dpAnnuaire.events.filter(null);
  dpDetail.events.filter(null);
  dpFormateurs.events.filter(null);

  // Mettre à jour les éléments de sous-menu
  appWidth.elements.subdropForm.innerHTML = '<i class="fas fa-filter"><span class="text-sx"> par formateur</span></i>';
  appElements.elements.subdropForm.innerHTML = '<i class="fas fa-filter"><span class="text-sx"> par formateur</span></i>';
  appFormElements.elements.subdropForm.innerHTML = '<i class="fas fa-filter"><span class="text-sx"> par formateur</span></i>';

  appWidth.elements.subdropStatus.innerHTML = '<i class="fas fa-filter"><span class="text-sx"> par statut</span></i>';

  // Désélectionner tous les éléments de liste déroulante
  const dropdownFormAnnuel = document.getElementsByName('formsAnnuel');
  const dropdownFormDetail = document.getElementsByName('formsWeek');
  const dropdownFormViewForm = document.getElementsByName('formsViewForms');
  const dropdownStatus = document.getElementsByName('formStatusAnnuel');

  dropdownFormAnnuel.forEach(radio => radio.checked = false);
  dropdownFormDetail.forEach(radio => radio.checked = false);
  dropdownFormViewForm.forEach(radio => radio.checked = false);
  dropdownStatus.forEach(radio => radio.checked = false);

  // Vider les éléments de badge
  var badge = $('#badge');
  var badgeForms = $('#badgeForms');
  var badgeStatus = $('#badgeStatus');

  badge.empty();
  badgeForms.empty();
  badgeStatus.empty();

  document.getElementById('vueSelector').selectedIndex = 1; // <== pointe vers formation...
  dpAnnuaire.events.filter({
    etat: 1 //etat 1=>"Formations"
  })
}
//Menu1 pour les SEANCES...
const menu1 = new DayPilot.Menu({
onShow: (args) => {
  const eventData = args.source.data;
  const idCustomer= sessionStorage.getItem('ID_CUSTOMER');
  console.log('eventData==>',eventData);
  args.menu.items = [
    {
      //Dynamic...
      text: "Ouvrir ce projet",
      icon: "fa-solid fa-tarp",
      onClick: (args) => {
        const e = args.source;
        //console.log('---->',e);
        const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
        window.location.replace(`/cfp/projets/${idProjet}/detail`);
      }
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
        showSessions(`/cfp/session-drawer/${idProjet}`, idProjet);
      }
    },

    {
      text: "Accéder au formateur",
      icon: "fa-solid fa-user-graduate",
      onClick: (args) => {
        const e = args.source;
        const idFormateur = DayPilot.Util.escapeHtml(e.data.prenom_form[0].idFormateur);
        const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
        viewMiniCV(idFormateur, idProjet);
      }
    },

    {
      text: "Accéder aux participants",
      icon: "fa-solid fa-users",
      onClick: (args) => {
        const e = args.source;
        const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
        showApprenants(`/cfp/apprenant-drawer/${idProjet}`, idProjet);
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
      icon: "fa-solid fa-folder",
      onClick: (args) => {
        const e = args.source;
        const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
        showDossiers(`/cfp/dossier-drawer/${idProjet}`);
      }
    },

    {
      text: 'Highlight',
      icon: "fa-solid fa-lightbulb",
      onClick: (args) => {
        const e = args.source;
        const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
        const links = [];
        //dp.update();
        //Recupère tous les projets de mème ID...
        const allEvents = dpAnnuaire.events.list;
        let selectedEvents = [];
        let unSelectedEvents = [];
        selectedEvents = allEvents.filter(evnt => evnt.idProjet == idProjet);
        unSelectedEvents = allEvents.filter(evnt => evnt.idProjet != idProjet);
        console.log('selectedEvents-->', selectedEvents);
        const listId = [];

        if (unSelectedEvents.length > 0) {
          unSelectedEvents.forEach(e => {
            listId.push(e.idSeance);

            if (e.selected == false) {
              //e.backColor = "rgba(190, 187, 187,0.5)";
              e.selected = true;
              //console.log('links---->',links)
              dpAnnuaire.events.update(e);
            }
          })
        }
        if (selectedEvents.length > 0) {
          selectedEvents.forEach(e => {
            listId.push(e.idSeance);

            if (e.selected == false) {
              e.backColor = "rgba(254, 207, 1,0.5)";
              e.selected = true;
              //console.log('links---->',links)
              dpAnnuaire.events.update(e);
            }
            else {
              console.log('SELECTED TRUE');
              dpAnnuaire.clearSelection();
              dpAnnuaire.update();
            }
          });
        }
      }
    },

   {
    text: !eventData.idCalendar ? "Ajouter les sessions à google Calendar" : "Enlever les sessions à google Calendar",
    icon: !eventData.idCalendar ? "fa-solid text-sm fa-thumbs-up text-green-500" : "fa-solid text-sm fa-thumbs-down text-red-500",
      onClick:(args) =>{
        const e = args.source;
        const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
        const storage_idAgenda = localStorage.getItem('ID_AGENDA');

        const allEvents = dpDetail.events.list;
        console.log('allEvents==>',allEvents);
        let selectedEvents = [];
        selectedEvents = allEvents.filter(evnt => evnt.idProjet == idProjet);
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
              dpAnnuaire.events.update(e);
            }       
          });
      }

      if(eventData.idCalendar ){
        if (!storedToken) {
          handleAuthClick();
        }else{          
          console.log('NEWSELECT ==>',newSelectedEvents);
          executeDeleteListOnGoogle(dpDetail,storage_idAgenda,newSelectedEvents);
          dpAnnuaire.message("Created on your GOOGLE CALENDAR!!!", { cssClass: "shadow-2xl rounded-md " });
        if(newSelectedEvents.length >0){
          newSelectedEvents.map(evnt =>{
            evnt.backColor = "rgba(246, 241, 216, 0.5)";
            evnt.idCalendar = null;
            dpAnnuaire.events.update(evnt);
            updateIdListCalendarSession(evnt.idSeance,null);

          })         
        } 
       // window.location.reload();
        // let restOfSeances = allEvents.filter(evnt => evnt.idSeance !== e.data.idSeance && evnt.etat === 1);
        // const newSeances = [...newSelectedEvents,...restOfSeances];
        // sessionStorage.setItem('ACCESS_EVENTS_DETAILS_' + idCustomer,JSON.stringify(newSeances));
      }          
      }else{
        if (!storedToken) {
          handleAuthClick();
          }else{
            if(newSelectedEvents.length >0){
              newSelectedEvents.map(evnt =>{
                evnt.backColor = "rgba(183, 181, 34, 0.5)";
             
                dpAnnuaire.events.update(evnt);
                updateIdListCalendarSession(evnt.idSeance,null);
              })         
            } 
          executeAddListOnGoogle(dpDetail,newSelectedEvents,storage_idAgenda);
          dpAnnuaire.message("Created on your GOOGLE CALENDAR!!!", { cssClass: "shadow-2xl rounded-md " });
         // window.location.reload();
        }
      }
    }
    },
  ]
}
});
//Menu2 pour les OPPORTUNITES...
const isIdCalendarNull = false;
// Exemple 1: Menu avec items dynamiques basés sur les données de l'événement
const menu2 = new DayPilot.Menu({
  onShow: (args) => {
    // Récupérer les données de l'événement
    const eventData = args.source.data;
    console.log('eventData==>',eventData);
    const idCustomer= sessionStorage.getItem('ID_CUSTOMER');
    const storedToken = localStorage.getItem('ACCESS_TOKEN');
    // Définir les items du menu de façon dynamique
    args.menu.items = [
      {
        text: eventData.idCalendar ? "Opportunité liée à GOOGLE":"Opportunité non liée à GOOGLE",
        //icon: "fa-solid text-sm fa-thumbs-up text-green-500",
      },
      {
        text: !eventData.idCalendar ? "Ajouter à google Calendar" : "Enlever à google Calendar",
        icon: !eventData.idCalendar ? "fa-solid text-sm fa-thumbs-up text-green-500" : "fa-solid text-sm fa-thumbs-down text-red-500",
        onClick: (menuArgs) => {        
          const idAgendaOpportunity = localStorage.getItem('ID_OPPORTUNITY');   
          const e = menuArgs.source;
          let allEvnts =  sessionStorage.getItem('ACCESS_EVENTS_PROSPECTS_'+idCustomer);

          const allEvents = JSON.parse(allEvnts);   
          const idAgenda = localStorage.getItem('ID_OPPORTUNITY');         
          /****************************** */
          if(eventData.idCalendar ){
            if (!storedToken) {
              handleAuthClick();
            }else{          
              executeDeleteOpportunityOnGoogle(eventData.idCalendar,dpAnnuaire,idAgenda,menuArgs);
              let opportunite = allEvents.filter(evnt => evnt.idOpportunity == e.data.idOpportunity);
             // let opportuniteDp = allDpEvents.filter(evnt => evnt.idOpportunity == e.data.idOpportunity)
              if(opportunite.length >0){
                opportunite.map(evnt =>{
                  evnt.backColor = "rgba(246, 241, 216, 0.5)";
                  evnt.idCalendar = null;
                  dpAnnuaire.events.update(evnt);
                })
              }
               let restOfOpportunities = allEvents.filter(evnt => evnt.idOpportunity !== e.data.idOpportunity && evnt.etat === 2);
               const newOpportunities = [...opportunite,...restOfOpportunities];
               sessionStorage.setItem('ACCESS_EVENTS_PROSPECTS_' + idCustomer,JSON.stringify(newOpportunities));
            }           
          }else{
            if (!storedToken) {
            handleAuthClick();
            }else{
              executeAddOpportunityOnGoogle(dpAnnuaire,args,idAgendaOpportunity);
               const idCalendarOpp = sessionStorage.getItem('ID_CALENDAR_OPP');
                let opportunite = allEvents.filter(evnt => evnt.idOpportunity == e.data.idOpportunity); 
                if(opportunite.length >0){
                  opportunite.map(evnt =>{
                    evnt.backColor = "rgba(204, 229, 244, 0.63)";
                    evnt.idCalendar = idCalendarOpp;
                    dpAnnuaire.events.update(evnt);
                  })
                }
                let restOfOpportunities = allEvents.filter(evnt => evnt.idOpportunity !== e.data.idOpportunity && evnt.etat === 2);
                const newOpportunities = [...opportunite,...restOfOpportunities];
                sessionStorage.setItem('ACCESS_EVENTS_PROSPECTS_' + idCustomer,JSON.stringify(newOpportunities));                           
            }
          }
        }
      },
    ];
  }
});

const dpAnnuaire = new DayPilot.Scheduler("dpAnnuaire", {
  /***************************************OPTIONS DAYPILOT******************************************************************* */
  locale: "fr-FR",
  startDate: `${getYear()}-01-01`,// <==premier jour et mois de l'année 
  //startDate:DayPilot.Date.today().firstDayOfYear(),
  scale: "CellDuration",
  cellDuration: 720,
  headerDateFormat: "ddds dd MMMM yyyy",
  //timeRangeSelectedHandling: "Enabled",
  eventMoveHandling: "Disabled",
  timeRangeSelectingStartEndEnabled: "Disabled",
  autoRefreshEnabled: true,
  eventDoubleClickHandling: "Update",
  //autoRefreshEnabled: true,
  cellGroupBy: "Month",
  days: 31,
  scale: "Day",
  timeHeaders: [

    { groupBy: "Day", format: "d" }
  ],

  treeEnabled: true,
  resources: [
    { name: "Janvier", id: 1 },
    { name: "Février", id: 2 },
    { name: "Mars", id: 3 },
    { name: "Avril", id: 4 },
    { name: "Mai", id: 5 },
    { name: "Juin", id: 6 },
    { name: "Juillet", id: 7 },
    { name: "Août", id: 8 },
    { name: "Septembre", id: 9 },
    { name: "Octobre", id: 10 },
    { name: "Novembre", id: 11 },
    { name: "Décembre", id: 12 },
  ],
  dynamicLoading: true,
  durationBarVisible: true,
  eventHoverHandling: "Bubble",
  // rectangleSelectMode: "Free",
  // rectangleSelectHandling: "EventSelect",
  // eventClickHandling: "JavaScript",
  linkBottomMargin: 20,
  // groupConcurrentEvents: true,
  groupConcurrentEventsLimit: 2,
  eventHeight: 80,
  // linkCreateHandling: "Update",
  /***************************************END OPTION DAYPILOT******************************************************************* */
  bubble: new DayPilot.Bubble({
    onLoad: (args) => {
      const e = args.source;
      if (e.data.etat === 1) {
        const module = DayPilot.Util.escapeHtml(e.data.module);
        const salle = DayPilot.Util.escapeHtml(e.data.salle);
        const ville = DayPilot.Util.escapeHtml(e.data.ville);
        const quartier = DayPilot.Util.escapeHtml(e.data.quartier);
        const modalite = DayPilot.Util.escapeHtml(e.data.modalite);
        const nb_seances = DayPilot.Util.escapeHtml(e.data.nb_seances);

        const tabForm = [...e.data.prenom_form];
        const nameEtp = DayPilot.Util.escapeHtml(e.data.nameEtp);
        const materiels = [...e.data.materiels];
        const tabNameEtp = [...e.data.nameEtps];
        const colorStatus = appAnnuel.setColorStatutRessource(e.data.statut)

        const statut = DayPilot.Util.escapeHtml(e.data.statut);
        const paiement = DayPilot.Util.escapeHtml(e.data.paiement);
        const nb_appr = DayPilot.Util.escapeHtml(e.data.nb_appr);
        const typeProjet = DayPilot.Util.escapeHtml(e.data.typeProjet);
        const colors = appAnnuel.setColorProjet(typeProjet);
        const colorModalite = setModaliteColor(modalite);
        const img_module = DayPilot.Util.escapeHtml(e.data.imgModule);
        const start = e.start().toString("dd/MM/yyyy");
        const date = DayPilot.Util.escapeHtml(e.data.date);
        const startTime = (e.data.startTime);
        const endTime = (e.data.endTime);

        const bubbleHtml1 = `
          <div class="flex items-center gap-2 h-full w-[28em] bg-white p-4">
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
                          <p class="text-base text-gray-500">${date}</p>
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
                          <p class="text-base text-gray-500">${nb_appr || "non assigné"}   </p>
                        </div>
                      </div>
                      <div class="inline-flex items-start gap-x-2">
                        <div class="w-[32%] inline-flex items-center justify-end">
                          <p class="text-base text-gray-400">Séances(s) :</p>
                        </div>
                        <div class="w-[66%] inline-flex items-center justify-start">
                          <p class="text-base text-gray-500">${(nb_seances != 'undefined') ? nb_seances : "--"}   </p>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>`;

        args.html = bubbleHtml1;
      }
      else if (e.data.etat === 2) {
        const img_module = DayPilot.Util.escapeHtml(e.data.imgModule);
        const module = DayPilot.Util.escapeHtml(e.data.module);
        const nb_appr = DayPilot.Util.escapeHtml(e.data.nb_appr);
        const date = DayPilot.Util.escapeHtml(e.data.date);
        const statut = DayPilot.Util.escapeHtml(e.data.statut);
        const colorStatus = DayPilot.Util.escapeHtml(e.data.barColor);
        const start = e.start().toString("dd/MM/yyyy");
        const end = e.end().toString("dd/MM/yyyy");
        const nameEtp = DayPilot.Util.escapeHtml(e.data.nameEtp);
        const bubbleHtml2 = ` 
  <div class="flex items-center gap-2 h-full w-[28em] bg-white p-4">
                  <div class="flex flex-col w-full">
                    <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
                      <div class="inline-flex items-start gap-2">
                        <div class="w-16 h-16 rounded-md flex items-center justify-center p-2">
                            <img
                              src="https://formafusionmg.ams3.digitaloceanspaces.com/formafusionmg/img/modules/${img_module}"
                              alt="" class="w-full h-auto object-cover">
                        </div>
                        <div class="flex flex-col">
                          <p class="text-lg font-semibold text-gray-700">${module || "non assigné"}
                          </p>
                          <p class="text-sm text-gray-400">Client : <span
                              class="text-sm text-gray-500"> ${nameEtp || "non assigné"} </span> 
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
                          <p class="text-base text-gray-500">${date}</p>
                        </div>
                      </div>
                      <div class="inline-flex items-start gap-x-2">
                        <div class="w-[32%] inline-flex items-center justify-end">
                        <p class="text-sm text-gray-400">De :</p>
                        </div>
                        <div class="w-[66%] inline-flex items-center justify-start">
                          <p class="text-base text-gray-500">${start} à ${end}</p>
                        </div>
                      </div>                

                      <div class="inline-flex items-start gap-x-2">
                        <div class="w-[32%] inline-flex items-center justify-end">
                          <p class="text-base text-gray-400">Apprenant(s) :</p>
                        </div>
                        <div class="w-[66%] inline-flex items-center justify-start">
                          <p class="text-base text-gray-500">${nb_appr || "non assigné"}   </p>
                        </div>
                      </div>
       
                    </div>
                  </div>
                </div>
          
          `    ;

        args.html = bubbleHtml2;
      }
    }
  }),

  onBeforeCellRender: args => {
    dpAnnuaire.loadingStart({ delay: 100, text: "Veuillez patienter ...", block: true });
    if (args.cell.start.getDayOfWeek() === 6 || args.cell.start.getDayOfWeek() === 0) {
      args.cell.backColor = "white";
    }
  },

  onEventFilter: (args) => {
    const text = extractText(args.e.data.html);
    let textFound;
    const input = document.querySelector("#filter");

    // Ajout du filtre par statut si nécessaire
    if (args.filter.idFormateur && args.e.data && args.e.data.prenom_form && args.e.data.prenom_form.length > 0) {
      const idForm_filter = args.filter.idFormateur;
      const idFormateur_filter = args.e.data.prenom_form[0]?.idFormateur || 0;
      args.visible = idForm_filter === idFormateur_filter;
    }
    else if (args.filter.status) {
      args.visible = args.e.data.statut === args.filter.status;
    }
    else if (args.filter.etat) { // filtre pour formation ou opportunité
      args.visible = args.e.data.etat === args.filter.etat;
    }
    else if (input.value) {
      //console.log('!text n existe pas...',text)
      textFound = text.toUpperCase().indexOf(args.filter.toUpperCase()) > -1;
      if (!textFound) {
        args.visible = false;
      }
    }
  },

  onRowFilter: (args) => {  //<=== Fonction pour filtrer le mois
    const query = args.filterParam.query;
    if (args.row.name.toUpperCase().indexOf(query.toUpperCase()) === -1) {
      args.visible = false;
    }
  },

  onScroll: async args => {
    args.async = true;

    let tabEvents = [];

    tabEvents = [...appAnnuel.getEventData(), ...appAnnuel.getEventDataOpp()]; // Ajout des evenements FORMATIONS et OPPORTUNITES...

    args.events = tabEvents;

    args.loaded();

    loadAllEventsDetail(); // <==Chargements de tous les events dans dpDetail.calendar 

  },

  onTimeRangeSelected: args => {
    dpAnnuaire.clearSelection();
    dpAnnuaire.update();
  },
  
  onTimeRangeDoubleClick: function (args) {
    if (!confirm('Etes-vous sur de modifier cette session?')) {
      args.preventDefault();
    }
  },

  onEventClick: args => {
    const e = args.e;
    const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
    dpAnnuaire.clearSelection();
    dpAnnuaire.update(e);
  }
});
//  menu SEANCE
dpAnnuaire.contextMenu = menu1;
//  menu OPPORTUNITE
dpAnnuaire.onBeforeEventRender = (args) => {
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
  //args.data.barHidden = true;
  args.data.cssClass = "shadow-lg rounded-xl";

  if (args.data.etat === 2) {
    args.data.contextMenu = menu2;
    args.data.cssClass = "shadow-2xl rounded-md ";
  }
  
//  const allEvents = dpAnnuaire.events.list;
//   const opportunites = allEvents.filter(evnt => evnt.etat === 2);
//  // console.log("LIST of OPPORTUNITY ==>", opportunites);
//   opportunites.forEach(e =>{
//     if (e.idCalendar === null || e.idCalendar === "") {
//     e.backColor = "rgba(246, 241, 216, 0.5)";
//    // dpAnnuaire.events.update(e);
//     }
//     else{
//       e.backColor = "rgba(204, 229, 244, 0.63)";
//     }
//   })
};

const setColorProject = {
  // barColor(i) {
  //   const colors = ["#3c78d8", "#6aa84f", "#f1c232", "#cc0000","#a4c2f4", "#b6d7a8", "#ffe599", "#ea9999"];
  //   return colors[i % 8];
  // },
  // barBackColor(i) {
  //     const colors = ["#a4c2f4", "#b6d7a8", "#ffe599", "#ea9999","#3c78d8", "#6aa84f", "#f1c232", "#cc0000"];
  //     return colors[i % 8];
  // }
  barColor(i) {
    const colors = ["#D3F8E2", "#E4C1F9", "#FB62F6", "#F694C1", "#EDE7B1", "#FAC9B8", "#A9DEF9", "#F26430", "#6761A8", "#F2FF49"];
    //const colors = [];
    // for( let i=1;i<9;i++)
    // {
    //   colors[i] = this.getRandomColor();
    // }
    return colors[i % 8];
  },
  barBackColor(i) {
    const colors = ["#D3F8E2", "#E4C1F9", "#FB62F6", "#F694C1", "#EDE7B1", "#FAC9B8", "#A9DEF9", "#F26430", "#6761A8", "#F2FF49"];
    //   const colors = [];
    //   for( let i=1;i<9;i++)
    //   {
    //     colors[i] = this.getRandomColor();
    //   }
    return colors[i % 8];
  },

  getRandomColor() {
    return `#${Math.floor(Math.random() * 16777215).toString(16)}`;
  }
}

const appAnnuel = {
  init() {
    this.addEventInputHandlers();
  },

  loadResource() {

    const resources = [];

    const startDate = DayPilot.Date.today().firstDayOfYear();

    for (let i = 0; i < 12; i++) {
      const month = startDate.addMonths(i);
      resources.push({
        name: month.toString("MMMM"),
        start: month,
        end: month.addMonths(1),
      });
    }

    dpAnnuaire.update({
      resources,
      startDate
    });


  },

  setColorHexa(idInvoiceStatus) {
    let color = "";
    if (idInvoiceStatus == 1) { //Brouillon
      color = '#808080';
    } else if (idInvoiceStatus == 2) { //Non Envoyé
      color = '#f472b6';

    } else if (idInvoiceStatus == 3) {//Envoyé
      color = '#06b6d4';
    }
    else if (idInvoiceStatus == 4) {//Payé
      color = '#22d3ee';
    }
    else if (idInvoiceStatus == 5) {//Partiel
      color = '#facc15';
    }
    else if (idInvoiceStatus == 6) {//Impayé
      color = '#ef4444';
    }
    else if (idInvoiceStatus == 7) {//Convertis
      color = '#0891b2';
    }
    else if (idInvoiceStatus == 8) {//Expiré
      color = '#dc2626';
    }
    else if (idInvoiceStatus == 9) {//Annulé
      color = '#f9a08d';
    }
    return color;
  },

  getEventData() {
    const events = [];

    const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
    const detailEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_GROUP_BY_' + idCustomer));
    const objectEvents = detailEvents ? detailEvents : this.getAllEventsGroupBy();

    for (const obj of objectEvents || []) {
      const month = new DayPilot.Date(obj.start).getMonth() + 1;
      const dayStart = new DayPilot.Date(obj.start).getDay();
      const dayEnd = new DayPilot.Date(obj.end).getDay();
      const year = new DayPilot.Date(obj.start).getYear();

      const jsStartDate = new DayPilot.Date(obj.start).getTime();
      const jsEndDate = new DayPilot.Date(obj.end).getTime();

      const dateHourStart = new Date(jsStartDate);
      const dateHourEnd = new Date(jsEndDate);

      const hourStart = dateHourStart.getUTCHours(); // Utilisez getUTCHours pour obtenir l'heure en UTC
      const minuteStart = dateHourStart.getUTCMinutes(); // Utilisez getUTCMinutes pour obtenir les minutes en UTC

      const hourEnd = dateHourEnd.getUTCHours(); // Utilisez getUTCHours pour obtenir l'heure en UTC
      const minuteEnd = dateHourEnd.getUTCMinutes(); // Utilisez getUTCMinutes pour obtenir les minutes en UTC

      const dayFormattedStart = dayStart.toString().padStart(2, '0');
      const dayFormattedEnd = dayEnd.toString().padStart(2, '0');
      const newstart = `${year}-01-${dayFormattedStart}`;
      const newend = `${year}-01-${dayFormattedEnd}`;
      //let date =  `${year}-${month}-${dayFormattedEnd}`;
      const date = `${dayFormattedEnd}/${month}/${year}`;

      const hourDeb = `${hourStart}h${minuteStart}mn`;
      const hourFin = `${hourEnd}h${minuteEnd}mn`;

      events.push({
        start: newstart,
        end: newend,
        startTime: hourDeb,
        endTime: hourFin,
        dateStart: obj.start,// format pour google:yyyy-mm-ddT00:00:00
        dateEnd: obj.end ,
        date: date,
        id: DayPilot.guid(),
        idCalendar:obj.idCalendar,
        idEtp: obj.idEtp,
        etat: obj.idSeance ? 1 : 2, // si etat==1 =>séance, si etat==2 ==>opportunité 
        idSeance: obj.idSeance,
        idProjet: obj.idProjet,
        idFormateur: obj.idFormateur,
        resource: month,
        text: ` REf:  ${obj.reference ?? '  -- '} \n - ${obj.module} - \n ${obj.codePostal}- ${obj.ville} - \n ${obj.nameEtp ? obj.nameEtp : obj.nameEtps ? obj.nameEtps.map(etp => etp.name).join(', ') : '--'}`,  // Ajoutez le texte 
        selected: false,
        height: 100,
        fontColor: "black", //<=== couleur TEXTE...
        imgModule: obj.imgModule,
        module: obj.module,
        nameEtp: obj.nameEtp,
        nameEtps: obj.nameEtps ? [...obj.nameEtps] : [],
        nameCfp: obj.nameCfp,
        statut: obj.statut,
        typeProjet: obj.typeProjet,
        ville: obj.ville,
        prenom_form: obj.prenom_form ? [...obj.prenom_form] : [],
        salle: obj.salle,
        paiement: obj.paiement,
        nb_appr: obj.nb_appr,
        materiels: obj.materiels ? [...obj.materiels] : [],
        codePostal: obj.codePostal,
        reference: obj.reference,
        quartier: obj.quartier,
        idModule: obj.idModule,
        idDossier: obj.idDossier,
        modalite: obj.modalite,
        nb_seances: obj.nb_seances,
        backColor: obj.backColor,

        html: '<div class="w-full ml-1 flex flex-col items-start text-xs mt-2">' +
          ' <span class="px-2 py-1 text-sm bg-pink-100 text-slate-700 rounded-md ">' +

          DayPilot.Util.escapeHtml(obj.module) +
          ' </span>' +
          '<span class="w-full ml-1 flex flex-col bg-grey-100 items-start italic text-xs rounded-md " > Ref: ' +
          `${obj.reference ? DayPilot.Util.escapeHtml(obj.reference) : "--"}  
          </span>`
          + (obj.nameEtp ?
            '<span class="px-3 py-1 text-sm bg-white text-slate-700 rounded-md">' +

            DayPilot.Util.escapeHtml(obj.nameEtp) +
            '</span>'
            :
            '<span class="px-3 py-1 text-sm bg-white text-slate-700 rounded-md">' +

            DayPilot.Util.escapeHtml(obj.nameEtps.map(e => e.name).join(', ')) +
            '</span>') +

          '<span class="px-2 py-1 text-sm bg-pink-100 text-slate-700 rounded-md">' +

          DayPilot.Util.escapeHtml(obj.codePostal) + ':' + DayPilot.Util.escapeHtml(obj.ville) +
          '</span>',

        //  + '</div>',

        barHidden: true,
      });
      dpAnnuaire.loadingStop();
    }
    return events;
  },

  getDetailFacture() {

    console.log('LOAD FACT....!');

    const detailEvents = [];
    const url = `/cfp/factures/getEvents`;
    fetch(url, { method: "GET" }).then(response => response.json())
      .then(data => {
        const objectEvents = (data.factures) ? data.factures : [];
        console.log('LISTE FACTURES LOADING...objectEvents-->', objectEvents);
        let idCustomer;
        if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
          idCustomer = sessionStorage.getItem('ID_CUSTOMER');
        }
        for (const evnt of objectEvents) {
          const formatTotal = Number(evnt.total);
          HTMLDATA = `
        <div class="flex items-start gap-2 bg-white p-4 w-full h-full">
          <div class="flex flex-col">
            <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
              <div class="inline-flex items-center gap-2">
                <div class="flex flex-col ">                         
                 
                    <p class="text-base  text-gray-700"> ${evnt.idNumber || 'non assigné'}
                    </p>
  
                  <span class="flex flex-row flex-wrap items-center divide-x divide-gray-200 gap-2">
                                           
                      <p class="text-sm text-gray-400"> <span
                          class="text-sm text-black rounded-md bg-white-100 ">${evnt.status} </span>
                      </p>
  
                      <p class="text-sm text-gray-500 rounded-md bg-yellow-100 "> ${evnt.nameEtp ?? 'non assigné'} </p>
  
                      <p class="text-sm text-gray-500 rounded-md bg-yellow-100 ">  ${formatTotal ?? 'non assigné'} Ar </p>
  
                  </span>
  
                </div>
              </div>
            </div>
                     
          </div>
        </div>`;

          detailEvents.push({
            idFacture: evnt.idFacture,
            idNumber: evnt.idNumber,
            idEntreprise: evnt.idEntreprise,
            start: evnt.start,
            end: evnt.end,
            nameEtp: evnt.nameEtp,
            total: evnt.total,
            idInvoiceStatus: evnt.idInvoiceStatus,
            status: evnt.status,
            barColor: this.setColorHexa(evnt.idInvoiceStatus),
            barBackColor: this.setColorHexa(evnt.idInvoiceStatus),
          })

        }
        dpAnnuaire.update({ detailEvents });
        sessionStorage.setItem('ACCESS_EVENTS_FACTURE_' + idCustomer, JSON.stringify(detailEvents));


      }).catch(error => { console.error(error) });
    return detailEvents;

  },

  getEventsOpportunities() {
    const detailEvents = [];
    const url = `/cfp/prospection/getEvents`;
    fetch(url, { method: "GET" }).then(response => response.json())
      .then(data => {
        const objectEvents = (data.seances) ? data.seances : [];
        console.log('LISTE PROSPECTION LOADING...objectEvents getAllDetailEvents-->', objectEvents);
        let idCustomer;
        if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
          idCustomer = sessionStorage.getItem('ID_CUSTOMER');
        }
        for (const evnt of objectEvents) {
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
                          class="text-base text-white px-2 rounded-md bg-[${evnt.barColor}] ">${evnt.status || 'non assigné'}</span>
                        </p>               
                                    
                        <p class="text-sm text-gray-400"> <span
                            class="text-sm text-black rounded-md bg-white-100 ">${evnt.nbPersonne} Pers</span>
                        </p>

                        p class="text-sm text-gray-400 "> ${evnt.etp_name || 'non assigné'} </p>
  
                        <p class="text-sm text-gray-500 rounded-md bg-yellow-100 ">${evnt.prix || 'non assigné'} </p>
  
                    </span>
  
                  </div>
                </div>
              </div>
                       
            </div>
          </div>`;

          detailEvents.push({
            start: evnt.start,
            end: evnt.end,
            id: DayPilot.guid(),
            idCfp: evnt.idCfp,
            idOpportunity: evnt.idOpportunity,
            idCalendar: evnt.idCalendar,
            html: HTMLDATA,
            etat:2,
            // resource: evnt.resource,
            title: evnt.text,
            module: evnt.module,
            nameEtp: evnt.etp_name,
            nameCfp: evnt.nameCfp,
            status: evnt.status,
            nb_appr: evnt.nbPersonne,
            imgModule: evnt.imgModule,
            //barColor: setColorStatutRessource(evnt.statut),
           // barColor: evnt.barColor,
           // barBackColor: evnt.barColor,
            backColor:evnt.backColor,

          })

        }
        dpAnnuaire.update({ detailEvents });
        sessionStorage.setItem('ACCESS_EVENTS_PROSPECTS_' + idCustomer, JSON.stringify(detailEvents));

      }).catch(error => { console.error(error) });

    return detailEvents;
  },

  getEventDataOpp() {
    const events = [];

    const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
    const detailEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_PROSPECTS_' + idCustomer));
    const objectEvents = detailEvents ? detailEvents : this.getEventsOpportunities();
    // console.log('OBJECTS EVENTS DATA--> ',objectEvents);
    for (const obj of objectEvents) {
      const month = new DayPilot.Date(obj.start).getMonth() + 1;
      const dayStart = new DayPilot.Date(obj.start).getDay();
      const dayEnd = new DayPilot.Date(obj.end).getDay();
      const year = new DayPilot.Date(obj.start).getYear();

      const dayFormattedStart = dayStart.toString().padStart(2, '0');
      const dayFormattedEnd = dayEnd.toString().padStart(2, '0');
      const newstart = `${year}-01-${dayFormattedStart}`;
      const newend = `${year}-01-${dayFormattedEnd}`;
      //let date =  `${year}-${month}-${dayFormattedEnd}`;
      const date = `${dayFormattedEnd}/${month}/${year}`;

      events.push({
        start: newstart,
        end: newend,
        startDate: obj.start+'T00:00:00',
        endDate: obj.end+'T00:00:00',
        date: date,
        id: DayPilot.guid(),
        etat: obj.idSeance ? 1 : 2, // si etat==1 =>séance, si etat==2 ==>opportunité 
        idOpportunity: obj.idOpportunity,
        idCalendar: obj.idCalendar,
        resource: month,
        text: ` REf:  ${obj.reference ?? '  -- '} \n - ${obj.module} }`,  // Ajoutez le texte 
        fontColor: "black", //<=== couleur TEXTE...
        barColor: obj.barColor,
        barBackColor: obj.barColor,
        backColor: obj.backColor,
        imgModule: obj.imgModule,
        module: obj.module,
        nameEtp: obj.nameEtp,
        nameCfp: obj.nameCfp,
        statut: obj.status,
        nb_appr: obj.nb_appr,
        height: 125,
        remarque:obj.title,
        html: '<div class="w-full ml-1 flex flex-col items-start text-xs mt-2">' +
          ' <span class="px-2 py-1 text-sm bg-pink-100 text-slate-700 rounded-md ">' +

          DayPilot.Util.escapeHtml(obj.module) +
          ' </span>' +
          '<span class="w-full ml-1 flex flex-col bg-grey-100 items-start italic text-xs rounded-md " > Statut: ' +
          `${obj.status ? DayPilot.Util.escapeHtml(obj.status) : "--"}  
          </span>`
          +
          '<span class="px-3 py-1 text-sm bg-white text-slate-700 rounded-md">' +

          DayPilot.Util.escapeHtml(obj.nameEtp) +
          '</span>'
          +

          '<span class="px-2 py-1 text-sm bg-pink-100 text-slate-700 rounded-md">' +

          DayPilot.Util.escapeHtml(obj.nb_appr) + ' personne(s)' +
          '</span>' +

          '<span class="px-2 py-1 text-sm bg-pink-100 text-slate-700 rounded-md">' + 'Remarque:  ' +
          DayPilot.Util.escapeHtml(obj.title) +
          '</span>',


        barHidden: false,
      })
    }
    return events;
  },

  getEventDataFact() {
    const events = [];
    //let ID_CUSTOMER = getIdCustomer();
    const detailEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_FACTURE_' + sessionStorage.getItem('ID_CUSTOMER')));
    const objectEvents = detailEvents ? detailEvents : [];

    for (const obj of objectEvents) {

      const month = new DayPilot.Date(obj.end).getMonth() + 1;
      const dayStart = new DayPilot.Date(obj.end).getDay();
      const dayEnd = new DayPilot.Date(obj.end).getDay();
      //let year = new DayPilot.Date(obj.start).getYear();
      const year = new DayPilot.Date(obj.end).getYear();
      const dayFormattedStart = dayStart.toString().padStart(2, '0');
      const dayFormattedEnd = dayEnd.toString().padStart(2, '0');
      const newstart = `${year}-01-${dayFormattedStart}`;
      const newend = `${year}-01-${dayFormattedEnd}`;
      const date = `${dayFormattedEnd}/${month}/${year}`;
      const formatTotal = Number(obj.total);

      events.push({
        start: newstart,
        end: newend,
        date: date,
        id: DayPilot.guid(),
        idFacture: obj.idFacture,
        reference: obj.idNumber,
        nameEtp: obj.nameEtp,
        nameCfp: obj.nameCfp,
        total: obj.total,
        status: obj.status,
        idInvoiceStatus: obj.idInvoiceStatus,
        resource: month,
        text: ` Fa:  ${obj.idNumber ?? '  -- '} \n - ${obj.nameEtp} - \n ${formatTotal} Ar`,  // Ajoutez le texte 

        barColor: this.setColorHexa(obj.idInvoiceStatus),
        barBackColor: this.setColorHexa(obj.idInvoiceStatus),

        html: '<div class="w-full ml-1 flex flex-col items-start text-xs">' +
          DayPilot.Util.escapeHtml(obj.idFacture) +
          '<br/>'
          +
          '<span class="px-2 py-1 text-sm bg-pink-100 text-slate-700 rounded-md">' +
          '<u> Fa:</u> ' + DayPilot.Util.escapeHtml(obj.idNumber) + '<br/> ' + DayPilot.Util.escapeHtml(obj.nameEtp) +
          '</span>'
          +
          '<span class="px-2 py-1 text-sm bg-white text-slate-700 rounded-md">'
          + formatTotal + ' Ar' +
          '</span>'

          + '</div>',
      })

    }
    console.log(" new Events-->", events);
    return events;
  },

  getAllDetailEvents() {
    //dpAnnuaire.init();
    const detailEvents = [];
    const url = `/cfp/agendas/getEvents`;
    fetch(url, { method: "GET" }).then(response => response.json())
      .then(data => {
        const objectEvents = (data.seances) ? data.seances : [];
        console.log('LISTE SEANCES LOADING...objectEvents getAllDetailEvents-->', objectEvents);
        let idCustomer;
        if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
          idCustomer = sessionStorage.getItem('ID_CUSTOMER');
        }
        //console.log('idCustomer-->', idCustomer);
        for (const evnt of objectEvents) {
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
                                        class="text-sm text-black rounded-md bg-white-100 ">${!(evnt.nameEtp) ? evnt.nameEtps.map(etp => etp.etp_name) : evnt.nameEtp}</span>
                                    </p>
              
                                    <p class="text-sm text-gray-500 rounded-md bg-yellow-100 ">${evnt.salle || 'non assigné'} </p>
              
                                </span>
              
                              </div>
                            </div>
                          </div>
                                   
                        </div>
                      </div>`;

          detailEvents.push({
            start: evnt.start,
            end: evnt.end,
            selected: false,
            id: DayPilot.guid(),
            idEtp: evnt.idEtp,
            idSeance: evnt.idSeance,
            idModule: evnt.idModule,
            idProjet: evnt.idProjet,
            idSalle: evnt.idSalle,
            idCalendar: evnt.idCalendar,
            idFormateur: evnt.idFormateur.map(form => {
              const idFormateur = form.idFormateur;
              return {
                idFormateur: idFormateur,
              };
            }),
            html: HTMLDATA,
            resource: evnt.resource,
            title: evnt.text,
            module: evnt.module,
            salle: evnt.salle,
            ville: evnt.ville,
            typeProjet: evnt.typeProjet,
            prenom_form: evnt.formateurs.map(formateur => {
              let idForm = formateur.idFormateur;
              let prenom_form = formateur.form_firstname;
              let email = formateur.email;
              return {
                idFormateur: idForm ? idForm : 0,
                prenom: prenom_form ? prenom_form : 'vide',
                email: email ? email : 'email non assigné',
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
            nameCfp: evnt.nameCfp,
            paiement: evnt.paiementEtp,
            materiels: evnt.materiels.map(mat => {
              const nameMateriel = mat.prestation_name;
              return { name: nameMateriel, };
            }
            ),
            statut: evnt.statut,
            nb_appr: (evnt.typeProjet == 'Intra') ? evnt.apprCountIntra : evnt.apprCountInter,
            imgModule: evnt.imgModule,
            codePostal: evnt.codePostal,
            reference: evnt.reference,
            height: 220,
            //barColor: setColorStatutRessource(evnt.statut),
            barColor: this.setColorStatutRessource(evnt.statut),
            barBackColor: this.setColorStatutRessource(evnt.statut),
            quartier: evnt.quartier,
            modalite: evnt.modalite,
          })

        }
        dpAnnuaire.update({ detailEvents });
        sessionStorage.setItem('ACCESS_EVENTS_DETAILS_' + idCustomer, JSON.stringify(detailEvents));

      }).catch(error => { console.error(error) });

    return detailEvents;
  },

  getAllEventsGroupBy() {
    let detailEvents = [];
    const url = `/cfp/agendas/getEventsGroupBy`;
    fetch(url, { method: "GET" }).then(response => response.json())
      .then(data => {

        dpAnnuaire.loadingStart({ delay: 100, text: "Veuillez patienter ...", block: true });

        let objectEvents = (data.seances) ? data.seances : [];
        console.log('LISTE SEANCES LOADING GROUP BY ...objectEvents getAllDetailEvents-->', objectEvents);
        let idCustomer;
        if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
          idCustomer = sessionStorage.getItem('ID_CUSTOMER');
        }
        //console.log('idCustomer-->', idCustomer);
        for (const evnt of objectEvents) {
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
                                        class="text-sm text-black rounded-md bg-white-100 ">${!(evnt.nameEtp) ? evnt.nameEtps.map(etp => etp.etp_name) : evnt.nameEtp}</span>
                                    </p>
              
                                    <p class="text-sm text-gray-500 rounded-md bg-yellow-100 ">${evnt.salle || 'non assigné'} </p>
              
                                </span>
              
                              </div>
                            </div>
                          </div>
                                   
                        </div>
                      </div>`;

          detailEvents.push({
            start: evnt.start,
            end: evnt.end,
            selected: false,
            idEtp: evnt.idEtp,
            id: DayPilot.guid(),
            idSeance: evnt.idSeance,
            idModule: evnt.idModule,
            idProjet: evnt.idProjet,
            idSalle: evnt.idSalle,
            idCalendar: evnt.idCalendar,
            idFormateur: evnt.idFormateur.map(form => {
              const idFormateur = form.idFormateur;
              return {
                idFormateur: idFormateur,
              };
            }),
            html: HTMLDATA,
            resource: evnt.resource,
            title: evnt.text,
            module: evnt.module,
            salle: evnt.salle,
            ville: evnt.ville,
            typeProjet: evnt.typeProjet,
            prenom_form: evnt.formateurs.map(formateur => {
              let idForm = formateur.idFormateur;
              let prenom_form = formateur.form_firstname;
              let email = formateur.email;
              return {
                idFormateur: idForm ? idForm : 0,
                prenom: prenom_form ? prenom_form : '',
                email: email ? email : '',
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
            nameCfp: evnt.nameCfp,
            paiement: evnt.paiementEtp,
            materiels: evnt.materiels.map(mat => {
              const nameMateriel = mat.prestation_name;
              return { name: nameMateriel, };
            }
            ),
            statut: evnt.statut,
            nb_appr: (evnt.typeProjet == 'Intra') ? evnt.apprCountIntra : evnt.apprCountInter,
            imgModule: evnt.imgModule,
            codePostal: evnt.codePostal,
            reference: evnt.reference,
            height: 220,
            //barColor: setColorStatutRessource(evnt.statut),
           // barColor: this.setColorStatutRessource(evnt.statut),
            //barBackColor: this.setColorStatutRessource(evnt.statut),
            backColor: evnt.backColor,
            quartier: evnt.quartier,
            modalite: evnt.modalite,
            nb_seances: evnt.nb_seances,
          })

        }
        dpAnnuaire.update({ detailEvents });
        sessionStorage.setItem('ACCESS_EVENTS_GROUP_BY_' + idCustomer, JSON.stringify(detailEvents));

      }).catch(error => { console.error(error) });

    return detailEvents;
  },

  loadAllEventsAnnuaire() {
    const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
    const objectEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_GROUP_BY_' + idCustomer));
    console.log('AFTER objectEvents ANNUAIRE==>', objectEvents)
    dpAnnuaire.init();
    dpAnnuaire.events.list = objectEvents;
    dpAnnuaire.update({ objectEvents });
    // dpAnnuaire.loadingStop();
    return objectEvents;

  },

  /**AJOUTER des évenements CLIC||CHANGE des boutons status_project */
  addEventInputHandlers() {  
    const dropdownForm = $('#dropdownFormAnnuel');  
    const monthSelector = $('#monthSelector');
    const vueSelector = $('#vueSelector');
 
    monthSelector.ready(function () {
      document.querySelectorAll("select").forEach(function (el) {
        el.addEventListener("change", function () {
          const selectedMonth = this.value;
          console.log('selectedMonth-->',selectedMonth);
          if (selectedMonth == "Tous" || selectedMonth == "tabDetail" || selectedMonth == "tabAnnuaire" || selectedMonth == "tabFormateur") {
            dpAnnuaire.rows.filter(null);
            document.getElementById('monthSelector').selectedIndex = 0;
          }
          else {
            dpAnnuaire.rows.filter(
              { query: selectedMonth, }
            );
          }
        })
      })
    })

    vueSelector.ready(function () {
      document.querySelectorAll("select").forEach(function (el) {
        el.addEventListener("change", function () {
          let selectedVue = this.value;
          console.log('selectedVue==>',selectedVue);
          if (selectedVue == "Formations") {
            dpAnnuaire.rows.filter(null);
            dpAnnuaire.events.filter({
              etat: 1 //etat 1=>"Formations"
            })
            document.getElementById('monthSelector').selectedIndex = 0;
          }
          else if (selectedVue == "Opportunite") {
            dpAnnuaire.rows.filter(null);
            dpAnnuaire.events.filter({
              etat: 2 //etat 2=>"Opportunités"
            })
            document.getElementById('monthSelector').selectedIndex = 0;
          }
          else if (selectedVue == "Toutes") {
            dpAnnuaire.rows.filter(null);
            dpAnnuaire.events.filter(null);
          }
        })
      })
    })

    /**Ajout evenement FORMS ou STATUT... */
    dropdownForm.ready(function () {
      document.querySelectorAll('li').forEach(function (el) {
        el.addEventListener("click", function (ev) {

          console.log('Clic sur li avec ID:', ev);

          const idFormateur = Number(ev.target.id);
          // const prenom = ev.target.innerText;

          console.log('idFormateur ==>', idFormateur);

          if (idFormateur) {
            dpAnnuaire.events.filter({
              idFormateur: idFormateur,
              etat: 1 //etat 1=>"Formations"
            })
          }
          else {
            const status = ev.target.id;
            dpAnnuaire.events.filter({
              status: status
            })
          }
        })
      })
    });
  },

  setColorStatutRessource(statut) {
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
  },

  setColorProjet(typeProjet) {
    let color = "";
    if (typeProjet == 'Intra') {
      color = '#1565c0';
    } else if (typeProjet == 'Inter') {
      color = '#7209b7';
    }
    return color;
  },

  stopLoading() {
    dpAnnuaire.loadingStop();
  },

}

const appWidth = {
  elements: {
    cellWidth: document.querySelector("#cellwidth"),
    label: document.querySelector("#label"),
    filter: document.querySelector("#filter"),
    clear: document.querySelector("#clear"),
    tous: document.querySelector("#tous"),
    touStatut: document.querySelector("#touStatut"),
    subdropForm: document.querySelector("#subdropForm"),
    subdropStatus: document.querySelector("#subdropStatus"),
  },

  init() {

    this.elements.filter.addEventListener("keyup", function () {
      var query = this.value;
      dpAnnuaire.events.filter(query); // see dp.onEventFilter
    });

    this.elements.clear.addEventListener("click", handleClearAction);

    this.elements.tous.addEventListener("click", handleClearAction);

    this.elements.touStatut.addEventListener("click", handleClearAction);

    document.getElementById('monthSelector').selectedIndex = 0;// Met le Select à "Tous"

    this.addEventHandlers();

  },

  addEventHandlers() {
    appWidth.elements.cellWidth.addEventListener("input", (ev) => {
      const cellWidth = parseInt(appWidth.elements.cellWidth.value);
      const start = dpAnnuaire.getViewPort().start;

      dpAnnuaire.update({
        cellWidth: cellWidth,
        scrollTo: start
      });
      appWidth.elements.label.innerText = cellWidth;
    });
  }
};

appWidth.init();


//Fonction pour générer le PDF
function createPdfAsBlob() {
  var pdf = new jsPDF('p', 'mm', 'a4');

  // Ajouter un titre
  pdf.text(105, 15, 'Calendrier');

  // Exporter le calendrier sous forme d'image
  var image = dpAnnuaire.exportAs("jpeg", {
    scale: 2,
    quality: 0.95
  });

  // Obtenir les dimensions de l'image
  var dimensions = image.dimensions();

  // Calculer les nouvelles dimensions pour s'adapter à la page A4
  var ratio = 210 / dimensions.width;
  var newWidth = 210;
  var newHeight = dimensions.height * ratio;

  // Ajouter l'image au PDF
  pdf.addImage(image.toDataUri(), 'JPEG', 10, 25, newWidth, newHeight);

  return pdf.output('blob');
}

// Fonction pour télécharger le PDF
function downloadPdf() {
  var blob = createPdfAsBlob();
  DayPilot.Util.downloadBlob(blob, "calendrier.pdf");
}

function getDrawer() {
  var drawer = $("#drawer_content_export");
  console.log('CLIC...');
  var element = dpAnnuaire.exportAs("png",
    {
      area: 'range',
      resourceFrom: 1,
      resourceTo: 11,

    }).toElement();
  drawer.html('').append(`<x-drawer-export-agenda></x-drawer-export-agenda>`);

  $("#export").html('').append(element);
}

$(document).ready(function () {
  $("#export-button").click(function (ev) {
    ev.preventDefault();
    //var area = $("#area").val();
    var element = dpAnnuaire.exportAs("png",
      {
        area: 'range',
        resourceFrom: 1,
        resourceTo: 11,

      }).toElement();

    console.log('Element', element);
    var drawer = $("#drawer_content_export");

    $("#export").html('').append(element);
    //drawer.html('').append(`<x-drawer-export-agenda></x-drawer-export-agenda>`);

  });

  const subdropMonthStart = document.getElementById('subdropMonthStart');
  const subdropMonthEnd = document.getElementById('subdropMonthEnd');


  var selectedOptionIdStart = '1';
  var selectedOptionIdEnd = '12';

  subdropMonthStart.addEventListener('change', function (e) {
    selectedOptionIdStart = $(this).find('option:selected').attr('id');
  });

  subdropMonthEnd.addEventListener('change', function (e) {
    selectedOptionIdEnd = $(this).find('option:selected').attr('id');
  });



  $("#download-button").click(function (ev) {
    ev.preventDefault();

    if (selectedOptionIdStart && selectedOptionIdEnd) {
      dpAnnuaire.exportAs("jpeg",
        {
          area: 'range',
          quality: 5.95,
          scale: 0.8,
          resourceFrom: Number(selectedOptionIdStart),
          resourceTo: Number(selectedOptionIdEnd),

        }).print();

      // dpAnnuaire.loadingStop();

    }
    //downloadPdf();
  });
});