var creer = document.getElementById('creer');
var inactiveEnvoyer = document.getElementById('inactiveEnvoyer');
var check = document.getElementById('check');
var activeEnvoyer = document.getElementById('activeEnvoyer');

var btnApprouver = document.getElementById('approuver');

btnApprouver.addEventListener("click", function () {
  setTimeout(() => {
    creer.classList.add('hidden');
    inactiveEnvoyer.classList.add('hidden');

    check.classList.remove('hidden');
    activeEnvoyer.classList.remove('hidden');
  }, 300);
})