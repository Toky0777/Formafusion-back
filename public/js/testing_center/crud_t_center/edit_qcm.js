// Function to add a new response field
function addResponseField(questionCard, responseId = null) {
  const responsesContainer = questionCard.querySelector(".responses-container");
  const questionId = questionCard.dataset.questionId;

  // Récupérer les catégories depuis un élément data existant
  const categoriesSelect = document.querySelector(
    "select[name*='[categorie_id]']"
  );
  let categoriesOptions = "";

  if (categoriesSelect) {
    // Cloner les options existantes d'un select déjà présent sur la page
    const options = categoriesSelect.querySelectorAll("option");
    options.forEach((option) => {
      categoriesOptions += `<option value="${option.value}">${option.textContent}</option>`;
    });
  }

  const newResponseHtml = `
                        <div class="bg-white rounded-lg p-1 response-group" ${
                          responseId ? `data-response-id="${responseId}"` : ""
                        }>
                            <div class="flex space-x-4 items-center">
                                <!-- Bouton radio désactivé -->
                                <div class="flex-shrink-0">
                                    <input type="radio" disabled
                                        class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                </div>
                                <div class="flex-1">
                                    <input type="text" 
                                        name="questions[${questionId}][responses][${
    responseId || "new_" + Date.now()
  }][texteReponse]" 
                                        class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                        placeholder="Texte de la réponse">
                                </div>
                                <div class="flex-1">
                                    <input type="text" 
                                        name="questions[${questionId}][responses][${
    responseId || "new_" + Date.now()
  }][explicationReponse]" 
                                        class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                        placeholder="Explication Réponse">
                                </div>
                                <div class="flex-1 flex items-center">
                                    <input type="number" 
                                        name="questions[${questionId}][responses][${
    responseId || "new_" + Date.now()
  }][points]" 
                                        class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                        placeholder="Points">
                                    <span class="text-sm text-gray-700 ml-2">Points</span>
                                </div>
                                <div class="flex-1">
                                    <select name="questions[${questionId}][responses][${
    responseId || "new_" + Date.now()
  }][categorie_id]" 
                                        class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        ${categoriesOptions}
                                    </select>
                                </div>
                                <div class="flex items-center">
                                    <!-- Bouton pour supprimer une reponse avec l'icône de la croix -->
                                    <button type="button" class="delete-response">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="w-4 h-4">
                                            <path
                                                d="M376.6 84.5c11.3-13.6 9.5-33.8-4.1-45.1s-33.8-9.5-45.1 4.1L192 206 56.6 43.5C45.3 29.9 25.1 28.1 11.5 39.4S-3.9 70.9 7.4 84.5L150.3 256 7.4 427.5c-11.3 13.6 9.5 33.8 4.1 45.1s33.8 9.5 45.1-4.1L192 306 327.4 468.5c11.3 13.6 31.5 15.4 45.1 4.1s15.4-31.5 4.1-45.1L233.7 256 376.6 84.5z"
                                                fill="#64748b" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
  responsesContainer.insertAdjacentHTML("beforeend", newResponseHtml);
}

// Fonction pour gérer le téléchargement d'images
function handleImageUpload() {
  document.addEventListener("change", function (event) {
    const target = event.target;

    if (target.classList.contains("image-upload-input")) {
      const questionId = target.dataset.questionId;
      const file = target.files[0];

      if (file) {
        const formData = new FormData();
        formData.append("myFile[]", file);

        // Afficher un indicateur de chargement
        const uploadLabel = target.closest(".image-upload-label");
        uploadLabel.innerHTML =
          '<div class="animate-spin rounded-full h-6 w-6 border-t-2 border-b-2 border-gray-900"></div>';

        // Effectuer la requête AJAX pour télécharger l'image
        fetch(`/qcm/question/${questionId}/upload-image`, {
          method: "POST",
          body: formData,
          headers: {
            "X-CSRF-TOKEN": document
              .querySelector('meta[name="csrf-token"]')
              .getAttribute("content"),
          },
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // Créer le conteneur d'aperçu d'image avec le bouton de suppression
              const imagePreviewContainer = document.createElement("div");
              imagePreviewContainer.className =
                "image-preview-container relative";
              imagePreviewContainer.setAttribute(
                "data-question-id",
                questionId
              );
              imagePreviewContainer.setAttribute("data-image-id", data.imageId);

              // Structure corrigée avec position relative et boutons en absolute
              imagePreviewContainer.innerHTML = `
                  <img src="${data.urls[0]}" alt="Image question" class="w-12 h-12 object-cover rounded">
                  <button type="button" class="delete-image-btn absolute top-0 right-0 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                  </button>
                  <button type="button" class="change-image-btn absolute bottom-0 right-0 bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                      </svg>
                  </button>
                  <input type="file" id="change-image-input-${questionId}" class="hidden change-image-input" data-question-id="${questionId}">
                `;

              // Remplacer le label d'upload par l'aperçu de l'image
              uploadLabel.parentNode.replaceChild(
                imagePreviewContainer,
                uploadLabel
              );
            } else {
              alert(
                data.error ||
                  "Une erreur est survenue lors du téléchargement de l'image."
              );
              uploadLabel.innerHTML = `
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  <input id="image-upload-${questionId}" type="file" class="hidden image-upload-input" data-question-id="${questionId}">
                `;
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("Une erreur est survenue lors du téléchargement de l'image.");
            uploadLabel.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <input id="image-upload-${questionId}" type="file" class="hidden image-upload-input" data-question-id="${questionId}">
              `;
          });
      }
    }

    // Gestion du changement d'image
    if (target.classList.contains("change-image-input")) {
      const questionId = target.dataset.questionId;
      const file = target.files[0];
      const imageContainer = target.closest(".image-preview-container");
      const imageId = imageContainer.dataset.imageId;

      if (file) {
        const formData = new FormData();
        formData.append("myFile[]", file);

        // Afficher un indicateur de chargement
        const img = imageContainer.querySelector("img");
        img.style.opacity = "0.5";

        // Ajouter un spinner à côté de l'image - CORRECTION ICI
        const spinner = document.createElement("div");
        spinner.className =
          "animate-spin rounded-full h-6 w-6 border-t-2 border-b-2 border-gray-900 absolute top-3 left-3";
        imageContainer.appendChild(spinner);

        // Effectuer la requête AJAX pour remplacer l'image
        fetch(`/qcm/question-image/${imageId}/replace`, {
          method: "POST",
          body: formData,
          headers: {
            "X-CSRF-TOKEN": document
              .querySelector('meta[name="csrf-token"]')
              .getAttribute("content"),
          },
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // Mise à jour de l'image
              img.src = data.urls[0];
              img.style.opacity = "1";
              spinner.remove();
            } else {
              alert(
                data.error ||
                  "Une erreur est survenue lors du remplacement de l'image."
              );
              img.style.opacity = "1";
              spinner.remove();
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("Une erreur est survenue lors du remplacement de l'image.");
            img.style.opacity = "1";
            spinner.remove();
          });
      }
    }
  });
}

// Fonction pour gérer la suppression d'images
function handleImageDeletion() {
  // Utiliser la délégation d'événements sur le document plutôt que d'attacher des gestionnaires aux éléments individuels
  document.addEventListener("click", function (event) {
    // Cette fonction est maintenant appelée pour chaque clic sur le document
    const target = event.target;

    // Vérifier si le clic est sur un bouton de suppression d'image ou à l'intérieur de celui-ci
    if (target.closest(".delete-image-btn")) {
      const imageContainer = target.closest(".image-preview-container");
      const questionId = imageContainer.dataset.questionId;
      const imageId = imageContainer.dataset.imageId;

      if (confirm("Êtes-vous sûr de vouloir supprimer cette image ?")) {
        // Effectuer la requête AJAX pour supprimer l'image
        fetch(`/qcm/question-image/${imageId}/delete`, {
          method: "DELETE",
          headers: {
            "X-CSRF-TOKEN": document
              .querySelector('meta[name="csrf-token"]')
              .getAttribute("content"),
            "Content-Type": "application/json",
          },
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // Créer le label d'upload pour remplacer l'aperçu de l'image
              const uploadLabel = document.createElement("label");
              uploadLabel.className =
                "image-upload-label cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-600 p-2 rounded-md flex items-center justify-center w-12 h-12 transition";
              uploadLabel.setAttribute("for", `image-upload-${questionId}`);

              uploadLabel.innerHTML = `
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  <input id="image-upload-${questionId}" type="file" class="hidden image-upload-input" data-question-id="${questionId}">
                `;

              // Remplacer l'aperçu de l'image par le label d'upload
              imageContainer.parentNode.replaceChild(
                uploadLabel,
                imageContainer
              );
            } else {
              alert(
                data.error ||
                  "Une erreur est survenue lors de la suppression de l'image."
              );
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("Une erreur est survenue lors de la suppression de l'image.");
          });
      }
    }

    // Gestion du clic sur le bouton de modification d'image
    if (target.closest(".change-image-btn")) {
      const imageContainer = target.closest(".image-preview-container");
      const questionId = imageContainer.dataset.questionId;
      const changeImageInput = imageContainer.querySelector(
        `#change-image-input-${questionId}`
      );

      // Déclencher le clic sur l'input file caché
      changeImageInput.click();
    }
  });
}

// Fonction pour ajouter une image aux nouvelles questions
function addImageUploadToNewQuestion(questionCard) {
  const questionId = questionCard.dataset.questionId;
  const questionHeader = questionCard.querySelector(
    ".flex.items-center.gap-4.mb-4"
  );

  // Créer la section d'image
  const imageSection = document.createElement("div");
  imageSection.className = "question-image-section flex-shrink-0";

  // Ajouter le label d'upload
  imageSection.innerHTML = `
                <label for="image-upload-${questionId}" class="image-upload-label cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-600 p-2 rounded-md flex items-center justify-center w-12 h-12 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <input id="image-upload-${questionId}" type="file" class="hidden image-upload-input" data-question-id="${questionId}">
                </label>
            `;

  // Insérer avant le bouton de suppression
  const deleteButton = questionHeader.querySelector(".delete-question");
  questionHeader.insertBefore(imageSection, deleteButton);
}

// Function to add a new question
function addNewQuestion() {
  const questionsContainer = document.getElementById("questions-container");
  const newQuestionId = "new_" + Date.now();
  const questionCount = document.querySelectorAll(".question-card").length + 1;

  // Récupérer les catégories depuis un élément data existant
  const categoriesSelect = document.querySelector(
    "select[name*='[categorie_id]']"
  );
  let categoriesOptions = "";

  if (categoriesSelect) {
    // Cloner les options existantes d'un select déjà présent sur la page
    const options = categoriesSelect.querySelectorAll("option");
    options.forEach((option) => {
      categoriesOptions += `<option value="${option.value}">${option.textContent}</option>`;
    });
  }

  const newQuestionHtml = `
      <div class="bg-white shadow-md rounded-lg p-6 question-card" data-question-id="${newQuestionId}">
        <div class="flex items-center gap-4 mb-4">
          <!-- Titre -->
          <h5 class="text-lg font-bold flex-shrink-0">Q${questionCount} :</h5>
  
          <!-- Input -->
          <input type="text" id="questions[${newQuestionId}][texteQuestion]"
            name="questions[${newQuestionId}][texteQuestion]"
            placeholder="Texte de la question"
            class="flex-1 block rounded-md border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
  
          <!-- Section pour l'image de la question -->
          <div class="question-image-section flex-shrink-0">
            <label for="image-upload-${newQuestionId}" class="image-upload-label cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-600 p-2 rounded-md flex items-center justify-center w-12 h-12 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <input id="image-upload-${newQuestionId}" type="file" class="hidden image-upload-input" data-question-id="${newQuestionId}">
            </label>
          </div>
  
          <!-- Bouton -->
          <button type="button" class="delete-question flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-4 h-4" fill="#64748b">
              <path d="M135.2 17.7C140.6 6.8 151.7 0 163.8 0L284.2 0c12.1 0 23.2 6.8 28.6 17.7L320 32l96 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 96C14.3 96 0 81.7 0 64S14.3 32 32 32l96 0 7.2-14.3zM32 128l384 0 0 320c0 35.3-28.7 64-64 64L96 512c-35.3 0-64-28.7-64-64l0-320zm96 64c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16z" />
            </svg>
          </button>
        </div>
  
        <div class="space-y-4">
          <div class="responses-container space-y-4"></div>
  
          <!-- Ajouter une réponse -->
          <span class="text-[#c7c7ce] font-bold cursor-pointer mt-5 add-response">Ajouter une réponse</span>
        </div>
      </div>
    `;

  questionsContainer.insertAdjacentHTML("beforeend", newQuestionHtml);
}

// Event delegation for dynamically added elements
document.addEventListener("click", function (event) {
  const target = event.target;

  if (
    target.classList.contains("add-response") ||
    target.closest(".add-response")
  ) {
    const questionCard = target.closest(".question-card");
    addResponseField(questionCard);
  } else if (target.closest(".delete-response")) {
    const responseGroup = target.closest(".response-group");
    if (responseGroup) {
      responseGroup.remove();
    }
  } else if (target.closest(".delete-question")) {
    const questionCard = target.closest(".question-card");
    if (questionCard) {
      questionCard.remove();
    }
  }
});

// Initialiser les gestionnaires d'événements lors du chargement de la page
document.addEventListener("DOMContentLoaded", function () {
  // Initialiser une seule fois chaque gestionnaire au démarrage
  handleImageUpload();
  handleImageDeletion();
});

// Add new question button
document
  .getElementById("add-question")
  .addEventListener("click", addNewQuestion);

// Switch section function
function switchSection(section) {
  const generalSection = document.getElementById("generalSection");
  const qaSection = document.getElementById("qaSection");
  const generalButton = document.getElementById("switchToGeneral");
  const qaButton = document.getElementById("switchToQA");

  // Reset all buttons
  generalButton.classList.remove("active");
  qaButton.classList.remove("active");

  // Update sections and buttons
  if (section === "general") {
    generalSection.style.display = "block";
    qaSection.style.display = "none";
    generalButton.classList.add("active");
  } else {
    generalSection.style.display = "none";
    qaSection.style.display = "block";
    qaButton.classList.add("active");
  }
}

switchSection("general");
