
// function getEtps(){
//     $.ajax({
//         type: "get",
//         url: "/cfp/sidebar/getEtps",
//         dataType: "json",
//         success: function (res) {
//             var project_all_count = $('#project_all_count');
//             var project_all = $('#project_all');
//             var project_b = $('#project_daft');
//             var project_e = $('#project_current');
//             var project_p = $('#project_coming');
//             var project_t = $('#project_finished');

//             project_all_count.html('');
//             project_all_count.append(`<a id="projet" href="#"
//                                             class="drawerBtn flex flex-col gap-1 w-14 h-14 group/drawer relative justify-center hover:shadow-md duration-150 bg-white cursor-pointer rounded-md top-3 group/sidebar items-center">
//                                             <i
//                                             class="bi bi-folder text-xl text-gray-400 group-hover/sidebar:text-gray-600 group-focus-within:text-purple-500 duration-150 cursor-pointer"></i>
//                                             <p class="text-xs text-white absolute -top-1 -right-3 bg-gray-300 px-2 rounded-full">`+res.projects.length+`</p>
//                                             <span
//                                             class="px-3 py-1 bg-gray-700 absolute left-20 hidden z-[999] text-white w-max group-hover/sidebar:block duration-300 rounded-md">Explorer vos projets</span>
//                                         </a>`);
//             project_all.html('');
//             project_b.html('');
//             project_e.html('');
//             project_p.html('');
//             project_t.html('');
//             if(res.projects.length > 0){
//                 project_all.text(res.projects.length);
//                 project_b.text(res.projetctCountB);
//                 project_e.text(res.projetctCountE);
//                 project_p.text(res.projetctCountP);
//                 project_t.text(res.projetctCountT);

//                 if(res.projetctB.length > 0){
//                     $('#draft_etps').html('');
//                     $.each(res.projetctB, function (k, v) { 
//                         $('#draft_etps').append(`<details class="w-full flex flex-col group/detail2">
//                                                         <summary
//                                                             class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-3">
//                                                             <div class="w-6 h-6 flex items-center justify-center">
//                                                             <i
//                                                                 class="fa-solid fa-chevron-right group-open/detail2:rotate-90 transition-all duration-200 text-gray-300"></i>
//                                                             </div>
//                                                             <div class="w-6 h-6 flex items-center justify-center mx-1">
//                                                             <i class="fa-regular fa-folder text-gray-300"></i>
//                                                             </div>
//                                                             <div onclick="getModules(`+v.idEtp+`)" class="h-6 text-md font-normal flex flex-row text-gray-500">`+v.etp_name+`<span class="ml-4">`+v.module_count.length+`</span>
//                                                             </div>
//                                                         </summary>
//                                                         <div id="draft_mdls_`+v.idEtp+`"></div>
//                                                     </details>`);
//                     });
//                 }

//                 if(res.projetctE.length > 0){
//                     $('#current_etps').html('');
//                     $.each(res.projetctE, function (k, v) { 
//                         $('#current_etps').append(`<details class="w-full flex flex-col group/detail2">
//                                                         <summary
//                                                             class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-3">
//                                                             <div class="w-6 h-6 flex items-center justify-center">
//                                                             <i
//                                                                 class="fa-solid fa-chevron-right group-open/detail2:rotate-90 transition-all duration-200 text-gray-300"></i>
//                                                             </div>
//                                                             <div class="w-6 h-6 flex items-center justify-center mx-1">
//                                                             <i class="fa-regular fa-folder text-gray-300"></i>
//                                                             </div>
//                                                             <div onclick="getModules(`+v.idEtp+`)" class="h-6 text-md font-normal flex flex-row text-gray-500">`+v.etp_name+`<span class="ml-4">`+v.module_count.length+`</span>
//                                                             </div>
//                                                         </summary>
//                                                         <div id="current_mdls_`+v.idEtp+`"></div>
//                                                     </details>`);
//                     });
//                 }

//                 if(res.projetctP.length > 0){
//                     $('#coming_etps').html('');
//                     $.each(res.projetctP, function (k, v) { 
//                         $('#coming_etps').append(`<details class="w-full flex flex-col group/detail2">
//                                                         <summary
//                                                             class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-3">
//                                                             <div class="w-6 h-6 flex items-center justify-center">
//                                                             <i
//                                                                 class="fa-solid fa-chevron-right group-open/detail2:rotate-90 transition-all duration-200 text-gray-300"></i>
//                                                             </div>
//                                                             <div class="w-6 h-6 flex items-center justify-center mx-1">
//                                                             <i class="fa-regular fa-folder text-gray-300"></i>
//                                                             </div>
//                                                             <div onclick="getModules(`+v.idEtp+`)" class="h-6 text-md font-normal flex flex-row text-gray-500">`+v.etp_name+`<span class="ml-4">`+v.module_count.length+`</span>
//                                                             </div>
//                                                         </summary>
//                                                         <div id="coming_mdls_`+v.idEtp+`"></div>
//                                                     </details>`);
//                     });
//                 }

//                 if(res.projetctT.length > 0){
//                     $('#finished_etps').html('');
//                     $.each(res.projetctT, function (k, v) { 
//                         $('#finished_etps').append(`<details class="w-full flex flex-col group/detail2">
//                                                         <summary
//                                                             class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-3">
//                                                             <div class="w-6 h-6 flex items-center justify-center">
//                                                             <i
//                                                                 class="fa-solid fa-chevron-right group-open/detail2:rotate-90 transition-all duration-200 text-gray-300"></i>
//                                                             </div>
//                                                             <div class="w-6 h-6 flex items-center justify-center mx-1">
//                                                             <i class="fa-regular fa-folder text-gray-300"></i>
//                                                             </div>
//                                                             <div onclick="getModules(`+v.idEtp+`)" class="h-6 text-md font-normal flex flex-row text-gray-500">`+v.etp_name+`<span class="ml-4">`+v.module_count.length+`</span>
//                                                             </div>
//                                                         </summary>
//                                                         <div id="finished_mdls_`+v.idEtp+`"></div>
//                                                     </details>`);
//                     });
//                 }
//             }
//         }
//     });
// }

function getModules(idEtp){
    $.ajax({
        type: "get",
        url: "/cfp/sidebar/getModules/"+idEtp,
        dataType: "json",
        success: function (res) {
            if(res.mdlsB.length > 0){
                $('#draft_mdls_'+idEtp).html('');
                $.each(res.mdlsB, function (i, val) { 
                    $('#draft_mdls_'+idEtp).append(`<details class="w-full flex flex-col group/detail3">
                                                        <summary
                                                            onclick="getProjects(`+idEtp+`, `+val.idModule+`)"
                                                            class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-6">
                                                            <div class="w-6 h-6 flex items-center justify-center">
                                                                <i
                                                                class="fa-solid fa-chevron-right group-open/detail3:rotate-90 transition-all duration-200 text-gray-300"></i>
                                                            </div>
                                                            <div class="w-6 h-6 flex items-center justify-center mx-1">
                                                                <i class="fa-regular fa-folder text-gray-300"></i>
                                                            </div>
                                                            <div class="h-6 text-md font-normal text-gray-500">`+val.module_name+`</div>
                                                        </summary>
                                                        <div id="draft_project`+idEtp+"_"+val.idModule+`"></div>
                                                    </details>`);
                });
            }

            if(res.mdlsE.length > 0){
                $('#current_mdls_'+idEtp).html('');
                $.each(res.mdlsE, function (i, val) { 
                    $('#current_mdls_'+idEtp).append(`<details class="w-full flex flex-col group/detail3">
                                                        <summary
                                                            onclick="getProjects(`+idEtp+`, `+val.idModule+`)"
                                                            class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-6">
                                                            <div class="w-6 h-6 flex items-center justify-center">
                                                                <i
                                                                class="fa-solid fa-chevron-right group-open/detail3:rotate-90 transition-all duration-200 text-gray-300"></i>
                                                            </div>
                                                            <div class="w-6 h-6 flex items-center justify-center mx-1">
                                                                <i class="fa-regular fa-folder text-gray-300"></i>
                                                            </div>
                                                            <div class="h-6 text-md font-normal text-gray-500">`+val.module_name+`</div>
                                                        </summary>
                                                        <div id="current_project`+idEtp+"_"+val.idModule+`"></div>
                                                    </details>`);
                });
            }

            if(res.mdlsP.length > 0){
                $('#coming_mdls_'+idEtp).html('');
                $.each(res.mdlsP, function (i, val) { 
                    $('#coming_mdls_'+idEtp).append(`<details class="w-full flex flex-col group/detail3">
                                                        <summary
                                                            onclick="getProjects(`+idEtp+`, `+val.idModule+`)"
                                                            class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-6">
                                                            <div class="w-6 h-6 flex items-center justify-center">
                                                                <i
                                                                class="fa-solid fa-chevron-right group-open/detail3:rotate-90 transition-all duration-200 text-gray-300"></i>
                                                            </div>
                                                            <div class="w-6 h-6 flex items-center justify-center mx-1">
                                                                <i class="fa-regular fa-folder text-gray-300"></i>
                                                            </div>
                                                            <div class="h-6 text-md font-normal text-gray-500">`+val.module_name+`</div>
                                                        </summary>
                                                        <div id="coming_project`+idEtp+"_"+val.idModule+`"></div>
                                                    </details>`);
                });
            }

            if(res.mdlsT.length > 0){
                $('.finished_mdls_count'+idEtp).text(res.mdlsT.length);
                $('#finished_mdls_'+idEtp).html('');
                $.each(res.mdlsT, function (i, val) { 
                    $('#finished_mdls_'+idEtp).append(`<details class="w-full flex flex-col group/detail3">
                                                        <summary
                                                            onclick="getProjects(`+idEtp+`, `+val.idModule+`)"
                                                            class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-6">
                                                            <div class="w-6 h-6 flex items-center justify-center">
                                                                <i
                                                                class="fa-solid fa-chevron-right group-open/detail3:rotate-90 transition-all duration-200 text-gray-300"></i>
                                                            </div>
                                                            <div class="w-6 h-6 flex items-center justify-center mx-1">
                                                                <i class="fa-regular fa-folder text-gray-300"></i>
                                                            </div>
                                                            <div class="h-6 text-md font-normal text-gray-500">`+val.module_name+`</div>
                                                        </summary>
                                                        <div id="finished_project`+idEtp+"_"+val.idModule+`"></div>
                                                    </details>`);
                });
            }
        }
    });
}

//getEtps();

function getDates(){
    $.ajax({
        type: "get",
        url: "/cfp/sidebar/getDates",
        dataType: "json",
        success: function (res) {
            var project_all_dates = $('#project_all_dates');
            project_all_dates.html('');

            if(res.dates.length > 0){
                $.each(res.dates, function (key, val) { 
                    project_all_dates.append(`<details class="w-full flex flex-col group/detail2">
                                                <summary
                                                    onclick="getDateEtps(`+val.project_year+`)"
                                                    class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-3">
                                                    <div class="w-6 h-6 flex items-center justify-center">
                                                        <i
                                                        class="fa-solid fa-chevron-right group-open/detail2:rotate-90 transition-all duration-200 text-gray-300"></i>
                                                    </div>
                                                    <div class="w-6 h-6 flex items-center justify-center mx-1">
                                                        <i class="fa-regular fa-folder text-gray-300"></i>
                                                    </div>
                                                    <div class="h-6 text-md font-normal flex flex-row text-gray-500">`+val.project_year+`<span class="ml-4">`+res.countEtps.length+`</span>
                                                    </div>
                                                </summary>
                                                <div id="all_date_etps"></div>
                                            </details>`);
                });
            }
        }
    });
}

function getDateEtps(year){
    $.ajax({
        type: "get",
        url: "/cfp/sidebar/getDateEtps/"+year,
        dataType: "json",
        success: function (res) {
            var all_date_etps = $('#all_date_etps');
            all_date_etps.html('');

            $.each(res.etps, function (key, val) { 
                 all_date_etps.append(`<details class="w-full flex flex-col group/detail3">
                                            <summary
                                                onclick="getDateModules(`+year+`, `+val.idEtp+`)"
                                                class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-6">
                                                <div class="w-6 h-6 flex items-center justify-center">
                                                    <i
                                                    class="fa-solid fa-chevron-right group-open/detail3:rotate-90 transition-all duration-200 text-gray-300"></i>
                                                </div>
                                                <div class="w-6 h-6 flex items-center justify-center mx-1">
                                                    <i class="fa-regular fa-folder text-gray-300"></i>
                                                </div>
                                                <div class="h-6 text-md font-normal text-gray-500">`+val.etp_name+`</div>
                                            </summary>
                                            <div id="all_module_etps`+val.idEtp+`"></div>
                                        </details>`);
            });
        }
    });
}

function getDateModules(year, idEtp){
    $.ajax({
        type: "get",
        url: "/cfp/sidebar/getDateModules/"+year+"/"+idEtp,
        dataType: "json",
        success: function (res) {
            console.log(res);
            var all_module_etps = $('#all_module_etps'+idEtp);
            all_module_etps.html('');

            $.each(res.mdls, function (key, val) { 
                 all_module_etps.append(`<details class="w-full flex flex-col group/detail4">
                                            <summary
                                                onclick="getProjectAll(`+year+`, `+idEtp+`, `+val.idModule+`)"
                                                class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-9">
                                                <div class="w-6 h-6 flex items-center justify-center">
                                                <i
                                                    class="fa-solid fa-chevron-right group-open/detail4:rotate-90 transition-all duration-200 text-gray-300"></i>
                                                </div>
                                                <div class="w-6 h-6 flex items-center justify-center mx-1">
                                                <i class="fa-regular fa-folder text-gray-300"></i>
                                                </div>
                                                <div class="h-6 text-md font-normal text-gray-500">`+val.module_name+`</div>
                                            </summary>
                                            <div id="all_project`+year+"_"+idEtp+"_"+val.idModule+`"></div>
                                        </details>`);
            });
        }
    });
}

function getProjects(idEtp, idModule){
    $.ajax({
        type: "get",
        url: "/cfp/sidebar/getProjects/"+idEtp+"/"+idModule,
        dataType: "json",
        success: function (res) {

            if(res.projetB.length > 0){
                var draft_project = $('#draft_project'+idEtp+"_"+idModule);
                draft_project.html('');

                $.each(res.projetB, function (k, v) { 
                    draft_project.append(`<div>
                                            <a href="#"
                                                class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-9 hover:text-inherit focus-within:bg-cyan-200 focus-within:text-cyan-600">
                                                <div class="w-6 h-6 flex items-center justify-center mx-1">
                                                    <i class="fa-regular fa-file-lines text-gray-500"></i>
                                                </div>
                                                <div class="h-6 text-md font-normal text-gray-500">`+v.project_name+`</div>
                                            </a>
                                        </div>`);
                });
            }

            if(res.projetE.length > 0){
                var current_project = $('#current_project'+idEtp+"_"+idModule);
                current_project.html('');

                $.each(res.projetE, function (k, v) { 
                     current_project.append(`<div>
                                                <a href="#"
                                                    class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-9 hover:text-inherit focus-within:bg-cyan-200 focus-within:text-cyan-600">
                                                    <div class="w-6 h-6 flex items-center justify-center mx-1">
                                                        <i class="fa-regular fa-file-lines text-gray-500"></i>
                                                    </div>
                                                    <div class="h-6 text-md font-normal text-gray-500">`+v.project_name+`</div>
                                                </a>
                                            </div>`);
                });
            }

            if(res.projetP.length > 0){
                var coming_project = $('#coming_project'+idEtp+"_"+idModule);
                coming_project.html('');

                $.each(res.projetP, function (k, v) { 
                    coming_project.append(`<div>
                                                <a href="#"
                                                    class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-9 hover:text-inherit focus-within:bg-cyan-200 focus-within:text-cyan-600">
                                                    <div class="w-6 h-6 flex items-center justify-center mx-1">
                                                        <i class="fa-regular fa-file-lines text-gray-500"></i>
                                                    </div>
                                                    <div class="h-6 text-md font-normal text-gray-500">`+v.project_name+`</div>
                                                </a>
                                            </div>`);
                });
            }

            if(res.projetT.length > 0){
                var finished_project = $('#finished_project'+idEtp+"_"+idModule);
                finished_project.html('');

                $.each(res.projetT, function (k, v) { 
                    finished_project.append(`<div>
                                                <a href="#"
                                                    class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-9 hover:text-inherit focus-within:bg-cyan-200 focus-within:text-cyan-600">
                                                    <div class="w-6 h-6 flex items-center justify-center mx-1">
                                                        <i class="fa-regular fa-file-lines text-gray-500"></i>
                                                    </div>
                                                    <div class="h-6 text-md font-normal text-gray-500">`+v.project_name+`</div>
                                                </a>
                                            </div>`);
                });
            }
        }
    });
}

function getProjectAll(year, idEtp, idModule){
    $.ajax({
        type: "get",
        url: "/cfp/sidebar/getProjectAll/"+year+"/"+idEtp+"/"+idModule,
        dataType: "json",
        success: function (res) {
            console.log(res);
            var all_project = $("#all_project"+year+"_"+idEtp+"_"+idModule);
            all_project.html('');

            $.each(res.projects, function (key, val) { 
                 all_project.append(`<div>
                                        <a href="#"
                                            class="hover:bg-gray-100 duration-300 cursor-pointer inline-flex items-center gap-0 w-full pl-12 hover:text-inherit focus-within:bg-cyan-200 focus-within:text-cyan-600">
                                            <div class="w-6 h-6 flex items-center justify-center mx-1">
                                            <i class="fa-regular fa-file-lines text-gray-500"></i>
                                            </div>
                                            <div class="h-6 text-md font-normal text-gray-500">`+val.project_name+`</div>
                                        </a>
                                    </div>`);
            });
        }
    });
}