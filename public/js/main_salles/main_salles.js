function mainAddSalle() {
    var salle_name = $('#main_salle_name').val();
    var salle_quartier = $('#main_salle_quartier').val();
    var salle_rue = $('#main_salle_rue').val();
    var salle_code = $('#main_salle_code_postal').val();
    var idVille = $('#main_salle_idVille').val();
    var salle_image = $('#main_salle_image')[0].files[0];

    var formData = new FormData();
    formData.append('salle_name', salle_name);
    formData.append('salle_quartier', salle_quartier);
    formData.append('salle_rue', salle_rue);
    formData.append('salle_code_postal', salle_code);
    formData.append('idVille', idVille);

    if (salle_image) {
        formData.append('salle_image', salle_image);
    }

    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    $.ajax({
        type: "post",
        url: "/cfp/salles",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            if (res.success) {
                $('#main_salle_name').val('');
                $('#main_salle_quartier').val('');
                $('#main_salle_rue').val('');
                $('#main_salle_code_postal').val('');
                $('#main_salle_image').val('');
                toastr.success(res.success, 'Succès', {
                    timeOut: 1500
                });
                sessionStorage.removeItem('modalSalleState');
                location.reload();
            } else {
                $('#error_main_salle_name').text(res.salle_name || '');
                $('#error_main_salle_idVille').text(res.idVille || '');

                $('.main_salle_name').addClass('border-red-500');
                $('.main_salle_idVille').addClass('border-red-500');
            }
        },
        error: function (error) {
            toastr.error("Une erreur est survenue : " + error.responseText, 'Erreur', {
                timeOut: 1500
            });
        }
    });
}


function mainLoadVille() {
    $.ajax({
        type: "get",
        url: "/cfp/salles/loadVille",
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
    var offcanvasElement = document.getElementById('offcanvasAddSalle');

    if (offcanvasElement) {
        var bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);

        if (bsOffcanvas) {
            bsOffcanvas.hide();
        }
    }
}


function getAllVilleCodeds() {
    var idVille = $('#idVille').val();
    var idVilleCoded = $('#idVilleCoded');

    $.ajax({
        type: "get",
        url: "/cfp/villes/" + idVille,
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

function getUpdateVilleCodeds() {
    var idVille = $('#idVilleUpdate').val();
    var idVilleCoded = $('#idVilleCodedUpdate');

    $.ajax({
        type: "get",
        url: "/cfp/villes/" + idVille,
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

function showEtpDropdown() {
    var append_all_etps = $('.append_all_etps');

    $('.place_radio').each(function () {
        if ($(this).is(':checked')) {
            console.log("Azo ve ", parseInt($(this).val()));
            switch (parseInt($(this).val())) {
                case 3:
                    $.ajax({
                        type: "get",
                        url: "/cfp/lieux/getEtps",
                        dataType: "json",
                        success: function (res) {
                            console.log(res);

                            append_all_etps.empty();
                            if (res.status == 200) {
                                append_all_etps.append(`<div class="mb-3">
                                    <label for="idEntreprise" class="form-label">Entreprise</label>
                                    <select onchange="getAllVilleCodeds()" name="idEntreprise" id="idEntreprise"
                                        class="form form-control form-sm">
                                    </select>
                                </div>`);

                                $('#idEntreprise').empty();
                                $('#idEntreprise').append(
                                    `<option value="0" selected disabled>--Veuillez choisir une entreprise--</option>`
                                );
                                $.each(res.etps, function (index, value) {
                                    $('#idEntreprise').append(
                                        `<option value="` + value.idEtp + `">` + value.etp_name + `</option>`
                                    );
                                });

                                propositionEtAjout(3);
                            } else if (res.status == 404) {
                                append_all_etps.append(`<div class="mb-3">
                                    <label for="idEntreprise" class="form-label">Vous n'avez pas d'entreprises clients</label>
                                </div>`);
                            }
                        }
                    });
                    break;

                case 1:
                    append_all_etps.empty();
                    propositionEtAjout(1);
                    break;
                case 2:
                    append_all_etps.empty();
                    propositionEtAjout(2);
                    break;

                default:
                    append_all_etps.empty();
                    break;
            }
        }
    });
}


function propositionEtAjout(id) {
    $.ajax({
        type: "get",
        url: `/cfp/lieux/${id}`,
        dataType: "json",
        success: function (res) {
            console.log(res);

            var inputContainer = $('#li_name').closest('.mb-3');

            inputContainer.append('<div id="suggestions" class="absolute w-full mt-2 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto z-10"></div>');

            $('#li_name').on('input', function () {
                var query = $(this).val().toLowerCase();
                var filteredResults = res.lieux.filter(function (lieu) {
                    return lieu.li_name.toLowerCase().includes(query);
                });

                var suggestions = $('#suggestions');
                suggestions.empty();

                if (filteredResults.length === 0) {
                    suggestions.append('<div class="p-2 text-center text-gray-500">Aucune correspondance</div>');
                    $('#idVille').prop('disabled', false);
                    $('#idVilleCoded').prop('disabled', false);
                    setTimeout(function () {
                        suggestions.empty();
                    }, 1000);
                } else {
                    $('#idVille').prop('disabled', true);
                    $('#idVilleCoded').prop('disabled', true);
                    filteredResults.forEach(function (lieu) {
                        suggestions.append(
                            `<a href="#" class="block p-2 px-4 text-gray-800 hover:bg-blue-100 hover:text-blue-600 cursor-pointer" data-id="${lieu.idLieu}">${lieu.li_name}</a>`
                        );
                    });
                }
            });

            $(document).on('click', '#suggestions a', function (e) {
                e.preventDefault();
                var selectedId = $(this).data('id');
                console.log("Ato ve ", selectedId);
                var selectedName = $(this).text();
                $('#li_name').val(selectedName);
                $('#idLieu').val(selectedId).prop('disabled', false);
                $('#suggestions').empty();
            });
        }
    });
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
        url: "/cfp/salles",
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
            $('#loadingSpinner').hide();
        }
    });
}


function showContent(tab) {
    document.querySelectorAll('.content').forEach(function (content) {
        content.classList.add('hidden');
    });

    document.querySelectorAll('.tab').forEach(function (tabElem) {
        tabElem.classList.remove('text-blue-600', 'bg-blue-100', 'font-semibold');
        tabElem.classList.add('text-gray-600', 'hover:text-blue-600');
    });

    document.getElementById(tab).classList.remove('hidden');

    document.getElementById('tab-' + tab).classList.add('text-blue-600', 'bg-blue-100', 'font-semibold');
    document.getElementById('tab-' + tab).classList.remove('text-gray-600');
}

function modifSalle(element) {
    const dataId = element.getAttribute('data-id');
    const [idSalle, idLieu] = dataId.split('-');
    console.log("idSalle:", idSalle);
    console.log("idLieu:", idLieu);
    fetch(`/cfp/getSalleDetails?idLieu=${idLieu}&idSalle=${idSalle}`)
        .then(response => response.json())
        .then(data => {
            // console.log("Données récupérées :", data);

            const idSalleField = document.getElementById('idSalle');
            const idLieuField = document.getElementById('idLieu');
            const liNameField = document.getElementById('li_name');
            const salleNameField = document.getElementById('salle_name');
            document.getElementById('li_quartier').value = data.li_quartier;
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
                    url: "/cfp/villes/" + data.idVille,
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

function openModal(idLieu, idSalle) {
    idLieuToDelete = idLieu;
    idSalleToDelete = idSalle;

    document.getElementById('confirmModal').classList.remove('hidden');
    var message = 'Voulez-vous vraiment supprimer ';

    document.getElementById('modalMessage').textContent = message;
}

function deleteSalle() {
    $.ajax({
        type: "delete",
        url: "/cfp/salles/" + idSalleToDelete,
        dataType: "json",
        beforeSend: function () {

        },
        complete: function () {

        },
        success: function (res) {
            if (res.status === 200) {
                toastr.success(res.message, 'Succès', {
                    timeOut: 1500
                });
                location.reload();
            } else if (res.status === 400) {
                toastr.error(res.message, 'Veuillez supprimer la salle d\'abord avant de supprimer le lieu', {
                    timeOut: 1500
                });
            } else if (res.status === 404) {
                toastr.error(res.message, 'Salle introuvable', {
                    timeOut: 1500
                });
            } else if (res.status === 500) {
                toastr.error(res.message, 'Impossible de supprimer cette salle!', {
                    timeOut: 15000
                });
            } else if (res.status === 401) {
                toastr.error(res.message, 'Impossible de supprimer cette salle car il est déja rattaché à un projet!!', {
                    timeOut: 15000
                });
            } else {
                toastr.error("Erreur inconnue !", 'Erreur', {
                    timeOut: 1500
                });
            }
        },
        error: function () {
            console.log("Erreur");
        }
    });
    closeModal();
}

function deleteLieu() {
    $.ajax({
        type: "delete",
        url: "/cfp/lieux_delete/" + idLieuToDelete,
        dataType: "json",
        beforeSend: function () {

        },
        complete: function () {

        },
        success: function (res) {
            if (res.status === 200) {
                toastr.success(res.message, 'Succès', {
                    timeOut: 1500
                });
                location.reload();
            } else if (res.status === 400) {
                toastr.error(res.message, 'Veuillez supprimer la salle d\'abord avant de supprimer le lieu', {
                    timeOut: 1500
                });
            } else if (res.status === 404) {
                toastr.error(res.message, 'Lieu introuvable', {
                    timeOut: 1500
                });
            } else if (res.status === 500) {
                toastr.error(res.message, 'Impossible de supprimer ce lieu!', {
                    timeOut: 15000
                });
            } else {
                toastr.error("Erreur inconnue !", 'Erreur', {
                    timeOut: 1500
                });
            }
        },
        error: function () {
            console.log("Erreur");
        }
    });
    console.log('Deleting Lieu with idSalle: ' + idSalleToDelete + ' and idLieu: ' + idLieuToDelete);
    closeModal();
}


function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
}

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

const liQuartierInput = document.getElementById('li_quartier');
if (liQuartierInput) {
    liQuartierInput.addEventListener('input', function () {
        const input = this.value;
        const suggestionBox = document.getElementById('quartierSuggestions');

        // Vérifie si suggestionBox existe avant de tenter de manipuler son contenu
        if (suggestionBox) {
            suggestionBox.innerHTML = ''; // Vider les suggestions
            suggestionBox.classList.add('hidden');

            if (input.length >= 0) { // Commence la recherche après 0 caractère
                fetch('/cfp/quartier')
                    .then(response => response.json())
                    .then(data => {
                        const filtered = data.filter(item =>
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