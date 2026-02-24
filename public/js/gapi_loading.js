let CLIENT_ID;
let API_KEY;
let DISCOVERY_DOC;
let SCOPES;
let TOKEN;

async function loadCredentials() {
    try {
        const response = await fetch('/home/api/config');
        const credentials = await response.json();
        CLIENT_ID = credentials.googleConfig.CLIENT_ID;
        API_KEY = credentials.googleConfig.API_KEY;
        DISCOVERY_DOC = credentials.googleConfig.DISCOVERY_DOC;
        SCOPES = credentials.googleConfig.SCOPES.join(' ');
        await initializeGoogleAuth();
        await checkTokenStatus();
    } catch (error) {
        console.error('Erreur lors du chargement des credentials:', error);
        showAlert('Erreur de chargement des credentials', 'error');
    }
}

async function initializeGoogleAuth() {
    await Promise.all([
        loadScript('https://apis.google.com/js/api.js'),
        loadScript('https://accounts.google.com/gsi/client')
    ]);

    await new Promise((resolve) => {
        gapi.load('client', async () => {
            await gapi.client.init({
                apiKey: API_KEY,
                discoveryDocs: [DISCOVERY_DOC],
            });
            resolve();
        });
    });

    console.log('APIs Google initialisées');
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.body.appendChild(script);
    });
}

function showAlert(message, type = 'info') {
    // Créer l'élément d'alerte
    const alertDiv = document.createElement('div');
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.padding = '15px';
    alertDiv.style.borderRadius = '5px';
    alertDiv.style.zIndex = '2000';
    alertDiv.style.opacity = '0';
    alertDiv.style.transition = 'opacity 0.3s ease-in-out';

    // Définir les couleurs selon le type
    switch(type) {
        case 'error':
            alertDiv.style.backgroundColor = '#f8d7da';
            alertDiv.style.color = '#721c24';
            alertDiv.style.border = '1px solid #f5c6cb';
            break;
        case 'success':
            alertDiv.style.backgroundColor = '#d4edda';
            alertDiv.style.color = '#155724';
            alertDiv.style.border = '1px solid #c3e6cb';
            break;
        default:
            alertDiv.style.backgroundColor = '#cce5ff';
            alertDiv.style.color = '#004085';
            alertDiv.style.border = '1px solid #b8daff';
    }

    alertDiv.textContent = message;
    document.body.appendChild(alertDiv);

    // Animation d'apparition
    setTimeout(() => {
        alertDiv.style.opacity = '1';
    }, 100);

    // Disparition après 3 secondes
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(alertDiv);
        }, 300);
    }, 3000);
}

async function checkTokenStatus() {
    const storedToken = localStorage.getItem('ACCESS_TOKEN');
    //const authButton = document.getElementById('authorize_button');
    if (!storedToken) {
        showAlert('Aucun token trouvé - Synchronisation requise', 'error');
        updateButtonState(true);
        //authButton.disabled = true;
        return;
    }

    try {
        const response = await fetch('https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=' + storedToken);
        const data = await response.json();

        if (data.error) {
            showAlert('Token expiré - Nouvelle synchronisation requise', 'error');
            localStorage.removeItem('ACCESS_TOKEN');
            TOKEN = null;
            updateButtonState(false);
        } else {
            showAlert('Token valide - Synchronisation active', 'success');
            TOKEN = { access_token: storedToken };
            updateButtonState(true);
            //authButton.disabled = false;
        }
    } catch (error) {
        console.error('Erreur lors de la vérification du token:', error);
        showAlert('Erreur de vérification du token', 'error');
        updateButtonState(false);
    }
}

function updateButtonState(isValid) {
    const authButton = document.getElementById('authorize_button');
    if (authButton) {
        //authButton.innerText = isValid ? 'Refresh' : 'Sync with Google';
        //authButton.style.visibility = isValid ? 'hidden' : 'visible';
        authButton.innerText = isValid ? 'Sync with GOOGLE' : 'Not sync with GOOGLE';
       // alert("isValid==>"+isValid);
        authButton.disabled = isValid;
    } 

    const signoutButton = document.getElementById('signout_button');
    if (signoutButton) {
        signoutButton.style.visibility = isValid ? 'visible' : 'hidden';
    }
}

async function handleAuthClick() {
    try {
        const oauth2Client = google.accounts.oauth2.initTokenClient({
            client_id: CLIENT_ID,
            scope: SCOPES,
            callback: async (response) => {
                if (response.error) {
                    console.error('Erreur d\'authentification:', response.error);
                    showAlert('Échec de l\'authentification', 'error');
                    updateButtonState(false);
                    return;
                }
                
                TOKEN = response;
                localStorage.setItem('ACCESS_TOKEN', response.access_token);
                showAlert('Authentification réussie', 'success');
                updateButtonState(true);
              
                 // Exemple d'utilisation de createOrGetCalendar
                    try {
                        // Créer ou récupérer les calendriers dont vous avez besoin
                        const formationsCalendar = await createOrGetCalendar('FF-Formations','3'); 
                        const opportunitesCalendar = await createOrGetCalendar('FF-Opportunités','4');                                       ;
                        console.log('ID Calendrier Formations CREER ou RECUPERER :', formationsCalendar.id); // <=== FORMATIONS
                        console.log('ID Calendrier Opportunités:', opportunitesCalendar.id); // <=== OPPORTUNITES

                        localStorage.setItem('ID_AGENDA',formationsCalendar.id);
                        localStorage.setItem('ID_OPPORTUNITY',opportunitesCalendar.id);

                    } catch (error) {
                        console.error('Erreur lors de la gestion des calendriers:', error);
                    }
            }
        });

        oauth2Client.requestAccessToken({prompt: 'consent'});
    } catch (error) {
        console.error('Erreur lors de l\'authentification:', error);
        showAlert('Erreur d\'authentification', 'error');
        updateButtonState(false);
        dpAnnuaire.loadingStart({ delay: 400, text: "Veuillez patienter ...", block: true });
        showAlert('Veuillez patienter pendant le chargement des données...!', 'error');
       // window.location.href = "/login/google";
       // this.handleAuthClick();
    }
}

function handleSignoutClick() {
    if (TOKEN?.access_token) {
        google.accounts.oauth2.revoke(TOKEN.access_token);
        gapi.client.setToken('');
        localStorage.removeItem('ACCESS_TOKEN');
        localStorage.removeItem('ID_AGENDA');  
        localStorage.removeItem('ID_OPPORTUNITY');
        TOKEN = null;
        updateButtonState(false);
        showAlert('Déconnexion réussie', 'info');
    }
}

function getAgendaOfName(calendarName) {
    let idCalendar;
    return gapi.client.calendar.calendarList.list({
      "maxResults": 10,
      "minAccessRole": "freeBusyReader",
      "showDeleted": true
    })
        .then(function(response) {
                // Handle the results here (response.result has the parsed body).
                console.log("Response", response);
                const calendarList = response;
                console.log('calendarList ITEM-->',calendarList.result.items);
                for (const calendarItem of calendarList.result.items) {
                    if (calendarItem.summary === calendarName) {
                     idCalendar={
                        id: calendarItem.id,
                        isNew: false
                      }; 
                    }  
                }
            console.log('IDCALENDAR==>', idCalendar);
            return idCalendar;
                //alert("List of Calendar:" + response.);
              })
              .catch((err) =>{ console.error("Execute error", err); });
}

async  function createOrGetCalendar(calendarName,colorId) {
    try {
      // Vérifiez d'abord si le calendrier existe
      const calendarList = await gapi.client.calendar.calendarList.list();
      
      for (const calendarItem of calendarList.result.items) {
        if (calendarItem.summary === calendarName) {
          // Le calendrier existe déjà, retournez son ID
          return {
            id: calendarItem.id,
            isNew: false
          };
        }
      }     
      // Le calendrier n'existe pas, créez-le
      const newCalendar = await gapi.client.calendar.calendars.insert({
        "resource": {
            "summary": calendarName,
            "location": "Africa/Nairobi",
            "colorId": colorId,
          },
        //"colorId": colorId,
        }
      );
      return {
        id: newCalendar.result.id,
        isNew: true
      };
    } catch (error) {
      console.error('Erreur lors de la création/récupération du calendrier:', error);
      throw error;
    }
}


// Vérifier périodiquement l'état du token (toutes les 5 minutes)
setInterval(checkTokenStatus, 5 * 60 * 1000);

const storedToken = localStorage.getItem('ACCESS_TOKEN');
// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded',loadCredentials);