$(document).ready(function () {
    var isMinimized = false;
  
    const reduire = `fa-solid fa-minus`;
    const retablir = `fa-solid fa-up-right-and-down-left-from-center text-sm`;
    $('.reduire').addClass(reduire);
  
    // Vérifier s'il y a une configuration précédente pour le modal
    var modalState = sessionStorage.getItem('modalState');
    if (modalState === 'minimized') {
      minimizeModal();
    } else if (modalState === 'normal') {
      openModal();
    }
  
    // Ouvrir le modal
    function openModal() {
      $("#screenProject").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modal").show();
      //$('#smartwizard-intra').smartWizard("reset");
      $('#smartwizard').smartWizard("reset");
    }
  
    // Ouvrir le modal
    $("#openModalBtn").click(function () {
      sessionStorage.setItem('modalState', 'normal');
      openModal();
    });
  
    // Fermer le modal
    $(".close").click(function () {
      $("#screenProject").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modal").hide();
      sessionStorage.removeItem('modalState');
    });
  
    // Réduire ou rétablir le modal
    $("#minimizeBtn").click(function () {
      if (isMinimized) {
        // Rétablir la taille principale du modal
        $("#modal").css({
          "max-height": "none",
          "max-width": "none",
          "overflow": "visible",
        });
        $("#screenProject").removeClass(`items-end justify-end fixed bottom-0 right-2`);
        $("#screenProject").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
        $('.reduire').addClass(reduire);
        isMinimized = false;
        sessionStorage.setItem('modalState', 'normal');
      } else {
        minimizeModal();
      }
    });
  
    // Réduire le modal
    function minimizeModal() {
      $("#modal").css({
        "max-height": "50px",
        "max-width": "270px",
        "overflow": "hidden",
      });
  
      $("#screenProject").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#screenProject").addClass(`items-end justify-end fixed bottom-0 right-2`);
      $('#modal').removeClass("hidden");
      $('.reduire').addClass(retablir);
      isMinimized = true;
      sessionStorage.setItem('modalState', 'minimized');
  
      $(".close").click(function () {
        $("#screenProject").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
        $("#modal").hide();
      });
    }
  });
  
  // Script pour CLIENT
  $(document).ready(function () {
    var isMinimized = false;
  
    const reduire = `fa-solid fa-minus`;
    const retablir = `fa-solid fa-up-right-and-down-left-from-center`;
    $('.reduireClient').addClass(reduire);
  
    // Vérifier s'il y a une configuration précédente pour le modalClient
    var modalClientState = sessionStorage.getItem('modalClientState');
    if (modalClientState === 'minimized') {
      minimizeModal();
    } else if (modalClientState === 'normal') {
      openModal();
    }
  
    // Ouvrir le modalClient
    function openModal() {
      $("#screenClient").removeClass(`items-end justify-end fixed bottom-0 right-[280px]`);
      $("#screenClient").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalClient").show();
    }
  
    // Ouvrir le modalClient
    $("#openModalBtnClient").click(function () {
      sessionStorage.setItem('modalClientState', 'normal');
      openModal();
    });
  
    // Fermer le modalClient
    $(".closeClient").click(function () {
      $("#screenClient").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalClient").hide();
      sessionStorage.removeItem('modalClientState');
    });
  
    // Réduire ou rétablir le modalClient
    $("#minimizeBtnClient").click(function () {
      if (isMinimized) {
        // Rétablir la taille principale du modalClient
        $("#modalClient").css({
          "max-height": "none",
          "max-width": "none",
          "overflow": "visible",
        });
        $("#screenClient").removeClass(`items-end justify-end fixed bottom-0 right-[280px]`);
        $("#screenClient").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
        $('.reduireClient').addClass(reduire);
        isMinimized = false;
        sessionStorage.setItem('modalClientState', 'normal');
      } else {
        minimizeModal();
      }
    });
  
    // Réduire le modalClient
    function minimizeModal() {
      $("#modalClient").css({
        "max-height": "50px",
        "max-width": "270px",
        "overflow": "hidden",
      });
      $("#screenClient").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#screenClient").addClass(`items-end justify-end fixed bottom-0 right-[280px]`);
      $('#modalClient').removeClass("hidden");
      $('.reduireClient').addClass(retablir);
      isMinimized = true;
      sessionStorage.setItem('modalClientState', 'minimized');
    }
  });
  
  // Script pour COURS
  $(document).ready(function () {
    var isMinimized = false;
  
    const reduire = `fa-solid fa-minus`;
    const retablir = `fa-solid fa-up-right-and-down-left-from-center`;
    $('.reduireCours').addClass(reduire);
  
    // Vérifier s'il y a une configuration précédente pour le modalCours
    var modalCoursState = sessionStorage.getItem('modalCoursState');
    if (modalCoursState === 'minimized') {
      minimizeModal();
    } else if (modalCoursState === 'normal') {
      openModal();
    }
  
    // Ouvrir le modalCours
    function openModal() {
      $("#screenCours").removeClass(`items-end justify-end fixed bottom-0 right-[555px]`);
      $("#screenCours").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalCours").show();
    }
  
    // Ouvrir le modalCours
    $("#openModalBtnCours").click(function () {
      sessionStorage.setItem('modalCoursState', 'normal');
      openModal();
    });
  
    // Fermer le modalCours
    $(".closeCours").click(function () {
      $("#screenCours").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalCours").hide();
      sessionStorage.removeItem('modalCoursState');
    });
  
    // Réduire ou rétablir le modalCours
    $("#minimizeBtnCours").click(function () {
      if (isMinimized) {
        // Rétablir la taille principale du modalCours
        $("#modalCours").css({
          "max-height": "none",
          "max-width": "none",
          "overflow": "visible",
        });
        $("#screenCours").removeClass(`items-end justify-end fixed bottom-0 right-[555px]`);
        $("#screenCours").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
        $('.reduireCours').addClass(reduire);
        isMinimized = false;
        sessionStorage.setItem('modalCoursState', 'normal');
      } else {
        minimizeModal();
      }
    });
  
    // Réduire le modalCours
    function minimizeModal() {
      $("#modalCours").css({
        "max-height": "50px",
        "max-width": "270px",
        "overflow": "hidden",
      });
      $("#screenCours").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#screenCours").addClass(`items-end justify-end fixed bottom-0 right-[555px]`);
      $('#modalCours').removeClass("hidden");
      $('.reduireCours').addClass(retablir);
      isMinimized = true;
      sessionStorage.setItem('modalCoursState', 'minimized');
    }
  });
  
  // Script pour FORMATEUR
  $(document).ready(function () {
    var isMinimized = false;
  
    const reduire = `fa-solid fa-minus`;
    const retablir = `fa-solid fa-up-right-and-down-left-from-center`;
    $('.reduireFormateur').addClass(reduire);
  
    // Vérifier s'il y a une configuration précédente pour le modalFormateur
    var modalFormateurState = sessionStorage.getItem('modalFormateurState');
    if (modalFormateurState === 'minimized') {
      minimizeModal();
    } else if (modalFormateurState === 'normal') {
      openModal();
    }
  
    // Ouvrir le modalFormateur
    function openModal() {
      $("#screenFormateur").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
      $("#screenFormateur").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalFormateurInterne").show();
    }
  
    // Ouvrir le modalFormateur
    $("#openModalBtnFormateur").click(function () {
      sessionStorage.setItem('modalFormateurState', 'normal');
      openModal();
    });
  
    // Fermer le modalFormateur
    $(".closeFormateur").click(function () {
      $("#screenFormateur").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalFormateurInterne").hide();
      sessionStorage.removeItem('modalFormateurState');
    });
  
    // Réduire ou rétablir le modalFormateur
    $("#minimizeBtnFormateur").click(function () {
      if (isMinimized) {
        // Rétablir la taille principale du modalFormateur
        $("#modalFormateurInterne").css({
          "max-height": "none",
          "max-width": "none",
          "overflow": "visible",
        });
        $("#screenFormateur").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
        $("#screenFormateur").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
        $('.reduireFormateur').addClass(reduire);
        isMinimized = false;
        sessionStorage.setItem('modalFormateurState', 'normal');
      } else {
        minimizeModal();
      }
    });
  
    // Réduire le modalFormateur
    function minimizeModal() {
      $("#modalFormateurInterne").css({
        "max-height": "50px",
        "max-width": "270px",
        "overflow": "hidden",
      });
      $("#screenFormateur").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#screenFormateur").addClass(`items-end justify-end fixed bottom-0 right-[830px]`);
      $('#modalFormateurInterne').removeClass("hidden");
      $('.reduireFormateur').addClass(retablir);
      isMinimized = true;
      sessionStorage.setItem('modalFormateurState', 'minimized');
    }
  });
  
  // Script pour APPRENANT
  $(document).ready(function () {
    var isMinimized = false;
  
    const reduire = `fa-solid fa-minus`;
    const retablir = `fa-solid fa-up-right-and-down-left-from-center`;
    $('.reduireApprenant').addClass(reduire);
  
    // Vérifier s'il y a une configuration précédente pour le modalApprenant
    var modalApprenantState = sessionStorage.getItem('modalApprenantState');
    if (modalApprenantState === 'minimized') {
      minimizeModal();
    } else if (modalApprenantState === 'normal') {
      openModal();
    }
  
    // Ouvrir le modalApprenant
    // function openModal() {
    //   $("#screenApprenant").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
    //   $("#screenApprenant").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
    //   $("#modalApprenant").show();
    // }
  
    // Ouvrir le modalApprenant
    // $("#openModalBtnApprenant").click(function () {
    //   sessionStorage.setItem('modalApprenantState', 'normal');
    //   openModal();
    // });

    // Ouvrir le modalEmploye
    function openModal() {
      $("#screenEmploye").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
      $("#screenEmploye").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalEmploye").show();
    }

    // Ouvrir le modalEmployé
    $("#openModalBtnEmploye").click(function () {
      sessionStorage.setItem('modalEmployeState', 'normal');
      openModal();
    });
  
    // Fermer le modalApprenant
    // $(".closeApprenant").click(function () {
    //   $("#screenApprenant").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
    //   $("#modalApprenant").hide();
    //   sessionStorage.removeItem('modalApprenantState');
    // });

     // Fermer le modalEmployé
    $(".closeEmploye").click(function () {
      $("#screenEmploye").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalEmploye").hide();
      sessionStorage.removeItem('modalEmployeState');
    });
  
    // Réduire ou rétablir le modalApprenant
    // $("#minimizeBtnApprenant").click(function () {
    //   if (isMinimized) {
    //     // Rétablir la taille principale du modalApprenant
    //     $("#modalApprenant").css({
    //       "max-height": "none",
    //       "max-width": "none",
    //       "overflow": "visible",
    //     });
    //     $("#screenApprenant").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
    //     $("#screenApprenant").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
    //     $('.reduireApprenant').addClass(reduire);
    //     isMinimized = false;
    //     sessionStorage.setItem('modalApprenantState', 'normal');
    //   } else {
    //     minimizeModal();
    //   }
    // });
    // Réduire ou rétablir le modalEmploye
    $("#minimizeBtnEmploye").click(function () {
      if (isMinimized) {
        // Rétablir la taille principale du modalApprenant
        $("#modalEmploye").css({
          "max-height": "none",
          "max-width": "none",
          "overflow": "visible",
        });
        $("#screenEmploye").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
        $("#screenEmploye").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
        $('.reduireEmploye').addClass(reduire);
        isMinimized = false;
        sessionStorage.setItem('modalApprenantState', 'normal');
      } else {
        minimizeModal();
      }
    });
  
    // Réduire le modalApprenant
  //   function minimizeModal() {
  //     $("#modalApprenant").css({
  //       "max-height": "50px",
  //       "max-width": "270px",
  //       "overflow": "hidden",
  //     });
  //     $("#screenApprenant").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
  //     $("#screenApprenant").addClass(`items-end justify-end fixed bottom-0 right-[830px]`);
  //     $('#modalApprenant').removeClass("hidden");
  //     $('.reduireApprenant').addClass(retablir);
  //     isMinimized = true;
  //     sessionStorage.setItem('modalApprenantState', 'minimized');
  //   }
  // });
  
    // Réduire le modalEmploye
    function minimizeModal() {
      $("#modalEmploye").css({
        "max-height": "50px",
        "max-width": "270px",
        "overflow": "hidden",
      });
      $("#screenEmploye").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#screenEmploye").addClass(`items-end justify-end fixed bottom-0 right-[830px]`);
      $('#modalEmploye').removeClass("hidden");
      $('.reduireEmploye').addClass(retablir);
      isMinimized = true;
      sessionStorage.setItem('modalEmployeState', 'minimized');
    }
  });
  
  
  // Script pour Salle
  $(document).ready(function () {
    var isMinimized = false;
  
    const reduire = `fa-solid fa-minus`;
    const retablir = `fa-solid fa-up-right-and-down-left-from-center`;
    $('.reduireSalle').addClass(reduire);
  
    // Vérifier s'il y a une configuration précédente pour le modalSalle
    var modalSalleState = sessionStorage.getItem('modalSalleState');
    if (modalSalleState === 'minimized') {
      minimizeModal();
    } else if (modalSalleState === 'normal') {
      openModal();
    }
  
    // Ouvrir le modalSalle
    function openModal() {
      $("#screenSalle").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
      $("#screenSalle").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalSalle").show();
    }
  
    // Ouvrir le modalSalle
    $("#openModalBtnSalle").click(function () {
      sessionStorage.setItem('modalSalleState', 'normal');
      openModal();
    });
  
    // Fermer le modalSalle
    $(".closeSalle").click(function () {
      $("#screenSalle").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalSalle").hide();
      sessionStorage.removeItem('modalSalleState');
    });
  
    // Réduire ou rétablir le modalSalle
    $("#minimizeBtnSalle").click(function () {
      if (isMinimized) {
        // Rétablir la taille principale du modalSalle
        $("#modalSalle").css({
          "max-height": "none",
          "max-width": "none",
          "overflow": "visible",
        });
        $("#screenSalle").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
        $("#screenSalle").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
        $('.reduireSalle').addClass(reduire);
        isMinimized = false;
        sessionStorage.setItem('modalSalleState', 'normal');
      } else {
        minimizeModal();
      }
    });
  
    // Réduire le modalSalle
    function minimizeModal() {
      $("#modalSalle").css({
        "max-height": "50px",
        "max-width": "270px",
        "overflow": "hidden",
      });
      $("#screenSalle").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#screenSalle").addClass(`items-end justify-end fixed bottom-0 right-[830px]`);
      $('#modalSalle').removeClass("hidden");
      $('.reduireSalle').addClass(retablir);
      isMinimized = true;
      sessionStorage.setItem('modalSalleState', 'minimized');
    }
  });
  
  
  
  // Script pour Referent
  $(document).ready(function () {
    var isMinimized = false;
  
    const reduire = `fa-solid fa-minus`;
    const retablir = `fa-solid fa-up-right-and-down-left-from-center`;
    $('.reduireReferent').addClass(reduire);
  
    // Vérifier s'il y a une configuration précédente pour le modalReferent
    var modalReferentState = sessionStorage.getItem('modalReferentState');
    if (modalReferentState === 'minimized') {
      minimizeModal();
    } else if (modalReferentState === 'normal') {
      openModal();
    }
  
    // Ouvrir le modalReferent
    function openModal() {
      $("#screenReferent").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
      $("#screenReferent").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalReferent").show();
    }
  
    // Ouvrir le modalReferent
    $("#openModalBtnReferent").click(function () {
      sessionStorage.setItem('modalReferentState', 'normal');
      openModal();
    });
  
    // Fermer le modalReferent
    $(".closeReferent").click(function () {
      $("#screenReferent").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalReferent").hide();
      sessionStorage.removeItem('modalReferentState');
    });
  
    // Réduire ou rétablir le modalReferent
    $("#minimizeBtnReferent").click(function () {
      if (isMinimized) {
        // Rétablir la taille principale du modalReferent
        $("#modalReferent").css({
          "max-height": "none",
          "max-width": "none",
          "overflow": "visible",
        });
        $("#screenReferent").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
        $("#screenReferent").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
        $('.reduireReferent').addClass(reduire);
        isMinimized = false;
        sessionStorage.setItem('modalReferentState', 'normal');
      } else {
        minimizeModal();
      }
    });
  
    // Réduire le modalReferent
    function minimizeModal() {
      $("#modalReferent").css({
        "max-height": "50px",
        "max-width": "270px",
        "overflow": "hidden",
      });
      $("#screenReferent").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#screenReferent").addClass(`items-end justify-end fixed bottom-0 right-[830px]`);
      $('#modalReferent').removeClass("hidden");
      $('.reduireReferent').addClass(retablir);
      isMinimized = true;
      sessionStorage.setItem('modalReferentState', 'minimized');
    }
  });
  
  // SCRIPT ADD BANK ACCOUNT
  $(document).ready(function () {
    var isMinimized = false;
  
    const reduire = `fa-solid fa-minus`;
    const retablir = `fa-solid fa-up-right-and-down-left-from-center`;
    $('.reduireBankAccount').addClass(reduire);
  
    // Vérifier s'il y a une configuration précédente pour le modalBankAccount
    var modalBankAccountState = sessionStorage.getItem('modalBankAccountState');
    if (modalBankAccountState === 'minimized') {
      minimizeModal();
    } else if (modalBankAccountState === 'normal') {
      openModal();
    }
  
    // Ouvrir le modalBankAccount
    function openModal() {
      $("#screenBankAccount").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
      $("#screenBankAccount").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalBankAccount").show();
    }
  
    // Ouvrir le modalBankAccount
    $("#openModalBtnBankAccount").click(function () {
      sessionStorage.setItem('modalBankAccountState', 'normal');
      openModal();
    });
  
    // Fermer le modalBankAccount
    $(".closeBankAccount").click(function () {
      $("#screenBankAccount").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#modalBankAccount").hide();
      sessionStorage.removeItem('modalBankAccountState');
    });
  
    // Réduire ou rétablir le modalBankAccount
    $("#minimizeBtnBankAccount").click(function () {
      if (isMinimized) {
        // Rétablir la taille principale du modalBankAccount
        $("#modalBankAccount").css({
          "max-height": "none",
          "max-width": "none",
          "overflow": "visible",
        });
        $("#screenBankAccount").removeClass(`items-end justify-end fixed bottom-0 right-[830px]`);
        $("#screenBankAccount").addClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
        $('.reduireBankAccount').addClass(reduire);
        isMinimized = false;
        sessionStorage.setItem('modalBankAccountState', 'normal');
      } else {
        minimizeModal();
      }
    });
  
    // Réduire le modalBankAccount
    function minimizeModal() {
      $("#modalBankAccount").css({
        "max-height": "50px",
        "max-width": "270px",
        "overflow": "hidden",
      });
      $("#screenBankAccount").removeClass(`w-screen h-screen flex items-center justify-center mx-auto fixed top-0 right-0 z-50 bg-gray-400/25 backdrop-blur-sm`);
      $("#screenBankAccount").addClass(`items-end justify-end fixed bottom-0 right-[830px]`);
      $('#modalBankAccount').removeClass("hidden");
      $('.reduireBankAccount').addClass(retablir);
      isMinimized = true;
      sessionStorage.setItem('modalBankAccountState', 'minimized');
    }
  });

  // Script pour APPRENANT lors ajout de plusieurs excel
  $(document).ready(function () {
    $("#addSeveralApprenantForm").hide();

    $("#linkApprenantExcel").click(() => {
      $("#addOneApprenantForm").hide();
      $("#addSeveralApprenantForm").show();
      $("#addApprenantFormTitle").text(
        "Ajouter plusieurs apprenants via Excel"
      );
    });
    $("#linkApprenantForm").click(() => {
      $("#addSeveralApprenantForm").hide();
      $("#addOneApprenantForm").show();
      $("#addApprenantFormTitle").text("Ajouter un apprenant");
    });

    $("#addStepOne").show();
    $("#addStepTwo").hide();
    $("#addStepThree").hide();

    $(".buttonStepOne").click(() => {
      $("#addStepOne").show();
      $("#addStepTwo").hide();
      $("#addStepThree").hide();
    });
    $(".buttonStepTwo").click(() => {
      $("#addStepTwo").show();
      $("#addStepOne").hide();
      $("#addStepThree").hide();
    });

    $(".buttonStepThree").click(() => {
      $("#addStepThree").show();
      $("#addStepOne").hide();
      $("#addStepTwo").hide();
    });

  });
