$(document).ready(function () {
  $('.__drawerBtn').click(function (e) {
    e.preventDefault();
    $('.__drawer').addClass("w-96");
    $('.__drawer').removeClass('w-0');

    $('.content-drawer').removeClass('hidden');
    const id = $(this).attr('id');

    if (id == 'projet') {
      $('.section').hide();
      $('#projets').show();
      // const show = $('#projets').hasClass('hidden');
      const show = $("#projets").attr('style');
      if (show == 'display: block;') {
        console.log(show);
        $('.__drawerBtn').removeClass('bg-purple-100');
        $('.__drawerBtn i').removeClass('text-purple-500');

        $('#projet').removeClass('bg-white');
        $('#projet').addClass('bg-purple-100');
        $('#projet i').addClass('text-purple-500');
      }

    } else if (id == 'session') {
      $('.section').hide();
      $('#sessions').show();

      const show = $("#sessions").attr('style');
      if (show == 'display: block;') {
        console.log('SHOW ME!!!',show);
        $('.__drawerBtn').removeClass('bg-purple-100');
        $('.__drawerBtn i').removeClass('text-purple-500');

        $('#session').removeClass('bg-white');
        $('#session').addClass('bg-purple-100');
        $('#session i').addClass('text-purple-500');
      }
    } else {
      $('.section').hide();
      $('#guides').show();

      const show = $("#guides").attr('style');
      if (show == 'display: block;') {
        console.log(show);
        $('.__drawerBtn').removeClass('bg-purple-100');
        $('.__drawerBtn i').removeClass('text-purple-500');

        $('#guide').removeClass('bg-white');
        $('#guide').addClass('bg-purple-100');
        $('#guide i').addClass('text-purple-500');
      }
    }

    $('.content-drawer').ready(function () {
      $('.close').click(function (e) {
        e.preventDefault();
        $('.__drawer').addClass("w-0");
        $('.__drawer').removeClass('w-96');

        $('.__drawerBtn').removeClass('bg-purple-100');
        $('.__drawerBtn i').removeClass('text-purple-500');

        $('.__drawerBtn').addClass('bg-white');
        $('.__drawerBtn i').addClass('text-gray-400');
      });
    });
  });

  $('.btnMenu').click(function (e) {
    e.preventDefault();
    const currentID = $(this).attr('id');
    if (currentID && $('.menu-' + currentID).hasClass('hidden')) {
      $('.menu-' + currentID).removeClass('hidden');
    } else {
      $('.menu-' + currentID).addClass('hidden');
    }
  });

});

