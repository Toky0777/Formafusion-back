function initializeStepper() {

    loadProjet(0);

    document.getElementById('select-projet').addEventListener('change', function () {
        const idProjet = this.value;
        if (idProjet) {
            loadProjetApprenant(idProjet);
        }
    });

    let currentStep = 1;
    const totalSteps = 4;

    const steps = document.querySelectorAll(".step");
    const progressBar = document.getElementById("progress-bar");
    const prevBtn = document.getElementById("prevBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const nextBtn = document.getElementById("nextBtn");
    const submitBtn = document.getElementById("submitBtn");
    const stepLabels = document.querySelectorAll(".step-label");

    if (!steps.length || !progressBar || !prevBtn || !cancelBtn || !nextBtn || !submitBtn || !stepLabels.length) {
        console.warn("Éléments du stepper introuvables. Vérifiez que le HTML est bien chargé.");
        return;
    }

    cancelBtn.addEventListener("click", function () {
        const offcanvasElement = document.getElementById('offcanvasAddAttestation');
        if (offcanvasElement) {
            const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
            bsOffcanvas.hide();
        }
    });

    nextBtn.addEventListener("click", function () {
        if (isFormValid(currentStep)) {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep, steps, progressBar, prevBtn, cancelBtn, nextBtn, submitBtn,
                    stepLabels);
            }
        }
    });

    prevBtn.addEventListener("click", function () {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep, steps, progressBar, prevBtn, cancelBtn, nextBtn, submitBtn, stepLabels);
        }
    });

    showStep(currentStep, steps, progressBar, prevBtn, cancelBtn, nextBtn, submitBtn, stepLabels);

    $("#stepper-form").on("submit", function (e) {
        e.preventDefault();

        const projet = $("#select-projet").val();
        const apprenant = $("#select-apprenant").val();
        const fichierInput = $("input[type='file']")[0];

        if (!projet || !apprenant || fichierInput.files.length === 0) {
            toastr.error("Tous les champs sont obligatoires !", "Erreur", {
                timeOut: 3000
            });
            return;
        }

        const formData = new FormData();
        formData.append("projet", projet);
        formData.append("apprenant", apprenant);
        formData.append("fichier", fichierInput.files[0]);

        $.ajax({
            url: "/cfp/attestation",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message, "Succès", {
                        timeOut: 1500
                    });
                    $("#stepper-form")[0].reset();
                    window.location.reload();
                } else {
                    const errorMessages = Object.values(response.error);
                    toastr.error(errorMessages, "Erreur", {
                        timeOut: 3000
                    });
                }
            },
        });
    });


}

function isFormValid(step) {
    const inputs = document.querySelectorAll(`#step-${step} input, #step-${step} select`);
    let isValid = true;
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    inputs.forEach(input => {
        const existingError = input.parentElement.querySelector('.error-message');

        // Supprime le message d'erreur existant s'il y en a un
        if (existingError) {
            existingError.remove();
        }
        input.classList.remove('!border-red-500');

        if (!input.value) {
            isValid = false;
            input.classList.add('!border-red-500');

            const errorDiv = document.createElement('div');
            errorDiv.classList.add('text-red-500', 'text-sm', 'mt-1', 'error-message');
            errorDiv.innerHTML =
                '<i class="mr-1 fa-solid fa-triangle-exclamation"></i>Ce champ est obligatoire.';
            input.parentElement.appendChild(errorDiv);
        } else {
            // Vérification spécifique pour le champ fichier
            if (input.type === 'file') {
                const file = input.files[0];
                if (file) {
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    if (!imageExtensions.includes(fileExtension)) {
                        isValid = false;
                        input.classList.add('!border-red-500');

                        const errorDiv = document.createElement('div');
                        errorDiv.classList.add('text-red-500', 'text-sm', 'mt-1', 'error-message');
                        errorDiv.innerHTML =
                            '<i class="mr-1 fa-solid fa-triangle-exclamation"></i>Le fichier doit être une image (jpg, jpeg, png, gif, webp).';
                        input.parentElement.appendChild(errorDiv);
                    }
                }
            }
        }
    });

    return isValid;
}

function showStep(step, steps, progressBar, prevBtn, cancelBtn, nextBtn, submitBtn, stepLabels) {
    steps.forEach((el, index) => el.classList.toggle("hidden", index !== step - 1));
    updateProgressBar(step, progressBar, stepLabels);
    updateButtons(step, prevBtn, cancelBtn, nextBtn, submitBtn, steps.length);
}

function updateProgressBar(step, progressBar, stepLabels) {
    const progress = ((step - 1) / (stepLabels.length - 1)) * 100;
    progressBar.style.width = `${progress}%`;

    stepLabels.forEach((label, index) => {
        const stepNumber = label.querySelector("span");

        if (index < step) {
            label.classList.add("opacity-100", "font-semibold", "bg-[#A462A4]", "text-white");
            label.classList.remove("opacity-50", "bg-gray-200", "text-gray-600");
            stepNumber.classList.add("text-white");
            stepNumber.classList.remove("text-gray-600");
        }
        else {
            label.classList.add("opacity-50", "bg-gray-200", "text-gray-600");
            label.classList.remove("opacity-100", "font-semibold", "bg-[#A462A4]", "text-white");
            stepNumber.classList.add("text-gray-600");
            stepNumber.classList.remove("text-white");
        }
    });
}

function updateButtons(step, prevBtn, cancelBtn, nextBtn, submitBtn, totalSteps) {
    prevBtn.classList.toggle("hidden", step === 1);
    nextBtn.classList.toggle("hidden", step === totalSteps);
    submitBtn.classList.toggle("hidden", step !== totalSteps);
    cancelBtn.classList.toggle("hidden", step !== 1);

    if (step === totalSteps) {
        nextBtn.classList.add("bg-[#8B4E8E]", "hover:bg-[#A462A4]");
        nextBtn.classList.remove("bg-[#A462A4]", "hover:bg-[#8B4E8E]");
        submitBtn.classList.add("bg-[#A462A4]", "hover:bg-[#8B4E8E]");
        submitBtn.classList.remove("bg-[#8B4E8E]", "hover:bg-[#A462A4]");
    } else {
        nextBtn.classList.add("bg-[#A462A4]", "hover:bg-[#8B4E8E]");
        nextBtn.classList.remove("bg-[#8B4E8E]", "hover:bg-[#A462A4]");
        submitBtn.classList.remove("bg-[#A462A4]", "hover:bg-[#8B4E8E]");
    }
}


function loadProjet(idProjet) {
    fetch('/cfp/attestation/getProjet')
        .then(response => response.json())
        .then(data => {
            if (idProjet == 0) {
                const selectProjet = document.getElementById('select-projet');
                selectProjet.innerHTML =
                    '<option value="" disabled selected>Choisir un projet</option>';

                data.forEach(projet => {
                    const option = document.createElement('option');
                    option.value = projet.idProjet;
                    option.textContent = projet.project_reference + '  --  ' + projet.module_name +
                        '  --  ' + projet.dateDebut + '  au  ' + projet.dateFin;
                    selectProjet.appendChild(option);
                });
            } else {
                const selectProjet = document.getElementById('select-projet');
                selectProjet.innerHTML =
                    '<option value="" disabled>Choisir un projet</option>';

                data.forEach(projet => {
                    const option = document.createElement('option');
                    option.value = projet.idProjet;
                    option.textContent = projet.project_reference + '  --  ' + projet.module_name +
                        '  --  ' + projet.dateDebut + '  au  ' + projet.dateFin;
                    if (projet.idProjet == idProjet) {
                        option.selected = true;
                    }
                    selectProjet.appendChild(option);
                });
            }

        })
        .catch(error => console.error('Erreur lors du chargement des projets:', error));
}

function loadProjetApprenant(idProjet) {
    fetch(`/cfp/attestation/${idProjet}/getApprenant`)
        .then(response => response.json())
        .then(data => {
            const selectProjet = document.getElementById('select-apprenant');
            selectProjet.innerHTML = '';

            if (data.length === 0) {
                selectProjet.innerHTML = '<option value="" disabled selected>Aucun apprenant disponible dans ce projet</option>';
                return;
            }

            selectProjet.innerHTML = '<option value="" disabled selected>Choisir un apprenant</option>';

            data.forEach(apprenant => {
                const option = document.createElement('option');
                option.value = apprenant.idEmploye;
                if (apprenant.has_attestation == 1) {
                    option.textContent = `${apprenant.emp_name} ${apprenant.emp_firstname} | N° Att : ${apprenant.number_attestation}`;
                    option.disabled = true;
                } else {
                    option.textContent = `${apprenant.emp_name} ${apprenant.emp_firstname}`;
                }

                selectProjet.appendChild(option);
            });
        })
        .catch(error => console.error('Erreur lors du chargement des projets:', error));
}