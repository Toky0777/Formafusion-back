let ID_CUSTOMER;
document.addEventListener('DOMContentLoaded', (e) => {
  
  ID_CUSTOMER = sessionStorage.getItem('ID_CUSTOMER');
  ID_CUSTOMER? ID_CUSTOMER:getIdCustomer();

  dpTresor.init() ;

  appTresor.init(); //Ajout évènement CLICK... 
  appTresor.getDetailFacture(); // on créé la session storage...
  
  dpTresor.events.list = appTresor.getEventData(); // load events after loading...
  //console.log("appTresor.getEventData()",appTresor.getEventData());
  //dpTresor.update();

  const width = window.innerWidth;
  dpTresor.update({
    cellWidth:  calculateCellWidth(width),
    //height:     calculateHeight(width),
  })

  $('#tabTresor').show();
  
}, true)

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

function getYear(){
  const startDdate = new Date();
  const year = startDdate.getFullYear();
 return year;
}

function calculateHeight(width) {
  // Calculez la hauteur en fonction de la largeur
  // Par exemple, pour une largeur de 1920px, utilisez une hauteur de 950px
  return width < 1920 ? 400 : 950;
  }
  
  function calculateCellWidth(width){
  return width < 1920 ? 50 : 68;
  }
  
  window.addEventListener('resize', () => {
  const width = window.innerWidth;
  // const height = window.innerHeight;
  dpTresor.update({
    cellWidth:  calculateCellWidth(width),
    height:     calculateHeight(width),
  });
  });
  
  function extractText(html) {
  var parser = new DOMParser();
  var doc = parser.parseFromString('<parser><![CDATA[' + html + ']]></parser>', 'text/html');
  var text = doc.body.textContent;
  
  // Nettoyez le texte pour supprimer les espaces en trop et les sauts de ligne
  return text.replace(/\s+/g, ' ').trim();
  }
function formatNumber(num) {
    return num.toLocaleString('fr-FR' );
}

function setColor(idInvoiceStatus){
  let color = "";
  if(idInvoiceStatus==1){ //Brouillon
     color = 'gray-400';
  } else  if(idInvoiceStatus==2){ //Non Envoyé
    color ='rose-500';
  
  } else  if(idInvoiceStatus==3){//Envoyé
    color ='teal-600';
  }
  else  if(idInvoiceStatus==4){//Payé
    color ='green-400';
    }
  else  if(idInvoiceStatus==5){//Partiel
  color ='yellow-400';
  }
  else  if(idInvoiceStatus==6){//Impayé
    color ='red-400';
    }
    else  if(idInvoiceStatus==7){//Convertis
      color ='green-600';
      }  
      else  if(idInvoiceStatus==8){//Expiré
        color ='red-600';
        }
        else  if(idInvoiceStatus==9){//Annulé
          color ='rose-500';
          }
  return color;
}

function setColorHexa(idInvoiceStatus){
  let color = "";
  if(idInvoiceStatus==1){ //Brouillon
     color = '#808080';
  } else  if(idInvoiceStatus==2){ //Non Envoyé
    color ='#f472b6';
  
  } else  if(idInvoiceStatus==3){//Envoyé
    color ='#06b6d4';
  }
  else  if(idInvoiceStatus==4){//Payé
    color ='#22d3ee';
    }
  else  if(idInvoiceStatus==5){//Partiel
  color ='#facc15';
  }
  else  if(idInvoiceStatus==6){//Impayé
    color ='#ef4444';
    }
    else  if(idInvoiceStatus==7){//Convertis
      color ='#0891b2';
      }  
      else  if(idInvoiceStatus==8){//Expiré
        color ='#dc2626';
        }
        else  if(idInvoiceStatus==9){//Annulé
          color ='#f9a08d';
          }
  return color;
}

const dpTresor = new DayPilot.Scheduler("dpTresor", {
  /***************************************OPTIONS DAYPILOT******************************************************************* */    
  locale: "fr-fr",
  startDate:`${getYear()}-01-01`,// <==premier jour et mois de l'année 
  scale: "CellDuration",
  cellDuration: 720,
  
  //timeRangeSelectedHandling: "Enabled",
  eventMoveHandling: "Disabled",
  timeRangeSelectingStartEndEnabled: "Disabled",
  autoRefreshEnabled: true,
  eventDoubleClickHandling: "Update",
  autoResizeRows: true,
  //autoRefreshEnabled: true,
  cellGroupBy: "Month",
    days: 31,
    scale: "Day",
    timeHeaders: [
        
        {groupBy: "Day", format: "d"}
    ],
    //heightSpec: "Content",
    heightSpec: "Max",
    //height: 50,
  
    treeEnabled: true,
    resources: [
        {name: "Janvier", id: 1 },
        {name: "Février", id: 2},
        {name: "Mars", id: 3} ,
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

  
    linkBottomMargin: 20,    
    // groupConcurrentEvents: true,
    groupConcurrentEventsLimit: 2,
    eventHeight : 80,
  
   // linkCreateHandling: "Update",
  
  /***************************************END OPTION DAYPILOT******************************************************************* */    
    bubble: new DayPilot.Bubble({
      onLoad: (args) => {
      const e = args.source;
      console.log('event==>',e);

      const status = DayPilot.Util.escapeHtml(e.data.status);
    
      const start = e.start().toString("dd/MM/yyyy");
      const date = DayPilot.Util.escapeHtml(e.data.date);
      const reference = DayPilot.Util.escapeHtml(e.data.reference);
      const nameEtp = DayPilot.Util.escapeHtml(e.data.nameEtp);
      const total = DayPilot.Util.escapeHtml(e.data.total);
      const idInVoiceStatus = DayPilot.Util.escapeHtml(e.data.idInvoiceStatus);
      const formatTotal = Number(total);
       
        const bubbleHtml = `
              <div class="flex items-center gap-2 h-full w-[15em] bg-white p-4">
                <div class="flex flex-col w-full">
                  <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
                    <div class="inline-flex items-start gap-2">              
                      <div class="flex flex-col">
                        <p class="text-sm font-semibold text-gray-700 "><span
                         class="text-base text-white px-2 rounded-md bg-[${setColorHexa(idInVoiceStatus)}]" ">${status ?? "non assigné"}</span>
                        </p>
                           <p class="text-sm text-gray-400"> <u> N° Facture:</u> <span
                            class="text-sm text-gray-500"> ${reference ?? 'Non assigné '} </span> 
                        </p>

                        <p class="text-sm text-gray-400">Entreprise : <span
                            class="text-sm text-gray-500"> ${nameEtp ?? 'Non assigné '} </span> 
                        </p>

                        <p class="text-sm text-gray-400">Montant : <span
                             class="text-sm text-gray-500"> ${formatNumber(formatTotal) ?? 'Non assigné '} Ar </span> 
                        </p>

                      </div>
                    </div>
                  </div>

                </div>
              </div>`;
   
        args.html = bubbleHtml;
      }
    }),  

    contextMenu: new DayPilot.Menu({
      items: [
        {
          text: "Voir ",
          onClick: (args) => {
            const e = args.source;
            const idFacture = DayPilot.Util.escapeHtml(e.data.idFacture);           
            window.location.replace(`/cfp/factures/${idFacture}`);
          }
        },
        {
          text: "Editer ",
          onClick: (args) => {
            const e = args.source;
            const idFacture = DayPilot.Util.escapeHtml(e.data.idFacture);           
            window.location.replace(`/cfp/factures/${idFacture}/edit`);
          }
        },
        {
          text: "Exporter en PDF ",
          onClick: (args) => {
            const e = args.source;
            const idFacture = DayPilot.Util.escapeHtml(e.data.idFacture);           
            window.location.replace(`/cfp/factures/export/${idFacture}`);
          }
        },
        {
          text: "Supprimer ",
          onClick: (args) => {
            const e = args.source;
            const idFacture = DayPilot.Util.escapeHtml(e.data.idFacture);           
            window.location.replace(`/cfp/factures/${idFacture}/destroy}`);
          }
        },
       
      ],
    }),
   
    onBeforeCellRender: args => {   
    
        if (args.cell.start.getDayOfWeek() === 6 || args.cell.start.getDayOfWeek() === 0) {
            args.cell.backColor = "white";
        }
  
    },
  
    onEventFilter: (args) => {
      const text = extractText(args.e.data.html);
      let textFound;
         // Ajout du filtre par statut si nécessaire
      if (args.filter.status) {
        args.visible = args.e.data.status === args.filter.status;
      }
      else{
        textFound = text.toUpperCase().indexOf(args.filter.toUpperCase()) > -1 ;
        if (!textFound ) {     
                args.visible = false;
        } 
      }

      },
     
  
    onScroll: async args => {
        args.async = true;
   
        args.events = appTresor.getEventData();  // charge sessionStorage("EVENTS_DETAILS")
  
        args.loaded();    
    
    },
    
});

const appTresor = {

  init() {
    this.addEventInputHandlers();
  },

 getEventData()
    {    
        const events =[];
        //let ID_CUSTOMER = getIdCustomer();
        const detailEvents = JSON.parse(sessionStorage.getItem('ACCESS_EVENTS_FACTURE_' + ID_CUSTOMER));
      //events = objectEvents.map(obj => obj.start);

      console.log('getEventData:',detailEvents);
      const objectEvents =  detailEvents? detailEvents : [] ;

          for( const obj of objectEvents ){

            const month = new DayPilot.Date(obj.end).getMonth()+1;

            const dayStart = new DayPilot.Date(obj.end).getDay();
            const dayEnd = new DayPilot.Date(obj.end).getDay();
            //let year = new DayPilot.Date(obj.start).getYear();
            const year = new DayPilot.Date(obj.end).getYear();

            console.log('year==>', year);
  
            const dayFormattedStart = dayStart.toString().padStart(2, '0');
            const dayFormattedEnd = dayEnd.toString().padStart(2, '0');

            const newstart = `${year}-01-${dayFormattedStart}`;
            const newend = `${year}-01-${dayFormattedEnd}`;
    
            const date =  `${dayFormattedEnd}/${month}/${year}`;
            const formatTotal = Number(obj.total);
       
            events.push({
                  start:      newstart,
                  end:        newend,          
                  date:       date,
                  id:         DayPilot.guid(), 
                  idFacture:  obj.idFacture,
                  reference:  obj.idNumber,
                  nameEtp:    obj.nameEtp,
                  total:      obj.total,                                     
                  status:     obj.status, 
                  idInvoiceStatus:obj.idInvoiceStatus,
                  resource:   month, 
                  text: ` Fa:  ${obj.idNumber ?? '  -- '} \n - ${obj.nameEtp} - \n ${formatNumber(formatTotal)} Ar`,  // Ajoutez le texte 
                  barColor:   setColorHexa(obj.idInvoiceStatus),
                  barBackColor:setColorHexa(obj.idInvoiceStatus),
                        
                  html: '<div class="w-full ml-1 flex flex-col items-start text-xs">' +
                  DayPilot.Util.escapeHtml(obj.idFacture) + 
                  '<br/>'
                  +   
                    '<span class="px-2 py-1 text-sm bg-pink-100 text-slate-700 rounded-md">' +
                    '<u> Fa:</u> ' +DayPilot.Util.escapeHtml(obj.idNumber)  +'<br/> ' + DayPilot.Util.escapeHtml(obj.nameEtp) +
                   '</span>' 
                  +
                   '<span class="px-2 py-1 text-sm bg-white text-slate-700 rounded-md">' 
                   + formatNumber(formatTotal) +' Ar'+
                  '</span>' 

                  + '</div>',                                  
            })

          }      
          console.log(" new Events-->",events);  
        return events;
},

getDetailFacture()
{
  const detailEvents = [];
  const url = `/cfp/factures/getEvents`;
fetch(url, { method: "GET" }).then(response => response.json())
  .then(data => {
    const objectEvents = (data.factures)? data.factures : [];
      console.log('LISTE FACTURES LOADING...objectEvents-->', objectEvents);
      let idCustomer;  
      if(sessionStorage.getItem('ID_CUSTOMER')!== null)
      {
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
                        class="text-sm text-black rounded-md bg-white-100 ">${evnt.status } </span>
                    </p>

                    <p class="text-sm text-gray-500 rounded-md bg-yellow-100 "> ${evnt.nameEtp ?? 'non assigné'} </p>

                    <p class="text-sm text-gray-500 rounded-md bg-yellow-100 ">  ${ formatNumber(formatTotal) ?? 'non assigné'} Ar </p>

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
          nameEtp:evnt.nameEtp,
          total: evnt.total,
          idInvoiceStatus:evnt.idInvoiceStatus,
          status: evnt.status,
          barColor:   setColorHexa(evnt.idInvoiceStatus),
          barBackColor:setColorHexa(evnt.idInvoiceStatus),
        })
      
    }
    dpTresor.update({detailEvents});
    sessionStorage.setItem('ACCESS_EVENTS_FACTURE_' + idCustomer, JSON.stringify(detailEvents));
                              

  }).catch(error => { console.error(error) });
return detailEvents;

},

addEventInputHandlers() {
  const tabTresor = $('#tabTresor');

  tabTresor.ready(function () {
    document.querySelectorAll("input[name=statut]").forEach(function(el) {

        el.addEventListener("change", function(ev) {
            const val = document.querySelector("input[name=statut]:checked").value;
            //const val = document.querySelector("input[name=statut]:checked").id;
            console.log("VAL-->",val)
            if(val!='Tous')
              dpTresor.events.filter({
                status: val || null,
            });                 
        
          if(val ==='Tous')
            { 
              ev.preventDefault();
              appWidth.elements.filter.value = "";
              dpTresor.events.filter(null);
            }
            
        });
    });
  });

},

}

const appWidth = {
  elements: {
      cellWidth: document.querySelector("#cellwidth"),
      label: document.querySelector("#label"),
  
      filter: document.querySelector("#filter"),
      clear: document.querySelector("#clear"),
  },
  
  init() {
  
    this.elements.filter.addEventListener("keyup", function () {
      var query = this.value;
      dpTresor.events.filter(query); // see dp.onEventFilter
  });
  
  this.elements.clear.addEventListener("click", function (ev) {
      ev.preventDefault();
      appWidth.elements.filter.value = "";
      dpTresor.events.filter(null);
      //Met input value='tous' à true
      const tous = document.querySelector("input[type=radio]:checked").value;
  
  
      if(tous === 'Tous'){
        tous.checked = "checked" ;
      }
  });
  
      this.addEventHandlers();
      
  },
  
  addEventHandlers() {
    appWidth.elements.cellWidth.addEventListener("input", (ev) => {
          const cellWidth = parseInt(appWidth.elements.cellWidth.value);
          const start = dpTresor.getViewPort().start;
  
          dpTresor.update({
              cellWidth: cellWidth,
              scrollTo: start
          });
          appWidth.elements.label.innerText = cellWidth;
      });
  }
  };


  const subdropMonthStart = document.getElementById('subdropMonthStart');
  const subdropMonthEnd = document.getElementById('subdropMonthEnd');

  var selectedOptionIdStart = '1' ;
  var selectedOptionIdEnd ='12';

  subdropMonthStart.addEventListener('change', function (e) {
    selectedOptionIdStart = $(this).find('option:selected').attr('id'); 
  });

  subdropMonthEnd.addEventListener('change', function (e) {
    selectedOptionIdEnd = $(this).find('option:selected').attr('id');
 });

  $("#export-button").click(function (ev) {
    ev.preventDefault();

    if(selectedOptionIdStart && selectedOptionIdEnd){
    dpTresor.exportAs("jpeg",
      {
        area: 'range',
        quality: 5.92,
        scale: 0.8,
        resourceFrom: Number(selectedOptionIdStart),
        resourceTo: Number(selectedOptionIdEnd),

      }).print();
    }  
    //downloadPdf();
  });
  appWidth.init();