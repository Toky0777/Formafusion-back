// -----------------------------DRAWER SCRIPT---------------------------
function showDetails(title, date, type, startTime, endTime, city, formateur, entreprise, financement) {
    document.getElementById('formationTitle').innerText = title;
    document.getElementById('formationDate').innerText = date;
    document.getElementById('formationType').innerText = type;
    document.getElementById('formationStartTime').innerText = startTime;
    document.getElementById('formationEndTime').innerText = endTime;
    document.getElementById('formationCity').innerText = city;
    document.getElementById('formateurName').innerText = formateur;
    document.getElementById('entrepriseName').innerText = entreprise;
    document.getElementById('financeName').innerText = financement;

    document.getElementById('drawer').classList.remove('hidden', 'translate-x-full');
    }

    function hideDrawer() {
    document.getElementById('drawer').classList.add('translate-x-full');
    setTimeout(() => {
        document.getElementById('drawer').classList.add('hidden');
    }, 300); // Temps de la transition
    }
    // ----------------------FIN DRAWER SCRIPT---------------------

    // ------------------------DATE SCRIPT---------------------

    // Fonction pour obtenir la date du début de la semaine actuelle (lundi)
    function getStartOfWeek(date) {
    const day = date.getDay() || 7;
    const startOfWeek = new Date(date);
    if (day !== 1) {
        startOfWeek.setDate(date.getDate() + (1 - day));
    }
    return startOfWeek;
    }

    // Fonction pour afficher la semaine précédente
    function showPreviousWeek() {
    const currentStartDate = new Date(document.getElementById("calendar").dataset.startDate);
    const previousStartDate = new Date(currentStartDate);
    previousStartDate.setDate(previousStartDate.getDate() - 7);
    displayWeek(previousStartDate);
    }

    // Fonction pour afficher la semaine suivante
    function showNextWeek() {
    const currentStartDate = new Date(document.getElementById("calendar").dataset.startDate);
    const nextStartDate = new Date(currentStartDate);
    nextStartDate.setDate(nextStartDate.getDate() + 7);
    displayWeek(nextStartDate);
    }

    // Fonction pour afficher une semaine à partir de la date de début
    function displayWeek(startDate) {
    const dayNames = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"];
    const headerDays = document.querySelectorAll(".font-bold.border-r");

    const weekStartDate = new Date(startDate);
    const weekEndDate = new Date(startDate);
    weekEndDate.setDate(weekEndDate.getDate() + 4);

    const today = new Date();
    const todayString = today.toISOString().slice(0, 10); // Format de date YYYY-MM-DD

    const weekRangeText = `Semaine du ${weekStartDate.toLocaleDateString()} au ${weekEndDate.toLocaleDateString()}`;
    document.getElementById("weekRange").innerText = weekRangeText;

    for (let i = 0; i < dayNames.length; i++) {
        const currentDate = new Date(weekStartDate);
        const dateString = currentDate.toISOString().slice(0, 10); // Format de date YYYY-MM-DD

        headerDays[i].innerText = dayNames[i] + "\n" + currentDate.getDate();

        // Ajout de la classe 'current-day' pour le jour actuel
        if (dateString === todayString) {
        headerDays[i].classList.add('current-day');
        } else {
        headerDays[i].classList.remove('current-day');
        }

        weekStartDate.setDate(weekStartDate.getDate() + 1);
    }

    // Mettre à jour la date de début de la semaine dans l'attribut "data-start-date"
    document.getElementById("calendar").dataset.startDate = startDate.toISOString();
    }

    // Événements de clic sur les boutons de navigation
    document.getElementById("prevWeekBtn").addEventListener("click", showPreviousWeek);
    document.getElementById("nextWeekBtn").addEventListener("click", showNextWeek);

    // Initialiser le calendrier avec la semaine actuelle lors du chargement de la page
    const today = new Date();
    const startOfWeek = getStartOfWeek(today);
    displayWeek(startOfWeek);
    // ------------------------------------FIN DATE SCRIPT------------------------------