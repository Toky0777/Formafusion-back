document.addEventListener('DOMContentLoaded', (e) => {
  getIdCustomer()
  getAllSeancesDetailJson()
  getAllSeancesRessourceJson()
  //LoadAllData()
  e.stopPropagation()
}, true);//getAllSeancesRessourceJson());//getAllSeancesRessource() || getAllSeancesRessourceJson()

function gapiLoaded() {
  gapi.load('client', initializeGapiClient);
}

async function getIdCustomer() {
  let idCustomer;
  const url = '/homeForm/get-id-customer';
  await fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      idCustomer = data.idCustomer;
      console.log('IDCUSTOMER==>', idCustomer);
      sessionStorage.setItem('ID_CUSTOMER', idCustomer);

    }).catch(error => { console.error(error) });
  return idCustomer;
}

function setColorProjet(typeProjet) {
  let color = "";
  if (typeProjet == 'Intra') {
    color = '#1565c0';
  } else if (typeProjet == 'Inter') {
    color = '#7209b7';

  } else if (typeProjet == 'Interne') {
    color = '#0ABFDB';
  }
  return color;
}

async function initializeGapiClient() {
  await gapi.client.init({
    apiKey: API_KEY,
    discoveryDocs: [DISCOVERY_DOC],
  });
  gapiInited = true;
  //maybeEnableButtons();
  //console.log('API Chargé...');   
}

function gisLoaded() {
  TOKEN_CLIENT = google.accounts.oauth2.initTokenClient({
    client_id: CLIENT_ID,
    scope: SCOPES,
    callback: '', // defined later
  })
  gisInited = true;
}

function initStorage() {
  sessionStorage.setItem('UPDATED_STORAGE', false);

}

function updateTokenClient()      //<======== Fonction permettant de modifier 'access_token' si un client google est connecté 
{
  const TOKEN = document.querySelector('meta[name="csrf-token-google"]').getAttribute('content');//tiré de la page (layouts)master.blade.php
  gapi.client.setToken(({
    //!!! access_token sera modifié à chaque connexion...
    access_token: TOKEN,
    authuser: '0',
    expires_in: 5055,//==>84 mn et 15s
    prompt: 'consent',
    scope: SCOPES,
    token_type: 'Bearer'
  }))
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


function getAllSeancesRessourceJson() {
  let dataEvnts;
  const salles = [];
  const entreprises = [];
  const modules = [];
  const formateurs = [];
  let item = 0;
  const url = `/agendaForms/getEvents`; // <=======  AgendaCfpController.php  
  let allEvents = [];
  fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      const dataEvnts = (data.seances) ? data.seances : [];
      let idCustomer;
      if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
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
          }

          item++;
        }

      }
    }).catch(error => { console.error(error) });
  return allEvents;
}

function getAllSeancesDetailJson() {
  let dataEvnts;
  const detailEvents = [];

  const url = `/agendaForms/getEvents`;
  fetch(url, { method: "GET" }).then(response => response.json())
    .then(data => {
      const dataEvnts = (data.seances) ? data.seances : [];
      console.log('LISTE SEANCES LOADING...dataEvnts-->', dataEvnts);
      let idCustomer;
      if (sessionStorage.getItem('ID_CUSTOMER') !== null) {
        idCustomer = sessionStorage.getItem('ID_CUSTOMER');
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
                      class="text-sm text-gray-500">${!(evnt.nameEtp) ? evnt.nameEtps.map(etp => etp.etp_name) : evnt.nameEtp}</span>
                  </p>
                  <p class="text-sm text-gray-400">Projet : <span
                      class="text-base text-white px-2 rounded-md bg-[${setColorProjet(evnt.typeProjet)}] ">${evnt.typeProjet || 'non assigné'}</span>
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
                <p class="text-sm text-gray-400">Matériel :</p>
                <p class="text-sm text-gray-500">1 Ordinateur</p>
              </div>
              <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                <p class="text-sm text-gray-400">Formateur :</p>
                <p class="text-sm text-gray-500">${evnt.formateurs.map(formateur => formateur.form_firstname) || 'non assigné'}</p>
              </div>
              <div class="flex flex-row justify-between items-center flex-wrap gap-x-2">
                <p class="text-sm text-gray-400">Apprenants :</p>
                <p class="text-sm text-gray-500">${(evnt.typeProjet == 'Intra') ? evnt.apprCountIntra : evnt.apprCountInter}</p>
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
          statut: evnt.statut,
          nb_appr: (evnt.typeProjet == 'Intra') ? evnt.apprCountIntra : evnt.apprCountInter,
          imgModule: evnt.imgModule,  //<========================== modifié...
          height: 20,
          barColor: setColorStatutRessource(evnt.statut),
        })

        sessionStorage.setItem('ACCESS_EVENTS_DETAILS_' + idCustomer, JSON.stringify(detailEvents))

      }

    }).catch(error => { console.error(error) });

}

function LoadAllData() {

  getIdCustomer().then(idCustomer => {

    if ((sessionStorage.getItem('ACCESS_EVENTS_DETAILS_' + idCustomer) === null &&
      sessionStorage.getItem('ACCESS_EVENTS_RESSOURCE_' + idCustomer) === null) ||
      sessionStorage.getItem('UPDATED_STORAGE') === true
      // sessionStorage.getItem('UPDATED_STORAGE_RESSOURCE') ===false  
    ) {
      getAllSeancesRessourceJson();
      getAllSeancesDetailJson();
      sessionStorage.setItem('UPDATED_STORAGE', false);
    }

  });
}












