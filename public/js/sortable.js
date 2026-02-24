$(function () {
  $(".column").sortable({
    connectWith: ".column",
    update: function (event, ui) {
      // Code à exécuter lorsqu'une carte est déplacée
      var movedCard = ui.item.text();
      var sourceColumn = ui.sender ? ui.sender.attr("id") : "--";
      var targetColumn = $(this).attr("id");
    }
  });

  $("#sortable").sortable();
});