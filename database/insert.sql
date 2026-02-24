INSERT INTO sexes VALUES
(1, 'Masculin'),
(2, 'Féminin');

INSERT INTO type_materiels(idTypeMateriel, typeMateriel) VALUES
(1, "Interne"),
(2, "Externe");

INSERT INTO type_services(idTypeService, nomTypeService) VALUES
(NULL, "Déplacement"),
(NULL, "Hebergement"),
(NULL, "Restauration"),
(NULL, "Sonorisation"),
(NULL, "Salle"),
(NULL, "Support"),
(NULL, "Autres");


INSERT INTO modalites VALUES
(NULL, 'Présentielle'),
(NULL, 'En ligne'), 
(NULL, 'Blended');

INSERT INTO type_questions(idTypeQuestion, typeQuestion) VALUES
(1, "Contenu de la formation"),
(2, "FORMATEUR(S)"),
(3, "IMPACT"),
(4, "Aspect de la formation");

INSERT INTO questions(idQuestion, question, idTypeQuestion) VALUES
(1, "Contenu conforme aux objectifs", 1),
(2, "Contenu conforme à vos attentes", 1),
(3, "Rapport théorie / pratique", 1),
(4, "Durée", 1),
(5, "Rythme", 1),
(6, "Support pédagogique", 1),
(7, "Logistique et conditions matérielles", 1),
(8, "Clarté du cours / des explications", 2),
(9, "Maîtrise du sujet par le(s) formateur(s)", 2),
(10, "Disponibilité", 2),
(11, "Méthode pédagogique", 2),
(12, "Intérêt des exercices pratiques", 2),
(13, "Utilité en situation de travail", 3),
(14, "Amélioration du développement", 3),
(15, "Personnel / professionnel", 3),
(16, "Pensez-vous avoir atteint les objectifs que vous vous étiez fixés en début de formation ?", 4),
(17, "Depuis la fin de votre formation, avez-vous pu mettre en pratique les connaissances acquises ?", 4),
(18, "Diriez-vous que la formation vous a permis d'évoluer ?", 4),
(19, "Recommanderiez-vous cette formation à l'un de vos collègues ?", 4);

INSERT INTO domaine_formations(idDomaine, nomDomaine) VALUES
(1, "Achats"),
(2, "Assistant(e)"),
(3, "Audit - Organisation - Conseil"),
(4, "Banque"),
(5, "Bureautique - PAO"),
(6, "Coaching"),
(7, "Commercial - Vente"),
(8, "Communication"),
(9, "Comptabilite - Fiscalité"),
(10, "Conduite du changement"),
(11, "Contrôle de gestion"),
(12, "Création d'entreprise"),
(13, "Digital"),
(14, "Direction"),
(15, "Droit"),
(16, "Droit des affaires"),
(17, "Developpement personnel"),
(18, "Efficacite professionelle"),
(19, "Finance - Trésorerie"),
(20, "Formation"),
(21, "Gestion du temps"),
(22, "Immo & Services généraux"),
(23, "Informatique - SI"),
(24, "Innovation - Créativité"),
(25, "International"),
(26, "Logistique"),
(27, "Management"),
(28, "Marketing"),
(29, "Organisation - Audit"),
(30, "Paie/Admin du personnel"),
(31, "Production"),
(32, "Projet"),
(33, "Qualité-Santé-Sécurité-Environnement"),
(34, "Relation client"),
(35, "Ressources Humaines"),
(36, "Developpement durable"),
(37, "Secteur public"),
(38, "Soft skills"),
(39, 'Télétravail (freelance)'),
(42, 'Langue'),
(43, 'Bureautique Microsoft Excel'),
(44, 'Bureautique Microsoft Power BI'),
(45, 'Bureautique Microsoft Powerpoint'),
(46, 'Bureautique Microsoft Word'),
(47, 'Bureautique Microsoft Access'),
(48, 'Art Oratoire'),
(49, 'Energie et Electricité');

INSERT INTO `secteurs` (`idSecteur`, `secteur`) VALUES
(1, 'BTP & Ressources stratégiques(BTP/DS)'),
(2, 'Développement Rural(DR)'),
(3, 'Technologies de l\'Information&Communication(TIC)'),
(4, 'Textile,Habillements&Accessoires(THA)'),
(5, 'Multi Sectoriel'),
(6, 'Formation équité MPE'),
(7, 'Autres');

INSERT INTO type_projets VALUES
(1, 'Intra'), 
(2, 'Inter'), 
(3, 'Interne');

INSERT INTO type_modules VALUES
(1, 'moduleCfp'), 
(2, 'module interne');

INSERT INTO type_customers VALUES
(1, 'CFP'), 
(2, 'Entreprise');

INSERT INTO paiements VALUES
(1, 'Fonds Propres'),
(2, 'FMFP'),
(3, 'Autres');

INSERT INTO niveau_etudes VALUES
(NULL, 'CEPE'), 
(NULL, 'BEPC'), 
(NULL, 'BACC'), 
(NULL, 'BACC + 1'), 
(NULL, 'BACC + 2'), 
(NULL, 'Licence'), 
(NULL, 'BACC + 4'), 
(NULL, 'Master'), 
(NULL, 'BACC + 6'), 
(NULL, 'BACC + 7'), 
(NULL, 'Doctorat'); 

INSERT INTO `type_formateurs` (`idTypeFormateur`, `type`) VALUES ('1', 'formateur'), ('2', 'formateurInterne');
INSERT INTO `type_fournisseurs` (`idTypeFournisseur`, `typeFournisseur`) VALUES (1, 'fourniseur CFP'), (2, 'fourniseur ETP');

INSERT INTO `specialtites` (`idSp`, `specialite`) VALUES ('1', 'Word'), ('2', 'EXCEL');

INSERT INTO `resp_materiels` (idRespMateriel, respMateriel) VALUES 
(1, 'Centre de formation'), 
(2, 'Apprenant');

INSERT INTO users(id, name, email, password) VALUES
(1, 'Levy', 'contact@formation.mg', '$2y$10$Xi1HQ8tWX5.9s7nTgPbvBOo2pmlrJkk8oNw65aZo9CGai2swByOa2');


INSERT INTO `roles` VALUES
(1, 'SuperAdmin', 'Super Admin'),
(2, 'Admin', 'Administrateur'),
(3, 'Cfp', 'Centre de formation Professionnelle'),
(4, 'Employe', 'Employés'),
(5, 'Formateur', 'Formateur'),
(6, 'Referent', 'Référent'),
(7, 'Formateur interne', 'Formateur interne'),
(8, 'EmployeCfp', 'Sous-Réferent CFP'),
(9, 'EmployeEtp', 'Sous-Référent Entreprise'),
(10, 'Particulier', 'Particulier');

INSERT INTO role_users(id, isActive, hasRole, role_id, user_id) VALUES(NULL, 1, 1, 1, 1);

INSERT INTO secteurs VALUES
(NULL, "BTP & Ressources stratégiques(BTP/DS)"),
(NULL, "Développement Rural(DR)"),
(NULL, "Technologies de l'Information&Communication(TIC)"),
(NULL, "Textile,Habillements&Accessoires(THA)"),
(NULL, "Multi Sectoriel"),
(NULL, "Formation équité MPE"),
(NULL, "Autres");

INSERT INTO pm_types(idTypePm, pm_type_name) VALUES
(1, "Chèque"),
(2, "Virement bancaire"),
(3, "Espèce"),
(4, "Mobile money");

INSERT INTO devises VALUES
(NULL, "MGA"),
(NULL, "USD");

INSERT INTO type_factures VALUES
(1, "Facture"),
(2, "Facture proforma"),
(3, "Facture d'acompte"),
(4, "Facture finale");

INSERT INTO sections(idSection, section) VALUES
(NULL, "Les bases"),
(NULL, "Avancé"),
(NULL, "Expert");

INSERT INTO quizz_questions(idQuestion, question, idSection) VALUES
(NULL, "Il y a combien de type de crayon ?", 1),
(NULL, "Que veut dire le H dans un crayon HB ?", 1),
(NULL, "Qu'est ce que le digital painting ?", 1),
(NULL, "Qu'est ce qu'un masque d'écrêtage ?", 2),
(NULL, "Quel outil est utilisé pour dessiner avec un logiciel PAO", 2),
(NULL, "Comment appelle t-on un logo en noir et blanc dans une charte graphique", 3);

INSERT INTO reponses(idReponse, reponse, idQuestion) VALUES
(NULL, 1, 1),
(NULL, 2, 1),
(NULL, 3, 1),
(NULL, 4, 1),
(NULL, 5, 1),
(NULL, "High", 2),
(NULL, "Hard", 2),
(NULL, "Hammer", 2),
(NULL, "Hash", 2),
(NULL, "Dessiner avec de la peinture", 3),
(NULL, "Dessiner en utilisant des crayons de couleur", 3),
(NULL, "Dessiner avec un logiciel PAO", 3),
(NULL, "C'est le fait de masquer les lignes qui dépassent", 4),
(NULL, "Ca ne veut rien dire", 4),
(NULL, "C'est un filtre utiliser dans un langage de développement", 4),
(NULL, "Crayon", 5),
(NULL, "Peinceau", 5),
(NULL, "Plume", 5),
(NULL, "Noir et Blanc", 6),
(NULL, "Achromatique", 6),
(NULL, "Grayscale", 6);

INSERT INTO unites(idUnite, unite_name) VALUES
(NULL, 'Personne'),
(NULL, 'Groupe'),
(NULL, 'Jour'),
(NULL, 'Unité');

INSERT INTO frais(frais, exemple) VALUES
("Honoraire formateur", "Séminaire, atelier, conférence"),
("Frais de déplacement", "Train, voiture, avion"),
("Frais d'hébergement", "Hôtel, chambre, auberge"),
("Frais de restauration", "Déjeuner, dîner, café"),
("Frais de location salle", "Salle, auditorium, local"),
("Frais de location équipement", "Projecteur, écran, microphone"),
("Frais de fourniture de bureau", "Stylos, papier, classeurs"),
("Frais technique", "Maintenance, support, installation"),
("Assurance", "Responsabilité, santé, professionnelle"),
("Frais de gestion administratif", "Comptabilité, secrétariat, juridique"),
("Autre","Divers,imprévus,évènements");

INSERT INTO `plans` (`id`, `name`, `dedicate`, `slug`, `description`, `user_type`, `is_recommander`, `is_active`, `price`, `signup_fee`, `currency`, `trial_period`, `trial_interval`, `invoice_period`, `invoice_interval`, `grace_period`, `grace_interval`, `prorate_day`, `prorate_period`, `prorate_extend_due`, `active_subscribers_limit`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '{\"fr\":\"invité\"}', '{\"fr\":\"Expérience inédite toujours gratuite\"}', 'invité', '{\"fr\":\"Vous avez accès aux projets de l entreprise qui vous a invité mais par contre vous ne pouvez pas envoyer une demande de collaboration.\"}', 'centre de formation', 0, 1, 0, 0, 'Ar', 10, 'day', 1, 'MONTH', 0, 'DAY', NULL, NULL, NULL, NULL, 2, '2024-07-18 07:44:39', '2024-07-24 13:30:24', NULL),
(2, '{\"fr\":\"indépendant\"}', '{\"fr\":\"Pour les travailleurs indépendants\"}', 'independant', '{\"fr\":\"Une offre unique dédiée aux formateurs indépendants.\"}', 'centre de formation', 1, 1, 50000, 0, 'Ar', 10, 'day', 1, 'MONTH', 0, 'DAY', NULL, NULL, NULL, NULL, 2, '2024-07-18 07:44:39', '2024-07-18 07:44:39', NULL),
(3, '{\"fr\":\"équipe\"}', '{\"fr\":\"Pour les petite équipes\"}', 'equipe', '{\"fr\":\"La seule platefonne tout intégrée pour les petites équipes de fonnation.\"}', 'centre de formation', 0, 1, 100000, 0, 'Ar', 10, 'day', 1, 'MONTH', 0, 'DAY', NULL, NULL, NULL, NULL, 3, '2024-07-18 07:45:48', '2024-07-18 07:45:48', NULL),
(4, '{\"fr\":\"illimité\"}', '{\"fr\":\"Pour les grandes équipes\"}', 'illimite', '{\"fr\":\"Gestion administrative illimitée pour les organismes.\"}', 'centre de formation', 0, 1, 200000, 0, 'Ar', 10, 'day', 1, 'MONTH', 0, 'DAY', NULL, NULL, NULL, NULL, 4, '2024-07-18 07:46:43', '2024-07-18 07:46:43', NULL),
(5, '{\"fr\":\"invité\"}', '{\"fr\":\"Expérience inédite toujours gratuite\"}', 'invitée', '{\"fr\":\"Vous avez accès aux projets de l entreprise qui vous a invité mais par contre vous ne pouvez pas envoyer une demande de collaboration.\"}', 'entreprise', 0, 1, 0, 0, 'Ar', 10, 'day', 1, 'MONTH', 0, 'DAY', NULL, NULL, NULL, NULL, 2, '2024-07-18 07:44:39', '2024-07-24 13:30:24', NULL),
(6, '{\"fr\":\"premium\"}', '{\"fr\":\"Pour les travailleurs indépendants\"}', 'independante', '{\"fr\":\"Une offre unique dédiée aux formateurs indépendants.\"}', 'entreprise', 1, 1, 25000, 0, 'Ar', 10, 'day', 1, 'MONTH', 0, 'DAY', NULL, NULL, NULL, NULL, 2, '2024-07-19 07:44:39', '2024-07-19 07:44:39', NULL),
(7, '{\"fr\":\"Pro\"}', '{\"fr\":\"Pour les petite équipes\"}', 'equipee', '{\"fr\":\"La seule platefonne tout intégrée pour les petites équipes de fonnation.\"}', 'entreprise', 0, 1, 50000, 0, 'Ar', 10, 'day', 1, 'MONTH', 0, 'DAY', NULL, NULL, NULL, NULL, 3, '2024-07-19 07:45:48', '2024-07-19 07:45:48', NULL),
(8, '{\"fr\":\"business\"}', '{\"fr\":\"Pour les grandes équipes\"}', 'illimitee', '{\"fr\":\"Gestion administrative illimitée pour les organismes.\"}', 'entreprise', 0, 1, 100000, 0, 'Ar', 10, 'day', 1, 'MONTH', 0, 'DAY', NULL, NULL, NULL, NULL, 4, '2024-07-19 07:46:43', '2024-07-19 07:46:43', NULL);

INSERT INTO `features` (`id`, `plan_id`, `name`, `slug`, `description`, `value`, `resettable_period`, `resettable_interval`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '{\"fr\":\"Référents\"}', 'ref_in', NULL, '1', 1, 'month', 1, '2024-07-18 07:49:05', '2024-07-18 07:49:05', NULL),
(2, 1, '{\"fr\":\"Formateurs\"}', 'form_in', NULL, '1', 1, 'month', 2, '2024-07-18 07:49:32', '2024-07-18 07:49:32', NULL),
(3, 1, '{\"fr\":\"Projets\"}', 'pro_in', NULL, '1', 1, 'month', 3, '2024-07-18 07:50:00', '2024-07-18 07:50:00', NULL),
(4, 2, '{\"fr\":\"Référents\"}', 'ref_ind', NULL, '2', 1, 'month', 4, '2024-07-18 07:49:05', '2024-07-18 07:49:05', NULL),
(5, 2, '{\"fr\":\"Formateurs\"}', 'form_ind', NULL, '3', 1, 'month', 5, '2024-07-18 07:49:32', '2024-07-18 07:49:32', NULL),
(6, 2, '{\"fr\":\"Projets\"}', 'pro_ind', NULL, '4', 1, 'MONTH', 6, '2024-07-18 07:50:00', '2024-07-24 13:45:23', NULL),
(7, 3, '{\"fr\":\"Référents\"}', 'ref_eq', NULL, '5', 1, 'month', 7, '2024-07-18 07:50:50', '2024-07-18 07:50:50', NULL),
(8, 3, '{\"fr\":\"Formateurs\"}', 'for_eq', NULL, '12', 1, 'month', 8, '2024-07-18 07:51:21', '2024-07-18 07:51:21', NULL),
(9, 3, '{\"fr\":\"Projets\"}', 'pro_eq', NULL, '16', 1, 'month', 9, '2024-07-18 07:53:34', '2024-07-18 07:53:34', NULL),
(10, 4, '{\"fr\":\"Référents\"}', 'ref_ill', NULL, 'illimités', 1, 'month', 10, '2024-07-18 07:54:02', '2024-07-18 07:54:02', NULL),
(11, 4, '{\"fr\":\"Formateurs\"}', 'for_ill', NULL, 'illimités', 1, 'month', 11, '2024-07-18 07:54:29', '2024-07-18 07:54:29', NULL),
(12, 4, '{\"fr\":\"Projets\"}', 'pro_ill', NULL, 'illimités', 1, 'month', 12, '2024-07-18 07:54:53', '2024-07-18 07:54:53', NULL),
(13, 5, '{\"fr\":\"Référents\"}', 'ref_e', NULL, '1', 1, 'month', 1, '2024-07-18 07:49:05', '2024-07-18 07:49:05', NULL),
(14, 5, '{\"fr\":\"Formateurs\"}', 'form_e', NULL, '1', 1, 'month', 2, '2024-07-18 07:49:32', '2024-07-18 07:49:32', NULL),
(15, 5, '{\"fr\":\"Projets\"}', 'pro_e', NULL, '1', 1, 'month', 3, '2024-07-18 07:50:00', '2024-07-18 07:50:00', NULL),
(16, 6, '{\"fr\":\"Référents\"}', 'ref_ed', NULL, '2', 1, 'month', 4, '2024-07-18 07:49:05', '2024-07-18 07:49:05', NULL),
(17, 6, '{\"fr\":\"Formateurs\"}', 'form_ed', NULL, '3', 1, 'month', 5, '2024-07-18 07:49:32', '2024-07-18 07:49:32', NULL),
(18, 6, '{\"fr\":\"Projets\"}', 'pro_ed', NULL, '4', 1, 'MONTH', 6, '2024-07-18 07:50:00', '2024-07-24 13:45:23', NULL),
(19, 7, '{\"fr\":\"Référents\"}', 'ref_et', NULL, '5', 1, 'month', 7, '2024-07-18 07:50:50', '2024-07-18 07:50:50', NULL),
(20, 7, '{\"fr\":\"Formateurs\"}', 'for_et', NULL, '12', 1, 'month', 8, '2024-07-18 07:51:21', '2024-07-18 07:51:21', NULL),
(21, 7, '{\"fr\":\"Projets\"}', 'pro_et', NULL, '16', 1, 'month', 9, '2024-07-18 07:53:34', '2024-07-18 07:53:34', NULL),
(22, 8, '{\"fr\":\"Référents\"}', 'ref_i', NULL, 'illimités', 1, 'month', 10, '2024-07-18 07:54:02', '2024-07-18 07:54:02', NULL),
(23, 8, '{\"fr\":\"Formateurs\"}', 'for_i', NULL, 'illimités', 1, 'month', 11, '2024-07-18 07:54:29', '2024-07-18 07:54:29', NULL),
(24, 8, '{\"fr\":\"Projets\"}', 'pro_i', NULL, 'illimités', 1, 'month', 12, '2024-07-18 07:54:53', '2024-07-18 07:54:53', NULL);

INSERT INTO type_entreprises(idTypeEtp, type_etp, type_etp_desc) VALUES
-- ireto 3 premier ireto tsy azo ovaina
(1, "etp_single", "Entreprise privée"),
(2, "etp_groupe", "Groupe d'entreprise"),
(3, "etp_grouped", "Entreprise membre d'un groupe"),
-- vao nampidirina ireto
-- NB: ny etp_single ihany no etp_private
(4, "etp_public_institution", "Institution publique"),
(5, "etp_ong_association", "ONG ou Association"),
(6, "etp_french_zone_and_special", "Zone franche et entreprise spéciale"),
(7, "etp_others", "Autres"),
-- ireto 2 ireto tsy manara-dàlana
(8, "particuliers", "Particulier"),
(9, "cfp", "Centre de formation");

INSERT INTO type_images(typeImage) VALUES
("momentum");

INSERT INTO restaurations(idRestauration, typeRestauration) VALUES
(1, "Petit déjeuner"),
(2, "Pause café matin"),
(3, "Déjeuner"),
(4, "Pause café après-midi"),
(5, "Dîner"),
(6, "Bouteille d'eau");

INSERT INTO section_documents (section_document) VALUES
('Offre'),
('Contrat'),
('Finance'),
('Rapport'),
('FMFP');

INSERT INTO type_documents(idSectionDocument, type_document) VALUES
(1, 'Offre technique'),
(1, 'Offre financier'),
(2, 'Contrat'),
(2, 'Avenant'),
(2, 'Convention'),
(2, 'Terme de référence'),
(3, 'Bon de commande'),
(3, 'Devis'),
(3, 'Facture'),
(4, 'Rapport de démarrage'),
(4, 'Rapport final'),
(4, 'Autre rapport'),
(5, 'Rapport technique'),
(5, 'Rapport financier'),
(5, 'Budget détaillé'),
(5, 'Cahier de charge'),
(5, 'Lettre de demande de financement'),
(5, 'Lettre de mandat');

INSERT INTO invoice_status(idInvoiceStatus, invoice_status_name) VALUES
(1, "Brouillon"),
(2, "Non envoyé"),
(3, "Envoyé"),
(4, "Payé"),
(5, "Partiel"),
(6, "Impayé"),
(7, "Convertis"),
(8, "Expiré"),
(9, "Annulé"),
(10, "En relance active"),
(11, "Douteuse"),
(12, "En litige"),
(13, "Irrecouvrable"),
(14, "Transmis à huissier"),
(15, "En poursuite judiciaire");

INSERT INTO module_levels(idLevel, module_level_name) VALUES
(NULL, "Fondamentaux"),
(NULL, "Intermédiaire"),
(NULL, "Avancée"),
(NULL, "MasterClass");

INSERT INTO type_publicites (type_pub_name) values ('publicite simple');
INSERT INTO type_publicites (type_pub_name) values ('publicite cfp');
INSERT INTO type_publicites (type_pub_name) values ('publicite etp');

-- villes et code postal
INSERT INTO villes(idVille, ville) VALUES
(1, "Antananarivo"),
(2, "Antsiranana"),
(3, "Fianarantsoa"),
(4, "Mahajanga"),
(5, "Toamasina"),
(6, "Toliary");

INSERT INTO lieu_types(idLieuType, lt_name) VALUES
(1, "public"),
(2, "private"),
(3, "etp_from_cfp");

INSERT INTO `ville_codeds` (`id`, `ville_name`, `vi_code_postal`, `idVille`) VALUES
(1, 'Antananarivo', '101', 1),
(2, 'Antananarivo', '102', 1),
(3, 'Antananarivo', '103', 1),
(4, 'Ambohidratrimo', '105', 1),
(5, 'Andramasina', '106', 1),
(6, 'Anjozorobe', '107', 1),
(7, 'Ankazobe', '108', 1),
(8, 'Antananarivo Nord', '103', 1),
(9, 'Antananarivo Sud', '102', 1),
(10, 'Manjakandriana', '116', 1),
(11, 'Fenoarivo Centre', '115', 1),
(12, 'Tsiroanomandidy', '119', 1),
(13, 'Arivonimamo', '112', 1),
(14, 'Miarinarivo', '117', 1),
(15, 'Soavinandriana', '118', 1),
(16, 'Ambatolampy', '104', 1),
(17, 'Antanifotsy', '109', 1),
(18, 'Antsirabe Rural', '111', 1),
(19, 'Antsirabe Urban', '110', 1),
(20, 'Betafo', '113', 1),
(21, 'Faratsiho', '114', 1),
(22, 'Ambanja', '203', 2),
(23, 'Ambilobe', '204', 2),
(24, 'Antsiranana Rural', '202', 2),
(25, 'Antsiranana Urban', '201', 2),
(26, 'Nosy Be', '207', 2),
(27, 'Andapa', '205', 2),
(28, 'Antalaha', '206', 2),
(29, 'Sambava', '208', 2),
(30, 'Vohimarina (Iharana)', '209', 2),
(31, 'Ambatofinandrahana', '304', 3),
(32, 'Ambositra', '306', 3),
(33, 'Fandriana', '308', 3),
(34, 'Manandriana', '323', 3),
(35, 'Befotaka', '307', 3),
(36, 'Farafangana', '309', 3),
(37, 'Midongy Sud', '318', 3),
(38, 'Vangaindrano', '320', 3),
(39, 'Vondrozo', '322', 3),
(40, 'Ambalavao', '303', 3),
(41, 'Ambohimahasoa', '305', 3),
(42, 'Fianarantsoa Rural', '302', 3),
(43, 'Fianarantsoa Urban', '301', 3),
(44, 'Ikalamavony', '314', 3),
(45, 'Iakora', '311', 3),
(46, 'Ihosy', '313', 3),
(47, 'Ivohibe', '315', 3),
(48, 'Ifanadiana', '312', 3),
(49, 'Ikongo', '310', 3),
(50, 'Manakara Sud', '316', 3),
(51, 'Mananjary', '317', 3),
(52, 'Nosy Varika', '319', 3),
(53, 'Vohipeno', '321', 3),
(58, 'Antsanitia ', '402', 4),
(59, 'Sambaina ', '401', 4),
(60, 'Katsepy ', '402', 4),
(61, 'Marovoay ', '402', 4),
(62, 'Mampikony ', '403', 4),
(63, 'Amborovy ', '401', 4),
(64, 'Maevatanana ', '411', 4),
(65, 'Ampahialina ', '411', 4),
(66, 'Ankazobe ', '411', 4),
(67, 'Ambatolahy ', '411', 4),
(68, 'Antanifotsy ', '412', 4),
(69, 'Tsiroanomandidy ', '412', 4),
(70, 'Antsohihy (centre-ville) ', '415', 4),
(71, 'Mampikony ', '415', 4),
(72, 'Ambalavato ', '415', 4),
(73, 'Mandritsara ', '415', 4),
(74, 'Boriziny ', '415', 4),
(75, NULL, NULL, 4),
(76, 'Ambatondrazaka', '503', 5),
(77, 'Amparafaravola', '504', 5),
(78, 'Andilamena', '505', 5),
(79, 'Anosibe', '506', 5),
(80, 'Moramanga', '514', 5),
(81, 'Fenoarivo Atsinanana', '509', 5),
(82, 'Mananara', '511', 5),
(83, 'Maroansetra', '512', 5),
(84, 'Nosy-Boraha (Ste Marie)', '515', 5),
(85, 'Soanierana-Ivongo', '516', 5),
(86, 'Vavatenina', '518', 5),
(87, 'Ampasimanolotra', '508', 5),
(88, 'Antanambao Manampotsy', '507', 5),
(89, 'Mahanoro', '510', 5),
(90, 'Marolambo', '513', 5),
(91, 'Toamasina Rural', '502', 5),
(92, 'Toamasina Urban', '501', 5),
(93, 'Vatomandry', '517', 5),
(94, 'Ambovombe-Androy', '604', 6),
(95, 'Bekily', '607', 6),
(96, 'Beloha', '609', 6),
(97, 'Tsiombe', '621', 6),
(98, 'Amboasary Sud', '603', 6),
(99, 'Betroka', '613', 6),
(100, 'Taolagnaro', '614', 6),
(101, 'Ampanihy', '605', 6),
(102, 'Ankazoabo Sud', '606', 6),
(103, 'Benenitra', '610', 6),
(104, 'Beroroha', '611', 6),
(105, 'Betioky Sud', '612', 6),
(106, 'Morombe', '618', 6),
(107, 'Sakaraha', '620', 6),
(108, 'Toliara Rural', '602', 6),
(109, 'Toliara Urban', '601', 6),
(110, 'Belon-i Tsiribihina', '608', 6),
(111, 'Mahabo', '615', 6),
(112, 'Manja', '616', 6),
(113, 'Miandrivazo', '617', 6),
(114, 'Morondava', '619', 6);

/**
Testing Center
*/

-- Insertion de catégories de réponses
INSERT INTO `categories_reponses` (`idCategorie`, `nomCategorie`, `descriptionCategorie`, `created_at`, `updated_at`) VALUES
(9, 'Formules et Fonctions', 'Catégorie pour les réponses relatives aux formules et fonctions Excel', '2024-10-18 10:50:50', '2024-10-18 10:50:50'),
(10, 'Gestion des Données', 'Catégorie pour les réponses relatives à la gestion et la manipulation des données dans Excel', '2024-10-18 10:50:50', '2024-10-18 10:50:50'),
(11, 'Graphiques et Visualisation', 'Catégorie pour les réponses relatives à la création et la personnalisation des graphiques et des visualisations dans Excel', '2024-10-18 10:50:50', '2024-10-18 10:50:50'),
(12, 'Raccourcis et Productivité', 'Catégorie pour les réponses relatives à l\'utilisation des raccourcis et des techniques de productivité dans Excel', '2024-10-18 10:50:50', '2024-10-18 10:50:50'),
(13, 'Analyse des Données', 'Catégorie pour les réponses relatives à l\'analyse et l\'interprétation des données dans Excel', '2024-10-18 10:50:50', '2024-10-18 10:50:50'),
(14, 'Environnement Excel', 'Catégorie pour les réponses relatives à l\'environnement et aux paramètres de Excel', '2024-10-18 10:50:50', '2024-10-18 10:50:50'),
(15, 'Gestion des Erreurs et Dépannage', 'Catégorie pour les réponses relatives à la gestion des erreurs et au dépannage dans Excel', '2024-10-18 10:50:50', '2024-10-18 10:50:50'),
(17, 'Recrutement et sélection', NULL, '2024-11-22 08:14:37', '2024-11-22 08:14:37'),
(18, 'Dax', 'Test de dax', '2024-12-08 00:52:58', '2024-12-08 00:52:58'),
(19, 'Langue Française', NULL, '2025-02-13 08:39:14', '2025-02-13 08:39:14'),
(20, 'Team working', 'Team working', '2025-03-13 09:11:17', '2025-03-13 09:11:17'),
(21, 'Adaptability/Change Management', 'Adaptability/Change Management', '2025-03-13 09:11:40', '2025-03-13 09:11:40'),
(22, 'Analytical thinking', 'Analytical thinking', '2025-03-13 09:12:00', '2025-03-13 09:12:00'),
(23, 'Communication', 'Communication', '2025-03-13 09:12:18', '2025-03-13 09:12:18'),
(24, 'Decision Making', 'Decision Making', '2025-03-13 09:12:42', '2025-03-13 09:12:42'),
(25, 'Developing others', 'Developing others', '2025-03-13 09:13:01', '2025-03-13 09:13:01'),
(26, 'Developing self', 'Developing self', '2025-03-13 09:13:26', '2025-03-13 09:13:26'),
(27, 'Integrity and Ethical management', 'Integrity and Ethical management', '2025-03-13 09:13:44', '2025-03-13 09:13:44'),
(28, 'Leadership', 'Leadership', '2025-03-13 09:14:06', '2025-03-13 09:14:06'),
(29, 'Motivation', 'Motivation', '2025-03-13 09:20:20', '2025-03-13 09:20:20'),
(30, 'Planning and Organising', 'Planning and\r\nOrganising', '2025-03-13 09:20:38', '2025-03-13 09:20:38'),
(31, 'Relationship Building', 'Relationship Building', '2025-03-13 09:21:00', '2025-03-13 09:21:00'),
(32, 'coaching', NULL, '2025-04-15 09:09:20', '2025-04-15 09:09:20'),
(33, 'developpement personnel', NULL, '2025-04-15 12:22:02', '2025-04-15 12:22:02'),
(34, 'Achat', NULL, '2025-04-15 13:28:47', '2025-04-15 13:28:47'),
(35, 'c\'est quoi ça?', NULL, '2025-04-18 07:57:15', '2025-04-18 07:57:15'),
(36, 'DIGITAL', NULL, '2025-04-22 13:26:26', '2025-04-22 13:26:26'),
(37, 'COMMERCIAL', NULL, '2025-04-22 13:28:45', '2025-04-22 13:28:45'),
(38, 'MARKETING', NULL, '2025-04-22 13:35:21', '2025-04-22 13:35:21'),
(39, 'coding', NULL, '2025-04-25 09:19:29', '2025-04-25 09:19:29'),
(40, 'RELATION CLIENT', NULL, '2025-05-12 07:51:01', '2025-05-12 07:51:01'),
(41, 'Réflexion sur la gestion de la situation', NULL, '2025-05-12 08:01:38', '2025-05-12 08:01:38'),
(42, 'Marketing Design', NULL, '2025-05-13 11:53:50', '2025-05-13 11:53:50'),
(43, 'a', NULL, '2025-09-22 09:57:27', '2025-09-22 09:57:27'),
(44, 'az', NULL, '2025-09-22 11:26:23', '2025-09-22 11:26:23');

-- Insertion des niveaux
INSERT INTO niveau_qcm(id, niveau) VALUES
(1, "Débutant"),
(2, "Intermédiaire"),
(3, "Avancé"),
(4, "Expert"),
(5, "Maître");

-- Pour Excel
INSERT INTO `qcm_bareme` (`idQCM`, `minPoints`, `maxPoints`, `id_niveau`) VALUES
(null, 0, 20, 1),
(null, 21, 40, 2),
(null, 41, 60, 3),
(null, 61, 80, 4),
(null, 81, 100, 5);

-- Insertion de QCM (Excel ihany aloha hatreto)
INSERT INTO qcm (idQCM, user_id, intituleQCM, descriptionQCM, idDomaine, prixUnitaire, statut, duree, created_at, updated_at) VALUES(1, null, 'Test Excel', 'Description Test Excel', 5, 55.00, 0, 300, '2024-10-22 16:54:46', '2024-10-22 16:55:08');

INSERT INTO `type_qcm` (`id`, `type`) VALUES
(1, 'qcm'),
(2, 'reponse courte');

-- Insertion de questions pour QCM Excel
INSERT INTO qcm_questions (idQuestion, idQCM, texteQuestion, created_at, updated_at) VALUES(1, 1, 'Quelle fonction permet de trouver la moyenne ?', '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_questions (idQuestion, idQCM, texteQuestion, created_at, updated_at) VALUES(2, 1, 'Comment trier des données par ordre alphabétique ?', '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_questions (idQuestion, idQCM, texteQuestion, created_at, updated_at) VALUES(3, 1, 'Quel raccourci clavier permet de copier une cellule ?', '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_questions (idQuestion, idQCM, texteQuestion, created_at, updated_at) VALUES(4, 1, 'Quel outil utiliser pour créer un graphique dynamique ?', '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_questions (idQuestion, idQCM, texteQuestion, created_at, updated_at) VALUES(5, 1, 'Comment diagnostiquer une erreur #N/A dans une cellule ?', '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_questions (idQuestion, idQCM, texteQuestion, created_at, updated_at) VALUES(6, 1, 'Quelle fonction permet d’arrondir un nombre au nombre entier inférieur ?', '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_questions (idQuestion, idQCM, texteQuestion, created_at, updated_at) VALUES(7, 1, 'Comment créer une liste déroulante dans une cellule ?', '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_questions (idQuestion, idQCM, texteQuestion, created_at, updated_at) VALUES(8, 1, 'Quel est le raccourci clavier pour sauvegarder rapidement un fichier ?', '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_questions (idQuestion, idQCM, texteQuestion, created_at, updated_at) VALUES(9, 1, 'Quelle fonction est utilisée pour chercher une valeur dans une colonne spécifique et renvoyer une autre valeur dans la même ligne ?', '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_questions (idQuestion, idQCM, texteQuestion, created_at, updated_at) VALUES(10, 1, 'Comment mettre en évidence les cellules dont la valeur dépasse une certaine limite ?', '2024-10-22 16:54:46', '2024-10-22 16:54:46');

-- Insertion de réponses pour le QCM Excel
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(1, 1, 9, 'SOMME()', -2, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(2, 1, 9, 'MOYENNE()', 5, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(3, 1, 9, 'MIN()', -2, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(4, 2, 10, 'Utiliser les filtres', 2, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(5, 2, 10, 'Cliquer sur "Trier A-Z"', 5, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(6, 2, 10, 'Appliquer un tableau croisé dynamique', -3, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(7, 3, 12, 'Ctrl + C', 4, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(8, 3, 12, 'Ctrl + X', -3, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(9, 3, 12, 'Ctrl + V', -3, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(10, 4, 11, 'Sparklines', 3, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(11, 4, 11, 'Tableaux croisés dynamiques', 5, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(12, 4, 11, 'Valeurs conditionnelles', -3, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(13, 5, 15, 'Utiliser la fonction RECHERCHEV', 4, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(14, 5, 15, 'Rechercher les formules mal écrites', 3, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(15, 5, 15, 'Ignorer l’erreur', -5, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(16, 6, 9, 'ARRONDI()', -2, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(17, 6, 9, 'ENT()', 5, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(18, 6, 9, 'ROND()', -2, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(19, 7, 10, 'Utiliser les validations de données', 5, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(20, 7, 10, 'Filtrer les données', -3, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(21, 7, 10, 'Formater comme tableau', -2, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(22, 8, 12, 'Ctrl + S', 4, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(23, 8, 12, 'Ctrl + P', -3, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(24, 8, 12, 'Ctrl + Q', -4, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(25, 9, 9, 'RECHERCHEH', -2, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(26, 9, 9, 'RECHERCHEV', 5, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(27, 9, 9, 'INDEX()', 3, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(28, 10, 10, 'Formater les cellules', -2, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(29, 10, 10, 'Appliquer une mise en forme conditionnelle', 5, '2024-10-22 16:54:46', '2024-10-22 16:54:46');
INSERT INTO qcm_reponses (idReponse, idQuestion, categorie_id, texteReponse, points, created_at, updated_at) VALUES(30, 10, 10, 'Utiliser les filtres', -3, '2024-10-22 16:54:46', '2024-10-22 16:54:46');

-- Données test pour les packs de crédits
INSERT INTO `credits_packs` (`type_pack`, `description_pack`, `credits`, `pack_price`, `currency`, `is_active`, `created_at`, `updated_at`) VALUES
('Pack Bronze', 'Petit pack de crédits pour les nouveaux utilisateurs', 100, 5.00, 'USD', 0, NOW(), NOW()),
('Pack Argent', 'Pack intermédiaire de crédits pour les utilisateurs réguliers', 250, 12.00, 'USD', 0, NOW(), NOW()),
('Pack Or', 'Pack de crédits pour les utilisateurs actifs', 500, 20.00, 'USD', 0, NOW(), NOW()),
('Pack Platine', 'Grand pack de crédits pour les utilisateurs avancés', 1000, 35.00, 'USD', 0, NOW(), NOW()),
('Pack Diamant', 'Pack premium de crédits pour les utilisateurs fréquents', 2000, 60.00, 'USD', 0, NOW(), NOW()),
('Pack Découverte', 'Pack d''essai pour tester les fonctionnalités', 50, 10000.00, 'MGA', 1, NOW(), NOW()),
('Pack Standard', 'Pack standard pour une utilisation modérée', 200, 40000.00, 'MGA', 1, NOW(), NOW()),
('Pack Professionnel', 'Pack de crédits pour les utilisateurs avancés', 750, 125000.00, 'MGA', 1, NOW(), NOW()),
('Pack VIP', 'Pack de crédits pour les utilisateurs VIP', 1500, 200000.00, 'MGA', 1, NOW(), NOW()),
('Pack Ultime', 'Pack ultime de crédits pour une utilisation intensive', 3000, 350000.00, 'MGA', 1, NOW(), NOW());
-- Données test pour les packs de crédits
-- Réinitialisation auto increment
ALTER TABLE qcm AUTO_INCREMENT = 1;
ALTER TABLE qcm_questions AUTO_INCREMENT = 1;
ALTER TABLE qcm_reponses AUTO_INCREMENT = 1;
ALTER TABLE reponses_utilisateurs AUTO_INCREMENT = 1;
ALTER TABLE sessions_test AUTO_INCREMENT = 1; 
ALTER TABLE credits_packs AUTO_INCREMENT = 1; 
-- Réinitialisation auto increment 

INSERT INTO `commissions_settings` (`payment_type`, `commission_rate`, `currency`, `created_at`, `updated_at`) VALUES
('cb', 2.50, 'MGA', NOW(), NOW()),
('cb', 2.50, 'USD', NOW(), NOW()),
('cheque', 1.50, 'MGA', NOW(), NOW()),
('cheque', 1.50, 'USD', NOW(), NOW()),
('virement', 2.00, 'MGA', NOW(), NOW()),
('virement', 2.00, 'USD', NOW(), NOW());

-- évaluation à froids
INSERT INTO quizz_levels(id, quizz_level_value, quizz_level_desc) VALUES
(1, 0, "Non"),
(2, 1, "Oui"),
(3, 2, "Partiellement");

INSERT INTO quizz_types(id, quizz_name) VALUES
(1, "Impact professionnel"),
(2, "Utilisation des compétences");

INSERT INTO quizz_colds(id, quizz_cold_name, idQuizzType) VALUES
(null, "Avez-vous eu l'occasion d'appliquer les compétences acquises lors de la formation ?", 2),
(null, "La formation a-t-elle eu un impact positif sur votre travail ?", 1),
(null, "La formation vous a-t-elle aidé à atteindre vos objectifs professionnels ?", 1);

-- requête pour donner 1000 crédits à tous les référents / entreprises (Testing Center)
-- Mettre à jour les soldes existants
update
	credits_wallet cw
inner join users u on
	u.id = cw.idUser
set
	cw.solde = cw.solde + 1000,
	cw.updated_at = CURRENT_TIMESTAMP
where
	exists (
	select
		1
	from
		customers c
	where
		c.idCustomer = u.id
)
	and u.user_is_deleted = 0;

-- Insérer de nouveaux wallets pour les customers qui n'en ont pas encore
insert
	into
	credits_wallet (idUser,
	solde)
select
	u.id,
	1000
from
	customers c
inner join users u on
	u.email = c.customerEmail
where
	u.user_is_deleted = 0
	and not exists (
	select
		1
	from
		credits_wallet cw
	where
		cw.idUser = u.id
);

INSERT INTO `type_images`(`idTypeImage`, `typeImage`) VALUES (2,'questionImg'); -- question image
-- requête pour donner 1000 crédits à tous les référents / entreprises (Testing Center)

INSERT INTO reward_types(id, name) VALUES
(1, "Gratuit"),
(2, "Réduction");

INSERT INTO reward_scopes(id, name, description) VALUES
(1, "Rewards Full", "Accès à tout les modules de formation"),
(2, "Rewards Less", "Accès à quelques modules de formation");

-- start nif stat rcs and currencies
INSERT INTO rcs_names(id, name, description) VALUES
(1, "RCS", "Registre du Commerce et des Sociétés");

INSERT INTO stat_names(id, name, description) VALUES
(1, "STAT", "Numéro Statistique");

INSERT INTO nif_names(id, name, description) VALUES
(1, "NIF", "Numéro d'Identification Fiscale");

INSERT INTO currencies(id, code, symbol, unit, description) VALUES
(1, "MGA", "Ar", "Ariary", "Malagasy Ariary"),
(2, "xof", "CFA Franc", "CFA Franc", "CFA Franc");

INSERT INTO countriess(id, name, code, flag, id_nif_name, id_currency) VALUES
(1, 'Madagascar', 'MG', 'https://flagcdn.com/mg.svg', 1, 1),
(2, "Côte d'Ivoire", 'CI', 'https://flagcdn.com/ci.svg', 1, 2);

INSERT INTO country_fulls(id, id_rcs_name, id_stat_name) VALUES
(1, 1, 1),
(2, 1, 1);
-- end nif stat rcs and currencies

INSERT INTO bc_status (idStatus, status_name, status_color) VALUES
(1, 'Nouveau', '#95a5a6'),
(2, 'Planifié', '#3498db'),
(3, 'En cours', '#f39c12'),
(4, 'Exécuté', '#2ecc71'),
(5, 'Annulé', '#e74c3c'), 
(6, 'Facturé', '#9b59b6'),
(7, 'Payé', '#27ae60'),
(8, 'Partiel', '#d48607');


-- FMFP
INSERT INTO fmfp_type_project(idType, type, type_description) VALUES
(1, "PFC", "Projets de formation continue"),
(2, "PFPE", "Projets de formation pré-emploi"),
(3, "PI", "Projets innovants ou spéciaux"),
(4, "PS", "Projets sectoriels ou territoriaux");



INSERT INTO fmfp_status(idStatus, status_name) VALUES 
(1, 'En soumission'),
(2, 'Analyse'),      
(3, 'Refusé'),      
(4, 'Exécuté'),      
(5, 'Validé'),       
(6, 'Cloturé');    

INSERT INTO `type_projets` (`idTypeProjet`, `type`) VALUES (NULL, 'Particulier');
