// Sélectionnez les éléments des boutons
var cheque = document.getElementById("cheque");
var virement = document.getElementById("virement");
var enLigne = document.getElementById("enLigne")
var contentCheque = document.getElementById('contentCheque');
var contentVirementbni = document.getElementById('contentVirementbni');
var contentpaymentEnligne = document.getElementById('paymentEnligne')
var selectVirement = document.getElementById('selectVirement');
var bni = document.getElementById('bni');

// Ajoutez un écouteur d'événements "click" pour chaque bouton
cheque.addEventListener("click", function () {
  cheque.classList.add("bg-[#a462a4]"); 
  cheque.classList.add("text-white"); 
  cheque.classList.add("scale-100"); 
  contentCheque.classList.remove("hidden")

  cheque.classList.remove("bg-white"); 
  cheque.classList.remove("text-gray-700"); 

  virement.classList.remove("bg-[#a462a4]");
  virement.classList.remove("text-white");
  virement.classList.remove("scale-100");
  enLigne.classList.remove("bg-[#a462a4]");
  enLigne.classList.remove("text-white");
  enLigne.classList.remove("scale-100");
  contentVirement.classList.add("hidden")
  contentpaymentEnligne.classList.add("hidden")

  virement.classList.add("bg-white");
  virement.classList.add("text-gray-700");
  enLigne.classList.add("bg-white");
  enLigne.classList.add("text-gray-700");
});

virement.addEventListener("click", function () {
  virement.classList.add("bg-[#a462a4]"); 
  virement.classList.add("text-white"); 
  virement.classList.add("scale-100"); 
  // selectVirement.classList.remove("hidden")
  contentVirement.classList.remove("hidden");
  contentCheque.classList.add("hidden");
  contentpaymentEnligne.classList.add("hidden");

  virement.classList.remove("bg-white"); 
  virement.classList.remove("text-gray-700"); 

  cheque.classList.remove("bg-[#a462a4]");
  cheque.classList.remove("text-white");
  cheque.classList.remove("scale-100");
  enLigne.classList.remove("bg-[#a462a4]");
  enLigne.classList.remove("text-white");
  enLigne.classList.remove("scale-100");
  // contentCheque.classList.add("hidden")

  cheque.classList.add("bg-white");
  cheque.classList.add("text-gray-700");
  enLigne.classList.add("bg-white");
  enLigne.classList.add("text-gray-700");
});

enLigne.addEventListener("click", function () {
  enLigne.classList.add("bg-[#a462a4]"); 
  enLigne.classList.add("text-white"); 
  enLigne.classList.add("scale-100"); 
  contentpaymentEnligne.classList.remove("hidden");
  contentVirement.classList.add("hidden");
  contentCheque.classList.add("hidden");

  enLigne.classList.remove("bg-white"); 
  enLigne.classList.remove("text-gray-700"); 

  cheque.classList.remove("bg-[#a462a4]");
  cheque.classList.remove("text-white");
  cheque.classList.remove("scale-100");
  virement.classList.remove("bg-[#a462a4]");
  virement.classList.remove("text-white");
  virement.classList.remove("scale-100");

  cheque.classList.add("bg-white");
  cheque.classList.add("text-gray-700");
  virement.classList.add("bg-white");
  virement.classList.add("text-gray-700");
});