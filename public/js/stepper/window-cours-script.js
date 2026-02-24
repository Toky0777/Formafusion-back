$(document).ready(function () {
  var isMinimizedClient = false;

  const reduireClient = `fa-solid fa-minus`;
  const retablirClient = `fa-solid fa-up-right-and-down-left-from-center text-sm`;
  $('.reduireClient').addClass(reduireClient);

  // Vérifier s'il y a une configuration précédente pour le modalClient
  var modalClientState = sessionStorage.getItem('modalClientState');
  if (modalClientState === 'minimized') {
    minimizeModal();
  } else if (modalClientState === 'normal') {
    openModal();
  }

  // Ouvrir le modalClient
  function openModal() {
    $("#modalClient").show();
  }

  // Ouvrir le modalClient
  $("#openModalBtnClient").click(function () {
    sessionStorage.setItem('modalClientState', 'normal');
    openModal();
  });

  // Fermer le modalClient
  $(".closeClient").click(function () {
    $("#modalClient").hide();
    sessionStorage.removeItem('modalClientState');
  });

  // Réduire ou rétablir le modalClient
  $("#minimizeBtnClient").click(function () {
    if (isMinimizedClient) {
      // Rétablir la taille principale du modalClient
      $("#modalClient").css({
        "height": "auto",
        "max-height": "none",
        "max-width": "none",
        "overflow": "visible",
      });
      $('.reduireClient').addClass(reduireClient);
      isMinimizedClient = false;
      sessionStorage.setItem('modalClientState', 'normal');
    } else {
      minimizeModal();
    }
  });

  // Réduire le modalClient
  function minimizeModal() {
    $("#modalClient").css({
      "height": "auto",
      "max-height": "50px",
      "max-width": "300px",
      "overflow": "hidden",
      "right": "320px",
    });
    $('#modalClient').removeClass("hidden");
    $('.reduireClient').addClass(retablirClient);
    isMinimizedClient = true;
    sessionStorage.setItem('modalClientState', 'minimized');
  }
});