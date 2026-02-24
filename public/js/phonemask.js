function phoneMask() {
  var num = $(this).val().replace(/\D/g, '');
  var formattedNum = '';
  var codePays = "+261"
  // Ajouter le code de pays (+261)
  formattedNum += '(' + codePays + ')';

  // Ajouter l'indicatif régional (34)
  if (num.length > 3) {
    formattedNum += ' ' + num.substring(3, 5);
  }

  // Ajouter le reste du numéro
  if (num.length > 5) {
    formattedNum += ' ' + num.substring(5, 7);
  }
  if (num.length > 7) {
    formattedNum += ' ' + num.substring(7, 10);
  }
  if (num.length > 10) {
    formattedNum += ' ' + num.substring(10, 12);
  }

  $(this).val(formattedNum);
}

$('[type="tel"]').keyup(phoneMask);

function cinMask() {
  var numCin = $(this).val().replace(/\D/g, '');
  var formattedCin = '';
  // Ajouter le code de pays (+261)
  formattedCin += numCin.substring(0, 3);

  // Ajouter l'indicatif régional (34)
  if (numCin.length > 3) {
    formattedCin += ' ' + numCin.substring(3, 6);
  }

  // Ajouter le reste du numCinéro
  if (numCin.length > 6) {
    formattedCin += ' ' + numCin.substring(6, 9);
  }
  if (numCin.length > 9) {
    formattedCin += ' ' + numCin.substring(9, 12);
  }

  $(this).val(formattedCin);
}

$('[type="cin"]').keyup(cinMask);



function setSelectionRange(input, selectionStart, selectionEnd) {
  if (input.setSelectionRange) {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  } else if (input.createTextRange) {
    var range = input.createTextRange();
    range.collapse(true);
    console.log(collapse);
    range.moveEnd('character', selectionEnd);
    range.moveStart('character', selectionStart);
    range.select();
  }
}

function setCaretToPos(input, pos) {
  setSelectionRange(input, pos, pos);
}


// $("#money").click(function () {

// });

var options = {
  onKeyPress: function (cep, e, field, options) {
    if (cep.length <= 6) {

      var inputVal = parseFloat(cep);
      jQuery('#money').val(inputVal.toFixed(2));
    }

    // setCaretToPos(jQuery('#money')[0], 4);

    var masks = ['# ##0.00', '0.00'];
    mask = (cep == 0) ? masks[1] : masks[0];
    $('#money').mask(mask, options);
  },
  reverse: true
};

function moneyClick(id) {
  var inputLength = $("#" + id).val().length;
  setCaretToPos($("#" + id)[0], inputLength)
  $('#' + id).mask('# ##0.00', options);
}



