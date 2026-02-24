/* Drawer Entreprise Global */
function showCustomer(idCustomer, route, idProjet = null) {
    $.ajax({
        type: "get",
        url: route + idCustomer,
        dataType: "json",
        success: function (res) {
            if (res) {
                const customer = res.customer;
                const referents = res.referents;

                console.log("test", res);

                const last = document.querySelector("#offcanvasEtp")
                if (last) {
                    last.remove();
                }

                const principal_referent =
                    `
                    <li class="pt-3 pb-0 sm:pt-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                ${referents[0].photo ? `<img class="w-10 h-10 rounded-full" src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/employes/${referents[0].photo}" alt="${referents[0].name ? referents[0].name : ''}">`
                        : `<div class="w-10 h-10 rounded-full text-gray-500 font-bold text-2xl text-center bg-gray-200 relative">
                                            <i class="fa-solid fa-user"></i>
                                        </div>`}
                            </div>
                            <div class="flex-1 min-w-0 ms-4 ">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                <i class="fa-solid fa-user text-sm"></i>
                                    ${referents[0].name ? referents[0].name : ''} ${referents[0].firstName ? referents[0].firstName : ''} ${referents[0].fonction ? '(' + referents[0].fonction + ')' : ''}
                                </p>
                                <p class="text-sm text-gray-500 truncate">
                                <i class="fa-solid fa-envelope text-sm"></i>
                                    ${referents[0].email ? referents[0].email : '--'}                       
                                </p>
                                <p class="text-sm text-gray-500 truncate">
                                <i class="fa-solid fa-phone text-sm"></i>
                                    ${referents[0].customerPhone ? referents[0].customerPhone : '--'}                      
                                </p>
                            </div>
                        </div>
                    </li>
                `;
                let other_referent = "";

                referents.forEach((referent, i) => {
                    if (i != 0) {
                        other_referent +=
                            `<li class="pt-3 pb-0 sm:pt-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    ${referent.photo ? `<img class="w-10 h-10 rounded-full" src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/employes/${referent.photo}" alt="${referent.name ? referent.name : ''}">`
                                : `<div class="w-10 h-10 rounded-full text-gray-500 font-bold text-2xl text-center bg-gray-200 relative">
                                                <p class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">${referent.name ? referent.name.charAt(0) : 'I'}</p>
                                            </div>`
                            }
                                </div>
                                <div class="flex-1 min-w-0 ms-4 ">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                    <i class="fa-solid fa-user text-sm"></i>
                                        ${referent.name ? referent.name : ''} ${referent.firstName ? referent.firstName : ''} ${referent.fonction ? '(' + referent.fonction + ')' : ''}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                    <i class="fa-solid fa-envelope text-sm"></i>
                                        ${referent.email ? referent.email : '--'}                         
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                    <i class="fa-solid fa-phone text-sm"></i>
                                        ${referent.phone ? referent.phone : '--'}                         
                                    </p>
                                </div>
                            </div>
                        </li>
                    `
                    }
                });

                const body = $("#drawer_content_detail");
                body.html('');
                body.append(`
                    <div class="offcanvas offcanvas-end !w-[70em] overflow-y-auto" tabindex="-1" id="offcanvasEtp"
                        aria-labelledby="offcanvasEtp">
                        <span class="inline-flex items-center gap-2 absolute top-1 right-1">
                        ${(idProjet != null) ? `<button id="btn_client_edit"  onclick="editDrawer('client', ${idProjet})" class="btn btn-outline btn-sm opacity-70"><i class="fa-solid fa-pen"></i> Changer l'entreprise</button>` : ""}
                            <a data-bs-toggle="offcanvas" href="offcanvasEtp" class= "w-10 h-10 rounded-md hover:bg-gray-200 duration-200 cursor-pointer flex items-center justify-center">
                            <i class="fa-solid fa-xmark text-gray-500"></i>
                        </a>
                        </span>
                        <div id="client_edit" class="flex flex-col w-full">

                            <div class="w-full p-3">
                                <div class="w-full inline-flex items-center gap-3">
                                    <div class="w-[116px] h-[77px] bg-white flex items-center justify-center p-1">
                                    ${customer.logo ? `<img class="object-cover w-full h-auto rounded-xl" src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/entreprises/${customer.logo}" alt="${customer.customerName}">` : `<img object-cover class="w-audo h-16 rounded-xl grayscale" src="/img/logo/Logo_mark.svg" alt="${customer.customerName}">`}
                                    </div>
                                    <div class="flex flex-col justify-start gap-y-2 w-full">
                                        <h3 class="text-2xl font-bold text-gray-700 text-left">${customer.customerName}</h3>
                                        <p class="text-base text-gray-500 text-left">${customer.siteweb ? customer.siteweb : "Site web --"}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full px-4"> 
                                <div>
                                    <ul role="list" class="divide-y divide-gray-200 ">
                                        <div>
                                            <p class="text-xl font-semibold text-gray-900">Référent principal</p>
                                        </div>
                                        ${principal_referent}
                                    </ul>
                                </div>
                                <div class="mt-4">
                                    <ul role="list" class="divide-y divide-gray-200">
                                        <div>
                                            <p class="text-xl font-semibold text-gray-900">Autres référents</p>
                                        </div>
                                        ${other_referent}
                                    </ul>
                                </div>
                                <div class="mt-4" id="title_in_preparation">
                                    
                                </div>
                                <div class="mt-1 table-responsive" id="project_in_preparation">
                                    <table class="table table-hover table-sm" id="table_in_preparation">
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
                                                <th scope="col">Cours</th>
                                                <th scope="col">Ref</th>
                                                <th scop="col">Lieu</th>
                                                <th scope="col"><i class="fa-solid fa-user text-sm"></i></th>
                                                <th scope="col">Montant (Ar)</th>
                                                <th scope="col"><i class="fa-solid fa-money-bill-transfer"></i></th>
                                                <th scope="col">Début - Fin</th>
                                                <th scope="col">Détail</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body_project_in_preparation">
                                            
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4" id="title_in_future">
                                    
                                </div>
                                <div class="mt-1 table-responsive" id="project_future">
                                    <table class="table table-hover table-sm" id="table_future">
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
                                                <th scope="col">Cours</th>
                                                <th scope="col">Ref</th>
                                                <th scop="col">Lieu</th>
                                                <th scope="col"><i class="fa-solid fa-user text-sm"></i></th>
                                                <th scope="col">Montant (Ar)</th>
                                                <th scope="col"><i class="fa-solid fa-money-bill-transfer"></i></th>
                                                <th scope="col">Début - Fin</th>
                                                <th scope="col">Détail</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body_project_future">
                                            
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4" id="title_in_progress">
                                    
                                </div>
                                <div class="mt-1 table-responsive" id="project_in_progress">
                                    <table class="table table-hover table-sm" id="table_in_progress">
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
                                                <th scope="col">Cours</th>
                                                <th scope="col">Ref</th>
                                                <th scop="col">Lieu</th>
                                                <th scope="col"><i class="fa-solid fa-user text-sm"></i></th>
                                                <th scope="col">Montant (Ar)</th>
                                                <th scope="col"><i class="fa-solid fa-money-bill-transfer"></i></th>
                                                <th scope="col">Début - Fin</th>
                                                <th scope="col">Détail</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body_project_in_progress">
                                            
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4" id="title_in_finished">
                                    
                                </div>
                                <div class="mt-1 table-responsive" id="project_finished">
                                    <table class="table table-hover table-sm" id="table_finished">
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
                                                <th scope="col">Cours</th>
                                                <th scope="col">Ref</th>
                                                <th scop="col">Lieu</th>
                                                <th scope="col"><i class="fa-solid fa-user text-sm"></i></th>
                                                <th scope="col">Montant (Ar)</th>
                                                <th scope="col"><i class="fa-solid fa-money-bill-transfer"></i></th>
                                                <th scope="col">Début - Fin</th>
                                                <th scope="col">Détail</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body_project_finished">
                                            
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4" id="title_in_fenced">
                                    
                                </div>
                                <div class="mt-1 table-responsive" id="project_fenced">
                                    <table class="table table-hover table-sm" id="table_fenced">
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
                                                <th scope="col">Cours</th>
                                                <th scope="col">Ref</th>
                                                <th scop="col">Lieu</th>
                                                <th scope="col"><i class="fa-solid fa-user text-sm"></i></th>
                                                <th scope="col">Montant (Ar)</th>
                                                <th scope="col"><i class="fa-solid fa-money-bill-transfer"></i></th>
                                                <th scope="col">Début - Fin</th>
                                                <th scope="col">Détail</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body_project_fenced">
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    `);

                body.ready(function () {
                    populateProjectTable('#table_in_preparation', res.projects_in_preparation, '#title_in_preparation');
                    populateProjectTable('#table_future', res.projects_future, '#title_in_future');
                    populateProjectTable('#table_in_progress', res.projects_in_progress, '#title_in_progress');
                    populateProjectTable('#table_finished', res.projects_finished, '#title_in_finished');
                    populateProjectTable('#table_fenced', res.projects_fenced, '#title_in_fenced');
                });


                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })

                const offcanvasEtpElement = $('#offcanvasEtp');
                const offcanvas = new bootstrap.Offcanvas(offcanvasEtpElement);

                offcanvas.toggle();

            } else {
                alert('Données non trouvées !');
            }
        },
        error: function () {
            alert('Échec de la recuperation des reférents');
        }
    });

}

function populateProjectTable(tableSelector, projects, title) {
    const table = $(tableSelector).hide();

    if (projects.length > 0) {
        let i = 1;
        table.show();
        const bodyProject = $(`${tableSelector} tbody`);

        appendTitle(title);

        $.each(projects, function (index, value) {
            bodyProject.append(`<tr>
                <td scope="row">${i++}</td>
                <td>${value.module_name} ${value.commanditaire ? `<i class="fa-solid text-yellow-500 fa-circle-info" data-bs-toggle="tooltip" data-bs-original-title="Commanditaire ${value.commanditaire}"></i>` : ''}</td>
                <td>${value.project_reference ? value.project_reference : '--'}</td>
                <td>${value.ville ?? '--'}</td>
                <td class="text-center">${value.total_apprenant}</td>
                <td>${value.total_ht}</td>
                <td>`+ invoiceStatus(value.isPaid) + `</td>
                <td>${value.date_debut} - ${value.date_fin}</td>
                <td class="text-center">
                    <a href="/cfp/projets/${value.id_projet}/detail">
                        <i class="fa-solid fa-eye opacity-50"></i>
                    </a>
                </td>
            </tr> `);
        });
    }  
}

function invoiceStatus(status) {
    if (status == 4) {
        return `<i class= "fa-solid fa-circle-check text-green-500" data-bs-toggle="tooltip" data-bs-original-title="Payé"></i> `;
    }
    else if (status == 5) {
        return `<i class= "fa-solid fa-circle-info text-yellow-500" data-bs-toggle="tooltip" data-bs-original-title="Partiel"></i> `;
    }
    else if (status == 6) {
        return `<i class= "fa-solid fa-circle-xmark text-red-500" data-bs-toggle="tooltip" data-bs-original-title="Non payé"></i> `;
    }
    else {
        return `<i class= "fa-solid fa-circle-question text-gray-500" data-bs-toggle="tooltip" data-bs-original-title="Non facturé"></i> `;
    }
}

function appendTitle(title) {
    if (title == '#title_in_preparation') {
        $(title).append('<span class="text-md rounded-xl py-1 px-3 text-white bg-[#F8E16F]"> En préparation </span>');
    }
    if (title == '#title_in_future') {
        $(title).append('<span class="text-md rounded-xl py-1 px-3 text-white bg-[#CBABD1]"> Planifié </span>');
    }
    if (title == '#title_in_progress') {
        $(title).append('<span class="text-md rounded-xl py-1 px-3 text-white bg-[#369ACC]"> En cours </span>');
    }
    if (title == '#title_in_finished') {
        $(title).append('<span class="text-md rounded-xl py-1 px-3 text-white bg-[#95CF92]"> Terniné </span>');
    }
    if (title == '#title_in_fenced') {
        $(title).append('<span class="text-md rounded-xl py-1 px-3 text-white bg-[#6F1926]"> Cloturé </span>');
    }
}

/* Drawer Mini-CV formateur Global */
function viewMiniCV(idFormateur, idProjet = null) {
    const drawer_content_detail = $('#drawer_content_detail');
    drawer_content_detail.html('');

    drawer_content_detail.append(`<div class= "offcanvas offcanvas-end !w-[60rem]" tabindex="-1" id="offcanvasCvForm_${idFormateur}"
        aria-labelledby="offcanvasCvForm_${idFormateur}">
                <div class="flex flex-col w-full">
                    <div class="w-full px-4 py-2 inline-flex items-center justify-between bg-gray-100">
                        <p class="text-lg text-gray-500 font-medium">A propos du formateur</p>
                        <div class="inline-flex items-center gap-2">
                            ${(idProjet != null) ? `<button onclick="editDrawer('formateurs', ${idProjet})" class="btn btn-sm btn-outline opacity-70"><i class="fa-solid fa-add"></i> Ajouter d'autre formateur</button>` : ''}
                            <a data-bs-toggle="offcanvas" href="#offcanvasCvForm_${idFormateur}"
                                class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 cursor-pointer flex items-center justify-center">
                                <i class="fa-solid fa-xmark text-gray-500"></i>
                            </a>
                        </div>
                    </div>

                    <div id="listFormateurDrawer" class="p-2">
                        <div class="w-full flex flex-col p-3 relative">
                            <div class="w-full h-40 rounded-3xl p-4">
                                <div class="grid grid-cols-4 items-center gap-2">
                                    <div class="grid col-span-1 grid-cols-subgrid">
                                        <div class="w-32 h-32 rounded-full p-2">
                                            <span class="photo_formateur_cv_${idFormateur}"></span>
                                        </div>
                                    </div>
                                    <div class="grid col-span-3 grid-cols-subgrid">
                                        <div class="ml-3 flex flex-col justify-start gap-y-2 w-full">
                                            <h3 class="text-2xl font-bold text-gray-700 line-clamp-2 text-left get_name_form_${idFormateur}"></h3>
                                            <p class="text-base text-gray-500 text-left get_titre_form"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="w-full flex flex-col p-3 gap-y-6">
                            <div class="w-full p-3 border-[1px] flex flex-col gap-2 border-gray-200 border-dashed rounded-md">
                                <div class="grid grid-cols-6">
                                    <div class="grid grid-cols-subgrid col-span-2">
                                        <h5 class="text-lg font-semibold text-gray-600">Email :</h5>
                                    </div>
                                    <div class="grid grid-cols-subgrid col-span-4 text-right">
                                        <p class="text-base text-gray-400 get_email_form_${idFormateur}"></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-6">
                                    <div class="grid grid-cols-subgrid col-span-2">
                                        <h5 class="text-lg font-semibold text-gray-600">Téléphone :</h5>
                                    </div>
                                    <div class="grid grid-cols-subgrid col-span-4 text-right">
                                        <p class="text-base text-gray-400 get_tel_form_${idFormateur}"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full p-3 border-[1px] flex flex-col gap-2 border-gray-200 border-dashed rounded-md">
                                <div class="flex flex-col w-full gap-y-1">
                                    <h5 class="text-lg font-semibold text-gray-600">Expériences</h5>
                                    <ul class="flex flex-col w-full get_exp_form_${idFormateur}"></ul>
                                </div>
                                <div class="flex flex-col gap-y-1">
                                    <h5 class="text-lg font-semibold text-gray-600">Formations</h5>
                                    <ul class="flex flex-col w-full get_dp_form_${idFormateur}"></ul>
                                </div>
                            </div>

                            <div class="w-full p-3 border-[1px] flex flex-col gap-2 border-gray-200 border-dashed rounded-md">
                                <div class="flex flex-col gap-y-1">
                                    <h5 class="text-lg font-semibold text-gray-600">Compétences</h5>
                                    <ul class="flex flex-col w-full get_cp_form_${idFormateur}"></ul>
                                </div>
                                <div class="flex flex-col gap-y-1">
                                    <h5 class="text-lg font-semibold text-gray-600">Langues</h5>
                                    <ul class="flex flex-col w-full get_lg_form_${idFormateur}"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div> `);

    $.ajax({
        type: "get",
        url: "/cfp/projets/formateur/" + idFormateur + "/mini-cv",
        dataType: "json",
        success: function (res) {
            if (res.speciality.form_titre !== null)
                $('.get_titre_form').text(res.speciality.form_titre);
            if (res.experiences && res.diplomes && res.competences && res.langues && res.form) {
                // Afficher les données du mini CV dans le drawer ou une autre section de la page
                showMiniCV(idFormateur, res.experiences, res.diplomes, res.competences, res.langues, res.form);
            } else {
                alert('Données du mini CV non trouvées');
            }
        },
        error: function () {
            alert('Échec de la récupération du mini CV');
        }
    });

    const offcanvasId = $('#offcanvasCvForm_' + idFormateur)
    var bsOffcanvas = new bootstrap.Offcanvas(offcanvasId);
    bsOffcanvas.show();
}

/* Drawer Module de formation Global*/
function showFormation(idModule) {
    const drawer_content_detail = $('#drawer_content_detail');
    drawer_content_detail.html('');

    drawer_content_detail.append(`<div class= "offcanvas offcanvas-end !w-[40em] overflow-y-auto" tabindex="-1" id="offcanvasFormation_${idModule}"
        aria-labelledby="offcanvasFormation_${idModule}">
                <div class="flex flex-col w-full">

                    <a data-bs-toggle="offcanvas" href="#offcanvasFormation_${idModule}"
                        class="w-full h-10  flex items-center justify-end">
                        <i class="fa-solid p-3 fa-xmark text-gray-500 rounded-full hover:bg-gray-200 duration-200 cursor-pointer"></i>
                    </a>

                    <div class="w-full flex flex-col p-3 relative">
                        <div class="w-full h-40 rounded-3xl absolute top-2 left-0 p-4">
                            <div class="w-full h-full inline-flex items-center gap-2">
                                <div class="w-[116px] h-[77px] rounded-xl p-1">
                                    <span class="photo_formation_${idModule}"></span>
                                </div>
                                <div class="ml-3 flex flex-col justify-start gap-y-2 w-full">
                                    <h3 class="text-2xl font-bold text-gray-700 text-start line-clamp-2 get_name_module_${idModule}"></h3>
                                    <p class="text-base text-gray-500 text-start line-clamp-3 get_description_module_${idModule}"></p>
                                </div>
                            </div>
                        </div>
                        <div class="w-full flex flex-col mt-[130px] gap-y-6">
                            <div class="w-full p-3 border-[1px] flex flex-col gap-2 border-gray-200 border-dashed rounded-md">
                                <div class="grid grid-cols-6">
                                    <div class="grid grid-cols-subgrid col-span-2">
                                        <h5 class="text-lg font-semibold text-gray-600">Durée :</h5>
                                    </div>
                                    <div class="grid grid-cols-subgrid col-span-4 text-right">
                                        <p class="text-base text-gray-400 get_duree_module_${idModule}"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full p-3 border-[1px] flex flex-col gap-2 border-gray-200 border-dashed  rounded-md">
                                <div class="flex flex-col w-full gap-y-1">
                                    <h5 class="text-lg font-semibold text-gray-600">Objectifs</h5>
                                    <ul class="flex flex-col gap-y-1 w-full get_objectif_module_${idModule}"></ul>
                                </div>
                            </div>

                            <div class="w-full p-3 border-[1px] flex flex-col gap-2 border-gray-200 border-dashed  rounded-md">
                                <div class="flex flex-col w-full gap-y-1">
                                    <h5 class="text-lg font-semibold text-gray-600">Programmes de formation</h5>
                                    <ul class="flex flex-col gap-y-1 w-full get_programme_module_${idModule}"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div> `);

    $.ajax({
        type: "get",
        url: "/cfp/modules/detail/" + idModule + "/drawer",
        dataType: "json",
        success: function (res) {
            if (res.details && res.objectifs && res.programmes) {
                showDetailModule(idModule, res.details, res.objectifs, res.programmes);
            } else {
                alert('Données non trouvées !');
            }
        },
    });

    const offcanvasId = $('#offcanvasFormation_' + idModule)
    var bsOffcanvas = new bootstrap.Offcanvas(offcanvasId);
    bsOffcanvas.show();


}


function showSessions(route, idProjet = null) {
    $.ajax({
        type: "get",
        url: route,
        dataType: "json",
        success: function (res) {
            if (res.sessions && res.module && res.totalSessionHours) {
                const sessions = res.sessions;
                const module = res.module;
                const totalSessionHours = res.totalSessionHours.sumHourSession;



                const dates = [];
                const organisedSessions = {}; // Utilisation d'un objet pour organiser les sessions par date
                let lastDateSeance = null;

                // Récupération des dates uniques
                sessions.forEach(session => {
                    if (session.dateSeance != lastDateSeance) {
                        lastDateSeance = session.dateSeance;
                        if (!dates.includes(session.dateSeance)) { // Vérifier si la date n'est pas déjà dans le tableau
                            dates.push(session.dateSeance);
                        }
                    }
                });

                // Organisation des sessions par date
                dates.forEach(date => {
                    organisedSessions[date] = { morning: [], afternoon: [] }; // Initialisation des deux périodes

                    sessions.forEach(session => {
                        if (session.dateSeance == date) {
                            const sessionHour = parseInt(session.heureDebut.split(':')[0]); // Extraction de l'heure

                            if (sessionHour < 12) {
                                organisedSessions[date].morning.push(session); // Ajout à la matinée
                            } else {
                                organisedSessions[date].afternoon.push(session); // Ajout à l'après-midi
                            }
                        }
                    });
                });

                const last = document.querySelector("#offcanvasSession");
                if (last) {
                    last.remove();
                }

                let html = '';

                // Génération du HTML
                if (dates != null) {
                    dates.forEach((date) => {

                        const _date = moment(date).format('dddd ll');
                        const morningSessions = organisedSessions[date].morning;
                        const afternoonSessions = organisedSessions[date].afternoon;

                        html += `<tbody>
                    <tr>
                        <td class="w-[40%] capitalize text-slate-700 font-medium">${_date}</td>
                        <td class="w-[30%] text-slate-500 text-right">${morningSessions.length > 0 ? morningSessions.map(session => `${session.heureDebut} - ${session.heureFin}`).join('<br>') : '--'}</td>
                        <td class="w-[30%] text-slate-500 text-right"> ${afternoonSessions.length > 0 ? afternoonSessions.map(session => `${session.heureDebut} - ${session.heureFin}`).join('<br>') : '--'}</td>
                    </tr>
                                        </tbody> `;
                    });
                } else {
                    html += `Pas de session`;
                }

                // Insertion du HTML dans le DOM
                const body = $("#drawer_content_detail");
                body.html('');
                body.append(`
                <div class= "offcanvas offcanvas-end !w-[80em] overflow-y-auto" data-bs-backdrop="static" tabindex="-1" id="offcanvasSession"
                        aria-labelledby="offcanvasSession">
                <div id="sessions_edit" class="flex flex-col w-full">
                    <span class="inline-flex items-center justify-end absolute z-10 top-1 right-1">
                        ${(idProjet != null) ? `<button onclick="editDrawer('sessions', ${idProjet})" class="btn btn-sm btn-outline opacity-70"><i class="fa-solid fa-pen"></i> Modifier</button>` : ''}
                        <a data-bs-toggle="offcanvas" href="offcanvasEtp"
                            class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 cursor-pointer flex items-center justify-center">
                            <i class="fa-solid fa-xmark text-gray-500"></i>
                        </a>
                    </span>
                    <div class="w-full flex flex-col p-3 relative">
                        <div class="w-full h-40 rounded-3xl p-4">
                            <div class="w-full h-full inline-flex items-center gap-2">
                                <div class="w-[116px] h-[77px] rounded-xl p-1">
                                    <span class="photo_formation_${module.idModule}"></span>
                                </div>
                                <div class="ml-3 flex flex-col justify-start gap-y-2 w-full">
                                    <h3 class="text-2xl font-bold text-gray-700 text-start line-clamp-2">${module.moduleName}</h3>
                                    <p class="text-base text-gray-500 text-start line-clamp-3">${module.module_subtitle ? module.module_subtitle : ""}</p>
                                </div>
                            </div>
                        </div>
                        <div class="w-full">
                            <p class="text-xl font-semibold text-gray-900">${totalSessionHours ? `Nombres d'heures : ${totalSessionHours}` : `Pas de session`}</p>
                        </div>
                        <table class="table mt-4">
                            ${html}
                        </table>
                    </div>
                </div>
                    </div>
                    `);

                var photo_formation = $('.photo_formation_' + module.idModule);
                photo_formation.html('');

                if (module.module_image == "" || module.module_image == null) {
                    photo_formation.append(
                        `<div class= "flex items-center justify-center w-full h-full text-3xl text-gray-500 uppercase bg-gray-100 rounded-xl"> ` +
                        module.moduleName[0] + `</div> `);
                } else {
                    photo_formation.append(
                        `<img src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/modules/${module.module_image}" alt="profil" class= "object-cover w-full h-full rounded-xl"> `
                    );
                }

                const offcanvasSessionElement = $('#offcanvasSession');
                const offcanvas = new bootstrap.Offcanvas(offcanvasSessionElement);

                offcanvas.toggle();
            } else {
                alert('Données non trouvées !');
            }
        },
        error: function () {
            alert('Échec de la récupération des sessions');
        }
    });

}

var endpoint = "https://formafusionmg.ams3.cdn.digitaloceanspaces.com";
var bucket = "formafusionmg";

function showDossiers(route) {
    $.ajax({
        type: "get",
        url: route,
        dataType: "json",
        success: function (res) {
            if (res.module) {
                const module = res.module;

                // Supprimer l'élément précédent du canvas s'il existe
                const lastCanvas = document.querySelector("#offcanvasDossier");
                if (lastCanvas) lastCanvas.remove();

                // Contenu de base de la vue dossier
                const dossierHtml = `
                <div class= "offcanvas offcanvas-end !w-[80em] overflow-y-auto" tabindex="-1" id="offcanvasDossier"
                        aria-labelledby="offcanvasDocument">
                <div class="flex flex-col w-full">
                    <div class="w-full flex flex-col p-3 relative">
                        <div class="w-full h-40 rounded-3xl p-4">
                            <div class="w-full h-full inline-flex items-center gap-2">
                                <div class="w-[116px] h-[77px] rounded-xl p-1">
                                    <span class="photo_formation_${module.idModule}"></span>
                                </div>
                                <div class="ml-3 flex flex-col justify-start gap-y-2 w-full">
                                    <h3 class="text-2xl font-bold text-gray-700">${module.moduleName}</h3>
                                    <p class="text-base text-gray-500">${module.module_subtitle || ""}</p>
                                </div>
                                <a class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 absolute top-1 right-1 cursor-pointer flex items-center justify-center"
                                    data-bs-dismiss="offcanvas">
                                    <i class="fa-solid fa-xmark text-gray-500"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="h-full bg-white border border-gray-300 rounded-lg p-4" id="previewPane"></div>
                </div>
                    </div> `;

                // Injection du contenu dans le DOM
                const body = $("#drawer_content_detail");
                body.empty().append(dossierHtml);

                // Gestion du contenu du panneau de prévisualisation
                var previewPane = $('#previewPane');
                previewPane.empty();

                let statusClass = 'text-gray-500';

                // Définir l'ordre de tri des statuts
                const statusOrder = [
                    'En préparation',
                    'En cours',
                    'Planifié',
                    'Terminé',
                    'Annulé',
                    'Reporté',
                    'Cloturé'
                ];

                // Fonction pour obtenir la valeur de tri
                const getSortValue = (status) => statusOrder.indexOf(status);

                // Table des projets associés
                if (res.projets && res.projets.length > 0) {
                    res.projets.sort((a, b) => getSortValue(a.project_status) - getSortValue(b
                        .project_status));
                    let projetsTable = `
                    <h2 class= "text-lg font-semibold mb-4"> Projets associés</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-1 text-left font-medium text-gray-500 uppercase">Nom du Module</th>
                            <th class="px-3 py-1 text-left font-medium text-gray-500 uppercase">Référence</th>
                            <th class="px-3 py-1 text-left font-medium text-gray-500 uppercase">Début - Fin</th>
                            <th class="px-3 py-1 text-left font-medium text-gray-500 uppercase">Entreprise</th>
                            <th class="px-3 py-1 text-left font-medium text-gray-500 uppercase">Prix</th>
                            <th class="px-3 py-1 text-left font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-3 py-1 text-left font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">`;

                    res.projets.forEach(function (projet) {
                        // Déterminer la classe de statut
                        switch (projet.project_status) {
                            case 'En préparation':
                                statusClass = 'bg-[#F8E16F]';
                                break;
                            case 'En cours':
                                statusClass = 'bg-[#369ACC]';
                                break;
                            case 'Planifié':
                                statusClass = 'bg-[#CBABD1]';
                                break;
                            case 'Terminé':
                                statusClass = 'bg-[#95CF92]';
                                break;
                            case 'Annulé':
                                statusClass = 'bg-[#DE324C]';
                                break;
                            case 'Reporté':
                                statusClass = 'bg-[#2E705A]';
                                break;
                            case 'Cloturé':
                                statusClass = 'bg-[#6F1926]';
                                break;
                            default:
                                statusClass = 'text-gray-500';
                        }
                        projetsTable += `
                        <tr class="hover:bg-gray-100">
                            <td class="px-3 py-1 text-gray-900">${projet.module_name}</td>
                            <td class="px-3 py-1 text-gray-500">${projet.project_reference}</td>
                            <td class="px-3 py-1 text-gray-500">${formatDate2Digit(projet.dateDebut)} - ${formatDate2Digit(projet.dateFin)}</td>
                            <td class="px-3 py-1 text-gray-500">${projet.etp_name_in_situ}</td>
                            <td class="px-3 py-1 text-gray-500">${projet.total_ht}</td>
                            <td class="px-3 py-1 text-white text-center rounded-2 border border-white ${statusClass}">${projet.project_status}</td>
                            <td class="px-3 py-1 text-center">
                                <a href="/cfp/projets/${projet.idProjet}/detail" class="text-gray-500 hover:text-[#A462A4]" title="Voir le projet">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>`;
                    });

                    projetsTable += `</tbody></table>`;
                    previewPane.append(projetsTable);
                } else {
                    previewPane.append(`<p class= "text-gray-500 mt-4"> Aucun projet trouvé pour ce dossier.</p> `);
                }

                // Table des documents du dossier
                if (res.nomDossier && res.documents.length > 0) {
                    let documentsTable = `
                <h2 class= "text-lg font-semibold mt-4 mb-4"> Documents associés</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Titre</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Section</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Date de Mise à Jour</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Extension</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500 uppercase">Taille</th>
                            <th class="px-4 py-2 text-center font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">`;

                    res.documents.forEach(function (doc) {
                        documentsTable += `
                            <tr>                            
                                <td class="px-4 py-2 text-gray-900">${doc.titre}</td>
                                <td class="px-4 py-2 text-gray-500">${doc.section_document}</td>
                                <td class="px-4 py-2 text-gray-500">${doc.type_document}</td>
                                <td class="px-4 py-2 text-gray-500">${doc.updated_at}</td>
                                <td class="px-4 py-2 text-gray-500">${doc.extension}</td>
                                <td class="px-4 py-2 text-gray-500 text-right">${doc.taille} Mo</td>
                                <td class="px-4 py-2 text-center">
                                    <div class="dropdown">
                                        <a href="#!" class="p-2 text-gray-700 hover:text-purple-500" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </a>
                                        <ul class="dropdown-menu">                                            
                                            <li><a class="dropdown-item" href="#" onclick="downloadFile('{{ $endpoint }}/{{ $bucket }}/${doc.path}', '${doc.titre}')"><i class="fa-solid fa-download"></i> Télécharger</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>`;
                    });

                    documentsTable += `</tbody></table>`;
                    previewPane.append(documentsTable);


                } else {
                    previewPane.append(`<p class= "text-gray-500 mt-4"> Aucun document trouvé pour ce dossier.</p> `);
                }

                // Gestion de l'image du module
                var photo_formation = $('.photo_formation_' + module.idModule);
                photo_formation.html(module.module_image
                    ? `<img src="${endpoint}/${bucket}/img/modules/${module.module_image}" class= "object-cover w-full h-full rounded-xl"> `
                    : `<div class= "flex items-center justify-center w-full h-full text-3xl text-gray-500 bg-gray-100 rounded-xl"> ${module.moduleName[0]}</div> `
                );

                // Activer l'Offcanvas
                new bootstrap.Offcanvas($('#offcanvasDossier')).toggle();
            } else {
                alert('Données non trouvées !');
            }
        },
        error: function () {
            alert('Échec de la récupération des Dossier');
        }
    });
}


function formatDate2Digit(date) {
    var dateFormated = new Date(date).toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "2-digit"
    })
    return dateFormated;
}

function showDocuments(route) {
    $.ajax({
        type: "get",
        url: route,
        dataType: "json",
        success: function (res) {

            if (res.module) {
                const module = res.module;

                const last = document.querySelector("#offcanvasDocument");
                if (last) {
                    last.remove();
                }

                let html = '';

                html += `
                <div
                            class= "grid col-span-1 p-4 file-table-container bg-white shadow-sm rounded-xl lg:col-span-11 grid-cols-subgrid">
                    <div class="w-full table-responsive">
                        <table class="table w-full align-middle rounded-lg caption-top table-light">
                            <caption class="mb-2 text-xl font-medium text-gray-800">Documents dans ce dossier</caption>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th scope="col" class="!p-2 !text-xl !font-medium !bg-gray-100">Titre</th>
                                    <th scope="col" class="!p-2 !text-xl !font-medium !bg-gray-100">Section</th>
                                    <th scope="col" class="!p-2 !text-xl !font-medium !bg-gray-100">Type</th>
                                    <th scope="col" class="!p-2 !text-xl !font-medium !bg-gray-100">Date</th>
                                    <th scope="col" class="!p-2 !text-xl !font-medium !bg-gray-100">Extension</th>
                                    <th scope="col" class="!p-2 !text-xl !font-medium !text-right !bg-gray-100">Taille</th>
                                    <th scope="col" class="!p-2 !text-xl !font-medium !text-center !bg-gray-100">Action</th>
                                </tr>
                            </thead>
                            <tbody class="file-table-body">
                                <!-- Les lignes seront insérées ici par JavaScript -->
                            </tbody>
                        </table>
                    </div>
                        </div>
                    <style>
                        .table-responsive {
                            overflow: visible;
                            }
                    </style>
                    `;





                // Insertion du HTML dans le DOM
                const body = $("#drawer_content_detail");
                body.html('');
                body.append(`
                    <div class= "offcanvas offcanvas-end !w-[80em] overflow-y-auto" tabindex="-1" id="offcanvasDocument"
                        aria-labelledby="offcanvasDocument">
                        <div id="fichier-modal" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50">
                            <div id="fichier-modal-content"
                                class="relative bg-white p-6 w-[90%] max-w-5xl max-h-[90vh] overflow-auto rounded-lg shadow-lg">
                                <span id="close-fichier-modal"
                                    class="absolute top-2 right-2 text-xl cursor-pointer text-gray-600 hover:text-gray-800">
                                    &times;
                                </span>
                                <div id="file-content" class="overflow-auto"></div>
                                <iframe id="fichier-viewer" class="w-full h-[600px] border-none hidden"></iframe>
                            </div>
                        </div>
                        <div class="flex flex-col w-full">
                            <div class="w-full flex flex-col p-3 relative">
                                <div class="w-full h-40 rounded-3xl p-4">
                                    <div class="w-full h-full inline-flex items-center gap-2">
                                        <div class="w-[116px] h-[77px] rounded-xl p-1">
                                            <span class="photo_formation_${module.idModule}"></span>
                                        </div>
                                        <div class="ml-3 flex flex-col justify-start gap-y-2 w-full">
                                            <h3 class="text-2xl font-bold text-gray-700 text-start line-clamp-2">${module.moduleName}</h3>
                                            <p class="text-base text-gray-500 text-start line-clamp-3">${module.module_subtitle ? module.module_subtitle : ""}</p>
                                        </div>
                                        <a data-bs-toggle="offcanvas" href="offcanvasEtp"
                                            class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 absolute top-1 right-1 cursor-pointer flex items-center justify-center">
                                            <i class="fa-solid fa-xmark text-gray-500"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
            
                            ${html}
            
                        </div>
                    </div>
                    `);

                body.ready(function () {

                    const closeModal = () => {
                        $('#fichier-modal').addClass('hidden');
                        $('#fichier-viewer').attr('src', '');
                        $('#file-content').empty();
                    };

                    $('#close-fichier-modal').click(closeModal);

                    $('#fichier-modal').click(function (e) {
                        if (e.target === this) {
                            closeModal();
                        }
                    });

                    $(document).keyup(function (e) {
                        if (e.key === "Escape" && $('#fichier-modal').hasClass('flex')) {
                            closeModal();
                        }
                    });


                    var tableContainer = $('.file-table-container');
                    var tableBody = $('.file-table-body');

                    tableBody.html('');
                    if (res.documents.length <= 0) {
                        tableContainer.hide();
                    } else {
                        tableContainer.show();

                        $.each(res.documents, function (key, val) {

                            var endpoint = "{{ $endpoint }}";
                            var bucket = "{{ $bucket }}";

                            tableBody.append(`
                    <tr>
                                    <td class="text-gray-500 text-center cursor-pointer hover:text-[#A462A4]" title="Visualiser le fichier" onclick="openFileModalCustomer('${res.digitalOcean}/${val.path}')">
                                        <i class="fa-solid fa-eye"></i>
                                    </td>
                                    <td class="!text-xl !text-gray-600 !bg-white">${val.titre}</td>
                                    <td class="!text-xl !text-gray-600 !bg-white">${val.section_document || 'Non spécifié'}</td>
                                    <td class="!text-xl !text-gray-600 !bg-white">${val.type_document || 'Non spécifié'}</td>
                                    <td class="!text-xl !text-gray-600 !bg-white">${val.updated_at}</td>
                                    <td class="!text-xl !text-gray-600 !bg-white">${val.extension}</td>
                                    <td class="!text-xl !text-gray-600 !text-right !bg-white">${val.taille || 'Non spécifié'} Mo</td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a href="#!" class="p-2 text-gray-700 bg-transparent rounded-full hover:bg-gray-200 focus:outline-none" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-vertical fa-lg"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <p class="dropdown-item d-flex align-items-center" onclick="downloadFile('${endpoint}/${bucket}/${val.path}', '${val.titre}.pdf')">
                                                        <i class="fa-solid fa-download mr-2"></i> 
                                                        Télécharger
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                    `);
                        });
                    }

                });

                var photo_formation = $('.photo_formation_' + module.idModule);
                photo_formation.html('');

                if (module.module_image == "" || module.module_image == null) {
                    photo_formation.append(
                        `<div class= "flex items-center justify-center w-full h-full text-3xl text-gray-500 uppercase bg-gray-100 rounded-xl"> ` +
                        module.moduleName[0] + `</div> `);
                } else {
                    photo_formation.append(
                        `<img src="${endpoint}/${bucket}/img/modules/${module.module_image}" alt="profil" class= "object-cover w-full h-full rounded-xl"> `
                    );
                }

                const offcanvasDocumentElement = $('#offcanvasDocument');
                const offcanvas = new bootstrap.Offcanvas(offcanvasDocumentElement);

                offcanvas.toggle();
            } else {
                alert('Données non trouvées !');
            }
        },
        error: function () {
            alert('Échec de la récupération des documents');
        }
    });

}


// aperçu d'un fichier debut
const openFileModalCustomer = (fileUrl) => {
    const fileExtension = fileUrl.split('.').pop().toLowerCase();
    let fileContentHtml = '';
    $('#file-content').empty();
    $('#fichier-viewer').hide();

    switch (fileExtension) {
        case 'pdf':
            $('#fichier-viewer').show().attr('src', fileUrl);
            break;
        case 'txt':
        case 'csv':
            $.get(fileUrl, function (data) {
                fileContentHtml = `<pre class= "whitespace-pre-wrap font-mono"> ${data}</pre> `;
                $('#file-content').html(fileContentHtml);
            });
            break;
        case 'xls':
        case 'xlsx':
            fileContentHtml = `<iframe src="https://docs.google.com/gview?url=${fileUrl}&embedded=true" class= "w-full h-[600px]"></iframe> `;
            $('#file-content').html(fileContentHtml);
            break;
        case 'ppt':
        case 'pptx':
            fileContentHtml = `<iframe src="https://docs.google.com/viewer?url=${fileUrl}&embedded=true" class= "w-full h-[600px]"></iframe> `;
            $('#file-content').html(fileContentHtml);
            break;
        default:
            $('#file-content').html('Format de fichier non pris en charge.');
    }

    $('#fichier-modal').removeClass('hidden');
};
// aperçu d'un fichier fin

function downloadFile(url, filename) {
    fetch(url)
        .then(response => response.blob())
        .then(blob => {
            saveAs(blob, filename);
        });
}


// getCompetence
function getCompetenceLevel(level) {
    switch (level) {
        case 5:
            return 'h-[100%]';
        case 4:
            return 'h-[80%]';
        case 3:
            return 'h-[60%]';
        case 2:
            return 'h-[40%]';
        case 1:
            return 'h-[20%]';
        default:
            return 'h-[0%]';
    }
}

function showApprenants(route, idProjet = null) {
    $.ajax({
        type: "get",
        url: route,
        dataType: "json",
        success: function (res) {
            if (res.apprenants && res.module && res.etps) {
                const apprenants = res.apprenants;
                const etps = res.etps;
                const module = res.module;
                const customers = [];
                const last = document.querySelector("#offcanvasApprenants");
                const idCfp_inter = res.idCfp_inter;

                if (last) {
                    last.remove();
                }

                let lastCustomer = ''
                let idEtp = null;

                etps.forEach(etp => {
                    idEtp = etp.idEtp;
                });

                apprenants.forEach(apprenant => {
                    if (apprenant.customerName != lastCustomer) {
                        lastCustomer = apprenant.customerName
                        customers.push(apprenant.customerName);
                    }
                })

                let html = ''
                customers.forEach(customer => {
                    html += `
                <div>
                <p class="text-xl font-medium text-gray-600 mt-4 mb-2">${customer}</p>
                        </div>
                    `
                    apprenants.forEach((apprenant, i) => {
                        if (apprenant.customerName == customer) {
                            html +=
                                `
                    <li class= "pt-3 pb-0 sm:pt-4 pb-2">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            ${apprenant.photo ? `<img class="w-14 h-14 rounded-full" src="${endpoint}/${bucket}/img/employes/${apprenant.photo}" alt="${apprenant.name ? apprenant.name : ''}">`
                                    : `<div class="w-14 h-14 rounded-full flex items-center justify-center text-gray-500 font-bold text-center bg-gray-200 relative">
                                                    <i class="fa-solid fa-user text-xl"></i>
                                                </div>`
                                }
                        </div>
                        <div class="flex-1 min-w-0 ms-4">
                            <span class="inline-flex items-center w-full justify-between mb-1">
                                <p class="text-lg text-slate-600 line-clamp-2">
                                    ${apprenant.name ?? ''}
                                </p>
                                ${apprenant.emargement == -1 ?
                                    `<span class="px-2 py-1 text-sm rounded-md bg-gray-500 text-white">En attente d'émargement</span>`
                                    : ''
                                }
                                ${apprenant.emargement == 100 ?
                                    `<span class="px-2 py-1 text-sm rounded-md bg-green-500 text-white">${apprenant.emargement.toFixed(2)}%</span>`
                                    : ''
                                }
                                ${apprenant.emargement > 0 && apprenant.emargement < 100 ?
                                    `<span class="px-2 py-1 text-sm rounded-md bg-amber-500 text-white">${apprenant.emargement.toFixed(2)}%</span>`
                                    : ''
                                }
                                ${apprenant.emargement == 0 ?
                                    `<span class="px-2 py-1 text-sm rounded-md bg-red-500 text-white">${apprenant.emargement.toFixed(2)}%</span>`
                                    : ''

                                }
                            </span>
                            <span class="inline-flex items-start w-full justify-between">
                                <p class="text-lg text-slate-600 line-clamp-1">
                                    ${apprenant.firstName ?? ''}
                                </p>
                                <div class="inline-flex items-center gap-2">
                                    <span class="px-2 py-1 h-[35px] w-[27px] border-[1px] border-gray-50 rounded-md text-sm text-center flex items-center justify-center rounded-sm text-gray-800 relative">
                                        ${apprenant.avg_before ? apprenant.avg_before : 0}
                                        <span class="${getCompetenceLevel(apprenant.avg_before ? apprenant.avg_before : 0)} w-[27px] bg-gray-700/50 z-[-1] absolute rounded-md bottom-0"></span>
                                    </span>
                                    <span class="px-2 py-1 h-[35px] w-[27px] border-[1px] border-gray-50 rounded-md text-sm text-center flex items-center justify-center rounded-sm text-gray-800 relative">
                                        ${apprenant.avg_after ? apprenant.avg_after : 0}
                                        <span class="${getCompetenceLevel(apprenant.avg_after ? apprenant.avg_after : 0)} w-[27px] bg-[#a462a4]/50 z-[-1] absolute rounded-md bottom-0"></span>
                                    </span>
                                </div>
                            </span>
                        </div>
                    </div>
                            </li>
                    `
                        }
                    })
                })

                const body = $("#drawer_content_detail");
                body.html('');
                body.append(`<div class= "offcanvas offcanvas-end !w-[60rem] overflow-y-auto" tabindex="-1" id="offcanvasEtp"
                        aria-labelledby="offcanvasEtp">
                <div class="flex flex-col w-full">
                    <div class="w-full px-4 py-2 inline-flex items-center justify-between bg-gray-100">
                        <p class="text-lg text-gray-500 font-medium">Nombres d'apprenants : ${apprenants.length}</p>
                        <div class="inline-flex items-center gap-2">
                            ${(idProjet != null) ? `<button onclick="editDrawer('apprenants', ${idProjet}, ${idEtp}, ${idCfp_inter})" class="btn btn-sm btn-outline opacity-70"><i class="fa-solid fa-add"></i>Ajouter d'autre apprenant</button>` : ''}
                            <a data-bs-toggle="offcanvas" href="#offcanvasParticipant"
                                class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 cursor-pointer flex items-center justify-center">
                                <i class="fa-solid fa-xmark text-gray-500"></i>
                            </a>
                        </div>
                    </div>

                    <div id="listApprDrawer" class="w-full flex flex-col p-3 relative">
                        <div class="w-full h-40 rounded-3xl p-4">
                            <div class="w-full h-full inline-flex items-center gap-2">
                                <div class="w-[116px] h-[77px] rounded-xl p-1">
                                    <span class="photo_formation_${module.idModule}"></span>
                                </div>
                                <div class="ml-3 flex flex-col justify-start gap-y-2 w-full">
                                    <h3 class="text-2xl font-bold text-gray-700 text-start line-clamp-2">${module.moduleName}</h3>
                                    <p class="text-base text-gray-500 text-start line-clamp-3">${module.module_subtitle ? module.module_subtitle : "--"}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 px-4">
                            <ul role="list" class="divide-y divide-dashed divide-gray-200">
                                ${html}
                            </ul>
                        </div>
                    </div>
                </div>
                    </div>
                    `);


                var photo_formation = $('.photo_formation_' + module.idModule);
                photo_formation.html('');

                if (module.module_image == "" || module.module_image == null) {
                    photo_formation.append(
                        `<div class= "flex items-center justify-center w-full h-full text-3xl text-gray-500 uppercase bg-gray-100 rounded-xl"> ` +
                        module.moduleName[0] + `</div> `);
                } else {
                    photo_formation.append(
                        `<img src="${endpoint}/${bucket}/img/modules/${module.module_image}" alt="profil" class= "object-cover w-full h-full rounded-xl"> `
                    );
                }


                const offcanvasEtpElement = $('#offcanvasEtp');
                const offcanvas = new bootstrap.Offcanvas(offcanvasEtpElement);

                offcanvas.toggle();

            } else if (res.error) {
                alert(res.error);
            }
        },
        error: function () {
            alert('Échec de la recuperation des reférents');
        }
    });

}

function showApprenantsCa(month, type) {
    $.ajax({
        type: "get",
        url: "/home/learner/" + month + '/' + type,
        dataType: "json",
        success: function (res) {
            let last = document.querySelector("#offcanvasApprenants");

            if (last) {
                last.remove();
            }

            let html = '';

            let monthConverted = convertToMonth(month);

            let currentTime = new Date();
            let year = (type == 0) ? currentTime.getFullYear() - 1 : currentTime.getFullYear();

            $.each(res.results, function (index, learners) { 
                html += `<h1 class="text-lg font-semibold">${index}</h1>`;

                $.each(learners, function (i, learner) { 
                    html += `<li class= "pt-3 pb-0 sm:pt-4 pb-2">
                           <div class="flex items-start">
                               <div class="flex-shrink-0">
                                   ${learner.photo ? `<img class="w-14 h-14 rounded-full" src="${endpoint}/${bucket}/img/employes/${learner.photo}" alt="${learner.name ? learner.name : ''}">`
                                           : `<div class="w-14 h-14 rounded-full flex items-center justify-center text-gray-500 font-bold text-center bg-gray-200 relative">
                                                           <i class="fa-solid fa-user text-xl"></i>
                                                       </div>`
                                       }
                               </div>
                               <div class="flex-1 min-w-0 ms-4">
                                   <span class="inline-flex items-center w-full justify-between mb-1">
                                       <p class="text-lg text-slate-600 line-clamp-2">
                                           ${learner.name ?? ''} 
                                       </p>
                                   </span>
                                   <span class="inline-flex items-start w-full justify-between">
                                       <p class="text-lg text-slate-600 line-clamp-1">
                                           ${learner.firstName ?? ''}
                                       </p>
                                       <div class="inline-flex items-center gap-2">
                                       </div>
                                   </span>
                               </div>
                           </div>
                       </li>`;
                    
                });
           });

            let body = $("#drawer_content_detail");
            body.html('');
            body.append(`<div class= "offcanvas offcanvas-end !w-[60rem] overflow-y-auto" tabindex="-1" id="offcanvasEtp"
                    aria-labelledby="offcanvasEtp">
                    <h1 class="px-4 font-semibold text-lg">Vos apprennants du ${monthConverted} ${year}</h1>
                    <div class="mt-2 px-4">
                        <ul role="list" class="divide-y divide-dashed divide-gray-200">
                            ${html}
                        </ul>
                    </div>
                </div>
                `);



            let offcanvasEtpElement = $('#offcanvasEtp');
            let offcanvas = new bootstrap.Offcanvas(offcanvasEtpElement);

            offcanvas.toggle();


        },
        error: function () {
            alert('Échec de la recuperation des reférents');
        }
    });

}

function convertToMonth(month){
    if(month == 'Jan'){
        return 'janvier';
    }
    else if(month == 'Fev'){
        return 'février';
    }
    else if(month == 'Mars'){
        return 'mars';
    }
    else if(month == 'Avr'){
        return 'avril';
    }
    else if(month == 'Mai'){
        return 'mai';
    }
    else if(month == 'Jui'){
        return 'juin';
    }
    else if(month == 'Juil'){
        return 'juillet';
    }
    else if(month == 'Aout'){
        return 'aout';
    }
    else if(month == 'Sept'){
        return 'septembre';
    }
    else if(month == 'Oct'){
        return 'octobre';
    }
    else if(month == 'Nov'){
        return 'novembre';
    }
    else{
        return 'décembre';
    }
}

function showSalle(idSalle) {
    const drawer_content_detail = $('#drawer_content_detail');
    drawer_content_detail.html('');

    drawer_content_detail.append(`<div class= "offcanvas offcanvas-end !w-[40em]" tabindex="-1" id="offcanvasSalleDrawer_${idSalle}"
        aria-labelledby="offcanvasSalleDrawer_${idSalle}">
                <div class="flex flex-col w-full">
                    <a data-bs-toggle="offcanvas" href="#offcanvasSalleDrawer_${idSalle}"
                        class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 absolute top-1 right-1 cursor-pointer flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-gray-500"></i>
                    </a>

                    <div class="w-full flex flex-col p-3 relative">
                        <div class="w-full h-40 rounded-3xl p-4">
                            <div class="grid grid-cols-4 items-center gap-2">
                                <div class="grid col-span-1 grid-cols-subgrid">
                                    <div class="w-32 h-32 rounded-full p-2">
                                        <span class="photo_formateur_cv_${idSalle}"></span>
                                    </div>
                                </div>
                                <div class="grid col-span-3 grid-cols-subgrid">
                                    <div class="ml-3 flex flex-col justify-start gap-y-2 w-full">
                                        <h3 class="text-2xl font-bold text-gray-700 line-clamp-2 text-left get_name_form_${idSalle}"></h3>
                                        <p class="text-base text-gray-500 text-left get_fonction_form_${idSalle}">Formateur</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full flex flex-col p-3 gap-y-6">
                        <div class="w-full p-3 border-[1px] flex flex-col gap-2 border-gray-200 border-dashed rounded-md">
                            <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-2">
                                    <h5 class="text-lg font-semibold text-gray-600">Email :</h5>
                                </div>
                                <div class="grid grid-cols-subgrid col-span-4 text-right">
                                    <p class="text-base text-gray-400 get_email_form_${idSalle}"></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-6">
                                <div class="grid grid-cols-subgrid col-span-2">
                                    <h5 class="text-lg font-semibold text-gray-600">Téléphone :</h5>
                                </div>
                                <div class="grid grid-cols-subgrid col-span-4 text-right">
                                    <p class="text-base text-gray-400 get_tel_form_${idSalle}"></p>
                                </div>
                            </div>
                        </div>

                        <div class="w-full p-3 border-[1px] flex flex-col gap-2 border-gray-200 border-dashed rounded-md">
                            <div class="flex flex-col w-full gap-y-1">
                                <h5 class="text-lg font-semibold text-gray-600">Expériences</h5>
                                <ul class="flex flex-col w-full get_exp_form_${idSalle}"></ul>
                            </div>
                            <div class="flex flex-col gap-y-1">
                                <h5 class="text-lg font-semibold text-gray-600">Formations</h5>
                                <ul class="flex flex-col w-full get_dp_form_${idSalle}"></ul>
                            </div>
                        </div>

                        <div class="w-full p-3 border-[1px] flex flex-col gap-2 border-gray-200 border-dashed rounded-md">
                            <div class="flex flex-col gap-y-1">
                                <h5 class="text-lg font-semibold text-gray-600">Compétences</h5>
                                <ul class="flex flex-col w-full get_cp_form_${idSalle}"></ul>
                            </div>
                            <div class="flex flex-col gap-y-1">
                                <h5 class="text-lg font-semibold text-gray-600">Langues</h5>
                                <ul class="flex flex-col w-full get_lg_form_${idSalle}"></ul>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        </div> `);

    // Ajax

    const offcanvasId = $('#offcanvasSalleDrawer_' + idSalle)
    var bsOffcanvas = new bootstrap.Offcanvas(offcanvasId);
    bsOffcanvas.show();
}

function showEmployeUnique(id, route) {
    $.ajax({
        type: "get",
        url: route + id,
        dataType: "json",
        success: function (res) {
            if (res) {
                const user = res.user;

                const last = document.querySelector("#offcanvasEmployeUnique")
                if (last) {
                    last.remove();
                }

                const principal_referent =
                    `
                    <li class= "pt-3 pb-0 sm:pt-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            ${user.photo ? `<img class="w-24 h-24 rounded-full" src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/employes/${user.photo}" alt="${user.name ? user.name : ''}">`
                        : `<div class="w-24 h-24 rounded-full text-gray-500 font-bold text-2xl text-center bg-gray-200 relative">
                                            <p class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">${user.name ? user.name.charAt(0) : 'I'}</p>        
                                        </div>`}
                        </div>
                        <div class="flex-1 min-w-0 ms-4 ">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                <i class="fa-solid fa-user text-sm"></i>
                                ${user.name ? user.name : ''} ${user.firstName ? user.firstName : ''} (${user.fonction ? user.fonction : 'fonction non renseignée'})
                            </p>
                            <p class="text-sm text-gray-500 truncate">
                                <i class="fa-solid fa-envelope text-sm"></i>
                                ${user.email ? user.email : 'non renseigné'}
                            </p>
                            <p class="text-sm text-gray-500 truncate">
                                <i class="fa-solid fa-phone text-sm"></i>
                                ${user.phone ? user.phone : 'non renseigné'}
                            </p>
                            <p class="text-sm text-gray-500 truncate">
                                <i class="fa-solid fa-address-card text-sm"></i>
                                ${user.matricule ? user.matricule : 'non renseigné'}
                            </p>
                            <p class="text-sm text-gray-500 truncate">
                                <i class="fa-solid fa-house-circle-check text-sm"></i>
                                ${user.customerName ? user.customerName : 'non renseigné'}
                            </p>
                        </div>
                    </div>
                    </li>
                    `;

                const body = $("#drawer_content_detail");
                body.html('');
                body.append(`<div class= "offcanvas offcanvas-end !w-[65rem] overflow-y-auto" tabindex="-1" id="offcanvasEmployeUnique"
                        aria-labelledby="offcanvasEmployeUnique">
                <div class="flex flex-col w-full">
                    <a data-bs-toggle="offcanvas" href="offcanvasEmployeUnique"
                        class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 absolute top-1 right-1 cursor-pointer flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-gray-500"></i>
                    </a>

                    <div class="w-full p-4">
                        <div>
                            <ul role="list" class="divide-y divide-gray-200 ">
                                <div>
                                    <p class="text-xl font-semibold text-gray-900">Employé</p>
                                </div>
                                ${principal_referent}
                            </ul>
                        </div>
                        <div class="mt-4" id="title_learner_in_preparation">
                        </div>
                        <div class="mt-1 table-responsive" id="project_in_preparation">
                            <table class="table table-hover table-sm" id="table_in_preparation">
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Cours</th>
                                        <th scope="col">Ref</th>
                                        <th scope="col">Début - Fin</th>
                                        <th scope="col">Détail</th>
                                    </tr>
                                </thead>
                                <tbody id="body_project_in_preparation">

                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4" id="title_learner_future">

                        </div>
                        <div class="mt-1 table-responsive" id="project_future">
                            <table class="table table-hover table-sm" id="table_future">
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Cours</th>
                                        <th scope="col">Ref</th>
                                        <th scope="col">Début - Fin</th>
                                        <th scope="col">Détail</th>
                                    </tr>
                                </thead>
                                <tbody id="body_project_future">

                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4" id="title_learner_in_progress">

                        </div>
                        <div class="mt-1 table-responsive" id="project_in_progress">
                            <table class="table table-hover table-sm" id="table_in_progress">
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Cours</th>
                                        <th scope="col">Ref</th>
                                        <th scope="col">Début - Fin</th>
                                        <th scope="col">Détail</th>
                                    </tr>
                                </thead>
                                <tbody id="body_project_in_progress">

                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4" id="title_learner_finished">

                        </div>
                        <div class="mt-1 table-responsive" id="project_finished">
                            <table class="table table-hover table-sm" id="table_finished">
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Cours</th>
                                        <th scope="col">Ref</th>
                                        <th scope="col">Début - Fin</th>
                                        <th scope="col">Détail</th>
                                    </tr>
                                </thead>
                                <tbody id="body_project_finished">

                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4" id="title_learner_fenced">

                        </div>
                        <div class="mt-1 table-responsive" id="project_fenced">
                            <table class="table table-hover table-sm" id="table_fenced">
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Cours</th>
                                        <th scope="col">Ref</th>
                                        <th scope="col">Début - Fin</th>
                                        <th scope="col">Détail</th>
                                    </tr>
                                </thead>
                                <tbody id="body_project_fenced">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                    </div>
                    `);

                populateProjectApprenantTable('#table_in_preparation', res.projects_in_preparation, '#title_learner_in_preparation');
                populateProjectApprenantTable('#table_future', res.projects_future, '#title_learner_future');
                populateProjectApprenantTable('#table_in_progress', res.projects_in_progress, '#title_learner_in_progress');
                populateProjectApprenantTable('#table_finished', res.projects_finished, '#title_learner_finished');
                populateProjectApprenantTable('#table_fenced', res.projects_fenced, '#title_learner_fenced');
                const offcanvasEtpElement = $('#offcanvasEmployeUnique');
                const offcanvas = new bootstrap.Offcanvas(offcanvasEtpElement);

                offcanvas.toggle();

            } else {
                alert('Les données de cet apprenant sont introuvables');
            }
        },
        error: function () {
            alert('Échec de la récupération des détails de cet apprenant');
        }
    });

}

function showDrawerCa(route, monthValue, type) {
    $.ajax({
        type: "get",
        url: route + monthValue + '/' + type,
        dataType: "json",
        success: function (res) {
            if (res) {
                const last = document.querySelector("#offcanvasEmployeUnique")
                if (last) {
                    last.remove();
                }

                const body = $("#drawer_content_detail");
                body.html('');
                body.append(`<div class= "offcanvas offcanvas-end !w-[85rem] overflow-y-auto" tabindex="-1" id="offcanvasEmployeUnique"
                        aria-labelledby="offcanvasEmployeUnique">
                <div class="flex flex-col w-full">
                    <a data-bs-toggle="offcanvas" href="offcanvasEmployeUnique"
                        class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 absolute top-1 right-1 cursor-pointer flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-gray-500"></i>
                    </a>

                    <div class="w-full p-4">
                        <h1 class="text-xl">
                            Vos projet du ${res.title}
                        </h1>
                    </div>

                    <div class="w-full p-4">
                        <div id="title_learner_future">

                        </div>
                        <div class="mt-1 table-responsive" id="project_future">
                            <table class="table table-hover table-sm" id="table_future">
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Cours</th>
                                        <th scope="col">Client</th>
                                        <th scope="col">Montant(Ar)</th>
                                        <th scope="col"><i class="fa-solid fa-user"></i></th>
                                        <th scope="col">Début - Fin</th>
                                        <th scope="col">Détail</th>
                                    </tr>
                                </thead>
                                <tbody id="body_project_future">

                                </tbody>
                            </table>
                        </div>
                        <div id="title_learner_in_progress">

                        </div>
                        <div class="mt-1 table-responsive" id="project_in_progress">
                            <table class="table table-hover table-sm" id="table_in_progress">
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Cours</th>
                                        <th scope="col">Client</th>
                                        <th scope="col">Montant(Ar)</th>
                                        <th scope="col"><i class="fa-solid fa-user"></i></th>
                                        <th scope="col">Début - Fin</th>
                                        <th scope="col">Détail</th>
                                    </tr>
                                </thead>
                                <tbody id="body_project_in_progress">

                                </tbody>
                            </table>
                        </div>
                        <div id="title_learner_finished">

                        </div>
                        <div class="mt-1 table-responsive" id="project_finished">
                            <table class="table table-hover table-sm" id="table_finished">
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Cours</th>
                                        <th scope="col">Client</th>
                                        <th scope="col">Montant(Ar)</th>
                                        <th scope="col"><i class="fa-solid fa-user"></i></th>
                                        <th scope="col">Début - Fin</th>
                                        <th scope="col">Détail</th>
                                    </tr>
                                </thead>
                                <tbody id="body_project_finished">

                                </tbody>
                            </table>
                        </div>
                        <div id="title_learner_fenced">

                        </div>
                        <div class="mt-1 table-responsive" id="project_fenced">
                            <table class="table table-hover table-sm" id="table_fenced">
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Cours</th>
                                        <th scope="col">Client</th>
                                        <th scope="col">Montant(Ar)</th>
                                        <th scope="col"><i class="fa-solid fa-user"></i></th>
                                        <th scope="col">Début - Fin</th>
                                        <th scope="col">Détail</th>
                                    </tr>
                                </thead>
                                <tbody id="body_project_fenced">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                    </div>
                    `);

                populateProjectWithApprenantCountTable('#table_future', res.future, '#title_learner_future');
                populateProjectWithApprenantCountTable('#table_in_progress', res.in_progress, '#title_learner_in_progress');
                populateProjectWithApprenantCountTable('#table_finished', res.finished, '#title_learner_finished');
                populateProjectWithApprenantCountTable('#table_fenced', res.fenced, '#title_learner_fenced');
                const offcanvasEtpElement = $('#offcanvasEmployeUnique');
                const offcanvas = new bootstrap.Offcanvas(offcanvasEtpElement);

                offcanvas.toggle();

            } else {
                alert('Les données de cet apprenant sont introuvables');
            }
        },
        error: function () {
            alert('Échec de la récupération des détails de cet apprenant');
        }
    });

}

function populateProjectApprenantTable(tableSelector, projects, title) {
    const table = $(tableSelector).hide();

    if (projects.length > 0) {
        let i = 1;
        table.show();
        const bodyProject = $(`${tableSelector} tbody`);

        appendTitleLearner(title);

        $.each(projects, function (index, value) {
            bodyProject.append(`<tr>
                <td scope="row">${i++}</td>
                <td>${value.module_name}</td>
                <td>${value.reference ?? '--'}</td>
                <td>${value.date_debut} - ${value.date_fin}</td>
                <td class="text-start">
                    <a href="/cfp/projets/${value.id_projet}/detail">
                        <i class="fa-solid fa-eye opacity-50"></i>
                    </a>
                </td>
            </tr> `);
        });
    }
}

function populateProjectWithApprenantCountTable(tableSelector, projects, title) {
    const table = $(tableSelector).hide();

    if (projects.length > 0) {
        let i = 1;
        table.show();
        const bodyProject = $(`${tableSelector} tbody`);

        appendTitleLearner(title);

        $.each(projects, function (index, value) {
            bodyProject.append(`<tr>
                <td scope="row">${i++}</td>
                <td>${value.module_name}</td>
                <td>${value.etp_name ?? '--'}</td>
                <td>${value.total_ttc}</td>
                <td>${value.learner}</td>
                <td>${value.date_debut} - ${value.date_fin}</td>
                <td class="text-start">
                    <a href="/cfp/projets/${value.id_projet}/detail">
                        <i class="fa-solid fa-eye opacity-50"></i>
                    </a>
                </td>
            </tr> `);
        });
    }
}

function appendTitleLearner(title) {
    if (title == '#title_learner_in_preparation') {
        $(title).append('<span class="text-md mt-4 rounded-xl py-1 px-3 text-white bg-[#F8E16F]"> En préparation </span>');
    }
    if (title == '#title_learner_future') {
        $(title).append('<span class="text-md mt-4 rounded-xl py-1 px-3 text-white bg-[#CBABD1]"> Planifié </span>');
    }
    if (title == '#title_learner_in_progress') {
        $(title).append('<span class="text-md mt-4 rounded-xl py-1 px-3 text-white bg-[#369ACC]"> En cours </span>');
    }
    if (title == '#title_learner_finished') {
        $(title).append('<span class="text-md mt-4 rounded-xl py-1 px-3 text-white bg-[#95CF92]"> Terniné </span>');
    }
    if (title == '#title_learner_fenced') {
        $(title).append('<span class="text-md mt-4 rounded-xl py-1 px-3 text-white bg-[#6F1926]"> Cloturé </span>');
    }
}

// Function drawer plan de repérage 
function showLieuDeReperage(route) {
    $.ajax({
        type: "get",
        url: route,
        dataType: "json",
        success: function (res) {
            if (res.module) {
                const module = res.module;

                // Supprimer l'élément précédent du canvas s'il existe
                const lastCanvas = document.querySelector("#offcanvasDossier");
                if (lastCanvas) lastCanvas.remove();

                // Contenu de base de la vue dossier
                const dossierHtml = `
                    <div class= "offcanvas offcanvas-end !w-[80em] overflow-y-auto" tabindex="-1" id="offcanvasDossier"
                        aria-labelledby="offcanvasDocument">
                <div class="flex flex-col w-full">
                    <div class="w-full flex flex-col p-3 relative">
                        <div class="w-full h-40 rounded-3xl p-4">
                            <div class="w-full h-full inline-flex items-center gap-2">
                                <div class="w-[116px] h-[77px] rounded-xl p-1">
                                    <span class="photo_formation_${module.idModule}"></span>
                                </div>
                                <div class="ml-3 flex flex-col justify-start gap-y-2 w-full">
                                    <h3 class="text-2xl font-bold text-gray-700">${module.moduleName}</h3>
                                    <p class="text-base text-gray-500">${module.module_subtitle || ""}</p>
                                </div>
                                <a class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 absolute top-1 right-1 cursor-pointer flex items-center justify-center"
                                    data-bs-dismiss="offcanvas">
                                    <i class="fa-solid fa-xmark text-gray-500"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="h-full bg-white border border-gray-300 rounded-lg p-4" id="previewPane"></div>
                </div>
                    </div> `;

                // Injection du contenu dans le DOM
                const body = $("#drawer_content_detail");
                body.empty().append(dossierHtml);

                // Gestion du contenu du panneau de prévisualisation
                var previewPane = $('#previewPane');
                previewPane.empty();

                // Function pour afficher l'image
                if (res.plandereperage && res.plandereperage.length > 0) {
                    res.plandereperage.forEach(function (plan) {
                        if (plan.salle_image == null) {
                            previewPane.append(`
                    <h1 class= "font-bold text-gray-500 text-xl mb-2">
                    Plan de repérage de la salle <span class= "italic text-xl text-slate-400"> "${plan.salle_name} , ${plan.li_name} , ${plan.li_quartier} , ${plan.ville_name} - ${plan.vi_code_postal} , ${plan.ville}"</span>
                                </h1>
                    `);
                            previewPane.append(`
                    <p class= "text-base text-gray-500"> Pas d'image pour le moment.</p>

                <div id="image-modal" class= "hidden fixed inset-0 bg-black bg-opacity-75 flex justify-center items-center z-50">
                <img id="modal-image" src="" alt="Image agrandie" class="max-w-full max-h-full p-2 rounded-lg">
                </div>
                            `);
                        } else {
                            previewPane.append(`
                    <h1 class= "font-bold text-gray-500 text-xl mb-2">
                    Plan de repérage de la salle <span class= "italic text-xl text-slate-400"> "${plan.salle_name} , ${plan.li_name} , ${plan.li_quartier} , ${plan.ville_name} - ${plan.vi_code_postal} , ${plan.ville}"</span>
                <i class="fa-solid fa-download text-2xl ml-2 cursor-pointer hover:text-blue-500" title="Télécharger" onclick="downloadImage('${plan.salle_image}')"></i>
                                </h1>
                    `);
                            previewPane.append(`
                    <img src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/salles/${plan.salle_image}"
                                        alt="Image de la salle"
                                        class= "w-full h-full rounded-lg cursor-pointer"
                                        onclick="openImageInModal(this.src)">

                    <div id="image-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex justify-center items-center z-50">
                        <img id="modal-image" src="" alt="Image agrandie" class="max-w-full max-h-full p-2 rounded-lg">
                    </div>
                            `);
                        }
                    });
                } else {
                    previewPane.append(`<p class= "text-gray-500 mt-4"> Aucun plan de repérage trouvé pour ce projet.</p> `);
                }

                // Gestion de l'image du module
                var photo_formation = $('.photo_formation_' + module.idModule);
                photo_formation.html(module.module_image
                    ? `<img src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/modules/${module.module_image}" class= "object-cover w-full h-full rounded-xl"> `
                    : `<div class= "flex items-center justify-center w-full h-full text-3xl text-gray-500 bg-gray-100 rounded-xl"> ${module.moduleName[0]}</div> `
                );

                // Activer l'Offcanvas
                new bootstrap.Offcanvas($('#offcanvasDossier')).toggle();
            } else {
                alert('Données non trouvées !');
            }
        },
        error: function () {
            alert('Échec de la récupération des Dossier');
        }
    });
}

// Fonction pour télécharger le plan de repérage dans un projet
function downloadImage(imageName) {
    const imageUrl = `https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/salles/${imageName}`;

    const link = document.createElement('a');
    link.href = imageUrl;
    link.download = imageName;

    if (imageUrl.startsWith(window.location.origin)) {
        link.click();
    } else {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', imageUrl, true);
        xhr.responseType = 'blob';

        xhr.onload = function () {
            const blob = xhr.response;
            const objectURL = URL.createObjectURL(blob);

            link.href = objectURL;
            link.download = imageName;
            link.click();
        };

        xhr.onerror = function () {
            console.error('Erreur lors du téléchargement de l\'image');
        };

        xhr.send();
    }
}

function openImageInModal(imageSrc) {
    const modal = document.getElementById('image-modal');
    const modalImage = document.getElementById('modal-image');

    modalImage.src = imageSrc;

    modal.classList.remove('hidden');

    modal.onclick = function () {
        modal.classList.add('hidden');
    };
}