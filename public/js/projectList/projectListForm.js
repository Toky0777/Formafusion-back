function _showProjet(res) {
    var projetFormCount = $('.projetFormCount');
    projetFormCount.html('');

    projetFormCount.append(`${res.projetFormCount ?? '--'}`);
    var headDate = $('#headDate');
    headDate.html('');

    if (res.projectDates.length <= 0) {
        headDate.append(`Pas de projet`);
    } else {
        $.each(res.projectDates, function (i, head) {

            const uniqueHeadDates = []; // Tableau pour stocker les valeurs uniques de v.headDate

            $.each(res.projets, function (i, v) {
                // Vérifier si v.headDate n'est pas déjà dans le tableau
                if (!uniqueHeadDates.includes(v.headDate)) {
                    uniqueHeadDates.push(v.headDate); // Ajouter v.headDate au tableau s'il est unique
                }
            });

            $.each(uniqueHeadDates, function (i, v) {
                if (v == head.headDate) {
                    headDate.append(`
                        <ul class="menu w-full p-0 [&_li>*]:rounded-none">
                            <li class="menu-title !text-2xl p-3 bg-white text-slate-700 capitalize"><a>${head.headDate}</a></li>
                            <section class="grid p-4 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-3 gap-4 content" data-val="${head.headDate}">
                            </section>
                        </ul>`);
                }
            });

            headDate.ready(function () {

                var content_grid_project = headDate.find(`.content[data-val="${head.headDate}"]`);
                content_grid_project.html('');

                $.each(res.projets, function (i, v) {
                    if (v.headDate == head.headDate) {

                        content_grid_project.append(
                            `<div class="grid col-span-1 p-4 h-[380px] rounded-2xl border-[1px] border-slate-200 shadow-md hover:shadow-xl relative duration-300 bg-white overflow-hidden group">
                                <div class="grid grid-cols-6">
                                    <div class="grid col-span-5 grid-cols-subgrid">
                                        <span class="inline-flex items-center gap-6 justify-between mb-1 sub_contractor_${v.idProjet}">
                                        </span>
                                        <h3 onclick="${v.idModule ? `showFormation(${v.idModule})` : ''}" class="cursor-pointer text-xl text-slate-600 font-medium w-full line-clamp-2">${v.module_name}</h3>
                                        <span class="inline-flex items-center h-full py-2 gap-2 p_note_${v.idProjet}">
                                        </span>
                                    </div>
                                    <div class="grid col-span-1 justify-end">
                                        <div class="dropdown dropdown-end">
                                            <div tabindex="0" role="button" class="btn bg-white m-1 h-12 w-12 flex items-center rounded-xl duration-200 cursor-pointer justify-center hover:bg-slate-100">
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
                                    <div data-bs-toggle="tooltip" onclick="showLieuDeReperage('/projetsForm/planreperage-drawer/${v.idProjet}')" title="Lieu de formation" class="inline-flex items-center gap-2 w-max">
                                        <i class="fa-solid fa-location-dot text-sm text-slate-400"></i>
                                        <p class="text-base text-slate-500 cursor-pointer hover:underline underline-offset-4">${v.salle_name}, ${v.li_name ?? '--'}, ${v.salle_quartier ?? '--'}, ${v.ville} (${v.salle_code_postal})</p>
                                    </div>
                                </div>
                
                                <div class="py-2 w-full text-slate-500 line-clamp-2">${v.project_description ?? 'Pas de description'}
                                </div>
                
                                <div class="inline-flex items-center gap-2 py-3 w-full justify-between">
                                    <div onclick="showApprenants('/projetsForm/apprenants-drawer/${v.idProjet}')" class="avatar-group -space-x-4 rtl:space-x-reverse apprs_${v.idProjet}" data-bs-toggle="tooltip"
                                        title="Participants">
                                         <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white text-slate-600 bg-slate-100 flex font-bold items-center justify-center uppercase">${v.apprCount}</div>
                                    </div>
                
                                    <div class="flex -space-x-2 overflow-hidden text-slate-400 etp_client_${v.idProjet}">
                                    </div>
                                </div>
                
                                <div class="inline-flex items-center gap-2 py-2 w-full justify-between">
                                    <div class="flex -space-x-2 overflow-hidden opacity-60 text-slate-400 form_${v.idProjet}" data-bs-toggle="tooltip" title="Formateurs">
                                    </div>
                
                                    <div class="inline-flex items-center gap-4">
                                        <span class="inline-flex items-center gap-2 cursor-pointer" onclick="showSessions('/projetsForm/${v.idProjet}/session-drawer/')" data-bs-toggle="tooltip" title="Heures">
                                            <p class="text-lg text-slate-600 font-medium">${v.totalSessionHour} <span
                                                    class="text-slate-400 underline font-normal">Hrs</span>
                                            </p>
                                        </span>
                                        <span class="inline-flex items-center gap-2 cursor-pointer" onclick="showSessions('/projetsForm/${v.idProjet}/session-drawer/')">
                                            <p class="text-lg text-slate-600 font-medium" data-bs-toggle="tooltip" title="Sessions">${v.seanceCount} <span
                                                    class="text-slate-400 underline font-normal">Ses</span></p>
                                        </span>
                                        <span onclick="showDocuments('/projetsForm/${v.idProjet}/document-drawer/')" class="inline-flex items-center gap-2 cursor-pointer">
                                            <p class="text-lg text-slate-600 font-medium" data-bs-toggle="tooltip" title="Documents">${v.nbDocument ?? 0} <span
                                                    class="text-slate-400 underline font-normal">Docs</span></p>
                                        </span>
                                    </div>
                                </div>

                                <div class="py-2 grid grid-cols-3 divide-x divide-slate-200">
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
                                </div>
                            </div>`);

                        // Déclaration de variable
                        const p_type = $(`.type_${v.idProjet}`);
                        const p_modalite = $(`.modalite_${v.idProjet}`);
                        const p_statut = $(`.statut_${v.idProjet}`);
                        const p_etp_client = $(`.etp_client_${v.idProjet}`);
                        const p_form = $(`.form_${v.idProjet}`);
                        const p_apprs = $(`.apprs_${v.idProjet}`);
                        const p_projet_menu = $(`.project_menu_${v.idProjet}`);
                        var p_note = $(`.p_note_${v.idProjet}`);

                        // Initialisation
                        p_etp_client.html('');
                        p_form.html('');
                        p_apprs.html('');
                        p_projet_menu.html('');
                        p_note.html('');

                        const menu_item = `
                            <li class="menu-title">Action</li>
                            <li><a href="/projetsForm/${v.idProjet}/detailForm"><i class="fa-solid fa-eye"></i> Aperçu</a></li>
                            `;

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

                        if (v.etp_name.length > 0) {
                            $.each(v.etp_name, function (i_etp, v_etp) {
                                if (v_etp.etp_logo != null) {
                                    p_etp_client.append(`
                                                <img onclick="showCustomer(${v_etp.idEtp}, '/projetsForm/etp-drawer/')" class="cursor-pointer inline-block h-10 w-20 grayscale hover:grayscale-0 duration-200 rounded-xl ring-2 ring-white"
                                                    src="${endpoint}/${bucket}/img/entreprises/${v_etp.etp_logo}"
                                                    alt="" />
                                                    `);
                                } else {
                                    p_etp_client.append(`
                                            <div onclick="showCustomer(${v_etp.idEtp}, '/projetsForm/etp-drawer/')" class="cursor-pointer inline-block h-10 w-20 rounded-xl ring-2 ring-white text-slate-600 bg-slate-100 flex font-bold items-center justify-center uppercase">${v_etp.etp_name}</div>
                                            `);
                                }
                            });
                        } else {
                            p_etp_client.append(`
                                    <div data-bs-toggle="tooltip" title="Entreprise Client" class="inline-block h-10 w-20 rounded-xl ring-2 ring-white text-slate-600 bg-slate-200 flex font-bold items-center justify-center uppercase"></div>
                                    `);
                        }

                        if (v.formateurs.length > 0) {
                            $.each(v.formateurs, function (i_f, v_f) {
                                if (v_f.form_photo != null) {
                                    p_form.append(`
                                                <img onclick="viewMiniCV(${v_f.idFormateur})" class="cursor-pointer inline-block h-8 w-8 rounded-full ring-2 ring-white"
                                                src="${endpoint}/${bucket}/img/formateurs/${v_f.form_photo}"
                                                alt="" />
                                                    `);
                                } else {
                                    p_form.append(`
                                            <div onclick="viewMiniCV(${v_f.idFormateur})" class="cursor-pointer inline-block h-8 w-8 rounded-full ring-2 ring-white text-slate-600 bg-slate-100 flex font-bold items-center justify-center uppercase">${v_f.form_initial_name[0]}</div>
                                            `);
                                }
                            });
                        } else {
                            p_form.append(`
                                    <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white text-slate-600 bg-slate-200 flex font-bold items-center justify-center uppercase"></div>
                                    `);
                        }

                        if (v.apprCount > 0 && v.apprCount < 4) {
                            $.each(v.apprs, function (i_ap, v_ap) {
                                if (v_ap.emp_photo != null) {
                                    p_apprs.append(`
                                            <div class="avatar">
                                                <div class="w-8">
                                                    <img src="${endpoint}/${bucket}/img/employes/${v_ap.emp_photo}"/>
                                                </div>
                                            </div>`);
                                } else {
                                    p_apprs.append(`
                                            <div class="avatar placeholder cursor-pointer">
                                                <div class="bg-slate-200 text-slate-600 w-8 rounded-full">
                                                    <span class="text-xl">${v_ap.emp_name}</span>
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
                                                <div class="w-8">
                                                    <img src="${endpoint}/${bucket}/img/employes/${v.apprs[i].emp_photo}"/>
                                                </div>
                                            </div>`);
                                } else {
                                    p_apprs.append(`
                                            <div class="avatar placeholder cursor-pointer">
                                                <div class="bg-slate-200 text-slate-600 w-8 rounded-full">
                                                    <span class="text-xl">${v.apprs[i].emp_name}</span>
                                                </div>
                                            </div>
                                        `);
                                }
                            }

                            p_apprs.append(`
                                    <div class="avatar placeholder cursor-pointer">
                                        <div class="bg-neutral !opacity-100 text-white w-8 rounded-full">
                                        <span class="text-md">+${remainingApprentices >= baseNumber ? `${baseNumber}${remainingApprentices}` : remainingApprentices}</span>
                                    </div>
                                `);
                        } else {
                            for (let i = 0; i < 4; i++) {
                                p_apprs.append(`
                                        <div class="avatar">
                                            <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white text-slate-600 bg-slate-200 flex font-bold items-center justify-center uppercase"></div>
                                        </div>
                                    `);
                            }
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
    return formattedNumber;
}