document.addEventListener("DOMContentLoaded", function () {
    var calendarEl = document.getElementById("calendar");

    var calendar = new tui.Calendar(calendarEl, {
        defaultView: "month",
        taskView: false,
        scheduleView: ["time"],
        useFormPopup: true,
        useDetailPopup: true,

        week: {
            startDayOfWeek: 1, // Lundi
            daynames: [
                "Dimanche",
                "Lundi",
                "Mardi",
                "Mercredi",
                "Jeudi",
                "Vendredi",
                "Samedi",
            ], // Noms des jours en français
            hourStart: 7,
            hourEnd: 18,
        },

        month: {
            startDayOfWeek: 1,
            daynames: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"],
        },

        //   affichage
        template: {
            time: function (schedule) {
                return (
                    "<div>" +
                    "<span>" +
                    schedule.start.getHours() +
                    ":" +
                    ("0" + schedule.start.getMinutes()).slice(-2) +
                    " " +
                    "</span>" +
                    "<span>" +
                    schedule.title +
                    "</span>" +
                    "</div>"
                );
            },

            popupDetailBody: function (schedule) {
                return getPopupContent(schedule);
            },
        },
    });

    //POPUP antsoina any amin'ny template
    function getPopupContent(schedule) {
        var content = '<div class="flew">';

        // Récupérer les propriétés raw
        var raw = schedule.raw;
        for (var key in raw) {
            var value = raw[key];
            if (typeof value === "object") {
                value = JSON.stringify(value, null, 2); // Formater les objets JSON
            }

            if (key === "url") {
                content += '<div class="flex items-center justify-center">';
                content += '<img src="' + value + '" class="h-[50px]">';
                content += "</div>";
            } else {
                content += '<div class="flex">';
                content +=
                    '<p class="text-lg font-medium text-[#212529]">' +
                    key +
                    "</p>";
                content +=
                    '<p class="text-lg font-medium">' + ": " + value + "</p>";
                content += "</div>";
            }
        }

        content += "</div>";
        return content;
    }

    //Selection du mois
    var monthSelector = document.getElementById("monthSelector");
    var monthElements = monthSelector.querySelectorAll("p[data-month]");

    // Événement click sur les éléments de mois
    monthElements.forEach(function (monthElement) {
        monthElement.addEventListener("click", function () {
            var selectedMonth = monthElement.dataset.month;
            if (selectedMonth >= 1 && selectedMonth <= 12) {
                // Récupérer l'année actuellement sélectionnée dans le sélecteur d'année
                var selectedYear = parseInt(yearSelector.value);
                if (!isNaN(selectedYear)) {
                    var selectedDate = new Date(
                        selectedYear,
                        selectedMonth - 1,
                        1
                    );
                    calendar.setDate(selectedDate);
                    updateCurrentMonthText();
                }
            }
        });
    });

    // Fonction utilitaire pour obtenir le nom du mois en fonction de son numéro
    function getMonthName(monthNumber) {
        var months = [
            "Janvier",
            "Février",
            "Mars",
            "Avril",
            "Mai",
            "Juin",
            "Juillet",
            "Août",
            "Septembre",
            "Octobre",
            "Novembre",
            "Décembre",
        ];
        return months[monthNumber];
    }
    var currentMonthEl = document.getElementById("currentMonth");

    // Fonction pour mettre à jour la barre de titre avec le mois sélectionné (current month)
    function updateCurrentMonthText() {
        var currentDate = calendar.getDate();
        var currentMonth = currentDate.getMonth();
        var currentYear = currentDate.getFullYear();
        var monthName = getMonthName(currentMonth);
        currentMonthEl.textContent = monthName + " " + currentYear;
    }

    // Précédent, suivant
    var prevMonthBtn = document.getElementById("prevMonthBtn");
    var nextMonthBtn = document.getElementById("nextMonthBtn");

    // Événement clic sur le bouton précédent
    prevMonthBtn.addEventListener("click", function () {
        calendar.prev();
        updateCurrentMonthText();
    });

    // Événement clic sur le bouton suivant
    nextMonthBtn.addEventListener("click", function () {
        calendar.next();
        updateCurrentMonthText();
    });

    // Affiche le mois actuel au chargement de la page
    updateCurrentMonthText();

    // Sélectionnez une année
    var yearSelector = document.getElementById("yearSelector");

    // Fonction pour générer les options d'année dans la liste déroulante
    function generateYearOptions() {
        // Effacer les options actuelles
        yearSelector.innerHTML = "";

        // Ajouter l'année actuelle
        var currentYear = new Date().getFullYear();

        // Nombre d'années à afficher avant et après l'année actuelle
        var yearsToShow = 3;

        // Générer les options d'année
        for (
            var i = currentYear - yearsToShow;
            i <= currentYear + yearsToShow;
            i++
        ) {
            var option = document.createElement("option");
            option.textContent = i.toString();
            yearSelector.appendChild(option);
        }

        // Sélectionner l'année actuelle par défaut
        yearSelector.value = currentYear;
    }

    // Générer les options d'année initiales lors du chargement de la page
    generateYearOptions();

    // Écouter l'événement change de la liste déroulante
    yearSelector.addEventListener("change", function () {
        var selectedYear = parseInt(yearSelector.value);
        if (!isNaN(selectedYear)) {
            // Récupérer la date sélectionnée actuellement dans le calendrier
            var currentCalendarDate = calendar.getDate();

            // Mettre à jour le calendrier avec la nouvelle date de l'année sélectionnée
            var newCalendarDate = new Date(
                selectedYear,
                currentCalendarDate.getMonth(),
                1
            );
            calendar.setDate(newCalendarDate);

            // Mettre à jour l'en-tête après avoir changé l'année dans le calendrier
            updateCurrentMonthText();
        }
    });

    // FIN Précédent, current month, suivant, ANNEE

    // Événement pour changer la vue du calendrier
    function changeCalendarView(viewName) {
        calendar.changeView(viewName);
    }

    // Boutons pour changer la vue du calendrier
    // var dayViewBtn = document.getElementById('dayViewBtn');
    var weekViewBtn = document.getElementById("weekViewBtn");
    var monthViewBtn = document.getElementById("monthViewBtn");

    // dayViewBtn.addEventListener('click', function() {
    //   changeCalendarView('day');
    // });

    weekViewBtn.addEventListener("click", function () {
        changeCalendarView("week");
    });

    monthViewBtn.addEventListener("click", function () {
        changeCalendarView("month");
    });
    // FIN pour changer la vue du calendrier

    getEvent();

    // getSeanceCount(7);

    function getEvent() {
        $.ajax({
            type: "get",
            url: "/agendaForms/getEvent",
            dataType: "json",
            success: function (response) {
                calendar.createSchedules(response.events);

                $.each(response.events, function (key, val) {
                    calendar.setCalendarColor(val.calendarId, {
                        bgColor: "#bfdbfe",
                        borderColor: "#0073cc",
                        dragBgColor: "#bfdbfe",
                    });
                });
            },
        });
    }

    // var newYear = new Date().getFullYear();
    // $(".nbrSeance1").text(getSeanceCount(1, newYear));
    // $(".nbrSeance2").text(getSeanceCount(2, newYear));
    // $(".nbrSeance3").text(getSeanceCount(3, newYear));
    // $(".nbrSeance4").text(getSeanceCount(4, newYear));
    // $(".nbrSeance5").text(getSeanceCount(5, newYear));
    // $(".nbrSeance6").text(getSeanceCount(6, newYear));
    // $(".nbrSeance7").text(getSeanceCount(7, newYear));
    // $(".nbrSeance8").text(getSeanceCount(8, newYear));
    // $(".nbrSeance9").text(getSeanceCount(9, newYear));
    // $(".nbrSeance10").text(getSeanceCount(10, newYear));
    // $(".nbrSeance11").text(getSeanceCount(11, newYear));
    // $(".nbrSeance0").text(getSeanceCount(12, newYear));
});

function getSeanceCount(monthKey, yearKey) {
    $.ajax({
        type: "get",
        url: "/agendaForms/countSeance/" + monthKey + "/" + yearKey,
        dataType: "json",
        success: function (res) {
            $(".nbrSeance" + monthKey).text(res[0].nbSeance);
        },
    });
}

$(document).on("change", "#yearSelector", function () {
    $(".nbrSeance1").text(getSeanceCount(1, parseInt($(this).val())));
    $(".nbrSeance2").text(getSeanceCount(2, parseInt($(this).val())));
    $(".nbrSeance3").text(getSeanceCount(3, parseInt($(this).val())));
    $(".nbrSeance4").text(getSeanceCount(4, parseInt($(this).val())));
    $(".nbrSeance5").text(getSeanceCount(5, parseInt($(this).val())));
    $(".nbrSeance6").text(getSeanceCount(6, parseInt($(this).val())));
    $(".nbrSeance7").text(getSeanceCount(7, parseInt($(this).val())));
    $(".nbrSeance8").text(getSeanceCount(8, parseInt($(this).val())));
    $(".nbrSeance9").text(getSeanceCount(9, parseInt($(this).val())));
    $(".nbrSeance10").text(getSeanceCount(10, parseInt($(this).val())));
    $(".nbrSeance11").text(getSeanceCount(11, parseInt($(this).val())));
    $(".nbrSeance12").text(getSeanceCount(12, parseInt($(this).val())));
});

$(document).on("click", ["#prevMonthBtn", "#nextMonthBtn"], function () {
    var newDate = $("#currentMonth").text();
    var yearKey = newDate.split(" ");

    $(".nbrSeance1").text(getSeanceCount(1, yearKey[1]));
    $(".nbrSeance2").text(getSeanceCount(2, yearKey[1]));
    $(".nbrSeance3").text(getSeanceCount(3, yearKey[1]));
    $(".nbrSeance4").text(getSeanceCount(4, yearKey[1]));
    $(".nbrSeance5").text(getSeanceCount(5, yearKey[1]));
    $(".nbrSeance6").text(getSeanceCount(6, yearKey[1]));
    $(".nbrSeance7").text(getSeanceCount(7, yearKey[1]));
    $(".nbrSeance8").text(getSeanceCount(8, yearKey[1]));
    $(".nbrSeance9").text(getSeanceCount(9, yearKey[1]));
    $(".nbrSeance10").text(getSeanceCount(10, yearKey[1]));
    $(".nbrSeance11").text(getSeanceCount(11, yearKey[1]));
    $(".nbrSeance12").text(getSeanceCount(12, yearKey[1]));
});
