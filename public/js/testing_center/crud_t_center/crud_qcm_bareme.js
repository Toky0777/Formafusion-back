document.addEventListener("DOMContentLoaded", function () {
  const qcmId = document.getElementById("qcm-id").value;
  const addBaremeForm = document.getElementById("add-bareme-form");
  const successToast = document.getElementById("successToast");
  const errorToast = document.getElementById("errorToast");
  const successMessage = document.getElementById("successMessage");
  const errorMessage = document.getElementById("errorMessage");

  // Function to show toast
  function showToast(toast, message) {
    toast.querySelector("span").textContent = message;
    toast.classList.remove("hidden");
    setTimeout(() => toast.classList.add("hidden"), 3000);
  }

  loadBaremes(qcmId);

  addBaremeForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("idQCM", qcmId);

    const baremeId = document.getElementById("bareme-id").value;
    const url = baremeId
      ? `/qcm/qcm-bareme/update/${baremeId}`
      : `/qcm/qcm-bareme/store/${qcmId}`;

    fetch(url, {
      method: "POST",
      body: formData,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
          .content,
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          loadBaremes(qcmId);
          addBaremeForm.reset();
          document.getElementById("bareme-id").value = "";
          showToast(successToast, "Barème enregistré avec succès!");
        } else {
          showToast(errorToast, "Erreur lors de l'enregistrement du barème.");
        }
      })
      .catch(() => showToast(errorToast, "Une erreur est survenue."));
  });

  function loadBaremes(qcmId) {
    fetch(`/qcm/get/${qcmId}`)
      .then((response) => response.json())
      .then((data) => {
        const baremeList = document.getElementById("bareme-list");
        baremeList.innerHTML = "";

        data.forEach((bareme) => {
          const row = document.createElement("tr");
          row.className = "border-b border-gray-200 hover:bg-gray-100";
          row.innerHTML = `
                <td class="py-3 px-6 text-left">${bareme.minPoints}</td>
                <td class="py-3 px-6 text-left">${bareme.maxPoints}</td>
                <td class="py-3 px-6 text-left">${bareme.niveau}</td>
                <td class="py-3 px-6 text-center">
                  <!-- Modifier Icon -->
                  <button onclick="editBareme(${bareme.idBareme})" class="text-[#64748b] hover:text-gray-600 mr-2">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-4 h-4">
                          <path
                              d="M410.3 231l11.3-11.3-33.9-33.9-62.1-62.1L291.7 89.8l-11.3 11.3-22.6 22.6L58.6 322.9c-10.4 10.4-18 23.3-22.2 37.4L1 480.7c-2.5 8.4-.2 17.5 6.1 23.7s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L387.7 253.7 410.3 231zM160 399.4l-9.1 22.7c-4 3.1-8.5 5.4-13.3 6.9L59.4 452l23-78.1c1.4-4.9 3.8-9.4 6.9-13.3l22.7-9.1 0 32c0 8.8 7.2 16 16 16l32 0zM362.7 18.7L348.3 33.2 325.7 55.8 314.3 67.1l33.9 33.9 62.1 62.1 33.9 33.9 11.3-11.3 22.6-22.6 14.5-14.5c25-25 25-65.5 0-90.5L453.3 18.7c-25-25-65.5-25-90.5 0zm-47.4 168l-144 144c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l144-144c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6z" fill="#64748b" />
                      </svg>
                  </button>

                  <!-- Supprimer Icon -->
                  <button onclick="deleteBareme(${bareme.idBareme})" class="text-[#64748b] hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-4 h-4">
                          <path
                              d="M135.2 17.7C140.6 6.8 151.7 0 163.8 0L284.2 0c12.1 0 23.2 6.8 28.6 17.7L320 32l96 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 96C14.3 96 0 81.7 0 64S14.3 32 32 32l96 0 7.2-14.3zM32 128l384 0 0 320c0 35.3-28.7 64-64 64L96 512c-35.3 0-64-28.7-64-64l0-320zm96 64c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16z" fill="#64748b" />
                      </svg>
                  </button>
              </td>
            `;
          baremeList.appendChild(row);
        });
      });
  }

  window.editBareme = function (baremeId) {
    fetch(`/qcm/qcm_bareme/${baremeId}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erreur lors de la récupération des données.");
        }
        return response.json();
      })
      .then((bareme) => {
        document.getElementById("bareme-id").value = bareme.idBareme;
        document.getElementById("minPoints").value = bareme.minPoints;
        document.getElementById("maxPoints").value = bareme.maxPoints;
        document.getElementById("niveau").value = bareme.niveau;

        // Display success message
        alert("Barème chargé avec succès!");
      })
      .catch((error) => {
        // Display error message
        alert("Erreur: " + error.message);
      });
  };

  window.deleteBareme = function (baremeId) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce barème ?")) {
      fetch(`/qcm/qcm-bareme/delete/${baremeId}`, {
        method: "DELETE",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
            .content,
        },
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Erreur lors de la suppression du barème.");
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            loadBaremes(qcmId);
            // Display success message
            alert("Barème supprimé avec succès!");
          }
        })
        .catch((error) => {
          // Display error message
          alert("Erreur: " + error.message);
        });
    }
  };
});
