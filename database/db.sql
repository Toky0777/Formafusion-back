CREATE TABLE lieu_types(
   idLieuType INT,
   lt_name VARCHAR(50) ,
   PRIMARY KEY(idLieuType)
);

CREATE TABLE module_levels(
   idLevel INT PRIMARY KEY AUTO_INCREMENT,
   module_level_name VARCHAR(150) 
);

CREATE TABLE secteurs(
   idSecteur INT AUTO_INCREMENT,
   secteur VARCHAR(50) ,
   PRIMARY KEY(idSecteur) 
);

CREATE TABLE niveau_etudes(
   idNiveau INT AUTO_INCREMENT,
   niveau VARCHAR(50) ,
   PRIMARY KEY(idNiveau)
);

CREATE TABLE users(
   id bigint AUTO_INCREMENT,
   name VARCHAR(250) ,
   email VARCHAR(100) UNIQUE,
   password VARCHAR(255) ,
   matricule VARCHAR(100) ,
   firstName VARCHAR(150) ,
   cin VARCHAR(50) UNIQUE,
   phone VARCHAR(50) ,
   adresse VARCHAR(150),
   photo TEXT,
   dateNais DATE,
   idVille INT DEFAULT 1,
   user_addr_quartier VARCHAR(100) ,
   user_addr_lot VARCHAR(100) ,
   user_addr_rue VARCHAR(100) ,
   user_addr_code_postal SMALLINT,
   created_at timestamp NULL DEFAULT NULL,
   updated_at timestamp NULL DEFAULT NULL,
   PRIMARY KEY(id)
);

CREATE TABLE roles(
   id bigint AUTO_INCREMENT,
   roleName VARCHAR(50) ,
   roleDescription VARCHAR(50) ,
   PRIMARY KEY(id)
);

CREATE TABLE sexes(
   idSexe INT AUTO_INCREMENT,
   sexe VARCHAR(50) ,
   PRIMARY KEY(idSexe)
);

CREATE TABLE specialtites(
   idSp INT AUTO_INCREMENT,
   specialite VARCHAR(50) ,
   PRIMARY KEY(idSp)
);

CREATE TABLE paiements(
   idPaiement INT AUTO_INCREMENT,
   paiement VARCHAR(50),
   PRIMARY KEY(idPaiement)
);

CREATE TABLE villes(
   idVille INT AUTO_INCREMENT,
   ville VARCHAR(50) ,
   PRIMARY KEY(idVille)
);

CREATE TABLE ville_codeds(
   id BIGINT AUTO_INCREMENT,
   ville_name VARCHAR(150) ,
   vi_code_postal VARCHAR(50) ,
   idVille INT NOT NULL,
   PRIMARY KEY(id),
   FOREIGN KEY(idVille) REFERENCES villes(idVille)
);

CREATE TABLE lieux(
   idLieu BIGINT AUTO_INCREMENT,
   li_name VARCHAR(150) ,
   li_quartier VARCHAR(100) ,
   li_rue VARCHAR(100) ,
   idVille INT NOT NULL,
   idLieuType INT NOT NULL,
   idVilleCoded BIGINT NOT NULL,
   PRIMARY KEY(idLieu),
   FOREIGN KEY(idVille) REFERENCES villes(idVille),
   FOREIGN KEY(idLieuType) REFERENCES lieu_types(idLieuType),
   FOREIGN KEY(idVilleCoded) REFERENCES ville_codeds(id)
);

CREATE TABLE salles(
   idSalle INT AUTO_INCREMENT,
   salle_name VARCHAR(255) ,
   idLieu BIGINT NOT NULL,
   salle_image TEXT,
   PRIMARY KEY(idSalle),
   FOREIGN KEY(idLieu) REFERENCES lieux(idLieu)
);

CREATE TABLE type_abns(
   idTypeAbn INT AUTO_INCREMENT,
   typeAbn VARCHAR(50) ,
   PRIMARY KEY(idTypeAbn)
);

CREATE TABLE modalites(
   idModalite INT AUTO_INCREMENT,
   modalite VARCHAR(50) ,
   PRIMARY KEY(idModalite)
);

CREATE TABLE type_projets(
   idTypeProjet INT AUTO_INCREMENT,
   type VARCHAR(50) ,
   PRIMARY KEY(idTypeProjet)
);

CREATE TABLE Type(
   id INT AUTO_INCREMENT,
   type VARCHAR(50) ,
   PRIMARY KEY(id)
);

CREATE TABLE type_questions(
   idTypeQuestion BIGINT AUTO_INCREMENT,
   typeQuestion VARCHAR(50) ,
   PRIMARY KEY(idTypeQuestion)
);

CREATE TABLE abonnements(
   idAbn BIGINT AUTO_INCREMENT,
   intitule VARCHAR(50) ,
   description VARCHAR(200) ,
   prixAbn DECIMAL(15,2)  ,
   PRIMARY KEY(idAbn)
);

CREATE TABLE abn_etps(
   idAbn BIGINT AUTO_INCREMENT,
   nbEmploye INT,
   isInfinity TINYINT,
   PRIMARY KEY(idAbn),
   FOREIGN KEY(idAbn) REFERENCES abonnements(idAbn)
);

CREATE TABLE type_modules(
   idTypeModule INT AUTO_INCREMENT,
   typeModule VARCHAR(50) ,
   PRIMARY KEY(idTypeModule)
);

CREATE TABLE type_customers(
   idTypeCustomer INT(2) AUTO_INCREMENT ,
   typeCustomer VARCHAR(50) ,
   PRIMARY KEY(idTypeCustomer)
);

CREATE TABLE type_formateurs(
   idTypeFormateur INT AUTO_INCREMENT,
   type VARCHAR(50) ,
   PRIMARY KEY(idTypeFormateur)
);

CREATE TABLE forms(
   idFormateur BIGINT,
   idTypeFormateur INT NOT NULL,
   idSexe INT NOT NULL,
   PRIMARY KEY(idFormateur),
   FOREIGN KEY(idFormateur) REFERENCES users(id),
   FOREIGN KEY(idTypeFormateur) REFERENCES type_formateurs(idTypeFormateur),
   FOREIGN KEY(idSexe) REFERENCES sexes(idSexe)
);

CREATE TABLE domaine_formations(
   idDomaine BIGINT AUTO_INCREMENT,
   nomDomaine VARCHAR(200) ,
   PRIMARY KEY(idDomaine)
);

CREATE TABLE abn_cfps(
   idAbn BIGINT AUTO_INCREMENT,
   nbReferent INT,
   nbForm INT,
   nbSession INT,
   isInfinity TINYINT,
   PRIMARY KEY(idAbn),
   FOREIGN KEY(idAbn) REFERENCES abonnements(idAbn)
);

CREATE TABLE pm_types(
   idTypePm SMALLINT AUTO_INCREMENT,
   pm_type_name VARCHAR(200) ,
   PRIMARY KEY(idTypePm)
);

CREATE TABLE mode_paiements(
   idPaiement BIGINT AUTO_INCREMENT,
   idTypePm SMALLINT NOT NULL,
   PRIMARY KEY(idPaiement),
   FOREIGN KEY(idTypePm) REFERENCES pm_types(idTypePm)
);

CREATE TABLE pm_cheques(
   idPaiement BIGINT NOT NULL,
   bank_account_orderer VARCHAR(200) ,
   PRIMARY KEY(idPaiement),
   FOREIGN KEY(idPaiement) REFERENCES mode_paiements(idPaiement)
);

CREATE TABLE pm_virements(
   idPaiement BIGINT NOT NULL,
   bank_titulaire VARCHAR(200) ,
   bank_name VARCHAR(200) ,
   bank_postal VARCHAR(50) ,
   bank_account_number VARCHAR(50) ,
   PRIMARY KEY(idPaiement),
   UNIQUE(bank_account_number),
   FOREIGN KEY(idPaiement) REFERENCES mode_paiements(idPaiement)
);

CREATE TABLE devises(
   idDevise BIGINT AUTO_INCREMENT,
   devise VARCHAR(200) ,
   PRIMARY KEY(idDevise)
);

CREATE TABLE unites(
   idUnite BIGINT AUTO_INCREMENT,
   unite_name VARCHAR(200) ,
   PRIMARY KEY(idUnite)
);

CREATE TABLE sections(
   idSection BIGINT AUTO_INCREMENT,
   section VARCHAR(255) ,
   PRIMARY KEY(idSection)
);

CREATE TABLE quizz_questions(
   idQuestion BIGINT AUTO_INCREMENT,
   question TEXT,
   idSection BIGINT NOT NULL,
   PRIMARY KEY(idQuestion),
   FOREIGN KEY(idSection) REFERENCES sections(idSection)
);

CREATE TABLE reponses(
   idReponse BIGINT AUTO_INCREMENT,
   reponse TEXT,
   idQuestion BIGINT NOT NULL,
   PRIMARY KEY(idReponse),
   FOREIGN KEY(idQuestion) REFERENCES quizz_questions(idQuestion)
);

CREATE TABLE type_factures(
   idTypeFacture INT AUTO_INCREMENT,
   typeFacture VARCHAR(200) ,
   PRIMARY KEY(idTypeFacture)
);

CREATE TABLE type_services(
   idTypeService BIGINT AUTO_INCREMENT,
   nomTypeService VARCHAR(255) ,
   PRIMARY KEY(idTypeService)
);

CREATE TABLE type_materiels(
   idTypeMateriel SMALLINT AUTO_INCREMENT,
   typeMateriel VARCHAR(50) ,
   PRIMARY KEY(idTypeMateriel)
);

CREATE TABLE resp_materiels(
   idRespMateriel INT AUTO_INCREMENT,
   respMateriel VARCHAR(100) ,
   PRIMARY KEY(idRespMateriel)
);

CREATE TABLE google_users(
   user_id BIGINT,
   google_id TEXT,
   avatar TEXT,
   PRIMARY KEY(user_id),
   FOREIGN KEY(user_id) REFERENCES users(id)
);

-- évaluation à froids ireto
CREATE TABLE quizz_types(
   id INT,
   quizz_name VARCHAR(250) ,
   PRIMARY KEY(id)
);

CREATE TABLE quizz_colds(
   id BIGINT AUTO_INCREMENT,
   quizz_cold_name CHAR(250) ,
   idQuizzType INT NOT NULL,
   PRIMARY KEY(id),
   FOREIGN KEY(idQuizzType) REFERENCES quizz_types(id)
);

CREATE TABLE quizz_levels(
   id INT,
   quizz_level_value TINYINT,
   quizz_level_desc VARCHAR(200) ,
   PRIMARY KEY(id)
);
-- fin évaluation à froids

CREATE TABLE questions(
   idQuestion BIGINT AUTO_INCREMENT,
   question TEXT ,
   idTypeQuestion BIGINT NOT NULL,
   PRIMARY KEY(idQuestion),
   FOREIGN KEY(idTypeQuestion) REFERENCES type_questions(idTypeQuestion)
);

CREATE TABLE customers(
   idCustomer BIGINT,
   customerName VARCHAR(255),
   assujetti TINYINT DEFAULT 0,
   description TEXT NULL,
   siteWeb VARCHAR(50) NULL,
   logo VARCHAR(50) NULL,
   customerEmail VARCHAR(200) UNIQUE,
   customerPhone VARCHAR(200),
   customer_addr_lot VARCHAR(255),
   customer_addr_quartier VARCHAR(100) ,
   customer_addr_rue VARCHAR(100) ,
   customer_addr_code_postal SMALLINT,
   customer_slogan VARCHAR(255) ,
   customer_social_siege VARCHAR(150) ,
   nif VARCHAR(50) UNIQUE DEFAULT NULL,
   stat VARCHAR(50) UNIQUE DEFAULT NULL,
   rcs VARCHAR(50) UNIQUE DEFAULT NULL,
   idSecteur INT NOT NULL,
   idTypeCustomer INT NOT NULL,
   idVilleCoded BIGINT NOT NULL,
   created_at timestamp NULL DEFAULT NULL,
   updated_at timestamp NULL DEFAULT NULL,
   PRIMARY KEY(idCustomer),
   FOREIGN KEY(idCustomer) REFERENCES users(id),
   FOREIGN KEY(idSecteur) REFERENCES secteurs(idSecteur),
   FOREIGN KEY(idVilleCoded) REFERENCES ville_codeds(id),
   FOREIGN KEY(idTypeCustomer) REFERENCES type_customers(idTypeCustomer)
);

CREATE TABLE cfps(
   idCustomer BIGINT,
   PRIMARY KEY(idCustomer),
   FOREIGN KEY(idCustomer) REFERENCES customers(idCustomer)
);

CREATE TABLE type_entreprises(
   idTypeEtp SMALLINT,
   type_etp VARCHAR(100) ,
   PRIMARY KEY(idTypeEtp)
);

CREATE TABLE entreprises(
   idCustomer BIGINT,
   idTypeEtp SMALLINT NOT NULL,
   PRIMARY KEY(idCustomer),
   FOREIGN KEY(idCustomer) REFERENCES customers(idCustomer),
   FOREIGN KEY(idTypeEtp) REFERENCES type_entreprises(idTypeEtp)
);

CREATE TABLE formateurs(
   idFormateur BIGINT,
   idSp INT NOT NULL,
   PRIMARY KEY(idFormateur),
   FOREIGN KEY(idFormateur) REFERENCES forms(idFormateur),
   FOREIGN KEY(idSp) REFERENCES specialtites(idSp)
);

CREATE TABLE feries(
   idFerie BIGINT AUTO_INCREMENT,
   titleFerie VARCHAR(255) ,
   dateFerie DATE,
   idCustomer BIGINT NOT NULL,
   PRIMARY KEY(idFerie),
   FOREIGN KEY(idCustomer) REFERENCES customers(idCustomer)
);

CREATE TABLE type_fournisseurs(
   idTypefournisseur INT,
   typeFournisseur VARCHAR(50) ,
   PRIMARY KEY(idTypefournisseur)
);

CREATE TABLE fournisseurs(
   idFournisseur BIGINT AUTO_INCREMENT,
   nomFournisseur VARCHAR(255) ,
   telFournisseur VARCHAR(50) ,
   emailFournisseur VARCHAR(50) ,
   serviceOffertFournisseur TEXT,
   idCustomer BIGINT NOT NULL,
   idTypefournisseur INT NOT NULL,
   idTypeService BIGINT NOT NULL,
   PRIMARY KEY(idFournisseur),
   UNIQUE(emailFournisseur),
   FOREIGN KEY(idCustomer) REFERENCES customers(idCustomer),
   FOREIGN KEY(idTypefournisseur) REFERENCES type_fournisseurs(idTypefournisseur),
   FOREIGN KEY(idTypeService) REFERENCES type_services(idTypeService)
);

CREATE TABLE materiel_cfps(
   idMateriel BIGINT AUTO_INCREMENT,
   codeMateriel VARCHAR(50) ,
   nomMateriel VARCHAR(255) ,
   descriptionMateriel TEXT,
   idCfp BIGINT NOT NULL,
   idTypeMateriel SMALLINT NOT NULL,
   PRIMARY KEY(idMateriel),
   FOREIGN KEY(idCfp) REFERENCES cfps(idCustomer),
   FOREIGN KEY(idTypeMateriel) REFERENCES type_materiels(idTypeMateriel)
);

CREATE TABLE materiel_internes(
   idMateriel BIGINT,
   PRIMARY KEY(idMateriel),
   FOREIGN KEY(idMateriel) REFERENCES materiel_cfps(idMateriel)
);

CREATE TABLE materiel_externes(
   idMateriel BIGINT,
   idFournisseur BIGINT NOT NULL,
   PRIMARY KEY(idMateriel),
   FOREIGN KEY(idMateriel) REFERENCES materiel_cfps(idMateriel),
   FOREIGN KEY(idFournisseur) REFERENCES fournisseurs(idFournisseur)
);

CREATE TABLE materiel_etps(
   idMateriel BIGINT,
   codeMateriel VARCHAR(50) ,
   nomMateriel VARCHAR(255) ,
   descriptionMateriel TEXT,
   idEtp BIGINT NOT NULL,
   idTypeMateriel SMALLINT NOT NULL,
   PRIMARY KEY(idMateriel),
   FOREIGN KEY(idEtp) REFERENCES entreprises(idCustomer),
   FOREIGN KEY(idTypeMateriel) REFERENCES type_materiels(idTypeMateriel)
);

CREATE TABLE materiel_interne_etps(
   idMateriel BIGINT,
   PRIMARY KEY(idMateriel),
   FOREIGN KEY(idMateriel) REFERENCES materiel_etps(idMateriel)
);

CREATE TABLE materiel_externe_etps(
   idMateriel BIGINT,
   idFournisseur BIGINT NOT NULL,
   PRIMARY KEY(idMateriel),
   FOREIGN KEY(idMateriel) REFERENCES materiel_etps(idMateriel),
   FOREIGN KEY(idFournisseur) REFERENCES fournisseurs(idFournisseur)
);

CREATE TABLE fournisseur_cfps(
   idFournisseur BIGINT,
   PRIMARY KEY(idFournisseur),
   FOREIGN KEY(idFournisseur) REFERENCES fournisseurs(idFournisseur)
);

CREATE TABLE fournisseur_etps(
   idFournisseur BIGINT,
   PRIMARY KEY(idFournisseur),
   FOREIGN KEY(idFournisseur) REFERENCES fournisseurs(idFournisseur)
);

CREATE TABLE fonctions(
   idFonction INT PRIMARY KEY AUTO_INCREMENT,
   fonction TEXT ,
   idCustomer BIGINT,
   FOREIGN KEY(idCustomer) REFERENCES customers(idCustomer)
);

CREATE TABLE mdls(
   idModule BIGINT AUTO_INCREMENT,
   reference VARCHAR(255) ,
   moduleName TEXT ,
   module_subtitle VARCHAR(200) ,
   module_tag VARCHAR(150) ,
   module_image TEXT ,
   description LONGTEXT ,
   minApprenant INT,
   maxApprenant INT,
   dureeJ INT,
   dureeH INT ,
   moduleStatut TINYINT DEFAULT 0,
   idDomaine BIGINT NOT NULL,
   idCustomer BIGINT NOT NULL,
   idTypeModule INT NOT NULL,
   module_is_complete TINYINT DEFAULT 0,
   idLevel INT NOT NULL DEFAULT 1,
   PRIMARY KEY(idModule),
   FOREIGN KEY(idDomaine) REFERENCES domaine_formations(idDomaine),
   FOREIGN KEY(idCustomer) REFERENCES customers(idCustomer),
   FOREIGN KEY(idTypeModule) REFERENCES type_modules(idTypeModule),
   FOREIGN KEY(idLevel) REFERENCES module_levels(idLevel)
);

CREATE TABLE projets(
   idProjet BIGINT AUTO_INCREMENT,
   referenceEtp VARCHAR(100) DEFAULT NULL,
   project_reference VARCHAR(100) ,
   project_title VARCHAR(200) ,
   projectName VARCHAR(50) ,
   dateDebut DATE,
   dateFin DATE,
   lieu TEXT DEFAULT NULL,
   idModule BIGINT NOT NULL,
   idBc BIGINT NOT NULL,
   idVilleCoded BIGINT NOT NULL,
   idCustomer BIGINT NOT NULL,
   idModalite INT NOT NULL,
   idTypeProjet INT NOT NULL,
   idSalle INT NOT NULL,
   project_description TEXT,
   project_num_fmfp VARCHAR(100),
   project_is_active TINYINT DEFAULT 0,
   project_is_reserved TINYINT DEFAULT 0,
   project_is_cancelled TINYINT DEFAULT 0,
   project_is_repported TINYINT DEFAULT 0,
   project_is_trashed TINYINT DEFAULT 0,
   project_is_closed TINYINT DEFAULT 0,
   project_is_archived TINYINT DEFAULT 0,
   project_price_pedagogique DECIMAL(15,2),
   project_price_annexe DECIMAL(15,2),
   total_ht DECIMAL(15,2) DEFAULT 0,
   total_ttc DECIMAL(15,2) DEFAULT 0,
   total_ht_sub_contractor DECIMAL(15,2) DEFAULT 0,
   total_ttc_sub_contractor DECIMAL(15,2) DEFAULT 0,
   created_at timestamp NULL DEFAULT NULL,
   updated_at timestamp NULL DEFAULT NULL,
   PRIMARY KEY(idProjet),
   FOREIGN KEY(idModule) REFERENCES mdls(idModule),
   FOREIGN KEY(idVilleCoded) REFERENCES ville_codeds(id),
   FOREIGN KEY(idCustomer) REFERENCES customers(idCustomer),
   FOREIGN KEY(idModalite) REFERENCES modalites(idModalite),
   FOREIGN KEY(idTypeProjet) REFERENCES type_projets(idTypeProjet),
   FOREIGN KEY(idSalle) REFERENCES salles(idSalle)
);

CREATE TABLE inters(
   idProjet BIGINT AUTO_INCREMENT,
   idPaiement INT NOT NULL,
   idCfp BIGINT NOT NULL,
   nbPlace TINYINT DEFAULT 0,
   PRIMARY KEY(idProjet),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idCfp) REFERENCES cfps(idCustomer)
);

CREATE TABLE intras(
   idProjet BIGINT AUTO_INCREMENT,
   idPaiement INT NOT NULL,
   idEtp BIGINT NOT NULL,
   idCfp BIGINT NOT NULL,
   PRIMARY KEY(idProjet),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idPaiement) REFERENCES paiements(idPaiement),
   FOREIGN KEY(idCfp) REFERENCES cfps(idCustomer)
);

CREATE TABLE internes(
   idProjet BIGINT AUTO_INCREMENT,
   idEtp BIGINT NOT NULL,
   PRIMARY KEY(idProjet),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idEtp) REFERENCES entreprises(idCustomer)
);

CREATE TABLE module_internes(
   idModule BIGINT AUTO_INCREMENT,
   PRIMARY KEY(idModule),
   FOREIGN KEY(idModule) REFERENCES mdls(idModule)
);

CREATE TABLE modules(
   idModule BIGINT AUTO_INCREMENT,
   prix DECIMAL(15,2) DEFAULT NULL,
   prixGroupe DECIMAL(15,2) DEFAULT NULL,
   PRIMARY KEY(idModule),
   FOREIGN KEY(idModule) REFERENCES mdls(idModule)
);

CREATE TABLE objectif_modules(
   idObjectif BIGINT AUTO_INCREMENT,
   objectif VARCHAR(255) ,
   idModule BIGINT NOT NULL,
   PRIMARY KEY(idObjectif),
   FOREIGN KEY(idModule) REFERENCES mdls(idModule)
);

CREATE TABLE cible_modules(
   idCible BIGINT AUTO_INCREMENT,
   cible VARCHAR(255) ,
   idModule BIGINT NOT NULL,
   PRIMARY KEY(idCible),
   FOREIGN KEY(idModule) REFERENCES mdls(idModule)
);

CREATE TABLE prestation_modules(
   idPrestation BIGINT AUTO_INCREMENT,
   prestation_name TEXT,
   idModule BIGINT NOT NULL,
   PRIMARY KEY(idPrestation),
   FOREIGN KEY(idModule) REFERENCES mdls(idModule)
);

CREATE TABLE particuliers(
   idParticulier BIGINT,
   PRIMARY KEY(idParticulier),
   FOREIGN KEY(idParticulier) REFERENCES users(id)
);

CREATE TABLE etp_singles(
   idEntreprise BIGINT,
   PRIMARY KEY(idEntreprise),
   FOREIGN KEY(idEntreprise) REFERENCES entreprises(idCustomer)
);

CREATE TABLE etp_groupes(
   idEntreprise BIGINT,
   PRIMARY KEY(idEntreprise),
   FOREIGN KEY(idEntreprise) REFERENCES entreprises(idCustomer)
);

CREATE TABLE etp_groupeds(
   idEntreprise BIGINT,
   idEntrepriseParent BIGINT NOT NULL,
   PRIMARY KEY(idEntreprise),
   FOREIGN KEY(idEntreprise) REFERENCES entreprises(idCustomer),
   FOREIGN KEY(idEntrepriseParent) REFERENCES etp_groupes(idEntreprise)
);

CREATE TABLE employes(
   idEmploye BIGINT,
   idFonction INT NOT NULL,
   idCustomer BIGINT NOT NULL,
   idSexe INT NOT NULL,
   idNiveau INT NOT NULL,
   created_at timestamp NULL DEFAULT NULL,
   updated_at timestamp NULL DEFAULT NULL,
   PRIMARY KEY(idEmploye),
   FOREIGN KEY(idEmploye) REFERENCES users(id),
   FOREIGN KEY(idFonction) REFERENCES fonctions(idFonction),
   FOREIGN KEY(idCustomer) REFERENCES customers(idCustomer),
   FOREIGN KEY(idSexe) REFERENCES sexes(idSexe),
   FOREIGN KEY(idNiveau) REFERENCES niveau_etudes(idNiveau)
);

-- miditra ato ny apprenant na employe nampidirin'ny CFP
CREATE TABLE c_emps(
   idEmploye BIGINT,
   id_cfp BIGINT NOT NULL,
   PRIMARY KEY(idEmploye),
   FOREIGN KEY(idEmploye) REFERENCES employes(idEmploye),
   FOREIGN KEY(id_cfp) REFERENCES cfps(idCustomer)
);

CREATE TABLE seances(
   idSeance BIGINT AUTO_INCREMENT,
   dateSeance DATE,
   heureDebut TIME,
   heureFin TIME,
   intervalle VARCHAR(30),
   id_google_seance TEXT,
   idProjet BIGINT NOT NULL,
   PRIMARY KEY(idSeance),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet)
);

CREATE TABLE apprenants(
   idEmploye BIGINT,
   PRIMARY KEY(idEmploye),
   FOREIGN KEY(idEmploye) REFERENCES employes(idEmploye)
);

CREATE TABLE frais_vies(
   idFraisVie INT AUTO_INCREMENT,
   fv_name VARCHAR(200) ,
   fv_description VARCHAR(250) ,
   idCfp BIGINT NOT NULL,
   PRIMARY KEY(idFraisVie),
   FOREIGN KEY(idCfp) REFERENCES cfps(idCustomer)
);

CREATE TABLE formateur_internes(
   idFormateur BIGINT,
   idEmploye BIGINT,
   idEntreprise BIGINT NOT NULL,
   PRIMARY KEY(idFormateur, idEmploye),
   FOREIGN KEY(idFormateur) REFERENCES forms(idFormateur),
   FOREIGN KEY(idEmploye) REFERENCES employes(idEmploye),
   FOREIGN KEY(idEntreprise) REFERENCES entreprises(idCustomer)
);

CREATE TABLE module_ressources(
   idModuleRessource BIGINT AUTO_INCREMENT,
   module_ressource_name VARCHAR(200) ,
   module_ressource_extension VARCHAR(5),
   idModule BIGINT NOT NULL,
   PRIMARY KEY(idModuleRessource),
   FOREIGN KEY(idModule) REFERENCES mdls(idModule)
);

CREATE TABLE project_images(
   idProjetImage BIGINT AUTO_INCREMENT,
   project_image_name VARCHAR(50) ,
   idProjet BIGINT NOT NULL,
   PRIMARY KEY(idProjetImage),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet)
);



CREATE TABLE programmes(
   idProgramme BIGINT AUTO_INCREMENT,
   program_title VARCHAR(255) ,
   program_description TEXT,
   idModule BIGINT NOT NULL,
   PRIMARY KEY(idProgramme),
   FOREIGN KEY(idModule) REFERENCES mdls(idModule)
);

CREATE TABLE `role_users` (
  `id` bigint NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `isActive` tinyint NOT NULL DEFAULT 1,
  `hasRole` tinyint NOT NULL DEFAULT 0,
  `role_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
   user_is_in_service TINYINT DEFAULT 1,
   created_at timestamp NULL DEFAULT NULL,
   updated_at timestamp NULL DEFAULT NULL,
  CONSTRAINT role_users_roles_FK FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT role_users_users_FK FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE cfp_etps(
   idEtp BIGINT,
   idCfp BIGINT,
   dateCollaboration DATE,
   activiteEtp BOOLEAN,
   activiteCfp BOOLEAN,
   isSent TINYINT DEFAULT 0,
   PRIMARY KEY(idEtp, idCfp),
   FOREIGN KEY(idEtp) REFERENCES entreprises(idCustomer),
   FOREIGN KEY(idCfp) REFERENCES cfps(idCustomer)
);

CREATE TABLE eval_chauds(
   idEval_chaud BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
   idProjet BIGINT,
   idEmploye BIGINT,
   idQuestion BIGINT,
   idExaminer BIGINT,
   note INT,
   com1 TEXT ,
   com2 TEXT ,
   idValComment TEXT,
   generalApreciate INT,
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idEmploye) REFERENCES apprenants(idEmploye),
   FOREIGN KEY(idQuestion) REFERENCES questions(idQuestion),
   FOREIGN KEY(idExaminer) REFERENCES users(id)
);

CREATE TABLE detail_abns(
   id BIGINT PRIMARY KEY AUTO_INCREMENT,
   idAbn BIGINT,
   idCustomer BIGINT,
   dateDemande DATE,
   dateDebut DATE,
   dateFin DATE,
   isActive TINYINT DEFAULT 0,
   isDisable TINYINT DEFAULT 0,
   isExpired TINYINT DEFAULT 0,
   isStoped TINYINT DEFAULT 0,
   FOREIGN KEY(idAbn) REFERENCES abonnements(idAbn),
   FOREIGN KEY(idCustomer) REFERENCES customers(idCustomer)
);

CREATE TABLE eval_froids(
   id BIGINT PRIMARY KEY AUTO_INCREMENT,
   idProjet BIGINT,
   idEmploye BIGINT,
   idQuizzCold BIGINT,
   date_added DATE,
   general_satisfaction TINYINT,
   general_recomand TINYINT,
   general_aspect TEXT,
   general_suggestion TEXT,
   note TINYINT,
   description TEXT DEFAULT NULL,
   FOREIGN KEY(idQuizzCold) REFERENCES quizz_colds(id),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idEmploye) REFERENCES apprenants(idEmploye)
);


CREATE TABLE inter_entreprises(
   id BIGINT AUTO_INCREMENT,
   idProjet BIGINT,
   idEtp BIGINT,
   isActiveInter TINYINT DEFAULT 0,
   nbPlaceReserved TINYINT DEFAULT 0,
   PRIMARY KEY(id),
   FOREIGN KEY(idProjet) REFERENCES inters(idProjet),
   FOREIGN KEY(idEtp) REFERENCES entreprises(idCustomer)
);

CREATE TABLE cfp_formateurs(
   idFormateur BIGINT,
   idCfp BIGINT,
   dateCollaboration DATE,
   isActiveFormateur BOOLEAN,
   isActiveCfp BOOLEAN,
   PRIMARY KEY(idFormateur, idCfp),
   FOREIGN KEY(idFormateur) REFERENCES formateurs(idFormateur),
   FOREIGN KEY(idCfp) REFERENCES cfps(idCustomer)
);

CREATE TABLE detail_apprenants(
   idProjet BIGINT,
   idEmploye BIGINT,
   nbAppr INTEGER(20),
   id_cfp_appr BIGINT,
   PRIMARY KEY(idProjet, idEmploye),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idEmploye) REFERENCES employes(idEmploye)
);

CREATE TABLE emargements(
   idSeance BIGINT,
   idEmploye BIGINT,
   idProjet BIGINT,
   isPresent TINYINT DEFAULT 0,
   nbPresent INTEGER(20),
   PRIMARY KEY(idSeance, idEmploye, idProjet),
   FOREIGN KEY(idSeance) REFERENCES seances(idSeance),
   FOREIGN KEY(idEmploye) REFERENCES apprenants(idEmploye),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet)
);

CREATE TABLE project_materials(
   idProjet BIGINT,
   idMateriel BIGINT,
   idRespMateriel INT,
   nombre INT,
   PRIMARY KEY(idProjet, idMateriel, idRespMateriel),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idMateriel) REFERENCES materiel_cfps(idMateriel),
   FOREIGN KEY(idRespMateriel) REFERENCES resp_materiels(idRespMateriel)
);

CREATE TABLE project_forms(
   idProjet BIGINT,
   idFormateur BIGINT,
   PRIMARY KEY(idProjet, idFormateur),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idFormateur) REFERENCES forms(idFormateur)
);

CREATE TABLE IF NOT EXISTS formateur_projet (
   id BIGINT AUTO_INCREMENT PRIMARY KEY,
   idProjet BIGINT NOT NULL,
   idFormateur BIGINT NOT NULL,
   FOREIGN KEY (idProjet) REFERENCES projets(idProjet) ON DELETE CASCADE ON UPDATE CASCADE,
   FOREIGN KEY (idFormateur) REFERENCES forms(idFormateur) ON DELETE CASCADE ON UPDATE CASCADE
);



-- Mini CV

CREATE TABLE experiences (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idFormateur BIGINT NOT NULL,
    Lieu_de_stage VARCHAR(200),
    Fonction VARCHAR(200),
    Date_debut DATE,
    Date_fin DATE,
    Lieu VARCHAR(200)
);

CREATE TABLE diplomes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idFormateur BIGINT NOT NULL,
    Ecole VARCHAR(200),
    Diplome VARCHAR(200),
    Domaine VARCHAR(200),
    Date_debut DATE,
    Date_fin DATE
);

CREATE TABLE competences (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idFormateur BIGINT NOT NULL,
    note INT,
    Competence VARCHAR(200)
);

CREATE TABLE langues (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idFormateur BIGINT NOT NULL,
    note INT,
    Langue VARCHAR(200)
);

   CREATE TABLE detail_apprenant_inters(
      idProjet BIGINT,
      idEtp BIGINT,
      idEmploye BIGINT,
      id_cfp_appr BIGINT,
      PRIMARY KEY(idProjet, idEtp, idEmploye),
      FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
      FOREIGN KEY(idEtp) REFERENCES entreprises(idCustomer),
      FOREIGN KEY(idEmploye) REFERENCES apprenants(idEmploye)
   );

CREATE TABLE prerequis_modules(
   idPrerequis BIGINT AUTO_INCREMENT,
   prerequis_name TEXT,
   idModule BIGINT NOT NULL,
   PRIMARY KEY(idPrerequis),
   FOREIGN KEY(idModule) REFERENCES mdls(idModule)
);

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `dedicate` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`dedicate`)),
  `slug` varchar(255) NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `user_type` varchar(255) NOT NULL,
  `is_recommander` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `signup_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL,
  `trial_period` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `trial_interval` varchar(255) NOT NULL DEFAULT 'day',
  `invoice_period` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `invoice_interval` varchar(255) NOT NULL DEFAULT 'month',
  `grace_period` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `grace_interval` varchar(255) NOT NULL DEFAULT 'day',
  `prorate_day` tinyint(3) UNSIGNED DEFAULT NULL,
  `prorate_period` tinyint(3) UNSIGNED DEFAULT NULL,
  `prorate_extend_due` tinyint(3) UNSIGNED DEFAULT NULL,
  `active_subscribers_limit` smallint(5) UNSIGNED DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
);
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plans_slug_unique` (`slug`);
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

CREATE TABLE `features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `slug` varchar(255) NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `value` varchar(255) NOT NULL,
  `resettable_period` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `resettable_interval` varchar(255) NOT NULL DEFAULT 'month',
  `sort_order` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
);
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `features_slug_unique` (`slug`);
ALTER TABLE `features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE projets
ADD COLUMN idBc INT NULL AFTER idProjet,
ADD CONSTRAINT fk_bc_projets FOREIGN KEY (idBc) REFERENCES bon_commandes(idBC);

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscriber_type` varchar(255) NOT NULL,
  `subscriber_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `slug` varchar(255) NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `timezone` varchar(255) DEFAULT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `cancels_at` datetime DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
);

ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscriptions_slug_unique` (`slug`),
  ADD KEY `subscriptions_subscriber_type_subscriber_id_index` (`subscriber_type`,`subscriber_id`);
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


CREATE TABLE `subscription_usage` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `feature_id` bigint(20) UNSIGNED NOT NULL,
  `used` smallint(5) UNSIGNED NOT NULL,
  `timezone` varchar(255) DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
);

ALTER TABLE `subscription_usage`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `subscription_usage`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    due_date DATE NOT NULL,
    payment_date DATE NOT NULL,
    user_id INT(11) NOT NULL,
    subscription_name VARCHAR(255) NOT NULL,
    payment_method VARCHAR(255) NOT NULL,
    total_price DECIMAL(15, 2) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);




-- TABLE FRAIS PROJET
CREATE TABLE `frais` (
  `idFrais` bigint(20) NOT NULL AUTO_INCREMENT,
  `Frais` varchar(255) DEFAULT NULL,
  `exemple` text DEFAULT NULL,
  PRIMARY KEY (`idFrais`)
);


CREATE TABLE fraisprojet(
   idFraisProjet BIGINT PRIMARY KEY AUTO_INCREMENT,
   idProjet BIGINT,
   idFrais BIGINT,
   montant DECIMAL(15,2) NOT NULL,
   datefrais TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   description VARCHAR(200),
   FOREIGN KEY (idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY (idFrais) REFERENCES frais(idFrais)
);

CREATE TABLE particulier_projet (
    idParticulier BIGINT NOT NULL,
    idProjet BIGINT NOT NULL,
    date_attribution DATE,
    PRIMARY KEY(idParticulier, idProjet),
    FOREIGN KEY(idParticulier) REFERENCES particuliers(idParticulier),
    FOREIGN KEY(idProjet) REFERENCES projets(idProjet)
);

ALTER TABLE fraisprojet add COLUMN isEtp int DEFAULT 0;
ALTER TABLE fraisprojet add COLUMN idPayeur BIGINT;

ALTER TABLE projets add COLUMN total_ht_etp DECIMAL DEFAULT 0;
ALTER TABLE projets add COLUMN total_ttc_etp DECIMAL DEFAULT 0;


CREATE TABLE type_images(
   idTypeImage BIGINT PRIMARY KEY AUTO_INCREMENT,
   typeImage VARCHAR(200) NOT NULL
);


CREATE TABLE images(
   idImages BIGINT PRIMARY KEY AUTO_INCREMENT,
   idTypeImage BIGINT,
   idProjet BIGINT,
   url TEXT,
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idTypeImage) REFERENCES type_images(idTypeImage)
);

ALTER TABLE images add COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE images add COLUMN nomImage VARCHAR(255) AFTER url;
ALTER TABLE images add COLUMN path TEXT AFTER nomImage;

ALTER TABLE images add COLUMN mediaType VARCHAR(20) DEFAULT 'image';

ALTER TABLE images add COLUMN id_added_by bigint(20) DEFAULT NULL AFTER nomImage;



ALTER TABLE inter_entreprises ADD COLUMN idEntrepriseParent BIGINT;
ALTER TABLE payments ADD COLUMN id_order VARCHAR(255);

ALTER TABLE users ADD COLUMN remember_token VARCHAR(100) AFTER password;

CREATE TABLE password_resets(
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL
);

ALTER TABLE projets ADD COLUMN taxe DECIMAL DEFAULT 0; -- valeur du TVA ( 20 % )

-- ALTER TABLE inters ADD COLUMN nbPlace TINYINT DEFAULT 0;

-- ALTER TABLE `inter_entreprises` CHANGE `nbPlace` `nbPlaceReserved` TINYINT(4) NULL DEFAULT '0'; 

CREATE TABLE restaurations(
   idRestauration TINYINT PRIMARY KEY AUTO_INCREMENT,
   typeRestauration VARCHAR(50) NOT NULL
);

CREATE TABLE project_restaurations(
	idProjet BIGINT,
   idRestauration TINYINT,
 	paidBy INT,
    FOREIGN KEY (idProjet) REFERENCES projets(idProjet),
    FOREIGN KEY (idRestauration) REFERENCES restaurations(idRestauration),
    FOREIGN KEY (paidBy) REFERENCES type_customers(idTypeCustomer)
);

ALTER TABLE inters ADD COLUMN project_inter_privacy TINYINT DEFAULT 0;


CREATE TABLE dossiers(
   idDossier BIGINT PRIMARY KEY AUTO_INCREMENT,
   nomDossier VARCHAR(200) UNIQUE,
   idCfp BIGINT,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   FOREIGN KEY(idCfp) REFERENCES cfps(idCustomer)
);

CREATE TABLE section_documents (
   idSectionDocument BIGINT PRIMARY KEY AUTO_INCREMENT,
   section_document VARCHAR(200)
);

CREATE TABLE type_documents (
   idTypeDocument BIGINT PRIMARY KEY AUTO_INCREMENT,
   idSectionDocument BIGINT,
   type_document VARCHAR(200),
   FOREIGN KEY (idSectionDocument) REFERENCES section_documents(idSectionDocument)
);

CREATE TABLE documents(
   idDocument BIGINT PRIMARY KEY AUTO_INCREMENT,
   titre VARCHAR(200),
   path TEXT,
   idDossier BIGINT,
   filename VARCHAR(200),
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   FOREIGN KEY(idDossier) REFERENCES dossiers(idDossier)
);

ALTER TABLE documents
ADD COLUMN idTypeDocument BIGINT,
ADD FOREIGN KEY (idTypeDocument) REFERENCES type_documents(idTypeDocument);

ALTER TABLE seances ADD COLUMN isDone BOOLEAN DEFAULT 0;

ALTER TABLE projets 
ADD COLUMN idDossier BIGINT,
ADD FOREIGN KEY (idDossier) REFERENCES dossiers(idDossier);

ALTER TABLE dossiers 
ADD COLUMN note text;

-- ALTER TABLE `invoices` CHANGE `invoice_is_paid` `invoice_status` TINYINT(4) NULL DEFAULT '0';

-- ALTER TABLE invoices ADD COLUMN invoice_total_amount decimal(15,2) NOT NULL DEFAULT 0 AFTER invoice_status;
-- ALTER TABLE invoice_details ADD COLUMN item_total_price decimal(15,2) NOT NULL DEFAULT 0 AFTER item_unit_price;

CREATE TABLE invoice_status(
   idInvoiceStatus BIGINT PRIMARY KEY AUTO_INCREMENT,
   invoice_status_name VARCHAR(50)
);

CREATE TABLE eval_apprenant (
    id MEDIUMINT NOT NULL AUTO_INCREMENT,
    idEmploye int,
    idProjet int,
    avant TINYINT,
    apres TINYINT,
    PRIMARY KEY(id)
);

ALTER TABLE documents
ADD COLUMN taille DECIMAL(15, 2);

-- ilay "idSubContractor" io no id an'ilay sous-traitant
CREATE TABLE project_sub_contracts(
   idProjet BIGINT,
   idSubContractor BIGINT,
   PRIMARY KEY(idProjet, idSubContractor),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idSubContractor) REFERENCES cfps(idCustomer)
);

CREATE TABLE sub_contractors(
   idSubContractor BIGINT,
   idCfp BIGINT,
   PRIMARY KEY(idSubContractor, idCfp),
   FOREIGN KEY(idSubContractor) REFERENCES customers(idCustomer),
   FOREIGN KEY(idCfp) REFERENCES cfps(idCustomer)
);

ALTER TABLE module_ressources
ADD COLUMN file_path TEXT;

ALTER TABLE module_ressources
ADD COLUMN taille DECIMAL(15, 2);

CREATE TABLE cfp_selected_by_admin(
   idSuperAdmin BIGINT,
   idCfp BIGINT,
   date_added DATE,
   PRIMARY KEY(idSuperAdmin, idCfp),
   FOREIGN KEY(idSuperAdmin) REFERENCES users(id),
   FOREIGN KEY(idCfp) REFERENCES cfps(idCustomer)
);

CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

ALTER TABLE users ADD COLUMN user_is_deleted int DEFAULT 0; 

CREATE TABLE ignoredConflitLieu (
   idSalle INT,
   idProjet BIGINT,
   PRIMARY KEY(idSalle,idProjet),
   FOREIGN KEY (idSalle) REFERENCES salles (idSalle),
   FOREIGN KEY (idProjet) REFERENCES projets (idProjet)
);

CREATE TABLE ignoredConflitFormateur (
   idFormateur BIGINT,
   idProjet BIGINT,
   PRIMARY KEY (idFormateur,idProjet),
   FOREIGN KEY(idFormateur) REFERENCES users(id),
   FOREIGN KEY (idProjet) REFERENCES projets (idProjet)
); 

/**
Testing Center
*/
CREATE TABLE `categories_reponses` (
  `idCategorie` bigint(20) NOT NULL AUTO_INCREMENT,
  `nomCategorie` varchar(50) NOT NULL,
  `descriptionCategorie` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idCategorie`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- lucid.qcm definition

CREATE TABLE `qcm` (
  `idQCM` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `intituleQCM` varchar(200) NOT NULL,
  `descriptionQCM` text DEFAULT NULL,
  `idDomaine` bigint(20) DEFAULT NULL,
  `prixUnitaire` decimal(10,2) NOT NULL COMMENT 'En crédit mais pas en monnaie réel',
  `statut` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 pour inactif, 1 pour actif',
  `duree` int(11) NOT NULL COMMENT 'Durée en secondes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idQCM`),
  KEY `idDomaine` (`idDomaine`),
  KEY `fk_qcm_user` (`user_id`),
  CONSTRAINT `fk_qcm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `qcm_ibfk_1` FOREIGN KEY (`idDomaine`) REFERENCES `domaine_formations` (`idDomaine`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Table pour stockées la branche principale d''un système de quiz';


-- lucid.qcm_bareme definition

CREATE TABLE `qcm_bareme` (
  `idBareme` bigint(20) NOT NULL AUTO_INCREMENT,
  `idQCM` bigint(20) DEFAULT NULL,
  `minPoints` int(11) NOT NULL COMMENT 'Nombre minimum de points pour ce niveau',
  `maxPoints` int(11) NOT NULL COMMENT 'Nombre maximum de points pour ce niveau',
  `niveau` varchar(50) NOT NULL COMMENT 'Niveau atteint pour cette plage de points',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idBareme`),
  KEY `idQCM` (`idQCM`),
  CONSTRAINT `fk_qcm_bareme_qcm` FOREIGN KEY (`idQCM`) REFERENCES `qcm` (`idQCM`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Barème des points pour chaque QCM';


-- lucid.qcm_questions definition

CREATE TABLE `qcm_questions` (
  `idQuestion` bigint(20) NOT NULL AUTO_INCREMENT,
  `idQCM` bigint(20) DEFAULT NULL,
  `texteQuestion` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idQuestion`),
  KEY `idQCM` (`idQCM`),
  CONSTRAINT `qcm_questions_ibfk_1` FOREIGN KEY (`idQCM`) REFERENCES `qcm` (`idQCM`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Table pour stocker les questions d''un quiz';


-- lucid.qcm_reponses definition

CREATE TABLE `qcm_reponses` (
  `idReponse` bigint(20) NOT NULL AUTO_INCREMENT,
  `idQuestion` bigint(20) DEFAULT NULL,
  `categorie_id` bigint(20) DEFAULT NULL,
  `texteReponse` text NOT NULL,
  `points` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idReponse`),
  KEY `idQuestion` (`idQuestion`),
  KEY `fk_categorie` (`categorie_id`),
  CONSTRAINT `fk_categorie` FOREIGN KEY (`categorie_id`) REFERENCES `categories_reponses` (`idCategorie`),
  CONSTRAINT `qcm_reponses_ibfk_1` FOREIGN KEY (`idQuestion`) REFERENCES `qcm_questions` (`idQuestion`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Table pour stocker les réponses d''une question dans un quiz';


-- lucid.sessions_test definition

CREATE TABLE `sessions_test` (
  `idSession` bigint(20) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` bigint(20) DEFAULT NULL,
  `idQCM` bigint(20) DEFAULT NULL,
  `dateDebut` datetime NOT NULL,
  `dateFin` datetime DEFAULT NULL,
  `totalPoints` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idSession`),
  KEY `idQCM` (`idQCM`),
  KEY `sessions_test_ibfk_2` (`idUtilisateur`),
  CONSTRAINT `sessions_test_ibfk_1` FOREIGN KEY (`idQCM`) REFERENCES `qcm` (`idQCM`) ON DELETE SET NULL,
  CONSTRAINT `sessions_test_ibfk_2` FOREIGN KEY (`idUtilisateur`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Table pour stockées les sessions de test d''un utilisateur';


-- lucid.reponses_utilisateurs definition

CREATE TABLE `reponses_utilisateurs` (
  `idReponseUtilisateur` bigint(20) NOT NULL AUTO_INCREMENT,
  `idSession` bigint(20) DEFAULT NULL,
  `idQuestion` bigint(20) DEFAULT NULL,
  `idReponse` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idReponseUtilisateur`),
  KEY `idSession` (`idSession`),
  KEY `idQuestion` (`idQuestion`),
  KEY `idReponse` (`idReponse`),
  CONSTRAINT `reponses_utilisateurs_ibfk_1` FOREIGN KEY (`idSession`) REFERENCES `sessions_test` (`idSession`) ON DELETE SET NULL,
  CONSTRAINT `reponses_utilisateurs_ibfk_2` FOREIGN KEY (`idQuestion`) REFERENCES `qcm_questions` (`idQuestion`) ON DELETE SET NULL,
  CONSTRAINT `reponses_utilisateurs_ibfk_3` FOREIGN KEY (`idReponse`) REFERENCES `qcm_reponses` (`idReponse`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `credits_wallet` (
  `idWallet` bigint(20) NOT NULL AUTO_INCREMENT,
  `idUser` bigint(20) DEFAULT NULL,
  `solde` decimal(10,2) NOT NULL COMMENT 'Solde de l''utilisateur en crédits',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idWallet`),
  KEY `credits_wallet_users_FK` (`idUser`),
  CONSTRAINT `credits_wallet_users_FK` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Portefeuille des crédits de l''utilisateur';

-- table pour les historiques de transactions de crédits
CREATE TABLE `transaction_history` (
  `idTransaction` bigint(20) NOT NULL AUTO_INCREMENT,
  `idUser` bigint(20) DEFAULT NULL,
  `transaction_ref` varchar(250) DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `typeTransaction` enum('credit','debit') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idTransaction`),
  KEY `transaction_history_users_FK` (`idUser`),
  CONSTRAINT `transaction_history_users_FK` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`)
);

-- Table pour stocker les packs de crédits vendus
CREATE TABLE `credits_packs` (
  `idPackCredit` bigint(20) NOT NULL AUTO_INCREMENT,
  `type_pack` varchar(255) DEFAULT NULL COMMENT 'Type du pack de crédits',
  `description_pack` varchar(255) DEFAULT NULL COMMENT 'Description du pack de crédits',
  `credits` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Montant de crédits',
  `pack_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Prix du pack de crédits',
  `currency` varchar(255) DEFAULT NULL COMMENT 'devise du prix',
  `is_active` tinyint(1) DEFAULT NULL COMMENT 'situation du pack de crédits actuellement (0 si inactif et 1 si actif)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idPackCredit`)
);

-- Enregistre les achats de crédits par chèque, virement bancaire, carte bancaire (mbola tsy nampiakarina)
CREATE TABLE `credits_payments` (
   `idCreditPayment` bigint(20) NOT NULL AUTO_INCREMENT,
   `user_id` bigint(20) DEFAULT NULL,
   `pack_credits_id` bigint(20) DEFAULT NULL COMMENT 'Id du pack de credits acheter',
   `reference` varchar(255) DEFAULT NULL COMMENT 'Référence du paiement',
   `amount_paid` decimal(10,2) NOT NULL COMMENT 'Montant payer par l''utilisateur pour acheter les packs de crédits',
   `currency` varchar(100) DEFAULT NULL,
   `payment_type` varchar(255) DEFAULT NULL COMMENT 'Méthode de payement de l''utilisateur soit chèque, virement bancaire, carte bancaire (cb)',
   `status` varchar(255) DEFAULT NULL COMMENT 'Status du paiement soit pending, canceled, paid',
   `created_at` timestamp NULL DEFAULT current_timestamp(),
   `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`idCreditPayment`),
   KEY `users_buy_credits_FK` (`user_id`),
   KEY `credits_payments_credits_packs_FK` (`pack_credits_id`),
   CONSTRAINT `credits_payments_credits_packs_FK` FOREIGN KEY (`pack_credits_id`) REFERENCES `credits_packs` (`idPackCredit`),
   CONSTRAINT `users_buy_credits_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);

-- table pour stocker les débits de crédits d'une entreprise par ses employés après avoir fait un qcm
CREATE TABLE `emp_debit_credit` (
  `idDebitEmpEtp` bigint(20) NOT NULL AUTO_INCREMENT,
  `idTransaction` bigint(20) DEFAULT NULL COMMENT 'Cle etrangere lie a la table transaction_history',
  `idUser` bigint(20) DEFAULT NULL COMMENT 'Cle etrangere lie a la table users',
  `description` varchar(255) DEFAULT NULL COMMENT 'Description du debit de credits',
  `montant` decimal(10,2) NOT NULL COMMENT 'Montant de credit debiter par les employes de l entreprise',
  `typeTransaction` enum('credit','debit') NOT NULL COMMENT 'Type du pack de credits',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idDebitEmpEtp`),
  KEY `transaction_history_emp_etp_users_FK` (`idUser`),
  CONSTRAINT `transaction_history_emp_etp_transaction_FK` FOREIGN KEY (`idTransaction`) REFERENCES `transaction_history` (`idTransaction`),
  CONSTRAINT `transaction_history_emp_etp_users_FK` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`)
);

-- table pour stocker les invitations à faire un qcm
CREATE TABLE `qcm_invitations` (
  `idInvitation` bigint(20) NOT NULL AUTO_INCREMENT,
  `idQCM` bigint(20) NULL,
  `idEmployeur` bigint(20) NULL,
  `idEmploye` bigint(20) NULL,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `custom_message` text DEFAULT NULL,
  `status` enum('pending','accepted','expired') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idInvitation`),
  KEY `fk_qcm` (`idQCM`),
  KEY `fk_employe` (`idEmploye`),
  CONSTRAINT `fk_employe` FOREIGN KEY (`idEmploye`) REFERENCES `employes` (`idEmploye`),
  CONSTRAINT `fk_employeur` FOREIGN KEY (`idEmployeur`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_qcm` FOREIGN KEY (`idQCM`) REFERENCES `qcm` (`idQCM`)
);

-- table pour stocker les paramètres de commissions
CREATE TABLE `commissions_settings` (
  `idCommissionSetting` bigint(20) NOT NULL AUTO_INCREMENT,
  `payment_type` varchar(255) DEFAULT NULL COMMENT 'Méthode de paiement (chèque, virement bancaire, cb)',
  `commission_rate` decimal(5,2) DEFAULT NULL COMMENT 'Taux de commission en pourcentage',
  `currency` varchar(100) DEFAULT NULL COMMENT 'Devise associée à ce paramètre',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idCommissionSetting`),
  UNIQUE KEY `unique_commission_setting` (`payment_type`, `currency`)
);

-- table pour stocker les commissions reçues après une transaction
CREATE TABLE `commissions_received` (
  `idCommissionReceived` bigint(20) NOT NULL AUTO_INCREMENT,
  `credit_payment_id` bigint(20) NULL COMMENT 'Référence vers le paiement de crédits',
  `commission_rate` decimal(5,2) NULL COMMENT 'Taux de commission appliqué',
  `total_commission` decimal(10,2) DEFAULT NULL COMMENT 'Montant total de la commission',
  `currency` varchar(100) DEFAULT NULL COMMENT 'Devise utilisée pour cette commission',
  `receiver_id` bigint(20) DEFAULT NULL COMMENT 'Utilisateur recevant la commission si applicable',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idCommissionReceived`),
  KEY `commissions_received_credits_payments_FK` (`credit_payment_id`),
  CONSTRAINT `commissions_received_credits_payments_FK` FOREIGN KEY (`credit_payment_id`) REFERENCES `credits_payments` (`idCreditPayment`),
  CONSTRAINT `commissions_receiver_FK` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`)
);

-- table pour stocker les campagnes d'invitations
CREATE TABLE `qcm_invit_camps` (
    `idInvitCamp` bigint(20) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) DEFAULT NULL,
    `created_date` DATE DEFAULT NULL,
    `created_by` bigint(20) NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`idInvitCamp`),
    CONSTRAINT `campaign_creator_FK` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
);

-- table pivot pour les campagnes d'invitations
CREATE TABLE `qcm_invit_camp_invitations` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `invit_camp_id` bigint(20) NULL,
    `invitation_id` bigint(20) NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`invit_camp_id`) REFERENCES `qcm_invit_camps` (`idInvitCamp`),
    FOREIGN KEY (`invitation_id`) REFERENCES `qcm_invitations` (`idInvitation`)
);

/**
Testing Center
*/

CREATE TABLE type_publicites (
   id INT AUTO_INCREMENT NOT NULL,
   type_pub_name VARCHAR(50),
   PRIMARY KEY (id)
);

CREATE TABLE publicites (
   id BIGINT AUTO_INCREMENT NOT NULL,
   idModule BIGINT,
   date_ajout DATE,
   rang_affichage INT,
   is_active INT DEFAULT 0,
   idType INT NOT NULL,
   PRIMARY KEY (id),
   FOREIGN KEY (idType) REFERENCES type_publicites(id),
   FOREIGN KEY (idModule) REFERENCES mdls(idModule)
);


CREATE TABLE pub_simples(
   id BIGINT,
   PRIMARY KEY(id),
   FOREIGN KEY(id) REFERENCES publicites(id)
);

ALTER TABLE formateurs ADD COLUMN form_titre VARCHAR(200) DEFAULT NULL;
ALTER TABLE formateurs ADD COLUMN form_speciality VARCHAR(200) DEFAULT NULL;

-- modification lieux et salles
ALTER TABLE customers DROP COLUMN IF EXISTS customer_addr_code_postal;

CREATE TABLE agences(
   idAgence BIGINT AUTO_INCREMENT,
   ag_name VARCHAR(150) ,
   idVilleCoded BIGINT NOT NULL,
   idCustomer BIGINT NOT NULL,
   PRIMARY KEY(idAgence),
   FOREIGN KEY(idVilleCoded) REFERENCES ville_codeds(id),
   FOREIGN KEY(idCustomer) REFERENCES customers(idCustomer)
);

CREATE TABLE lieu_privates(
   idLieu BIGINT,
   idCustomer BIGINT NOT NULL,
   PRIMARY KEY(idLieu),
   FOREIGN KEY(idLieu) REFERENCES lieux(idLieu),
   FOREIGN KEY(idCustomer) REFERENCES customers(idCustomer)
);

CREATE TABLE lieu_publics(
   idLieu BIGINT,
   PRIMARY KEY(idLieu),
   FOREIGN KEY(idLieu) REFERENCES lieux(idLieu)
);
-- fin modifications villes, lieux et salles

CREATE TABLE batches (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255),
    customer_id BIGINT NOT NULL,
    
    PRIMARY KEY (id),
    
    FOREIGN KEY (customer_id)
    REFERENCES customers(idCustomer)
);

CREATE TABLE batch_learners (
    id INT NOT NULL AUTO_INCREMENT,
    batch_id INT NOT NULL,
    employe_id BIGINT NOT NULL,
    
    PRIMARY KEY(id),

    FOREIGN KEY (batch_id)
      REFERENCES batches(id),

    FOREIGN KEY (employe_id)
      REFERENCES employes(idEmploye)
);

ALTER TABLE documents ADD COLUMN extension VARCHAR(10);

CREATE TABLE bankacounts(
   id BIGINT NOT NULL AUTO_INCREMENT,
   ba_idCustomer BIGINT NOT NULL,
   ba_titulaire VARCHAR(50) ,
   ba_name VARCHAR(50),
   ba_idPostal BIGINT,
   ba_quartier VARCHAR(50),
   ba_account_number VARCHAR(50) ,
   PRIMARY KEY(id),
   FOREIGN KEY(ba_idCustomer) REFERENCES customers(idCustomer),
   FOREIGN KEY(ba_idPostal) REFERENCES ville_codeds(id)
);

CREATE TABLE place_etp_from_cfps(
   idLieu BIGINT,
   date_added DATE,
   idEntreprise BIGINT NOT NULL,
   idCfp BIGINT NOT NULL,
   PRIMARY KEY(idLieu),
   FOREIGN KEY(idLieu) REFERENCES lieux(idLieu),
   FOREIGN KEY(idEntreprise) REFERENCES entreprises(idCustomer),
   FOREIGN KEY(idCfp) REFERENCES cfps(idCustomer)
);

-- utilisateur iray vaovao natsofoka t@ ora farany...
-- na tsy ao anaty planning ary ny hampidirana azy...
CREATE TABLE customer_others(
   id BIGINT,
   PRIMARY KEY(id),
   FOREIGN KEY(id) REFERENCES users(id)
);

CREATE TABLE invoices(
   idInvoice BIGINT AUTO_INCREMENT,
   invoice_number VARCHAR(255),
   invoice_bc VARCHAR(255),
   invoice_date DATE,
   invoice_date_pm DATE,
   invoice_status TINYINT DEFAULT 0,
   invoice_condition VARCHAR(255),
   invoice_total_amount decimal(15,2) NOT NULL DEFAULT 0,
   idCustomer BIGINT,
   idEntreprise BIGINT NOT NULL,
   idPaiement BIGINT NOT NULL,
   idBankAcount BIGINT NULL,
   idTypeFacture INT NOT NULL,
   PRIMARY KEY(idInvoice),
   UNIQUE(invoice_number),
   FOREIGN KEY(idEntreprise) REFERENCES entreprises(idCustomer),
   FOREIGN KEY(idPaiement) REFERENCES mode_paiements(idPaiement),
   FOREIGN KEY(idTypeFacture) REFERENCES type_factures(idTypeFacture),
   FOREIGN KEY(idBankAcount) REFERENCES bankacounts(id)
);

CREATE TABLE invoice_details(
   idItem BIGINT AUTO_INCREMENT,
   item_qty SMALLINT,
   item_description VARCHAR(200),
   item_unit_price decimal(15,2) NOT NULL DEFAULT 0,
   item_total_price decimal(15,2) NOT NULL DEFAULT 0,
   idInvoice BIGINT NOT NULL,
   idUnite BIGINT NOT NULL,
   idProjet BIGINT NOT NULL,
   idItems BIGINT,
   PRIMARY KEY(idItem),
   FOREIGN KEY(idInvoice) REFERENCES invoices(idInvoice),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idUnite) REFERENCES unites(idUnite)
);

CREATE TABLE invoice_standards(
   idInvoice BIGINT,
   PRIMARY KEY(idInvoice),
   FOREIGN KEY(idInvoice) REFERENCES invoices(idInvoice)
);

CREATE TABLE invoice_acomptes(
   idInvoice BIGINT,
   percent INT,
   PRIMARY KEY(idInvoice),
   FOREIGN KEY(idInvoice) REFERENCES invoices(idInvoice)
);

CREATE TABLE invoice_payments(
   id BIGINT PRIMARY KEY AUTO_INCREMENT,
   invoice_id BIGINT,
   amount decimal(15,2) NOT NULL DEFAULT 0,
   payment_date DATE,
   payment_method_id BIGINT,
   payment_bank_id BIGINT,
   payment_mobilemoney_id BIGINT,
   payment_description  VARCHAR(50),
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   FOREIGN KEY(invoice_id) REFERENCES invoices(idInvoice),
   FOREIGN KEY (payment_method_id) REFERENCES pm_types(idTypePm),
   FOREIGN KEY (payment_bank_id) REFERENCES bankacounts(id),
   FOREIGN KEY (payment_mobilemoney_id) REFERENCES mobilemoneyacounts(id)
);

ALTER TABLE invoices ADD COLUMN invoice_sub_total decimal(15,2) NULL DEFAULT 0 AFTER invoice_status;
ALTER TABLE invoices ADD COLUMN invoice_reduction decimal(15,2) NULL DEFAULT 0 AFTER invoice_sub_total;
ALTER TABLE invoices ADD COLUMN invoice_tva decimal(15,2) NULL DEFAULT 0 AFTER invoice_reduction;
ALTER TABLE invoices ADD COLUMN invoice_letter VARCHAR(255) AFTER invoice_total_amount;

CREATE TABLE invoice_deleted (
   id BIGINT PRIMARY KEY AUTO_INCREMENT,
   idInvoice BIGINT,
   FOREIGN KEY (idInvoice) REFERENCES invoices (idInvoice) 
);

ALTER TABLE invoices ADD CONSTRAINT fk_idBankAcount FOREIGN KEY (idBankAcount) REFERENCES bankacounts(id);

CREATE TABLE opportunites(
   id BIGINT PRIMARY KEY AUTO_INCREMENT,
   idCustomer BIGINT,
   idVille BIGINT,
   idModule BIGINT,
   idEtp BIGINT,
   statut INT,
   nbPersonne INT,
   prix DECIMAL(15,2),
   dateDeb DATE,
   dateFin DATE,
   ref_name VARCHAR(250),
   ref_firstName VARCHAR(150),
   ref_email VARCHAR(100) UNIQUE,
   ref_phone VARCHAR(50),
   note VARCHAR(200)
);

ALTER TABLE projets ADD COLUMN link TEXT DEFAULT NULL;
ALTER TABLE projets ADD COLUMN secret_code VARCHAR(250) DEFAULT NULL;

CREATE TABLE prospects(
   id BIGINT PRIMARY KEY AUTO_INCREMENT,
   prospect_name VARCHAR(250)
);

ALTER TABLE opportunites ADD COLUMN id_prospect BIGINT DEFAULT NULL;
ALTER TABLE opportunites ADD COLUMN source VARCHAR(200);

ALTER TABLE opportunites ADD COLUMN opportunitie_is_win TINYINT DEFAULT 0;
ALTER TABLE opportunites ADD COLUMN opportunitie_is_lost TINYINT DEFAULT 0;
ALTER TABLE opportunites ADD COLUMN opportunitie_is_standBy TINYINT DEFAULT 0;
ALTER TABLE opportunites ADD COLUMN position INT DEFAULT 0;

ALTER TABLE invoices DROP CONSTRAINT invoices_ibfk_1;
ALTER TABLE prospects ADD COLUMN idCustomer BIGINT;

-- miditra ato ny apprenant nampidirin'ny FORMATEUR ho an'ny Entreprise
CREATE TABLE f_emps(
   idEmploye BIGINT,
   date_ajout DATE,
   id_formateur BIGINT NOT NULL,
   PRIMARY KEY(idEmploye),
   FOREIGN KEY(idEmploye) REFERENCES employes(idEmploye),
   FOREIGN KEY(id_formateur) REFERENCES formateurs(idFormateur)
);

ALTER TABLE invoices ADD CONSTRAINT fk_idCustomer FOREIGN KEY (idCustomer) REFERENCES customers(idCustomer);
ALTER TABLE invoices ADD CONSTRAINT fk_idEntreprise FOREIGN KEY (idEntreprise) REFERENCES customers(idCustomer);

CREATE TABLE invoice_details_profo(
   idItem BIGINT AUTO_INCREMENT,
   item_qty SMALLINT,
   item_description VARCHAR(200),
   item_unit_price decimal(15,2) NOT NULL DEFAULT 0,
   item_total_price decimal(15,2) NOT NULL DEFAULT 0,
   idInvoice BIGINT NOT NULL,
   idUnite BIGINT NOT NULL,
   idItems BIGINT,
   idModule BIGINT,
   PRIMARY KEY(idItem),
   FOREIGN KEY(idInvoice) REFERENCES invoices(idInvoice),
   FOREIGN KEY(idUnite) REFERENCES unites(idUnite),
   FOREIGN KEY(idModule) REFERENCES mdls(idModule)
);

CREATE TABLE cfp_particuliers(
   idCfp BIGINT,
   idParticulier BIGINT,
   is_sent TINYINT DEFAULT 0,
   is_active_cfp TINYINT DEFAULT 0,
   is_active_particulier TINYINT DEFAULT 0,
   date_collaboration DATE,
   PRIMARY KEY(idCfp, idParticulier),
   FOREIGN KEY(idCfp) REFERENCES cfps(idCustomer),
   FOREIGN KEY(idParticulier) REFERENCES particuliers(idParticulier)
);

CREATE TABLE attestations(
   idAttestation BIGINT AUTO_INCREMENT,
   idProjet BIGINT NOT NULL,
   idEmploye BIGINT NOT NULL,
   idCfp BIGINT NOT NULL,
   file_path TEXT,
   file_name VARCHAR(200),
   PRIMARY KEY(idAttestation),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idEmploye) REFERENCES employes(idEmploye),
   FOREIGN KEY(idCfp) REFERENCES customers(idCustomer)
);

CREATE TABLE suivi_envois (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idProjet BIGINT NOT NULL,
    idEmploye BIGINT NOT NULL,
    idDocument BIGINT NOT NULL,
    type_document VARCHAR(200) NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
    FOREIGN KEY(idEmploye) REFERENCES employes(idEmploye)
);

CREATE TABLE financial_goals (
   id INT AUTO_INCREMENT,       
   id_customer BIGINT NOT NULL,       
   value BIGINT NOT NULL,      
   date DATE NOT NULL, 
   id_module BIGINT NOT NULL,      
   PRIMARY KEY(id),
   FOREIGN KEY (id_customer) REFERENCES customers(idCustomer),
   FOREIGN KEY (id_module) REFERENCES mdls(idModule)
);

ALTER TABLE attestations ADD COLUMN number_attestation VARCHAR(20) NOT NULL;
CREATE TABLE eval_froid_sents(
   idProjet BIGINT,
   idEtp BIGINT,
   date_sent DATE,
   eval_is_sent TINYINT DEFAULT 0,
   PRIMARY KEY(idProjet, idEtp),
   FOREIGN KEY(idProjet) REFERENCES projets(idProjet),
   FOREIGN KEY(idEtp) REFERENCES entreprises(idCustomer)
);

ALTER TABLE projets ADD COLUMN project_date_cold_evaluation DATE DEFAULT NULL;



CREATE TABLE mobilemoneyacounts(
   id BIGINT NOT NULL AUTO_INCREMENT,
   mm_idCustomer BIGINT NOT NULL,
   mm_titulaire VARCHAR(50) ,
   mm_operateur VARCHAR(50),
   mm_phone VARCHAR(50) ,
   PRIMARY KEY(id),
   FOREIGN KEY(mm_idCustomer) REFERENCES customers(idCustomer)
);

-- invoice_payments alter
ALTER TABLE invoice_payments CHANGE `payment_acompte_id` `payment_bank_id` BIGINT(20) NULL;
ALTER TABLE invoice_payments ADD COLUMN payment_mobilemoney_id BIGINT NULL AFTER payment_bank_id;

ALTER TABLE invoice_payments ADD CONSTRAINT fk_idBankCompte FOREIGN KEY (payment_bank_id) REFERENCES bankacounts(id);
ALTER TABLE invoice_payments ADD CONSTRAINT fk_idMobileMoney FOREIGN KEY (payment_mobilemoney_id) REFERENCES mobilemoneyacounts(id);

ALTER TABLE type_entreprises ADD COLUMN type_etp_desc VARCHAR(200) DEFAULT NULL;

--Ajout id reliant à Google Calendar
ALTER TABLE `opportunites` ADD `id_google_opportunite` TEXT NULL AFTER `idCustomer`;

-- badge
CREATE TABLE badges(
   idBadge BIGINT AUTO_INCREMENT,
   idModule BIGINT NOT NULL,
   idCfp BIGINT NOT NULL,
   file_path TEXT,
   file_name VARCHAR(200),
   PRIMARY KEY(idBadge),
   FOREIGN KEY(idModule) REFERENCES mdls(idModule),
   FOREIGN KEY(idCfp) REFERENCES customers(idCustomer)
);

-- table categories évaluations qcm
CREATE TABLE `qcm_category_evaluations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idQCM` bigint(20) DEFAULT NULL,
  `idCategorie` bigint(20) DEFAULT NULL,
  `min_percentage` float DEFAULT NULL,
  `max_percentage` float DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idQCM` (`idQCM`),
  KEY `idCategorie` (`idCategorie`),
  CONSTRAINT `fk_qcm_category_evaluations_qcm` FOREIGN KEY (`idQCM`) REFERENCES `qcm` (`idQCM`),
  CONSTRAINT `fk_qcm_category_evaluations_category` FOREIGN KEY (`idCategorie`) REFERENCES `categories_reponses` (`idCategorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- rajout de la colonne explicationReponse dans la table qcm_reponses
ALTER TABLE qcm_reponses ADD explicationReponse TEXT DEFAULT NULL NULL COMMENT 'explication reponse';
ALTER TABLE qcm_reponses CHANGE explicationReponse explicationReponse TEXT DEFAULT NULL NULL COMMENT 'explication reponse' AFTER texteReponse;

-- table pour les images des questions sur les qcm
CREATE TABLE `images_qcm` (
  `idImageQ` bigint(20) NOT NULL AUTO_INCREMENT,
  `idTypeImage` bigint(20) DEFAULT NULL,
  `url` text DEFAULT NULL,
  `nomImage` varchar(255) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idImageQ`),
  KEY `idTypeImage` (`idTypeImage`),
  CONSTRAINT `images_qcm_ibfk_1` FOREIGN KEY (`idTypeImage`) REFERENCES `type_images` (`idTypeImage`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Ajouter une nouvelle colonne `idImageQ` dans la table `qcm_questions` avec une clé étrangère nullable
ALTER TABLE `qcm_questions` 
ADD COLUMN `idImageQ` BIGINT(20) DEFAULT NULL AFTER `idQCM`,
ADD CONSTRAINT `qcm_questions_ibfk_2` FOREIGN KEY (`idImageQ`) REFERENCES `images_qcm` (`idImageQ`) ON DELETE SET NULL;

CREATE TABLE reservation_participant (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idReservation BIGINT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    fonction VARCHAR(255) NOT NULL,
    FOREIGN KEY (idReservation) REFERENCES inter_entreprises(id) ON DELETE CASCADE
);

CREATE TABLE reservation_responsable (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idReservation BIGINT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    fonction VARCHAR(255) NOT NULL,
    FOREIGN KEY (idReservation) REFERENCES inter_entreprises(id) ON DELETE CASCADE
);

CREATE TABLE traits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idCustomer BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
   CONSTRAINT traits_ibfk_1 FOREIGN KEY (idCustomer) REFERENCES customers (idCustomer)
);

CREATE TABLE reasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idCustomer BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    CONSTRAINT reasons_ibfk_1 FOREIGN KEY (idCustomer) REFERENCES customers (idCustomer)
);

CREATE TABLE reglement (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idCustomer BIGINT NOT NULL,
    contenu TEXT NOT NULL,
    CONSTRAINT reglement_ibfk_1 FOREIGN KEY (idCustomer) REFERENCES customers (idCustomer)
);

CREATE TABLE accueil (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idCustomer BIGINT NOT NULL,
    contenu TEXT NOT NULL,
    CONSTRAINT accueil_ibfk_1 FOREIGN KEY (idCustomer) REFERENCES customers (idCustomer)
);

CREATE TABLE conditions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idCustomer BIGINT NOT NULL,
    contenu TEXT NOT NULL,
    CONSTRAINT conditions_ibfk_1 FOREIGN KEY (idCustomer) REFERENCES customers (idCustomer)
);

CREATE TABLE acces (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idCustomer BIGINT NOT NULL,
    contenu TEXT NOT NULL,
    CONSTRAINT acces_ibfk_1 FOREIGN KEY (idCustomer) REFERENCES customers (idCustomer)
);

CREATE TABLE accompagnement (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idCustomer BIGINT NOT NULL,
    contenu TEXT NOT NULL,
    CONSTRAINT accompagnement_ibfk_1 FOREIGN KEY (idCustomer) REFERENCES customers (idCustomer)
);

CREATE TABLE marketplace_images (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idCustomer BIGINT NOT NULL,
    url TEXT NOT NULL,
    path TEXT NOT NULL,
    CONSTRAINT mrkimg_ibfk_1 FOREIGN KEY (idCustomer) REFERENCES customers (idCustomer)
);

ALTER TABLE eval_chauds ADD temoignage TEXT;
ALTER TABLE qcm_reponses ADD explicationReponse TEXT;
ALTER TABLE `qcm_category_evaluations` ADD id_niveau int


CREATE TABLE attendance_count(
   id BIGINT AUTO_INCREMENT PRIMARY KEY,
   idProjet bigint(20) ,
   nb_present int,
   nb_absent int,
   nb_total_inscrit int,
   nb_a_saisir int,
   FOREIGN KEY(idProjet) references projets (idProjet) ON DELETE CASCADE
);
-- materiels
DROP TABLE IF EXISTS project_materials;

CREATE TABLE material_types(
   id SMALLINT AUTO_INCREMENT,
   name VARCHAR(100) ,
   PRIMARY KEY(id)
);

CREATE TABLE materials(
   id BIGINT AUTO_INCREMENT,
   name VARCHAR(200) ,
   stock_number SMALLINT,
   description TEXT,
   created_at TIMESTAMP NULL,
   updated_at TIMESTAMP NULL,
   customer_id BIGINT NOT NULL,
   material_type_id SMALLINT NOT NULL,
   PRIMARY KEY(id),
   FOREIGN KEY(customer_id) REFERENCES customers(idCustomer),
   FOREIGN KEY(material_type_id) REFERENCES material_types(id)
);

CREATE TABLE project_materials(
   project_id BIGINT,
   material_id BIGINT,
   number SMALLINT,
   created_at TIMESTAMP NULL,
   updated_at TIMESTAMP NULL,
   PRIMARY KEY(project_id, material_id),
   FOREIGN KEY(project_id) REFERENCES projets(idProjet),
   FOREIGN KEY(material_id) REFERENCES materials(id)
);

-- insert
-- afaka ampiana ireto type ireto fa tsy voatery hoe ireo fotsiny no ampiasaina akory
INSERT INTO material_types(id, name) VALUES
(1, 'Matériel informatique'),
(2, 'Matériel pédagogique'),
(3, 'Matériel de bureau'),
(4, 'Salle de formation');
-- end materiels

ALTER TABLE seances ADD COLUMN is_reported BOOLEAN DEFAULT false;
ALTER TABLE seances ADD COLUMN is_report_undetermined BOOLEAN DEFAULT false;
ALTER TABLE seances ADD COLUMN report_date DATETIME NULL AFTER dateSeance;

ALTER TABLE seances ADD COLUMN heure_debut_reportee TIME NULL,
ADD COLUMN heure_fin_reportee TIME NULL AFTER report_date;

CREATE TABLE invoice_payments_grouped(
   id BIGINT AUTO_INCREMENT,
   invoice_id BIGINT ,
   etp_id BIGINT,
   payment_date DATE,
   payment_method_id SMALLINT,
   amount DECIMAL(15,2) NOT NULL,
   payment_bank_id BIGINT,
   payment_description  VARCHAR(50),
   created_at timestamp NULL DEFAULT NULL,
   updated_at timestamp NULL DEFAULT NULL,
   PRIMARY KEY(id),
   FOREIGN KEY(invoice_id) REFERENCES invoices(idInvoice),
   FOREIGN KEY(etp_id) REFERENCES entreprises(idCustomer),
   FOREIGN KEY (payment_method_id) REFERENCES pm_types(idTypePm),
   FOREIGN KEY (payment_bank_id) REFERENCES bankacounts(id)
);
