document.addEventListener(
  "DOMContentLoaded",
  getAllSeancesDetailJson(),
  true
);

function setColorStatut(statut) {

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

function getAllSeancesDetailJson() {
  //<======== Fonction permettant de récuperer les "data" des details d'une seance en format JSON
  let dataEvnts;
  const detailEvents = [];
  const item = 0;
   
  const url = `/agendaForms/getEvents`;
  fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      dataEvnts = data.seances;
    
      if(sessionStorage.getItem('IDCUSTOMER_ETP')!== null)
      {
         idCustomer = sessionStorage.getItem('IDCUSTOMER_ETP');
      }  
      for (const evnt of dataEvnts) {      
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
                                        class="text-base text-white px-2 rounded-md bg-[${setColorProjet(evnt.typeProjet )}] ">${evnt.typeProjet || 'non assigné'}</span>
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
                                  <p class="text-sm text-gray-400">Matériel :</p>
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
            barColor: setColorStatut(evnt.statut),
          })
       
            sessionStorage.setItem('ACCESS_EVENTS_DETAILS', JSON.stringify(detailEvents))
            //Mettre à jour Daypilot
            if(sessionStorage.getItem('ACCESS_EVENTS_DETAILS') )
            { 
              dpForm.events.list = detailEvents;
              dpForm.update();
            }
        }

      }).catch((error) => {
      console.error(error);
    });

  return detailEvents;
    
}

const dpForm = new DayPilot.Calendar("dpForm", {
/***************************************OPTIONS DAYPILOT******************************************************************* */    
  locale: "fr-fr",
  startDate: DayPilot.Date.today(),
  viewType: "Week",
  businessBeginsHour: 7,
  dayBeginsHour: 7,
  showEventStartEnd: false,
  scrollLabelsVisible: true,
  timeRangeSelectingStartEndEnabled: true,
  // eventDeleteHandling: "Update",
  // onEventDeleted: (args) => {
  //   console.log("Event deleted: " + args.e.text());
  // },
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

  contextMenu: new DayPilot.Menu({
    items: [
      {
        //Dynamic...
        text: "Ouvrir ce projet",
        onClick: (args) => {
          const e = args.source;
          const idProjet = DayPilot.Util.escapeHtml(e.data.idProjet);
          window.location.replace(`/projetsForm`);
        },
      }
    ],
  }),

  bubble: new DayPilot.Bubble({
    onLoad: (args) => {
      const e = args.source;
      console.log(e.data)
      const module = DayPilot.Util.escapeHtml(e.data.module);
      const salle = DayPilot.Util.escapeHtml(e.data.salle);
      const ville = DayPilot.Util.escapeHtml(e.data.ville); 
      const nameEtp = DayPilot.Util.escapeHtml(e.data.nameEtp);
      const tabNameEtp = [...e.data.nameEtps];
      const color = DayPilot.Util.escapeHtml(e.data.barColor);
      const statut = DayPilot.Util.escapeHtml(e.data.statut);
      const paiement = DayPilot.Util.escapeHtml(e.data.paiement);
      const materiels = [...e.data.materiels];
      const nb_appr = DayPilot.Util.escapeHtml(e.data.nb_appr);
      const typeProjet =  DayPilot.Util.escapeHtml(e.data.typeProjet);
      const colors = setColorProjet(typeProjet);
      const img_module = DayPilot.Util.escapeHtml(e.data.imgModule);
      const start = e.start().toString("dd/MM/yyyy");
      const endTime = e.end().toString("h:mm tt");
      const startTime = e.start().toString("h:mm tt");

      const htd = ` <div class="flex items-center gap-2 h-full w-[28em] bg-white p-4">
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
                            class="text-sm text-gray-500">${!nameEtp ? tabNameEtp.map(etp => etp.name) : nameEtp }</span> 
                        </p>
                        <p class="text-sm text-gray-400">Projet : <span
                             class="text-base text-white px-2 rounded-md bg-[${colors}] ">${typeProjet}</span>
                        </p>
                      </div>
                    </div>
                    <div class="flex flex-col ml-3">
                      <div class="px-3 py-1 text-sm bg-[${color}] text-white rounded-md">${statut}</div>
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
      args.html = htd;
    },
  }),
});

dpForm.init();

