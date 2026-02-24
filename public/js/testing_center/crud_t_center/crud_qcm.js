document.addEventListener("DOMContentLoaded", function () {
  let sectionCount = 0;
  const sectionsContainer = document.getElementById("sections-container");
  const addSectionBtn = document.getElementById("add-section");
  const infoButton = document.getElementById("info-button");
  const questionsButton = document.getElementById("questions-button");
  const infoSection = document.getElementById("qcm-info");

  // Navigation handlers
  infoButton.addEventListener("click", () => {
    infoSection.classList.remove("hidden");
    sectionsContainer.classList.add("hidden");
    addSectionBtn.classList.add("hidden");
    infoButton.classList.remove("bg-[#c7c7ce]", "text-gray-700");
    infoButton.classList.add("bg-[#a462a4]", "text-white");
    questionsButton.classList.remove("bg-[#a462a4]", "text-white");
    questionsButton.classList.add("bg-[#c7c7ce]", "text-gray-700");
  });

  questionsButton.addEventListener("click", () => {
    infoSection.classList.add("hidden");
    sectionsContainer.classList.remove("hidden");
    addSectionBtn.classList.remove("hidden");
    questionsButton.classList.remove("bg-[#c7c7ce]", "text-gray-700");
    questionsButton.classList.add("bg-[#a462a4]", "text-white");
    infoButton.classList.remove("bg-[#a462a4]", "text-white");
    infoButton.classList.add("bg-[#c7c7ce]", "text-gray-700");
  });

  // Debounce function
  function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
  }

  function createCategorySearchInput(sectionIndex) {
    const searchDiv = document.createElement("div");
    searchDiv.className = "relative";
    searchDiv.innerHTML = `
          <input 
              type="text" 
              name="sections[${sectionIndex}][categorie_search]" 
              placeholder="Rechercher une section" 
              class="category-search block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              autocomplete="off"
          >
          <input 
              type="hidden" 
              name="sections[${sectionIndex}][categorie_id]" 
              class="category-id" 
              required
          >
          <div class="category-suggestions absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg hidden">
              <ul class="suggestions-list max-h-60 overflow-y-auto"></ul>
              <div class="create-option p-2 bg-gray-50 border-t hover:bg-gray-100 cursor-pointer hidden">
                  <span class="text-blue-600">+ Créer "<span class="create-text"></span>"</span>
              </div>
          </div>
      `;

    const searchInput = searchDiv.querySelector(".category-search");
    const suggestionsContainer = searchDiv.querySelector(
      ".category-suggestions"
    );
    const suggestionsList = searchDiv.querySelector(".suggestions-list");
    const createOption = searchDiv.querySelector(".create-option");
    const createText = searchDiv.querySelector(".create-text");
    const categoryIdInput = searchDiv.querySelector(".category-id");

    searchInput.addEventListener(
      "input",
      debounce(function (e) {
        const query = e.target.value.trim();
        if (query.length < 1) {
          suggestionsContainer.classList.add("hidden");
          return;
        }

        axios
          .get("/ct-reponses/search", {
            params: {
              query: query,
            },
          })
          .then((response) => {
            suggestionsList.innerHTML = "";

            if (response.data.length > 0) {
              response.data.forEach((category) => {
                const li = document.createElement("li");
                li.className = "p-2 hover:bg-gray-100 cursor-pointer";
                li.textContent = category.nomCategorie;
                li.dataset.id = category.idCategorie;

                li.addEventListener("click", function () {
                  searchInput.value = category.nomCategorie;
                  categoryIdInput.value = category.idCategorie;
                  suggestionsContainer.classList.add("hidden");
                  updateCategoryIdInAnswers(sectionIndex, category.idCategorie);
                });

                suggestionsList.appendChild(li);
              });

              suggestionsContainer.classList.remove("hidden");
            }

            const exactMatch = response.data.some(
              (category) =>
                category.nomCategorie.toLowerCase() === query.toLowerCase()
            );

            if (!exactMatch) {
              createText.textContent = query;
              createOption.classList.remove("hidden");

              createOption.onclick = function () {
                axios
                  .post("/ct-reponses/create", {
                    nomCategorie: query,
                  })
                  .then((response) => {
                    searchInput.value = response.data.nomCategorie;
                    categoryIdInput.value = response.data.idCategorie;
                    suggestionsContainer.classList.add("hidden");
                    updateCategoryIdInAnswers(
                      sectionIndex,
                      response.data.idCategorie
                    );
                  })
                  .catch((error) => {
                    console.error(
                      "Erreur lors de la création:",
                      error.response.data
                    );
                    alert("Erreur lors de la création de la catégorie");
                  });
              };
            } else {
              createOption.classList.add("hidden");
            }
          })
          .catch((error) => {
            console.error("Erreur de recherche:", error);
          });
      }, 300)
    );

    document.addEventListener("click", function (e) {
      if (!searchDiv.contains(e.target)) {
        suggestionsContainer.classList.add("hidden");
      }
    });

    return searchDiv;
  }

  function updateCategoryIdInAnswers(sectionIndex, categoryId) {
    const section = document.querySelector(
      `[data-section-index="${sectionIndex}"]`
    );
    const categoryInputs = section.querySelectorAll(".category-id");
    categoryInputs.forEach((input) => {
      input.value = categoryId;
    });
  }

  function createSection() {
    const sectionIndex = sectionCount++;
    const sectionDiv = document.createElement("div");
    sectionDiv.className = "bg-white rounded-lg shadow-lg p-6 space-y-4";
    sectionDiv.dataset.sectionIndex = sectionIndex;

    const categorySearch = createCategorySearchInput(sectionIndex);

    sectionDiv.innerHTML = `
          <div class="flex items-center justify-between mb-4">
              <div class="flex-1 mr-4"></div>
              <button type="button" class="delete-section text-[#c7c7ce] hover:text-[#64748b] p-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
              </button>
          </div>
          <div class="questions-container space-y-4"></div>
          <button type="button" class="add-question w-full bg-[#c7c7ce] text-white hover:bg-[#64748b] font-medium py-2 px-4 rounded-lg flex items-center gap-2 transition duration-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Ajouter une question
          </button>
      `;

    sectionDiv.querySelector(".flex-1").appendChild(categorySearch);

    sectionDiv
      .querySelector(".delete-section")
      .addEventListener("click", function () {
        sectionDiv.remove();
      });

    sectionDiv
      .querySelector(".add-question")
      .addEventListener("click", function () {
        createQuestion(
          sectionDiv.querySelector(".questions-container"),
          sectionIndex
        );
      });

    sectionsContainer.appendChild(sectionDiv);
  }

  function createQuestion(container, sectionIndex) {
    const questionIndex = container.children.length;
    const questionDiv = document.createElement("div");
    questionDiv.className = "bg-gray-50 rounded-lg p-4 space-y-4";
    questionDiv.dataset.questionIndex = questionIndex;

    questionDiv.innerHTML = `
          <div class="flex items-start space-x-4">
              <div class="flex-1">
                  <input type="text" 
                      name="sections[${sectionIndex}][questions][${questionIndex}][texteQuestion]" 
                      placeholder="Question" 
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                      required>
                      
                  <div class="flex items-center space-x-2 mt-2">
                      <input type="file"
                          name="sections[${sectionIndex}][questions][${questionIndex}][image]"
                          class="image-input block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100"
                          accept="image/*">
                      <button type="button" class="cancel-image text-red-600 hover:text-red-800 hidden">Annuler</button>
                      <input type="hidden" name="sections[${sectionIndex}][questions][${questionIndex}][removeImage]" value="0">
                  </div>
                  <div class="image-preview hidden mt-2">
                      <img src="#" alt="Image Preview" class="max-h-40 rounded-md">
                  </div>
              </div>
              <button type="button" class="delete-question text-[#c7c7ce] hover:text-[#64748b] p-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
          </button>
      </div>
      <div class="answers-container space-y-2 ml-6"></div>
      <button type="button" class="add-answer text-[#c7c7ce] hover:text-[#64748b] font-medium py-1 px-2 rounded-lg transition duration-300 ml-6">
          Ajouter une réponse
      </button>
  `;

    const imageInput = questionDiv.querySelector(".image-input");
    const cancelImageButton = questionDiv.querySelector(".cancel-image");
    const imagePreview = questionDiv.querySelector(".image-preview img");
    const imagePreviewContainer = questionDiv.querySelector(".image-preview");
    const removeImageInput = questionDiv.querySelector(
      `input[name="sections[${sectionIndex}][questions][${questionIndex}][removeImage]"]`
    );

    imageInput.addEventListener("change", function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();

        reader.onload = function (e) {
          imagePreview.src = e.target.result;
          imagePreviewContainer.classList.remove("hidden");
          cancelImageButton.classList.remove("hidden");
          removeImageInput.value = "0"; // Reset removeImage value
        };

        reader.readAsDataURL(file);
      } else {
        imagePreview.src = "#";
        imagePreviewContainer.classList.add("hidden");
        cancelImageButton.classList.add("hidden");
        removeImageInput.value = "1"; // Set removeImage value to 1 if no file selected
      }
    });

    cancelImageButton.addEventListener("click", function () {
      imageInput.value = ""; // Clear the file input
      imagePreview.src = "#";
      imagePreviewContainer.classList.add("hidden");
      cancelImageButton.classList.add("hidden");
      removeImageInput.value = "1"; // Set removeImage value to 1 when cancel button is clicked
    });

    questionDiv
      .querySelector(".delete-question")
      .addEventListener("click", function () {
        questionDiv.remove();
      });

    questionDiv
      .querySelector(".add-answer")
      .addEventListener("click", function () {
        createAnswer(
          questionDiv.querySelector(".answers-container"),
          sectionIndex,
          questionIndex
        );
      });

    container.appendChild(questionDiv);
    createAnswer(
      questionDiv.querySelector(".answers-container"),
      sectionIndex,
      questionIndex
    );
  }

  function createAnswer(container, sectionIndex, questionIndex) {
    const answerIndex = container.children.length;
    const answerDiv = document.createElement("div");
    answerDiv.className = "flex items-center space-x-2 mb-2";

    const sectionCategoryId = document.querySelector(
      `[data-section-index="${sectionIndex}"] .category-id`
    ).value;

    answerDiv.innerHTML = `
          <div class="flex items-center space-x-4 w-full">
              <input type="radio" disabled class="w-4 h-4 cursor-not-allowed" style="accent-color: #c7c7ce">
              <input type="text" 
                  name="sections[${sectionIndex}][questions][${questionIndex}][reponses][${answerIndex}][texteReponse]" 
                  placeholder="Réponse" 
                  class="flex-1 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                  required>
              <input type="text" 
                  name="sections[${sectionIndex}][questions][${questionIndex}][reponses][${answerIndex}][explicationReponse]" 
                  placeholder="Explication de la réponse" 
                  class="flex-1 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                  >
              <input type="number" 
                  name="sections[${sectionIndex}][questions][${questionIndex}][reponses][${answerIndex}][points]" 
                  placeholder="Points" 
                  class="w-24 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                  required>
              <button type="button" class="delete-answer text-[#c7c7ce] hover:text-[#64748b] transition-colors">
                  <svg class="w-5 h-5 text-[#c7c7ce]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M6 18L18 6M6 6l12 12" />
                  </svg>
              </button>
              <input type="hidden" 
                  name="sections[${sectionIndex}][questions][${questionIndex}][reponses][${answerIndex}][categorie_id]" 
                  class="category-id" 
                  value="${sectionCategoryId}">
          </div>
      `;

    answerDiv
      .querySelector(".delete-answer")
      .addEventListener("click", function () {
        if (container.children.length > 1) {
          answerDiv.remove();
        }
      });

    container.appendChild(answerDiv);
  }

  addSectionBtn.addEventListener("click", createSection);
});
