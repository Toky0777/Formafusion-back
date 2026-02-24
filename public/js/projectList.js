function _showProjet(res) {

    var projetCount = $('.projetCount');
    projetCount.html('');

    projetCount.append(`${res.projetCount ?? '--'}`);

    var headDate = $('#headDate');
    headDate.html('');

    if (res.projectDates.length <= 0) {
        headDate.append(`Pas de projet`);
    } else {
        $.each(res.projectDates, function (i, head) {
            headDate.append(`
            <ul class="menu w-full p-0 [&_li>*]:rounded-none">
                <li class="menu-title !text-2xl p-3 bg-slate-50 rounded-xl text-slate-700 capitalize">${head.headDate}</li>
                <section class="grid p-4 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-3 gap-4 content" data-view="carte" data-val="${head.headDate}">
                </section>
                <section class="grid p-4 grid-cols-1 hidden gap-4 content" data-view="list" data-val="list_${head.headDate}">
                </section>
            </ul>`);

            headDate.ready(function () {

                var content_grid_project = headDate.find(`.content[data-val="${head.headDate}"]`);
                content_grid_project.html('');

                var content_list_project = headDate.find(`.content[data-val="list_${head.headDate}"]`);
                content_list_project.html('');

                // Ajouter un écouteur d'événement pour chaque changement d'état de la checkbox
                $('input[name="view_check"]').on('change', toggleView);

                // Initialiser l'état de la section en fonction de l'état de la checkbox
                toggleView(); // Appel initial pour définir l'état des sections au chargement de la page

                $.each(res.projets, function (i, v) {
                    if (v.headDate == head.headDate) {

                        var nomDossier = "Pas de dossier";

                        if (v.dossier && v.dossier.length > 0) {
                            nomDossier = v.dossier[0].nomDossier;
                        }

                        let content_list = `<div class="grid col-span-1 p-4 rounded-2xl border-[1px] border-slate-200 shadow-md hover:shadow-xl duration-300 bg-white group">
                            <div class="grid grid-cols-12 gap-4 items-start">
                                <div class="grid col-span-6 gird-cols-subgrid">
                                    <div class="grid col-span-1">
                                        <div class="inline-flex items-center gap-2">
                                            <h3 onclick="${v.idModule ? `showFormation(${v.idModule})` : ''}" class="cursor-pointer text-xl text-slate-600 font-medium line-clamp-2">${v.module_name}</h3>
                                            <span class="inline-flex items-center h-full py-2 gap-2 p_ref_${v.idProjet}">
                                                <p class="text-slate-600 italic">Ref : ${v.project_reference}, </p>
                                            </span>
                                            <p class="text-slate-500">
                                            le <span class="text-slate-600 text-lg md:text-base lg:text-xl font-semibold">${formatDate(v.dateDebut)}</span> jusqu'au <span class="text-slate-600 text-lg md:text-base lg:text-xl font-semibold">${formatDate(v.dateFin)}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="grid col-span-1 py-1">
                                            <div data-bs-toggle="tooltip" onclick="showLieuDeReperage('/cfp/planreperage-drawer/${v.idProjet}')" title="Lieu de formation" class="inline-flex items-center gap-2 w-max">
                                                <i class="fa-solid fa-location-dot text-sm text-slate-400"></i>
                                                <p class="text-base text-slate-500 cursor-pointer line-clamp-1 hover:underline underline-offset-4">${v.salle_name}, ${v.li_name ?? '--'}, ${v.salle_quartier ?? '--'}, ${v.ville} (${v.salle_code_postal})</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="py-2 w-full text-slate-500 line-clamp-2">${v.project_description ?? 'Pas de description'}
                                    </div>
                                    <div class="inline-flex items-center gap-4">
                                        <span class="inline-flex relative items-center gap-2 cursor-pointer" onclick="showSessions('/cfp/session-drawer/${v.idProjet}', ${v.idProjet})" data-bs-toggle="tooltip" title="Heures">
                                            <p class="text-lg text-slate-600 font-medium">${v.totalSessionHour} <span class="text-slate-400 underline font-normal">Hrs</span></p>
                                        </span>
                                        <span class="inline-flex items-center gap-2 cursor-pointer relative" onclick="showSessions('/cfp/session-drawer/${v.idProjet}', ${v.idProjet})">
                                            ${v.seanceCount <= 0 ? `<i class="fa-solid fa-triangle-exclamation -top-1 -right-1 text-amber-500 fa-fade absolute"></i>` : ''}
                                            <p class="text-lg text-slate-600 font-medium" data-bs-toggle="tooltip" title="Sessions"><span id="session_${v.idProjet}">${v.seanceCount}</span> <span
                                                    class="text-slate-400 underline font-normal">Ses</span></p>
                                        </span>
                                        <span onclick="showDocuments('/cfp/document-drawer/${v.idProjet}')" class="inline-flex items-center gap-2 cursor-pointer">
                                            <p class="text-lg text-slate-600 font-medium" data-bs-toggle="tooltip" title="Documents">${v.nbDocument ?? 0} <span
                                                    class="text-slate-400 underline font-normal">Docs</span></p>
                                        </span>
                                    </div>
                                </div>
                                <div class="grid col-span-3 gird-cols-subgrid">
                                    <div class="grid col-span-1">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="px-3 py-1 text-base rounded-xl type_${v.idProjet}">${v.project_type}</span>
                                            <span class="px-3 py-1 text-base rounded-xl modalite_${v.idProjet}">${v.modalite}</span>
                                            <span class="px-3 py-1 text-base rounded-xl text-slate-600 bg-slate-50">${v.paiement}</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div class="grid col-span-1">
                                            <div class="inline-flex items-center gap-2">
                                                <div onclick="showApprenants('/cfp/apprenant-drawer/${v.idProjet}', ${v.idProjet})" class="apprs_${v.idProjet} avatar-group -space-x-4 relative rtl:space-x-reverse">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="grid col-span-1">
                                            <div class="flex -space-x-2 opacity-60 text-slate-400 form_${v.idProjet}" data-bs-toggle="tooltip" title="Formateurs"></div>
                                        </div>
                                    </div>

                                    <div class="grid col-span-1 py-1">
                                        <div data-bs-toggle="tooltip" onclick="showDossiers('/cfp/dossier-drawer/${v.idProjet}')" title="Dossier" class="inline-flex items-center gap-2 w-max">
                                            <i class="fa-solid fa-folder text-sm text-slate-400"></i>
                                            <p class="text-base text-slate-500 cursor-pointer hover:underline underline-offset-4">${nomDossier}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid col-span-3 gird-cols-subgrid">
                                    <div class="grid col-span-1">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            <span class="inline-flex items-center h-full gap-2 p_note_${v.idProjet}">
                                            </span>
                                            <span class="px-3 py-1 text-base rounded-xl statut_${v.idProjet}">${v.project_status}</span>
                                            <div class="dropdown dropdown-end">
                                                <div name="menu" tabindex="0" role="button" class="btn btn-square btn-outline btn-sm opacity-50">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </div>
                                                <ul tabindex="0" class="dropdown-content project_menu_${v.idProjet} menu bg-base-100 rounded-box z-[1] w-72 p-2 shadow text-slate-600"></ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid col-span-1 my-3">
                                        <div class="flex -space-x-2 text-slate-400 justify-end etp_client_${v.idProjet}"></div>
                                    </div>
                                    <div class="grid col-span-1 justify-end">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="px-3 py-1 text-base rounded-xl fact_${v.isPaid.idInvoiceStatus}">${v.isPaid.invoice_status_name ?? 'non facturé'}</span>
                                            <p class="text-slate-600 text-lg md:text-base lg:text-xl font-bold">${v.total_ht ?? 0}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                        content_list_project.append(content_list);

                        let content_card = `<div class="grid col-span-1 p-4 h-[380px] rounded-2xl border-[1px] border-slate-200 shadow-md hover:shadow-xl duration-300 bg-white group">
                            <div class="grid grid-cols-6">
                                <div class="grid col-span-5 grid-cols-subgrid">
                                    <span class="inline-flex items-center gap-6 justify-between mb-1 sub_contractor_${v.idProjet}">
                                    </span>
                                    <h3 onclick="${v.idModule ? `showFormation(${v.idModule})` : ''}" class="cursor-pointer text-xl text-slate-600 font-medium w-full line-clamp-2">${v.module_name}</h3>
                                    <span class="inline-flex items-center h-full py-2 gap-2 p_ref_${v.idProjet}">
                                        <p class="text-slate-600 italic">Ref : ${v.project_reference}</p>
                                    </span>
                                    <span class="inline-flex items-center h-full py-2 gap-2 p_note_${v.idProjet}">
                                    </span>
                                </div>

                                <div class="grid col-span-1 justify-end">
                                    <div class="dropdown dropdown-end">
                                        <div name="menu" tabindex="0" role="button" class="btn bg-white m-1 h-12 w-12 flex items-center rounded-xl duration-200 cursor-pointer justify-center hover:bg-slate-100">
                                            <i class="fa-solid fa-ellipsis-vertical text-slate-400 text-xl"></i>
                                        </div>
                                        <ul tabindex="0" class="dropdown-content project_menu_${v.idProjet} menu bg-base-100 rounded-box z-[1] w-72 p-2 shadow text-slate-600"></ul>
                                    </div>
                                </div>
                            </div>
                            <div class="inline-flex items-center gap-2 py-2 w-full justify-between">
                                <div class="inline-flex items-center gap-2">
                                    <span class="px-3 py-1 text-base rounded-xl type_${v.idProjet}">${v.project_type}</span>
                                    <span class="px-3 py-1 text-base rounded-xl modalite_${v.idProjet}">${v.modalite}</span>
                                    <span class="px-3 py-1 text-base rounded-xl text-slate-600 bg-slate-50">${v.paiement}</span>
                                </div>
                                <span class="px-3 py-1 text-base rounded-xl statut_${v.idProjet}">${v.project_status}</span>
                            </div>

                            <div class="grid col-span-1 py-1">
                                <div data-bs-toggle="tooltip" onclick="showLieuDeReperage('/cfp/planreperage-drawer/${v.idProjet}')" title="Lieu de formation" class="inline-flex items-center gap-2 w-full">
                                    <i class="fa-solid fa-location-dot text-sm text-slate-400"></i>
                                    <p class="text-base text-slate-500 cursor-pointer line-clamp-1 hover:underline underline-offset-4">${v.salle_name}, ${v.li_name ?? '--'}, ${v.salle_quartier ?? '--'}, ${v.ville} (${v.salle_code_postal})</p>
                                </div>
                            </div>

                            <div class="grid col-span-1 py-1">
                                <div data-bs-toggle="tooltip" onclick="showDossiers('/cfp/dossier-drawer/${v.idProjet}')" title="Dossier" class="inline-flex items-center gap-2 w-max">
                                    <i class="fa-solid fa-folder text-sm text-slate-400"></i>
                                    <p class="text-base text-slate-500 cursor-pointer hover:underline underline-offset-4">${nomDossier}</p>
                                </div>
                            </div>
            
                            <div class="py-2 w-full text-slate-500 line-clamp-2">${v.project_description ?? 'Pas de description'}
                            </div>
            
                            <div class="inline-flex items-center gap-2 py-3 w-full justify-between">
                                <div onclick="showApprenants('/cfp/apprenant-drawer/${v.idProjet}', ${v.idProjet})" class="apprs_${v.idProjet} avatar-group -space-x-4 relative rtl:space-x-reverse" data-bs-toggle="tooltip"
                                    title="Participants">
                                </div>
            
                                <div class="flex -space-x-2 text-slate-400 etp_client_${v.idProjet}">
                                </div>
                            </div>
            
                            <div class="inline-flex items-center gap-2 py-2 w-full justify-between">
                                <div class="flex -space-x-2 opacity-60 text-slate-400 form_${v.idProjet}">
                                </div>
            
                                <div class="inline-flex items-center gap-4">
                                    <span class="inline-flex relative items-center gap-2 cursor-pointer" onclick="showSessions('/cfp/session-drawer/${v.idProjet}', ${v.idProjet})" data-bs-toggle="tooltip" title="Heures">
                                        <p class="text-lg text-slate-600 font-medium">${v.totalSessionHour} <span
                                                class="text-slate-400 underline font-normal">Hrs</span>
                                        </p>
                                    </span>
                                    <span class="inline-flex items-center gap-2 cursor-pointer relative" onclick="showSessions('/cfp/session-drawer/${v.idProjet}', ${v.idProjet})">
                                        ${v.seanceCount <= 0 ? `<i class="fa-solid fa-triangle-exclamation -top-1 -right-1 text-amber-500 fa-fade absolute"></i>` : ''}
                                        <p class="text-lg text-slate-600 font-medium" data-bs-toggle="tooltip" title="Sessions"><span id="session_${v.idProjet}">${v.seanceCount}</span> <span
                                                class="text-slate-400 underline font-normal">Ses</span></p>
                                    </span>
                                    <span onclick="showDocuments('/cfp/document-drawer/${v.idProjet}')" class="inline-flex items-center gap-2 cursor-pointer">
                                        <p class="text-lg text-slate-600 font-medium" data-bs-toggle="tooltip" title="Documents">${v.nbDocument ?? 0} <span
                                                class="text-slate-400 underline font-normal">Docs</span></p>
                                    </span>
                                </div>
                            </div>

                            <div class="py-2 grid grid-cols-4 divide-x divide-slate-200">
                                <div class="grid col-span-2 grid-cols-2">
                                    <div class="flex flex-col items-start ml-3">
                                        <h5 class="text-slate-400 text-base capitalize">Début :</h5>
                                        <p class="text-slate-600 text-lg md:text-base lg:text-xl font-semibold">${formatDate(v.dateDebut)}</p>
                                    </div>
                                    <div class="flex flex-col items-start ml-3">
                                        <h5 class="text-slate-400 text-base capitalize">échéance :</h5>
                                        <p class="text-slate-600 text-lg md:text-base lg:text-xl font-semibold">${formatDate(v.dateFin)}</p>
                                    </div>
                                </div>
                                <div class="grid col-span-1">
                                    <div class="flex flex-col items-start ml-3">
                                        <h5 class="text-slate-400 text-base">Prix HT :</h5>
                                        <p class="text-slate-600 text-lg md:text-base lg:text-xl font-bold">${v.total_ht ?? 0}</p>
                                    </div>
                                </div>
                                <div class="grid col-span-1">
                                    <div class="flex flex-col items-start ml-3">
                                        <h5 class="text-slate-400 text-base">Paiement :</h5>
                                        <span class="px-3 py-1 text-base rounded-xl fact_${v.isPaid.idInvoiceStatus}">${v.isPaid.invoice_status_name ?? 'non facturé'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                        content_grid_project.append(content_card);


                        // Déclaration de variable
                        const p_type = $(`.type_${v.idProjet}`);
                        const p_modalite = $(`.modalite_${v.idProjet}`);
                        const p_statut = $(`.statut_${v.idProjet}`);
                        const p_etp_client = $(`.etp_client_${v.idProjet}`);
                        const p_form = $(`.form_${v.idProjet}`);
                        const p_apprs = $(`.apprs_${v.idProjet}`);
                        const p_projet_menu = $(`.project_menu_${v.idProjet}`);
                        var p_note = $(`.p_note_${v.idProjet}`);
                        var p_ispaid = $(`.fact_${v.isPaid.idInvoiceStatus}`);

                        // Initialisation
                        p_etp_client.html('');
                        p_form.html('');
                        p_apprs.html('');
                        p_projet_menu.html('');
                        p_note.html('');

                        let menu_item = `
                            <li class="menu-title">Action</li>
                            <li><a href="/cfp/projets/${v.idProjet}/detail"><i class="fa-solid fa-eye"></i> Aperçu</a></li>
                            `;

                        if (v.project_status != 'Planifié' && v.project_status != 'Cloturé' && v.project_status != 'Terminé' && v.project_status != 'En cours') {
                            menu_item += `<li><span onclick="showModalConfirmation(${v.idProjet}, 'Valider')"><i class="fa-solid fa-check"></i> Valider le projet</span></li>`;
                        };

                        menu_item += `
                                <li><span onclick="showModalConfirmation(${v.idProjet}, 'Supprimer')"><i class="fa-solid fa-trash-can"></i> Supprimer</span></li>
                                <li><span onclick="showModalConfirmation(${v.idProjet}, 'Dupliquer')"><i class="fa-solid fa-copy"></i> Dupliquer</span></li>
                                <li><span onclick="showModalConfirmation(${v.idProjet}, 'Archiver')"><i class="fa-solid fa-box-archive"></i> Archiver</span></li>`;

                        if (v.project_type == 'Inter') {
                            if (v.project_inter_privacy == 0) {
                                menu_item += `<li class="menu-title">Place de marché</li>
                                    <li><span onclick="showModalConfirmation(${v.idProjet}, 'RendrePublic')"><i class="fa-solid fa-store"></i> Mettre sur le marcher</span></li>`;
                            } else if (v.project_inter_privacy == 1) {
                                menu_item += `<li class="menu-title">Place de marché</li>
                                    <li><span onclick="showModalConfirmation(${v.idProjet}, 'RendrePrivee')"><i class="fa-solid fa-store-slash"></i> Retirer sur le marcher</span></li>`;
                            }
                        };

                        menu_item += `<li class="menu-title">Statut</li>`;

                        if (v.project_status != 'Annulé') {
                            menu_item += `<li><span onclick="showModalConfirmation(${v.idProjet}, 'Annuler')"><i class="fa-solid fa-xmark"></i> Annuler</span></li>`;
                        };
                        if (v.project_status != 'Reporté') {
                            menu_item += `<li><span onclick="showModalConfirmation(${v.idProjet}, 'Reporter')"><i class="fa-solid fa-calendar-days"></i> Reporter</span></li>`;
                        };
                        if (v.project_status != 'Cloturé') {
                            menu_item += `<li><span onclick="showModalConfirmation(${v.idProjet}, 'Cloturer')"><i class="fa-solid fa-circle-xmark"></i> Clôturer</span></li>`;
                        };

                        p_projet_menu.html(menu_item);


                        // Condition
                        switch (v.project_type) {
                            case 'Intra':
                                p_type.addClass(`text-[#1565c0] bg-[#1565c0]/10`);
                                break;
                            case 'Inter':
                                p_type.addClass(`text-[#7209b7] bg-[#7209b7]/10`);
                                break;
                            default:
                                p_type.addClass(`text-slate-600 bg-slate-50`);
                                break;
                        };

                        switch (v.modalite) {
                            case 'Présentielle':
                                p_modalite.addClass(`text-[#00b4d8] bg-[#00b4d8]/10`);
                                break;
                            case 'En ligne':
                                p_modalite.addClass(`text-[#fca311] bg-[#fca311]/10`);
                                break;
                            case 'Blended':
                                p_modalite.addClass(`text-[#005f73] bg-[#005f73]/10`);
                                break;

                            default:
                                p_modalite.addClass(`text-[#00b4d8] bg-[#00b4d8]/10`);
                                break;
                        };

                        switch (v.project_status) {
                            case 'En préparation':
                                p_statut.addClass(`text-white bg-[#F8E16F]`);
                                break;
                            case 'Réservé':
                                p_statut.addClass(`text-white bg-[#33303D]`);
                                break;
                            case 'En cours':
                                p_statut.addClass(`text-white bg-[#369ACC]`);
                                break;
                            case 'Terminé':
                                p_statut.addClass(`text-white bg-[#95CF92]`);
                                break;
                            case 'Annulé':
                                p_statut.addClass(`text-white bg-[#DE324C]`);
                                break;
                            case 'Reporté':
                                p_statut.addClass(`text-white bg-[#2E705A]`);
                                break;
                            case 'Planifié':
                                p_statut.addClass(`text-white bg-[#CBABD1]`);
                                break;
                            case 'Cloturé':
                                p_statut.addClass(`text-white bg-[#6F1926]`);
                                break;

                            default:
                                p_statut.addClass(`text-slate-600 bg-slate-50`);
                                break;
                        };

                        switch (v.isPaid.idInvoiceStatus) {
                            case 1: //brouillon
                                p_ispaid.addClass(`text-slate-600 bg-slate-50`);
                                break;
                            case 2: //Non envoyé
                                p_ispaid.addClass(`text-rose-500 bg-rose-50`);
                                break;
                            case 3: //Envoyé
                                p_ispaid.addClass(`text-[#37718e] bg-[#37718e]/10`);
                                break;
                            case 4: //payé
                                p_ispaid.addClass(`text-teal-600 bg-teal-600/10`);
                                break;
                            case 5: //partiel
                                p_ispaid.addClass(`text-yellow-600 bg-yellow-600/10`);
                                break;
                            case 6: //Impayé
                                p_ispaid.addClass(`text-red-400 bg-red-400/10`);
                                break;
                            case 7: //Convertis
                                p_ispaid.addClass(`text-green-600 bg-green-600/10`);
                                break;
                            case 8: //Expiré
                                p_ispaid.addClass(`text-red-600 bg-red-600/10`);
                                break;
                            default:
                                p_ispaid.addClass(`text-slate-600 bg-slate-50`);
                                break;
                        };

                        if (v.etp_name.length > 0) {
                            if (v.idCfp_inter == null || v.idCfp_inter == "null") {
                                $.each(v.etp_name, function (i_etp, v_etp) {
                                    if (v_etp.etp_logo != null) {
                                        p_etp_client.append(`
                                                    <img onclick="showCustomer(${v_etp.idEtp}, '/cfp/etp-drawer/', ${v.idProjet})" class="cursor-pointer inline-block h-[30px] w-[53.2px] grayscale hover:grayscale-0 duration-200 rounded-xl ring-2 ring-white"
                                                        src="${endpoint}/${bucket}/img/entreprises/${v_etp.etp_logo}"
                                                        alt="" />
                                                        `);
                                    } else {
                                        p_etp_client.append(`
                                                <div onclick="showCustomer(${v_etp.idEtp}, '/cfp/etp-drawer/', ${v.idProjet})" class="cursor-pointer inline-block h-[30px] w-[53.2px] rounded-xl ring-2 ring-white text-slate-600 bg-slate-100 flex font-bold items-center justify-center uppercase">${v_etp.etp_name[0]}</div>
                                                `);
                                    }
                                });
                            } else {
                                $.each(v.etp_name, function (i_etp, v_etp) {
                                    if (v_etp.etp_logo != null) {
                                        p_etp_client.append(`
                                                        <img onclick="drawerClient(${v.idProjet}, ${v.idCfp_inter})" class="cursor-pointer inline-block h-[30px] w-[53.2px] grayscale hover:grayscale-0 duration-200 rounded-xl ring-2 ring-white"
                                                            src="${endpoint}/${bucket}/img/entreprises/${v_etp.etp_logo}"
                                                            alt="" />
                                                            `);
                                    } else {
                                        p_etp_client.append(`
                                                    <div onclick="drawerClient(${v.idProjet}, ${v.idCfp_inter})" class="cursor-pointer inline-block h-[30px] w-[53.2px] rounded-xl ring-2 ring-white text-slate-600 bg-slate-100 flex font-bold items-center justify-center uppercase">${v_etp.etp_name[0]}</div>
                                                    `);
                                    }
                                });
                            }
                        } else {
                            p_etp_client.append(`
                            <div class="relative">
                                    <span class="absolute -top-2 -right-2 rounded-full z-[99]"><i class="fa-solid text-amber-500 text-lg fa-fade fa-triangle-exclamation"></i></span>
                                    <div onclick="drawerClient(${v.idProjet}, ${v.idCfp_inter})" data-bs-toggle="tooltip" title="Entreprise Client" class="inline-block h-[30px] w-[53.2px] rounded-xl ring-2 ring-white text-slate-600 bg-slate-200 flex font-bold items-center justify-center uppercase"></div>
                            </div>`);
                        }

                        if (v.formateurs.length > 0) {
                            $.each(v.formateurs, function (i_f, v_f) {
                                if (v_f.form_photo != null) {
                                    p_form.append(`
                                                <img onclick="viewMiniCV(${v_f.idFormateur}, ${v.idProjet})" class="cursor-pointer inline-block h-8 w-8 rounded-full ring-2 ring-white"
                                                src="${endpoint}/${bucket}/img/formateurs/${v_f.form_photo}"
                                                alt="" />
                                                    `);
                                } else {
                                    p_form.append(`
                                            <div onclick="viewMiniCV(${v_f.idFormateur}, ${v.idProjet})" class="cursor-pointer inline-block h-8 w-8 rounded-full ring-2 ring-white text-slate-600 bg-slate-100 flex font-bold items-center justify-center uppercase">${v_f.form_initial_name[0]}</div>
                                            `);
                                }
                            });
                        } else {
                            p_form.append(`
                            <div class="relative">
                                <span class="absolute -top-2 -right-2 rounded-full z-[99]"><i class="fa-solid text-amber-500 text-lg fa-fade fa-triangle-exclamation"></i></span>
                                <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white text-slate-600 bg-slate-200 flex font-bold items-center justify-center uppercase"></div>
                            </div>
                                    `);
                        }

                        if (v.apprCount > 0 && v.apprCount < 4) {
                            $.each(v.apprs, function (i_ap, v_ap) {
                                if (v_ap.emp_photo != null) {
                                    p_apprs.append(`
                                            <div class="avatar">
                                                <div class="w-8 rounded-full overflow-hidden">
                                                    <img src="${endpoint}/${bucket}/img/employes/${v_ap.emp_photo}"/>
                                                </div>
                                            </div>`);
                                } else {
                                    p_apprs.append(`
                                            <div class="avatar placeholder rounded-full overflow-hidden cursor-pointer">
                                                <div class="bg-slate-200 text-slate-600 w-8 rounded-full">
                                                    <span class="text-xl">${v_ap.emp_initial_name}</span>
                                                </div>
                                            </div>
                                        `);
                                }
                            });
                        } else if (v.apprCount >= 4) {
                            const totalApprentices = v.apprs.length;
                            const remainingApprentices = totalApprentices - 3;
                            const baseNumber = Math.floor(totalApprentices / 10);

                            for (let i = 0; i < 3; i++) {
                                if (v.apprs[i].emp_photo != null) {
                                    p_apprs.append(`
                                            <div class="avatar">
                                                <div class="w-8 rounded-full overflow-hidden">
                                                    <img src="${endpoint}/${bucket}/img/employes/${v.apprs[i].emp_photo}"/>
                                                </div>
                                            </div>`);
                                } else {
                                    p_apprs.append(`
                                            <div class="avatar rounded-full overflow-hidden placeholder cursor-pointer">
                                                <div class="bg-slate-200 text-slate-600 w-8 rounded-full">
                                                    <span class="text-xl">${v.apprs[i].emp_initial_name}</span>
                                                </div>
                                            </div>
                                        `);
                                }
                            }

                            p_apprs.append(`
                                    <div class="avatar placeholder cursor-pointer rounded-full overflow-hidden">
                                        <div class="bg-neutral !opacity-100 text-white w-8 rounded-full">
                                        <span class="text-md">+${remainingApprentices}</span>
                                    </div>
                                `);
                        } else {
                            p_apprs.append(`
                            <div class="relative">
                                <span class="absolute top-0 right-0 rounded-full z-[99]"><i class="fa-solid text-amber-500 text-lg fa-fade fa-triangle-exclamation"></i></span>
                                <span id="empty_appr" class="avatar-group -space-x-4 relative rtl:space-x-reverse">
                                    <div class="avatar">
                                        <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white text-slate-600 bg-slate-200 flex font-bold items-center justify-center uppercase"></div>
                                    </div>
                                    <div class="avatar">
                                        <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white text-slate-600 bg-slate-200 flex font-bold items-center justify-center uppercase"></div>
                                    </div>
                                    <div class="avatar">
                                        <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white text-slate-600 bg-slate-200 flex font-bold items-center justify-center uppercase"></div>
                                    </div>
                                </span>
                            </div>
                            `)
                        }

                        p_note.append(`<div class="inline-flex items-center justify-end gap-1">
                            <div id="raty_notation_${v.idProjet}"
                                data-val="${v.general_note ? v.general_note[0] : '0'}"
                                class="inline-flex items-center gap-1 raty_notation_id">
                            </div>
                            <p class="font-medium text-gray-500 p_note_${v.idProjet}">
                            ${v.general_note ? formatNumber(v.general_note[0], 1, ',', ' ') : '0'}</p>
                                <span class="text-gray-400">
                                    (${v.general_note ? v.general_note[1] : '0'} avis)
                                </span>
                            </div>`);


                        var RatingNote = $(`.raty_notation_id`);

                        $.each(RatingNote, function (i, v) {
                            ratyNotation($(this).attr('id'), parseFloat($(this).attr('data-val')));
                        });



                        //  Activer les tooltip bootstrap
                        loadBsTooltip();
                        const sub_contractor = $('.sub_contractor_' + v.idProjet);
                        sub_contractor.empty();
                        if (v.sub_name != null) {
                            if (v.idUser == v.idSubContractor) {
                                sub_contractor.append(`<h3 class="text-md text-slate-400 text-wrap line-clamp-2">Commanditaire : ${v.cfp_name}</h3>`);
                            } else {
                                sub_contractor.append(`<h3 class="text-md text-slate-400 text-wrap line-clamp-2">Sous-traitant : ${v.sub_name}</h3>`);
                            }
                        } else {
                            sub_contractor.html('');
                        }
                    }
                });
            });
        });
    }
}


function formatNumber(number, decimals, dec_point, thousands_sep) {
    // Limiter à 'decimals' chiffres après la virgule
    let n = number.toFixed(decimals);

    // Remplacer le point par la virgule pour la partie décimale
    n = n.replace('.', dec_point);

    // Séparer les parties entière et décimale
    const parts = n.split(dec_point);
    let integerPart = parts[0];
    const decimalPart = parts.length > 1 ? dec_point + parts[1] : '';

    // Ajouter les séparateurs de milliers
    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);

    return integerPart + decimalPart;
}


// Fonction pour formater la date
function formatDate(dateString) {
    // Convertir la chaîne de date en un objet Date
    var date = moment(dateString.replace(/-/g, '/'), 'YYYY-MM-DD');

    // Formater la date selon le format souhaité
    var formattedDate = date.format('DD MMM YYYY');

    return formattedDate;
}

function formatAmount(nombre) {
    // const nombre = 3100000;
    const formattedNumber = nombre.toLocaleString('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
    console.log(formattedNumber); // Affichera "3.1M"
    return formattedNumber;
}