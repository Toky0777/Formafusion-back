$(document).ready(function () {
  $('.domaineCheckbox').click(function (e) {
    const nameDomaine = $(this).attr('name');
    // Récupérer l'état actuel de la case à cocher pour ce domaine
    var isChecked = $(this).prop('checked');

    // Parcourir tous les éléments .contentDomaine pour ce domaine
    $('.contentDomaine[id="' + nameDomaine + '"]').each(function () {
      // Afficher ou masquer le contenu en fonction de l'état de la case à cocher
      if (isChecked) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });
});

$(document).ready(function () {
  $('.allDomaineOnline').click(function (e) {
    var allChecked = $(".allDomaineOnline").prop('checked');
    $('.allDomaineOnline').prop('checked', !allChecked);
    // Inverser l'état de cochage de tous les éléments .domaineCheckbox
    $('.domaineCheckbox').each(function () {
      if (allChecked) {
        // Parcourir tous les éléments .contentDomaine pour chaque domaine
        $('.contentDomaine').each(function () {
          // Inverser la visibilité de chaque élément .contentDomaine
          $(this).show();
        });
        $(this).prop('checked', true);
      } else {
        // Parcourir tous les éléments .contentDomaine pour chaque domaine
        $('.contentDomaine').each(function () {
          // Inverser la visibilité de chaque élément .contentDomaine
          $(this).hide();
        });
        $(this).prop('checked', false);
      }
    });
  });
});



$(document).ready(function () {
  $('.domaineCheckboxOffline').click(function (e) {
    const nameDomaineOffline = $(this).attr('name');
    // Récupérer l'état actuel de la case à cocher pour ce domaine
    var isChecked = $(this).prop('checked');

    // Parcourir tous les éléments .contentDomaineOffline pour ce domaine
    $('.contentDomaineOffline[id="' + nameDomaineOffline + '"]').each(function () {
      // Afficher ou masquer le contenu en fonction de l'état de la case à cocher
      if (isChecked) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });
});

$(document).ready(function () {
  $('.allDomaineOff').click(function (e) {
    var allChecked = $(".allDomaineOff").prop('checked');
    $('.allDomaineOff').prop('checked', !allChecked);
    // Inverser l'état de cochage de tous les éléments .domaineCheckboxOffline
    $('.domaineCheckboxOffline').each(function () {
      if (allChecked) {
        // Parcourir tous les éléments .contentDomaineOffline pour chaque domaine
        $('.contentDomaineOffline').each(function () {
          // Inverser la visibilité de chaque élément .contentDomaineOffline
          $(this).show();
        });
        $(this).prop('checked', true);
      } else {
        // Parcourir tous les éléments .contentDomaineOffline pour chaque domaine
        $('.contentDomaineOffline').each(function () {
          // Inverser la visibilité de chaque élément .contentDomaineOffline
          $(this).hide();
        });
        $(this).prop('checked', false);
      }
    });
  });
});