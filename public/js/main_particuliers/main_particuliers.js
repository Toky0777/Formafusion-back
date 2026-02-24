function storeParticulier() {
    var name = $('.part_name');
    var firstname = $('.part_firstname');
    var email = $('.part_email');
    var cin = $('.part_cin');

    $.ajax({
        type: "post",
        url: "/particulier",
        data: {

            part_name: name.val(),
            part_firstname: firstname.val(),
            part_email: email.val(),
            part_cin: cin.val(),
        },
        dataType: "json",
        beforeSend: function () {
            $('.main_loading_part').append(`<div id="main_img_loading_part" class="spinner-grow text-primary" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>`);
        },
        complete: function () {
            $('#main_img_loading_part').remove();
        },
        success: function (res) {
            if (res.success) {
                toastr.success(res.success, 'Succès', {
                    timeOut: 1600
                });
                name.val('');
                firstname.val('');
                email.val('');
                cin.val('');
                window.location.replace("/particulier");
            } else if (res.error) {
                toastr.error("Erreur !", 'Erreur', {
                    timeOut: 1600
                });
                // console.log(res.error);
            }
        }
    });
}