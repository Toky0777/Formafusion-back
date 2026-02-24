function mainAddSalle() {
    var salle_name = $('#main_salle_name');
    var salle_quartier = $('#main_salle_quartier');
    var salle_rue = $('#main_salle_rue');
    var salle_code = $('#main_salle_code_postal');
    var idVille = $('#main_salle_idVille');

    $.ajax({
        type: "post",
        url: "/etp/salles",
        data: {
            salle_name: salle_name.val(),
            salle_quartier: salle_quartier.val(),
            salle_rue: salle_rue.val(),
            salle_code_postal: salle_code.val(),
            idVille: idVille.val()
        },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                salle_name.val('');
                salle_quartier.val('');
                salle_rue.val('');
                salle_code.val('');
                toastr.success(res.success, 'Succès', {
                    timeOut: 1500
                });
                sessionStorage.removeItem('modalSalleState');
                location.reload();
            } else {
                $('#error_main_salle_name').text(res.idVille);
                $('#error_main_salle_idVille').text(res.salle_name);

                $('.main_salle_name').addClass('border-red-500');
                $('.main_salle_idVille').addClass('border-red-500');
            }
        },
        error: function (error) {
            console.log(error);
        }
    });

}

function mainLoadVille() {
    $.ajax({
        type: "get",
        url: "/etp/salles/loadVille",
        dataType: "json",
        success: function (res) {
            var villes = $('#main_salle_idVille');
            villes.html('');
            villes.append(`<option value="0" selected disabled>--selectionnez une ville--</option>`);
            $.each(res.villes, function (key, val) {
                villes.append(`<option value="` + val.idVille + `">` + val.ville + `</option>`);
            });
        }
    });
}


function closeSalleMain() {
    $("#screenSalle").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
    $("#modalSalle").hide();
    sessionStorage.removeItem('modalSalleState');
}

function ajoutSalle() {
    var salle_name = $('#main_salle_name').val();
    var salle_image = $('#main_salle_image')[0].files[0];
    var idLieu = $('#lieu').val();

    console.log("Lieu sélectionné : ", idLieu);
    console.log("Nom de la salle : ", salle_name);
    console.log("Image de la salle : ", salle_image);

    var formData = new FormData();
    formData.append('salle_name', salle_name);

    if (idLieu) {
        formData.append('idLieu', idLieu);
    }

    if (salle_image) {
        formData.append('salle_image', salle_image);
    }

    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    $('#loadingSpinner').show();

    $.ajax({
        type: "POST",
        url: "/etp/salles",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            if (res.success) {
                $('#main_salle_name').val('');
                $('#main_salle_image').val('');
                $('#lieu').val('');
                $('#loadingSpinner').hide();
                toastr.success(res.success, 'Succès', { timeOut: 1500 });
                sessionStorage.removeItem('modalSalleState');
                closeSalleMain();
                location.reload();
            } else {
                $('#error_main_salle_name').text(res.errors.salle_name || '');
                $('#error_main_salle_lieu').text(res.errors.idLieu || '');
                $('#error_main_salle_image').text(res.errors.salle_image || '');

                $('.main_salle_name').addClass('border-red-500');
                $('.main_salle_lieu').addClass('border-red-500');
                $('.main_salle_image').addClass('border-red-500');
            }
        },
        error: function (error) {
            ('#loadingSpinner').hide();
            toastr.error("Une erreur est survenue : " + error.responseText, 'Erreur', { timeOut: 1500 });
        },
        complete: function () {
            // Optionnel : Masquer le spinner à la fin, succès ou erreur
            $('#loadingSpinner').hide();
        }
    });
}

function modifSalle(element) {
    const dataId = element.getAttribute('data-id');
    const [idSalle, idLieu] = dataId.split('-');
    console.log("idSalle:", idSalle);
    console.log("idLieu:", idLieu);
    fetch(`/etp/getSalleDetails?idLieu=${idLieu}&idSalle=${idSalle}`)
        .then(response => response.json())
        .then(data => {
            // console.log("Données récupérées :", data);

            const idSalleField = document.getElementById('idSalle');
            const idLieuField = document.getElementById('idLieu');
            const liNameField = document.getElementById('li_name');
            const salleNameField = document.getElementById('salle_name');
            const liQuartierInput = document.getElementById('li_quartier')
            liQuartierInput.value = data.li_quartier;
            // document.getElementById('vi_code_postal').value = data.vi_code_postal;
            document.getElementById('ville_name').value = data.ville_name;
            document.getElementById('lt_name').value = data.lt_name;

            const idVilleSelect = document.getElementById('idVilleUpdate');
            idVilleSelect.value = data.idVille;

            // Mise à jour du select des codes postaux
            const idVilleCodedSelect = document.getElementById('idVilleCodedUpdate');
            // console.log(idVilleCodedSelect);

            // Effacer les options existantes
            idVilleCodedSelect.innerHTML = '';

            $(document).ready(function () {
                $.ajax({
                    type: "get",
                    url: "/etp/villes/" + data.idVille,
                    dataType: "json",
                    success: function (res) {
                        if (res.status == 200) {
                            const idVilleCodedSelect = $('#idVilleCodedUpdate'); // Cibler l'élément select
                            $.each(res.villeCodeds, function (key, val) {
                                const option = `<option value="${val.id}">${val.vi_code_postal} - ${val.ville_name}</option>`;
                                idVilleCodedSelect.append(option); // Ajouter l'option au select

                                // Pré-sélectionner l'option
                                if (parseInt(val.id) === parseInt(data.idVilleCoded)) {
                                    idVilleCodedSelect.val(val.id); // Utiliser .val() pour sélectionner
                                }
                            });
                        } else if (res.status == 404) {
                            console.log(res.message);
                        }
                    }
                });
            });

            // document.getElementById('idVilleVaovao').value = data.idVille;
            // document.getElementById('idLieuType').value = data.idLieuType;

            // var selectElement = document.getElementById('lt_name');
            // selectElement.value = data.idLieuType;


            if (idSalleField) idSalleField.value = data.idSalle;
            else console.error("Élément idSalle introuvable.");

            if (idLieuField) idLieuField.value = data.idLieu;
            else console.error("Élément idLieu introuvable.");

            if (liNameField) liNameField.value = data.li_name;
            else console.error("Élément li_name introuvable.");

            if (salleNameField) salleNameField.value = data.salle_name;
            else console.error("Élément salle_name introuvable.");

            // Image (facultatif)
            const salleImage = document.getElementById('salle_image');
            if (salleImage) {
                salleImage.src = data.salle_image ?
                    `https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/salles/${data.salle_image}` :
                    'https://via.placeholder.com/100';
            } else {
                console.error("Élément salle_image introuvable.");
            }

            // Modal
            const modal = document.getElementById('editSalleModal');
            if (modal) modal.classList.remove('hidden');
            else console.error("Modal editSalleModal introuvable.");
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des données :', error);
        });
}

let idLieuToDelete = null;
let idSalleToDelete = null;

// Gestion des modals
function openModal(idLieu, idSalle) {
    idLieuToDelete = idLieu;
    idSalleToDelete = idSalle;

    const modal = document.getElementById("confirmModal");
    if (modal) modal.classList.remove("hidden");

    const message = "Voulez-vous vraiment supprimer ?";
    const modalMessage = document.getElementById("modalMessage");
    if (modalMessage) modalMessage.textContent = message;
}

function closeModal() {
    const modal = document.getElementById("confirmModal");
    if (modal) modal.classList.add("hidden");
}

// Suppression de salle
function deleteSalle() {
    if (!idSalleToDelete) return;

    $.ajax({
        type: "DELETE",
        url: `/etp/salles/${idSalleToDelete}`,
        dataType: "json",
        success: function (res) {
            handleAjaxResponse(res, "Salle");
        },
        error: function () {
            console.error("Erreur lors de la suppression de la salle.");
        },
        complete: closeModal
    });
}

// Suppression de lieu
function deleteLieu() {
    if (!idLieuToDelete) return;

    $.ajax({
        type: "DELETE",
        url: `/etp/lieux_delete/${idLieuToDelete}`,
        dataType: "json",
        success: function (res) {
            handleAjaxResponse(res, "Lieu");
        },
        error: function () {
            console.error("Erreur lors de la suppression du lieu.");
        },
        complete: closeModal
    });
}

// function closeModal() {
//     document.getElementById('confirmModal').classList.add('hidden');
// }

function closeModalModif() {
    document.getElementById('editSalleModal').classList.add('hidden');
}

function openImageModal(image) {
    document.getElementById('image-modal').classList.remove('hidden');
    document.getElementById('modal-image').src = "https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/salles/" + image;
}


function closeImageModal(event = null) {
    if (event && event.target.id === "image-modal") {
        document.getElementById('image-modal').classList.add('hidden');
    } else if (!event) {
        document.getElementById('image-modal').classList.add('hidden');
    }
}

// Gestion des réponses AJAX
function handleAjaxResponse(res, type) {
    const messageMap = {
        200: () => toastr.success(res.message, "Succès", {
            timeOut: 1500
        }),
        400: () => toastr.error(res.message,
            `Veuillez supprimer la salle d'abord avant de supprimer le ${type.toLowerCase()}.`, {
            timeOut: 1500
        }),
        404: () => toastr.error(`${type} introuvable.`, {
            timeOut: 1500
        }),
        500: () => toastr.error(`Impossible de supprimer ce ${type.toLowerCase()} !`, {
            timeOut: 15000
        }),
        default: () => toastr.error("Erreur inconnue !", "Erreur", {
            timeOut: 1500
        })
    };

    (messageMap[res.status] || messageMap.default)();
    if (res.status === 200) location.reload();
}

function getUpdateVilleCodeds() {
    var idVille = $('#idVilleUpdate').val();
    var idVilleCoded = $('#idVilleCodedUpdate');

    $.ajax({
        type: "get",
        url: "/etp/villes/" + idVille,
        dataType: "json",
        success: function (res) {
            idVilleCoded.empty();
            if (res.status == 200) {
                $.each(res.villeCodeds, function (key, val) {
                    idVilleCoded.append(
                        `<option value="${val.id}">${val.vi_code_postal} - ${val.ville_name}</option>`
                    );
                });
            } else if (res.status == 404) {
                console.log(res.message);
            }
        }
    });
}

const liQuartierInput = document.getElementById('li_quartier');
if (liQuartierInput) {
    liQuartierInput.addEventListener('input', function () {
        const input = this.value;
        const suggestionBox = document.getElementById('quartierSuggestions');
        suggestionBox.innerHTML = ''; // Vider les suggestions
        suggestionBox.classList.add('hidden');

        if (input.length >= 0) { // Commence la recherche après 0 caractère
            fetch('/etp/quartier')
                .then(response => response.json())
                .then(data => {
                    const filtered = data.filter(item =>
                        // item.li_quartier.toLowerCase().includes(input.toLowerCase())
                        item.li_quartier &&
                        item.li_quartier.toLowerCase().startsWith(input.toLowerCase()) // Recherche stricte au début
                    );

                    if (filtered.length > 0) {
                        suggestionBox.classList.remove('hidden');
                        filtered.forEach(item => {
                            const div = document.createElement('div');
                            div.textContent = item.li_quartier;
                            div.className = 'px-2 py-1 cursor-pointer hover:bg-gray-200';
                            div.addEventListener('click', function () {
                                const liQuartierInput = document.getElementById('li_quartier')
                                liQuartierInput.value = item.li_quartier;
                                suggestionBox.classList.add('hidden');
                            });
                            suggestionBox.appendChild(div);
                        });
                    }
                    if (filtered.length === 0) {
                        const div = document.createElement('div');
                        div.textContent = 'Aucune suggestion disponible';
                        div.className = 'px-2 py-1 text-gray-500';
                        suggestionBox.appendChild(div);
                        suggestionBox.classList.remove('hidden');
                    }
                })
                .catch(error => console.error('Erreur lors de la récupération des suggestions:', error));
        } else {
            suggestionBox.classList.add('hidden'); // Cacher les suggestions si aucun caractère
        }
    });

    // Masquer les suggestions si l'utilisateur clique ailleurs
    document.addEventListener('click', function (event) {
        const suggestionBox = document.getElementById('quartierSuggestions');
        if (!event.target.closest('#li_quartier')) {
            suggestionBox.classList.add('hidden');
        }
    });

}