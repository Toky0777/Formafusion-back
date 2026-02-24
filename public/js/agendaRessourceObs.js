$(document).ready(function () {

  function filter() {
    // see dpObs.onRowFilter below
    dpObsRessource.rows.filter({
      query: $("#filter").val(),
      hideEmpty: $("#hideEmpty").is(":checked")
    });
  }

  $("#filter").keyup(function () {
    filter();
  });

  $("#hideEmpty").change(function () {
    filter();
  });

  $("#clear").click(function () {
    $("#filter").val("");
    filter();
    return false;
  });
});

const dpObsRessource = new DayPilot.Scheduler("dpObsRessource", {
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
  startDate: DayPilot.Date.today(),
  eventHeight: 20,
  timeRangeSelectedHandling: "Enabled",
  crosshairType: "Full",
  // onTimeRangeSelected: async (args) => {
  //   const dpObsRessource = args.control;
  //   const form = [
  //     {
  //       html: `
  //       <div class="flex flex-col gap-2">
  //         <label class="text-xl text-gray-700 font-semibold">Ajouter une session</label>

  //         <div class="inline-flex items-center w-full gap-2">
  //           <div class="flex flex-col w-full gap-1">
  //             <label class="text-base text-gray-500">De</label>
  //             <input type="time" format="hh:mm" class="outline-none w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400" />
  //           </div>

  //           <div class="flex flex-col w-full gap-1">
  //             <label class="text-base text-gray-500">Jusqu'à</label>
  //             <input type="time" format="hh:mm" class="outline-none w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400" />
  //           </div>
  //         </div>
  //       </div>
  //       `
  //     },
  //     {
  //       type: 'radio',
  //       id: 'statut',
  //       name: 'Statut',
  //       options: [
  //         {
  //           name: 'Réservé',
  //           id: 'reserve',
  //           value: 'reserve',
  //         },
  //         {
  //           name: 'Brouillon',
  //           id: 'brouillon',
  //           value: 'brouillon',
  //         },
  //         {
  //           name: 'Prévisionnel',
  //           id: 'previsionnel',
  //           value: 'previsionnel',
  //         },
  //         {
  //           name: 'En cours',
  //           id: 'encours',
  //           value: 'encours',
  //         },
  //         {
  //           name: 'Annulé',
  //           id: 'annule',
  //           value: 'annule',
  //         },
  //       ],
  //     },
  //   ];

  //   const modal = await DayPilot.Modal.form(form).then(function (modal) {
  //     if (modal.canceled) { return modal.result; }
  //     dpObsRessource.clearSelection();
  //     console.log(modal.result);
  //     let backColor = "";

  //     if (modal.result.statut === 'reserve') {
  //       backColor = "#1F2937";
  //     } else if (modal.result.statut === 'annule') {
  //       backColor = "#EF4444";
  //     } else if (modal.result.statut === 'previsionnel') {
  //       backColor = "#F59E0B";
  //     } else if (modal.result.statut === 'encours') {
  //       backColor = "#A462A4";
  //     } else {
  //       backColor = "#6B7280";
  //     }
  //     dpObsRessource.events.add({
  //       start: args.start,
  //       end: args.end,
  //       id: DayPilot.guid(),
  //       resource: args.resource,
  //       "height": 20,
  //       barColor: backColor,
  //       backColor: backColor,
  //       borderColor: backColor,
  //     });
  //   });
  // },

  eventMoveHandling: "Update",
  onEventMoved: (args) => {
    args.control.message("Vous avez déplacé un évènement.");
  },
  eventResizeHandling: "Update",
  onEventResized: (args) => {
    args.control.message("Vous avez modifié la date d'un évènement. ");
  },
  // eventDeleteHandling: "Update",
  // onEventDeleted: (args) => {
  //   args.control.message("Vous avez supprimer un évènement.");
  // },
  eventClickHandling: "Disabled",
  eventHoverHandling: "Bubble",
  bubble: new DayPilot.Bubble({
    onLoad: (args) => {
      const bubbleHtml = `
      <div class="flex flex-col gap-2 p-2 bg-white">
        <div class="flex flex-col">
          <div class=" inline-flex w-full justify-between items-center gap-2">
            <p class="text-xl font-semibold text-gray-700 capitalize">Power BI Niveau I</p>
            <p class="text-gray-400">Ref : P-001</p>
          </div>
          <div class="flex flex-row items-center gap-2">
            <p class="text-gray-500">Le 16 février 2024</p>
            <p class="text-gray-500">de 9h à 11h</p>
          </div>
        </div>

        <div class=" inline-flex w-full justify-between items-center gap-2">
          <p class="text-gray-500">Client : KENTIA</p>
        </div>
        <div class="flex flex-col">
          <p class="text-gray-500">Formateur : RASOLOFONIMANANA Tahiry</p>
          <p class="text-gray-500">Module : Power BI 1</p>
          <p class="text-gray-500">Salle : Salle 3</p>
        </div>
      </div>
      `;
      // if the event object doesn't specify "bubbleHtml" property
      // this onLoad handler will be called to provide the bubble HTML
      args.html = bubbleHtml;
    }
  }),
  treeEnabled: true,
});

dpObsRessource.init();
// dpObsRessource.scrollTo("2023-09-01");

const app = {
  elements: {
    filter: document.querySelector("#filter"),
    hideEmpty: document.querySelector("#hideEmpty"),
    clear: document.querySelector("#clear"),
  },
  filter() {
    // see dpObsRessource.onRowFilter above
    dpObsRessource.rows.filter({
      query: app.elements.filter.value,
      hideEmpty: app.elements.hideEmpty.checked
    });
  },
  init() {
    app.elements.filter.addEventListener("keyup", function () {
      app.filter();
    });

    app.elements.hideEmpty.addEventListener("change", function () {
      app.filter();
    });

    app.elements.clear.addEventListener("click", function (ev) {
      ev.preventDefault();
      app.elements.filter.value = "";
      app.filter();
    });
  },

  loadData() {
    const resources = [
      {
        name: "Projets", id: "G1", expanded: true, children: [
          { name: "P-001", id: "P1" },
          { name: "P-002", id: "P2" },
          { name: "P-003", id: "P3" },
          { name: "P-004", id: "P4" },
        ]
      },
      {
        name: "Centre de formation", id: "G2", expanded: true, children: [
          { name: "NUMERIKA CENTER", id: "CL" },
          { name: "CFPM", id: "KT" },
          { name: "CEGOS", id: "LC" }
        ]
      },
      {
        name: "Apprenants", id: "G3", expanded: true, children: [
          { name: "Tahiry", id: "THR" },
          { name: "Faniry", id: "FNR" },
          { name: "Sedra", id: "SDR" },
          { name: "Stella", id: "STL" }
        ]
      },
      {
        name: "Modules", id: "G4", expanded: true, children: [
          { name: "Excel 1", id: "EX1" },
          { name: "Excel 2", id: "EX2" },
          { name: "Power BI 1", id: "PB1" },
          { name: "Power BI 2", id: "PB2" }
        ]
      },
      // {
      //   name: "Salle", id: "G4", expanded: true, children: [
      //     { name: "Salle 1", id: "S1" },
      //     { name: "Salle 2", id: "S2" },
      //     { name: "Salle 3", id: "S3" },
      //     { name: "Salle 4", id: "S4" }
      //   ]
      // },
    ];

    const events = [
      {
        "start": first.addHours(12),
        "end": first.addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4fa00f4d",
        "resource": "P1",
        "height": 20,
        "barColor": "#A462A4",
        "backColor": "#A462A4",
        "borderColor": "#A462A4",
      },
      {
        "start": first.addHours(12),
        "end": first.addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4fa00f4dsfd",
        "resource": "KT",
        "height": 20,
        "barColor": "#A462A4",
        "backColor": "#A462A4",
        "borderColor": "#A462A4",
      },
      {
        "start": first.addHours(12),
        "end": first.addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4fa00f4dsdf",
        "resource": "THR",
        "height": 20,
        "barColor": "#A462A4",
        "backColor": "#A462A4",
        "borderColor": "#A462A4",
      },
      {
        "start": first.addHours(12),
        "end": first.addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4fa0fdsg0f4d",
        "resource": "PB1",
        "height": 20,
        "barColor": "#A462A4",
        "backColor": "#A462A4",
        "borderColor": "#A462A4",
      },
      {
        "start": first.addHours(12),
        "end": first.addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4fa0dsfqs0f4d",
        "resource": "S3",
        "height": 20,
        "barColor": "#A462A4",
        "backColor": "#A462A4",
        "borderColor": "#A462A4",
      },

      // EVENT 2
      {
        "start": first.addDays(1).addHours(13),
        "end": first.addDays(1).addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3asqfsd6f4fdfga00f4d",
        "resource": "P3",
        "height": 20,
        "barColor": "#787F8C",
        "backColor": "#787F8C",
        "borderColor": "#787F8C",
      },
      {
        "start": first.addDays(1).addHours(13),
        "end": first.addDays(1).addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6fsdf4fa00dfgdsgf4dsfd",
        "resource": "CL",
        "height": 20,
        "barColor": "#787F8C",
        "backColor": "#787F8C",
        "borderColor": "#787F8C",
      },
      {
        "start": first.addDays(1).addHours(13),
        "end": first.addDays(1).addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4fa0dfgdfg0f4dsdf",
        "resource": "THR",
        "height": 20,
        "barColor": "#787F8C",
        "backColor": "#787F8C",
        "borderColor": "#787F8C",
      },
      {
        "start": first.addDays(1).addHours(13),
        "end": first.addDays(1).addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3dfga6f4fa0fdsg0f4d",
        "resource": "EX1",
        "height": 20,
        "barColor": "#787F8C",
        "backColor": "#787F8C",
        "borderColor": "#787F8C",
      },
      {
        "start": first.addDays(1).addHours(13),
        "end": first.addDays(1).addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6fdsfgdfg4fa0dsfqs0f4d",
        "resource": "S1",
        "height": 20,
        "barColor": "#787F8C",
        "backColor": "#787F8C",
        "borderColor": "#787F8C",
      },


      // EVENT 3
      {
        "start": first.addDays(5).addHours(11),
        "end": first.addDays(5).addHours(16),
        "id": "73c2016a-dd02-dae5-3ed3-3asqfsd6f4fdfga00f4qdfsd",
        "resource": "P4",
        "height": 20,
        "barColor": "#F59E0B",
        "backColor": "#F59E0B",
        "borderColor": "#F59E0B",
      },
      {
        "start": first.addDays(5).addHours(11),
        "end": first.addDays(5).addHours(16),
        "id": "73c2016a-dd02-dae5-3ed3-3a6fsdf4fa00dfgdsgf4dsfqdfsd",
        "resource": "LC",
        "height": 20,
        "barColor": "#F59E0B",
        "backColor": "#F59E0B",
        "borderColor": "#F59E0B",
      },
      {
        "start": first.addDays(5).addHours(11),
        "end": first.addDays(5).addHours(16),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4fa0dfgdfg0f4dsdqdfsf",
        "resource": "SDR",
        "height": 20,
        "barColor": "#F59E0B",
        "backColor": "#F59E0B",
        "borderColor": "#F59E0B",
      },
      {
        "start": first.addDays(5).addHours(11),
        "end": first.addDays(5).addHours(16),
        "id": "73c2016a-dd02-dae5-3ed3-3dfga6f4fa0fdsg0f4qdfsd",
        "resource": "EX2",
        "height": 20,
        "barColor": "#F59E0B",
        "backColor": "#F59E0B",
        "borderColor": "#F59E0B",
      },
      {
        "start": first.addDays(5).addHours(11),
        "end": first.addDays(5).addHours(16),
        "id": "73c2016a-dd02-dae5-3ed3-3a6fdsfgdfg4fa0dsfqs0f4qdfsd",
        "resource": "S2",
        "height": 20,
        "barColor": "#F59E0B",
        "backColor": "#F59E0B",
        "borderColor": "#F59E0B",
      },

      // EVENT 4
      {
        "start": first.addDays(7).addHours(13),
        "end": first.addDays(7).addHours(16),
        "id": "73c2016a-dd02-dae5-3ed3-3asqfsd6fsqdfdsqf4fdfga00f4qdfsd",
        "resource": "P2",
        "height": 20,
        "barColor": "#F59E0B",
        "backColor": "#F59E0B",
        "borderColor": "#F59E0B",
      },
      {
        "start": first.addDays(7).addHours(13),
        "end": first.addDays(7).addHours(16),
        "id": "73c2016a-dd02-dae5-3ed3-3a6fsdfsdqfsdf4fa00dfgdsgf4dsfqdfsd",
        "resource": "LC",
        "height": 20,
        "barColor": "#F59E0B",
        "backColor": "#F59E0B",
        "borderColor": "#F59E0B",
      },
      {
        "start": first.addDays(7).addHours(13),
        "end": first.addDays(7).addHours(16),
        "id": "73c2016a-dd02-dae5sdqf-3ed3-3a6f4fa0dfgdfg0f4dsdqdfsf",
        "resource": "SDR",
        "height": 20,
        "barColor": "#F59E0B",
        "backColor": "#F59E0B",
        "borderColor": "#F59E0B",
      },
      {
        "start": first.addDays(7).addHours(13),
        "end": first.addDays(7).addHours(16),
        "id": "73c2016a-dd02-dae5-3ed3-sdfsd",
        "resource": "PB1",
        "height": 20,
        "barColor": "#F59E0B",
        "backColor": "#F59E0B",
        "borderColor": "#F59E0B",
      },
      {
        "start": first.addDays(7).addHours(13),
        "end": first.addDays(7).addHours(16),
        "id": "73c2016a-dd02-dae5-sdfsd-3a6fdsfgdfg4fa0dsfqs0f4qdfsd",
        "resource": "S1",
        "height": 20,
        "barColor": "#F59E0B",
        "backColor": "#F59E0B",
        "borderColor": "#F59E0B",
      },

      // EVENT 5
      {
        "start": first.addDays(18).addHours(12),
        "end": first.addDays(18).addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4qdsfdsffa00f4d",
        "resource": "P1",
        "height": 20,
        "barColor": "#787F8C",
        "backColor": "#787F8C",
        "borderColor": "#787F8C",
      },
      {
        "start": first.addDays(18).addHours(12),
        "end": first.addDays(18).addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4sdfsdffa00f4dsfd",
        "resource": "KT",
        "height": 20,
        "barColor": "#787F8C",
        "backColor": "#787F8C",
        "borderColor": "#787F8C",
      },
      {
        "start": first.addDays(18).addHours(12),
        "end": first.addDays(18).addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4fsdfqsdfa00f4dsdf",
        "resource": "THR",
        "height": 20,
        "barColor": "#787F8C",
        "backColor": "#787F8C",
        "borderColor": "#787F8C",
      },
      {
        "start": first.addDays(18).addHours(12),
        "end": first.addDays(18).addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4fasqdfsdf0fdsg0f4d",
        "resource": "PB1",
        "height": 20,
        "barColor": "#787F8C",
        "backColor": "#787F8C",
        "borderColor": "#787F8C",
      },
      {
        "start": first.addDays(18).addHours(12),
        "end": first.addDays(18).addHours(15),
        "id": "73c2016a-dd02-dae5-3ed3-3a6f4fa0dfsdfqdsfqs0f4d",
        "resource": "S3",
        "height": 20,
        "barColor": "#787F8C",
        "backColor": "#787F8C",
        "borderColor": "#787F8C",
      },
    ];
    dpObsRessource.update({ resources, events });
  },
};

app.loadData();