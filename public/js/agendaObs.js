const dpObs = new DayPilot.Calendar("dpObs", {
  locale: "fr-fr",
  startDate: DayPilot.Date.today(),
  viewType: "Week",
  businessBeginsHour: 8,
  businessEndsHour: 20,
  dayBeginsHour: 6,
  dayEndsHour: 21,
  showEventStartEnd: false,
  scrollLabelsVisible: true,
  eventResizeHandling: "Disabled",
  eventClickHandling: "Disabled",
  eventHoverHandling: "Disabled",
  eventMoveHandling: "Disabled",
  // onEventClicked: (args) => {
  //   DayPilot.Modal.alert(args.e.data.text);
  // },

});

dpObs.init();

const html = ` <div class="flex flex-col p-2 justify-start">
                <p class="text-sm text-gray-500">Entreprise : COLAS</p>
                <p class="text-sm text-gray-500">Module : Excel Niveau I</p>
                <p class="text-sm text-gray-500">Formateur : Rasoloarinaivo Ilaina</p>
                <p class="text-sm text-gray-500 w-full">Lieu : Carlton Anosy</p>
                <p class="text-sm text-gray-500">Matériel : 1PC</p>
                <div class="inline-flex items-center w-full justify-between gap-2">
                  <p class="text-sm text-gray-500">Apprenant : 02</p>
                  <p class="text-sm text-gray-500">Statut : Reporté</p>
                </div>
              </div>`;
const first = DayPilot.Date.today().firstDayOfWeek("fr-fr").addDays(1);
const events = [
  {
    start: first.addHours(12),
    end: first.addHours(15),
    id: 1,
    // text: "Event 1",
    html: html,
    barColor: "#3c78d8",
    barBackColor: "#a4c2f4"
  },
  {
    start: first.addDays(1).addHours(10),
    end: first.addDays(1).addHours(12),
    id: 2,
    // text: "Event 2",
    html: html,
    barColor: "#6aa84f",
    barBackColor: "#b6d7a8"
  },
  {
    start: first.addDays(1).addHours(13),
    end: first.addDays(1).addHours(15),
    id: 3,
    // text: "Event 3",
    html: html,
    barColor: "#f1c232",
    barBackColor: "#ffe599"
  },
  {
    start: first.addDays(2).addHours(11),
    end: first.addDays(2).addHours(16),
    id: 4,
    // text: "Event 4",
    html: html,
    barColor: "#cc0000",
    barBackColor: "#ea9999"
  },
];
dpObs.update({ events });