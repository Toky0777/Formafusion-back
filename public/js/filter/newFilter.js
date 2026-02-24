$(document).ready(function () {
  const classBtnDrop =
    `w-full input input-bordered hover:bg-slate-200 hover:border-slate-400 hover:text-slate-700 cursor-pointer duration-200 text-slate-500 flex items-center justify-between`;
  $('.btnDrop').addClass(classBtnDrop);
  const classBtnDropSelected =
    `input input-bordered bg-slate-700 hover:bg-slate-900 text-white hover:border-slate-900 cursor-pointer duration-200 rounded-l-md border-slate-700 flex items-center justify-between`;
  $('.btnDropSelected').addClass(classBtnDropSelected);
  const classBtnDropSelectedIcon =
    `input input-bordered bg-slate-700 hover:bg-slate-900 text-white hover:border-slate-900 cursor-pointer duration-200 rounded-r-md border-slate-700 flex items-center justify-center`;
  $('.btnDropSelectedIcon').addClass(classBtnDropSelectedIcon);
  const
    inputSearchClass =
      `input input-bordered w-full text-slate-600`;
  $('.inputSearch').addClass(inputSearchClass);
});


function searchInput(idBtnDrop) {
  var value = $('#input-' + idBtnDrop).val().toLowerCase();
  $("#list-" + idBtnDrop + " li").filter(function () {
    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -
      1)
  });
}

// function refreshList(classCheckbox, id, nb) {
//   let array = $('.' + classCheckbox);
//   for (let i = 0; i < array.length; i++) {
//     array[i].checked = false;
//     nb = 0;
//     $('.countSelected_' + id).text(nb);
//   }
// }