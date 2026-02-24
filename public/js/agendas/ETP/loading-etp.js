document.addEventListener('DOMContentLoaded', (e) => {
  getIdCustomer()
  app.getAllSeancesGroupByJson()
  app.getAllSeancesDetailJson()
  e.stopPropagation()
}, true);


async function getIdCustomer() {
  let idCustomer;
  const url = '/homeEtp/customer';
   await fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      idCustomer = data.idCustomer;
      console.log('IDCUSTOMER==>', idCustomer);
      sessionStorage.setItem('ID_CUSTOMER',idCustomer);

    }).catch(error => { console.error(error) });
  return idCustomer;
}


const app = {
  setColorProjet(typeProjet)
  {
    let color = "";
    if(typeProjet=='Intra'){
       color = '#1565c0';
    } else  if(typeProjet=='Inter'){
      color ='#7209b7';
    
    } else  if(typeProjet=='Interne'){
      color ='#7F055F';
    }
    return color;
  },
  
 setColorStatutRessource(statut) 
 {
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
  } else if (statut === 'Cloturé') {
    backColor = "#828282";                //#828282 <===(couleur gris )
  } else {
    backColor = "#32CD32";                //#32CD32 <===(couleur vert : Terminée)
  }
  return backColor;
},

getNbAppr(typeProjet,evnt){

  switch(typeProjet) {
    case 'Intra':
      return evnt.apprCountIntra;
      break;
    
    case 'Inter':
      return evnt.apprCountInter;
      break;
      
    case 'Interne': 
     // console.log('TEST==>',evnt.apprCountInterne);
      return evnt.apprCountInterne;
  }

},


getAllSeancesDetailJson() 
{
  let dataEvnts;
  const detailEvents = [];
  const url = `/etp/agendas/getEvents`;
  fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      dataEvnts = (data.seances)? data.seances:[];
        console.log('LISTE SEANCES LOADING...dataEvnts-->', dataEvnts);
      let idCustomer;  
      if(sessionStorage.getItem('ID_CUSTOMER')!== null)
      {
         idCustomer = sessionStorage.getItem('ID_CUSTOMER');
      }  

      for (const evnt of dataEvnts) {  
      const  HTMLDATA = `
        <div class="flex items-start gap-2 bg-white p-4 w-full h-full">
          <div class="flex flex-col">
            <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
              <div class="inline-flex items-center gap-2">
                <div class="flex flex-col ">                         
                 
                    <p class="text-base  text-gray-700"> ${evnt.module || 'non assigné'}
                    </p>

                  <span class="flex flex-row flex-wrap items-center divide-x divide-gray-200 gap-2">

                      <p class="text-sm text-gray-400"> <span
                        class="text-base text-white px-2 rounded-md bg-[${this.setColorProjet(evnt.typeProjet )}] ">${evnt.typeProjet || 'non assigné'}</span>
                      </p>               
                     <p class="text-sm text-gray-400"> <span
                          class="text-sm text-black rounded-md bg-white-100 ">${(evnt.typeProjet =='Interne')?  evnt.nameEtp : evnt.nameCfp[0].etp_name }</span>
                      </p>

                      <p class="text-sm text-gray-500 rounded-md bg-yellow-100 ">${evnt.salle || 'non assigné'} </p>

                  </span>

                </div>
              </div>
            </div>
                     
          </div>
        </div>`;


      const TEXTE = `\n\n REF: ${evnt.reference??'  -- '} \n\n - ${evnt.module} - \n\n ${evnt.codePostal}- ${evnt.ville} - ${(evnt.typeProjet =='Interne')?  evnt.nameEtp : evnt.nameCfp[0].etp_name }
      Formateur:
         ${evnt.formateurs[0]?.form_firstname } , ${evnt.formateurs[1]?.form_firstname ?? ''}      
         ` ;
          detailEvents.push({
            id: crypto.randomUUID(),
            start: evnt.start,
            end: evnt.end,
            idEtp :evnt.idEtp,
            idSeance: evnt.idSeance,
            idModule: evnt.idModule,
            idProjet: evnt.idProjet,
            idSalle: evnt.idSalle,
            idCalendar: evnt.idCalendar,
            idCustomer: evnt.idCustomer,
            html: HTMLDATA,
            text: TEXTE,
            resource: evnt.resource,
            title: evnt.text,
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
              const prenom_form = formateur.form_firstname;
              return {
                idFormateur: idForm,
                prenom: prenom_form,
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
            //nb_appr: (evnt.typeProjet=='Intra')? evnt.apprCountIntra : evnt.apprCountInter   evnt.apprCountInterne,
            nb_appr: this.getNbAppr(evnt.typeProjet,evnt),

            imgModule: evnt.imgModule,  
            nameCfp: evnt.nameCfp.map(e => e.etp_name) ,
            codePostal:evnt.codePostal,
            reference: evnt.reference,
            //height: 20,
            barColor: this.setColorStatutRessource(evnt.statut),
            backColor: this.setColorStatutRessource(evnt.statut),
            quartier: evnt.quartier,
            modalite: evnt.modalite,
          })       
            
      }
      sessionStorage.setItem('ACCESS_EVENTS_DETAILS_' + idCustomer, JSON.stringify(detailEvents))

    }).catch(error => { console.error(error) });
},


getAllSeancesGroupByJson() 
{
  let dataEvnts;
  const detailEvents = [];
  const url = `/etp/agendas/getEventsGroupBy`;
  fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      dataEvnts = (data.seances)? data.seances:[];
        console.log('LISTE SEANCES LOADING...dataEvnts GROUP BY-->', dataEvnts);
      let idCustomer;  
      if(sessionStorage.getItem('ID_CUSTOMER')!== null)
      {
         idCustomer = sessionStorage.getItem('ID_CUSTOMER');
      }  
      for (const evnt of dataEvnts) {  
      const  HTMLDATA = `
        <div class="flex items-start gap-2 bg-white p-4 w-full h-full">
          <div class="flex flex-col">
            <div class="flex flex-row flex-wrap gap-x-2 w-full mb-3">
              <div class="inline-flex items-center gap-2">
                <div class="flex flex-col ">                         
                 
                    <p class="text-base  text-gray-700"> ${evnt.module || 'non assigné'}
                    </p>

                  <span class="flex flex-row flex-wrap items-center divide-x divide-gray-200 gap-2">

                      <p class="text-sm text-gray-400"> <span
                        class="text-base text-white px-2 rounded-md bg-[${this.setColorProjet(evnt.typeProjet )}] ">${evnt.typeProjet || 'non assigné'}</span>
                      </p>               
                     <p class="text-sm text-gray-400"> <span
                          class="text-sm text-black rounded-md bg-white-100 ">${(evnt.typeProjet =='Interne')?  evnt.nameEtp : evnt.nameCfp[0].etp_name }</span>
                      </p>

                      <p class="text-sm text-gray-500 rounded-md bg-yellow-100 ">${evnt.salle || 'non assigné'} </p>

                  </span>

                </div>
              </div>
            </div>
                     
          </div>
        </div>`;


      const TEXTE = `\n\n REF: ${evnt.reference??'  -- '} \n\n - ${evnt.module} - \n\n ${evnt.codePostal}- ${evnt.ville} - ${(evnt.typeProjet =='Interne')?  evnt.nameEtp : evnt.nameCfp[0].etp_name }
      Formateur:
         ${evnt.formateurs[0]?.form_firstname } , ${evnt.formateurs[1]?.form_firstname ?? ''}      
         ` ;
          detailEvents.push({
            id: crypto.randomUUID(),
            start: evnt.start,
            end: evnt.end,
            idEtp :evnt.idEtp,
            idSeance: evnt.idSeance,
            idModule: evnt.idModule,
            idProjet: evnt.idProjet,
            idSalle: evnt.idSalle,
            idCalendar: evnt.idCalendar,
            idCustomer: evnt.idCustomer,
            html: HTMLDATA,
            text: TEXTE,
            resource: evnt.resource,
            title: evnt.text,
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
              const prenom_form = formateur.form_firstname;
              return {
                idFormateur: idForm,
                prenom: prenom_form,
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
            nb_appr: this.getNbAppr(evnt.typeProjet,evnt),
            imgModule: evnt.imgModule,  
            nameCfp: evnt.nameCfp.map(e => e.etp_name) ,
            codePostal:evnt.codePostal,
            reference: evnt.reference,
            //height: 20,
            barColor: this.setColorStatutRessource(evnt.statut),
            backColor: this.setColorStatutRessource(evnt.statut),
            quartier: evnt.quartier,
            modalite: evnt.modalite,
            nb_seances: evnt.nb_seances,
          })       
            
      }
      sessionStorage.setItem('ACCESS_EVENTS_GROUP_BY_' + idCustomer, JSON.stringify(detailEvents))

    }).catch(error => { console.error(error) });
},
  
}





















