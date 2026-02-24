const active = "bg-white font-semibold rounded-xl shadow-sm";
const activeInter = 'bg-pink-600 !text-white font-semibold rounded-xl shadow-sm';
const activeIntra = 'bg-lime-600 !text-white font-semibold rounded-xl shadow-sm';
// Pour activer le premier élément
// $(".tab-slider--body").hide();
$(".tab-slider--body").hide();

if (localStorage.getItem('active') === 'offlineModule') {
  // Pour activer le deuxième élément
  $(".tab-slider--body:eq(1)").show(); // Sélectionne le deuxième élément
  $(".tab-slider--nav li:eq(1)").addClass(active); // Ajoute la classe active au deuxième élément
} else {
  if ($(".tab-slider--nav li").attr("id") == 'tab_intra') {
    $(".tab-slider--body:first").show();
    $(".tab-slider--nav li:first").addClass(activeIntra);
  } else if ($(".tab-slider--nav li").attr("id") == 'tab_inter') {
    $(".tab-slider--body:first").show();
    $(".tab-slider--nav li:first").addClass(activeInter);
  } else {
    $(".tab-slider--body:first").show();
    $(".tab-slider--nav li:first").addClass(active);
  }
}

$(".tab-slider--nav li").click(function () {
  $(".tab-slider--body").hide();

  var activeTab = $(this).attr("rel");
  $("#" + activeTab).fadeIn();
  $('.tab-slider--body').each(function () {
    var id = $(this).attr('id');
    // console.log(id);
  });
  if ($(this).attr("rel") == $('tab-slider--body').attr("id")) {
    // console.log(activeTab);
    $('.tab-slider--tabs').addClass('slide');
  } else {
    $('.tab-slider--tabs').removeClass('slide');
  }

  // if ($(this).attr("rel") == $('tab-slider--body--create-project').attr("id")) {
  //   $('.tab-slider--tabs').addClass('slide');
  // } else {
  //   $('.tab-slider--tabs').removeClass('slide');
  // }

  if ($(this).attr("id") == 'tab_intra') {
    $(".tab-slider--nav li").removeClass(activeIntra);
    $(".tab-slider--nav li").removeClass(activeInter);
    $(".tab-slider--nav li").removeClass(active);
    $(this).addClass(activeIntra);
  } else if ($(this).attr("id") == 'tab_inter') {
    $(".tab-slider--nav li").removeClass(activeInter);
    $(".tab-slider--nav li").removeClass(activeIntra);
    $(".tab-slider--nav li").removeClass(active);
    $(this).addClass(activeInter);
  } else {
    // $(".tab-slider--nav li").removeClass(actactiveInterive);
    // $(".tab-slider--nav li").removeClass(activeIntra);
    $(".tab-slider--nav li").removeClass(active);
    $(this).addClass(active);
  }
});
// FIN TAB