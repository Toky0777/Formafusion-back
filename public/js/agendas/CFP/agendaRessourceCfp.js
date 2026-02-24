/** FONCTIONS */

$(document).ready(function () {
  $("#filter").keyup(function () {
    //dp.rows.filter(query); // see dp.onRowFilter below
  });

  function filter() {
    // see dp.onRowFilter below
    dp.rows.filter({
        query:      $("#filter").val(),
        hideEmpty:  $("#hideEmpty").is(":checked"),
      //clients:    $("button[name='clients']").attr("name")
    });
  }

  function filterbtnclients(){
    dp.rows.filter({
    
      clients:    $("button[name='clients']").attr("name")
    });
   // console.log('filterclients-->',filterclients)   clients: $("button[name='clients']").attr("name") // Filtrer par client
  }  

  function filterbtnformateurs(){
    dp.rows.filter({formateurs:    $("button[name='formateurs']").attr("name")} )
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
        dp.rows.filter(null);
        return false;
      });
});

document.addEventListener('DOMContentLoaded', (e)=>{
   // dp.init();
    getIdCustomer();
    //getAllResource().then(loadAllEvents)
    //getAllResource();
   // loadAllEvents();

    e.events = getAllSeancesRessourceJson(); 
    console.log("Chargment... e==>",e)

 

},true);
/************************************************FONCTIONS ***********************************************************************************************************/

function getIdCustomer() {
  let idCustomer;
  const url = '/home/customer';
    fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      idCustomer = data.idCustomer;
      sessionStorage.setItem('ID_CUSTOMER',idCustomer);

    }).catch(error => { console.error(error) });
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

const resource = {
  getResource(data){
    dp.resources =
    [ 
      {
        name: "Cours", id: "MD", expanded: true, children: data.modules
      },
      {
        name: "Clients", id: "ETP", expanded: false, children: data.etps
      },
      {
        name: "Salles", id: "SL", expanded: false, children: data.salles
      },
      {
        name: "Formateurs", id: "FM", expanded: false, children: data.formateurs
      },
    ];
    dp.update();
  }
}

function getChildren(id,data){
  var children=[];
  switch (id) {
    case "MD": 
    children = data.modules;
      break;
    case "ETP":  
    children = data.etps;
      break;
    case "SL":  
    children = data.salles;
      break;
    case "FM":  
    children = data.formateurs;
      break;

      default: children=[];
       break;  
  }
  return children; 
}

 function getAllResource()                       //<======== Fonction permettant de charger les resources sur Daypilot
{
  const url = '/cfp/agendas/events_resources_agenda';
   fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      console.log('Liste ressources-->', data)

    var ressource_check = $('input[name="ressource_check"]');
    var ressource_table = [    
      {
        name: "Cours", id: "MD", expanded: true, children: data.modules
      },
      {
        name: "Clients", id: "ETP", expanded: true, children: data.etps
      },
      {
        name: "Salles", id: "SL", expanded: true, children: data.salles
      },
      {
        name: "Formateurs", id: "FM", expanded: true, children: data.formateurs
      },
    ];
    var data_content = null;
    console.log('ressource_tablessss-->',ressource_table)
    ressource_check.click(function () {      
      var id_checked =[];
      $.each(ressource_check, function () {       
        if ($(this).is(':checked')) 
        {
            id_checked.push( 
              {
                name: $(this).attr('data-label'),
                id:   $(this).attr('id'),
                expanded: true,
                //children: ($(this).attr('id')== 'FM')? data.formateurs :data.salles,
                children: getChildren($(this).attr('id'),data)
              }              
            );
              console.log('id_checked-->',id_checked);
              console.log('id_checked.length-->',id_checked.length);
              if(id_checked.length > 0){ 
                dp.resources = id_checked;
                dp.update();
              }

          } 
     
      });
    });
    
    resource.getResource(data);


    }).catch(error => { console.error(error) });
  return dp;
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

 function loadAllEvents() {
  //getIdCustomer().then(idCustomer => {
    const idCustomer = sessionStorage.getItem('ID_CUSTOMER');
    if (sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_' + idCustomer) !== null) {
      // console.log('LOCAL_STORAGE-->',sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_'+idCustomer) );
      const objectEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_' + idCustomer));
      //console.log('Objets Events-->', objectEvents);
      //dp.init();
      dp.events.list = objectEvents;
      dp.update({ objectEvents });
    }
    else {
      console.log('STORAGE VIDE!!!');
      getAllSeancesRessourceJson();
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
  let item = 0;
  const url = `/cfp/agendas/getEvents`; // <=======  AgendaCfpController.php  
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
              nameEtp: evnt.nameEtp,
              nameEtps: evnt.nameEtps.map( etp =>{
                 const nameEtp = etp.etp_name;
                 return {
                   name: nameEtp,
                 };
              }),
              paiement:   evnt.paiementEtp,
              imgModule:  evnt.imgModule, 
              barColor:   setColorStatutRessource(evnt.statut),
              backColor:  setColorStatutRessource(evnt.statut),
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
            // Ajout sur les lignes formateurs ...  
          evnt.formateurs.forEach(form => {                                             
            formateurs.push({
              start: evnt.start,
              end: evnt.end,
              id: crypto.randomUUID(),
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
              paiement: evnt.paiementEtp,
              imgModule: evnt.imgModule,  
              barColor: setColorStatutRessource(evnt.statut),
              backColor: setColorStatutRessource(evnt.statut),
            })
          }); 
            allEvents = [...salles, ...entreprises, ...modules, ...formateurs];
                       
            //Mise en memoire(LocalSorage | SessionStorage) de tous les évennements...

            if (item == nbEvents - 1) {
                console.log('STOP!!!')
                console.log('Liste resourceEvents STOP-->', allEvents);
                sessionStorage.setItem('ACCESS_EVENTS_RESOURCE_' + idCustomer, JSON.stringify(allEvents));
                const events_resource = sessionStorage.getItem('ACCESS_EVENTS_RESOURCE_' + idCustomer);
               //loadAllEvents();
            }

            item++;
          }
    
   }
  }).catch(error => { console.error(error) });
  return allEvents;
}


const dp = new DayPilot.Scheduler("dp", {
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

 /***************************************END OPTION DAYPILOT******************************************************************* */    
  onBeforeCellRender: function(args) {
    // Vérifie si la cellule est vide
    if (!args.cell.events.length) {
      // Cache la cellule si elle est vide
      args.cell.visible = false;
    }
  },

/***********************************Deplacement(Drag and Drop)******************************************************************** */
onEventMove : function(args) {
  if(!confirm('Etes-vous sur de déplacer cette session?')) {
    args.preventDefault();
  }
  },
onEventMoved: (args) => {
    args.control.message("Vous avez déplacé un évènement.");
    //const e = args.source;
    const resource = args.e.data.id;
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
    const apiUrl = '/cfp/agendas/events_updateForm_agenda';
        axios.patch(apiUrl,
           {
              "idProjet":   idProjet,
              "date":       dateSeance,
              "start":      startTime,
              "end":        endTime,
              "idFormateur":newIdFormateur,
              "idSeance":   newIdSeance,
            },
            {headers:{"Content-Type" : "application/json"}}
        )
          .then(function (response) {
              console.log(response);      
              //dp.message(`La session a été modifiée ... `); 
              toastr.success(response.success, 'Opération effectuée avec succès', {
                timeOut: 1500
              });
              sessionStorage.clear();
              location.reload();           
          })

          .catch(function (error) {
            console.log(error);
            console.log('PROBLEME sur le déplacement de la séssion!');
        });     
  },
/********************************************************************************************* */  
 
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
  treeEnabled: true,
onRowFilter: function (args) {

    const objquery = args.filter;
    const hideEmpty = args.filter.hideEmpty;
    const query = objquery.query;

    const clients= args.filter.clients;
    const formateurs= args.filter.formateurs;
    console.log('args.row.name -->',args.row.name );

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

// console.log( getAllSeancesRessourceJson());
// dp.events.list = getAllSeancesRessourceJson();
// dp.update();
