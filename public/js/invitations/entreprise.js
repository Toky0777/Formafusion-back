function mainGetName() {
    var name = $('#main-customer-name-search').val();

    if (name.length !== 0) {
        $.ajax({
            type: "get",
            url: "/cfp/invite-etp/" + name + "/name",
            dataType: "json",
            success: function (res) {
                $('.main-customer-name-append').html('');

                if (res.status == 404) {
                    $('.main-customer-name-append').append(`<button
                                                                type="button"
                                                                onclick="mainAddCustomer('`+ name + `')"
                                                                class="w-full py-1 flex justify-center items-center text-lg text-gray-400 border-[1px] border-gray-200 rounded-md gap-2 bg-gray-100">
                                                                <i class="fa-solid fa-plus"></i>
                                                                Ajouter un client
                                                            </button>`);
                } else if (res.status == 200) {
                    $.each(res.customers, function (key, val) {
                        $('.main-customer-name-append').append(`<div class="flex flex-col w-full gap-1">
                                                                    <div class="card mb-2">
                                                                        <div class="card-body">
                                                                            <h5 class="card-title">`+ val.customer_name + `</h5>
                                                                            <div class="row">
                                                                                <div class="col-4">
                                                                                    <p class="card-text">`+ val.customer_email + `</p>
                                                                                    <p class="card-text">Type: `+ val.customer_type_desc + `</p>
                                                                                </div>
                                                                                <div class="col-5">
                                                                                    <p class="card-text">NIF: ${val.customer_nif != null ? val.customer_nif : '--'}</p>
                                                                                    <p class="card-text">Adresse: ${val.customer_addr_lot != null ? val.customer_addr_lot : '--'}</p>
                                                                                </div>
                                                                                <div class="col-3">
                                                                                    ${
                                                                                        val.customer_in_collaboration
                                                                                        ? 
                                                                                        `<i class="text-sm hidden xl:block fa-sharp fa-solid fa-city" style="color: green; font-size: 30px"></i>`
                                                                                        :
                                                                                        `<button onclick="mainInviteCustomer(`+val.idTypeCustomer+`, `+val.idCustomer+`)" type="button" class="btn btn-primary main_loading_send`+val.idCustomer+`" style="color: #000">Inviter</button>`
                                                                                    }
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>`);
                    });
                }
            }
        });
    } else {
        $('.main-customer-name-append').html('');
    }
}

function mainAddCustomer(name) {
    const form_control = `bg-white w-full bg-transparent pl-2 h-10 border-[1px] border-gray-200 rounded-md hover:border-purple-300 focus:border-purple-300 focus:ring-2 focus:ring-purple-100 duration-200 text-gray-400`;
    $('.main-customer-name-append').html('');
    $('.main-customer-name-append').append(`<span class="main_loading_send"></span>
                                            <div class="mb-3">
                                                <span class="inline-flex items-center gap-2">
                                                    <div class="">
                                                        <input onclick='getCustomerType()' class="form-check-input customer-radio" type="radio" name="idTypeCustomer" id="radio-particulier" value="3">
                                                        <label class="form-check-label" for="radio-particulier" style="cursor: pointer">
                                                            Particulier
                                                        </label>
                                                    </div>
                                                    <div class="">
                                                        <input onclick='getCustomerType()' class="form-check-input customer-radio" type="radio" name="idTypeCustomer" id="radio-entreprise" value="2" checked>
                                                        <label class="form-check-label" for="radio-entreprise" style="cursor: pointer">
                                                            Entreprise
                                                        </label>
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="flex flex-col gap-1 w-full">
                                                <label for="etp_name" class="text-gray-600 after:content-['*'] after:ml-0.5 after:text-red-500">Nom du client</label>
                                                <input type="text" class="`+ form_control + ` etp_name" value="` + name + `">
                                                <div id="error_etp_name" class="text-sm text-red-500"></div>
                                            </div>
                                            <div class="flex flex-col gap-1 w-full">
                                                <label for="main_etp_email" class="text-gray-600 after:content-['*'] after:ml-0.5 after:text-red-500">E-mail</label>
                                                <input type="email" class="`+ form_control + ` etp_email">
                                                <div id="error_main_etp_email" class="text-sm text-red-500"></div>
                                            </div>
                                            <div class="w-full inline-flex justify-end pt-2 gap-2">
                                                <a data-bs-toggle="offcanvas" href="#offcanvasAddClient" class="hover:text-inherit btn">
                                                    Annuler
                                                </a>
                                                <button onclick="mainInviteNewCustomer()" type="button" class="btn btn-primary hover:!text-white">Ajouter ce client</button>
                                            </div>`);
}

function mainInviteCustomer(typeCustomer, idCustomer) {
    $.ajax({
        type: "post",
        url: "/cfp/invite-etp/store/"+typeCustomer+"/"+idCustomer,
        dataType: "json",
        beforeSend: function () {
            $('.main_loading_send'+idCustomer).append(`<div id="main_img_loading`+idCustomer+`" class="spinner-grow text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                  </div>`);
        },
        complete: function () {
            $('#main_img_loading'+idCustomer).remove();
        },
        success: function (res) {
            // console.log(res);
            mainGetName();

            if(res.status == 200){
                toastr.success(res.message, 'Succès', { timeOut: 1500 });
            }else{
                toastr.error("Invitation impossible", 'Erreur', { timeOut: 2000 });
            }
        }
    });
}

function mainInviteNewCustomer(){   
    $.ajax({
        type: "post",
        url: "/cfp/invite-etp/new-customer",
        data: {
            idTypeCustomer: getCustomerType(),
            customer_name: $('.etp_name').val(),
            customer_email: $('.etp_email').val()
        },
        dataType: "json",
        beforeSend: function () {
            $('.main_loading_send').append(`<div id="main_img_loading" class="spinner-grow text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                  </div>`);
        },
        complete: function () {
            $('#main_img_loading').remove();
        },
        success: function (res) {
            console.log(res);
            
            if(res.status == 200){
                toastr.success(res.message, 'Succès', { timeOut: 1500 });
                $('.etp_name').val('');
                $('.etp_email').val('');
                $('#main-customer-name-search').val('');
                location.reload();
            }else{
                toastr.error("Invitation impossible", 'Erreur', { timeOut: 2000 });
            }
        }
    });
}

function getCustomerType(){
    var customerRadio = $("input[name='idTypeCustomer']:checked").val();

    if(customerRadio){
        var idTypeCustomer = parseInt(customerRadio);
    }

    return idTypeCustomer;
}