$(document).ready(function () {
  // Sélectionner les éléments de pagination
  var paginationItems = $('#pagination nav ul.pagination li');

  // Itérer à travers chaque élément de pagination
  paginationItems.each(function (index) {
    // Vérifier si l'élément est un nombre de page ou un point de suspension
    if (!$(this).hasClass('disabled') && !$(this).hasClass('active') && !$(this).hasClass('page-link')) {
      // Masquer l'élément si ce n'est pas la page actuelle ou les deux pages précédentes ou suivantes des points de suspension
      if (index < paginationItems.index($('.active')) - 2 || index > paginationItems.index($('.active')) +
        2) {
        $(this).hide();
      }
    }
  });

  setTimeout(() => {
    $('#pagination').show();
  }, 500);
});