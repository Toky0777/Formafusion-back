/* Drawer Entreprise Global */
var endpoint = "https://formafusionmg.ams3.cdn.digitaloceanspaces.com";
var bucket = "formafusionmg";

function showCustomer(idCustomer, route) {
    $.ajax({
        type: "get",
        url: route + idCustomer,
        dataType: "json",
        success: function (res) {
            console.log("referents", res.referents);

            if (res) {
                const customer = res.customer;
                const referents = res.referents;

                const last = document.querySelector("#offcanvasEtp")
                if (last) {
                    last.remove();
                }

                const principal_referent =
                    `
                    <li class="pt-3 pb-0 sm:pt-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                ${referents[0].photo ? `<img class="w-10 h-10 rounded-full" src="/img/employes/${referents[0].photo}" alt="${referents[0].name ? referents[0].name : ''}">`
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
                                    ${referents[0].phone ? referents[0].phone : '--'}                      
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
                                    ${referent.photo ? `<img class="w-10 h-10 rounded-full" src="/img/employes/${referent.photo}" alt="${referent.name ? referent.name : ''}">`
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
                    <div class="offcanvas offcanvas-end !w-[52em] overflow-y-auto" tabindex="-1" id="offcanvasEtp"
                        aria-labelledby="offcanvasEtp">
                        <div class="flex flex-col w-full">
                            <a data-bs-toggle="offcanvas" href="offcanvasEtp"
                                class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 absolute top-1 right-1 cursor-pointer flex items-center justify-center">
                                <i class="fa-solid fa-xmark text-gray-500"></i>
                            </a>

                            <div class="w-full p-3">
                                <div class="w-full inline-flex items-center gap-3">
                                    <div class="w-[116px] h-[77px] bg-white flex items-center justify-center p-1">
                                    ${customer.logo ? `<img class="object-cover w-full h-auto rounded-xl" src="${endpoint}/${bucket}/img/entreprises/${customer.logo}" alt="${customer.customerName}">` : `<img object-cover class="w-audo h-16 rounded-xl grayscale" src="/img/logo/Logo_mark.svg" alt="${customer.customerName}">`}
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
                                            <p class="text-xl font-semibold text-gray-900">Referent principal</p>
                                        </div>
                                        ${principal_referent}
                                    </ul>
                                </div>
                                <div class="mt-4">
                                    <ul role="list" class="divide-y divide-gray-200">
                                        <div>
                                            <p class="text-xl font-semibold text-gray-900">Autres referents</p>
                                        </div>
                                        ${other_referent}
                                    </ul>
                                </div>
                                <div class="mt-4 table-responsive">
                                    <table class="table table-striped table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Cours</th>
                                                <th scope="col" data-bs-toggle="tooltip" title="Centre de formation">CFP</th>
                                                <th scope="col">Apprenant</th>
                                                <th scope="col">Montant (Ar)</th>
                                                <th scope="col">Date</th>
                                                <th scope="col" data-bs-toggle="tooltip" title="Taux de présence général">Présence</th>
                                                <th scope="col" data-bs-toggle="tooltip" title="Appréciation du projet"><i class="fa-solid fa-star text-sm text-amber-500"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">1</th>
                                                <td>POWER BI</td>
                                                <td>Numerika</td>
                                                <td class="text-center">12</td>
                                                <td>12M</td>
                                                <td>12/06/24 - 17/06/24</td>
                                                <td class="text-center">89%</td>
                                                <td class="text-center">4.5</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">2</th>
                                                <td>Anglais</td>
                                                <td>ETP</td>
                                                <td class="text-center">06</td>
                                                <td>6M</td>
                                                <td>12/06/24 - 17/06/24</td>
                                                <td class="text-center">93%</td>
                                                <td class="text-center">4.8</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">3</th>
                                                    <td>Developpement Web</td>
                                                    <td>Kentia</td>
                                                    <td class="text-center">03</td>
                                                    <td>2M</td>
                                                    <td>12/06/24 - 17/06/24</td>
                                                    <td class="text-center">100%</td>
                                                    <td class="text-center">4</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    `);

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

/* Drawer Mini-CV formateur Global */
function viewMiniCV(idFormateur) {
    const drawer_content_detail = $('#drawer_content_detail');
    drawer_content_detail.html('');

    drawer_content_detail.append(`<div class="offcanvas offcanvas-end !w-[40em]" tabindex="-1" id="offcanvasCvForm_${idFormateur}"
        aria-labelledby="offcanvasCvForm_${idFormateur}">
        <div class="flex flex-col w-full">
            <a data-bs-toggle="offcanvas" href="#offcanvasCvForm_${idFormateur}"
                class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 absolute top-1 right-1 cursor-pointer flex items-center justify-center">
                <i class="fa-solid fa-xmark text-gray-500"></i>
            </a>

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
                                <p class="text-base text-gray-500 text-left /* get_fonction_form_${idFormateur} */">Expert en power BI</p>
                                <p class="text-base text-gray-500 text-left get_fonction_form_${idFormateur}">Formateur</p>
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
        </div>`);

    $.ajax({
        type: "get",
        url: "/formateur/" + idFormateur + "/mini-cv",
        dataType: "json",
        success: function (res) {
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

    drawer_content_detail.append(`<div class="offcanvas offcanvas-end !w-[40em] overflow-y-auto" tabindex="-1" id="offcanvasFormation_${idModule}"
        aria-labelledby="offcanvasFormation_${idModule}">
        <div class="flex flex-col w-full">
            <a data-bs-toggle="offcanvas" href="#offcanvasFormation_${idModule}"
                class="w-10 h-10 rounded-md hover:bg-gray-200 duration-200 absolute top-1 right-1 cursor-pointer flex items-center justify-center">
                <i class="fa-solid fa-xmark text-gray-500"></i>
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
        </div>`);

    $.ajax({
        type: "get",
        url: "/projetsForm/detail/" + idModule + "/drawer",
        dataType: "json",
        success: function (res) {
            console.log(res);
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


function showSessions(route) {
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
                        dates.push(session.dateSeance);
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
                                        </tbody>`;
                    });
                } else {
                    html += `Pas de session`;
                }

                // Insertion du HTML dans le DOM
                const body = $("#drawer_content_detail");
                body.html('');
                body.append(`
                    <div class="offcanvas offcanvas-end !w-[40em] overflow-y-auto" tabindex="-1" id="offcanvasSession"
                        aria-labelledby="offcanvasSession">
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
                        `<div class="flex items-center justify-center w-full h-full text-3xl text-gray-500 uppercase bg-gray-100 rounded-xl">` +
                        module.moduleName[0] + `</div>`);
                } else {
                    photo_formation.append(
                        `<img src="${endpoint}/${bucket}/img/modules/${module.module_image}" alt="profil" class="object-cover w-full h-full rounded-xl">`
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

var endpoint = "{{ $endpoint }}";
var bucket = "{{ $bucket }}";

function showLieuDeReperage(route) {
    $.ajax({
        type: "get",
        url: route,
        dataType: "json",
        success: function (res) {
            if (res.module) {
                const module = res.module;

                // Supprimer l'élément précédent du canvas s'il existe
                const lastCanvas = document.querySelector("#offcanvasSession");
                if (lastCanvas) lastCanvas.remove();

                // Contenu de base de la vue dossier
                const dossierHtml = `
                    <div class="offcanvas offcanvas-end !w-[80em] overflow-y-auto" tabindex="-1" id="offcanvasDossier"
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
                    </div>`;

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
                                <h1 class="font-bold text-gray-500 text-xl mb-2">
                                    Plan de repérage de la salle <span class="italic text-xl text-slate-400">"${plan.salle_name} , ${plan.li_name} , ${plan.li_quartier} , ${plan.ville_name} - ${plan.vi_code_postal} , ${plan.ville}"</span>
                                </h1>
                            `);
                            previewPane.append(`
                                <p class="text-base text-gray-500">Pas d'image pour le moment.</p>

                                <div id="image-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex justify-center items-center z-50">
                                    <img id="modal-image" src="" alt="Image agrandie" class="max-w-full max-h-full p-2 rounded-lg">
                                </div>
                            `);
                        } else {
                            previewPane.append(`
                                <h1 class="font-bold text-gray-500 text-xl mb-2">
                                    Plan de repérage de la salle <span class="italic text-xl text-slate-400">"${plan.salle_name} , ${plan.li_name} , ${plan.li_quartier} , ${plan.ville_name} - ${plan.vi_code_postal} , ${plan.ville}"</span>
                                    <i class="fa-solid fa-download text-2xl ml-2 cursor-pointer hover:text-blue-500" title="Télécharger" onclick="downloadImage('${plan.salle_image}')"></i>
                                </h1>
                            `);
                            previewPane.append(`
                                <img src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/salles/${plan.salle_image}"
                                        alt="Image de la salle"
                                        class="w-full h-full rounded-lg cursor-pointer"
                                        onclick="openImageInModal(this.src)">

                                <div id="image-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex justify-center items-center z-50">
                                    <img id="modal-image" src="" alt="Image agrandie" class="max-w-full max-h-full p-2 rounded-lg">
                                </div>
                            `);
                        }
                    });
                } else {
                    previewPane.append(`<p class="text-gray-500 mt-4">Aucun plan de repérage trouvé pour ce projet.</p>`);
                }

                // Gestion de l'image du module
                var photo_formation = $('.photo_formation_' + module.idModule);
                photo_formation.html(module.module_image
                    ? `<img src="https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg/img/modules/${module.module_image}" class="object-cover w-full h-full rounded-xl">`
                    : `<div class="flex items-center justify-center w-full h-full text-3xl text-gray-500 bg-gray-100 rounded-xl">${module.moduleName[0]}</div>`
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

function showDocuments(route) {
    $.ajax({
        type: "get",
        url: route,
        dataType: "json",
        success: function (res) {

            console.log(res);

            if (res.module) {
                const module = res.module;

                const last = document.querySelector("#offcanvasSession");
                if (last) {
                    last.remove();
                }

                let html = '';

                html += `
                        <div
                            class="grid col-span-1 p-4 file-table-container bg-white shadow-sm rounded-xl lg:col-span-11 grid-cols-subgrid">
                            <div class="w-full table-responsive">
                                <table class="table w-full align-middle rounded-lg caption-top table-light">
                                    <caption class="mb-2 text-xl font-medium text-gray-800">Documents dans ce dossier</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col" class="!p-2 !text-xl !font-medium !bg-gray-100">Titre</th>
                                            <th scope="col" class="!p-2 !text-xl !font-medium !bg-gray-100">Section de document</th>
                                            <th scope="col" class="!p-2 !text-xl !font-medium !bg-gray-100">Type de document</th>
                                            <th scope="col" class="!p-2 !text-xl !font-medium !bg-gray-100">Taille</th>
                                            <th scope="col" class="!p-2 !text-xl !font-medium !bg-gray-100">dernière modification</th>
                                            <th scope="col" class="!p-2 !text-xl !font-medium !bg-gray-100">Action</th>
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
                    <div class="offcanvas offcanvas-end !w-[80em] overflow-y-auto" tabindex="-1" id="offcanvasSession"
                        aria-labelledby="offcanvasSession">
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
                                    <td class="!text-xl !text-gray-600 !bg-white">${val.titre}</td>
                                    <td class="!text-xl !text-gray-600 !bg-white">${val.section_document || 'Non spécifié'}</td>
                                    <td class="!text-xl !text-gray-600 !bg-white">${val.type_document || 'Non spécifié'}</td>
                                    <td class="!text-xl !text-gray-600 !bg-white">${val.taille || 'Non spécifié'} Mo</td>
                                    <td class="!text-xl !text-gray-600 !bg-white">${val.updated_at}</td>
                                    <td>
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
                        `<div class="flex items-center justify-center w-full h-full text-3xl text-gray-500 uppercase bg-gray-100 rounded-xl">` +
                        module.moduleName[0] + `</div>`);
                } else {
                    photo_formation.append(
                        `<img src="${endpoint}/${bucket}/img/modules/${module.module_image}" alt="profil" class="object-cover w-full h-full rounded-xl">`
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
            alert('Échec de la récupération des documents');
        }
    });

}

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
                const last = document.querySelector("#offcanvasApprenants")

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
                        <li class="pt-3 pb-0 sm:pt-4 pb-2">
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
                body.append(`<div class="offcanvas offcanvas-end !w-[60rem] overflow-y-auto" tabindex="-1" id="offcanvasEtp"
                        aria-labelledby="offcanvasEtp">
                        <div class="flex flex-col w-full">
                  
                                 <a data-bs-toggle="offcanvas" href="#offcanvasParticipant"
                                        class="w-full h-10  flex items-center justify-end">
                                        <i class="fa-solid p-3 fa-xmark text-gray-500 rounded-full hover:bg-gray-200 duration-200 cursor-pointer"></i>
                                </a>

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
                        `<div class="flex items-center justify-center w-full h-full text-3xl text-gray-500 uppercase bg-gray-100 rounded-xl">` +
                        module.moduleName[0] + `</div>`);
                } else {
                    photo_formation.append(
                        `<img src="${endpoint}/${bucket}/img/modules/${module.module_image}" alt="profil" class="object-cover w-full h-full rounded-xl">`
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