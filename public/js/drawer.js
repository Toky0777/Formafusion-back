$(document).ready(function() {
    $(".open-drawer").click(function() {
        var targetDrawerID = $(this).data("target");
        $("#" + targetDrawerID).toggleClass("translate-x-full"); /*Ouvrir ou fermer le drawer */
    });

    /* Boutons de fermeture des drawers */
    $(".fermer-drawer").click(function() {
        var targetDrawerID = $(this).data("target");
        $("#" + targetDrawerID).toggleClass("translate-x-full");
    });
});
