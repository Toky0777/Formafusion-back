/** FONCTIONS */

$(document).ready(function () {
  $("#filter").keyup(function () {
    //dp.rows.filter(query); // see dpRessource.onRowFilter below
  });

  function filter() {
    // see dpRessource.onRowFilter below
    dpRessource.rows.filter({
        query:      $("#filter").val(),
        hideEmpty:  $("#hideEmpty").is(":checked"),
      //clients:    $("button[name='clients']").attr("name")
    });
  }

  function filterbtnclients(){
    dpRessource.rows.filter({
    
      clients:    $("button[name='clients']").attr("name")
    });
   // console.log('filterclients-->',filterclients)   clients: $("button[name='clients']").attr("name") // Filtrer par client
  }  

  function filterbtnformateurs(){
    dpRessource.rows.filter({formateurs:    $("button[name='formateurs']").attr("name")} )
  }  

      $("#filter").keyup(function() {
          filter();
      });

      $("#hideEmpty").change(function() {
          filter();
      });

      $("#clients").click(function() {
        filterbtnclients();
          
      });

      $("#formateurs").click(function() {
        filterbtnformateurs();
          
      });

      $("#clear").click(function() {
          $("#filter").val("");
          filter();
          return false;
      });

      $("#clear").click(function () {
        $("#filter").val("");
        dpRessource.rows.filter(null);
        return false;
      });
});



document.addEventListener('DOMContentLoaded', 
  //getIdCustomer(),
  getAllResource().then(loadAllEvents),
  getAllSeancesRessourceJson());// getAllSeancesRessourceJson()
/************************************************FONCTIONS ***********************************************************************************************************/
 function getIdCustomer() {
  let idCustomer;
  const url = "/homeEtp/customer";
   fetch(url, { method: "GET" })
    .then((response) => response.json())
    .then((data) => {
      idCustomer = data.idCustomer;
      console.log('IDCUSTOMER==>', idCustomer);
      sessionStorage.setItem('ID_CUSTOMER',idCustomer);
    })
    .catch((error) => {
      console.error(error);
    });
  return idCustomer;
}


function getToken()                                   //<======= Récupere le TOKEN fourni par LARAVEL entete de html(meta)
{
  const metas = document.getElementsByTagName("meta");
  for (let i = 0; i < metas.length; i++) {
    const meta = metas[i];
    if (meta.name === "csrf-token") {
      return meta.content;
    }
  }
}

async function getAllResource()                       //<======== Fonction permettant de charger les resources sur Daypilot
{
  const url = '/etp/agendas/events_resources_agenda';
  await fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      dpRessource.resources =
        [
          /*{
            name: "Projets", id: "PJ", expanded: true, children: data.projets
          },*/
          {
            name: "Cours", id: "MD", expanded: true, children: data.modules
          },
          {
            name: "Cours_externes", id: "MD", expanded: true, children: data.module_externes
          },
          // {
          //   name: "Clients", id: "ETP", expanded: true, children: data.etps
          // },
          {
            name: "Salles", id: "SL", expanded: false, children: data.salles
          },
          {
            name: "Formateurs", id: "FM", expanded: false, children: data.formateurs
          },
          {
            name: "Formateur_externes", id: "FM", expanded: false, children: data.formateur_externes
          },
        ];
      dpRessource.update();

    }).catch(error => { console.error(error) });
  return dpRessource;
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

 async function loadAllEvents() {
  //getIdCustomer().then(idCustomer => {
    const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
    if (sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_' + idCustomer) !== null) {
      // console.log('LOCAL_STORAGE-->',sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_'+idCustomer) );
      const objectEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_' + idCustomer));
      //console.log('Objets Events-->', objectEvents);
      dpRessource.events.list = objectEvents;
      dpRessource.update({ objectEvents });
    }
    else {
  
      sessionStorage.setItem('ID_CUSTOMER',idCustomer);
      const objectEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_' + idCustomer));
   
      //loadAllEvents();
     // dp.init();
     dpRessource.events.list = getAllSeancesDetailJson();
     dpRessource.update();
    }
 // })

}

function getAllSeancesRessourceJson()                 //<======== Fonction permettant de récuperer les "data" des details d'une seance en format JSON
{
  let dataEvnts;
  const salles = [];
  const entreprises = [];
  const modules = [];
  const formateurs = [];
  const formateur_externes = [];
  let item = 0;
  const url = `/etp/agendas/getEvents`; // <=======  AgendaEtpController.php  
  let allEvents = [];
  fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      const dataEvnts = (data.seances)? data.seances:[];
      let idCustomer;  
      if(sessionStorage.getItem('ID_CUSTOMER')!== null)
      {
         idCustomer = sessionStorage.getItem('ID_CUSTOMER');
      }   
        if (sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_' + idCustomer) === null) {
          const i = 0;
          for (const evnt of dataEvnts) {
            const nbEvents = dataEvnts.length;
      
              // Ajout sur les lignes salles ...                                                                                        
              salles.push({
                start: evnt.start,
                end: evnt.end,
                id: crypto.randomUUID(),
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
                prenom_form_interne: evnt.formateur_internes.map(formateur => {
                  const idForm = formateur.idFormateur;
                  const prenom_form =  formateur.form_firstname;
                  return {
                    idFormateur:  idForm,
                    prenom:       prenom_form,
                  };
              
                }),
                nameEtp: evnt.nameEtp,
                nameEtps: evnt.nameEtps.map( etp =>{
                const nameEtp = etp.etp_name;
                return {
                  name: nameEtp,
                };
             }),
                paiement: evnt.paiementEtp,
                imgModule: evnt.imgModule, 
                barColor: setColorStatutRessource(evnt.statut),
                backColor: setColorStatutRessource(evnt.statut),
              })
              // Ajout sur les lignes clients (Entreprise) ...   
              entreprises.push({
                start: evnt.start,
                end: evnt.end,
                id: crypto.randomUUID(),
                idSeance: evnt.idSeance,
                idSalle: evnt.idSalle,
                idModule: evnt.idModule,
                resource: 'ETP_' + evnt.idEtp,     
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
              prenom_form_interne: evnt.formateur_internes.map(formateur => {
                const idForm = formateur.idFormateur;
                const prenom_form =  formateur.form_firstname;
                return {
                  idFormateur:  idForm,
                  prenom:       prenom_form,
                };
            
              }),
              nameEtp: evnt.nameEtp,
              nameEtps: evnt.nameEtps.map( etp =>{
              const nameEtp = etp.etp_name;
              return {
                name: nameEtp,
              };
           }),
                paiement: evnt.paiementEtp,
                imgModule: evnt.imgModule,  
                barColor: setColorStatutRessource(evnt.statut),
                backColor: setColorStatutRessource(evnt.statut),
              })
              // Ajout sur les lignes modules ...      
              modules.push({
                start: evnt.start,
                end: evnt.end,
                id: crypto.randomUUID(),
                idSeance: evnt.idSeance,
                idModule: evnt.idModule,
                idSalle: evnt.idSalle,
                resource: 'MD_' + evnt.idModule,
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
                prenom_form_interne: evnt.formateur_internes.map(formateur => {
                  const idForm = formateur.idFormateur;
                  const prenom_form =  formateur.form_firstname;
                  return {
                    idFormateur:  idForm,
                    prenom:       prenom_form,
                  };
              
                }),
                nameEtp: evnt.nameEtp,
                nameEtps: evnt.nameEtps.map( etp =>{
                const nameEtp = etp.etp_name;
                return {
                  name: nameEtp,
                };
              }),
                paiement: evnt.paiementEtp,
                imgModule: evnt.imgModule,  
                barColor: setColorStatutRessource(evnt.statut),
                backColor: setColorStatutRessource(evnt.statut),
              })

              // Ajout sur les lignes formateurs internes ...

              formateurs.push({
                start: evnt.start,
                end: evnt.end,
                id: crypto.randomUUID(),
                idSeance: evnt.idSeance,
                idSalle: evnt.idSalle,
                idModule: evnt.idModule,
                resource: 'FM_' + evnt.formateurs.map(form =>form.idFormateur),
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
            prenom_form_interne: evnt.formateur_internes.map(formateur => {
              const idForm = formateur.idFormateur;
              const prenom_form =  formateur.form_firstname;
              return {
                idFormateur:  idForm,
                prenom:       prenom_form,
              };
          
            }),
            nameEtp: evnt.nameEtp,
            nameEtps: evnt.nameEtps.map( etp =>{
            const nameEtp = etp.etp_name;
            return {
              name: nameEtp,
            };
          }),
                paiement: evnt.paiementEtp,
                imgModule: evnt.imgModule,  
                barColor: setColorStatutRessource(evnt.statut),
                backColor: setColorStatutRessource(evnt.statut),
              })

              // Ajout sur les lignes formateurs externes ...  
                                                 
              formateur_externes.push({
                start: evnt.start,
                end: evnt.end,
                id: crypto.randomUUID(),
                idSeance: evnt.idSeance,
                idSalle: evnt.idSalle,
                idModule: evnt.idModule,
                resource: 'FM_EXT_' + evnt.formateurs.map(form =>form.idFormateur),
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
            prenom_form_interne: evnt.formateur_internes.map(formateur => {
              const idForm = formateur.idFormateur;
              const prenom_form =  formateur.form_firstname;
              return {
                idFormateur:  idForm,
                prenom:       prenom_form,
              };
          
            }),
            nameEtp: evnt.nameEtp,
            nameEtps: evnt.nameEtps.map( etp =>{
            const nameEtp = etp.etp_name;
            return {
              name: nameEtp,
              };
            }),
                paiement: evnt.paiementEtp,
                imgModule: evnt.imgModule,  
                barColor: setColorStatutRessource(evnt.statut),
                backColor: setColorStatutRessource(evnt.statut),
            })
           console.log('formateur_externes-->',formateur_externes);
              allEvents = [...salles, ...entreprises, ...modules, ...formateurs,...formateur_externes];
      
              

              if (item == nbEvents - 1) {  
                 // console.log('STOP!!!')
                  console.log('Liste resourceEvents STOP-->', allEvents);            
                  sessionStorage.setItem('ACCESS_EVENTS_RESOURCE_' + idCustomer, JSON.stringify(allEvents));
                  const events_resource = sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_' + idCustomer);
                 // $('.loader').addClass(`fondu-out hidden`);
                 loadAllEvents();
              }

              item++;
            }
      
     }
    }).catch(error => { console.error(error) });


}


const dpRessource = new DayPilot.Scheduler("dpRessource", {

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




  onBeforeCellRender: function(args) {
    // Vérifie si la cellule est vide
    if (!args.cell.events.length) {
      // Cache la cellule si elle est vide
      args.cell.visible = false;
    }
  },
  // Autres configurations...


  // onRowFilter: (args) => {
  //   const query = args.filterParam.query;
  //   const hideEmpty = args.filterParam.hideEmpty;

  //   alert('COCHER-->TRUE');
  //   console.log('Query-->',query);

  //   if (args.row.name.toUpperCase().indexOf(query.toUpperCase()) === -1) {
  //     args.visible = false;
  //   } else if (hideEmpty && args.row.events.isEmpty()) {
  //     args.visible = false;
  //   }

  // },

  onEventMoved: (args) => {
    args.control.message("Vous avez déplacé un évènement.");
  },
  eventResizeHandling: "Update",
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
      const tabForm = (e.data.prenom_form)? [...e.data.prenom_form]: [];
      const tabFormInterne = (e.data.prenom_form_interne)? [...e.data.prenom_form_interne]: [];
      const nameEtp = DayPilot.Util.escapeHtml(e.data.nameEtp);
      const tabNameEtp = [...e.data.nameEtps];
      const paiement = DayPilot.Util.escapeHtml(e.data.paiement);
      const typeProjet = DayPilot.Util.escapeHtml(e.data.typeProjet)
      //const color=  DayPilot.Util.escapeHtml(e.data.barColor);
      const colors = setColorProjet(typeProjet);
      const text = DayPilot.Util.escapeHtml(e.data.title);
      const start = e.start().toString("dd/MM/yyyy");
      //const end = e.end().toString("dd/MM/yyyy h:mm tt");
      const endTime = e.end().toString('h:mm tt');
      const startTime = e.start().toString('h:mm tt');
      const img_module =  DayPilot.Util.escapeHtml(e.data.imgModule); //imgModule: object.imgModule; //<========================== modifié...
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
          <div class="inline-flex items-start gap-x-2">
                      <div class="w-[32%] inline-flex items-center justify-end">
                        <p class="text-base text-gray-400">Formateur :</p>
                      </div>
                      <div class="w-[66%] inline-flex items-center justify-start">
                    <p class="text-base text-gray-500">${ (typeProjet=='Interne')? tabFormInterne.map(f => f.prenom): tabForm.map(f => f.prenom)
          }</p>
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
  treeEnabled: true,
  onRowFilter: function (args) {

    const objquery = args.filter;
    const hideEmpty = args.filter.hideEmpty;
    const query = objquery.query;

    const clients= args.filter.clients;
    const formateurs= args.filter.formateurs;
   // console.log('args.row.name -->',args.row.name );

  if(query){
    if(args.row.name==null) return
    if(args.row.name.toUpperCase().indexOf(query.toUpperCase()) === -1)  
    {
      args.visible = false;
    } 
  }
    else if (hideEmpty && args.row.events.isEmpty())
    {
      args.visible = false;
    }
    else 
    if(clients){
      if (clients && args.row.resource!== clients)
      {
       
        args.visible = false;
      }
  }
    else if (formateurs && args.row.events.isEmpty())
    {
      args.visible = false;
    }
    
  },

});

dpRessource.init();
// dp.onRowFilter = function(args){
//   var objquery = args.filter;
//   var hideEmpty = args.filter.hideEmpty;
//   var query = objquery.query;

//   if(query){
//     if (args.row.name.toUpperCase().indexOf(query.toUpperCase()) === -1) 
//     {
//       args.visible = false;
//     }
//   }  
//     else if (hideEmpty && args.row.events.isEmpty())
//     {
//       args.visible = false;
//     }

// };