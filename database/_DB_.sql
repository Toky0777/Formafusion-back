CREATE TABLE `abn_cfps` (
  `idAbn` bigint(20) NOT NULL,
  `nbReferent` int(11) DEFAULT NULL,
  `nbForm` int(11) DEFAULT NULL,
  `nbSession` int(11) DEFAULT NULL,
  `isInfinity` tinyint(4) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `abn_etps`
--

CREATE TABLE `abn_etps` (
  `idAbn` bigint(20) NOT NULL,
  `nbEmploye` int(11) DEFAULT NULL,
  `isInfinity` tinyint(4) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `abonnements`
--

CREATE TABLE `abonnements` (
  `idAbn` bigint(20) NOT NULL,
  `intitule` varchar(50) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `prixAbn` decimal(15,2) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `acces`
--

CREATE TABLE `acces` (
  `id` bigint(20) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `contenu` text NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `accompagnement`
--

CREATE TABLE `accompagnement` (
  `id` bigint(20) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `contenu` text NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `accueil`
--

CREATE TABLE `accueil` (
  `id` bigint(20) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `contenu` text NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `agences`
--

CREATE TABLE `agences` (
  `idAgence` bigint(20) NOT NULL,
  `ag_name` varchar(150) DEFAULT NULL,
  `idVilleCoded` bigint(20) NOT NULL,
  `idCustomer` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `apprenants`
--

CREATE TABLE `apprenants` (
  `idEmploye` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `app_country`
--

CREATE TABLE `app_country` (
  `id` int(11) NOT NULL,
  `idCountry` int(11) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `attestations`
--

CREATE TABLE `attestations` (
  `idAttestation` bigint(20) NOT NULL,
  `idProjet` bigint(20) NOT NULL,
  `idEmploye` bigint(20) NOT NULL,
  `idCfp` bigint(20) NOT NULL,
  `file_path` text DEFAULT NULL,
  `file_name` varchar(200) DEFAULT NULL,
  `number_attestation` varchar(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `attributions_badge`
--

CREATE TABLE `attributions_badge` (
  `idAttribution` bigint(20) NOT NULL,
  `idBadge` bigint(20) NOT NULL,
  `idEmploye` bigint(20) NOT NULL,
  `idProjet` bigint(20) NOT NULL,
  `date_attribution` datetime DEFAULT current_timestamp(),
  `date_expiration` datetime DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `est_verifie` tinyint(1) DEFAULT 1
);

-- --------------------------------------------------------

--
-- Structure de la table `badges`
--

CREATE TABLE `badges` (
  `idBadge` bigint(20) NOT NULL,
  `idModule` bigint(20) NOT NULL,
  `idCfp` bigint(20) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `sous_titre` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `a_propos` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `identifiant_unique` varchar(30) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `is_reset` int(11) DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `bankacounts`
--

CREATE TABLE `bankacounts` (
  `id` bigint(20) NOT NULL,
  `ba_idCustomer` bigint(20) NOT NULL,
  `ba_titulaire` varchar(50) DEFAULT NULL,
  `ba_name` varchar(50) DEFAULT NULL,
  `ba_idPostal` bigint(20) DEFAULT NULL,
  `ba_quartier` varchar(50) DEFAULT NULL,
  `ba_account_number` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `batches`
--

CREATE TABLE `batches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `customer_id` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `batch_learners`
--

CREATE TABLE `batch_learners` (
  `id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `employe_id` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `categories_reponses`
--

CREATE TABLE `categories_reponses` (
  `idCategorie` bigint(20) NOT NULL,
  `nomCategorie` varchar(50) NOT NULL,
  `descriptionCategorie` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `cfps`
--

CREATE TABLE `cfps` (
  `idCustomer` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `cfp_etps`
--

CREATE TABLE `cfp_etps` (
  `idEtp` bigint(20) NOT NULL,
  `idCfp` bigint(20) NOT NULL,
  `dateCollaboration` date DEFAULT NULL,
  `activiteEtp` tinyint(1) DEFAULT NULL,
  `activiteCfp` tinyint(1) DEFAULT NULL,
  `isSent` tinyint(4) DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `cfp_formateurs`
--

CREATE TABLE `cfp_formateurs` (
  `idFormateur` bigint(20) NOT NULL,
  `idCfp` bigint(20) NOT NULL,
  `dateCollaboration` date DEFAULT NULL,
  `isActiveFormateur` tinyint(1) DEFAULT NULL,
  `isActiveCfp` tinyint(1) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `cfp_particuliers`
--

CREATE TABLE `cfp_particuliers` (
  `idCfp` bigint(20) NOT NULL,
  `idParticulier` bigint(20) NOT NULL,
  `is_sent` tinyint(4) DEFAULT 0,
  `is_active_cfp` tinyint(4) DEFAULT 0,
  `is_active_particulier` tinyint(4) DEFAULT 0,
  `date_collaboration` date DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `cfp_selected_by_admin`
--

CREATE TABLE `cfp_selected_by_admin` (
  `idSuperAdmin` bigint(20) NOT NULL,
  `idCfp` bigint(20) NOT NULL,
  `date_added` date DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `cible_modules`
--

CREATE TABLE `cible_modules` (
  `idCible` bigint(20) NOT NULL,
  `cible` varchar(255) DEFAULT NULL,
  `idModule` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `commissions_received`
--

CREATE TABLE `commissions_received` (
  `idCommissionReceived` bigint(20) NOT NULL,
  `credit_payment_id` bigint(20) DEFAULT NULL COMMENT 'Référence vers le paiement de crédits',
  `commission_rate` decimal(5,2) DEFAULT NULL COMMENT 'Taux de commission appliqué',
  `total_commission` decimal(10,2) DEFAULT NULL COMMENT 'Montant total de la commission',
  `currency` varchar(100) DEFAULT NULL COMMENT 'Devise utilisée pour cette commission',
  `receiver_id` bigint(20) DEFAULT NULL COMMENT 'Utilisateur recevant la commission si applicable',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `commissions_settings`
--

CREATE TABLE `commissions_settings` (
  `idCommissionSetting` bigint(20) NOT NULL,
  `payment_type` varchar(255) DEFAULT NULL COMMENT 'Méthode de paiement (chèque, virement bancaire, cb)',
  `commission_rate` decimal(5,2) DEFAULT NULL COMMENT 'Taux de commission en pourcentage',
  `currency` varchar(100) DEFAULT NULL COMMENT 'Devise associée à ce paramètre',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `nif` varchar(200) DEFAULT NULL,
  `stat` varchar(200) DEFAULT NULL,
  `rcs` varchar(200) DEFAULT NULL,
  `adresse` varchar(200) DEFAULT NULL,
  `mail` varchar(200) DEFAULT NULL,
  `phone` varchar(200) DEFAULT NULL,
  `website` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `competences`
--

CREATE TABLE `competences` (
  `id` bigint(20) NOT NULL,
  `idFormateur` bigint(20) NOT NULL,
  `Competence` varchar(200) DEFAULT NULL,
  `note` int(11) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `competences_badge`
--

CREATE TABLE `competences_badge` (
  `idCompetence` bigint(20) NOT NULL,
  `idBadge` bigint(20) NOT NULL,
  `nom_competence` varchar(100) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `conditions`
--

CREATE TABLE `conditions` (
  `id` bigint(20) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `contenu` text NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `contacts`
--

CREATE TABLE `contacts` (
  `idContact` int(11) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(50) DEFAULT NULL,
  `idLieu` bigint(20) NOT NULL,
  `contact_tel` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `content_files`
--

CREATE TABLE `content_files` (
  `idFile` bigint(20) NOT NULL,
  `idContent` bigint(20) NOT NULL,
  `fileName` varchar(200) DEFAULT NULL,
  `filePath` text DEFAULT NULL,
  `fileDescription` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `content_refresh`
--

CREATE TABLE `content_refresh` (
  `idContent` bigint(20) NOT NULL,
  `videoLink` text DEFAULT NULL,
  `videoDescription` text DEFAULT NULL,
  `texte` text DEFAULT NULL,
  `idModuleContent` int(11) NOT NULL,
  `isDelete` int(11) DEFAULT 0,
  `title` varchar(255) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `flag` varchar(250) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `countriess`
--

CREATE TABLE `countriess` (
  `id` int(11) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `flag` varchar(200) DEFAULT NULL,
  `id_nif_name` int(11) NOT NULL,
  `id_currency` int(11) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `country_fulls`
--

CREATE TABLE `country_fulls` (
  `id` int(11) NOT NULL,
  `id_rcs_name` int(11) NOT NULL,
  `id_stat_name` int(11) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `country_less`
--

CREATE TABLE `country_less` (
  `id` int(11) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `credits_packs`
--

CREATE TABLE `credits_packs` (
  `idPackCredit` bigint(20) NOT NULL,
  `type_pack` varchar(255) DEFAULT NULL COMMENT 'Type du pack de crédits',
  `description_pack` varchar(255) DEFAULT NULL COMMENT 'Description du pack de crédits',
  `credits` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Montant de crédits',
  `pack_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Prix du pack de crédits',
  `currency` varchar(255) DEFAULT NULL COMMENT 'devise du prix',
  `is_active` tinyint(1) DEFAULT NULL COMMENT 'situation du pack de crédits actuellement (0 si inactif et 1 si actif)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `credits_payments`
--

CREATE TABLE `credits_payments` (
  `idCreditPayment` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `pack_credits_id` bigint(20) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL COMMENT 'Référence du paiement',
  `amount_paid` decimal(10,2) NOT NULL COMMENT 'Montant payer par l''utilisateur pour acheter les packs de crédits',
  `currency` varchar(100) DEFAULT NULL,
  `payment_type` varchar(255) DEFAULT NULL COMMENT 'Méthode de payement de l''utilisateur soit chèque, virement bancaire, carte bancaire (cb)',
  `status` varchar(255) DEFAULT NULL COMMENT 'Status du paiement soit pending, canceled, paid',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `credits_wallet`
--

CREATE TABLE `credits_wallet` (
  `idWallet` bigint(20) NOT NULL,
  `idUser` bigint(20) DEFAULT NULL,
  `solde` decimal(10,2) NOT NULL COMMENT 'Solde de l''utilisateur en crédits',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) COMMENT='Portefeuille des crédits de l''utilisateur';

-- --------------------------------------------------------

--
-- Structure de la table `criteres_badge`
--

CREATE TABLE `criteres_badge` (
  `idCritere` bigint(20) NOT NULL,
  `idBadge` bigint(20) NOT NULL,
  `texte_critere` text NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT 1
);

-- --------------------------------------------------------

--
-- Structure de la table `currencies`
--

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `symbol` varchar(50) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `customers`
--

CREATE TABLE `customers` (
  `idCustomer` bigint(20) NOT NULL,
  `customerName` varchar(255) DEFAULT NULL,
  `nif` varchar(50) DEFAULT NULL,
  `stat` varchar(50) DEFAULT NULL,
  `assujetti` tinyint(4) DEFAULT 0,
  `rcs` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `siteWeb` varchar(50) DEFAULT NULL,
  `logo` varchar(50) DEFAULT NULL,
  `customerEmail` varchar(200) DEFAULT NULL,
  `customerPhone` varchar(200) DEFAULT NULL,
  `idSecteur` int(11) NOT NULL,
  `idTypeCustomer` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `customer_addr_lot` varchar(255) DEFAULT NULL,
  `customer_addr_quartier` varchar(100) DEFAULT NULL,
  `customer_addr_rue` varchar(100) DEFAULT NULL,
  `customer_slogan` varchar(255) DEFAULT NULL,
  `idVilleCoded` bigint(20) NOT NULL,
  `id_country` int(11) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `c_emps`
--

CREATE TABLE `c_emps` (
  `idEmploye` bigint(20) NOT NULL,
  `id_cfp` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `detail_abns`
--

CREATE TABLE `detail_abns` (
  `id` bigint(20) NOT NULL,
  `idAbn` bigint(20) DEFAULT NULL,
  `idCustomer` bigint(20) DEFAULT NULL,
  `dateDemande` date DEFAULT NULL,
  `dateDebut` date DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `isActive` tinyint(4) DEFAULT 0,
  `isDisable` tinyint(4) DEFAULT 0,
  `isExpired` tinyint(4) DEFAULT 0,
  `isStoped` tinyint(4) DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `detail_apprenants`
--

CREATE TABLE `detail_apprenants` (
  `idProjet` bigint(20) NOT NULL,
  `idEmploye` bigint(20) NOT NULL,
  `nbAppr` int(20) DEFAULT NULL,
  `id_cfp_appr` bigint(20) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `detail_apprenant_inters`
--

CREATE TABLE `detail_apprenant_inters` (
  `idProjet` bigint(20) NOT NULL,
  `idEtp` bigint(20) NOT NULL,
  `idEmploye` bigint(20) NOT NULL,
  `id_cfp_appr` bigint(20) DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `devises`
--

CREATE TABLE `devises` (
  `idDevise` bigint(20) NOT NULL,
  `devise` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `diplomes`
--

CREATE TABLE `diplomes` (
  `id` bigint(20) NOT NULL,
  `idFormateur` bigint(20) NOT NULL,
  `Ecole` varchar(200) DEFAULT NULL,
  `Diplome` varchar(200) DEFAULT NULL,
  `Domaine` varchar(200) DEFAULT NULL,
  `Date_debut` date DEFAULT NULL,
  `Date_fin` date DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `idDocument` bigint(20) NOT NULL,
  `titre` varchar(200) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `idDossier` bigint(20) DEFAULT NULL,
  `filename` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `taille` decimal(15,2) DEFAULT NULL,
  `idTypeDocument` bigint(20) DEFAULT NULL,
  `extension` varchar(10) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `domaine_formations`
--

CREATE TABLE `domaine_formations` (
  `idDomaine` bigint(20) NOT NULL,
  `nomDomaine` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `dossiers`
--

CREATE TABLE `dossiers` (
  `idDossier` bigint(20) NOT NULL,
  `nomDossier` varchar(200) DEFAULT NULL,
  `idCfp` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `note` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `emargements`
--

CREATE TABLE `emargements` (
  `idSeance` bigint(20) NOT NULL,
  `idEmploye` bigint(20) NOT NULL,
  `isPresent` tinyint(4) DEFAULT 0,
  `nbPresent` int(20) DEFAULT NULL,
  `idProjet` bigint(20) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `employes`
--

CREATE TABLE `employes` (
  `idEmploye` bigint(20) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `idSexe` int(11) NOT NULL,
  `idNiveau` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `idFonction` int(11) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `emp_debit_credit`
--

CREATE TABLE `emp_debit_credit` (
  `idDebitEmpEtp` bigint(20) NOT NULL,
  `idTransaction` bigint(20) DEFAULT NULL COMMENT 'Cle etrangere lie a la table transaction_history',
  `idUser` bigint(20) DEFAULT NULL COMMENT 'Cle etrangere lie a la table users',
  `description` varchar(255) DEFAULT NULL COMMENT 'Description du debit de credits',
  `montant` decimal(10,2) NOT NULL COMMENT 'Montant de credit debiter par les employes de l entreprise',
  `typeTransaction` enum('credit','debit') NOT NULL COMMENT 'Type du pack de credits',
  `created_at` timestamp NULL DEFAULT current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `entreprises`
--

CREATE TABLE `entreprises` (
  `idCustomer` bigint(20) NOT NULL,
  `idTypeEtp` smallint(6) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `equipments`
--

CREATE TABLE `equipments` (
  `idEquipment` int(11) NOT NULL,
  `equipment_name` varchar(255) DEFAULT NULL,
  `idSalle` int(11) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `etp_groupeds`
--

CREATE TABLE `etp_groupeds` (
  `idEntreprise` bigint(20) NOT NULL,
  `idEntrepriseParent` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `etp_groupes`
--

CREATE TABLE `etp_groupes` (
  `idEntreprise` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `etp_informals`
--

CREATE TABLE `etp_informals` (
  `idEntreprise` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `etp_singles`
--

CREATE TABLE `etp_singles` (
  `idEntreprise` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `eval_apprenant`
--

CREATE TABLE `eval_apprenant` (
  `id` mediumint(9) NOT NULL,
  `idEmploye` int(11) DEFAULT NULL,
  `idProjet` int(11) DEFAULT NULL,
  `avant` tinyint(4) DEFAULT NULL,
  `apres` tinyint(4) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `eval_chauds`
--

CREATE TABLE `eval_chauds` (
  `idEval_chaud` bigint(20) NOT NULL,
  `idProjet` bigint(20) DEFAULT NULL,
  `idEmploye` bigint(20) DEFAULT NULL,
  `idQuestion` bigint(20) DEFAULT NULL,
  `idExaminer` bigint(20) DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `com1` text DEFAULT NULL,
  `com2` text DEFAULT NULL,
  `idValComment` text DEFAULT NULL,
  `generalApreciate` int(11) DEFAULT NULL,
  `temoignage` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `eval_froids`
--

CREATE TABLE `eval_froids` (
  `id` bigint(20) NOT NULL,
  `idProjet` bigint(20) DEFAULT NULL,
  `idEmploye` bigint(20) DEFAULT NULL,
  `idQuizzCold` bigint(20) DEFAULT NULL,
  `date_added` date DEFAULT NULL,
  `general_satisfaction` tinyint(4) DEFAULT NULL,
  `general_recomand` tinyint(4) DEFAULT NULL,
  `general_aspect` text DEFAULT NULL,
  `general_suggestion` text DEFAULT NULL,
  `note` tinyint(4) DEFAULT NULL,
  `description` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `eval_froid_sents`
--

CREATE TABLE `eval_froid_sents` (
  `idProjet` bigint(20) NOT NULL,
  `idEtp` bigint(20) NOT NULL,
  `date_sent` date DEFAULT NULL,
  `eval_is_sent` tinyint(4) DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `experiences`
--

CREATE TABLE `experiences` (
  `id` bigint(20) NOT NULL,
  `idFormateur` bigint(20) NOT NULL,
  `Lieu_de_stage` varchar(200) DEFAULT NULL,
  `Fonction` varchar(200) DEFAULT NULL,
  `Date_debut` date DEFAULT NULL,
  `Date_fin` date DEFAULT NULL,
  `Lieu` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `features`
--

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

-- --------------------------------------------------------

--
-- Structure de la table `feries`
--

CREATE TABLE `feries` (
  `idFerie` bigint(20) NOT NULL,
  `titleFerie` varchar(255) DEFAULT NULL,
  `dateFerie` date DEFAULT NULL,
  `idCustomer` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `financial_goals`
--

CREATE TABLE `financial_goals` (
  `id` int(11) NOT NULL,
  `id_customer` bigint(20) NOT NULL,
  `value` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `id_module` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `fonctions`
--

CREATE TABLE `fonctions` (
  `idFonction` int(11) NOT NULL,
  `fonction` text DEFAULT NULL,
  `idCustomer` bigint(20) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `formateurs`
--

CREATE TABLE `formateurs` (
  `idFormateur` bigint(20) NOT NULL,
  `idSp` int(11) NOT NULL,
  `form_titre` varchar(200) DEFAULT NULL,
  `form_speciality` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `formateur_internes`
--

CREATE TABLE `formateur_internes` (
  `idFormateur` bigint(20) NOT NULL,
  `idEmploye` bigint(20) NOT NULL,
  `idEntreprise` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `forms`
--

CREATE TABLE `forms` (
  `idFormateur` bigint(20) NOT NULL,
  `idTypeFormateur` int(11) NOT NULL,
  `idSexe` int(11) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `fournisseurs`
--

CREATE TABLE `fournisseurs` (
  `idFournisseur` bigint(20) NOT NULL,
  `nomFournisseur` varchar(255) DEFAULT NULL,
  `telFournisseur` varchar(50) DEFAULT NULL,
  `emailFournisseur` varchar(50) DEFAULT NULL,
  `serviceOffertFournisseur` text DEFAULT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `idTypefournisseur` int(11) NOT NULL,
  `idTypeService` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `fournisseur_cfps`
--

CREATE TABLE `fournisseur_cfps` (
  `idFournisseur` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `fournisseur_etps`
--

CREATE TABLE `fournisseur_etps` (
  `idFournisseur` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `frais`
--

CREATE TABLE `frais` (
  `idFrais` bigint(20) NOT NULL,
  `Frais` varchar(255) DEFAULT NULL,
  `exemple` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `fraisprojet`
--

CREATE TABLE `fraisprojet` (
  `idFraisProjet` bigint(20) NOT NULL,
  `idProjet` bigint(20) DEFAULT NULL,
  `idFrais` bigint(20) DEFAULT NULL,
  `montant` decimal(15,2) NOT NULL,
  `datefrais` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` varchar(200) DEFAULT NULL,
  `isEtp` int(11) DEFAULT 0,
  `idPayeur` bigint(20) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `f_emps`
--

CREATE TABLE `f_emps` (
  `idEmploye` bigint(20) NOT NULL,
  `date_ajout` date DEFAULT NULL,
  `id_formateur` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `google_users`
--

CREATE TABLE `google_users` (
  `user_id` bigint(20) NOT NULL,
  `google_id` text DEFAULT NULL,
  `avatar` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `iframes`
--

CREATE TABLE `iframes` (
  `idIframe` bigint(20) NOT NULL,
  `iframe` text DEFAULT NULL,
  `idCustomer` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `ignoredConflitFormateur`
--

CREATE TABLE `ignoredConflitFormateur` (
  `idFormateur` bigint(20) NOT NULL,
  `idProjet` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `ignoredConflitLieu`
--

CREATE TABLE `ignoredConflitLieu` (
  `idSalle` int(11) NOT NULL,
  `idProjet` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `images`
--

CREATE TABLE `images` (
  `idImages` bigint(20) NOT NULL,
  `idTypeImage` bigint(20) DEFAULT NULL,
  `idProjet` bigint(20) DEFAULT NULL,
  `url` text DEFAULT NULL,
  `nomImage` varchar(255) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_added_by` bigint(20) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `images_qcm`
--

CREATE TABLE `images_qcm` (
  `idImageQ` bigint(20) NOT NULL,
  `idTypeImage` bigint(20) DEFAULT NULL,
  `url` text DEFAULT NULL,
  `nomImage` varchar(255) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `internes`
--

CREATE TABLE `internes` (
  `idProjet` bigint(20) NOT NULL,
  `idEtp` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `inters`
--

CREATE TABLE `inters` (
  `idProjet` bigint(20) NOT NULL,
  `idCfp` bigint(20) NOT NULL,
  `idPaiement` int(11) NOT NULL,
  `nbPlace` tinyint(4) DEFAULT 0,
  `project_inter_privacy` tinyint(4) DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `inter_entreprises`
--

CREATE TABLE `inter_entreprises` (
  `id` bigint(20) NOT NULL,
  `idProjet` bigint(20) DEFAULT NULL,
  `idEtp` bigint(20) DEFAULT NULL,
  `isActiveInter` tinyint(4) DEFAULT 0,
  `nbPlaceReserved` tinyint(4) DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `intras`
--

CREATE TABLE `intras` (
  `idProjet` bigint(20) NOT NULL,
  `idPaiement` int(11) NOT NULL,
  `idEtp` bigint(20) NOT NULL,
  `idCfp` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `invoices`
--

CREATE TABLE `invoices` (
  `idInvoice` bigint(20) NOT NULL,
  `invoice_number` varchar(255) DEFAULT NULL,
  `invoice_bc` varchar(255) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `invoice_date_pm` date DEFAULT NULL,
  `invoice_status` tinyint(4) DEFAULT 0,
  `invoice_condition` varchar(255) DEFAULT NULL,
  `invoice_sub_total` decimal(15,2) DEFAULT NULL,
  `invoice_reduction` decimal(15,2) DEFAULT 0.00,
  `invoice_tva` decimal(15,2) DEFAULT 0.00,
  `invoice_total_amount` decimal(15,2) DEFAULT NULL,
  `invoice_letter` varchar(255) DEFAULT NULL,
  `idTypeClient` int(11) DEFAULT NULL,
  `idEntreprise` bigint(20) NOT NULL,
  `idCustomer` bigint(20) DEFAULT NULL,
  `idCompany` bigint(20) DEFAULT NULL,
  `idPaiement` bigint(20) NOT NULL,
  `idTypeFacture` int(11) NOT NULL,
  `idBankAcount` bigint(20) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `invoice_acomptes`
--

CREATE TABLE `invoice_acomptes` (
  `idInvoice` bigint(20) NOT NULL,
  `percent` int(11) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `invoice_deleted`
--

CREATE TABLE `invoice_deleted` (
  `id` bigint(20) NOT NULL,
  `idInvoice` bigint(20) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `invoice_details`
--

CREATE TABLE `invoice_details` (
  `idItem` bigint(20) NOT NULL,
  `item_qty` smallint(6) DEFAULT NULL,
  `item_description` varchar(200) DEFAULT NULL,
  `item_unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `item_total_price` decimal(15,2) DEFAULT NULL,
  `idInvoice` bigint(20) NOT NULL,
  `idUnite` bigint(20) NOT NULL,
  `idItems` bigint(20) DEFAULT NULL,
  `idProjet` bigint(20) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `invoice_details_profo`
--

CREATE TABLE `invoice_details_profo` (
  `idItem` bigint(20) NOT NULL,
  `item_qty` smallint(6) DEFAULT NULL,
  `item_description` varchar(200) DEFAULT NULL,
  `item_unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `item_total_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `idInvoice` bigint(20) NOT NULL,
  `idUnite` bigint(20) NOT NULL,
  `idItems` bigint(20) DEFAULT NULL,
  `idModule` bigint(20) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `invoice_payments`
--

CREATE TABLE `invoice_payments` (
  `id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_date` date DEFAULT NULL,
  `payment_method_id` smallint(6) DEFAULT NULL,
  `payment_bank_id` bigint(20) DEFAULT NULL,
  `payment_mobilemoney_id` bigint(20) DEFAULT NULL,
  `payment_description` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `invoice_standards`
--

CREATE TABLE `invoice_standards` (
  `idInvoice` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `invoice_status`
--

CREATE TABLE `invoice_status` (
  `idInvoiceStatus` bigint(20) NOT NULL,
  `invoice_status_name` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `invoice_type_client`
--

CREATE TABLE `invoice_type_client` (
  `idType` int(11) NOT NULL,
  `typeName` varchar(50) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `langues`
--

CREATE TABLE `langues` (
  `id` bigint(20) NOT NULL,
  `idFormateur` bigint(20) NOT NULL,
  `note` int(11) DEFAULT NULL,
  `Langue` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `launchers`
--

CREATE TABLE `launchers` (
  `idLauncher` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `link` varchar(100) DEFAULT NULL,
  `icone` varchar(100) DEFAULT NULL,
  `idCountry` int(11) DEFAULT NULL,
  `category` varchar(20) DEFAULT 'other',
  `is_active` int(11) DEFAULT 1,
  `order` tinyint(4) DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `lieux`
--

CREATE TABLE `lieux` (
  `idLieu` bigint(20) NOT NULL,
  `li_name` varchar(150) DEFAULT NULL,
  `idVille` int(11) NOT NULL,
  `idLieuType` int(11) NOT NULL,
  `idVilleCoded` bigint(20) NOT NULL,
  `li_quartier` varchar(100) DEFAULT NULL,
  `li_rue` varchar(100) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `lieu_privates`
--

CREATE TABLE `lieu_privates` (
  `idLieu` bigint(20) NOT NULL,
  `idCustomer` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `lieu_publics`
--

CREATE TABLE `lieu_publics` (
  `idLieu` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `lieu_types`
--

CREATE TABLE `lieu_types` (
  `idLieuType` int(11) NOT NULL,
  `lt_name` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `marketplace_images`
--

CREATE TABLE `marketplace_images` (
  `id` bigint(20) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `url` text NOT NULL,
  `path` text NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `materiel_cfps`
--

CREATE TABLE `materiel_cfps` (
  `idMateriel` bigint(20) NOT NULL,
  `codeMateriel` varchar(50) DEFAULT NULL,
  `nomMateriel` varchar(255) DEFAULT NULL,
  `descriptionMateriel` text DEFAULT NULL,
  `idCfp` bigint(20) NOT NULL,
  `idTypeMateriel` smallint(6) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `materiel_etps`
--

CREATE TABLE `materiel_etps` (
  `idMateriel` bigint(20) NOT NULL,
  `codeMateriel` varchar(50) DEFAULT NULL,
  `nomMateriel` varchar(255) DEFAULT NULL,
  `descriptionMateriel` text DEFAULT NULL,
  `idEtp` bigint(20) NOT NULL,
  `idTypeMateriel` smallint(6) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `materiel_externes`
--

CREATE TABLE `materiel_externes` (
  `idMateriel` bigint(20) NOT NULL,
  `idFournisseur` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `materiel_externe_etps`
--

CREATE TABLE `materiel_externe_etps` (
  `idMateriel` bigint(20) NOT NULL,
  `idFournisseur` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `materiel_internes`
--

CREATE TABLE `materiel_internes` (
  `idMateriel` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `materiel_interne_etps`
--

CREATE TABLE `materiel_interne_etps` (
  `idMateriel` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `mdls`
--

CREATE TABLE `mdls` (
  `idModule` bigint(20) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `moduleName` text DEFAULT NULL,
  `module_subtitle` varchar(200) DEFAULT NULL,
  `module_tag` varchar(150) DEFAULT NULL,
  `module_image` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `minApprenant` int(11) DEFAULT NULL,
  `maxApprenant` int(11) DEFAULT NULL,
  `dureeJ` int(11) DEFAULT NULL,
  `dureeH` int(11) DEFAULT NULL,
  `moduleStatut` tinyint(4) DEFAULT 0,
  `idDomaine` bigint(20) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `idTypeModule` int(11) NOT NULL,
  `module_is_complete` tinyint(4) DEFAULT 0,
  `idLevel` int(11) NOT NULL DEFAULT 1,
  `is_public` tinyint(1) DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `menu_refresh`
--

CREATE TABLE `menu_refresh` (
  `idMenuRefresh` bigint(20) NOT NULL,
  `menu` varchar(200) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `idModule` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `mobilemoneyacounts`
--

CREATE TABLE `mobilemoneyacounts` (
  `id` bigint(20) NOT NULL,
  `mm_idCustomer` bigint(20) NOT NULL,
  `mm_titulaire` varchar(50) DEFAULT NULL,
  `mm_operateur` varchar(50) DEFAULT NULL,
  `mm_phone` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `modalites`
--

CREATE TABLE `modalites` (
  `idModalite` int(11) NOT NULL,
  `modalite` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `mode_paiements`
--

CREATE TABLE `mode_paiements` (
  `idPaiement` bigint(20) NOT NULL,
  `idTypePm` smallint(6) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `modules`
--

CREATE TABLE `modules` (
  `idModule` bigint(20) NOT NULL,
  `prix` decimal(15,2) DEFAULT NULL,
  `prixGroupe` decimal(15,2) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `module_internes`
--

CREATE TABLE `module_internes` (
  `idModule` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `module_levels`
--

CREATE TABLE `module_levels` (
  `idLevel` int(11) NOT NULL,
  `module_level_name` varchar(150) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `module_program_contents`
--

CREATE TABLE `module_program_contents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `idProgramme` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `module_ressources`
--

CREATE TABLE `module_ressources` (
  `idModuleRessource` bigint(20) NOT NULL,
  `module_ressource_name` varchar(200) DEFAULT NULL,
  `module_ressource_extension` varchar(5) DEFAULT NULL,
  `idModule` bigint(20) NOT NULL,
  `file_path` text DEFAULT NULL,
  `taille` decimal(15,2) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `module_rewards`
--

CREATE TABLE `module_rewards` (
  `id` bigint(20) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `place_number` smallint(6) DEFAULT NULL,
  `reduction` int(11) DEFAULT NULL,
  `normal_price_per_place` int(11) DEFAULT 0,
  `idCfp` bigint(20) NOT NULL,
  `idEtp` bigint(20) NOT NULL,
  `id_reward_scope` smallint(6) NOT NULL,
  `id_reward_type` int(11) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `module_reward_fulls`
--

CREATE TABLE `module_reward_fulls` (
  `id_module_reward` bigint(20) NOT NULL,
  `is_all_module_allowed` tinyint(1) DEFAULT 1
);

-- --------------------------------------------------------

--
-- Structure de la table `module_reward_less`
--

CREATE TABLE `module_reward_less` (
  `id_module_reward` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `module_skills`
--

CREATE TABLE `module_skills` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `idModule` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `nif_names`
--

CREATE TABLE `nif_names` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `niveau_etudes`
--

CREATE TABLE `niveau_etudes` (
  `idNiveau` int(11) NOT NULL,
  `niveau` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `niveau_gamification`
--

CREATE TABLE `niveau_gamification` (
  `numero` int(11) NOT NULL,
  `classement` varchar(50) DEFAULT NULL,
  `min_points` int(11) NOT NULL,
  `max_points` int(11) DEFAULT NULL,
  `url_image` varchar(255) DEFAULT NULL,
  `lien_recomponse` varchar(255) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `niveau_qcm`
--

CREATE TABLE `niveau_qcm` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `niveau` varchar(255) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint(20) NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `objectif_modules`
--

CREATE TABLE `objectif_modules` (
  `idObjectif` bigint(20) NOT NULL,
  `objectif` varchar(255) DEFAULT NULL,
  `idModule` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `opportunites`
--

CREATE TABLE `opportunites` (
  `id` bigint(20) NOT NULL,
  `idCustomer` bigint(20) DEFAULT NULL,
  `id_google_opportunite` text DEFAULT NULL,
  `idVille` bigint(20) DEFAULT NULL,
  `idModule` bigint(20) DEFAULT NULL,
  `idEtp` bigint(20) DEFAULT NULL,
  `statut` int(11) DEFAULT NULL,
  `nbPersonne` int(11) DEFAULT NULL,
  `prix` decimal(15,2) DEFAULT NULL,
  `dateDeb` date DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `ref_name` varchar(250) DEFAULT NULL,
  `ref_firstName` varchar(150) DEFAULT NULL,
  `ref_email` varchar(100) DEFAULT NULL,
  `ref_phone` varchar(50) DEFAULT NULL,
  `note` varchar(200) DEFAULT NULL,
  `id_prospect` bigint(20) DEFAULT NULL,
  `source` varchar(200) DEFAULT NULL,
  `opportunitie_is_win` tinyint(4) DEFAULT 0,
  `opportunitie_is_lost` tinyint(4) DEFAULT 0,
  `opportunitie_is_standBy` tinyint(4) DEFAULT 0,
  `position` int(11) DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `idPaiement` int(11) NOT NULL,
  `paiement` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `particuliers`
--

CREATE TABLE `particuliers` (
  `idParticulier` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `particulier_projet`
--

CREATE TABLE `particulier_projet` (
  `idParticulier` bigint(20) NOT NULL,
  `idProjet` bigint(20) NOT NULL,
  `date_attribution` date DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `due_date` date NOT NULL,
  `payment_date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscription_name` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_order` varchar(255) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` char(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `place_etp_from_cfps`
--

CREATE TABLE `place_etp_from_cfps` (
  `idLieu` bigint(20) NOT NULL,
  `date_added` date DEFAULT NULL,
  `idEntreprise` bigint(20) NOT NULL,
  `idCfp` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `plans`
--

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `dedicate` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`dedicate`)),
  `slug` varchar(255) NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`description`)),
  `user_type` varchar(255) NOT NULL,
  `is_recommander` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `price` decimal(8,2) NOT NULL DEFAULT 0.00,
  `signup_fee` decimal(8,2) NOT NULL DEFAULT 0.00,
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

-- --------------------------------------------------------

--
-- Structure de la table `pm_cheques`
--

CREATE TABLE `pm_cheques` (
  `idPaiement` bigint(20) NOT NULL,
  `bank_account_orderer` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `pm_types`
--

CREATE TABLE `pm_types` (
  `idTypePm` smallint(6) NOT NULL,
  `pm_type_name` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `pm_virements`
--

CREATE TABLE `pm_virements` (
  `idPaiement` bigint(20) NOT NULL,
  `bank_titulaire` varchar(200) DEFAULT NULL,
  `bank_name` varchar(200) DEFAULT NULL,
  `bank_postal` varchar(50) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `prerequis_modules`
--

CREATE TABLE `prerequis_modules` (
  `idPrerequis` bigint(20) NOT NULL,
  `prerequis_name` text DEFAULT NULL,
  `idModule` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `prestation_modules`
--

CREATE TABLE `prestation_modules` (
  `idPrestation` bigint(20) NOT NULL,
  `prestation_name` text DEFAULT NULL,
  `idModule` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `programmes`
--

CREATE TABLE `programmes` (
  `idProgramme` bigint(20) NOT NULL,
  `program_title` varchar(255) DEFAULT NULL,
  `program_description` text DEFAULT NULL,
  `idModule` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `project_forms`
--

CREATE TABLE `project_forms` (
  `idProjet` bigint(20) NOT NULL,
  `idFormateur` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `project_invoices`
--

CREATE TABLE `project_invoices` (
  `idCfp` bigint(20) NOT NULL,
  `idEtp` bigint(20) NOT NULL,
  `idPaiement` bigint(20) NOT NULL,
  `idTypeInvoice` bigint(20) NOT NULL,
  `idDevise` bigint(20) NOT NULL,
  `dateInvoice` date DEFAULT NULL,
  `invoiceStatus` tinyint(4) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `project_materials`
--

CREATE TABLE `project_materials` (
  `idProjet` bigint(20) NOT NULL,
  `idMateriel` bigint(20) NOT NULL,
  `idRespMateriel` int(11) NOT NULL,
  `nombre` int(11) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `project_restaurations`
--

CREATE TABLE `project_restaurations` (
  `idProjet` bigint(20) DEFAULT NULL,
  `idRestauration` tinyint(4) DEFAULT NULL,
  `paidBy` int(11) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `project_sub_contracts`
--

CREATE TABLE `project_sub_contracts` (
  `idProjet` bigint(20) NOT NULL,
  `idSubContractor` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `projets`
--

CREATE TABLE `projets` (
  `idProjet` bigint(20) NOT NULL,
  `referenceEtp` varchar(100) DEFAULT NULL,
  `project_title` varchar(200) DEFAULT NULL,
  `projectName` varchar(50) DEFAULT NULL,
  `dateDebut` date DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `lieu` text DEFAULT NULL,
  `idModule` bigint(20) NOT NULL,
  `idVilleCoded` bigint(11) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `idModalite` int(11) NOT NULL,
  `idTypeProjet` int(11) NOT NULL,
  `idSalle` int(11) NOT NULL,
  `project_description` text DEFAULT NULL,
  `project_num_fmfp` varchar(100) DEFAULT NULL,
  `project_is_active` tinyint(4) DEFAULT 0,
  `project_is_reserved` tinyint(4) DEFAULT 0,
  `project_is_cancelled` tinyint(4) DEFAULT 0,
  `project_is_repported` tinyint(4) DEFAULT 0,
  `project_is_trashed` tinyint(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `project_price_pedagogique` decimal(15,2) DEFAULT 0.00,
  `project_price_annexe` decimal(15,2) DEFAULT 0.00,
  `total_ht` decimal(10,0) DEFAULT 0,
  `total_ttc` decimal(10,0) DEFAULT 0,
  `total_ht_etp` decimal(10,0) DEFAULT 0,
  `total_ttc_etp` decimal(10,0) DEFAULT 0,
  `project_is_closed` tinyint(4) DEFAULT 0,
  `taxe` decimal(10,0) DEFAULT 0,
  `idDossier` bigint(20) DEFAULT NULL,
  `total_ht_sub_contractor` decimal(10,0) DEFAULT 0,
  `total_ttc_sub_contractor` decimal(10,0) DEFAULT 0,
  `project_is_archived` tinyint(4) DEFAULT 0,
  `link` text DEFAULT NULL,
  `secret_code` varchar(250) DEFAULT NULL,
  `project_date_cold_evaluation` date DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `prospects`
--

CREATE TABLE `prospects` (
  `id` bigint(20) NOT NULL,
  `prospect_name` varchar(250) DEFAULT NULL,
  `idCustomer` bigint(20) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `publicites`
--

CREATE TABLE `publicites` (
  `id` bigint(20) NOT NULL,
  `idModule` bigint(20) DEFAULT NULL,
  `date_ajout` date DEFAULT NULL,
  `rang_affichage` int(11) DEFAULT NULL,
  `is_active` int(11) DEFAULT 0,
  `idType` int(11) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `pub_simples`
--

CREATE TABLE `pub_simples` (
  `id` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `qcm`
--

CREATE TABLE `qcm` (
  `idQCM` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `intituleQCM` varchar(200) NOT NULL,
  `descriptionQCM` text DEFAULT NULL,
  `idDomaine` bigint(20) DEFAULT NULL,
  `prixUnitaire` decimal(10,2) NOT NULL COMMENT 'En crédit mais pas en monnaie réel',
  `statut` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 pour inactif, 1 pour actif',
  `duree` int(11) NOT NULL COMMENT 'Durée en secondes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) COMMENT='Table pour stockées la branche principale d''un système de quiz';

-- --------------------------------------------------------

--
-- Structure de la table `qcm_bareme`
--

CREATE TABLE `qcm_bareme` (
  `idBareme` bigint(20) NOT NULL,
  `idQCM` bigint(20) DEFAULT NULL,
  `minPoints` int(11) NOT NULL COMMENT 'Nombre minimum de points pour ce niveau',
  `maxPoints` int(11) NOT NULL COMMENT 'Nombre maximum de points pour ce niveau',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_niveau` bigint(20) UNSIGNED DEFAULT NULL
) COMMENT='Barème des points pour chaque QCM';

-- --------------------------------------------------------

--
-- Structure de la table `qcm_category_evaluations`
--

CREATE TABLE `qcm_category_evaluations` (
  `id` bigint(20) NOT NULL,
  `idQCM` bigint(20) DEFAULT NULL,
  `idCategorie` bigint(20) DEFAULT NULL,
  `id_niveau` bigint(20) UNSIGNED NOT NULL,
  `min_percentage` float DEFAULT NULL,
  `max_percentage` float DEFAULT NULL,
  `description` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `qcm_invitations`
--

CREATE TABLE `qcm_invitations` (
  `idInvitation` bigint(20) NOT NULL,
  `idQCM` bigint(20) DEFAULT NULL,
  `idEmployeur` bigint(20) DEFAULT NULL,
  `idEmploye` bigint(20) DEFAULT NULL,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `custom_message` text DEFAULT NULL,
  `status` enum('pending','accepted','expired') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `qcm_invit_camps`
--

CREATE TABLE `qcm_invit_camps` (
  `idInvitCamp` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `qcm_invit_camp_invitations`
--

CREATE TABLE `qcm_invit_camp_invitations` (
  `id` bigint(20) NOT NULL,
  `invit_camp_id` bigint(20) DEFAULT NULL,
  `invitation_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `qcm_questions`
--

CREATE TABLE `qcm_questions` (
  `idQuestion` bigint(20) NOT NULL,
  `idQCM` bigint(20) DEFAULT NULL,
  `idImageQ` bigint(20) DEFAULT NULL,
  `texteQuestion` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idTypeQcmQuestion` bigint(20) UNSIGNED NOT NULL DEFAULT 1
) COMMENT='Table pour stocker les questions d''un quiz';

-- --------------------------------------------------------

--
-- Structure de la table `qcm_reponses`
--

CREATE TABLE `qcm_reponses` (
  `idReponse` bigint(20) NOT NULL,
  `idQuestion` bigint(20) DEFAULT NULL,
  `categorie_id` bigint(20) DEFAULT NULL,
  `texteReponse` text NOT NULL,
  `explicationReponse` text DEFAULT NULL COMMENT 'explication reponse',
  `points` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) COMMENT='Table pour stocker les réponses d''une question dans un quiz';

-- --------------------------------------------------------

--
-- Structure de la table `questions`
--

CREATE TABLE `questions` (
  `idQuestion` bigint(20) NOT NULL,
  `question` text DEFAULT NULL,
  `idTypeQuestion` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `quizz_colds`
--

CREATE TABLE `quizz_colds` (
  `id` bigint(20) NOT NULL,
  `quizz_cold_name` char(250) DEFAULT NULL,
  `idQuizzType` int(11) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `quizz_levels`
--

CREATE TABLE `quizz_levels` (
  `id` int(11) NOT NULL,
  `quizz_level_value` tinyint(4) DEFAULT NULL,
  `quizz_level_desc` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `quizz_questions`
--

CREATE TABLE `quizz_questions` (
  `idQuestion` bigint(20) NOT NULL,
  `question` text DEFAULT NULL,
  `idSection` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `quizz_types`
--

CREATE TABLE `quizz_types` (
  `id` int(11) NOT NULL,
  `quizz_name` varchar(250) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `rcs_names`
--

CREATE TABLE `rcs_names` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `reasons`
--

CREATE TABLE `reasons` (
  `id` int(11) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `reglement`
--

CREATE TABLE `reglement` (
  `id` bigint(20) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `contenu` text NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `reponses`
--

CREATE TABLE `reponses` (
  `idReponse` bigint(20) NOT NULL,
  `reponse` text DEFAULT NULL,
  `idQuestion` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `reponses_utilisateurs`
--

CREATE TABLE `reponses_utilisateurs` (
  `idReponseUtilisateur` bigint(20) NOT NULL,
  `idSession` bigint(20) DEFAULT NULL,
  `idQuestion` bigint(20) DEFAULT NULL,
  `idReponse` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `reservation_participant`
--

CREATE TABLE `reservation_participant` (
  `id` bigint(20) NOT NULL,
  `idReservation` bigint(20) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `fonction` varchar(255) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `reservation_responsable`
--

CREATE TABLE `reservation_responsable` (
  `id` bigint(20) NOT NULL,
  `idReservation` bigint(20) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `fonction` varchar(255) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `resp_materiels`
--

CREATE TABLE `resp_materiels` (
  `idRespMateriel` int(11) NOT NULL,
  `respMateriel` varchar(100) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `restaurations`
--

CREATE TABLE `restaurations` (
  `idRestauration` tinyint(4) NOT NULL,
  `typeRestauration` varchar(50) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `reward_catalogue_contents`
--

CREATE TABLE `reward_catalogue_contents` (
  `id` int(11) NOT NULL,
  `id_module_reward` bigint(20) NOT NULL,
  `idModule` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `reward_scopes`
--

CREATE TABLE `reward_scopes` (
  `id` smallint(6) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `reward_types`
--

CREATE TABLE `reward_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) NOT NULL,
  `roleName` varchar(50) DEFAULT NULL,
  `roleDescription` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `role_users`
--

CREATE TABLE `role_users` (
  `id` bigint(20) NOT NULL,
  `isActive` tinyint(4) NOT NULL DEFAULT 1,
  `hasRole` tinyint(4) NOT NULL DEFAULT 0,
  `role_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_is_in_service` tinyint(4) DEFAULT 1
);

-- --------------------------------------------------------

--
-- Structure de la table `salles`
--

CREATE TABLE `salles` (
  `idSalle` int(11) NOT NULL,
  `salle_name` varchar(255) DEFAULT NULL,
  `idLieu` bigint(20) NOT NULL,
  `salle_image` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `seances`
--

CREATE TABLE `seances` (
  `idSeance` bigint(20) NOT NULL,
  `dateSeance` date DEFAULT NULL,
  `heureDebut` time DEFAULT NULL,
  `heureFin` time DEFAULT NULL,
  `id_google_seance` text DEFAULT NULL,
  `idProjet` bigint(20) NOT NULL,
  `intervalle` varchar(30) DEFAULT NULL,
  `isDone` tinyint(1) DEFAULT 0,
  `session_catch_up` tinyint(1) NOT NULL DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `secteurs`
--

CREATE TABLE `secteurs` (
  `idSecteur` int(11) NOT NULL,
  `secteur` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `sections`
--

CREATE TABLE `sections` (
  `idSection` bigint(20) NOT NULL,
  `section` varchar(255) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `section_documents`
--

CREATE TABLE `section_documents` (
  `idSectionDocument` bigint(20) NOT NULL,
  `section_document` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `sessions_test`
--

CREATE TABLE `sessions_test` (
  `idSession` bigint(20) NOT NULL,
  `idUtilisateur` bigint(20) DEFAULT NULL,
  `idQCM` bigint(20) DEFAULT NULL,
  `invitationId` bigint(20) UNSIGNED DEFAULT NULL,
  `campId` bigint(20) UNSIGNED DEFAULT NULL,
  `dateDebut` datetime NOT NULL,
  `dateFin` datetime DEFAULT NULL,
  `totalPoints` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) COMMENT='Table pour stockées les sessions de test d''un utilisateur';

-- --------------------------------------------------------

--
-- Structure de la table `sexes`
--

CREATE TABLE `sexes` (
  `idSexe` int(11) NOT NULL,
  `sexe` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `skill_matrix`
--

CREATE TABLE `skill_matrix` (
  `id` bigint(20) NOT NULL,
  `skill_score_before` smallint(6) DEFAULT NULL,
  `skill_score_after` smallint(6) DEFAULT NULL,
  `id_module_skill` int(11) NOT NULL,
  `idEmploye` bigint(20) NOT NULL,
  `idProjet` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `specialtites`
--

CREATE TABLE `specialtites` (
  `idSp` int(11) NOT NULL,
  `specialite` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `stat_names`
--

CREATE TABLE `stat_names` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `submenu_refresh`
--

CREATE TABLE `submenu_refresh` (
  `idSubmenuRefresh` bigint(20) NOT NULL,
  `submenu` varchar(200) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `idMenuRefresh` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `subscriptions`
--

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

-- --------------------------------------------------------

--
-- Structure de la table `subscription_usage`
--

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

-- --------------------------------------------------------

--
-- Structure de la table `sub_contractors`
--

CREATE TABLE `sub_contractors` (
  `idSubContractor` bigint(20) NOT NULL,
  `idCfp` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `suivi_envois`
--

CREATE TABLE `suivi_envois` (
  `id` bigint(20) NOT NULL,
  `idProjet` bigint(20) NOT NULL,
  `idEmploye` bigint(20) NOT NULL,
  `idDocument` bigint(20) NOT NULL,
  `type_document` varchar(200) NOT NULL,
  `date_envoi` datetime DEFAULT current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `testing_timer`
--

CREATE TABLE `testing_timer` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `qcm_id` bigint(20) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `finished_test` timestamp NOT NULL DEFAULT current_timestamp(),
  `duration` bigint(20) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `traits`
--

CREATE TABLE `traits` (
  `id` int(11) NOT NULL,
  `idCustomer` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `transaction_history`
--

CREATE TABLE `transaction_history` (
  `idTransaction` bigint(20) NOT NULL,
  `idUser` bigint(20) DEFAULT NULL,
  `transaction_ref` varchar(250) DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `typeTransaction` enum('credit','debit') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
);

-- --------------------------------------------------------

--
-- Structure de la table `Type`
--

CREATE TABLE `Type` (
  `id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_abns`
--

CREATE TABLE `type_abns` (
  `idTypeAbn` int(11) NOT NULL,
  `typeAbn` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_customers`
--

CREATE TABLE `type_customers` (
  `idTypeCustomer` int(2) NOT NULL,
  `typeCustomer` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_documents`
--

CREATE TABLE `type_documents` (
  `idTypeDocument` bigint(20) NOT NULL,
  `idSectionDocument` bigint(20) DEFAULT NULL,
  `type_document` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_entreprises`
--

CREATE TABLE `type_entreprises` (
  `idTypeEtp` smallint(6) NOT NULL,
  `type_etp` varchar(100) DEFAULT NULL,
  `type_etp_desc` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_factures`
--

CREATE TABLE `type_factures` (
  `idTypeFacture` int(11) NOT NULL,
  `typeFacture` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_formateurs`
--

CREATE TABLE `type_formateurs` (
  `idTypeFormateur` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_fournisseurs`
--

CREATE TABLE `type_fournisseurs` (
  `idTypefournisseur` int(11) NOT NULL,
  `typeFournisseur` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_images`
--

CREATE TABLE `type_images` (
  `idTypeImage` bigint(20) NOT NULL,
  `typeImage` varchar(200) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_invoices`
--

CREATE TABLE `type_invoices` (
  `idTypeInvoice` bigint(20) NOT NULL,
  `typeInvoice` varchar(100) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_materiels`
--

CREATE TABLE `type_materiels` (
  `idTypeMateriel` smallint(6) NOT NULL,
  `typeMateriel` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_modules`
--

CREATE TABLE `type_modules` (
  `idTypeModule` int(11) NOT NULL,
  `typeModule` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_projets`
--

CREATE TABLE `type_projets` (
  `idTypeProjet` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_publicites`
--

CREATE TABLE `type_publicites` (
  `id` int(11) NOT NULL,
  `type_pub_name` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_qcm`
--

CREATE TABLE `type_qcm` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_questions`
--

CREATE TABLE `type_questions` (
  `idTypeQuestion` bigint(20) NOT NULL,
  `typeQuestion` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `type_services`
--

CREATE TABLE `type_services` (
  `idTypeService` bigint(20) NOT NULL,
  `nomTypeService` varchar(255) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `unites`
--

CREATE TABLE `unites` (
  `idUnite` bigint(20) NOT NULL,
  `unite_name` varchar(200) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `reset_token_created_at` TIMESTAMP NULL,
  `matricule` varchar(100) DEFAULT NULL,
  `firstName` varchar(150) DEFAULT NULL,
  `cin` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `adresse` varchar(150) DEFAULT NULL,
  `photo` text DEFAULT NULL,
  `dateNais` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_addr_quartier` varchar(100) DEFAULT NULL,
  `user_addr_lot` varchar(100) DEFAULT NULL,
  `user_addr_rue` varchar(100) DEFAULT NULL,
  `user_addr_code_postal` smallint(6) DEFAULT NULL,
  `idVille` int(11) DEFAULT 1,
  `user_is_deleted` tinyint(1) DEFAULT 0
);

-- --------------------------------------------------------

--
-- Structure de la table `val_comments`
--

CREATE TABLE `val_comments` (
  `idValComment` bigint(20) NOT NULL,
  `valComment` varchar(250) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `verifications_badge`
--

CREATE TABLE `verifications_badge` (
  `idVerification` bigint(20) NOT NULL,
  `idAttribution` bigint(20) NOT NULL,
  `code_verification` varchar(64) NOT NULL,
  `date_verification` datetime DEFAULT NULL,
  `ip_verification` varchar(45) DEFAULT NULL,
  `idProjet` bigint(20) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `video_refresh`
--

CREATE TABLE `video_refresh` (
  `idVideoRefresh` bigint(20) NOT NULL,
  `videoLink` text DEFAULT NULL,
  `videoDescription` text DEFAULT NULL,
  `isMenuRefresh` tinyint(1) DEFAULT 0,
  `idMenuRefresh` bigint(20) DEFAULT NULL,
  `idSubmenuRefresh` bigint(20) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `villes`
--

CREATE TABLE `villes` (
  `idVille` int(11) NOT NULL,
  `ville` varchar(50) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table `ville_codeds`
--

CREATE TABLE `ville_codeds` (
  `id` bigint(20) NOT NULL,
  `ville_name` varchar(150) DEFAULT NULL,
  `vi_code_postal` varchar(50) DEFAULT NULL,
  `idVille` int(11) NOT NULL
);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `abn_cfps`
--
ALTER TABLE `abn_cfps`
  ADD PRIMARY KEY (`idAbn`);

--
-- Index pour la table `abn_etps`
--
ALTER TABLE `abn_etps`
  ADD PRIMARY KEY (`idAbn`);

--
-- Index pour la table `abonnements`
--
ALTER TABLE `abonnements`
  ADD PRIMARY KEY (`idAbn`);

--
-- Index pour la table `acces`
--
ALTER TABLE `acces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `acces_ibfk_1` (`idCustomer`);

--
-- Index pour la table `accompagnement`
--
ALTER TABLE `accompagnement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `accompagnement_ibfk_1` (`idCustomer`);

--
-- Index pour la table `accueil`
--
ALTER TABLE `accueil`
  ADD PRIMARY KEY (`id`),
  ADD KEY `accueil_ibfk_1` (`idCustomer`);

--
-- Index pour la table `agences`
--
ALTER TABLE `agences`
  ADD PRIMARY KEY (`idAgence`),
  ADD KEY `idVilleCoded` (`idVilleCoded`),
  ADD KEY `idCustomer` (`idCustomer`);

--
-- Index pour la table `apprenants`
--
ALTER TABLE `apprenants`
  ADD PRIMARY KEY (`idEmploye`);

--
-- Index pour la table `app_country`
--
ALTER TABLE `app_country`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idCountry` (`idCountry`);

--
-- Index pour la table `attestations`
--
ALTER TABLE `attestations`
  ADD PRIMARY KEY (`idAttestation`),
  ADD KEY `idProjet` (`idProjet`),
  ADD KEY `idEmploye` (`idEmploye`),
  ADD KEY `idCfp` (`idCfp`),
  ADD KEY `number_attestation` (`number_attestation`) USING BTREE;

--
-- Index pour la table `attributions_badge`
--
ALTER TABLE `attributions_badge`
  ADD PRIMARY KEY (`idAttribution`),
  ADD UNIQUE KEY `idBadge` (`idBadge`,`idEmploye`,`idProjet`),
  ADD KEY `idEmploye` (`idEmploye`),
  ADD KEY `idProjet` (`idProjet`);

--
-- Index pour la table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`idBadge`),
  ADD UNIQUE KEY `identifiant_unique` (`identifiant_unique`),
  ADD KEY `idModule` (`idModule`),
  ADD KEY `idCfp` (`idCfp`);

--
-- Index pour la table `bankacounts`
--
ALTER TABLE `bankacounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ba_idCustomer` (`ba_idCustomer`),
  ADD KEY `ba_idPostal` (`ba_idPostal`);

--
-- Index pour la table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Index pour la table `batch_learners`
--
ALTER TABLE `batch_learners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `batch_id` (`batch_id`),
  ADD KEY `employe_id` (`employe_id`);

--
-- Index pour la table `categories_reponses`
--
ALTER TABLE `categories_reponses`
  ADD PRIMARY KEY (`idCategorie`);

--
-- Index pour la table `cfps`
--
ALTER TABLE `cfps`
  ADD PRIMARY KEY (`idCustomer`);

--
-- Index pour la table `cfp_etps`
--
ALTER TABLE `cfp_etps`
  ADD PRIMARY KEY (`idEtp`,`idCfp`),
  ADD KEY `idCfp` (`idCfp`);

--
-- Index pour la table `cfp_formateurs`
--
ALTER TABLE `cfp_formateurs`
  ADD PRIMARY KEY (`idFormateur`,`idCfp`),
  ADD KEY `idCfp` (`idCfp`);

--
-- Index pour la table `cfp_particuliers`
--
ALTER TABLE `cfp_particuliers`
  ADD PRIMARY KEY (`idCfp`,`idParticulier`),
  ADD KEY `idParticulier` (`idParticulier`);

--
-- Index pour la table `cfp_selected_by_admin`
--
ALTER TABLE `cfp_selected_by_admin`
  ADD PRIMARY KEY (`idSuperAdmin`,`idCfp`),
  ADD KEY `idCfp` (`idCfp`);

--
-- Index pour la table `cible_modules`
--
ALTER TABLE `cible_modules`
  ADD PRIMARY KEY (`idCible`),
  ADD KEY `idModule` (`idModule`);

--
-- Index pour la table `commissions_received`
--
ALTER TABLE `commissions_received`
  ADD PRIMARY KEY (`idCommissionReceived`),
  ADD KEY `commissions_received_credits_payments_FK` (`credit_payment_id`),
  ADD KEY `commissions_receiver_FK` (`receiver_id`);

--
-- Index pour la table `commissions_settings`
--
ALTER TABLE `commissions_settings`
  ADD PRIMARY KEY (`idCommissionSetting`),
  ADD UNIQUE KEY `unique_commission_setting` (`payment_type`,`currency`);

--
-- Index pour la table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nif` (`nif`),
  ADD UNIQUE KEY `stat` (`stat`),
  ADD UNIQUE KEY `rcs` (`rcs`),
  ADD KEY `idCustomer` (`idCustomer`);

--
-- Index pour la table `competences`
--
ALTER TABLE `competences`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `competences_badge`
--
ALTER TABLE `competences_badge`
  ADD PRIMARY KEY (`idCompetence`),
  ADD KEY `idBadge` (`idBadge`);

--
-- Index pour la table `conditions`
--
ALTER TABLE `conditions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conditions_ibfk_1` (`idCustomer`);

--
-- Index pour la table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`idContact`),
  ADD KEY `idLieu` (`idLieu`);

--
-- Index pour la table `content_files`
--
ALTER TABLE `content_files`
  ADD PRIMARY KEY (`idFile`),
  ADD KEY `idContent` (`idContent`);

--
-- Index pour la table `content_refresh`
--
ALTER TABLE `content_refresh`
  ADD PRIMARY KEY (`idContent`),
  ADD KEY `idModuleContent` (`idModuleContent`);

--
-- Index pour la table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `countriess`
--
ALTER TABLE `countriess`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nif_name` (`id_nif_name`),
  ADD KEY `id_currency` (`id_currency`);

--
-- Index pour la table `country_fulls`
--
ALTER TABLE `country_fulls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_rcs_name` (`id_rcs_name`),
  ADD KEY `id_stat_name` (`id_stat_name`);

--
-- Index pour la table `country_less`
--
ALTER TABLE `country_less`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `credits_packs`
--
ALTER TABLE `credits_packs`
  ADD PRIMARY KEY (`idPackCredit`);

--
-- Index pour la table `credits_payments`
--
ALTER TABLE `credits_payments`
  ADD PRIMARY KEY (`idCreditPayment`),
  ADD KEY `users_buy_credits_FK` (`user_id`),
  ADD KEY `credits_pack_FK` (`pack_credits_id`);

--
-- Index pour la table `credits_wallet`
--
ALTER TABLE `credits_wallet`
  ADD PRIMARY KEY (`idWallet`),
  ADD KEY `credits_wallet_users_FK` (`idUser`);

--
-- Index pour la table `criteres_badge`
--
ALTER TABLE `criteres_badge`
  ADD PRIMARY KEY (`idCritere`),
  ADD KEY `idBadge` (`idBadge`);

--
-- Index pour la table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`idCustomer`),
  ADD UNIQUE KEY `rcs` (`rcs`),
  ADD UNIQUE KEY `nif` (`nif`),
  ADD UNIQUE KEY `stat` (`stat`),
  ADD UNIQUE KEY `customerEmail` (`customerEmail`),
  ADD KEY `idSecteur` (`idSecteur`),
  ADD KEY `idTypeCustomer` (`idTypeCustomer`);

--
-- Index pour la table `c_emps`
--
ALTER TABLE `c_emps`
  ADD PRIMARY KEY (`idEmploye`),
  ADD KEY `id_cfp` (`id_cfp`);

--
-- Index pour la table `detail_abns`
--
ALTER TABLE `detail_abns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idAbn` (`idAbn`),
  ADD KEY `idCustomer` (`idCustomer`);

--
-- Index pour la table `detail_apprenants`
--
ALTER TABLE `detail_apprenants`
  ADD PRIMARY KEY (`idProjet`,`idEmploye`),
  ADD KEY `idEmploye` (`idEmploye`);

--
-- Index pour la table `detail_apprenant_inters`
--
ALTER TABLE `detail_apprenant_inters`
  ADD PRIMARY KEY (`idProjet`,`idEtp`,`idEmploye`),
  ADD KEY `idEtp` (`idEtp`),
  ADD KEY `idEmploye` (`idEmploye`);

--
-- Index pour la table `devises`
--
ALTER TABLE `devises`
  ADD PRIMARY KEY (`idDevise`);

--
-- Index pour la table `diplomes`
--
ALTER TABLE `diplomes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`idDocument`),
  ADD KEY `idDossier` (`idDossier`),
  ADD KEY `idTypeDocument` (`idTypeDocument`);

--
-- Index pour la table `domaine_formations`
--
ALTER TABLE `domaine_formations`
  ADD PRIMARY KEY (`idDomaine`);

--
-- Index pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD PRIMARY KEY (`idDossier`),
  ADD UNIQUE KEY `nomDossier` (`nomDossier`),
  ADD KEY `idCfp` (`idCfp`);

--
-- Index pour la table `emargements`
--
ALTER TABLE `emargements`
  ADD PRIMARY KEY (`idSeance`,`idEmploye`),
  ADD KEY `idEmploye` (`idEmploye`);

--
-- Index pour la table `employes`
--
ALTER TABLE `employes`
  ADD PRIMARY KEY (`idEmploye`),
  ADD KEY `idCustomer` (`idCustomer`),
  ADD KEY `idSexe` (`idSexe`),
  ADD KEY `idNiveau` (`idNiveau`);

--
-- Index pour la table `emp_debit_credit`
--
ALTER TABLE `emp_debit_credit`
  ADD PRIMARY KEY (`idDebitEmpEtp`),
  ADD KEY `transaction_history_emp_etp_users_FK` (`idUser`),
  ADD KEY `transaction_history_emp_etp_transaction_FK` (`idTransaction`);

--
-- Index pour la table `entreprises`
--
ALTER TABLE `entreprises`
  ADD PRIMARY KEY (`idCustomer`);

--
-- Index pour la table `equipments`
--
ALTER TABLE `equipments`
  ADD PRIMARY KEY (`idEquipment`),
  ADD KEY `idSalle` (`idSalle`);

--
-- Index pour la table `etp_groupeds`
--
ALTER TABLE `etp_groupeds`
  ADD PRIMARY KEY (`idEntreprise`),
  ADD KEY `idEntrepriseParent` (`idEntrepriseParent`);

--
-- Index pour la table `etp_groupes`
--
ALTER TABLE `etp_groupes`
  ADD PRIMARY KEY (`idEntreprise`);

--
-- Index pour la table `etp_informals`
--
ALTER TABLE `etp_informals`
  ADD PRIMARY KEY (`idEntreprise`);

--
-- Index pour la table `etp_singles`
--
ALTER TABLE `etp_singles`
  ADD PRIMARY KEY (`idEntreprise`);

--
-- Index pour la table `eval_apprenant`
--
ALTER TABLE `eval_apprenant`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `eval_chauds`
--
ALTER TABLE `eval_chauds`
  ADD PRIMARY KEY (`idEval_chaud`),
  ADD KEY `idProjet` (`idProjet`),
  ADD KEY `idEmploye` (`idEmploye`),
  ADD KEY `idQuestion` (`idQuestion`),
  ADD KEY `idExaminer` (`idExaminer`);

--
-- Index pour la table `eval_froids`
--
ALTER TABLE `eval_froids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idQuizzCold` (`idQuizzCold`),
  ADD KEY `idProjet` (`idProjet`),
  ADD KEY `idEmploye` (`idEmploye`);

--
-- Index pour la table `eval_froid_sents`
--
ALTER TABLE `eval_froid_sents`
  ADD PRIMARY KEY (`idProjet`,`idEtp`),
  ADD KEY `idEtp` (`idEtp`);

--
-- Index pour la table `experiences`
--
ALTER TABLE `experiences`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `features_slug_unique` (`slug`);

--
-- Index pour la table `feries`
--
ALTER TABLE `feries`
  ADD PRIMARY KEY (`idFerie`),
  ADD KEY `idCustomer` (`idCustomer`);

--
-- Index pour la table `financial_goals`
--
ALTER TABLE `financial_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_customer` (`id_customer`),
  ADD KEY `id_module` (`id_module`);

--
-- Index pour la table `fonctions`
--
ALTER TABLE `fonctions`
  ADD PRIMARY KEY (`idFonction`),
  ADD KEY `idCustomer` (`idCustomer`);

--
-- Index pour la table `formateurs`
--
ALTER TABLE `formateurs`
  ADD PRIMARY KEY (`idFormateur`),
  ADD KEY `idSp` (`idSp`);

--
-- Index pour la table `formateur_internes`
--
ALTER TABLE `formateur_internes`
  ADD PRIMARY KEY (`idFormateur`,`idEmploye`),
  ADD KEY `idEmploye` (`idEmploye`),
  ADD KEY `idEntreprise` (`idEntreprise`);

--
-- Index pour la table `forms`
--
ALTER TABLE `forms`
  ADD PRIMARY KEY (`idFormateur`),
  ADD KEY `idTypeFormateur` (`idTypeFormateur`),
  ADD KEY `idSexe` (`idSexe`);

--
-- Index pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  ADD PRIMARY KEY (`idFournisseur`),
  ADD UNIQUE KEY `emailFournisseur` (`emailFournisseur`),
  ADD KEY `idCustomer` (`idCustomer`),
  ADD KEY `idTypefournisseur` (`idTypefournisseur`),
  ADD KEY `idTypeService` (`idTypeService`);

--
-- Index pour la table `fournisseur_cfps`
--
ALTER TABLE `fournisseur_cfps`
  ADD PRIMARY KEY (`idFournisseur`);

--
-- Index pour la table `fournisseur_etps`
--
ALTER TABLE `fournisseur_etps`
  ADD PRIMARY KEY (`idFournisseur`);

--
-- Index pour la table `frais`
--
ALTER TABLE `frais`
  ADD PRIMARY KEY (`idFrais`);

--
-- Index pour la table `fraisprojet`
--
ALTER TABLE `fraisprojet`
  ADD PRIMARY KEY (`idFraisProjet`),
  ADD KEY `idProjet` (`idProjet`),
  ADD KEY `idFrais` (`idFrais`);

--
-- Index pour la table `f_emps`
--
ALTER TABLE `f_emps`
  ADD PRIMARY KEY (`idEmploye`),
  ADD KEY `id_formateur` (`id_formateur`);

--
-- Index pour la table `google_users`
--
ALTER TABLE `google_users`
  ADD PRIMARY KEY (`user_id`);

--
-- Index pour la table `iframes`
--
ALTER TABLE `iframes`
  ADD PRIMARY KEY (`idIframe`),
  ADD KEY `idCustomer` (`idCustomer`);

--
-- Index pour la table `ignoredConflitFormateur`
--
ALTER TABLE `ignoredConflitFormateur`
  ADD PRIMARY KEY (`idFormateur`,`idProjet`),
  ADD KEY `idProjet` (`idProjet`);

--
-- Index pour la table `ignoredConflitLieu`
--
ALTER TABLE `ignoredConflitLieu`
  ADD PRIMARY KEY (`idSalle`,`idProjet`),
  ADD KEY `idProjet` (`idProjet`);

--
-- Index pour la table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`idImages`),
  ADD KEY `idProjet` (`idProjet`),
  ADD KEY `idTypeImage` (`idTypeImage`);

--
-- Index pour la table `images_qcm`
--
ALTER TABLE `images_qcm`
  ADD PRIMARY KEY (`idImageQ`),
  ADD KEY `idTypeImage` (`idTypeImage`);

--
-- Index pour la table `internes`
--
ALTER TABLE `internes`
  ADD PRIMARY KEY (`idProjet`),
  ADD KEY `idEtp` (`idEtp`);

--
-- Index pour la table `inters`
--
ALTER TABLE `inters`
  ADD PRIMARY KEY (`idProjet`),
  ADD KEY `idCfp` (`idCfp`);

--
-- Index pour la table `inter_entreprises`
--
ALTER TABLE `inter_entreprises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idProjet` (`idProjet`),
  ADD KEY `idEtp` (`idEtp`);

--
-- Index pour la table `intras`
--
ALTER TABLE `intras`
  ADD PRIMARY KEY (`idProjet`),
  ADD KEY `idPaiement` (`idPaiement`),
  ADD KEY `idCfp` (`idCfp`);

--
-- Index pour la table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`idInvoice`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idPaiement` (`idPaiement`),
  ADD KEY `idTypeFacture` (`idTypeFacture`),
  ADD KEY `fk_idBankAcount` (`idBankAcount`),
  ADD KEY `fk_idCustomer` (`idCustomer`),
  ADD KEY `fk_idEntreprise` (`idEntreprise`),
  ADD KEY `fk_idCompany` (`idCompany`),
  ADD KEY `fk_invoices_type_client` (`idTypeClient`);

--
-- Index pour la table `invoice_acomptes`
--
ALTER TABLE `invoice_acomptes`
  ADD PRIMARY KEY (`idInvoice`);

--
-- Index pour la table `invoice_deleted`
--
ALTER TABLE `invoice_deleted`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idInvoice` (`idInvoice`);

--
-- Index pour la table `invoice_details`
--
ALTER TABLE `invoice_details`
  ADD PRIMARY KEY (`idItem`),
  ADD KEY `idInvoice` (`idInvoice`),
  ADD KEY `idUnite` (`idUnite`),
  ADD KEY `fk_idProjet` (`idProjet`);

--
-- Index pour la table `invoice_details_profo`
--
ALTER TABLE `invoice_details_profo`
  ADD PRIMARY KEY (`idItem`),
  ADD KEY `idInvoice` (`idInvoice`),
  ADD KEY `idUnite` (`idUnite`),
  ADD KEY `idModule` (`idModule`);

--
-- Index pour la table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `fk_payment_method` (`payment_method_id`),
  ADD KEY `fk_idBankCompte` (`payment_bank_id`),
  ADD KEY `fk_idMobileMoney` (`payment_mobilemoney_id`);

--
-- Index pour la table `invoice_standards`
--
ALTER TABLE `invoice_standards`
  ADD PRIMARY KEY (`idInvoice`);

--
-- Index pour la table `invoice_status`
--
ALTER TABLE `invoice_status`
  ADD PRIMARY KEY (`idInvoiceStatus`);

--
-- Index pour la table `invoice_type_client`
--
ALTER TABLE `invoice_type_client`
  ADD PRIMARY KEY (`idType`);

--
-- Index pour la table `langues`
--
ALTER TABLE `langues`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `launchers`
--
ALTER TABLE `launchers`
  ADD PRIMARY KEY (`idLauncher`),
  ADD KEY `idCountry` (`idCountry`);

--
-- Index pour la table `lieux`
--
ALTER TABLE `lieux`
  ADD PRIMARY KEY (`idLieu`),
  ADD KEY `idVille` (`idVille`),
  ADD KEY `idLieuType` (`idLieuType`),
  ADD KEY `idVilleCoded` (`idVilleCoded`);

--
-- Index pour la table `lieu_privates`
--
ALTER TABLE `lieu_privates`
  ADD PRIMARY KEY (`idLieu`),
  ADD KEY `idCustomer` (`idCustomer`);

--
-- Index pour la table `lieu_publics`
--
ALTER TABLE `lieu_publics`
  ADD PRIMARY KEY (`idLieu`);

--
-- Index pour la table `lieu_types`
--
ALTER TABLE `lieu_types`
  ADD PRIMARY KEY (`idLieuType`);

--
-- Index pour la table `marketplace_images`
--
ALTER TABLE `marketplace_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mrkimg_ibfk_1` (`idCustomer`);

--
-- Index pour la table `materiel_cfps`
--
ALTER TABLE `materiel_cfps`
  ADD PRIMARY KEY (`idMateriel`),
  ADD KEY `idCfp` (`idCfp`),
  ADD KEY `idTypeMateriel` (`idTypeMateriel`);

--
-- Index pour la table `materiel_etps`
--
ALTER TABLE `materiel_etps`
  ADD PRIMARY KEY (`idMateriel`),
  ADD KEY `idEtp` (`idEtp`),
  ADD KEY `idTypeMateriel` (`idTypeMateriel`);

--
-- Index pour la table `materiel_externes`
--
ALTER TABLE `materiel_externes`
  ADD PRIMARY KEY (`idMateriel`),
  ADD KEY `idFournisseur` (`idFournisseur`);

--
-- Index pour la table `materiel_externe_etps`
--
ALTER TABLE `materiel_externe_etps`
  ADD PRIMARY KEY (`idMateriel`),
  ADD KEY `idFournisseur` (`idFournisseur`);

--
-- Index pour la table `materiel_internes`
--
ALTER TABLE `materiel_internes`
  ADD PRIMARY KEY (`idMateriel`);

--
-- Index pour la table `materiel_interne_etps`
--
ALTER TABLE `materiel_interne_etps`
  ADD PRIMARY KEY (`idMateriel`);

--
-- Index pour la table `mdls`
--
ALTER TABLE `mdls`
  ADD PRIMARY KEY (`idModule`),
  ADD KEY `idDomaine` (`idDomaine`),
  ADD KEY `idCustomer` (`idCustomer`),
  ADD KEY `idTypeModule` (`idTypeModule`);

--
-- Index pour la table `menu_refresh`
--
ALTER TABLE `menu_refresh`
  ADD PRIMARY KEY (`idMenuRefresh`),
  ADD KEY `idModule` (`idModule`);

--
-- Index pour la table `mobilemoneyacounts`
--
ALTER TABLE `mobilemoneyacounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mm_idCustomer` (`mm_idCustomer`);

--
-- Index pour la table `modalites`
--
ALTER TABLE `modalites`
  ADD PRIMARY KEY (`idModalite`);

--
-- Index pour la table `mode_paiements`
--
ALTER TABLE `mode_paiements`
  ADD PRIMARY KEY (`idPaiement`);

--
-- Index pour la table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`idModule`);

--
-- Index pour la table `module_internes`
--
ALTER TABLE `module_internes`
  ADD PRIMARY KEY (`idModule`);

--
-- Index pour la table `module_levels`
--
ALTER TABLE `module_levels`
  ADD PRIMARY KEY (`idLevel`);

--
-- Index pour la table `module_program_contents`
--
ALTER TABLE `module_program_contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idProgramme` (`idProgramme`);

--
-- Index pour la table `module_ressources`
--
ALTER TABLE `module_ressources`
  ADD PRIMARY KEY (`idModuleRessource`),
  ADD KEY `idModule` (`idModule`);

--
-- Index pour la table `module_rewards`
--
ALTER TABLE `module_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idCfp` (`idCfp`),
  ADD KEY `idEtp` (`idEtp`),
  ADD KEY `id_reward_scope` (`id_reward_scope`),
  ADD KEY `id_reward_type` (`id_reward_type`);

--
-- Index pour la table `module_reward_fulls`
--
ALTER TABLE `module_reward_fulls`
  ADD PRIMARY KEY (`id_module_reward`);

--
-- Index pour la table `module_reward_less`
--
ALTER TABLE `module_reward_less`
  ADD PRIMARY KEY (`id_module_reward`);

--
-- Index pour la table `module_skills`
--
ALTER TABLE `module_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idModule` (`idModule`);

--
-- Index pour la table `nif_names`
--
ALTER TABLE `nif_names`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `niveau_etudes`
--
ALTER TABLE `niveau_etudes`
  ADD PRIMARY KEY (`idNiveau`);

--
-- Index pour la table `niveau_gamification`
--
ALTER TABLE `niveau_gamification`
  ADD PRIMARY KEY (`numero`);

--
-- Index pour la table `niveau_qcm`
--
ALTER TABLE `niveau_qcm`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `objectif_modules`
--
ALTER TABLE `objectif_modules`
  ADD PRIMARY KEY (`idObjectif`),
  ADD KEY `idModule` (`idModule`);

--
-- Index pour la table `opportunites`
--
ALTER TABLE `opportunites`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`idPaiement`);

--
-- Index pour la table `particuliers`
--
ALTER TABLE `particuliers`
  ADD PRIMARY KEY (`idParticulier`);

--
-- Index pour la table `particulier_projet`
--
ALTER TABLE `particulier_projet`
  ADD PRIMARY KEY (`idParticulier`,`idProjet`),
  ADD KEY `idProjet` (`idProjet`);

--
-- Index pour la table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `tokenable_index` (`tokenable_type`,`tokenable_id`);

--
-- Index pour la table `place_etp_from_cfps`
--
ALTER TABLE `place_etp_from_cfps`
  ADD PRIMARY KEY (`idLieu`),
  ADD KEY `idEntreprise` (`idEntreprise`),
  ADD KEY `idCfp` (`idCfp`);

--
-- Index pour la table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plans_slug_unique` (`slug`);

--
-- Index pour la table `pm_cheques`
--
ALTER TABLE `pm_cheques`
  ADD PRIMARY KEY (`idPaiement`);

--
-- Index pour la table `pm_types`
--
ALTER TABLE `pm_types`
  ADD PRIMARY KEY (`idTypePm`);

--
-- Index pour la table `pm_virements`
--
ALTER TABLE `pm_virements`
  ADD PRIMARY KEY (`idPaiement`),
  ADD UNIQUE KEY `bank_account_number` (`bank_account_number`);

--
-- Index pour la table `prerequis_modules`
--
ALTER TABLE `prerequis_modules`
  ADD PRIMARY KEY (`idPrerequis`),
  ADD KEY `idModule` (`idModule`);

--
-- Index pour la table `prestation_modules`
--
ALTER TABLE `prestation_modules`
  ADD PRIMARY KEY (`idPrestation`),
  ADD KEY `idModule` (`idModule`);

--
-- Index pour la table `programmes`
--
ALTER TABLE `programmes`
  ADD PRIMARY KEY (`idProgramme`),
  ADD KEY `idModule` (`idModule`);

--
-- Index pour la table `project_forms`
--
ALTER TABLE `project_forms`
  ADD PRIMARY KEY (`idProjet`,`idFormateur`),
  ADD KEY `idFormateur` (`idFormateur`);

--
-- Index pour la table `project_invoices`
--
ALTER TABLE `project_invoices`
  ADD PRIMARY KEY (`idCfp`,`idEtp`,`idPaiement`,`idTypeInvoice`,`idDevise`),
  ADD KEY `idEtp` (`idEtp`),
  ADD KEY `idPaiement` (`idPaiement`),
  ADD KEY `idTypeInvoice` (`idTypeInvoice`),
  ADD KEY `idDevise` (`idDevise`);

--
-- Index pour la table `project_materials`
--
ALTER TABLE `project_materials`
  ADD PRIMARY KEY (`idProjet`,`idMateriel`,`idRespMateriel`),
  ADD KEY `idMateriel` (`idMateriel`),
  ADD KEY `idRespMateriel` (`idRespMateriel`);

--
-- Index pour la table `project_restaurations`
--
ALTER TABLE `project_restaurations`
  ADD KEY `idProjet` (`idProjet`),
  ADD KEY `idRestauration` (`idRestauration`),
  ADD KEY `paidBy` (`paidBy`);

--
-- Index pour la table `project_sub_contracts`
--
ALTER TABLE `project_sub_contracts`
  ADD PRIMARY KEY (`idProjet`,`idSubContractor`),
  ADD KEY `idSubContractor` (`idSubContractor`);

--
-- Index pour la table `projets`
--
ALTER TABLE `projets`
  ADD PRIMARY KEY (`idProjet`),
  ADD KEY `idModule` (`idModule`),
  ADD KEY `idVille` (`idVilleCoded`),
  ADD KEY `idCustomer` (`idCustomer`),
  ADD KEY `idModalite` (`idModalite`),
  ADD KEY `idTypeProjet` (`idTypeProjet`),
  ADD KEY `idSalle` (`idSalle`),
  ADD KEY `idDossier` (`idDossier`);

--
-- Index pour la table `prospects`
--
ALTER TABLE `prospects`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `publicites`
--
ALTER TABLE `publicites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idType` (`idType`),
  ADD KEY `idModule` (`idModule`);

--
-- Index pour la table `pub_simples`
--
ALTER TABLE `pub_simples`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `qcm`
--
ALTER TABLE `qcm`
  ADD PRIMARY KEY (`idQCM`),
  ADD KEY `idDomaine` (`idDomaine`),
  ADD KEY `fk_qcm_user` (`user_id`);

--
-- Index pour la table `qcm_bareme`
--
ALTER TABLE `qcm_bareme`
  ADD PRIMARY KEY (`idBareme`),
  ADD KEY `idQCM` (`idQCM`),
  ADD KEY `fk_bareme_niveau` (`id_niveau`);

--
-- Index pour la table `qcm_category_evaluations`
--
ALTER TABLE `qcm_category_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idQCM` (`idQCM`),
  ADD KEY `idCategorie` (`idCategorie`),
  ADD KEY `niveau_inde` (`id_niveau`);

--
-- Index pour la table `qcm_invitations`
--
ALTER TABLE `qcm_invitations`
  ADD PRIMARY KEY (`idInvitation`),
  ADD KEY `fk_qcm` (`idQCM`),
  ADD KEY `fk_employe` (`idEmploye`),
  ADD KEY `fk_employeur` (`idEmployeur`);

--
-- Index pour la table `qcm_invit_camps`
--
ALTER TABLE `qcm_invit_camps`
  ADD PRIMARY KEY (`idInvitCamp`),
  ADD KEY `campaign_creator_FK` (`created_by`);

--
-- Index pour la table `qcm_invit_camp_invitations`
--
ALTER TABLE `qcm_invit_camp_invitations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invit_camp_id` (`invit_camp_id`),
  ADD KEY `invitation_id` (`invitation_id`);

--
-- Index pour la table `qcm_questions`
--
ALTER TABLE `qcm_questions`
  ADD PRIMARY KEY (`idQuestion`),
  ADD KEY `idQCM` (`idQCM`),
  ADD KEY `fk_qcm_questions_type` (`idTypeQcmQuestion`),
  ADD KEY `qcm_questions_ibfk_2` (`idImageQ`);

--
-- Index pour la table `qcm_reponses`
--
ALTER TABLE `qcm_reponses`
  ADD PRIMARY KEY (`idReponse`),
  ADD KEY `idQuestion` (`idQuestion`),
  ADD KEY `fk_categorie` (`categorie_id`);

--
-- Index pour la table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`idQuestion`),
  ADD KEY `idTypeQuestion` (`idTypeQuestion`);

--
-- Index pour la table `quizz_colds`
--
ALTER TABLE `quizz_colds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idQuizzType` (`idQuizzType`);

--
-- Index pour la table `quizz_levels`
--
ALTER TABLE `quizz_levels`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `quizz_questions`
--
ALTER TABLE `quizz_questions`
  ADD PRIMARY KEY (`idQuestion`),
  ADD KEY `idSection` (`idSection`);

--
-- Index pour la table `quizz_types`
--
ALTER TABLE `quizz_types`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `rcs_names`
--
ALTER TABLE `rcs_names`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `reasons`
--
ALTER TABLE `reasons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reasons_ibfk_1` (`idCustomer`);

--
-- Index pour la table `reglement`
--
ALTER TABLE `reglement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reglement_ibfk_1` (`idCustomer`);

--
-- Index pour la table `reponses`
--
ALTER TABLE `reponses`
  ADD PRIMARY KEY (`idReponse`),
  ADD KEY `idQuestion` (`idQuestion`);

--
-- Index pour la table `reponses_utilisateurs`
--
ALTER TABLE `reponses_utilisateurs`
  ADD PRIMARY KEY (`idReponseUtilisateur`),
  ADD KEY `idSession` (`idSession`),
  ADD KEY `idQuestion` (`idQuestion`),
  ADD KEY `idReponse` (`idReponse`);

--
-- Index pour la table `reservation_participant`
--
ALTER TABLE `reservation_participant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idReservation` (`idReservation`);

--
-- Index pour la table `reservation_responsable`
--
ALTER TABLE `reservation_responsable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idReservation` (`idReservation`);

--
-- Index pour la table `resp_materiels`
--
ALTER TABLE `resp_materiels`
  ADD PRIMARY KEY (`idRespMateriel`);

--
-- Index pour la table `restaurations`
--
ALTER TABLE `restaurations`
  ADD PRIMARY KEY (`idRestauration`);

--
-- Index pour la table `reward_catalogue_contents`
--
ALTER TABLE `reward_catalogue_contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_module_reward` (`id_module_reward`),
  ADD KEY `idModule` (`idModule`);

--
-- Index pour la table `reward_scopes`
--
ALTER TABLE `reward_scopes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `reward_types`
--
ALTER TABLE `reward_types`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `role_users`
--
ALTER TABLE `role_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_users_roles_FK` (`role_id`),
  ADD KEY `role_users_users_FK` (`user_id`);

--
-- Index pour la table `salles`
--
ALTER TABLE `salles`
  ADD PRIMARY KEY (`idSalle`),
  ADD KEY `idLieu` (`idLieu`);

--
-- Index pour la table `seances`
--
ALTER TABLE `seances`
  ADD PRIMARY KEY (`idSeance`),
  ADD KEY `idProjet` (`idProjet`);

--
-- Index pour la table `secteurs`
--
ALTER TABLE `secteurs`
  ADD PRIMARY KEY (`idSecteur`);

--
-- Index pour la table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`idSection`);

--
-- Index pour la table `section_documents`
--
ALTER TABLE `section_documents`
  ADD PRIMARY KEY (`idSectionDocument`);

--
-- Index pour la table `sessions_test`
--
ALTER TABLE `sessions_test`
  ADD PRIMARY KEY (`idSession`),
  ADD KEY `idQCM` (`idQCM`),
  ADD KEY `sessions_test_ibfk_2` (`idUtilisateur`),
  ADD KEY `invitation_index` (`invitationId`),
  ADD KEY `campagne_index` (`campId`);

--
-- Index pour la table `sexes`
--
ALTER TABLE `sexes`
  ADD PRIMARY KEY (`idSexe`);

--
-- Index pour la table `skill_matrix`
--
ALTER TABLE `skill_matrix`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_module_skill` (`id_module_skill`),
  ADD KEY `idEmploye` (`idEmploye`),
  ADD KEY `idProjet` (`idProjet`);

--
-- Index pour la table `specialtites`
--
ALTER TABLE `specialtites`
  ADD PRIMARY KEY (`idSp`);

--
-- Index pour la table `stat_names`
--
ALTER TABLE `stat_names`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `submenu_refresh`
--
ALTER TABLE `submenu_refresh`
  ADD PRIMARY KEY (`idSubmenuRefresh`),
  ADD KEY `idMenuRefresh` (`idMenuRefresh`);

--
-- Index pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscriptions_slug_unique` (`slug`),
  ADD KEY `subscriptions_subscriber_type_subscriber_id_index` (`subscriber_type`,`subscriber_id`);

--
-- Index pour la table `subscription_usage`
--
ALTER TABLE `subscription_usage`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sub_contractors`
--
ALTER TABLE `sub_contractors`
  ADD PRIMARY KEY (`idSubContractor`,`idCfp`),
  ADD KEY `idCfp` (`idCfp`);

--
-- Index pour la table `suivi_envois`
--
ALTER TABLE `suivi_envois`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idProjet` (`idProjet`),
  ADD KEY `idEmploye` (`idEmploye`);

--
-- Index pour la table `testing_timer`
--
ALTER TABLE `testing_timer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qcm_id` (`qcm_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `traits`
--
ALTER TABLE `traits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `traits_ibfk_1` (`idCustomer`);

--
-- Index pour la table `transaction_history`
--
ALTER TABLE `transaction_history`
  ADD PRIMARY KEY (`idTransaction`),
  ADD KEY `transaction_history_users_FK` (`idUser`);

--
-- Index pour la table `Type`
--
ALTER TABLE `Type`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `type_abns`
--
ALTER TABLE `type_abns`
  ADD PRIMARY KEY (`idTypeAbn`);

--
-- Index pour la table `type_customers`
--
ALTER TABLE `type_customers`
  ADD PRIMARY KEY (`idTypeCustomer`);

--
-- Index pour la table `type_documents`
--
ALTER TABLE `type_documents`
  ADD PRIMARY KEY (`idTypeDocument`),
  ADD KEY `idSectionDocument` (`idSectionDocument`);

--
-- Index pour la table `type_entreprises`
--
ALTER TABLE `type_entreprises`
  ADD PRIMARY KEY (`idTypeEtp`);

--
-- Index pour la table `type_factures`
--
ALTER TABLE `type_factures`
  ADD PRIMARY KEY (`idTypeFacture`);

--
-- Index pour la table `type_formateurs`
--
ALTER TABLE `type_formateurs`
  ADD PRIMARY KEY (`idTypeFormateur`);

--
-- Index pour la table `type_fournisseurs`
--
ALTER TABLE `type_fournisseurs`
  ADD PRIMARY KEY (`idTypefournisseur`);

--
-- Index pour la table `type_images`
--
ALTER TABLE `type_images`
  ADD PRIMARY KEY (`idTypeImage`);

--
-- Index pour la table `type_invoices`
--
ALTER TABLE `type_invoices`
  ADD PRIMARY KEY (`idTypeInvoice`);

--
-- Index pour la table `type_materiels`
--
ALTER TABLE `type_materiels`
  ADD PRIMARY KEY (`idTypeMateriel`);

--
-- Index pour la table `type_modules`
--
ALTER TABLE `type_modules`
  ADD PRIMARY KEY (`idTypeModule`);

--
-- Index pour la table `type_projets`
--
ALTER TABLE `type_projets`
  ADD PRIMARY KEY (`idTypeProjet`);

--
-- Index pour la table `type_publicites`
--
ALTER TABLE `type_publicites`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `type_qcm`
--
ALTER TABLE `type_qcm`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `type_questions`
--
ALTER TABLE `type_questions`
  ADD PRIMARY KEY (`idTypeQuestion`);

--
-- Index pour la table `type_services`
--
ALTER TABLE `type_services`
  ADD PRIMARY KEY (`idTypeService`);

--
-- Index pour la table `unites`
--
ALTER TABLE `unites`
  ADD PRIMARY KEY (`idUnite`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cin` (`cin`);

--
-- Index pour la table `val_comments`
--
ALTER TABLE `val_comments`
  ADD PRIMARY KEY (`idValComment`);

--
-- Index pour la table `verifications_badge`
--
ALTER TABLE `verifications_badge`
  ADD PRIMARY KEY (`idVerification`),
  ADD UNIQUE KEY `code_verification` (`code_verification`),
  ADD KEY `idAttribution` (`idAttribution`),
  ADD KEY `idProjet` (`idProjet`);

--
-- Index pour la table `video_refresh`
--
ALTER TABLE `video_refresh`
  ADD PRIMARY KEY (`idVideoRefresh`),
  ADD KEY `idMenuRefresh` (`idMenuRefresh`),
  ADD KEY `idSubmenuRefresh` (`idSubmenuRefresh`);

--
-- Index pour la table `villes`
--
ALTER TABLE `villes`
  ADD PRIMARY KEY (`idVille`);

--
-- Index pour la table `ville_codeds`
--
ALTER TABLE `ville_codeds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idVille` (`idVille`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `abn_cfps`
--
ALTER TABLE `abn_cfps`
  MODIFY `idAbn` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `abn_etps`
--
ALTER TABLE `abn_etps`
  MODIFY `idAbn` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `abonnements`
--
ALTER TABLE `abonnements`
  MODIFY `idAbn` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `acces`
--
ALTER TABLE `acces`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `accompagnement`
--
ALTER TABLE `accompagnement`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `accueil`
--
ALTER TABLE `accueil`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `agences`
--
ALTER TABLE `agences`
  MODIFY `idAgence` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `app_country`
--
ALTER TABLE `app_country`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `attestations`
--
ALTER TABLE `attestations`
  MODIFY `idAttestation` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `attributions_badge`
--
ALTER TABLE `attributions_badge`
  MODIFY `idAttribution` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `badges`
--
ALTER TABLE `badges`
  MODIFY `idBadge` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bankacounts`
--
ALTER TABLE `bankacounts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `batch_learners`
--
ALTER TABLE `batch_learners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categories_reponses`
--
ALTER TABLE `categories_reponses`
  MODIFY `idCategorie` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cible_modules`
--
ALTER TABLE `cible_modules`
  MODIFY `idCible` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commissions_received`
--
ALTER TABLE `commissions_received`
  MODIFY `idCommissionReceived` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commissions_settings`
--
ALTER TABLE `commissions_settings`
  MODIFY `idCommissionSetting` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `competences`
--
ALTER TABLE `competences`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `competences_badge`
--
ALTER TABLE `competences_badge`
  MODIFY `idCompetence` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `conditions`
--
ALTER TABLE `conditions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `idContact` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `content_files`
--
ALTER TABLE `content_files`
  MODIFY `idFile` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `content_refresh`
--
ALTER TABLE `content_refresh`
  MODIFY `idContent` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `credits_packs`
--
ALTER TABLE `credits_packs`
  MODIFY `idPackCredit` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `credits_payments`
--
ALTER TABLE `credits_payments`
  MODIFY `idCreditPayment` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `credits_wallet`
--
ALTER TABLE `credits_wallet`
  MODIFY `idWallet` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `criteres_badge`
--
ALTER TABLE `criteres_badge`
  MODIFY `idCritere` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `detail_abns`
--
ALTER TABLE `detail_abns`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `devises`
--
ALTER TABLE `devises`
  MODIFY `idDevise` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `diplomes`
--
ALTER TABLE `diplomes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `idDocument` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `domaine_formations`
--
ALTER TABLE `domaine_formations`
  MODIFY `idDomaine` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dossiers`
--
ALTER TABLE `dossiers`
  MODIFY `idDossier` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `emp_debit_credit`
--
ALTER TABLE `emp_debit_credit`
  MODIFY `idDebitEmpEtp` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `equipments`
--
ALTER TABLE `equipments`
  MODIFY `idEquipment` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `eval_apprenant`
--
ALTER TABLE `eval_apprenant`
  MODIFY `id` mediumint(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `eval_chauds`
--
ALTER TABLE `eval_chauds`
  MODIFY `idEval_chaud` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `eval_froids`
--
ALTER TABLE `eval_froids`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `experiences`
--
ALTER TABLE `experiences`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `features`
--
ALTER TABLE `features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `feries`
--
ALTER TABLE `feries`
  MODIFY `idFerie` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `financial_goals`
--
ALTER TABLE `financial_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fonctions`
--
ALTER TABLE `fonctions`
  MODIFY `idFonction` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  MODIFY `idFournisseur` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `frais`
--
ALTER TABLE `frais`
  MODIFY `idFrais` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fraisprojet`
--
ALTER TABLE `fraisprojet`
  MODIFY `idFraisProjet` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `iframes`
--
ALTER TABLE `iframes`
  MODIFY `idIframe` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `images`
--
ALTER TABLE `images`
  MODIFY `idImages` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `images_qcm`
--
ALTER TABLE `images_qcm`
  MODIFY `idImageQ` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `internes`
--
ALTER TABLE `internes`
  MODIFY `idProjet` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inters`
--
ALTER TABLE `inters`
  MODIFY `idProjet` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inter_entreprises`
--
ALTER TABLE `inter_entreprises`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `intras`
--
ALTER TABLE `intras`
  MODIFY `idProjet` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `idInvoice` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `invoice_deleted`
--
ALTER TABLE `invoice_deleted`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `invoice_details`
--
ALTER TABLE `invoice_details`
  MODIFY `idItem` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `invoice_details_profo`
--
ALTER TABLE `invoice_details_profo`
  MODIFY `idItem` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `invoice_status`
--
ALTER TABLE `invoice_status`
  MODIFY `idInvoiceStatus` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `langues`
--
ALTER TABLE `langues`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `launchers`
--
ALTER TABLE `launchers`
  MODIFY `idLauncher` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `lieux`
--
ALTER TABLE `lieux`
  MODIFY `idLieu` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `marketplace_images`
--
ALTER TABLE `marketplace_images`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `materiel_cfps`
--
ALTER TABLE `materiel_cfps`
  MODIFY `idMateriel` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `mdls`
--
ALTER TABLE `mdls`
  MODIFY `idModule` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `menu_refresh`
--
ALTER TABLE `menu_refresh`
  MODIFY `idMenuRefresh` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `mobilemoneyacounts`
--
ALTER TABLE `mobilemoneyacounts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `modalites`
--
ALTER TABLE `modalites`
  MODIFY `idModalite` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `mode_paiements`
--
ALTER TABLE `mode_paiements`
  MODIFY `idPaiement` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `modules`
--
ALTER TABLE `modules`
  MODIFY `idModule` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `module_internes`
--
ALTER TABLE `module_internes`
  MODIFY `idModule` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `module_levels`
--
ALTER TABLE `module_levels`
  MODIFY `idLevel` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `module_program_contents`
--
ALTER TABLE `module_program_contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `module_ressources`
--
ALTER TABLE `module_ressources`
  MODIFY `idModuleRessource` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `module_rewards`
--
ALTER TABLE `module_rewards`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `module_skills`
--
ALTER TABLE `module_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `niveau_etudes`
--
ALTER TABLE `niveau_etudes`
  MODIFY `idNiveau` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `niveau_qcm`
--
ALTER TABLE `niveau_qcm`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `objectif_modules`
--
ALTER TABLE `objectif_modules`
  MODIFY `idObjectif` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `opportunites`
--
ALTER TABLE `opportunites`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `idPaiement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `pm_types`
--
ALTER TABLE `pm_types`
  MODIFY `idTypePm` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `prerequis_modules`
--
ALTER TABLE `prerequis_modules`
  MODIFY `idPrerequis` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `prestation_modules`
--
ALTER TABLE `prestation_modules`
  MODIFY `idPrestation` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `programmes`
--
ALTER TABLE `programmes`
  MODIFY `idProgramme` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `projets`
--
ALTER TABLE `projets`
  MODIFY `idProjet` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `prospects`
--
ALTER TABLE `prospects`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `publicites`
--
ALTER TABLE `publicites`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qcm`
--
ALTER TABLE `qcm`
  MODIFY `idQCM` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qcm_bareme`
--
ALTER TABLE `qcm_bareme`
  MODIFY `idBareme` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qcm_category_evaluations`
--
ALTER TABLE `qcm_category_evaluations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qcm_invitations`
--
ALTER TABLE `qcm_invitations`
  MODIFY `idInvitation` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qcm_invit_camps`
--
ALTER TABLE `qcm_invit_camps`
  MODIFY `idInvitCamp` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qcm_invit_camp_invitations`
--
ALTER TABLE `qcm_invit_camp_invitations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qcm_questions`
--
ALTER TABLE `qcm_questions`
  MODIFY `idQuestion` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qcm_reponses`
--
ALTER TABLE `qcm_reponses`
  MODIFY `idReponse` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `questions`
--
ALTER TABLE `questions`
  MODIFY `idQuestion` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quizz_colds`
--
ALTER TABLE `quizz_colds`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `quizz_questions`
--
ALTER TABLE `quizz_questions`
  MODIFY `idQuestion` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reasons`
--
ALTER TABLE `reasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reglement`
--
ALTER TABLE `reglement`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reponses`
--
ALTER TABLE `reponses`
  MODIFY `idReponse` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reponses_utilisateurs`
--
ALTER TABLE `reponses_utilisateurs`
  MODIFY `idReponseUtilisateur` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reservation_participant`
--
ALTER TABLE `reservation_participant`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reservation_responsable`
--
ALTER TABLE `reservation_responsable`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `resp_materiels`
--
ALTER TABLE `resp_materiels`
  MODIFY `idRespMateriel` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `restaurations`
--
ALTER TABLE `restaurations`
  MODIFY `idRestauration` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reward_catalogue_contents`
--
ALTER TABLE `reward_catalogue_contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `role_users`
--
ALTER TABLE `role_users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `salles`
--
ALTER TABLE `salles`
  MODIFY `idSalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `seances`
--
ALTER TABLE `seances`
  MODIFY `idSeance` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `secteurs`
--
ALTER TABLE `secteurs`
  MODIFY `idSecteur` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sections`
--
ALTER TABLE `sections`
  MODIFY `idSection` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `section_documents`
--
ALTER TABLE `section_documents`
  MODIFY `idSectionDocument` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sessions_test`
--
ALTER TABLE `sessions_test`
  MODIFY `idSession` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sexes`
--
ALTER TABLE `sexes`
  MODIFY `idSexe` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `skill_matrix`
--
ALTER TABLE `skill_matrix`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `specialtites`
--
ALTER TABLE `specialtites`
  MODIFY `idSp` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `submenu_refresh`
--
ALTER TABLE `submenu_refresh`
  MODIFY `idSubmenuRefresh` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `subscription_usage`
--
ALTER TABLE `subscription_usage`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `suivi_envois`
--
ALTER TABLE `suivi_envois`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `testing_timer`
--
ALTER TABLE `testing_timer`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `traits`
--
ALTER TABLE `traits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `transaction_history`
--
ALTER TABLE `transaction_history`
  MODIFY `idTransaction` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Type`
--
ALTER TABLE `Type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_abns`
--
ALTER TABLE `type_abns`
  MODIFY `idTypeAbn` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_customers`
--
ALTER TABLE `type_customers`
  MODIFY `idTypeCustomer` int(2) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_documents`
--
ALTER TABLE `type_documents`
  MODIFY `idTypeDocument` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_factures`
--
ALTER TABLE `type_factures`
  MODIFY `idTypeFacture` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_formateurs`
--
ALTER TABLE `type_formateurs`
  MODIFY `idTypeFormateur` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_images`
--
ALTER TABLE `type_images`
  MODIFY `idTypeImage` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_invoices`
--
ALTER TABLE `type_invoices`
  MODIFY `idTypeInvoice` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_materiels`
--
ALTER TABLE `type_materiels`
  MODIFY `idTypeMateriel` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_modules`
--
ALTER TABLE `type_modules`
  MODIFY `idTypeModule` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_projets`
--
ALTER TABLE `type_projets`
  MODIFY `idTypeProjet` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_publicites`
--
ALTER TABLE `type_publicites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_questions`
--
ALTER TABLE `type_questions`
  MODIFY `idTypeQuestion` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `type_services`
--
ALTER TABLE `type_services`
  MODIFY `idTypeService` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `unites`
--
ALTER TABLE `unites`
  MODIFY `idUnite` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `val_comments`
--
ALTER TABLE `val_comments`
  MODIFY `idValComment` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `verifications_badge`
--
ALTER TABLE `verifications_badge`
  MODIFY `idVerification` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `video_refresh`
--
ALTER TABLE `video_refresh`
  MODIFY `idVideoRefresh` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `villes`
--
ALTER TABLE `villes`
  MODIFY `idVille` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ville_codeds`
--
ALTER TABLE `ville_codeds`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `abn_cfps`
--
ALTER TABLE `abn_cfps`
  ADD CONSTRAINT `abn_cfps_ibfk_1` FOREIGN KEY (`idAbn`) REFERENCES `abonnements` (`idAbn`);

--
-- Contraintes pour la table `abn_etps`
--
ALTER TABLE `abn_etps`
  ADD CONSTRAINT `abn_etps_ibfk_1` FOREIGN KEY (`idAbn`) REFERENCES `abonnements` (`idAbn`);

--
-- Contraintes pour la table `acces`
--
ALTER TABLE `acces`
  ADD CONSTRAINT `acces_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `accompagnement`
--
ALTER TABLE `accompagnement`
  ADD CONSTRAINT `accompagnement_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `accueil`
--
ALTER TABLE `accueil`
  ADD CONSTRAINT `accueil_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `agences`
--
ALTER TABLE `agences`
  ADD CONSTRAINT `agences_ibfk_1` FOREIGN KEY (`idVilleCoded`) REFERENCES `ville_codeds` (`id`),
  ADD CONSTRAINT `agences_ibfk_2` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `apprenants`
--
ALTER TABLE `apprenants`
  ADD CONSTRAINT `apprenants_ibfk_1` FOREIGN KEY (`idEmploye`) REFERENCES `employes` (`idEmploye`);

--
-- Contraintes pour la table `app_country`
--
ALTER TABLE `app_country`
  ADD CONSTRAINT `app_country_ibfk_1` FOREIGN KEY (`idCountry`) REFERENCES `countries` (`id`);

--
-- Contraintes pour la table `attestations`
--
ALTER TABLE `attestations`
  ADD CONSTRAINT `attestations_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `attestations_ibfk_2` FOREIGN KEY (`idEmploye`) REFERENCES `employes` (`idEmploye`),
  ADD CONSTRAINT `attestations_ibfk_3` FOREIGN KEY (`idCfp`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `attributions_badge`
--
ALTER TABLE `attributions_badge`
  ADD CONSTRAINT `attributions_badge_ibfk_1` FOREIGN KEY (`idBadge`) REFERENCES `badges` (`idBadge`),
  ADD CONSTRAINT `attributions_badge_ibfk_2` FOREIGN KEY (`idEmploye`) REFERENCES `apprenants` (`idEmploye`),
  ADD CONSTRAINT `attributions_badge_ibfk_3` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`);

--
-- Contraintes pour la table `badges`
--
ALTER TABLE `badges`
  ADD CONSTRAINT `badges_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`),
  ADD CONSTRAINT `badges_ibfk_2` FOREIGN KEY (`idCfp`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `bankacounts`
--
ALTER TABLE `bankacounts`
  ADD CONSTRAINT `bankacounts_ibfk_1` FOREIGN KEY (`ba_idCustomer`) REFERENCES `customers` (`idCustomer`),
  ADD CONSTRAINT `bankacounts_ibfk_2` FOREIGN KEY (`ba_idPostal`) REFERENCES `ville_codeds` (`id`);

--
-- Contraintes pour la table `batches`
--
ALTER TABLE `batches`
  ADD CONSTRAINT `batches_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `batch_learners`
--
ALTER TABLE `batch_learners`
  ADD CONSTRAINT `batch_learners_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`),
  ADD CONSTRAINT `batch_learners_ibfk_2` FOREIGN KEY (`employe_id`) REFERENCES `employes` (`idEmploye`);

--
-- Contraintes pour la table `cfps`
--
ALTER TABLE `cfps`
  ADD CONSTRAINT `cfps_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `cfp_etps`
--
ALTER TABLE `cfp_etps`
  ADD CONSTRAINT `cfp_etps_ibfk_1` FOREIGN KEY (`idEtp`) REFERENCES `entreprises` (`idCustomer`),
  ADD CONSTRAINT `cfp_etps_ibfk_2` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`);

--
-- Contraintes pour la table `cfp_formateurs`
--
ALTER TABLE `cfp_formateurs`
  ADD CONSTRAINT `cfp_formateurs_ibfk_1` FOREIGN KEY (`idFormateur`) REFERENCES `formateurs` (`idFormateur`),
  ADD CONSTRAINT `cfp_formateurs_ibfk_2` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`);

--
-- Contraintes pour la table `cfp_particuliers`
--
ALTER TABLE `cfp_particuliers`
  ADD CONSTRAINT `cfp_particuliers_ibfk_1` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`),
  ADD CONSTRAINT `cfp_particuliers_ibfk_2` FOREIGN KEY (`idParticulier`) REFERENCES `particuliers` (`idParticulier`);

--
-- Contraintes pour la table `cfp_selected_by_admin`
--
ALTER TABLE `cfp_selected_by_admin`
  ADD CONSTRAINT `cfp_selected_by_admin_ibfk_1` FOREIGN KEY (`idSuperAdmin`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cfp_selected_by_admin_ibfk_2` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`);

--
-- Contraintes pour la table `cible_modules`
--
ALTER TABLE `cible_modules`
  ADD CONSTRAINT `cible_modules_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `commissions_received`
--
ALTER TABLE `commissions_received`
  ADD CONSTRAINT `commissions_received_credits_payments_FK` FOREIGN KEY (`credit_payment_id`) REFERENCES `credits_payments` (`idCreditPayment`),
  ADD CONSTRAINT `commissions_receiver_FK` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `competences_badge`
--
ALTER TABLE `competences_badge`
  ADD CONSTRAINT `competences_badge_ibfk_1` FOREIGN KEY (`idBadge`) REFERENCES `badges` (`idBadge`) ON DELETE CASCADE;

--
-- Contraintes pour la table `conditions`
--
ALTER TABLE `conditions`
  ADD CONSTRAINT `conditions_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`idLieu`) REFERENCES `lieux` (`idLieu`);

--
-- Contraintes pour la table `content_files`
--
ALTER TABLE `content_files`
  ADD CONSTRAINT `content_files_ibfk_1` FOREIGN KEY (`idContent`) REFERENCES `content_refresh` (`idContent`) ON DELETE CASCADE;

--
-- Contraintes pour la table `content_refresh`
--
ALTER TABLE `content_refresh`
  ADD CONSTRAINT `content_refresh_ibfk_1` FOREIGN KEY (`idModuleContent`) REFERENCES `module_program_contents` (`id`);

--
-- Contraintes pour la table `countriess`
--
ALTER TABLE `countriess`
  ADD CONSTRAINT `countriess_ibfk_1` FOREIGN KEY (`id_nif_name`) REFERENCES `nif_names` (`id`),
  ADD CONSTRAINT `countriess_ibfk_2` FOREIGN KEY (`id_currency`) REFERENCES `currencies` (`id`);

--
-- Contraintes pour la table `country_fulls`
--
ALTER TABLE `country_fulls`
  ADD CONSTRAINT `country_fulls_ibfk_1` FOREIGN KEY (`id`) REFERENCES `countriess` (`id`),
  ADD CONSTRAINT `country_fulls_ibfk_2` FOREIGN KEY (`id_rcs_name`) REFERENCES `rcs_names` (`id`),
  ADD CONSTRAINT `country_fulls_ibfk_3` FOREIGN KEY (`id_stat_name`) REFERENCES `stat_names` (`id`);

--
-- Contraintes pour la table `country_less`
--
ALTER TABLE `country_less`
  ADD CONSTRAINT `country_less_ibfk_1` FOREIGN KEY (`id`) REFERENCES `countriess` (`id`);

--
-- Contraintes pour la table `credits_payments`
--
ALTER TABLE `credits_payments`
  ADD CONSTRAINT `credits_pack_FK` FOREIGN KEY (`pack_credits_id`) REFERENCES `credits_packs` (`idPackCredit`),
  ADD CONSTRAINT `users_buy_credits_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `credits_wallet`
--
ALTER TABLE `credits_wallet`
  ADD CONSTRAINT `credits_wallet_users_FK` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `criteres_badge`
--
ALTER TABLE `criteres_badge`
  ADD CONSTRAINT `criteres_badge_ibfk_1` FOREIGN KEY (`idBadge`) REFERENCES `badges` (`idBadge`) ON DELETE CASCADE;

--
-- Contraintes pour la table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`idSecteur`) REFERENCES `secteurs` (`idSecteur`),
  ADD CONSTRAINT `customers_ibfk_3` FOREIGN KEY (`idTypeCustomer`) REFERENCES `type_customers` (`idTypeCustomer`);

--
-- Contraintes pour la table `c_emps`
--
ALTER TABLE `c_emps`
  ADD CONSTRAINT `c_emps_ibfk_1` FOREIGN KEY (`idEmploye`) REFERENCES `employes` (`idEmploye`),
  ADD CONSTRAINT `c_emps_ibfk_2` FOREIGN KEY (`id_cfp`) REFERENCES `cfps` (`idCustomer`);

--
-- Contraintes pour la table `detail_abns`
--
ALTER TABLE `detail_abns`
  ADD CONSTRAINT `detail_abns_ibfk_1` FOREIGN KEY (`idAbn`) REFERENCES `abonnements` (`idAbn`),
  ADD CONSTRAINT `detail_abns_ibfk_2` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `detail_apprenants`
--
ALTER TABLE `detail_apprenants`
  ADD CONSTRAINT `detail_apprenants_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `detail_apprenants_ibfk_2` FOREIGN KEY (`idEmploye`) REFERENCES `employes` (`idEmploye`);

--
-- Contraintes pour la table `detail_apprenant_inters`
--
ALTER TABLE `detail_apprenant_inters`
  ADD CONSTRAINT `detail_apprenant_inters_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `detail_apprenant_inters_ibfk_2` FOREIGN KEY (`idEtp`) REFERENCES `entreprises` (`idCustomer`),
  ADD CONSTRAINT `detail_apprenant_inters_ibfk_3` FOREIGN KEY (`idEmploye`) REFERENCES `apprenants` (`idEmploye`);

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`idDossier`) REFERENCES `dossiers` (`idDossier`),
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`idTypeDocument`) REFERENCES `type_documents` (`idTypeDocument`);

--
-- Contraintes pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD CONSTRAINT `dossiers_ibfk_1` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`);

--
-- Contraintes pour la table `emargements`
--
ALTER TABLE `emargements`
  ADD CONSTRAINT `emargements_ibfk_1` FOREIGN KEY (`idSeance`) REFERENCES `seances` (`idSeance`),
  ADD CONSTRAINT `emargements_ibfk_2` FOREIGN KEY (`idEmploye`) REFERENCES `apprenants` (`idEmploye`);

--
-- Contraintes pour la table `employes`
--
ALTER TABLE `employes`
  ADD CONSTRAINT `employes_ibfk_1` FOREIGN KEY (`idEmploye`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `employes_ibfk_2` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`),
  ADD CONSTRAINT `employes_ibfk_3` FOREIGN KEY (`idSexe`) REFERENCES `sexes` (`idSexe`),
  ADD CONSTRAINT `employes_ibfk_4` FOREIGN KEY (`idNiveau`) REFERENCES `niveau_etudes` (`idNiveau`);

--
-- Contraintes pour la table `emp_debit_credit`
--
ALTER TABLE `emp_debit_credit`
  ADD CONSTRAINT `transaction_history_emp_etp_transaction_FK` FOREIGN KEY (`idTransaction`) REFERENCES `transaction_history` (`idTransaction`),
  ADD CONSTRAINT `transaction_history_emp_etp_users_FK` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `entreprises`
--
ALTER TABLE `entreprises`
  ADD CONSTRAINT `entreprises_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `equipments`
--
ALTER TABLE `equipments`
  ADD CONSTRAINT `equipments_ibfk_1` FOREIGN KEY (`idSalle`) REFERENCES `salles` (`idSalle`);

--
-- Contraintes pour la table `etp_groupeds`
--
ALTER TABLE `etp_groupeds`
  ADD CONSTRAINT `etp_groupeds_ibfk_1` FOREIGN KEY (`idEntreprise`) REFERENCES `entreprises` (`idCustomer`),
  ADD CONSTRAINT `etp_groupeds_ibfk_2` FOREIGN KEY (`idEntrepriseParent`) REFERENCES `etp_groupes` (`idEntreprise`);

--
-- Contraintes pour la table `etp_groupes`
--
ALTER TABLE `etp_groupes`
  ADD CONSTRAINT `etp_groupes_ibfk_1` FOREIGN KEY (`idEntreprise`) REFERENCES `entreprises` (`idCustomer`);

--
-- Contraintes pour la table `etp_informals`
--
ALTER TABLE `etp_informals`
  ADD CONSTRAINT `etp_informals_ibfk_1` FOREIGN KEY (`idEntreprise`) REFERENCES `entreprises` (`idCustomer`);

--
-- Contraintes pour la table `etp_singles`
--
ALTER TABLE `etp_singles`
  ADD CONSTRAINT `etp_singles_ibfk_1` FOREIGN KEY (`idEntreprise`) REFERENCES `entreprises` (`idCustomer`);

--
-- Contraintes pour la table `eval_chauds`
--
ALTER TABLE `eval_chauds`
  ADD CONSTRAINT `eval_chauds_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `eval_chauds_ibfk_2` FOREIGN KEY (`idEmploye`) REFERENCES `apprenants` (`idEmploye`),
  ADD CONSTRAINT `eval_chauds_ibfk_3` FOREIGN KEY (`idQuestion`) REFERENCES `questions` (`idQuestion`),
  ADD CONSTRAINT `eval_chauds_ibfk_4` FOREIGN KEY (`idExaminer`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `eval_froids`
--
ALTER TABLE `eval_froids`
  ADD CONSTRAINT `eval_froids_ibfk_1` FOREIGN KEY (`idQuizzCold`) REFERENCES `quizz_colds` (`id`),
  ADD CONSTRAINT `eval_froids_ibfk_2` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `eval_froids_ibfk_3` FOREIGN KEY (`idEmploye`) REFERENCES `apprenants` (`idEmploye`);

--
-- Contraintes pour la table `eval_froid_sents`
--
ALTER TABLE `eval_froid_sents`
  ADD CONSTRAINT `eval_froid_sents_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `eval_froid_sents_ibfk_2` FOREIGN KEY (`idEtp`) REFERENCES `entreprises` (`idCustomer`);

--
-- Contraintes pour la table `feries`
--
ALTER TABLE `feries`
  ADD CONSTRAINT `feries_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `financial_goals`
--
ALTER TABLE `financial_goals`
  ADD CONSTRAINT `financial_goals_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customers` (`idCustomer`),
  ADD CONSTRAINT `financial_goals_ibfk_2` FOREIGN KEY (`id_module`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `fonctions`
--
ALTER TABLE `fonctions`
  ADD CONSTRAINT `fonctions_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `formateurs`
--
ALTER TABLE `formateurs`
  ADD CONSTRAINT `formateurs_ibfk_1` FOREIGN KEY (`idFormateur`) REFERENCES `forms` (`idFormateur`),
  ADD CONSTRAINT `formateurs_ibfk_2` FOREIGN KEY (`idSp`) REFERENCES `specialtites` (`idSp`);

--
-- Contraintes pour la table `formateur_internes`
--
ALTER TABLE `formateur_internes`
  ADD CONSTRAINT `formateur_internes_ibfk_1` FOREIGN KEY (`idFormateur`) REFERENCES `forms` (`idFormateur`),
  ADD CONSTRAINT `formateur_internes_ibfk_2` FOREIGN KEY (`idEmploye`) REFERENCES `employes` (`idEmploye`),
  ADD CONSTRAINT `formateur_internes_ibfk_3` FOREIGN KEY (`idEntreprise`) REFERENCES `entreprises` (`idCustomer`);

--
-- Contraintes pour la table `forms`
--
ALTER TABLE `forms`
  ADD CONSTRAINT `forms_ibfk_1` FOREIGN KEY (`idFormateur`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `forms_ibfk_2` FOREIGN KEY (`idTypeFormateur`) REFERENCES `type_formateurs` (`idTypeFormateur`),
  ADD CONSTRAINT `forms_ibfk_3` FOREIGN KEY (`idSexe`) REFERENCES `sexes` (`idSexe`);

--
-- Contraintes pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  ADD CONSTRAINT `fournisseurs_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`),
  ADD CONSTRAINT `fournisseurs_ibfk_2` FOREIGN KEY (`idTypefournisseur`) REFERENCES `type_fournisseurs` (`idTypefournisseur`),
  ADD CONSTRAINT `fournisseurs_ibfk_3` FOREIGN KEY (`idTypeService`) REFERENCES `type_services` (`idTypeService`);

--
-- Contraintes pour la table `fournisseur_cfps`
--
ALTER TABLE `fournisseur_cfps`
  ADD CONSTRAINT `fournisseur_cfps_ibfk_1` FOREIGN KEY (`idFournisseur`) REFERENCES `fournisseurs` (`idFournisseur`);

--
-- Contraintes pour la table `fournisseur_etps`
--
ALTER TABLE `fournisseur_etps`
  ADD CONSTRAINT `fournisseur_etps_ibfk_1` FOREIGN KEY (`idFournisseur`) REFERENCES `fournisseurs` (`idFournisseur`);

--
-- Contraintes pour la table `fraisprojet`
--
ALTER TABLE `fraisprojet`
  ADD CONSTRAINT `fraisprojet_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `fraisprojet_ibfk_2` FOREIGN KEY (`idFrais`) REFERENCES `frais` (`idFrais`);

--
-- Contraintes pour la table `f_emps`
--
ALTER TABLE `f_emps`
  ADD CONSTRAINT `f_emps_ibfk_1` FOREIGN KEY (`idEmploye`) REFERENCES `employes` (`idEmploye`),
  ADD CONSTRAINT `f_emps_ibfk_2` FOREIGN KEY (`id_formateur`) REFERENCES `formateurs` (`idFormateur`);

--
-- Contraintes pour la table `google_users`
--
ALTER TABLE `google_users`
  ADD CONSTRAINT `google_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `iframes`
--
ALTER TABLE `iframes`
  ADD CONSTRAINT `iframes_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `ignoredConflitFormateur`
--
ALTER TABLE `ignoredConflitFormateur`
  ADD CONSTRAINT `ignoredConflitFormateur_ibfk_1` FOREIGN KEY (`idFormateur`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ignoredConflitFormateur_ibfk_2` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`);

--
-- Contraintes pour la table `ignoredConflitLieu`
--
ALTER TABLE `ignoredConflitLieu`
  ADD CONSTRAINT `ignoredConflitLieu_ibfk_1` FOREIGN KEY (`idSalle`) REFERENCES `salles` (`idSalle`),
  ADD CONSTRAINT `ignoredConflitLieu_ibfk_2` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`);

--
-- Contraintes pour la table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `images_ibfk_2` FOREIGN KEY (`idTypeImage`) REFERENCES `type_images` (`idTypeImage`);

--
-- Contraintes pour la table `images_qcm`
--
ALTER TABLE `images_qcm`
  ADD CONSTRAINT `images_qcm_ibfk_1` FOREIGN KEY (`idTypeImage`) REFERENCES `type_images` (`idTypeImage`);

--
-- Contraintes pour la table `internes`
--
ALTER TABLE `internes`
  ADD CONSTRAINT `internes_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `internes_ibfk_2` FOREIGN KEY (`idEtp`) REFERENCES `entreprises` (`idCustomer`);

--
-- Contraintes pour la table `inters`
--
ALTER TABLE `inters`
  ADD CONSTRAINT `inters_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `inters_ibfk_2` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`);

--
-- Contraintes pour la table `inter_entreprises`
--
ALTER TABLE `inter_entreprises`
  ADD CONSTRAINT `inter_entreprises_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `inters` (`idProjet`),
  ADD CONSTRAINT `inter_entreprises_ibfk_2` FOREIGN KEY (`idEtp`) REFERENCES `entreprises` (`idCustomer`);

--
-- Contraintes pour la table `intras`
--
ALTER TABLE `intras`
  ADD CONSTRAINT `intras_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `intras_ibfk_2` FOREIGN KEY (`idPaiement`) REFERENCES `paiements` (`idPaiement`),
  ADD CONSTRAINT `intras_ibfk_3` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`);

--
-- Contraintes pour la table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_idBankAcount` FOREIGN KEY (`idBankAcount`) REFERENCES `bankacounts` (`id`),
  ADD CONSTRAINT `fk_idCompany` FOREIGN KEY (`idCompany`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `fk_idCustomer` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`),
  ADD CONSTRAINT `fk_invoices_type_client` FOREIGN KEY (`idTypeClient`) REFERENCES `invoice_type_client` (`idType`),
  ADD CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`idPaiement`) REFERENCES `mode_paiements` (`idPaiement`),
  ADD CONSTRAINT `invoices_ibfk_4` FOREIGN KEY (`idTypeFacture`) REFERENCES `type_factures` (`idTypeFacture`);

--
-- Contraintes pour la table `invoice_acomptes`
--
ALTER TABLE `invoice_acomptes`
  ADD CONSTRAINT `invoice_acomptes_ibfk_1` FOREIGN KEY (`idInvoice`) REFERENCES `invoices` (`idInvoice`);

--
-- Contraintes pour la table `invoice_deleted`
--
ALTER TABLE `invoice_deleted`
  ADD CONSTRAINT `invoice_deleted_ibfk_1` FOREIGN KEY (`idInvoice`) REFERENCES `invoices` (`idInvoice`);

--
-- Contraintes pour la table `invoice_details`
--
ALTER TABLE `invoice_details`
  ADD CONSTRAINT `fk_idProjet` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `invoice_details_ibfk_1` FOREIGN KEY (`idInvoice`) REFERENCES `invoices` (`idInvoice`),
  ADD CONSTRAINT `invoice_details_ibfk_2` FOREIGN KEY (`idUnite`) REFERENCES `unites` (`idUnite`);

--
-- Contraintes pour la table `invoice_details_profo`
--
ALTER TABLE `invoice_details_profo`
  ADD CONSTRAINT `invoice_details_profo_ibfk_1` FOREIGN KEY (`idInvoice`) REFERENCES `invoices` (`idInvoice`),
  ADD CONSTRAINT `invoice_details_profo_ibfk_2` FOREIGN KEY (`idUnite`) REFERENCES `unites` (`idUnite`),
  ADD CONSTRAINT `invoice_details_profo_ibfk_3` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD CONSTRAINT `fk_idBankCompte` FOREIGN KEY (`payment_bank_id`) REFERENCES `bankacounts` (`id`),
  ADD CONSTRAINT `fk_idMobileMoney` FOREIGN KEY (`payment_mobilemoney_id`) REFERENCES `mobilemoneyacounts` (`id`),
  ADD CONSTRAINT `fk_payment_method` FOREIGN KEY (`payment_method_id`) REFERENCES `pm_types` (`idTypePm`),
  ADD CONSTRAINT `invoice_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`idInvoice`);

--
-- Contraintes pour la table `invoice_standards`
--
ALTER TABLE `invoice_standards`
  ADD CONSTRAINT `invoice_standards_ibfk_1` FOREIGN KEY (`idInvoice`) REFERENCES `invoices` (`idInvoice`);

--
-- Contraintes pour la table `launchers`
--
ALTER TABLE `launchers`
  ADD CONSTRAINT `launchers_ibfk_1` FOREIGN KEY (`idCountry`) REFERENCES `countries` (`id`);

--
-- Contraintes pour la table `lieux`
--
ALTER TABLE `lieux`
  ADD CONSTRAINT `lieux_ibfk_1` FOREIGN KEY (`idVille`) REFERENCES `villes` (`idVille`),
  ADD CONSTRAINT `lieux_ibfk_2` FOREIGN KEY (`idLieuType`) REFERENCES `lieu_types` (`idLieuType`),
  ADD CONSTRAINT `lieux_ibfk_3` FOREIGN KEY (`idVilleCoded`) REFERENCES `ville_codeds` (`id`);

--
-- Contraintes pour la table `lieu_privates`
--
ALTER TABLE `lieu_privates`
  ADD CONSTRAINT `lieu_privates_ibfk_1` FOREIGN KEY (`idLieu`) REFERENCES `lieux` (`idLieu`),
  ADD CONSTRAINT `lieu_privates_ibfk_2` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `lieu_publics`
--
ALTER TABLE `lieu_publics`
  ADD CONSTRAINT `lieu_publics_ibfk_1` FOREIGN KEY (`idLieu`) REFERENCES `lieux` (`idLieu`);

--
-- Contraintes pour la table `marketplace_images`
--
ALTER TABLE `marketplace_images`
  ADD CONSTRAINT `mrkimg_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `materiel_cfps`
--
ALTER TABLE `materiel_cfps`
  ADD CONSTRAINT `materiel_cfps_ibfk_1` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`),
  ADD CONSTRAINT `materiel_cfps_ibfk_2` FOREIGN KEY (`idTypeMateriel`) REFERENCES `type_materiels` (`idTypeMateriel`);

--
-- Contraintes pour la table `materiel_etps`
--
ALTER TABLE `materiel_etps`
  ADD CONSTRAINT `materiel_etps_ibfk_1` FOREIGN KEY (`idEtp`) REFERENCES `entreprises` (`idCustomer`),
  ADD CONSTRAINT `materiel_etps_ibfk_2` FOREIGN KEY (`idTypeMateriel`) REFERENCES `type_materiels` (`idTypeMateriel`);

--
-- Contraintes pour la table `materiel_externes`
--
ALTER TABLE `materiel_externes`
  ADD CONSTRAINT `materiel_externes_ibfk_1` FOREIGN KEY (`idMateriel`) REFERENCES `materiel_cfps` (`idMateriel`),
  ADD CONSTRAINT `materiel_externes_ibfk_2` FOREIGN KEY (`idFournisseur`) REFERENCES `fournisseurs` (`idFournisseur`);

--
-- Contraintes pour la table `materiel_externe_etps`
--
ALTER TABLE `materiel_externe_etps`
  ADD CONSTRAINT `materiel_externe_etps_ibfk_1` FOREIGN KEY (`idMateriel`) REFERENCES `materiel_etps` (`idMateriel`),
  ADD CONSTRAINT `materiel_externe_etps_ibfk_2` FOREIGN KEY (`idFournisseur`) REFERENCES `fournisseurs` (`idFournisseur`);

--
-- Contraintes pour la table `materiel_internes`
--
ALTER TABLE `materiel_internes`
  ADD CONSTRAINT `materiel_internes_ibfk_1` FOREIGN KEY (`idMateriel`) REFERENCES `materiel_cfps` (`idMateriel`);

--
-- Contraintes pour la table `materiel_interne_etps`
--
ALTER TABLE `materiel_interne_etps`
  ADD CONSTRAINT `materiel_interne_etps_ibfk_1` FOREIGN KEY (`idMateriel`) REFERENCES `materiel_etps` (`idMateriel`);

--
-- Contraintes pour la table `mdls`
--
ALTER TABLE `mdls`
  ADD CONSTRAINT `mdls_ibfk_1` FOREIGN KEY (`idDomaine`) REFERENCES `domaine_formations` (`idDomaine`),
  ADD CONSTRAINT `mdls_ibfk_2` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`),
  ADD CONSTRAINT `mdls_ibfk_3` FOREIGN KEY (`idTypeModule`) REFERENCES `type_modules` (`idTypeModule`);

--
-- Contraintes pour la table `menu_refresh`
--
ALTER TABLE `menu_refresh`
  ADD CONSTRAINT `menu_refresh_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `modules` (`idModule`);

--
-- Contraintes pour la table `mobilemoneyacounts`
--
ALTER TABLE `mobilemoneyacounts`
  ADD CONSTRAINT `mobilemoneyacounts_ibfk_1` FOREIGN KEY (`mm_idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `module_internes`
--
ALTER TABLE `module_internes`
  ADD CONSTRAINT `module_internes_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `module_program_contents`
--
ALTER TABLE `module_program_contents`
  ADD CONSTRAINT `module_program_contents_ibfk_1` FOREIGN KEY (`idProgramme`) REFERENCES `programmes` (`idProgramme`);

--
-- Contraintes pour la table `module_ressources`
--
ALTER TABLE `module_ressources`
  ADD CONSTRAINT `module_ressources_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `module_rewards`
--
ALTER TABLE `module_rewards`
  ADD CONSTRAINT `module_rewards_ibfk_1` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`),
  ADD CONSTRAINT `module_rewards_ibfk_2` FOREIGN KEY (`idEtp`) REFERENCES `entreprises` (`idCustomer`),
  ADD CONSTRAINT `module_rewards_ibfk_3` FOREIGN KEY (`id_reward_scope`) REFERENCES `reward_scopes` (`id`),
  ADD CONSTRAINT `module_rewards_ibfk_4` FOREIGN KEY (`id_reward_type`) REFERENCES `reward_types` (`id`);

--
-- Contraintes pour la table `module_reward_fulls`
--
ALTER TABLE `module_reward_fulls`
  ADD CONSTRAINT `module_reward_fulls_ibfk_1` FOREIGN KEY (`id_module_reward`) REFERENCES `module_rewards` (`id`);

--
-- Contraintes pour la table `module_reward_less`
--
ALTER TABLE `module_reward_less`
  ADD CONSTRAINT `module_reward_less_ibfk_1` FOREIGN KEY (`id_module_reward`) REFERENCES `module_rewards` (`id`);

--
-- Contraintes pour la table `module_skills`
--
ALTER TABLE `module_skills`
  ADD CONSTRAINT `module_skills_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `objectif_modules`
--
ALTER TABLE `objectif_modules`
  ADD CONSTRAINT `objectif_modules_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `particulier_projet`
--
ALTER TABLE `particulier_projet`
  ADD CONSTRAINT `particulier_projet_ibfk_1` FOREIGN KEY (`idParticulier`) REFERENCES `particuliers` (`idParticulier`),
  ADD CONSTRAINT `particulier_projet_ibfk_2` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`);

--
-- Contraintes pour la table `place_etp_from_cfps`
--
ALTER TABLE `place_etp_from_cfps`
  ADD CONSTRAINT `place_etp_from_cfps_ibfk_1` FOREIGN KEY (`idLieu`) REFERENCES `lieux` (`idLieu`),
  ADD CONSTRAINT `place_etp_from_cfps_ibfk_2` FOREIGN KEY (`idEntreprise`) REFERENCES `entreprises` (`idCustomer`),
  ADD CONSTRAINT `place_etp_from_cfps_ibfk_3` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`);

--
-- Contraintes pour la table `pm_cheques`
--
ALTER TABLE `pm_cheques`
  ADD CONSTRAINT `pm_cheques_ibfk_1` FOREIGN KEY (`idPaiement`) REFERENCES `mode_paiements` (`idPaiement`);

--
-- Contraintes pour la table `pm_virements`
--
ALTER TABLE `pm_virements`
  ADD CONSTRAINT `pm_virements_ibfk_1` FOREIGN KEY (`idPaiement`) REFERENCES `mode_paiements` (`idPaiement`);

--
-- Contraintes pour la table `prerequis_modules`
--
ALTER TABLE `prerequis_modules`
  ADD CONSTRAINT `prerequis_modules_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `prestation_modules`
--
ALTER TABLE `prestation_modules`
  ADD CONSTRAINT `prestation_modules_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `programmes`
--
ALTER TABLE `programmes`
  ADD CONSTRAINT `programmes_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `project_forms`
--
ALTER TABLE `project_forms`
  ADD CONSTRAINT `project_forms_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `project_forms_ibfk_2` FOREIGN KEY (`idFormateur`) REFERENCES `forms` (`idFormateur`);

--
-- Contraintes pour la table `project_invoices`
--
ALTER TABLE `project_invoices`
  ADD CONSTRAINT `project_invoices_ibfk_1` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`),
  ADD CONSTRAINT `project_invoices_ibfk_2` FOREIGN KEY (`idEtp`) REFERENCES `entreprises` (`idCustomer`),
  ADD CONSTRAINT `project_invoices_ibfk_3` FOREIGN KEY (`idPaiement`) REFERENCES `mode_paiements` (`idPaiement`),
  ADD CONSTRAINT `project_invoices_ibfk_4` FOREIGN KEY (`idTypeInvoice`) REFERENCES `type_invoices` (`idTypeInvoice`),
  ADD CONSTRAINT `project_invoices_ibfk_5` FOREIGN KEY (`idDevise`) REFERENCES `devises` (`idDevise`);

--
-- Contraintes pour la table `project_materials`
--
ALTER TABLE `project_materials`
  ADD CONSTRAINT `project_materials_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `project_materials_ibfk_2` FOREIGN KEY (`idMateriel`) REFERENCES `materiel_cfps` (`idMateriel`),
  ADD CONSTRAINT `project_materials_ibfk_3` FOREIGN KEY (`idRespMateriel`) REFERENCES `resp_materiels` (`idRespMateriel`);

--
-- Contraintes pour la table `project_restaurations`
--
ALTER TABLE `project_restaurations`
  ADD CONSTRAINT `project_restaurations_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `project_restaurations_ibfk_2` FOREIGN KEY (`idRestauration`) REFERENCES `restaurations` (`idRestauration`),
  ADD CONSTRAINT `project_restaurations_ibfk_3` FOREIGN KEY (`paidBy`) REFERENCES `type_customers` (`idTypeCustomer`);

--
-- Contraintes pour la table `project_sub_contracts`
--
ALTER TABLE `project_sub_contracts`
  ADD CONSTRAINT `project_sub_contracts_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `project_sub_contracts_ibfk_2` FOREIGN KEY (`idSubContractor`) REFERENCES `cfps` (`idCustomer`);

--
-- Contraintes pour la table `projets`
--
ALTER TABLE `projets`
  ADD CONSTRAINT `projets_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`),
  ADD CONSTRAINT `projets_ibfk_3` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`),
  ADD CONSTRAINT `projets_ibfk_4` FOREIGN KEY (`idModalite`) REFERENCES `modalites` (`idModalite`),
  ADD CONSTRAINT `projets_ibfk_5` FOREIGN KEY (`idTypeProjet`) REFERENCES `type_projets` (`idTypeProjet`),
  ADD CONSTRAINT `projets_ibfk_7` FOREIGN KEY (`idDossier`) REFERENCES `dossiers` (`idDossier`);

--
-- Contraintes pour la table `publicites`
--
ALTER TABLE `publicites`
  ADD CONSTRAINT `publicites_ibfk_1` FOREIGN KEY (`idType`) REFERENCES `type_publicites` (`id`),
  ADD CONSTRAINT `publicites_ibfk_2` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `pub_simples`
--
ALTER TABLE `pub_simples`
  ADD CONSTRAINT `pub_simples_ibfk_1` FOREIGN KEY (`id`) REFERENCES `publicites` (`id`);

--
-- Contraintes pour la table `qcm`
--
ALTER TABLE `qcm`
  ADD CONSTRAINT `fk_qcm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `qcm_ibfk_1` FOREIGN KEY (`idDomaine`) REFERENCES `domaine_formations` (`idDomaine`) ON DELETE SET NULL;

--
-- Contraintes pour la table `qcm_bareme`
--
ALTER TABLE `qcm_bareme`
  ADD CONSTRAINT `fk_bareme_niveau` FOREIGN KEY (`id_niveau`) REFERENCES `niveau_qcm` (`id`),
  ADD CONSTRAINT `fk_qcm_bareme_qcm` FOREIGN KEY (`idQCM`) REFERENCES `qcm` (`idQCM`) ON DELETE CASCADE;

--
-- Contraintes pour la table `qcm_category_evaluations`
--
ALTER TABLE `qcm_category_evaluations`
  ADD CONSTRAINT `fk_qcm_category_evaluations_category` FOREIGN KEY (`idCategorie`) REFERENCES `categories_reponses` (`idCategorie`),
  ADD CONSTRAINT `fk_qcm_category_evaluations_qcm` FOREIGN KEY (`idQCM`) REFERENCES `qcm` (`idQCM`),
  ADD CONSTRAINT `fk_qcm_category_evaluations_qcm_niveau` FOREIGN KEY (`id_niveau`) REFERENCES `niveau_qcm` (`id`);

--
-- Contraintes pour la table `qcm_invitations`
--
ALTER TABLE `qcm_invitations`
  ADD CONSTRAINT `fk_employe` FOREIGN KEY (`idEmploye`) REFERENCES `employes` (`idEmploye`),
  ADD CONSTRAINT `fk_employeur` FOREIGN KEY (`idEmployeur`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_qcm` FOREIGN KEY (`idQCM`) REFERENCES `qcm` (`idQCM`);

--
-- Contraintes pour la table `qcm_invit_camps`
--
ALTER TABLE `qcm_invit_camps`
  ADD CONSTRAINT `campaign_creator_FK` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `qcm_invit_camp_invitations`
--
ALTER TABLE `qcm_invit_camp_invitations`
  ADD CONSTRAINT `qcm_invit_camp_invitations_ibfk_1` FOREIGN KEY (`invit_camp_id`) REFERENCES `qcm_invit_camps` (`idInvitCamp`),
  ADD CONSTRAINT `qcm_invit_camp_invitations_ibfk_2` FOREIGN KEY (`invitation_id`) REFERENCES `qcm_invitations` (`idInvitation`);

--
-- Contraintes pour la table `qcm_questions`
--
ALTER TABLE `qcm_questions`
  ADD CONSTRAINT `fk_qcm_questions_type` FOREIGN KEY (`idTypeQcmQuestion`) REFERENCES `type_qcm` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qcm_questions_ibfk_1` FOREIGN KEY (`idQCM`) REFERENCES `qcm` (`idQCM`) ON DELETE SET NULL,
  ADD CONSTRAINT `qcm_questions_ibfk_2` FOREIGN KEY (`idImageQ`) REFERENCES `images_qcm` (`idImageQ`) ON DELETE SET NULL;

--
-- Contraintes pour la table `qcm_reponses`
--
ALTER TABLE `qcm_reponses`
  ADD CONSTRAINT `fk_categorie` FOREIGN KEY (`categorie_id`) REFERENCES `categories_reponses` (`idCategorie`),
  ADD CONSTRAINT `qcm_reponses_ibfk_1` FOREIGN KEY (`idQuestion`) REFERENCES `qcm_questions` (`idQuestion`) ON DELETE SET NULL;

--
-- Contraintes pour la table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`idTypeQuestion`) REFERENCES `type_questions` (`idTypeQuestion`);

--
-- Contraintes pour la table `quizz_colds`
--
ALTER TABLE `quizz_colds`
  ADD CONSTRAINT `quizz_colds_ibfk_1` FOREIGN KEY (`idQuizzType`) REFERENCES `quizz_types` (`id`);

--
-- Contraintes pour la table `quizz_questions`
--
ALTER TABLE `quizz_questions`
  ADD CONSTRAINT `quizz_questions_ibfk_1` FOREIGN KEY (`idSection`) REFERENCES `sections` (`idSection`);

--
-- Contraintes pour la table `reasons`
--
ALTER TABLE `reasons`
  ADD CONSTRAINT `reasons_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `reglement`
--
ALTER TABLE `reglement`
  ADD CONSTRAINT `reglement_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `reponses`
--
ALTER TABLE `reponses`
  ADD CONSTRAINT `reponses_ibfk_1` FOREIGN KEY (`idQuestion`) REFERENCES `quizz_questions` (`idQuestion`);

--
-- Contraintes pour la table `reponses_utilisateurs`
--
ALTER TABLE `reponses_utilisateurs`
  ADD CONSTRAINT `reponses_utilisateurs_ibfk_1` FOREIGN KEY (`idSession`) REFERENCES `sessions_test` (`idSession`) ON DELETE SET NULL,
  ADD CONSTRAINT `reponses_utilisateurs_ibfk_2` FOREIGN KEY (`idQuestion`) REFERENCES `qcm_questions` (`idQuestion`) ON DELETE SET NULL,
  ADD CONSTRAINT `reponses_utilisateurs_ibfk_3` FOREIGN KEY (`idReponse`) REFERENCES `qcm_reponses` (`idReponse`) ON DELETE SET NULL;

--
-- Contraintes pour la table `reservation_participant`
--
ALTER TABLE `reservation_participant`
  ADD CONSTRAINT `reservation_participant_ibfk_1` FOREIGN KEY (`idReservation`) REFERENCES `inter_entreprises` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservation_responsable`
--
ALTER TABLE `reservation_responsable`
  ADD CONSTRAINT `reservation_responsable_ibfk_1` FOREIGN KEY (`idReservation`) REFERENCES `inter_entreprises` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reward_catalogue_contents`
--
ALTER TABLE `reward_catalogue_contents`
  ADD CONSTRAINT `reward_catalogue_contents_ibfk_1` FOREIGN KEY (`id_module_reward`) REFERENCES `module_reward_less` (`id_module_reward`),
  ADD CONSTRAINT `reward_catalogue_contents_ibfk_2` FOREIGN KEY (`idModule`) REFERENCES `mdls` (`idModule`);

--
-- Contraintes pour la table `role_users`
--
ALTER TABLE `role_users`
  ADD CONSTRAINT `role_users_roles_FK` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_users_users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `salles`
--
ALTER TABLE `salles`
  ADD CONSTRAINT `salles_ibfk_1` FOREIGN KEY (`idLieu`) REFERENCES `lieux` (`idLieu`);

--
-- Contraintes pour la table `seances`
--
ALTER TABLE `seances`
  ADD CONSTRAINT `seances_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`);

--
-- Contraintes pour la table `sessions_test`
--
ALTER TABLE `sessions_test`
  ADD CONSTRAINT `sessions_test_ibfk_1` FOREIGN KEY (`idQCM`) REFERENCES `qcm` (`idQCM`) ON DELETE SET NULL,
  ADD CONSTRAINT `sessions_test_ibfk_2` FOREIGN KEY (`idUtilisateur`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `skill_matrix`
--
ALTER TABLE `skill_matrix`
  ADD CONSTRAINT `skill_matrix_ibfk_1` FOREIGN KEY (`id_module_skill`) REFERENCES `module_skills` (`id`),
  ADD CONSTRAINT `skill_matrix_ibfk_2` FOREIGN KEY (`idEmploye`) REFERENCES `apprenants` (`idEmploye`),
  ADD CONSTRAINT `skill_matrix_ibfk_3` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`);

--
-- Contraintes pour la table `submenu_refresh`
--
ALTER TABLE `submenu_refresh`
  ADD CONSTRAINT `submenu_refresh_ibfk_1` FOREIGN KEY (`idMenuRefresh`) REFERENCES `menu_refresh` (`idMenuRefresh`);

--
-- Contraintes pour la table `sub_contractors`
--
ALTER TABLE `sub_contractors`
  ADD CONSTRAINT `sub_contractors_ibfk_1` FOREIGN KEY (`idSubContractor`) REFERENCES `customers` (`idCustomer`),
  ADD CONSTRAINT `sub_contractors_ibfk_2` FOREIGN KEY (`idCfp`) REFERENCES `cfps` (`idCustomer`);

--
-- Contraintes pour la table `suivi_envois`
--
ALTER TABLE `suivi_envois`
  ADD CONSTRAINT `suivi_envois_ibfk_1` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`),
  ADD CONSTRAINT `suivi_envois_ibfk_2` FOREIGN KEY (`idEmploye`) REFERENCES `employes` (`idEmploye`);

--
-- Contraintes pour la table `testing_timer`
--
ALTER TABLE `testing_timer`
  ADD CONSTRAINT `testing_timer_ibfk_1` FOREIGN KEY (`qcm_id`) REFERENCES `qcm` (`idQCM`),
  ADD CONSTRAINT `testing_timer_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `traits`
--
ALTER TABLE `traits`
  ADD CONSTRAINT `traits_ibfk_1` FOREIGN KEY (`idCustomer`) REFERENCES `customers` (`idCustomer`);

--
-- Contraintes pour la table `transaction_history`
--
ALTER TABLE `transaction_history`
  ADD CONSTRAINT `transaction_history_users_FK` FOREIGN KEY (`idUser`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `type_documents`
--
ALTER TABLE `type_documents`
  ADD CONSTRAINT `type_documents_ibfk_1` FOREIGN KEY (`idSectionDocument`) REFERENCES `section_documents` (`idSectionDocument`);

--
-- Contraintes pour la table `verifications_badge`
--
ALTER TABLE `verifications_badge`
  ADD CONSTRAINT `verifications_badge_ibfk_1` FOREIGN KEY (`idAttribution`) REFERENCES `attributions_badge` (`idAttribution`) ON DELETE CASCADE,
  ADD CONSTRAINT `verifications_badge_ibfk_2` FOREIGN KEY (`idProjet`) REFERENCES `projets` (`idProjet`);

--
-- Contraintes pour la table `video_refresh`
--
ALTER TABLE `video_refresh`
  ADD CONSTRAINT `video_refresh_ibfk_1` FOREIGN KEY (`idMenuRefresh`) REFERENCES `menu_refresh` (`idMenuRefresh`),
  ADD CONSTRAINT `video_refresh_ibfk_2` FOREIGN KEY (`idSubmenuRefresh`) REFERENCES `submenu_refresh` (`idSubmenuRefresh`);

--
-- Contraintes pour la table `ville_codeds`
--
ALTER TABLE `ville_codeds`
  ADD CONSTRAINT `ville_codeds_ibfk_1` FOREIGN KEY (`idVille`) REFERENCES `villes` (`idVille`);

CREATE TABLE invoice_contacts (
    idContact INT AUTO_INCREMENT PRIMARY KEY,
    contact_name VARCHAR(100) NOT NULL,
    contact_mail VARCHAR(100),
    contact_phone VARCHAR(20),
    idEtp BIGINT NOT NULL,
    CONSTRAINT fk_contacts_etp FOREIGN KEY (idEtp) REFERENCES users(id)
);

ALTER TABLE invoices ADD COLUMN idContact INT NULL, ADD CONSTRAINT fk_invoices_contact FOREIGN KEY (idContact) REFERENCES invoice_contacts(idContact);

CREATE TABLE bc_contacts (
    idContact INT AUTO_INCREMENT PRIMARY KEY,
    contact_name VARCHAR(100) NOT NULL,
    contact_mail VARCHAR(100),
    contact_phone VARCHAR(20)
);

CREATE TABLE bon_commandes (
    idBC INT PRIMARY KEY AUTO_INCREMENT,
    numero VARCHAR(50) NOT NULL,
    montant DECIMAL(15,2),
    date DATE,
    idDevis BIGINT NOT NULL,
    idContact INT,
    idCfp BIGINT,
    CONSTRAINT fk_devis FOREIGN KEY (idDevis) REFERENCES invoices(idInvoice),
    CONSTRAINT fk_contact FOREIGN KEY (idContact) REFERENCES bc_contacts(idContact),
    CONSTRAINT fk_bc_customer FOREIGN KEY (idCfp) REFERENCES customers(idCustomer)
);

ALTER TABLE bc_contacts ADD COLUMN idBC INT NULL, ADD CONSTRAINT fk_bc_contact FOREIGN KEY (idBC) REFERENCES bon_commandes(idBC);

CREATE TABLE bc_status (
    idStatus INT PRIMARY KEY AUTO_INCREMENT,
    status_name VARCHAR(50) NOT NULL
);

ALTER TABLE bon_commandes ADD COLUMN idStatus INT NULL, ADD CONSTRAINT fk_bc_status FOREIGN KEY (idStatus) REFERENCES bc_status(idStatus);

ALTER table bc_status add COLUMN status_color VARCHAR(7) NOT NULL DEFAULT '#000000';

CREATE TABLE attendance_count(
   id BIGINT AUTO_INCREMENT PRIMARY KEY,
   idProjet bigint(20) ,
   nb_present int,
   nb_absent int,
   nb_total_inscrit int,
   nb_a_saisir int,
   FOREIGN KEY(idProjet) references projets (idProjet) ON DELETE CASCADE
);
ALTER TABLE bon_commandes 
ADD COLUMN date_debut DATE NULL AFTER date,
ADD COLUMN date_fin DATE NULL AFTER date_debut;

CREATE TABLE bc_documents (
    idDocument BIGINT AUTO_INCREMENT PRIMARY KEY,
    idBC INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bc_document FOREIGN KEY (idBC) REFERENCES bon_commandes(idBC) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS working_days_policy (
  id INT AUTO_INCREMENT,
  monday BOOLEAN DEFAULT TRUE,
  tuesday BOOLEAN DEFAULT TRUE,
  wednesday BOOLEAN DEFAULT TRUE,
  thursday BOOLEAN DEFAULT TRUE,
  friday BOOLEAN DEFAULT TRUE,
  saturday BOOLEAN DEFAULT FALSE,
  sunday BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  idCfp BIGINT(20) NULL,  -- Rendue nullable
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id)
)

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

-- FMFP
CREATE TABLE fmfp_status(
   idStatus BIGINT AUTO_INCREMENT PRIMARY KEY,
   status_name VARCHAR(100)
);

CREATE TABLE fmfp_type_project(
   idType BIGINT AUTO_INCREMENT PRIMARY KEY,
   type VARCHAR(20), 
   type_description VARCHAR(255)
);


CREATE  TABLE fmfp_projects (
   id BIGINT AUTO_INCREMENT PRIMARY KEY,
   code_project VARCHAR(255),
   type_fmfp_project BIGINT NOT NULL,
   idCfp BIGINT,
   idTypeProjet INT,
   requested_amount FLOAT NOT NULL,
   approved_amount FLOAT,
   request_date DATE,
   start_date date, 
   end_date date,
   idStatus BIGINT,
   FOREIGN KEY (type_fmfp_project) REFERENCES fmfp_type_project(idType),
   FOREIGN KEY(idCfp) REFERENCES customers(idCustomer),
   FOREIGN KEY (idTypeProjet) REFERENCES type_projets(idTypeProjet),
   FOREIGN KEY (idStatus) REFERENCES fmfp_status(idStatus)
);



CREATE TABLE fmfp_entreprises(
   id BIGINT AUTO_INCREMENT PRIMARY KEY,
   idFmfp_projects BIGINT,
   idEtp BIGINT,
   FOREIGN KEY (idFmfp_projects) REFERENCES fmfp_projects(id),
   FOREIGN KEY (idEtp) REFERENCES customers(idCustomer)
);
-- FMFP
INSERT INTO fmfp_type_project(idType, type, type_description) VALUES
(1, "PFC", "Projets de formation continue"),
(2, "PFPE", "Projets de formation pré-emploi"),
(3, "PFM", "Projets de formation mutualisée"),
(4, "PAI", "Projets d’appui institutionnel ou de renforcement des capacités"),
(5, "PI", "Projets innovants ou spéciaux"),
(6, "PS", "Projets sectoriels ou territoriaux");
-- FMFP

ALTER TABLE fmfp_projects DROP FOREIGN KEY fmfp_projects_ibfk_1;
DROP TABLE fmfp_type_project;




INSERT INTO fmfp_status(idStatus, status_name) VALUES 
(1, 'En soumission'),
(2, 'Analyse'),
(3, 'Refusé'),
(4, 'Exécuté'),
(5, 'Validé'),
(6, 'Cloturé');

ALTER TABLE bon_commandes 
ADD COLUMN modalite int(3) NULL AFTER date_debut;

ALTER TABLE projets
ADD COLUMN idBc INT NULL AFTER idProjet,
ADD CONSTRAINT fk_bc_projets FOREIGN KEY (idBc) REFERENCES bon_commandes(idBC);

ALTER TABLE seances ADD COLUMN is_reported BOOLEAN DEFAULT false;
ALTER TABLE seances ADD COLUMN is_report_undetermined BOOLEAN DEFAULT false;
ALTER TABLE seances ADD COLUMN report_date DATETIME NULL AFTER dateSeance;

ALTER TABLE projets
ADD COLUMN idBc BIGINT NULL AFTER idProjet;

CREATE TABLE invoice_details_acompte(
   idItem BIGINT AUTO_INCREMENT,
   item_qty SMALLINT,
   item_description VARCHAR(200),
   item_unit_price decimal(15,2) NOT NULL DEFAULT 0,
   item_total_price decimal(15,2) NOT NULL DEFAULT 0,
   idInvoice BIGINT NOT NULL,
   idUnite BIGINT NOT NULL,
   PRIMARY KEY(idItem),
   FOREIGN KEY(idInvoice) REFERENCES invoices(idInvoice),
   FOREIGN KEY(idUnite) REFERENCES unites(idUnite)
);

DROP TABLE invoice_acomptes;

CREATE TABLE invoice_acomptes(
    idAcompte BIGINT AUTO_INCREMENT PRIMARY KEY,
    idInvoice BIGINT NOT NULL,
    idBC INT NOT NULL,
    FOREIGN KEY(idInvoice) REFERENCES invoices(idInvoice),
    FOREIGN KEY(idBC) REFERENCES bon_commandes(idBC)
)

ALTER TABLE bon_commandes
MODIFY COLUMN idDevis BIGINT NULL;

ALTER TABLE bon_commandes
ADD COLUMN idEtp BIGINT NULL,
ADD CONSTRAINT fk_customers
    FOREIGN KEY (idEtp) REFERENCES customers(idCustomer);

CREATE TABLE invoice_soldes (
    idSolde BIGINT AUTO_INCREMENT PRIMARY KEY,
    idInvoice BIGINT NOT NULL,
    idBC INT NOT NULL,
    bc_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    acount_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY(idInvoice) REFERENCES invoices(idInvoice),
    FOREIGN KEY(idBC) REFERENCES bon_commandes(idBC)
);

CREATE TABLE invoice_details_solde(
   idItem BIGINT AUTO_INCREMENT,
   item_service VARCHAR(200),
   item_qty SMALLINT,
   item_description VARCHAR(200),
   item_unit_price decimal(15,2) NOT NULL DEFAULT 0,
   item_total_price decimal(15,2) NOT NULL DEFAULT 0,
   idInvoice BIGINT NOT NULL,
   idUnite BIGINT NOT NULL,
   PRIMARY KEY(idItem),
   FOREIGN KEY(idInvoice) REFERENCES invoices(idInvoice),
   FOREIGN KEY(idUnite) REFERENCES unites(idUnite)
);

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

INSERT INTO type_factures VALUES
(4, "Facture de solde");

CREATE TABLE invoice_soldes (
    idSolde BIGINT AUTO_INCREMENT PRIMARY KEY,
    idInvoice BIGINT NOT NULL,
    idBC INT NOT NULL,
    bc_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    acount_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY(idInvoice) REFERENCES invoices(idInvoice),
    FOREIGN KEY(idBC) REFERENCES bon_commandes(idBC)
);

CREATE TABLE invoice_details_solde(
   idItem BIGINT AUTO_INCREMENT,
   item_service VARCHAR(200),
   item_qty SMALLINT,
   item_description VARCHAR(200),
   item_unit_price decimal(15,2) NOT NULL DEFAULT 0,
   item_total_price decimal(15,2) NOT NULL DEFAULT 0,
   idInvoice BIGINT NOT NULL,
   idUnite BIGINT NOT NULL,
   PRIMARY KEY(idItem),
   FOREIGN KEY(idInvoice) REFERENCES invoices(idInvoice),
   FOREIGN KEY(idUnite) REFERENCES unites(idUnite)
);
