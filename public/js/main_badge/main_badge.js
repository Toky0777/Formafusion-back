function initializeStepper(idModule) {

    // loadCatalogue(0);

    let currentStep = 1;
    const totalSteps = 3;

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
        const offcanvasElement = document.getElementById('offcanvasAddBadge');
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

        var formData = new FormData(this);
        $.ajax({
            url: `/cfp/badge/${idModule}`,
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


function loadCatalogue() {
    fetch('/cfp/badge/getCatalogue')
        .then(response => response.json())
        .then(data => {
            const selectProjet = document.getElementById('select-catalogue');
            data.forEach(catalogue => {
                selectProjet.innerText = catalogue.moduleName
            });
        })
        .catch(error => console.error('Erreur lors du chargement des catalogues:', error));
}
