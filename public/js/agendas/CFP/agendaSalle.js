document.addEventListener('DOMContentLoaded', (e)=>{
     
  getIdCustomer();  

  dpSalles.init();    

    salles.getResourceSalles();

    salles.loadAllEventsSalles();

},true);

const dpSalles = new DayPilot.Scheduler("dpSalles", {
  /*************************************** OPTIONS DAYPILOT******************************************************************* */    
    cellWidthSpec: "Fixed",
    cellWidth: 20,
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

    treeEnabled: true,
    dynamicLoading: true,

   /***************************************END OPTION DAYPILOT******************************************************************* */    
   onBeforeEventRender: function(args) {
    args.data.areas = [
      {
        right: 11,
        top: 4,
        width: 4,
        height: 2,
        icon: "fas fa-chevron-down",
        cssClass: "event-action",
      },
    ];
  },
  
  onScroll: async args => {
    args.async = true;
  
    args.events = salles.getEventData();  
   
    args.loaded();  
  },

  onEventResized: (args) => {
      args.control.message("Vous avez modifié la date d'un évènement. ");
    },
    eventClickHandling: "Disabled",
    eventHoverHandling: "Bubble",
    bubble: new DayPilot.Bubble({
      onLoad: (args) => {
        const e = args.source;
        
        const module = DayPilot.Util.escapeHtml(e.data.module);
        const salle = DayPilot.Util.escapeHtml(e.data.salle);
        const ville = DayPilot.Util.escapeHtml(e.data.ville);
        const tabform = [...e.data.prenom_form];
        const nameEtp = DayPilot.Util.escapeHtml(e.data.nameEtp);
        const tabNameEtp = [...e.data.nameEtps];
        const paiement = DayPilot.Util.escapeHtml(e.data.paiement);
        const typeProjet =  DayPilot.Util.escapeHtml(e.data.typeProjet);
        const colors = setColorProjet(typeProjet);
    
        const text = DayPilot.Util.escapeHtml(e.data.title);
        const start = e.start().toString("dd/MM/yyyy");
    
        const endTime = e.end().toString('h:mm tt');
        const startTime = e.start().toString('h:mm tt');
        const img_module =  DayPilot.Util.escapeHtml(e.data.imgModule);
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
                  <p class="text-lg font-semibold text-gray-700">${module || 'non assigné'}
                  </p>
                  <p class="text-base text-gray-400">Client : <span
                      class="text-base text-gray-500">${!nameEtp ? tabNameEtp.map(etp => etp.name) : nameEtp }</span>
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
                  <p class="text-base text-gray-500">${tabform.map(f=>f.prenom) || 'non assigné'}</p>
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

const salles = {
      getResourceSalles(){
        const url = '/cfp/agendas/events_resources_agenda';
        fetch(url, { method: "GET" }).then(response => response.json())
         .then(data => {
          console.log('Salle IS-->',data.salleInSitus);
        const ressource_table_salles = [    
            {
                name: "Salles", id: "SL", expanded: true, children: data.salles
            },

            {
                name: "SalleInSitu", id: "IS", expanded: false, children: data.salleInSitus
            },
        
         ];

         dpSalles.resources = ressource_table_salles;
         dpSalles.update();
            
         }).catch(error => { console.error(error) });
       return dpSalles;
      },

      getEventData(){
        const salles =[];
        const salleInSitus = [];
        let allSalle =[];

        const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
        const salleEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_DETAILS_' + idCustomer));
        const objectEvents =  salleEvents ? salleEvents  : this.getAllSalleEvents() ;
        console.log('DONNEE SALLE...',objectEvents);
        for( const obj of objectEvents ){
          salles.push({
            start: obj.start,
            end: obj.end,
            id: DayPilot.guid(),
            idEtp : obj.idEtp,
            idSeance:   obj.idSeance,
            idProjet:   obj.idProjet,
            idCustomer: obj.idCustomer,
            //resource: (obj.salle=='In situ')? 'SL_' + obj.idSalle : 'IS_' + obj.idCustomer,
            resource: 'SL_' + obj.idSalle,
            height: 20,
            idProjet: obj.idProjet,
            idCalendar: obj.idCalendar,
            module: obj.module,
            salle: obj.salle,
            ville: obj.ville,
            typeProjet:obj.typeProjet,
            prenom_form: [...obj.prenom_form],
            nameEtp: obj.nameEtp,
            nameEtps: [...obj.nameEtps],
            paiement: obj.paiement,
            imgModule: obj.imgModule,  
            barColor: obj.barColor,
            backColor: obj.backColor, 
            html : `<div class="flex items-start gap-2 bg-white p-4 w-full h-full">
            <div class="flex flex-col">
              <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
                <div class="inline-flex items-center gap-2">
                  <div class="flex flex-col">
                  </div>
              </div>
            </div>
          </div>`,         
          })
          
          salleInSitus.push({
            start: obj.start,
            end: obj.end,
            id: DayPilot.guid(),
            idEtp : obj.idEtp,
            idSeance:   obj.idSeance,
            idProjet:   obj.idProjet,
            idCustomer: obj.idCustomer,
            resource:'IS_' + obj.idCustomer,
            //resource: 'SL_' + obj.idSalle,
            height: 20,
            idProjet: obj.idProjet,
            idCalendar: obj.idCalendar,
            module: obj.module,
            salle: obj.salle,
            ville: obj.ville,
            typeProjet:obj.typeProjet,
            prenom_form: [...obj.prenom_form],
            nameEtp: obj.nameEtp,
            nameEtps: [...obj.nameEtps],
            paiement: obj.paiement,
            imgModule: obj.imgModule,  
            barColor: obj.barColor,
            backColor: obj.backColor, 
            html : `<div class="flex items-start gap-2 bg-white p-4 w-full h-full">
            <div class="flex flex-col">
              <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
                <div class="inline-flex items-center gap-2">
                  <div class="flex flex-col">
                    <p class="text-lg font-semibold text-gray-700"> ${obj.module || 'non assigné'}
                    </p>
                    <p class="text-sm text-gray-400">Client : <span
                        class="text-sm text-gray-500">${!(obj.nameEtp)? obj.nameEtps.map(etp => etp.etp_name) : obj.nameEtp }</span>
                    </p>
                    <p class="text-sm text-gray-400">Projet : <span
                        class="text-base text-white px-2 rounded-md bg-[${setColorProjet(obj.typeProjet )}] ">${obj.typeProjet || 'non assigné'}</span>
                    </p>
                  </div>
                </div>
              </div>
              <div class="flex flex-col">
                <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                  <p class="text-sm text-gray-400">Lieu :</p>
                  <p class="text-sm text-gray-500">  ${obj.ville || 'non assigné'}</p>
                </div>
                <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                  <p class="text-sm text-gray-400">Salle :</p>
                  <p class="text-sm text-gray-500">${obj.salle || 'non assigné'} </p>
                </div>
                <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                  <p class="text-sm text-gray-400">Financement :</p>
                  <p class="text-sm text-gray-500">${obj.paiementEtp || 'non assigné'}</p>
                </div>
                <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                  <p class="text-sm text-gray-400">Matériel(s) :</p>
                  <p class="text-sm text-gray-500">${obj.materiels.map(mat => mat.name)|| 'non assigné'  }</p>
                </div>
                <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                  <p class="text-sm text-gray-400">Formateur(s) :</p>
                  <p class="text-sm text-gray-500">${ obj.prenom_form.map(formateur => formateur.prenom) || 'non assigné'}</p>
                </div>
                <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                  <p class="text-sm text-gray-400">Apprenants :</p>
                  <p class="text-sm text-gray-500">${ (obj.typeProjet=='Intra')? obj.apprCountIntra: obj.apprCountInter }</p>
                </div>
              </div>
            </div>
          </div>`,
     
          
          })

          allSalle = [...salles,...salleInSitus];

          }  
          console.log('ALL_SALLE',allSalle);
            return allSalle;
      },
      
      getAllSalleEvents(){
        const salles = [];
        const salleInsitus = [];
        const url = `/cfp/agendas/getEvents`; // <=======  AgendaCfpController.php  
        let salleEvents = [];
        fetch(url, { method: "GET" }).then(response => response.json())
          .then(data => {
             const dataEvnts = (data.seances)? data.seances:[];
             let idCustomer;  
            if(sessionStorage.getItem('ID_CUSTOMER')!== null)
            {
               idCustomer = sessionStorage.getItem('ID_CUSTOMER');
            }
            //if (sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_' + idCustomer) === null) {
             
              for (const evnt of dataEvnts) {
                const nbEvents = dataEvnts.length;
                  //console.log('nbEvents-->',nbEvents);
                  // Ajout sur les lignes formateurs ...  
                                                          
                salles.push({
                    start: evnt.start,
                    end: evnt.end,
                    id: crypto.randomUUID(),
                    idEtp :evnt.idEtp,
                    idSeance: evnt.idSeance,
                    idSalle: evnt.idSalle,
                    idModule: evnt.idModule,
                    resource: 'SL_' + evnt.idSalle,
                    height: 20,
                    idProjet: evnt.idProjet,
                    idCalendar: evnt.idCalendar,
                    module: evnt.module,
                    salle: evnt.salle,
                    ville: evnt.ville,
                    typeProjet:evnt.typeProjet,
                    prenom_form: evnt.formateurs.map(formateur => {
                      const idForm = formateur.idFormateur;
                      const prenom_form =  formateur.form_firstname;
                      return {
                        idFormateur:  idForm,
                        prenom:       prenom_form,
                      };
                  }
              
                ),
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
                paiement: evnt.paiementEtp,
                statut: evnt.statut,
                nb_appr: (evnt.typeProjet=='Intra')? evnt.apprCountIntra : evnt.apprCountInter ,
                    imgModule: evnt.imgModule,  
                    barColor: setColorStatutRessource(evnt.statut),
                    backColor: setColorStatutRessource(evnt.statut),
                });

                salleInsitus.push({
                  start: evnt.start,
                  end: evnt.end,
                  id: crypto.randomUUID(),
                  idEtp :evnt.idEtp,
                  idSeance: evnt.idSeance,
                  idSalle: evnt.idSalle,
                  idModule: evnt.idModule,
                  resource: 'IS_' + evnt.idCustomer,
                  height: 20,
                  idProjet: evnt.idProjet,
                  idCalendar: evnt.idCalendar,
                  module: evnt.module,
                  salle: evnt.salle,
                  ville: evnt.ville,
                  typeProjet:evnt.typeProjet,
                  prenom_form: evnt.formateurs.map(formateur => {
                    const idForm = formateur.idFormateur;
                    const prenom_form =  formateur.form_firstname;
                    return {
                      idFormateur:  idForm,
                      prenom:       prenom_form,
                    };
                }
            
              ),
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
              paiement: evnt.paiementEtp,
              statut: evnt.statut,
              nb_appr: (evnt.typeProjet=='Intra')? evnt.apprCountIntra : evnt.apprCountInter ,
                  imgModule: evnt.imgModule,  
                  barColor: setColorStatutRessource(evnt.statut),
                  backColor: setColorStatutRessource(evnt.statut),
                });
              
                  salleEvents = [...salles,...salleInsitus];
                      
                }
                          //Mise en memoire(LocalSorage | SessionStorage) de tous les évennements...
                          console.log('Salles EVENTS...',salleEvents);
      
                          sessionStorage.setItem('ACCESS_EVENTS_DETAILS_' + idCustomer, JSON.stringify(salleEvents));    
          
         //}
        }).catch(error => { console.error(error) });
                  dpSalles.events.list = salleEvents;
                  dpSalles.update({ salleEvents });
        return coursEvents;
      },

      loadAllEventsSalles(){
          const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
          if (sessionStorage.getItem('ACCESS_EVENTS_DETAILS_' + idCustomer) !== null) {     
            const objectEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_DETAILS_' + idCustomer));
            dpSalles.events.list = objectEvents;
            dpSalles.update({ objectEvents });
            return objectEvents;
          }
       
      }
  }


  
