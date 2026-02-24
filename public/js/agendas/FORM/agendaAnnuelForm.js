
document.addEventListener('DOMContentLoaded', (e) => {
    getIdCustomer()
   // getAllDetailEvents()
    dpAnnuaire.init() ;
    e.stopPropagation()
  }, true)
  
   function getIdCustomer() {
    let idCustomer;
    const url = '/agendaForms/customer';
     fetch(url, { method: "GET" }).then(response => response.json())
      .then(data => {
        idCustomer = data.idCustomer;
        sessionStorage.setItem('ID_CUSTOMER',idCustomer);
  
      }).catch(error => { console.error(error) });
    return idCustomer;
  }
  
  function getDaysInMonth(year, month) {
    // Créez une date du premier jour du mois suivant
    const date = new Date(year, month + 1, 0);
    
    // Récupérez le jour du mois, qui est le nombre total de jours dans le mois
    return date.getDate();
  }
  function getYear(){
      const startDdate = new Date();
      const year = startDdate.getFullYear();
     return year;
  }
  function calculateHeight(width) {
    // Calculez la hauteur en fonction de la largeur
    // Par exemple, pour une largeur de 1920px, utilisez une hauteur de 600px
    return width < 1920 ? 300 : 600;
  }
  
  window.addEventListener('resize', () => {
    const width = window.innerWidth;
    //console.log('Width Window-->',width)
    dpAnnuaire.update({
      height: calculateHeight(width),
    });
  });
  
  const dpAnnuaire = new DayPilot.Scheduler("dpAnnuaire", {
    /***************************************OPTIONS DAYPILOT******************************************************************* */    
    startDate:`${getYear()}-01-01`,// <==premier jour et mois de l'année 
    scale: "CellDuration",
    cellDuration: 720,
    eventHeight: 30,
    //timeRangeSelectedHandling: "Disabled",
    //crosshairType: "Text",
    eventMoveHandling: "Disabled",
    timeRangeSelectingStartEndEnabled: "Disabled",
    autoRefreshEnabled: true,
    eventDoubleClickHandling: "Update",
    //cellGroupBy: "Month",
      days: 31,
      scale: "Day",
      timeHeaders: [
          {groupBy: "Day", format: "d"}
      ],
      heightSpec: "Content",  
      //height: 500,
      treeEnabled: true,
      resources: [
          {name: "Janvier", id: 1},
          {name: "Février", id: 2},
          {name: "Mars", id: 3},
          {name: "Avril", id: 4},
          {name: "Mai", id: 5},
          {name: "Juin", id: 6},
          {name: "Juillet", id: 7},
          {name: "Août", id: 8},
          {name: "Septembre", id: 9},
          {name: "Octobre", id: 10},
          {name: "Novembre", id: 11},
          {name: "Décembre", id: 12},
      ],
      dynamicLoading: true,
      durationBarVisible: true,
      eventHoverHandling: "Bubble",

      rectangleSelectMode: "Free",
      rectangleSelectHandling: "EventSelect",
      eventClickHandling: "JavaScript",
  
      linkBottomMargin: 20,
/***************************************END OPTION DAYPILOT******************************************************************* */    
      bubble: new DayPilot.Bubble({
        onLoad: (args) => {
          const e = args.source;
         console.log('e=>',e) ;
        const module = DayPilot.Util.escapeHtml(e.data.module);
        const salle = DayPilot.Util.escapeHtml(e.data.salle);
        const ville = DayPilot.Util.escapeHtml(e.data.ville);
  
        //const tabForm = [...e.data.prenom_form];
        const nameEtp = DayPilot.Util.escapeHtml(e.data.nameEtp);
        const tabNameEtp = [...e.data.nameEtps];
        const colorStatus = setColorStatutRessource(e.data.statut)
        const statut = DayPilot.Util.escapeHtml(e.data.statut);
        const paiement = DayPilot.Util.escapeHtml(e.data.paiement);
        const materiels = [...e.data.materiels];
        const nb_appr = DayPilot.Util.escapeHtml(e.data.nb_appr);
        const typeProjet =  DayPilot.Util.escapeHtml(e.data.typeProjet);
        const colors = setColorProjet(typeProjet);
        const img_module = DayPilot.Util.escapeHtml(e.data.imgModule);
        const start = e.start().toString("dd/MM/yyyy");
        const date = DayPilot.Util.escapeHtml(e.data.date);
        // const endTime = e.end().toString("h:mm tt");
        // const startTime = e.start().toString("h:mm tt");
        const startTime = (e.data.startTime);
        const endTime = (e.data.endTime);
  
  
          const bubbleHtml = `
          <div class="flex items-center gap-2 h-full w-[28em] bg-white p-4">
                  <div class="flex flex-col w-full">
                    <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
                      <div class="inline-flex items-start gap-2">
                        <div class="w-16 h-16 rounded-md flex items-center justify-center p-2">
                            <img
                              src="/img/modules/${img_module}"
                              alt="" class="w-full h-auto object-cover">
                        </div>
                        <div class="flex flex-col">
                          <p class="text-lg font-semibold text-gray-700">${module || "non assigné"
          }
                          </p>
                          <p class="text-sm text-gray-400">Client : <span
                              class="text-sm text-gray-500"> ${!nameEtp ? tabNameEtp.map(etp => etp.name) : nameEtp } </span> 
                          </p>
                          <p class="text-sm text-gray-400">Projet : <span
                              class="text-base text-white px-2 rounded-md bg-[${colors}] ">${typeProjet}</span>
                          </p>
                        </div>
                      </div>
                      <div class="flex flex-col ml-3">
                        <div class="px-3 py-1 text-sm bg-[${colorStatus}] text-white rounded-md">${statut}</div>
                      </div>
                    </div>
                    <div class="flex flex-col w-full">
                      <div class="inline-flex items-start gap-x-2">
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
                          <p class="text-base text-gray-400">Matériel(s) :</p>
                        </div>
                        <div class="w-[66%] inline-flex items-center justify-start">
                          <p class="text-base text-gray-500">${materiels.map(m =>m.name) || 'non assigné'}</p>
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
          // if the event object doesn't specify "bubbleHtml" property
          // this onLoad handler will be called to provide the bubble HTML
          args.html = bubbleHtml;
        }
      }),
  
      contextMenu: new DayPilot.Menu({
        items: [
          {
            //Dynamic...
            text: "Ouvrir ce projet",
            onClick: (args) => {
              const e = args.source;
              const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
              window.location.replace(`/projetsForm/${idProjet}/detailForm`);
            },
          },
          {
            text:'Highlight',
            onClick:(args) => {
              const e = args.source;
              const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
              const links = [];
              //dpAnnuaire.update();
              //Recupère tous les projets de mème ID...
              const allEvents = dpAnnuaire.events.list;
              let selectedEvents = [];
              let unSelectedEvents = [];
                  selectedEvents = allEvents.filter(evnt => evnt.idProjet==idProjet );
                  unSelectedEvents = allEvents.filter(evnt => evnt.idProjet!=idProjet );
              console.log('selectedEvents-->',selectedEvents);
             const listId =[];
  
             if (unSelectedEvents.length > 0 )
              {
                unSelectedEvents.forEach(e => {
                  listId.push(e.idSeance); 
                console.log(e);
                if(e.selected == false )
                  {            
                    //e.backColor = "rgba(190, 187, 187,0.5)";
                    e.selected = true;  
                    console.log('links---->',links)
                    dpAnnuaire.events.update(e);                            
                 }
                }) 
             }
              if (selectedEvents.length > 0 ) {            
                selectedEvents.forEach(e => {
                  listId.push(e.idSeance);
               
                  console.log(e);
                  if(e.selected == false ){               
                    e.backColor = "rgba(254, 207, 1,0.5)";
                    e.selected = true;  
                     console.log('links---->',links)
                     dpAnnuaire.events.update(e);              
                   }
                  else
                  {
                    console.log('SELECTED TRUE');         
                    dpAnnuaire.clearSelection();
                    dpAnnuaire.update();
                  }  
  
                  // for (let i =0; i<listId.length-1; i++ )
                  //   //for(let i in listId)
                  //    {
   
                  //      links.push({
                  //        from: listId[i],
                  //        to: listId[i+1] ,
                  //        type: "FinishToStart",
                  //        //text: "Link text",
                  //        color: "green",
                  //        //textAlignment: "left",
                  //        bubbleHtml: "Bubble text",
                  //      });
                  //    } 
                  // dpAnnuaire.update({links})
  
  
                });                  
              }
            }
          }
         
        ],
        
      }),
  
      onScroll: async args => {
          args.async = true;
        console.log('BeforeEvent-->',args);
          args.events = app.getEventData();
        //  args.events = app.getEventData(args.viewport.start, args.viewport.end);
          console.log('App-->',args.events);
         // args.events = app.getEventData();
          args.loaded();
         // dpAnnuaire.update();
          
      },
      onTimeRangeDoubleClick: function(args) {
        if(!confirm('Etes-vous sur de modifier cette session?')) {
          args.preventDefault();
        }
      },  
      onEventClick: args =>{
        const e = args.e;      
        const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
        console.log('On clic...',idProjet)
        dpAnnuaire.clearSelection();
        dpAnnuaire.update(e);
      }
  });

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
      const colors = ["#D3F8E2", "#E4C1F9", "#FB62F6", "#F694C1","#EDE7B1", "#FAC9B8", "#A9DEF9", "#F26430", "#6761A8", "#F2FF49"];
      return colors[i % 8];
    },
    barBackColor(i) {
        const colors = ["#D3F8E2", "#E4C1F9", "#FB62F6", "#F694C1","#EDE7B1", "#FAC9B8", "#A9DEF9", "#F26430", "#6761A8", "#F2FF49"];
        return colors[i % 8];
    }
  }
  
  const app = {
      getEventData() {
          const events =[];
          const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
          const detailEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_DETAILS_' + idCustomer));
          const objectEvents =  detailEvents? detailEvents : getAllDetailEvents() ;
  
            for( const obj of objectEvents ){
              const month = new DayPilot.Date(obj.start).getMonth()+1;
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
              const date =  `${dayFormattedEnd}/${month}/${year}`;
  
              const hourDeb = `${hourStart}h${minuteStart}mn`;
              const hourFin = `${hourEnd}h${minuteEnd}mn`;
              //console.log('obj---->',obj);
              events.push({
                    start: newstart,
                    end: newend,
                    startTime:  hourDeb,
                    endTime:    hourFin,
                    date:       date,
                    id: DayPilot.guid(),
                    idProjet:   obj.idProjet,
                    resource:   month,
                    //text:       obj.module,
                    selected: false,
                    fontColor :"black", //<=== couleur TEXTE...
                    //fontColor :setColorStatutRessource(obj.statut),
                      barColor:  setColorProject.barColor(obj.idProjet),
                      barBackColor:setColorProject.barBackColor(obj.idProjet),
                    imgModule:  obj.imgModule,
                    module:     obj.module,
                    nameEtp:    obj.nameEtp,
                    nameEtps:[...obj.nameEtps],
                    statut:     obj.statut,
                    typeProjet: obj.typeProjet,
                    ville:      obj.ville,
                   // prenom_form: [...obj.prenom_form],
                    salle:      obj.salle,
                    paiement:   obj.paiement,
                    nb_appr:    obj.nb_appr,
                    materiels:   [...obj.materiels],
                    html:'<div class="w-full ml-1 inline-flex items-center ">' +
                    '<strong>' + DayPilot.Util.escapeHtml(obj.module) + '</strong>' +
                    '</div>',
              })
            }      
          return events;
      }
     
  }
   function getAllDetailEvents(){
    const detailEvents = [];
                  const url = `/agendaForms/getEvents`;
             fetch(url, { method: "GET" }).then(response => response.json())
                  .then(data => {
                    const objectEvents = (data.seances)? data.seances : [];
                      console.log('LISTE SEANCES FORMATEURS LOADING...objectEvents-->', objectEvents);
                   let idCustomer;  
                    if(sessionStorage.getItem('ID_CUSTOMER')!== null)
                    {
                       idCustomer = sessionStorage.getItem('ID_CUSTOMER');
                    }  
          
                    for (const evnt of objectEvents) {  
                   
                        HTMLDATA = `<div class="flex items-start gap-2 bg-white p-4 w-full h-full">
                        <div class="flex flex-col">
                          <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
                            <div class="inline-flex items-center gap-2">
                              <div class="flex flex-col">
                                <p class="text-lg font-semibold text-gray-700"> ${evnt.module || 'non assigné'}
                                </p>
                                <p class="text-sm text-gray-400">Client : <span
                                    class="text-sm text-gray-500">${!(evnt.nameEtp)? evnt.nameEtps.map(etp => etp.etp_name) : evnt.nameEtp }</span>
                                </p>
                                <p class="text-sm text-gray-400">Projet : <span
                                    class="text-base text-white px-2 rounded-md bg-[${setColorStatutRessource(evnt.typeProjet )}] ">${evnt.typeProjet || 'non assigné'}</span>
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
                              <p class="text-sm text-gray-500">${evnt.paiement || 'non assigné'}</p>
                            </div>
                            <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                              <p class="text-sm text-gray-400">Matériel(s) :</p>
                              <p class="text-sm text-gray-500">1 Ordinateur</p>
                            </div>
                            
                            <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                              <p class="text-sm text-gray-400">Apprenants :</p>
                              <p class="text-sm text-gray-500">${ (evnt.typeProjet=='Intra')? evnt.apprCountIntra: evnt.apprCountInter }</p>
                            </div>
                          </div>
                        </div>
                      </div>`;
                        detailEvents.push({
                          start: evnt.start,
                          end: evnt.end,
                          selected: false,
                          idSeance: evnt.idSeance,
                          idModule: evnt.idModule,
                          idProjet: evnt.idProjet,
                          idSalle: evnt.idSalle,
                          idCalendar: evnt.idCalendar,
                          html: HTMLDATA,
                          resource: evnt.resource,
                          title: evnt.text,
                          module: evnt.module,
                          salle: evnt.salle,
                          ville: evnt.ville,
                          typeProjet:evnt.typeProjet,
                        //   prenom_form: evnt.formateurs.map(formateur => {
                        //     let idForm = formateur.idFormateur;
                        //     let prenom_form =  formateur.form_firstname;
                        //     return {
                        //       idFormateur:  idForm,
                        //       prenom:       prenom_form,
                        //     };
                        // }
                     // ),
                          nameEtp: evnt.nameEtp,
                          nameEtps: evnt.nameEtps.map( etp =>{
                            const nameEtp = etp.etp_name;
                            return {
                              name: nameEtp,
                            };
                          }),
                          materiels: evnt.materiels.map(mat=>{
                            const nameMateriel =  mat.prestation_name;
                            return { name:nameMateriel, };
                            }),
                          paiement: evnt.paiement,
                          statut: evnt.statut,
                          nb_appr: (evnt.typeProjet=='Intra')? evnt.apprCountIntra : evnt.apprCountInter ,
                          imgModule: evnt.imgModule, 
                          height: 20,
                          barColor: setColorStatutRessource(evnt.statut),
                        })
                     
                          dpAnnuaire.update({detailEvents});
                          sessionStorage.setItem('ACCESS_EVENTS_DETAILS_' + idCustomer, JSON.stringify(detailEvents));
                                          
                    }
              
                  }).catch(error => { console.error(error) });
              return detailEvents;
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
  
  function setColorProjet(typeProjet){
    let color = "";
    if(typeProjet=='Intra'){
       color = '#1565c0';
    } else  if(typeProjet=='Inter'){
      color ='#7209b7';
    
    } else  if(typeProjet=='Interne'){
      color ='#7F055F';
    }
    return color;
  }
  
const appWidth = {
  elements: {
      cellWidth: document.querySelector("#cellwidth"),
      label: document.querySelector("#label")
  },
  init() {
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
  