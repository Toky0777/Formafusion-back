
// Récupérer tous les éléments de navigation
const navItems = $('.navElement');
const deconnexions = $('.deconnexion')

// Ajouter un gestionnaire d'événement de clic à chaque élément
for (let i = 0; i < navItems.length; i++) {
    navItems[i].addEventListener('click', function (event) {
        // Enregistrer l'index de l'élément sélectionné dans localStorage
        localStorage.setItem('selectedNavItem', i.toString());
    });
}
// Récupérer l'index de l'élément sélectionné depuis localStorage
const selectedNavItemIndex = localStorage.getItem('selectedNavItem');

// Vérifier si un élément a été sélectionné précédemment
if (selectedNavItemIndex !== null) {
    // Appliquer l'état actif à l'élément de navigation correspondant
    navItems[selectedNavItemIndex].addClass('active');
}

// Ajouter un gestionnaire d'événement de clic à chaque élément
for (let i = 0; i < deconnexions.length; i++) {
    deconnexions[i].addEventListener('click', function (event) {
        // Enregistrer l'index de l'élément sélectionné dans localStorage
        localStorage.setItem('selectedNavItem', '0')
    });
}
