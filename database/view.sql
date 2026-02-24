CREATE OR REPLACE VIEW v_formateur_cfps AS
SELECT 
	formateurs.form_titre, 
	formateurs.form_speciality, 
	project_forms.idProjet,
	cfp_formateurs.idCfp,
	cfp_formateurs.idFormateur,
	cfp_formateurs.isActiveFormateur,
	cfp_formateurs.isActiveCfp,
	users.photo AS photoForm,
	users.name,
	users.firstName,
	users.phone AS form_phone,
	users.email,
	role_users.isActive,
	SUBSTRING(users.name, 1, 1) AS initialNameForm,
	users.user_addr_lot AS form_addr_lot,
	users.user_addr_quartier AS form_addr_qrt,
	users.user_addr_rue AS form_addr_rue,
	users.user_addr_code_postal AS form_addr_cp,
	intras.idEtp,
	inter_entreprises.idEtp as idEtp_inter,  
	inters.idCfp as idCfp_inter,
	type_projets.type AS project_type,
	projets.idVilleCoded,
    ville_codeds.idVille,
	villes.ville,
	mdls.idModule,
	projets.dateDebut,
	projets.dateFin,
		CASE
			WHEN (projets.project_is_archived = 1) THEN "Archivé"
			WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
			WHEN (projets.project_is_closed = 1) THEN "Cloturé"
			WHEN (projets.project_is_active = 1
									AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
			WHEN (projets.project_is_active = 0
									AND projets.project_is_cancelled = 0
									AND projets.project_is_repported = 0
									AND projets.project_is_reserved = 0
									AND projets.project_is_archived = 0
									AND projets.project_is_trashed = 0) THEN "En préparation"
			WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
			WHEN (projets.project_is_repported = 1) THEN "Reporté"
			WHEN (projets.project_is_reserved = 1) THEN "Réservé"
			WHEN (projets.project_is_active = 1
									AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
			ELSE "En cours"
		END project_status,
	CASE
		WHEN(projets.dateDebut < CURRENT_DATE AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)	AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month"
		WHEN(projets.dateDebut >= CURRENT_DATE AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)	AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
		END p_id_periode,
		DATE_FORMAT(dateDebut, "%M, %Y") AS headDate,
		DATE_FORMAT(dateDebut, "%b") AS headMonthDebut,
		DATE_FORMAT(dateFin, "%b") AS headMonthFin,
		DATE_FORMAT(dateDebut, "%Y") AS headYear,
		DATE_FORMAT(dateDebut, "%d") AS headDayDebut,
		DATE_FORMAT(dateFin, "%d") AS headDayFin
FROM cfp_formateurs
INNER JOIN cfps ON cfp_formateurs.idCfp = cfps.idCustomer
INNER JOIN formateurs ON cfp_formateurs.idFormateur = formateurs.idFormateur
INNER JOIN forms ON formateurs.idFormateur = forms.idFormateur
INNER JOIN users ON forms.idFormateur = users.id
INNER JOIN role_users ON role_users.user_id = users.id
LEFT JOIN project_forms ON cfp_formateurs.idFormateur = project_forms.idFormateur
LEFT JOIN projets  ON  project_forms.idProjet = projets.idProjet 
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN inter_entreprises ON inter_entreprises.idProjet = projets.idProjet
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN ville_codeds ON projets.idVilleCoded = ville_codeds.id
LEFT JOIN villes ON ville_codeds.idVille = villes.idVille
WHERE role_users.role_id = 5;

-- New view ok(ETP)--

CREATE OR REPLACE VIEW v_formateur_internes AS
SELECT 
	 formateurs.form_titre, 
	 formateurs.form_speciality, 
	project_forms.idProjet,
	employes.idEmploye,
	users.photo AS photoForm,
	users.name,
	users.firstName,
	users.phone AS form_phone,
	formateur_internes.idEntreprise,
	customers.customerName,
	customers.customer_addr_quartier,
	users.email,
	role_users.isActive,
	SUBSTRING(users.name, 1, 1) AS initialNameForm,
	users.user_addr_lot AS form_addr_lot,
	users.user_addr_quartier AS form_addr_qrt,
	users.user_addr_rue AS form_addr_rue,
	users.user_addr_code_postal AS form_addr_cp,
	type_projets.type AS project_type,
    projets.idVilleCoded,
	vc.idVille,
	villes.ville,
	mdls.idModule,
	projets.dateDebut,
	projets.dateFin,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	CASE
		WHEN(projets.dateDebut < CURRENT_DATE AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)	AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month"
		WHEN(projets.dateDebut >= CURRENT_DATE AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)	AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
		END p_id_periode,
		DATE_FORMAT(dateDebut, "%M, %Y") AS headDate,
		DATE_FORMAT(dateDebut, "%b") AS headMonthDebut,
		DATE_FORMAT(dateFin, "%b") AS headMonthFin,
		DATE_FORMAT(dateDebut, "%Y") AS headYear,
		DATE_FORMAT(dateDebut, "%d") AS headDayDebut,
		DATE_FORMAT(dateFin, "%d") AS headDayFin
FROM formateur_internes
INNER JOIN employes ON formateur_internes.idEmploye = employes.idEmploye
INNER JOIN users ON employes.idEmploye = users.id
INNER JOIN forms ON users.id = forms.idFormateur
INNER JOIN entreprises ON formateur_internes.idEntreprise = entreprises.idCustomer
INNER JOIN customers ON customers.idCustomer = entreprises.idCustomer
INNER JOIN role_users ON role_users.user_id = users.id
LEFT JOIN project_forms ON employes.idEmploye = project_forms.idFormateur
LEFT JOIN projets  ON  project_forms.idProjet = projets.idProjet
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
LEFT JOIN villes ON vc.idVille = villes.idVille
LEFT JOIN formateurs ON formateur_internes.idEmploye = formateurs.idFormateur;


CREATE OR REPLACE VIEW v_union_formateurs AS 
SELECT 
	form_titre, 
	form_speciality, 
	idProjet,
	idCfp,
	idFormateur,
	isActiveFormateur,
	isActiveCfp,
	photoForm,
	name,
	firstName,
	form_phone,
	email,
	isActive,
	initialNameForm,
	form_addr_lot,
	form_addr_qrt,
	form_addr_rue,
	form_addr_cp,
	idEtp,
	idEtp_inter,  
	idCfp_inter,
	project_type,
	idVilleCoded,
  idVille,
	ville,
	idModule,
	dateDebut,
	dateFin,
  project_status,
	p_id_periode,
	headDate,
	headMonthDebut,
	headMonthFin,
	headYear,
	headDayDebut,
	headDayFin
FROM v_formateur_cfps 
UNION
SELECT 
	form_titre, 
	form_speciality, 
	idProjet,
	null AS idCfp,
	idEmploye AS idFormateur,
	null AS isActiveFormateur,
	null AS isActiveCfp,
	photoForm,
	name,
	firstName,
	form_phone,
	email,
	isActive,
	initialNameForm,
	form_addr_lot,
	form_addr_qrt,
	form_addr_rue,
	form_addr_cp,
	idEntreprise AS idEtp ,
	null AS idEtp_inter,  
	null AS idCfp_inter,
	project_type,
	idVilleCoded,
  idVille,
	ville,
	idModule,
	dateDebut,
	dateFin,
  project_status,
	p_id_periode,
	headDate,
	headMonthDebut,
	headMonthFin,
	headYear,
	headDayDebut,
	headDayFin
FROM v_formateur_internes;

CREATE OR REPLACE VIEW v_module_cfps AS
SELECT mdls.idCustomer,
	mdls.idModule,
	mdls.module_image,
	mdls.reference,
	mdls.moduleName,
	mdls.module_tag,
	mdls.module_subtitle,
	mdls.description,
	mdls.minApprenant,
	mdls.maxApprenant,
	mdls.dureeJ,
	mdls.dureeH,
	mdls.moduleStatut,
	mdls.idTypeModule,
	mdls.module_is_complete,
	modules.prix,
	modules.prixGroupe,
	SUBSTRING(customers.customerName, 1, 1) AS initialName,
	customers.customerName AS cfpName,
	customers.logo,
	mdls.idDomaine,
	domaine_formations.nomDomaine,
    mdls.idLevel, module_levels.module_level_name
FROM `mdls`
INNER JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
INNER JOIN modules ON modules.idModule = mdls.idModule
JOIN customers ON mdls.idCustomer = customers.idCustomer
INNER JOIN module_levels ON mdls.idLevel = module_levels.idLevel;

-- New view (ETP) seulement interne idTypeprojet = 3

CREATE OR REPLACE VIEW v_projet_etps AS
SELECT projets.idProjet,
	projets.referenceEtp,
	projets.idTypeprojet,
	projets.projectName AS project_name,
	projets.dateDebut,
	projets.dateFin,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.project_is_reserved,
	projets.project_is_active,
	projets.project_is_trashed,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	internes.idEtp,
	inters.idCfp as cfp_inter,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name,
	customers.customerName AS etp_name,
	customers.logo AS etp_logo,
	customers.description AS etp_description,
	type_projets.type AS project_type,
	projets.idCustomer,
	modalites.modalite,
	projets.idVilleCoded,
    vc.idVille,
	villes.ville,
	projets.idModule,
	mdls.moduleName AS module_name,
	mdls.description AS module_description,
	mdls.module_image,
	mdls.idDomaine,
	domaine_formations.nomDomaine AS domaine_name,
	projets.idSalle,
	salles.salle_name,
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
	vc.vi_code_postal  AS salle_code_postal,
    vc.ville_name AS ville_name_coded
FROM projets
LEFT JOIN internes ON internes.idProjet = projets.idProjet
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN entreprises ON internes.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
LEFT JOIN villes ON vc.idVille = villes.idVille
LEFT JOIN lieux ON salles.idLieu = lieux.idLieu
WHERE projets.idTypeprojet = 3
AND projets.dateDebut IS NOT NULL;

-- New view (ETP)

CREATE OR REPLACE VIEW v_module_etps AS
SELECT mdls.idCustomer,
	mdls.idModule,
	mdls.module_image,
	mdls.reference,
	mdls.moduleName,
	mdls.module_tag,
	mdls.module_subtitle,
	mdls.description,
	mdls.minApprenant,
	mdls.maxApprenant,
	mdls.dureeJ,
	mdls.dureeH,
	mdls.moduleStatut,
	mdls.idTypeModule,
	mdls.module_is_complete,
	SUBSTRING(customers.customerName, 1, 1) AS initialName,
	customers.customerName AS cfpName,
	customers.logo,
	mdls.idDomaine,
	domaine_formations.nomDomaine
FROM `mdls`
INNER JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
INNER JOIN module_internes ON module_internes.idModule = mdls.idModule
JOIN customers ON mdls.idCustomer = customers.idCustomer;

-- apprenant fa tsy employes (etp)

CREATE OR REPLACE VIEW v_list_apprenants AS
SELECT detail_apprenants.idEmploye,
	detail_apprenants.idProjet,
	employes.idCustomer AS idEtp,
	SUBSTRING(users.name, 1, 1) AS emp_initial_name,
	users.name AS emp_name,
	users.firstName AS emp_firstname,
	users.matricule AS emp_matricule,
	users.dateNais AS emp_date_nais,
	users.email AS emp_email,
	users.cin AS emp_cin,
	users.phone AS emp_phone,
	users.adresse AS emp_adresse,
	users.photo AS emp_photo,
	fonctions.fonction AS emp_fonction,
	customers.customerName AS etp_name,
	customers.customerEmail AS etp_email
FROM detail_apprenants
INNER JOIN apprenants ON detail_apprenants.idEmploye = apprenants.idEmploye
INNER JOIN employes ON apprenants.idEmploye = employes.idEmploye
INNER JOIN users ON employes.idEmploye = users.id
INNER JOIN projets ON detail_apprenants.idProjet = projets.idProjet
INNER JOIN customers ON employes.idCustomer = customers.idCustomer
INNER JOIN fonctions ON employes.idFonction = fonctions.idFonction;

-- rehefa mampiasa table roa mitovy dia mapiasa alias

CREATE OR REPLACE VIEW v_collaboration_cfp_etps AS
SELECT SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name,
	customers.customerName AS etp_name,
	customers.logo AS etp_logo,
	customers.description AS etp_description,
	customers.customer_addr_quartier AS etp_addr_quartier,
	customers.customer_addr_rue AS etp_addr_rue,
	customers.customer_addr_lot AS etp_addr_lot,
    vc.ville_name AS etp_ville,
    vc.vi_code_postal AS etp_addr_code_postal,
	customers.nif AS etp_nif,
	customers.stat AS etp_stat,
	customers.rcs AS etp_rcs,
	customers.customerPhone AS etp_phone,
	customers.siteWeb AS etp_site_web,
	cfp_etps.idEtp,
	cfp_etps.idCfp,
	cfp_etps.activiteCfp,
	cfp_etps.activiteEtp,
	cfp_etps.isSent,
	users.email AS etp_referent_email,
	customers.customerEmail AS etp_email,
	cfp_etps.dateCollaboration AS dateInvitation,
	users.name AS etp_referent_name,
	users.firstName As etp_referent_firstname,
	users.phone AS etp_referent_phone,
	fonctions.fonction AS etp_referent_fonction,
    entreprises.idTypeEtp,
    te.type_etp_desc,
    SUBSTRING(cm.customerName, 1, 1) AS cfp_initial_name,
    cm.customerName AS cfp_name,
    cm.nif AS cfp_nif,
    cm.stat AS cfp_stat,
    cm.rcs AS cfp_rcs,
    cm.description AS cfp_description,
    cm.siteWeb AS cfp_siteweb,
    cm.logo AS cfp_logo,
    cm.customerEmail AS cfp_email,
   	cm.customerPhone AS cfp_phone,
    cm.customer_slogan AS cfp_slogan,
    u.name AS cfp_referent_name,
	u.firstName As cfp_referent_firstname,
	u.phone AS cfp_referent_phone,
    u.email AS cfp_referent_email
FROM cfp_etps
LEFT JOIN cfps ON cfp_etps.idCfp = cfps.idCustomer
LEFT JOIN entreprises ON cfp_etps.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN customers AS cm ON cfps.idCustomer = cm.idCustomer
LEFT JOIN users ON customers.idCustomer = users.id
LEFT JOIN users AS u ON cm.idCustomer = u.id
LEFT JOIN employes ON users.id = employes.idEmploye
LEFT JOIN fonctions ON employes.idFonction = fonctions.idFonction
LEFT JOIN ville_codeds AS vc ON customers.idVilleCoded = vc.id
LEFT JOIN type_entreprises AS te ON entreprises.idTypeEtp = te.idTypeEtp;

-- ilay "project_inter_privacy => 1: public, 0: privé"
-- izay projet tsy manana date dia tsy tafiditra ato
CREATE OR REPLACE VIEW v_projet_cfps AS
SELECT projets.idProjet,
    projets.idBc,
    projets.referenceEtp,
    projets.projectName AS project_name,
    projets.dateDebut,
    projets.dateFin,
	projets.taxe,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.project_is_reserved,
	projets.project_is_active,
	projets.project_is_trashed,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	projets.project_price_pedagogique,
	projets.project_price_annexe,
	projets.total_ht,
	projets.total_ttc,
	projets.total_ht_sub_contractor,
	projets.total_ttc_sub_contractor,
	projets.idDossier,
	projets.link,
	projets.secret_code,
	intras.idEtp,
	intras.idCfp,
	inters.idCfp as idCfp_inter,
	inters.project_inter_privacy,
	customers.customerEmail AS etp_email,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name,
	customers.customerName AS etp_name,
	customers.logo AS etp_logo,
	customers.description AS etp_description,
	type_projets.type AS project_type,
	projets.idCustomer,
	modalites.idModalite,
	modalites.modalite,
	ville_codeds.idVille,
	ville_codeds.ville_name AS ville,
	projets.idModule,
	l.idLieu,
	l.li_name,
	mdls.moduleName AS module_name,
	mdls.description AS module_description,
	mdls.module_image,
	paiements.idPaiement AS idPaiement,
	paiements.paiement,
	mdls.idDomaine,
	domaine_formations.nomDomaine AS domaine_name,
	projets.idSalle,
	salles.salle_name,
	l.li_quartier AS salle_quartier,
	l.li_rue AS salle_rue,
	salles.salle_image,
	ville_codeds.vi_code_postal AS salle_code_postal,
CASE
	WHEN(projets.dateDebut < CURRENT_DATE AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month"
	WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month"
	WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)	AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month"
	WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month"
	WHEN(projets.dateDebut >= CURRENT_DATE AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month"
	WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month"
	WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)	AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month"
	WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
	END p_id_periode,
	DATE_FORMAT(dateDebut, "%M, %Y") AS headDate,
	DATE_FORMAT(dateDebut, "%b") AS headMonthDebut,
	DATE_FORMAT(dateFin, "%b") AS headMonthFin,
	DATE_FORMAT(dateDebut, "%Y") AS headYear,
	DATE_FORMAT(dateDebut, "%d") AS headDayDebut,
	DATE_FORMAT(dateFin, "%d") AS headDayFin, psc.idSubContractor, SUBSTRING(sub.customerName, 1, 1) AS sub_initial_name, sub.customerName AS sub_name, sub.nif AS sub_nif, sub.stat AS sub_stat, sub.rcs AS sub_rsc, sub.description AS sub_description, sub.logo AS sub_logo, sub.customerEmail AS sub_email, sub.customerPhone AS sub_phone, ce.customerName AS cfp_name,
	bon_commandes.numero as numero_bc
FROM projets
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN paiements ON intras.idPaiement = paiements.idPaiement OR inters.idPaiement = paiements.idPaiement
LEFT JOIN entreprises ON intras.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN project_sub_contracts AS psc ON projets.idProjet = psc.idProjet
LEFT JOIN customers AS sub ON psc.idSubContractor = sub.idCustomer
LEFT JOIN customers AS ce ON projets.idCustomer = ce.idCustomer
LEFT JOIN lieux AS l ON salles.idLieu = l.idLieu
LEFT JOIN ville_codeds ON l.idVilleCoded = ville_codeds.id
LEFT JOIN villes ON ville_codeds.idVille = villes.idVille
LEFT JOIN bon_commandes ON projets.idBC = bon_commandes.idBc
WHERE projets.dateDebut IS NOT NULL;


CREATE OR REPLACE VIEW v_projet_emps AS
SELECT projets.idProjet,
    projets.referenceEtp,
    projets.projectName AS project_name,
    projets.dateDebut,
    projets.dateFin,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.project_is_reserved,
	projets.project_is_active,
	projets.project_is_trashed,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	projets.project_price_pedagogique,
	projets.project_price_annexe,
	projets.total_ht,
	projets.total_ttc,
	projets.idDossier,
	intras.idEtp,
	intras.idCfp,
	inters.idCfp as idCfp_inter,
	inters.project_inter_privacy,
	customers.customerEmail AS etp_email,
	apprenants.idEmploye,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name,
	customers.customerName AS etp_name,
	customers.logo AS etp_logo,
	customers.description AS etp_description,
	type_projets.type AS project_type,
	projets.idCustomer,
	modalites.idModalite,
	modalites.modalite,
	projets.idVilleCoded,
    vc.idVille,
	villes.ville,
	projets.idModule,
	mdls.moduleName AS module_name,
	mdls.description AS module_description,
	mdls.module_image,
	mdls.moduleStatut,
	CASE
		WHEN inters.idCfp IS NULL THEN intras.idPaiement
		WHEN inters.idCfp = 2 THEN inters.idPaiement
	END AS idPaiement,
	paiements.paiement,
	mdls.idDomaine,
	domaine_formations.nomDomaine AS domaine_name,
	projets.idSalle,
	salles.salle_name,
	vc.vi_code_postal AS salle_code_postal,
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
CASE
	WHEN(projets.dateDebut < CURRENT_DATE AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month"
	WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month"
	WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)	AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month"
	WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month"
	WHEN(projets.dateDebut >= CURRENT_DATE AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month"
	WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month"
	WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)	AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month"
	WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
	END p_id_periode,
	DATE_FORMAT(dateDebut, "%M, %Y") AS headDate,
	DATE_FORMAT(dateDebut, "%b") AS headMonthDebut,
	DATE_FORMAT(dateFin, "%b") AS headMonthFin,
	DATE_FORMAT(dateDebut, "%Y") AS headYear,
	DATE_FORMAT(dateDebut, "%d") AS headDayDebut,
	DATE_FORMAT(dateFin, "%d") AS headDayFin, psc.idSubContractor, SUBSTRING(sub.customerName, 1, 1) AS sub_initial_name, sub.customerName AS sub_name, sub.nif AS sub_nif, sub.stat AS sub_stat, sub.rcs AS sub_rsc, sub.description AS sub_description, sub.logo AS sub_logo, sub.customerEmail AS sub_email, sub.customerPhone AS sub_phone, ce.customerName AS cfp_name
FROM projets
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN paiements ON intras.idPaiement = paiements.idPaiement OR inters.idPaiement = paiements.idPaiement
LEFT JOIN entreprises ON intras.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
LEFT JOIN villes ON vc.idVille = villes.idVille
LEFT JOIN project_sub_contracts AS psc ON projets.idProjet = psc.idProjet
LEFT JOIN customers AS sub ON psc.idSubContractor = sub.idCustomer
LEFT JOIN customers AS ce ON projets.idCustomer = ce.idCustomer
LEFT JOIN detail_apprenants ON detail_apprenants.idProjet = projets.idProjet
LEFT JOIN lieux ON salles.idLieu = lieux.idLieu
LEFT JOIN apprenants ON apprenants.idEmploye = detail_apprenants.idEmploye
WHERE projets.dateDebut IS NOT NULL;


CREATE OR REPLACE VIEW v_materiel_cfps AS
SELECT materiel_cfps.idMateriel,
	materiel_cfps.codeMateriel,
	materiel_cfps.nomMateriel,
	materiel_cfps.descriptionMateriel,
	materiel_cfps.idCfp,
	customers.customerName,
	fournisseurs.nomFournisseur,
	fournisseurs.idTypefournisseur,
	type_materiels.typeMateriel
FROM customers
INNER JOIN cfps ON cfps.idCustomer = customers.idCustomer
INNER JOIN materiel_cfps ON materiel_cfps.idCfp = cfps.idCustomer
INNER JOIN type_materiels ON materiel_cfps.idTypeMateriel = type_materiels.idTypeMateriel
LEFT JOIN materiel_externes ON materiel_externes.idMateriel = materiel_cfps.idMateriel
LEFT JOIN fournisseurs ON materiel_externes.idFournisseur = fournisseurs.idFournisseur;

-- WHERE fournisseurs.idTypefournisseur = 1;
 -- v_employes pour cfp et etp

CREATE OR REPLACE VIEW v_employe_alls AS
SELECT employes.idEmploye,
	employes.idCustomer,
	customers.customerName,
	users.matricule,
	SUBSTRING(users.name, 1, 1) AS initialName,
	users.name,
	users.firstName,
	users.email,
	users.cin,
	users.dateNais,
	users.phone,
	users.photo,
	users.adresse,
	customers.customer_addr_quartier,
	customers.customer_addr_rue,
	customers.customer_addr_lot,
    villes.ville AS customer_ville,
	customers.customerEmail,
	customers.customerPhone,
	fonctions.fonction,
	niveau_etudes.niveau AS niveauEtude,
	employes.idSexe,
	IF(sexes.sexe = "Masculin", "M", "F") AS sexe,
	role_users.role_id,
	role_users.isActive,
	role_users.hasRole,
    role_users.user_is_in_service,
	users.user_addr_quartier,
	users.user_addr_lot,
	users.user_addr_rue,
	users.user_addr_code_postal,
    ville_codeds.vi_code_postal AS customer_addr_code_postal,
    ville_codeds.ville_name,
    ville_codeds.idVille
FROM employes
INNER JOIN niveau_etudes ON employes.idNiveau = niveau_etudes.idNiveau
INNER JOIN customers ON employes.idCustomer = customers.idCustomer
INNER JOIN sexes ON employes.idSexe = sexes.idSexe
INNER JOIN users ON employes.idEmploye = users.id
INNER JOIN role_users ON role_users.user_id = users.id
INNER JOIN fonctions ON employes.idFonction = fonctions.idFonction
INNER JOIN ville_codeds ON customers.idVilleCoded = ville_codeds.id
INNER JOIN villes ON ville_codeds.idVille = villes.idVille
ORDER BY employes.idEmploye ASC;

-- employes etp ao anaty cfp
-- ao @ apprenant ao @ cfp ihany ito no afaka ampiasaina

CREATE OR REPLACE VIEW v_apprenant_etp_alls AS
SELECT users.id AS idEmploye,
	cfp_etps.idEtp,
	cfp_etps.idCfp,
	cfp_etps.activiteEtp AS etp_is_active,
	cfp_etps.activiteCfp AS cfp_is_active,
	etp.customerName AS etp_name,
	etp.customerEmail AS etp_email,
	etp.idTypeCustomer,
	SUBSTRING(users.name, 1, 1) AS emp_initial_name,
	users.name AS emp_name,
	users.firstName AS emp_firstname,
	users.email AS emp_email,
	users.matricule AS emp_matricule,
	users.phone AS emp_phone,
	users.cin AS emp_cin,
	users.photo AS emp_photo,
	employes.idFonction,
	fonctions.fonction AS emp_fonction,
	users.user_addr_lot,
	users.user_addr_quartier,
	users.user_addr_rue,
	users.user_addr_code_postal,
	role_users.user_is_in_service,
	role_users.role_id,
	role_users.isActive AS emp_is_active,
	role_users.hasRole,
	c_emps.id_cfp,
	users.idVille,
	villes.ville,
	da.idProjet,
    da.id_cfp_appr,
	vp.dateDebut,
	vp.project_type,
	vp.dateFin,
	vp.idSalle,
	vp.salle_name,
	vp.salle_quartier,
	vp.salle_rue,
	vp.salle_code_postal,
	vp.idVille AS project_id_ville,
	vp.ville AS project_ville,
	vp.project_status,
	vp.modalite AS project_modality,
	vp.idModule,
	vp.module_name,
	MONTH(vp.dateDebut) AS project_month,
	MONTH(CURDATE()) AS current_month,
	ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AS p_prev_three_month,
	ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AS p_prev_six_month,
	ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AS p_prev_twelve_month,
	ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH) AS p_prev_tw_four_month,
	ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AS p_next_three_month,
	ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AS p_next_six_month,
	ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AS p_next_twelve_month,
	ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH) AS p_next_tw_four_month,
	CASE WHEN(vp.dateDebut < CURRENT_DATE AND vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month" WHEN(vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month" WHEN(vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AND vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month" WHEN(vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month" WHEN(vp.dateDebut >= CURRENT_DATE AND vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month" WHEN(vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)	AND vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month" WHEN(vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AND vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month" WHEN(vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
	END p_id_periode
FROM cfp_etps
INNER JOIN customers AS etp ON cfp_etps.idEtp = etp.idCustomer
INNER JOIN employes ON employes.idCustomer = etp.idCustomer
INNER JOIN users ON employes.idEmploye = users.id
INNER JOIN role_users ON role_users.user_id = users.id
INNER JOIN villes ON users.idVille = villes.idVille
INNER JOIN fonctions ON employes.idFonction = fonctions.idFonction
LEFT JOIN detail_apprenants AS da ON da.idEmploye = employes.idEmploye
LEFT JOIN v_projet_cfps AS vp ON vp.idProjet = da.idProjet
LEFT JOIN c_emps ON employes.idEmploye = c_emps.idEmploye
-- WHERE cfp_etps.idCfp = id_cfp
AND role_users.role_id = 4;

CREATE OR REPLACE VIEW v_apprenant_etp AS
SELECT users.id AS idEmploye,
	etp.idCustomer AS idEtp,
	etp.customerName AS etp_name,
	etp.customerEmail AS etp_email,
	etp.idTypeCustomer,
	SUBSTRING(users.name, 1, 1) AS emp_initial_name,
	users.name AS emp_name,
	users.firstName AS emp_firstname,
	users.email AS emp_email,
	users.matricule AS emp_matricule,
	users.phone AS emp_phone,
	users.cin AS emp_cin,
	users.photo AS emp_photo,
	employes.idFonction,
	fonctions.fonction AS emp_fonction,
	users.user_addr_lot,
	users.user_addr_quartier,
	users.user_addr_rue,
	users.user_addr_code_postal,
	role_users.role_id,
	role_users.isActive AS emp_is_active,
	role_users.hasRole,
    role_users.user_is_in_service,
	users.idVille,
	villes.ville
FROM employes
INNER JOIN customers AS etp ON employes.idCustomer = etp.idCustomer
INNER JOIN users ON employes.idEmploye = users.id
INNER JOIN role_users ON role_users.user_id = users.id
INNER JOIN villes ON users.idVille = villes.idVille
INNER JOIN fonctions ON employes.idFonction = fonctions.idFonction
WHERE role_users.role_id = 4;

CREATE OR REPLACE VIEW v_apprenant_etp_all_filters AS SELECT
	va.idEmploye, va.idEtp, va.idCfp, va.id_cfp, va.etp_name, va.emp_name, va.emp_firstname, va.idFonction, va.emp_fonction
FROM v_apprenant_etp_alls AS va
WHERE va.role_id = 4
GROUP BY va.idEmploye, va.idEtp, va.idCfp, va.id_cfp, va.etp_name, va.emp_name, va.emp_firstname, va.idFonction, va.emp_fonction
ORDER BY va.idEmploye ASC;

CREATE OR REPLACE VIEW v_periodes AS SELECT
	va.idEmploye, va.idEtp, va.idCfp, va.id_cfp, va.etp_name, va.emp_name, va.emp_firstname, va.idFonction, va.emp_fonction, va.p_id_periode, va.project_id_ville, va.project_ville
FROM v_apprenant_etp_alls AS va
WHERE va.role_id = 4
AND va.p_id_periode != "null"
GROUP BY va.idEmploye, va.idEtp, va.idCfp, va.id_cfp, va.etp_name, va.emp_name, va.emp_firstname, va.idFonction, va.emp_fonction, va.p_id_periode, va.project_id_ville, va.ville
ORDER BY va.idEmploye ASC;

CREATE OR REPLACE VIEW v_detail_customers AS
SELECT idCustomer,
	SUBSTRING(c.customerName, 1, 1) AS initialName,
	c.customerName,
	c.customer_addr_quartier,
	c.customer_addr_rue,
	c.customer_addr_lot,
    villes.ville AS customer_ville,
	nif,
	stat,
	assujetti,
	customerPhone,
	rcs,
	description,
	siteWeb,
	logo,
	customerEmail,
	c.customer_slogan,
    ru.role_id,
    ru.isActive AS user_is_active,
    ru.hasRole AS user_has_role,
    ru.user_is_in_service,
    vc.ville_name,
    vc.vi_code_postal AS customer_addr_code_postal,
    vc.idVille,
    c.idVilleCoded
FROM customers AS c
INNER JOIN users ON c.idCustomer = users.id
INNER JOIN role_users AS ru ON ru.user_id = users.id
INNER JOIN ville_codeds AS vc ON c.idVilleCoded = vc.id
INNER JOIN villes ON vc.idVille = villes.idVille;

-- seances pour CFP

CREATE OR REPLACE VIEW v_seances AS
SELECT seances.idSeance,
	seances.id_google_seance,
	DATE_FORMAT(seances.dateSeance, '%a %d %b %y') AS dateSeanceEmar,
	seances.dateSeance,
	seances.session_catch_up,
	seances.is_reported,
	seances.heure_debut_reportee,
	seances.heure_fin_reportee,
	seances.is_report_undetermined,
	seances.report_date,
	seances.heureDebut,
	seances.heureFin,
	seances.idProjet,
	TIMEDIFF(seances.heureFin,seances.heureDebut) AS intervalle_raw,
	projets.idSalle,
	salles.salle_name,
	l.li_quartier AS salle_quartier,
	l.li_rue AS salle_rue,
	ville_codeds.vi_code_postal AS salle_code_postal,
	l.idLieu,
	l.li_name,
	projets.idCustomer AS idCfp,
	projets.idTypeProjet,
	projets.idDossier,
	projets.referenceEtp AS project_reference_etp,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.projectName AS project_name,
	projets.project_is_active,
	projets.project_is_reserved,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_trashed,
	projets.dateDebut AS project_date_debut,
	projets.dateFin AS project_date_fin,
	projets.idVilleCoded,
    ville_codeds.idVille,
	villes.ville,
	projets.idModule,
	modalites.modalite,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	mdls.moduleName AS module_name,
	mdls.module_subtitle,
    mdls.module_image,
    intras.idEtp,
    cste.customerName AS etp_name,
    cste.customerEmail AS etp_email, 
    cste.logo AS etp_logo
FROM seances
INNER JOIN projets ON seances.idProjet = projets.idProjet
INNER JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN ville_codeds ON projets.idVilleCoded = ville_codeds.id
INNER JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN villes ON ville_codeds.idVille = villes.idVille
LEFT JOIN intras ON projets.idProjet = intras.idProjet
LEFT JOIN customers AS cste ON intras.idEtp = cste.idCustomer
LEFT JOIN lieux AS l ON salles.idLieu = l.idLieu
WHERE projets.dateDebut IS NOT NULL
ORDER BY seances.dateSeance;

-- phase UNION projet(inter,intra,interne)

CREATE OR REPLACE VIEW v_projet_internes AS
SELECT projets.idProjet,
	projets.referenceEtp,
	projets.idTypeprojet,
	projets.projectName AS project_name,
	projets.dateDebut,
	projets.dateFin,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.project_is_reserved,
	projets.project_is_active,
	projets.project_is_trashed,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	projets.total_ht,
	projets.idDossier,
  projets.total_ttc,
	projets.total_ht_etp,
	projets.total_ttc_etp,
	projets.total_ht_sub_contractor,
	internes.idEtp,
	intras.idEtp as idEtp_intra, 
	intras.idCfp,
	inters.idCfp as idCfp_inter,
	inter_entreprises.idEtp as idEtp_inter,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name,
	customers.customerName AS etp_name,
	customers.logo AS etp_logo,
	customers.description AS etp_description,
	type_projets.type AS project_type,
	projets.idCustomer,
	modalites.idModalite,
	modalites.modalite,
    projets.idVilleCoded,
	vc.idVille,
	villes.ville,
	projets.idModule,
	mdls.moduleName AS module_name,
	mdls.description AS module_description,
	mdls.module_image,
	mdls.idDomaine,
	domaine_formations.nomDomaine AS domaine_name,
	projets.idSalle,
	salles.salle_name, 
	lieux.idLieu,
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
	vc.vi_code_postal AS salle_code_postal,
	CASE WHEN(projets.dateDebut < CURRENT_DATE	AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month" WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month" WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)	AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month" WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month" WHEN(projets.dateDebut >= CURRENT_DATE AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month" WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month" WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month" WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
	END p_id_periode,
	DATE_FORMAT(dateDebut, "%M, %Y") AS headDate,
	DATE_FORMAT(dateDebut, "%b") AS headMonthDebut,
	DATE_FORMAT(dateFin, "%b") AS headMonthFin,
	DATE_FORMAT(dateDebut, "%Y") AS headYear,
	DATE_FORMAT(dateDebut, "%d") AS headDayDebut,
	DATE_FORMAT(dateFin, "%d") AS headDayFin
FROM projets
LEFT JOIN internes ON internes.idProjet = projets.idProjet
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN inter_entreprises ON inter_entreprises.idProjet = projets.idProjet
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN entreprises ON internes.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
LEFT JOIN villes ON vc.idVille = villes.idVille
LEFT JOIN lieux ON salles.idLieu = lieux.idLieu
WHERE projets.idTypeprojet = 3
AND projets.dateDebut IS NOT NULL;

-- projet intra
CREATE OR REPLACE VIEW v_projet_intras AS SELECT
    projets.idProjet, 
	projets.referenceEtp,
	projets.idTypeprojet, 
	projets.projectName AS project_name, 
	projets.dateDebut,
	projets.dateFin, 
	projets.project_reference, 
	projets.project_title, 
	projets.project_description, 
	projets.project_is_reserved, 
	projets.project_is_active, 
	projets.project_is_trashed, 
	projets.project_is_cancelled,
	projets.project_is_repported, 
	projets.project_is_closed, 
	projets.project_price_pedagogique, 
	projets.project_price_annexe,
	projets.total_ht,
  projets.total_ttc,
	projets.total_ht_etp,
	projets.total_ttc_etp,
	projets.total_ht_sub_contractor,
	projets.idDossier,
	intras.idEtp,
	paiements.paiement,
	CASE
		WHEN inters.idCfp IS NULL THEN intras.idPaiement
		WHEN inters.idCfp = 2 THEN inters.idPaiement
	END AS idPaiement,
    inter_entreprises.idEtp as idEtp_inter,	
	intras.idCfp as idCfp_intra, 
	inters.idCfp as idCfp_inter,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
     SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name, customers.customerName AS etp_name, customers.logo AS etp_logo, customers.description AS etp_description, type_projets.type AS project_type, projets.idCustomer, 	modalites.idModalite,
		 modalites.modalite, projets.idVilleCoded, vc.idVille, villes.ville, projets.idModule, mdls.moduleName AS module_name, mdls.description AS module_description, mdls.module_image, mdls.idDomaine, domaine_formations.nomDomaine AS domaine_name, projets.idSalle, salles.salle_name, 
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
		 vc.vi_code_postal AS salle_code_postal,
		 CASE 
		WHEN(projets.dateDebut < CURRENT_DATE AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH))
			THEN "prev_3_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH))
			THEN "prev_6_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH))
			THEN "prev_12_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH))
			THEN "prev_24_month"
		WHEN(projets.dateDebut >= CURRENT_DATE AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH))
			THEN "next_3_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH))
			THEN "next_6_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH))
			THEN "next_12_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH))
			THEN "next_24_month"
    END p_id_periode, DATE_FORMAT(dateDebut, "%M, %Y") AS headDate, DATE_FORMAT(dateDebut, "%b") AS headMonthDebut, DATE_FORMAT(dateFin, "%b") AS headMonthFin, DATE_FORMAT(dateDebut, "%Y") AS headYear, DATE_FORMAT(dateDebut, "%d") AS headDayDebut,
    DATE_FORMAT(dateFin, "%d") AS headDayFin
FROM projets 
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN internes ON internes.idProjet = projets.idProjet
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN inter_entreprises ON inter_entreprises.idProjet = projets.idProjet
LEFT JOIN paiements ON intras.idPaiement = paiements.idPaiement
LEFT JOIN entreprises ON intras.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
LEFT JOIN villes ON vc.idVille = villes.idVille
LEFT JOIN lieux ON salles.idLieu = lieux.idLieu
WHERE projets.idTypeprojet = 1
AND projets.dateDebut IS NOT NULL;


-- projet inter
CREATE OR REPLACE VIEW v_projet_inters AS SELECT
    projets.idProjet, 
	projets.referenceEtp,
	projets.idTypeprojet, 
	projets.projectName AS project_name, 
	projets.dateDebut, 
	projets.dateFin, 
	projets.project_reference, 
	projets.project_title, 
	projets.project_description, 
	projets.project_is_reserved, 
	projets.project_is_active, 
	projets.project_is_trashed, 
	projets.project_is_cancelled, 
	projets.project_is_repported, 
	projets.project_is_closed, 
	projets.project_price_pedagogique, 
	projets.project_price_annexe, 
	projets.total_ht,
	projets.total_ttc,
	projets.total_ht_etp,
	projets.total_ttc_etp,
	projets.total_ht_sub_contractor,
	projets.idDossier,
	intras.idEtp, 
	intras.idCfp as idCfp_intra, 
	inters.idCfp as idCfp_inter,
	inter_entreprises.idEtp as idEtp_inter,
	paiements.paiement,
	CASE
		WHEN inters.idCfp IS NULL THEN intras.idPaiement
		WHEN inters.idCfp = 2 THEN inters.idPaiement
	END AS idPaiement,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
     SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name, customers.customerName AS etp_name, customers.logo AS etp_logo, customers.description AS etp_description, type_projets.type AS project_type, projets.idCustomer,	modalites.idModalite,
		  modalites.modalite, projets.idVilleCoded, vc.idVille, villes.ville, projets.idModule, mdls.moduleName AS module_name, mdls.description AS module_description, mdls.module_image, mdls.idDomaine, domaine_formations.nomDomaine AS domaine_name, projets.idSalle, salles.salle_name, 
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
		  vc.vi_code_postal AS salle_code_postal,
		 CASE 
		WHEN(projets.dateDebut < CURRENT_DATE AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH))
			THEN "prev_3_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH))
			THEN "prev_6_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH))
			THEN "prev_12_month"
		WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH))
			THEN "prev_24_month"
		WHEN(projets.dateDebut >= CURRENT_DATE AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH))
			THEN "next_3_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH))
			THEN "next_6_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH))
			THEN "next_12_month"
		WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH))
			THEN "next_24_month"
    END p_id_periode, DATE_FORMAT(dateDebut, "%M, %Y") AS headDate, DATE_FORMAT(dateDebut, "%b") AS headMonthDebut, DATE_FORMAT(dateFin, "%b") AS headMonthFin, DATE_FORMAT(dateDebut, "%Y") AS headYear, DATE_FORMAT(dateDebut, "%d") AS headDayDebut, 
    DATE_FORMAT(dateFin, "%d") AS headDayFin
FROM projets 
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN paiements ON inters.idPaiement = paiements.idPaiement
LEFT JOIN inter_entreprises ON inter_entreprises.idProjet = projets.idProjet
LEFT JOIN entreprises ON intras.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
LEFT JOIN villes ON vc.idVille = villes.idVille
LEFT JOIN lieux ON salles.idLieu = lieux.idLieu
WHERE projets.idTypeprojet = 2
AND projets.dateDebut IS NOT NULL;

-- View Formateur
CREATE OR REPLACE VIEW v_projet_form AS
SELECT projets.idProjet,
    projets.referenceEtp,
    projets.projectName AS project_name,
    projets.dateDebut,
    projets.dateFin,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.project_is_reserved,
	projets.project_is_active,
	projets.project_is_trashed,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	projets.project_price_pedagogique,
	projets.project_price_annexe,
	projets.total_ht,
	projets.total_ttc,
	projets.idDossier,
	projets.idTypeProjet,
	intras.idEtp,
	intras.idCfp,
	inters.idCfp as idCfp_inter,
	inters.project_inter_privacy,
	customers.customerEmail AS etp_email,
	formateurs.idFormateur,
	particuliers.idParticulier,
	mdls.moduleStatut,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name,
	customers.customerName AS etp_name,
	customers.logo AS etp_logo,
	customers.description AS etp_description,
	type_projets.type AS project_type,
	projets.idCustomer,
	modalites.idModalite,
	modalites.modalite,
    projets.idVilleCoded,
	vc.idVille,
	villes.ville,
	projets.idModule,
	mdls.moduleName AS module_name,
	mdls.description AS module_description,
	mdls.module_image,
	
	CASE
		WHEN inters.idCfp IS NULL THEN intras.idPaiement
		WHEN inters.idCfp = 2 THEN inters.idPaiement
	END AS idPaiement,
	paiements.paiement,
	mdls.idDomaine,
	domaine_formations.nomDomaine AS domaine_name,
	projets.idSalle,
	salles.salle_name,
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
	vc.vi_code_postal AS salle_code_postal,
CASE
	WHEN(projets.dateDebut < CURRENT_DATE AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month"
	WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month"
	WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)	AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month"
	WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month"
	WHEN(projets.dateDebut >= CURRENT_DATE AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month"
	WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month"
	WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)	AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month"
	WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
	END p_id_periode,
	DATE_FORMAT(dateDebut, "%M, %Y") AS headDate,
	DATE_FORMAT(dateDebut, "%b") AS headMonthDebut,
	DATE_FORMAT(dateFin, "%b") AS headMonthFin,
	DATE_FORMAT(dateDebut, "%Y") AS headYear,
	DATE_FORMAT(dateDebut, "%d") AS headDayDebut,
	DATE_FORMAT(dateFin, "%d") AS headDayFin, psc.idSubContractor, SUBSTRING(sub.customerName, 1, 1) AS sub_initial_name, sub.customerName AS sub_name, sub.nif AS sub_nif, sub.stat AS sub_stat, sub.rcs AS sub_rsc, sub.description AS sub_description, sub.logo AS sub_logo, sub.customerEmail AS sub_email, sub.customerPhone AS sub_phone, ce.customerName AS cfp_name
FROM projets
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN paiements ON intras.idPaiement = paiements.idPaiement OR inters.idPaiement = paiements.idPaiement
LEFT JOIN entreprises ON intras.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN project_sub_contracts AS psc ON projets.idProjet = psc.idProjet
LEFT JOIN customers AS sub ON psc.idSubContractor = sub.idCustomer
LEFT JOIN customers AS ce ON projets.idCustomer = ce.idCustomer
LEFT JOIN project_forms ON project_forms.idProjet = projets.idProjet
LEFT JOIN formateurs ON formateurs.idFormateur = project_forms.idFormateur
LEFT JOIN particulier_projet ON particulier_projet.idProjet = projets.idProjet
LEFT JOIN particuliers ON particuliers.idParticulier = particulier_projet.idParticulier
LEFT JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
LEFT JOIN lieux ON salles.idLieu = lieux.idLieu
LEFT JOIN villes ON vc.idVille = villes.idVille
WHERE projets.dateDebut IS NOT NULL;

-- collaboration etp cfp
CREATE OR REPLACE VIEW v_collaboration_etp_cfps AS
SELECT SUBSTRING(ce.customerName, 1, 1) AS etp_initial_name,
	ce.customerName AS etp_name,
	ce.logo AS etp_logo,
	ce.description AS etp_description,
	ce.customer_addr_quartier AS etp_addr_quartier,
	ce.customer_addr_rue AS etp_addr_rue,
	ce.customer_addr_lot AS etp_addr_lot,
    vc.ville_name AS etp_ville,
    vc.vi_code_postal AS etp_code_postal,
	ce.nif AS etp_nif,
	ce.stat AS etp_stat,
	ce.rcs AS etp_rcs,
	ce.customerPhone AS etp_phone,
	ce.siteWeb AS etp_site_web,
	cfp_etps.idEtp,
	cfp_etps.idCfp,
	cfp_etps.activiteCfp,
	cfp_etps.activiteEtp,
	cfp_etps.isSent,
	users.email AS etp_referent_email,
	customers.customerEmail AS etp_email,
	cfp_etps.dateCollaboration AS dateInvitation,
	users.name AS etp_referent_name,
	users.firstName As etp_referent_firstname,
	users.phone AS etp_referent_phone,
	fonctions.fonction AS etp_referent_fonction
FROM cfp_etps
LEFT JOIN cfps ON cfp_etps.idCfp = cfps.idCustomer
LEFT JOIN customers ON cfps.idCustomer = customers.idCustomer
LEFT JOIN customers AS ce ON cfps.idCustomer = ce.idCustomer
LEFT JOIN users ON customers.idCustomer = users.id
LEFT JOIN employes ON users.id = employes.idEmploye
LEFT JOIN fonctions ON employes.idFonction = fonctions.idFonction
LEFT JOIN ville_codeds AS vc ON ce.idVilleCoded = vc.id;

CREATE OR REPLACE VIEW v_emargement_appr AS
	SELECT se.idSeance,
	DATE_FORMAT(se.dateSeance, '%a %d %b %y') AS dateSeanceEmar,
	se.dateSeance,
	se.heureDebut,
	se.heureFin,
	se.idProjet,
	da.idEmploye,
	usr.name,
	usr.firstName,
	usr.photo,
    emar.isPresent
FROM seances AS se
INNER JOIN detail_apprenants AS da ON se.idProjet = da.idProjet
LEFT JOIN emargements AS emar ON se.idSeance = emar.idSeance AND da.idEmploye = emar.idEmploye AND se.idProjet = emar.idProjet
INNER JOIN users AS usr ON da.idEmploye = usr.id
ORDER BY da.idEmploye,
	se.dateSeance ASC;

CREATE OR REPLACE VIEW v_cfp_forms AS
SELECT cfp_formateurs.idFormateur,
	customers.customerName,
	customers.idCustomer,
	customers.customerEmail,
	customers.description,
	customers.logo,
	customers.idTypeCustomer,
	cfp_formateurs.isActiveFormateur,
	cfp_formateurs.isActiveCfp,
	SUBSTRING(customers.customerName, 1,1) AS initialNameCustomer
FROM formateurs
INNER JOIN cfp_formateurs ON cfp_formateurs.idFormateur = formateurs.idFormateur
INNER JOIN cfps ON cfp_formateurs.idCfp = cfps.idCustomer
INNER JOIN customers ON cfps.idCustomer = customers.idCustomer;


CREATE or REPLACE VIEW v_nombre_projets_intra AS
select count(idProjet) as nombre_projets_intra
from projets
join type_projets on projets.idTypeProjet = type_projets.idTypeProjet
where type_projets.idTypeProjet=1;

-- inter

CREATE or REPLACE VIEW v_nombre_projets_inter AS
select count(idProjet) as nombre_projets_inter
from projets
join type_projets on projets.idTypeProjet = type_projets.idTypeProjet
where type_projets.idTypeProjet=2;

-- interne

CREATE or REPLACE VIEW v_nombre_projets_interne AS
select count(idProjet) as nombre_projets_interne
from projets
join type_projets on projets.idTypeProjet = type_projets.idTypeProjet
where type_projets.idTypeProjet=3;

-- stat du modules cfp

CREATE or REPLACE VIEW v_nombre_projets_modules_cfp AS
select count(idModule) as nombre_projets_modules_cfp
from mdls
join type_modules on mdls.idTypeModule = type_modules.idTypeModule
where type_modules.idTypeModule=1;

-- stat du module entreprise

CREATE or REPLACE VIEW v_nombre_projets_modules_entreprise AS
select count(mdls.idModule) as nombre_projets_modules_entreprise
from mdls
join type_modules on mdls.idTypeModule = type_modules.idTypeModule
where type_modules.idTypeModule=2;

-- stat cfp

CREATE or REPLACE VIEW v_nombre_cfp AS
select count(cfps.idCustomer) as nombre_cfp
from cfps
join customers on cfps.idCustomer = customers.idCustomer
join users on users.id = customers.idCustomer
join role_users on role_users.user_id = cfps.idCustomer
where users.user_is_deleted = 0 and role_users.isActive = 1;

-- stat entreprises

CREATE or REPLACE VIEW v_nombre_entreprise AS
select count(entreprises.idCustomer) as nombre_entreprise
from entreprises
join customers on entreprises.idCustomer = customers.idCustomer;

-- stat formateurs

CREATE or REPLACE VIEW v_nombre_formateur AS
select count(idFormateur) as nombre_formateur
from forms;

-- stat formateur cfps

CREATE or REPLACE VIEW v_nombre_formateur_cfp as
SELECT count(idFormateur) as nombre_formateur_cfp
FROM cfp_formateurs;

-- stat de referent cfp

create or REPLACE VIEW v_nombre_referent_cfp as
SELECT count(id) as nombre_referent_cfp
from role_users
where role_id = 3;

-- stat de referent entreprise

create or REPLACE VIEW v_nombre_referent_entreprise as
SELECT count(id) as nombre_referent_entreprise
from role_users
where role_id = 6;

-- stat formateur interne

create or REPLACE VIEW v_nombre_formateur_entreprise as
select count(idFormateur) as nombre_formateur_entreprise
from formateur_internes;

-- stat apprenants

CREATE or REPLACE VIEW v_nombre_apprenant AS
select count(idEmploye) as nombre_apprenant
from apprenants;

-- stat fond propre

CREATE or REPLACE VIEW v_nombre_projet_fond_propre AS
SELECT count(idProjet) as nombre_projet_fond_propre
from intras
where idPaiement=1;

-- stat FMTP

CREATE or REPLACE VIEW v_nombre_projet_fmtp AS
SELECT count(idProjet) as nombre_projet_fmtp
from intras
where idPaiement=2;

-- stat Autre

CREATE or REPLACE VIEW v_nombre_projet_autres AS
SELECT count(idProjet) as nombre_projet_autres
from intras
where idPaiement=3;

-- Group By etp
CREATE or REPLACE VIEW v_list_entreprise_inter AS
SELECT cst.idCustomer AS idEtp, cst.idTypeCustomer, cst.logo AS etp_logo, cst.customerName AS etp_name, cst.customerEmail AS mail, ie.idProjet, SUBSTRING(cst.customerName, 1, 1) AS etp_initial_name
FROM customers AS cst
INNER JOIN inter_entreprises AS ie ON cst.idCustomer = ie.idEtp
WHERE cst.idTypeCustomer = 2;

CREATE or REPLACE VIEW v_list_apprenant_inter AS
SELECT emp.idEmploye, ie.idEtp, usr.name AS emp_name, usr.firstName AS emp_firstname, usr.matricule AS emp_matricule, fonctions.fonction AS emp_fonction, usr.photo AS emp_photo, usr.email AS emp_email, cst.customerName AS etp_name, ie.idProjet, role_users.role_id, role_users.user_is_in_service
FROM employes AS emp
INNER JOIN inter_entreprises AS ie ON emp.idCustomer = ie.idEtp
INNER JOIN users AS usr ON emp.idEmploye = usr.id
INNER JOIN customers AS cst ON emp.idCustomer = cst.idCustomer
INNER JOIN employes ON usr.id = employes.idEmploye
INNER JOIN fonctions ON employes.idFonction = fonctions.idFonction
INNER JOIN role_users ON employes.idEmploye = role_users.user_id
WHERE usr.name IS NOT NULL;

CREATE or REPLACE VIEW v_list_apprenant_inter_added AS
SELECT da.idEmploye, da.idEtp, usr.name AS emp_name, usr.firstName AS emp_firstname, usr.matricule AS emp_matricule, fonctions.fonction AS emp_fonction, usr.photo AS emp_photo, usr.email AS emp_email, cst.customerName AS etp_name, da.idProjet
FROM detail_apprenant_inters AS da
INNER JOIN users AS usr ON da.idEmploye = usr.id
INNER JOIN customers AS cst ON da.idEtp = cst.idCustomer
INNER JOIN employes ON usr.id = employes.idEmploye
INNER JOIN fonctions ON employes.idFonction = fonctions.idFonction
WHERE usr.name IS NOT NULL;


CREATE OR REPLACE VIEW v_entreprise_all AS
SELECT customers.customerName,
	customers.description,
	customers.customer_addr_lot,
	customers.customerPhone,
	customers.customerEmail,
	customers.idCustomer AS idEtp,
	users.user_is_deleted,
    ru.isActive, ru.role_id
FROM entreprises
INNER JOIN customers on entreprises.idCustomer=customers.idCustomer
INNER JOIN users on users.id = entreprises.idCustomer
INNER JOIN role_users AS ru ON entreprises.idCustomer = ru.user_id;

-- findall cfp

CREATE OR REPLACE VIEW v_cfp_all AS
SELECT SUBSTRING(customers.customerName, 1, 1) AS initial_name,
	customers.customerName,
	customers.nif,
	customers.stat,
	customers.rcs,
	customers.description,
	customers.customer_addr_lot,
	customers.customer_addr_quartier,
	customers.customerPhone,
	customers.customerEmail,
    customers.idCustomer AS idCfp,
	vc.ville_name AS customer_ville,
    vc.vi_code_postal AS customer_addr_code_postal,
	users.user_is_deleted,
    ru.isActive, ru.role_id
FROM cfps
INNER JOIN customers on cfps.idCustomer=customers.idCustomer
INNER JOIN users on users.id = customers.idCustomer
INNER JOIN role_users AS ru ON cfps.idCustomer = ru.user_id
LEFT JOIN ville_codeds AS vc ON customers.idVilleCoded = vc.id;

-- liste referent cfp

CREATE OR REPLACE VIEW v_referent_cfp_all AS
SELECT distinct(users.id),
	name,
	firstName,
	phone,
	email,
	user_addr_quartier
from role_users
inner join users on role_users.user_id=users.id
where role_id = 3;

-- liste referent entreprise

CREATE OR REPLACE VIEW v_referent_etp_all AS
SELECT distinct(users.id),
	name,
	firstName,
	phone,
	email,
	user_addr_quartier
from role_users
inner join users on role_users.user_id=users.id
where role_id = 6;

-- liste module cfp

CREATE OR REPLACE VIEW v_module_cfp_all AS
select mdls.idModule,mdls.moduleName,
	mdls.description,
	domaine_formations.nomDomaine,
	customers.customerName
from mdls
inner join type_modules on mdls.idTypeModule = type_modules.idTypeModule
inner join customers on customers.idCustomer = mdls.idCustomer
inner join domaine_formations on domaine_formations.idDomaine= mdls.idDomaine
where type_modules.idTypeModule=1;

-- liste module etp

CREATE OR REPLACE VIEW v_module_etp_all AS
select mdls.idModule,mdls.moduleName,
	mdls.description,
	domaine_formations.nomDomaine,
	customers.customerName
from mdls
inner join type_modules on mdls.idTypeModule = type_modules.idTypeModule
inner join customers on customers.idCustomer = mdls.idCustomer
inner join domaine_formations on domaine_formations.idDomaine= mdls.idDomaine
where type_modules.idTypeModule=2;

-- seances ETP
CREATE OR REPLACE VIEW v_seances_etp AS
SELECT seances.idSeance,
	seances.id_google_seance,
	DATE_FORMAT(seances.dateSeance, '%a %d %b %y') AS dateSeanceEmar,
	seances.dateSeance,
	seances.heureDebut,
	seances.heureFin,
	seances.idProjet,
	TIMEDIFF(seances.heureFin,seances.heureDebut) AS intervalle_raw,
	lieux.idLieu,
	lieux.li_name,
	type_projets.type AS project_type,
	modalites.modalite,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	internes.idEtp, 
	intras.idEtp AS idEtp_intra, 
	inter_entreprises.idEtp AS idEtp_inter, 
	projets.idSalle,
	salles.salle_name,
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
	vc.vi_code_postal AS salle_code_postal,
	projets.idCustomer AS idCfp,
	projets.idTypeProjet,
	projets.idDossier,
	projets.referenceEtp AS project_reference_etp,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.projectName AS project_name,
	projets.project_is_active,
	projets.project_is_reserved,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	projets.project_is_trashed,
	projets.dateDebut AS project_date_debut,
	projets.dateFin AS project_date_fin,
    projets.idVilleCoded,
	vc.idVille,
	villes.ville,
	projets.idModule,
	mdls.moduleName AS module_name,
	mdls.module_subtitle
FROM seances
INNER JOIN projets ON seances.idProjet = projets.idProjet
INNER JOIN modalites ON projets.idModalite = modalites.idModalite
INNER JOIN salles ON projets.idSalle = salles.idSalle
INNER JOIN lieux ON salles.idLieu = lieux.idLieu
INNER JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
INNER JOIN villes ON vc.idVille = villes.idVille
INNER JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN internes ON internes.idProjet = projets.idProjet
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inter_entreprises ON inter_entreprises.idProjet = projets.idProjet
WHERE projets.dateDebut IS NOT NULL
ORDER BY seances.dateSeance;

-- seances formateur
CREATE OR REPLACE VIEW v_seances_form AS
SELECT seances.idSeance,
	seances.id_google_seance,
	seances.dateSeance,
	seances.heureDebut,
	seances.heureFin,
	seances.idProjet,
	TIMEDIFF(seances.heureFin,seances.heureDebut) AS intervalle_raw,
	type_projets.type AS project_type,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	internes.idEtp, 
	intras.idEtp AS idEtp_intra, 
	inter_entreprises.idEtp AS idEtp_inter, 
	projets.idSalle,
	salles.salle_name,
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
	vc.vi_code_postal AS salle_code_postal,
	projets.idCustomer AS idCfp,
	projets.idTypeProjet,
	projets.referenceEtp AS project_reference_etp,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.projectName AS project_name,
	projets.project_is_active,
	projets.project_is_reserved,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	projets.dateDebut AS project_date_debut,
	projets.dateFin AS project_date_fin,
    projets.idVilleCoded,
	vc.idVille,
	villes.ville,
	projets.idModule,
	mdls.moduleName AS module_name,
	mdls.module_subtitle,
	formateurs.idFormateur
FROM seances
INNER JOIN projets ON seances.idProjet = projets.idProjet
INNER JOIN salles ON projets.idSalle = salles.idSalle
INNER JOIN lieux ON salles.idLieu = lieux.idLieu
INNER JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
INNER JOIN villes ON vc.idVille = villes.idVille
INNER JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN internes ON internes.idProjet = projets.idProjet
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inter_entreprises ON inter_entreprises.idProjet = projets.idProjet
LEFT JOIN project_forms ON project_forms.idProjet = projets.idProjet
LEFT JOIN formateurs ON formateurs.idFormateur = project_forms.idFormateur
WHERE projets.dateDebut IS NOT NULL;

-- seance apprenant
CREATE OR REPLACE VIEW v_seances_appr AS
SELECT seances.idSeance,
	seances.id_google_seance,
	seances.dateSeance,
	seances.heureDebut,
	seances.heureFin,
	seances.idProjet,
	TIMEDIFF(seances.heureFin,seances.heureDebut) AS intervalle_raw,
	type_projets.type AS project_type,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	internes.idEtp, 
	intras.idEtp AS idEtp_intra, 
	inter_entreprises.idEtp AS idEtp_inter, 
	projets.idSalle,
	salles.salle_name,
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
	vc.vi_code_postal AS salle_code_postal,
	projets.idCustomer AS idCfp,
	projets.idTypeProjet,
	projets.referenceEtp AS project_reference_etp,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.projectName AS project_name,
	projets.project_is_active,
	projets.project_is_reserved,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	projets.dateDebut AS project_date_debut,
	projets.dateFin AS project_date_fin,
    projets.idVilleCoded,
	vc.idVille,
	villes.ville,
	projets.idModule,
	mdls.moduleName AS module_name,
	mdls.module_subtitle,
	apprenants.idEmploye
FROM seances
INNER JOIN projets ON seances.idProjet = projets.idProjet
INNER JOIN salles ON projets.idSalle = salles.idSalle
INNER JOIN lieux ON salles.idLieu = lieux.idLieu
INNER JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
INNER JOIN villes ON vc.idVille = villes.idVille
INNER JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN internes ON internes.idProjet = projets.idProjet
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inter_entreprises ON inter_entreprises.idProjet = projets.idProjet
LEFT JOIN detail_apprenants ON detail_apprenants.idProjet = projets.idProjet
LEFT JOIN apprenants ON apprenants.idEmploye = detail_apprenants.idEmploye
WHERE projets.dateDebut IS NOT NULL;

CREATE OR REPLACE VIEW v_projet_intras2 AS
SELECT projets.idProjet,
	projets.referenceEtp,
	projets.idTypeprojet,
	projets.projectName AS project_name,
	projets.dateDebut,
	projets.dateFin,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.project_is_reserved,
	projets.project_is_active,
	projets.project_is_trashed,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	projets.project_price_pedagogique,
	projets.project_price_annexe,
	intras.idEtp,
	intras.idPaiement,
	paiements.paiement,
	intras.idCfp as cfp_intra,
	inters.idCfp as cfp_inter,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name,
	customers.customerName AS etp_name,
	customers.logo AS etp_logo,
	customers.description AS etp_description,
	type_projets.type AS project_type,
	projets.idCustomer,
	modalites.modalite,
    projets.idVilleCoded,
	vc.idVille,
	villes.ville,
	projets.idModule,
	mdls.moduleName AS module_name,
	mdls.description AS module_description,
	mdls.module_image,
	mdls.idDomaine,
	domaine_formations.nomDomaine AS domaine_name,
	projets.idSalle,
	salles.salle_name,
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
	vc.vi_code_postal AS salle_code_postal,
	CASE WHEN(projets.dateDebut < CURRENT_DATE
		AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month" WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month" WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month" WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month" WHEN(projets.dateDebut >= CURRENT_DATE AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month" WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month" WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month" WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
	END p_id_periode,
	DATE_FORMAT(dateDebut, "%M, %Y") AS headDate,
	DATE_FORMAT(dateDebut, "%b") AS headMonthDebut,
	DATE_FORMAT(dateFin, "%b") AS headMonthFin,
	DATE_FORMAT(dateDebut, "%Y") AS headYear,
	DATE_FORMAT(dateDebut, "%d") AS headDayDebut,
	DATE_FORMAT(dateFin, "%d") AS headDayFin
FROM projets
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN paiements ON intras.idPaiement = paiements.idPaiement
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN entreprises ON intras.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
LEFT JOIN lieux ON salles.idLieu = lieux.idLieu
LEFT JOIN villes ON vc.idVille =villes.idVille
WHERE projets.dateDebut IS NOT NULL;

-- liste des projets
CREATE OR REPLACE VIEW v_union_projets2 AS
SELECT idProjet,
	referenceEtp,
	'--' as cfp_intra,
	idTypeprojet,
	idModule,
	idVille,
	p_id_periode,
	module_name,
	etp_name,
	ville,
	project_status,
	project_type,
	project_name,
	dateDebut,
	dateFin,
	project_reference,
	project_title,
	project_description,
	project_is_reserved,
	project_is_active,
	project_is_trashed,
	module_image,
	etp_logo,
	etp_initial_name,
	null as idPaiement,
	null as paiement,
	salle_name,
	salle_quartier,
	salle_code_postal,
	project_is_cancelled,
	project_is_repported,
	'--' as project_price_pedagogique,
	'--' as project_price_annexe,
	idEtp,
	idCfp_inter,
	headDate,
	headMonthDebut,
	headMonthFin,
	headYear,
	headDayDebut,
	headDayFin
FROM v_projet_internes
UNION
SELECT idProjet,
	referenceEtp,
	cfp_intra,
	idTypeprojet,
	idModule,
	idVille,
	p_id_periode,
	module_name,
	etp_name,
	ville,
	project_status,
	project_type,
	project_name,
	dateDebut,
	dateFin,
	project_reference,
	project_title,
	project_description,
	project_is_reserved,
	project_is_active,
	project_is_trashed,
	module_image,
	etp_logo,
	etp_initial_name,
	idPaiement,
	paiement,
	salle_name,
	salle_quartier,
	salle_code_postal,
	project_is_cancelled,
	project_is_repported,
	project_price_pedagogique,
	project_price_annexe,
	idEtp,
	cfp_inter,
	headDate,
	headMonthDebut,
	headMonthFin,
	headYear,
	headDayDebut,
	headDayFin
FROM v_projet_intras2;

CREATE OR REPLACE VIEW v_projet_all AS
SELECT projets.idProjet,
	projets.referenceEtp,
	projets.idTypeprojet,
	projets.projectName AS project_name,
	projets.dateDebut,
	projets.dateFin,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.project_is_reserved,
	projets.project_is_active,
	projets.project_is_trashed,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	projets.project_price_pedagogique,
	projets.project_price_annexe,
	intras.idEtp,
	intras.idPaiement,
	paiements.paiement,
	intras.idCfp as cfp_intra,
	inters.idCfp as cfp_inter,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name,
	customers.customerName AS etp_name,
	customers.logo AS etp_logo,
	customers.description AS etp_description,
	type_projets.type AS project_type,
	projets.idCustomer,
	modalites.modalite,
    projets.idVilleCoded,
	vc.idVille,
	villes.ville,
	projets.idModule,
	mdls.moduleName AS module_name,
	mdls.description AS module_description,
	mdls.module_image,
	mdls.idDomaine,
	domaine_formations.nomDomaine AS domaine_name,
	projets.idSalle,
	salles.salle_name,
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
	vc.vi_code_postal AS salle_code_postal,
	CASE WHEN(projets.dateDebut < CURRENT_DATE AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month" WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month" WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) 	AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month" WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month" WHEN(projets.dateDebut >= CURRENT_DATE AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month" WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) 	AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month" WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month" WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
	END p_id_periode,
	DATE_FORMAT(dateDebut, "%M, %Y") AS headDate,
	DATE_FORMAT(dateDebut, "%b") AS headMonthDebut,
	DATE_FORMAT(dateFin, "%b") AS headMonthFin,
	DATE_FORMAT(dateDebut, "%Y") AS headYear,
	DATE_FORMAT(dateDebut, "%d") AS headDayDebut,
	DATE_FORMAT(dateFin, "%d") AS headDayFin
FROM projets
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN paiements ON intras.idPaiement = paiements.idPaiement
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN entreprises ON intras.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
LEFT JOIN lieux ON salles.idLieu = lieux.idLieu
LEFT JOIN villes ON vc.idVille = villes.idVille
WHERE projets.dateDebut IS NOT NULL;

CREATE OR REPLACE VIEW v_emargement_appr_inter AS	
    SELECT se.idSeance,
	se.dateSeance,
	se.heureDebut,
	se.heureFin,
	se.idProjet,
	da.idEmploye,
	usr.name,
	usr.firstName,
	usr.photo,
    emar.isPresent
FROM seances AS se
INNER JOIN detail_apprenant_inters AS da ON se.idProjet = da.idProjet
LEFT JOIN emargements AS emar ON se.idSeance = emar.idSeance AND da.idEmploye = emar.idEmploye AND se.idProjet = emar.idProjet
INNER JOIN users AS usr ON da.idEmploye = usr.id
ORDER BY da.idEmploye,
	se.dateSeance ASC;

CREATE OR REPLACE VIEW v_list_apprenants_inter AS
		SELECT dai.idEmploye,
	dai.idProjet,
	employes.idCustomer AS idEtp,
	SUBSTRING(users.name, 1, 1) AS emp_initial_name,
	users.name AS emp_name,
	users.firstName AS emp_firstname,
	users.matricule AS emp_matricule,
	users.dateNais AS emp_date_nais,
	users.email AS emp_email,
	users.cin AS emp_cin,
	users.phone AS emp_phone,
	users.adresse AS emp_adresse,
	users.photo AS emp_photo,
	fonctions.fonction AS emp_fonction,
	customers.customerName AS etp_name,
	customers.customerEmail AS etp_email
FROM detail_apprenant_inters AS dai
INNER JOIN apprenants ON dai.idEmploye = apprenants.idEmploye
INNER JOIN employes ON apprenants.idEmploye = employes.idEmploye
INNER JOIN users ON employes.idEmploye = users.id
INNER JOIN fonctions ON employes.idFonction = fonctions.idFonction
INNER JOIN projets ON dai.idProjet = projets.idProjet
INNER JOIN customers ON employes.idCustomer = customers.idCustomer;


 CREATE OR REPLACE VIEW v_evaluation_alls AS
SELECT eval_chauds.idProjet,
	eval_chauds.idEmploye,
	eval_chauds.note,
	eval_chauds.com1,
	eval_chauds.com2,
	eval_chauds.generalApreciate,
  eval_chauds.idExaminer,
  eval_chauds.idValComment,
	questions.idQuestion,
	questions.question,
	questions.idTypeQuestion,
	type_questions.typeQuestion,
    users.name AS name_examiner,
    users.firstName AS firstname_examiner
FROM eval_chauds
INNER JOIN questions ON eval_chauds.idQuestion = questions.idQuestion
INNER JOIN type_questions ON questions.idTypeQuestion = type_questions.idTypeQuestion
INNER JOIN users ON eval_chauds.idExaminer = users.id;

 CREATE OR REPLACE VIEW v_general_note_evaluation AS
SELECT eval_chauds.idProjet,
	eval_chauds.idEmploye,
	eval_chauds.note,
	eval_chauds.com1,
	eval_chauds.com2,
	eval_chauds.generalApreciate,
  eval_chauds.idExaminer,
	questions.idQuestion,
	questions.question,
	questions.idTypeQuestion,
	type_questions.typeQuestion,
    users.name,
    users.firstName
FROM eval_chauds
INNER JOIN questions ON eval_chauds.idQuestion = questions.idQuestion
INNER JOIN type_questions ON questions.idTypeQuestion = type_questions.idTypeQuestion
INNER JOIN users ON eval_chauds.idExaminer = users.id
GROUP BY eval_chauds.idProjet, eval_chauds.idEmploye;


-- reporting formation
CREATE OR REPLACE VIEW v_apprenant_information AS 
SELECT
    cfp_etps.idCfp,           
    cfp_etps.idCfp AS id_cfp, 
    cfp_etps.idEtp,
    employes.idEmploye,        
    employes.idEmploye AS role_id, 
    users.matricule AS emp_matricule,
    vp.module_name,
    vp.idModule,
    users.name AS emp_name,
    users.firstName AS emp_firstname,
    fonctions.fonction AS emp_fonction,
    vp.salle_name,
    vp.salle_quartier,
    vp.project_status,
    vp.project_type,
    etp.customerName AS etp_name,
    cfp.customerName AS cfp_name,
    modules.dureeH,
    vp.dateDebut,
    vp.dateFin,
	modules.module_image,
    COALESCE(SUM(CASE WHEN em.isPresent = 1 THEN 1 ELSE 0 END), 0) AS nbPresent,  -- Total des présences
    COUNT(em.idSeance) AS totalSeances,  -- Total des séances
    COALESCE(SUM(CASE WHEN em.isPresent = 1 THEN 1 ELSE 0 END), 0) / NULLIF(COUNT(em.idSeance), 0) * 100 AS taux_de_presence -- Calcul du taux de présence
FROM cfp_etps
INNER JOIN customers AS etp ON cfp_etps.idEtp = etp.idCustomer
INNER JOIN customers AS cfp ON cfp_etps.idCfp = cfp.idCustomer
INNER JOIN employes ON employes.idCustomer = etp.idCustomer
INNER JOIN users ON employes.idEmploye = users.id
INNER JOIN fonctions ON employes.idFonction = fonctions.idFonction
LEFT JOIN detail_apprenants AS da ON da.idEmploye = employes.idEmploye
LEFT JOIN v_projet_cfps AS vp ON vp.idProjet = da.idProjet
INNER JOIN mdls AS modules ON modules.idModule = vp.idModule
LEFT JOIN emargements AS em ON em.idEmploye = employes.idEmploye -- Joindre la table des emargements
GROUP BY 
    cfp_etps.idCfp,           
    cfp_etps.idEtp,
    employes.idEmploye,        
    users.matricule,
    vp.module_name,
    vp.idModule,
    users.name,
    users.firstName,
    fonctions.fonction,
    vp.salle_name,
    vp.salle_quartier,
    vp.project_status,
    vp.project_type,
    etp.customerName,
    cfp.customerName,
    modules.dureeH,
    vp.dateDebut,
    vp.dateFin;


CREATE OR REPLACE VIEW v_list_particuliers AS
SELECT `pt`.`idParticulier` AS `idParticulier`, substr(`us`.`name`,1,1) AS `part_initial_name`, `us`.`name` AS `part_name`, `us`.`firstName` AS `part_firstname`, `us`.`email` AS `part_email`, `us`.`cin` AS `part_cin`, `us`.`matricule` AS `part_matricule`, `us`.`phone` AS `part_phone`, `vc`.`ville_name` AS `part_ville`, `us`.`user_addr_code_postal` AS `part_addr_code_postal`, `us`.`adresse` AS `part_addr`, `us`.`user_addr_lot` AS `part_addr_lot`, `us`.`user_addr_quartier` AS `part_addr_quartier`, `us`.`photo` AS `part_photo`, `us`.`dateNais` AS `part_date_nais`, `ru`.`role_id` AS `part_role_id`, `ru`.`hasRole` AS `part_has_role`, `ru`.`isActive` AS `part_is_active`, `ru`.`user_is_in_service` AS `user_is_in_service` FROM particuliers AS pt 
INNER JOIN users AS us ON pt.idParticulier = us.id
INNER JOIN role_users AS ru ON ru.user_id = pt.idParticulier
left join `ville_codeds` `vc` on(`us`.`idVille` = `vc`.`id`)
ORDER BY pt.idParticulier ASC;


CREATE OR REPLACE VIEW v_particuliers_projet AS
SELECT 
    pt.idParticulier, 
    us.name AS part_name, 
    us.firstName AS part_firstname, 
    us.email AS part_email, 
    us.cin AS part_cin, 
    us.matricule AS part_matricule, 
    us.phone AS part_phone, 
    us.adresse AS part_addr, 
    us.photo AS part_photo, 
    us.dateNais AS part_date_nais, 
    ru.role_id AS part_role_id, 
    ru.hasRole AS part_has_role, 
    ru.isActive AS part_is_active,
    ru.user_is_in_service,
    it.idProjet,
	2 AS idCfp
FROM particulier_projet AS pt 
INNER JOIN users AS us ON pt.idParticulier = us.id
INNER JOIN role_users AS ru ON ru.user_id = pt.idParticulier
INNER JOIN particuliers AS pd ON pt.idParticulier = pd.idParticulier
INNER JOIN inters AS it ON it.idProjet = pt.idProjet
ORDER BY pt.idParticulier ASC;


CREATE OR REPLACE VIEW v_projet_particulier AS 
SELECT
    projets.idProjet, 
    projets.referenceEtp, 
    projets.projectName AS project_name, 
    projets.dateDebut, 
    projets.dateFin, 
    projets.project_reference, 
    projets.project_title, 
    projets.project_description, 
    projets.project_is_reserved, 
    projets.project_is_active, 
    projets.project_is_trashed, 
    projets.project_is_cancelled, 
    projets.project_is_repported, 
    projets.project_is_closed, 
    projets.project_price_pedagogique, 
    projets.project_price_annexe, 
    intras.idEtp, 
    intras.idCfp, 
    inters.idCfp as cfp_inter, 
    particuliers.idParticulier,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
    SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name, 
    customers.customerName AS etp_name, 
    customers.logo AS etp_logo, 
    customers.description AS etp_description, 
    type_projets.type AS project_type, 
    projets.idCustomer, 
    modalites.modalite, 
    projets.idVilleCoded,
    vc.idVille, 
    villes.ville, 
    projets.idModule, 
    mdls.moduleName AS module_name, 
    mdls.description AS module_description, 
    mdls.module_image, 
    intras.idPaiement, 
    mdls.idDomaine, 
    domaine_formations.nomDomaine AS domaine_name, 
    projets.idSalle, 
    salles.salle_name, 
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
    vc.vi_code_postal AS salle_code_postal,
    CASE 
        WHEN(projets.dateDebut < CURRENT_DATE AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH))
            THEN "prev_3_month"
        WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH))
            THEN "prev_6_month"
        WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH))
            THEN "prev_12_month"
        WHEN(projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH))
            THEN "prev_24_month"
        WHEN(projets.dateDebut >= CURRENT_DATE AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH))
            THEN "next_3_month"
        WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH))
            THEN "next_6_month"
        WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH))
            THEN "next_12_month"
        WHEN(projets.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND projets.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH))
            THEN "next_24_month"
    END p_id_periode, 
    DATE_FORMAT(dateDebut, "%M, %Y") AS headDate, 
    DATE_FORMAT(dateDebut, "%b") AS headMonthDebut, 
    DATE_FORMAT(dateFin, "%b") AS headMonthFin, 
    DATE_FORMAT(dateDebut, "%Y") AS headYear, 
    DATE_FORMAT(dateDebut, "%d") AS headDayDebut, 
    DATE_FORMAT(dateFin, "%d") AS headDayFin
FROM projets 
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN entreprises ON intras.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN particulier_projet ON particulier_projet.idProjet = projets.idProjet
LEFT JOIN particuliers ON particuliers.idParticulier = particulier_projet.idParticulier
LEFT JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
LEFT JOIN lieux ON salles.idLieu = lieux.idLieu
LEFT JOIN villes ON vc.idVille = villes.idVille
WHERE projets.dateDebut IS NOT NULL;

CREATE OR REPLACE VIEW v_list_etp_groupeds AS
SELECT 
	egd.idEntreprise, egd.idEntrepriseParent, c.customerName AS etp_name, c.nif AS etp_nif, c.stat AS etp_stat, c.rcs AS etp_rcs, c.description AS etp_description, c.siteWeb AS etp_site_web, c.logo AS etp_logo, c.customerEmail AS etp_email, c.customerPhone AS etp_phone, c.customer_slogan AS etp_slogan
FROM etp_groupeds AS egd
INNER JOIN customers AS c ON c.idCustomer = egd.idEntreprise;

-- misy ireo mpiasa sy referent rehetra miaraka @ orin'asa sy ny vondron'orin'asa misy azy avy
CREATE OR REPLACE VIEW v_list_emp_grps AS
SELECT
	e.idEmploye, egp.idEntreprise, egp.idEntrepriseParent, c_parent.customerName AS etp_name_parent, c_parent.nif AS etp_nif_parent, c_parent.stat AS etp_stat_parent, c_parent.rcs AS etp_rcs_parent, c_parent.description AS etp_description_parent, c_parent.siteWeb AS etp_siteweb_parent, c_parent.logo AS etp_logo_parent, c_parent.customerEmail AS etp_email_parent, c_parent.customerPhone AS etp_phone_parent, c_parent.customer_slogan AS etp_slogan_parent, c_child.customerName AS etp_name, SUBSTRING(u.name, 1, 1) AS emp_initial_name, u.name AS emp_name, u.firstName As emp_firstname, u.email AS emp_email, u.matricule AS emp_matricule, u.cin AS emp_cin, u.phone AS emp_phone, u.photo AS emp_photo, u.dateNais AS emp_date_nais, sexes.sexe AS emp_sexe, ru.role_id, ru.isActive AS emp_is_active, ru.hasRole AS emp_has_role, ru.user_is_in_service, c_child.nif AS etp_nif, c_child.stat AS etp_stat, c_child.rcs AS etp_rcs, c_child.description AS etp_description, c_child.siteWeb AS etp_siteweb, c_child.logo AS etp_logo, c_child.customerEmail AS etp_email, c_child.customerPhone AS etp_phone, c_child.customer_slogan AS etp_slogan, fct.fonction AS emp_fonction
FROM etp_groupeds AS egp
INNER JOIN customers AS c_parent ON egp.idEntrepriseParent = c_parent.idCustomer
INNER JOIN customers AS c_child ON egp.idEntreprise = c_child.idCustomer
INNER JOIN employes AS e ON egp.idEntreprise = e.idCustomer
INNER JOIN sexes ON e.idSexe = sexes.idSexe
INNER JOIN users AS u ON e.idEmploye = u.id
INNER JOIN role_users AS ru ON ru.user_id = u.id
INNER JOIN fonctions AS fct ON e.idFonction = fct.idFonction
ORDER BY e.idEmploye ASC;

CREATE OR REPLACE VIEW v_union_list_apprenant_inter AS
SELECT idEmploye, idEtp, '--' AS idEntrepriseParent, emp_name, emp_firstname, emp_matricule, emp_fonction, emp_photo, emp_email, etp_name, idProjet, role_id, user_is_in_service
FROM v_list_apprenant_inter
UNION
SELECT idEmploye, idEntreprise AS idEtp, idEntrepriseParent, emp_name, emp_firstname, emp_matricule, '--' AS emp_fonction, emp_photo, emp_email, etp_name, '--' AS idProjet, role_id, user_is_in_service
FROM v_list_emp_grps;

CREATE or REPLACE VIEW v_union_list_entreprise_inter AS
SELECT
    vei.idEtp,
    vei.idProjet,
    '--' AS idEtpParent,
    vei.etp_name,
    vei.etp_logo,
    vei.mail AS etp_mail
FROM
    v_list_entreprise_inter AS vei
UNION
SELECT
    egd.idEntreprise AS idEtp,
    '--' AS idProjet,
    egd.idEntrepriseParent  AS idEtpParent,
    cst.customerName AS etp_name,
    cst.logo AS etp_logo,
    cst.customerEmail AS etp_mail
FROM
    etp_groupeds AS egd
   JOIN customers AS cst ON egd.idEntreprise = cst.idCustomer;

CREATE OR REPLACE VIEW v_document_dossier AS
SELECT 
    dc.idDocument,
    dc.titre, 
    dc.path, 
	dc.extension,
    dc.idDossier, 
    DATE_FORMAT(dc.updated_at, '%b %d, %Y, %l:%i %p') as updated_at,
    do.nomDossier, 
    do.idCfp,
    dc.filename, 
    dc.taille,
    dc.idTypeDocument, 
    tdc.type_document, 
    tdc.idSectionDocument, 
    sdc.section_document,
    (SELECT p.idProjet 
     FROM projets p 
     WHERE p.idDossier = do.idDossier 
     LIMIT 1) as idProjet
FROM documents as dc
INNER JOIN dossiers as do ON do.idDossier = dc.idDossier
INNER JOIN type_documents as tdc ON dc.idTypeDocument = tdc.idTypeDocument
INNER JOIN section_documents as sdc ON tdc.idSectionDocument = sdc.idSectionDocument;


CREATE OR REPLACE VIEW v_list_sub_contractors AS
SELECT 
	c.idCustomer AS idCfp, SUBSTRING(cc.customerName, 1,1) AS cfp_initial_name, cc.customerName AS cfp_name, cc.nif AS cfp_nif, cc.stat AS cfp_stat, cc.logo AS cfp_logo, cc.description AS cfp_description, cc.customerEmail AS cfp_email,
	vc.ville_name AS cfp_ville,
    vc.vi_code_postal AS cfp_addr_code_postal,
	cc.rcs AS cfp_rcs,
	cc.customer_addr_lot AS cfp_addr_lot,
	cc.customer_addr_quartier AS cfp_addr_quartier,
	sub_contractors.idSubContractor, cs.customerName AS sub_name, cs.customerEmail AS sub_email, SUBSTRING(cs.customerName, 1,1) AS sub_initial_name, sub_contractors.idCfp AS id_cfp, cs.logo AS sub_logo
FROM cfps AS c
INNER JOIN customers AS cc ON c.idCustomer = cc.idCustomer
LEFT JOIN sub_contractors ON cc.idCustomer = sub_contractors.idCfp
LEFT JOIN customers AS cs ON sub_contractors.idSubContractor = cs.idCustomer
LEFT JOIN ville_codeds AS vc ON cc.idVilleCoded = vc.id
WHERE cc.idTypeCustomer = 1;

CREATE OR REPLACE VIEW v_list_sub_contractor_addeds AS 
SELECT
projets.idCustomer AS idCfp, psc.idProjet, psc.idSubContractor, c.customerName AS cfp_name, c.customerEmail AS cfp_email, c.nif AS cfp_nif, c.logo AS cfp_logo, SUBSTRING(c.customerName, 1, 1) AS cfp_initial_name, cs.customerName AS sub_name, cs.customerEmail AS sub_email, cs.logo AS sub_logo, SUBSTRING(cs.customerName, 1, 1) AS sub_initial_name
FROM project_sub_contracts AS psc
INNER JOIN customers AS cs ON psc.idSubContractor = cs.idCustomer
INNER JOIN projets ON psc.idProjet = projets.idProjet
INNER JOIN customers AS c ON projets.idCustomer = c.idCustomer;

CREATE OR REPLACE VIEW v_project_sub_contracts AS
SELECT
psc.idProjet, psc.idSubContractor, c.customerName AS sub_name, c.customerEmail AS sub_email, c.nif AS sub_nif, c.logo AS sub_logo, SUBSTRING(c.customerName, 1, 1) AS sub_initial_name,
ccfp.customerName AS cfp_name, ccfp.idCustomer AS idCfp, ccfp.description AS cfp_description, ccfp.customerEmail AS cfp_email, ccfp.logo AS cfp_logo, SUBSTRING(ccfp.customerName, 1, 1) AS cfp_initial_name, 	p.project_reference, p.project_title, p.project_description, p.project_is_reserved, p.project_is_active, p.project_is_trashed, p.project_is_cancelled, p.project_is_repported, p.project_is_closed, p.project_price_pedagogique, p.project_price_annexe, p.total_ht, p.total_ttc, p.idDossier, p.idTypeProjet, tp.type AS project_type, p.referenceEtp,
	CASE
		WHEN (p.project_is_archived = 1) THEN "Archivé"
		WHEN (p.project_is_trashed = 1) THEN "Supprimé"
		WHEN (p.project_is_closed = 1) THEN "Cloturé"
		WHEN (p.project_is_active = 1
								AND p.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (p.project_is_active = 0
								AND p.project_is_cancelled = 0
								AND p.project_is_repported = 0
								AND p.project_is_reserved = 0
								AND p.project_is_archived = 0
								AND p.project_is_trashed = 0) THEN "En préparation"
		WHEN (p.project_is_cancelled = 1) THEN "Annulé"
		WHEN (p.project_is_repported = 1) THEN "Reporté"
		WHEN (p.project_is_reserved = 1) THEN "Réservé"
		WHEN (p.project_is_active = 1
								AND p.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status
FROM project_sub_contracts AS psc
INNER JOIN customers AS c ON psc.idSubContractor = c.idCustomer
INNER JOIN projets AS p ON psc.idProjet = p.idProjet
INNER JOIN customers AS ccfp ON p.idCustomer = ccfp.idCustomer
INNER JOIN type_projets AS tp ON p.idTypeProjet = tp.idTypeProjet
WHERE p.dateDebut IS NOT NULL;

-- union projets
CREATE OR REPLACE VIEW v_union_projets AS
SELECT
	idProjet, idDossier, referenceEtp,'--' as idCfp_intra,idTypeprojet, idModule, idVilleCoded, idVille,'--'as idPaiement,'--' as paiement, p_id_periode,module_name,module_description,etp_name,ville,project_status,project_type, project_name, dateDebut, dateFin, project_reference, project_title, project_description, project_is_reserved, project_is_active, project_is_trashed,module_image, etp_logo, etp_initial_name,salle_name,salle_rue, salle_quartier,salle_code_postal, project_is_cancelled,project_is_repported, '--' as project_price_pedagogique,'--' as project_price_annexe ,idEtp, idCfp_inter,idEtp_inter,idModalite,modalite,headDate,headMonthDebut,headMonthFin,headYear,headDayDebut,headDayFin, total_ht,total_ttc,total_ht_etp,total_ttc_etp, total_ht_sub_contractor
FROM v_projet_internes
UNION
SELECT
	idProjet, idDossier,referenceEtp,idCfp_intra, idTypeprojet, idModule, idVilleCoded, idVille,idPaiement,paiement,p_id_periode,module_name,module_description,etp_name,ville,project_status,project_type,project_name,dateDebut, dateFin, project_reference, project_title, project_description, project_is_reserved, project_is_active,project_is_trashed,module_image, etp_logo,etp_initial_name,salle_name,salle_rue, salle_quartier,salle_code_postal,project_is_cancelled,project_is_repported,project_price_pedagogique,project_price_annexe, idEtp,idCfp_inter,idEtp_inter,idModalite,modalite, headDate, headMonthDebut, headMonthFin, headYear, headDayDebut,headDayFin, total_ht,total_ttc,total_ht_etp,total_ttc_etp, total_ht_sub_contractor 
FROM v_projet_inters
UNION
SELECT
	idProjet, idDossier, referenceEtp,idCfp_intra, idTypeprojet,idModule, idVilleCoded, idVille,idPaiement,paiement,p_id_periode,module_name,module_description,etp_name,ville,project_status,project_type,  project_name, dateDebut, dateFin, project_reference, project_title, project_description, project_is_reserved, project_is_active, project_is_trashed,module_image, etp_logo,etp_initial_name,salle_name,salle_rue, salle_quartier,salle_code_postal,  project_is_cancelled, project_is_repported, project_price_pedagogique, project_price_annexe, idEtp, idCfp_inter,idEtp_inter,idModalite,modalite,headDate,headMonthDebut,headMonthFin,headYear,headDayDebut,headDayFin, total_ht,total_ttc,total_ht_etp,total_ttc_etp, total_ht_sub_contractor 
FROM v_projet_intras;

CREATE OR REPLACE VIEW v_union_projects AS
SELECT
    idProjet,
    idDossier,
    referenceEtp,
    idCfp_inter AS id_cfp,
    idEtp_inter AS id_etp,
    idTypeprojet,
    idModule,
    idVilleCoded,
    idVille,
    idPaiement,
    paiement,
    p_id_periode,
    module_name,
    module_description,
    etp_name,
    ville,
    project_status,
    project_type,
    project_name,
    dateDebut,
    dateFin,
    project_reference,
    project_title,
    project_description,
    project_is_reserved,
    project_is_active,
    project_is_trashed,
    module_image,
    etp_logo,
    etp_initial_name,
    salle_name,
    salle_rue,
    salle_quartier,
    salle_code_postal,
    project_is_cancelled,
    project_is_repported,
    project_price_pedagogique,
    project_price_annexe,
    idModalite,
    modalite,
    headDate,
    headMonthDebut,
    headMonthFin,
    headYear,
    headDayDebut,
    headDayFin,
    total_ht,
    total_ttc,
    total_ht_etp,
    total_ttc_etp,
    total_ht_sub_contractor
FROM v_projet_inters

UNION

SELECT
    idProjet,
    idDossier,
    referenceEtp,
    idCfp_intra AS id_cfp,
    idEtp AS id_etp,
    idTypeprojet,
    idModule,
    idVilleCoded,
    idVille,
    idPaiement,
    paiement,
    p_id_periode,
    module_name,
    module_description,
    etp_name,
    ville,
    project_status,
    project_type,
    project_name,
    dateDebut,
    dateFin,
    project_reference,
    project_title,
    project_description,
    project_is_reserved,
    project_is_active,
    project_is_trashed,
    module_image,
    etp_logo,
    etp_initial_name,
    salle_name,
    salle_rue,
    salle_quartier,
    salle_code_postal,
    project_is_cancelled,
    project_is_repported,
    project_price_pedagogique,
    project_price_annexe,
    idModalite,
    modalite,
    headDate,
    headMonthDebut,
    headMonthFin,
    headYear,
    headDayDebut,
    headDayFin,
    total_ht,
    total_ttc,
    total_ht_etp,
    total_ttc_etp,
    total_ht_sub_contractor
FROM v_projet_intras;


CREATE OR REPLACE VIEW v_projet_cfps_inters AS
SELECT projets.idProjet,
    projets.projectName AS project_name,
    projets.dateDebut,
    projets.dateFin,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.project_is_reserved,
	projets.project_is_active,
	projets.project_is_closed,
	inters.idCfp as idCfp_inter,
	CF.logo as logo_cfp,
	inters.project_inter_privacy,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	CASE WHEN (mdls.dureeJ) IS NULL THEN 0
		WHEN (mdls.dureeJ) = 1 THEN 1
		WHEN (mdls.dureeJ) = 2 THEN 2
		WHEN (mdls.dureeJ) = 3 THEN 3
		ELSE 4 END during,
	C.customerName AS cfp_name,
	type_projets.type AS project_type,
	projets.idCustomer,
	modalites.idModalite,
	modalites.modalite,
    projets.idVilleCoded,
	vc.id as idVille,
	vc.ville_name,
	vc.vi_code_postal,
	villes.ville,
	projets.idModule,
	mdls.moduleName AS module_name,
	mdls.description AS module_description,
	mdls.module_image,
	mdls.moduleStatut,
	mdls.dureeH,
	mdls.dureeJ,
	M.prix,
	M.prixGroupe,
	mdls.minApprenant,
	mdls.maxApprenant,
	mdls.idDomaine,
	domaine_formations.nomDomaine AS domaine_name,
    mdls.idLevel,
    module_levels.module_level_name AS level_name
FROM projets
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN entreprises ON intras.idEtp = entreprises.idCustomer
LEFT JOIN customers AS C ON projets.idCustomer = C.idCustomer
LEFT JOIN customers AS CF ON CF.idCustomer = inters.idCfp
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN module_levels ON mdls.idLevel = module_levels.idLevel
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
LEFT JOIN villes ON vc.idVille = villes.idVille
LEFT JOIN modules as M ON M.idModule = projets.idModule
WHERE projets.idTypeProjet = 2
AND projets.dateDebut IS NOT NULL;

CREATE OR REPLACE VIEW v_module_cfp_with_ville AS
SELECT  vc.id as idVille, 
		V.ville, 
		P.idProjet, 
		M.idModule, 
		M.moduleName, 
		MD.prix, M.idCustomer, 
		C.customerName AS cfp_name, 
		C.logo AS logo_cfp,M.dureeJ, 
		M.dureeH, M.idDomaine, 
		M.module_image,M.description, 
		CASE 
			WHEN M.dureeJ IS NULL THEN 0 
			WHEN M.dureeJ = 1 THEN 1 
			WHEN M.dureeJ = 2 THEN 2 
			WHEN M.dureeJ = 3 THEN 3 
			ELSE 4 END AS during, 
		M.idLevel, 
		L.module_level_name AS level_name,
		P.project_is_active, 
		P.dateDebut,
		P.idTypeProjet
    FROM mdls AS M
    LEFT JOIN module_levels AS L ON M.idLevel = L.idLevel 
    LEFT JOIN customers AS C ON C.idCustomer = M.idCustomer 
    LEFT JOIN modules AS MD ON MD.idModule = M.idModule
    LEFT JOIN projets AS P ON P.idModule = M.idModule
    LEFT JOIN ville_codeds AS vc ON P.idVilleCoded = vc.id
    LEFT JOIN villes AS V ON vc.idVille = V.idVille
    WHERE M.moduleStatut = 1
    AND M.idTypeModule = 1
    AND M.moduleName != 'Default module';

	
-- maj view v_reponses_users_qcm
DROP VIEW IF EXISTS `v_reponses_users_qcm`;

CREATE VIEW `v_reponses_users_qcm` AS
SELECT
    `st`.`idUtilisateur` AS `idUtilisateur`,
    `st`.`idSession` AS `idSession`,
    `st`.`totalPoints` AS `total_points`,
    `q`.`user_id` AS `qcm_creator_id`,
    `q`.`idQCM` AS `idQCM`,
    `q`.`intituleQCM` AS `intituleQCM`,
    `qq`.`idQuestion` AS `idQuestion`,
    `qq`.`texteQuestion` AS `enonce_question`,
    `qq`.`idTypeQcmQuestion` AS `idTypeQcmQuestion`,
    `st`.`totalPoints` AS `totalPoints`,
    CASE WHEN `qr`.`idReponse` IS NOT NULL THEN `qr`.`idReponse` ELSE `ru`.`idReponse`
    END AS `idReponse`,
    -- Récupérer categorie_id selon le type de question
    CASE 
        WHEN `qr`.`idReponse` IS NOT NULL THEN `qr`.`categorie_id`  -- Question QCM
        ELSE `qr_courte`.`categorie_id`  -- Réponse courte
    END AS `idRepCategorie`,
    CASE WHEN `qr`.`idReponse` IS NOT NULL THEN `qr`.`texteReponse` ELSE CAST(
        `ru`.`idReponse` AS CHAR CHARSET utf8mb4
    )
    END AS `choosen_responses`,
    COALESCE(`qr`.`points`, 0) AS `points_obtenus`,
    CAST(`st`.`dateDebut` AS DATE) AS `date_session`
FROM
    (
        (
            (
                (
                    (
                        `reponses_utilisateurs` `ru`
                    JOIN `sessions_test` `st`
                    ON
                        (`ru`.`idSession` = `st`.`idSession`)
                    )
                JOIN `qcm_questions` `qq`
                ON
                    (`ru`.`idQuestion` = `qq`.`idQuestion`)
                )
            LEFT JOIN `qcm_reponses` `qr`
            ON
                (`ru`.`idReponse` = `qr`.`idReponse`)
            )
        LEFT JOIN `qcm_reponses` `qr_courte`
        ON
            (`ru`.`idQuestion` = `qr_courte`.`idQuestion` AND `qr`.`idReponse` IS NULL)
        )
    JOIN `qcm` `q`
    ON
        (`qq`.`idQCM` = `q`.`idQCM`)
    );

-- vue pour avoir la liste des apprenants d'un qcm même ceux qui n'ont pas fais le qcm (ceux qui ont fait un qcm, avec ceux qui ont reçues une invitation à un QCM, cas particulier pour les particuliers)
create or replace
algorithm = UNDEFINED view `v_all_users_qcm` as
select
    `users_union`.`idUtilisateur` as `idUtilisateur`,
    `users_union`.`emp_initial_name` as `emp_initial_name`,
    `users_union`.`emp_fonction` as `emp_fonction`,
    `users_union`.`emp_photo` as `emp_photo`,
    `users_union`.`name` as `name`,
    `users_union`.`firstName` as `firstName`,
    `users_union`.`emp_matricule` as `emp_matricule`,
    `users_union`.`emp_email` as `emp_email`,
    `users_union`.`etp_name` as `etp_name`,
    `users_union`.`emp_phone` as `emp_phone`,
    `users_union`.`emp_is_active` as `emp_is_active`,
    `users_union`.`idEtp` as `idEtp`,
    `users_union`.`ville` as `ville`,
    `users_union`.`idVille` as `idVille`,
    `v`.`idSession` as `idSession`,
    `v`.`date_session` as `date_session`,
    `q`.`idQCM` as `idQCM`,
    `q`.`qcm_creator_id` as `qcm_creator_id`,
    `q`.`intituleQCM` as `intituleQCM`,
    coalesce((
        select sum(points_obtenus)
        from v_reponses_users_qcm vrq
        where vrq.idUtilisateur = users_union.idUtilisateur
        and vrq.idQCM = q.idQCM
        and (v.idSession is null or vrq.idSession = v.idSession)
    ), 0) as `total_points`
from
    (((
    select
        `e`.`idEmploye` as `idUtilisateur`,
        `e`.`initialName` as `emp_initial_name`,
        `e`.`fonction` as `emp_fonction`,
        `e`.`photo` as `emp_photo`,
        `e`.`name` as `name`,
        `e`.`firstName` as `firstName`,
        `e`.`matricule` as `emp_matricule`,
        `e`.`email` as `emp_email`,
        `e`.`customerName` as `etp_name`,
        `e`.`phone` as `emp_phone`,
        `e`.`isActive` as `emp_is_active`,
        `e`.`idCustomer` as `idEtp`,
        null as `ville`,
        null as `idVille`
    from
        `v_employe_alls` `e`
    where
        `e`.`role_id` = 4
union all
    select
        `p`.`idParticulier` as `idUtilisateur`,
        null as `emp_initial_name`,
        null as `emp_fonction`,
        null as `emp_photo`,
        `u`.`name` as `name`,
        `u`.`firstName` as `firstName`,
        null as `emp_matricule`,
        `u`.`email` as `emp_email`,
        null as `etp_name`,
        `u`.`phone` as `emp_phone`,
        null as `emp_is_active`,
        null as `idEtp`,
        null as `ville`,
        null as `idVille`
    from
        (((`particuliers` `p`
    join `users` `u` on
        (`p`.`idParticulier` = `u`.`id`))
    join `role_users` `ru` on
        (`p`.`idParticulier` = `ru`.`user_id`)))) `users_union`
cross join (
    select 
        `q`.`idQCM`,
        `q`.`user_id` as `qcm_creator_id`,
        `q`.`intituleQCM`
    from `qcm` `q`
    where exists (
        select 1 
        from `v_reponses_users_qcm` `v` 
        where `v`.`idQCM` = `q`.`idQCM`
    )
    or exists (
        select 1 
        from `qcm_invitations` `qi` 
        where `qi`.`idQCM` = `q`.`idQCM`
    )
) `q`)
left join (
    select
        `v_reponses_users_qcm`.`idUtilisateur` as `idUtilisateur`,
        `v_reponses_users_qcm`.`idSession` as `idSession`,
        `v_reponses_users_qcm`.`qcm_creator_id` as `qcm_creator_id`,
        `v_reponses_users_qcm`.`idQCM` as `idQCM`,
        `v_reponses_users_qcm`.`intituleQCM` as `intituleQCM`,
        `v_reponses_users_qcm`.`date_session` as `date_session`
    from
        `v_reponses_users_qcm`
    group by
        `idUtilisateur`,
        `idSession`,
        `qcm_creator_id`,
        `idQCM`,
        `intituleQCM`,
        `date_session`) `v` on
    (`users_union`.`idUtilisateur` = `v`.`idUtilisateur`
        and `q`.`idQCM` = `v`.`idQCM`))
where exists (
    select 1
    from `qcm_invitations` `qi`
    where `qi`.`idEmploye` = `users_union`.`idUtilisateur`
    and `qi`.`idQCM` = `q`.`idQCM`
    and now() between `qi`.`valid_from` and `qi`.`valid_until`
)
or exists (
    select 1
    from `v_reponses_users_qcm` `v`
    where `v`.`idUtilisateur` = `users_union`.`idUtilisateur`
    and `v`.`idQCM` = `q`.`idQCM`
)
or exists (
    select 1
    from `particuliers` `p`
    where `p`.`idParticulier` = `users_union`.`idUtilisateur`
);
-- vue pour avoir la liste des apprenants d'un qcm même ceux qui n'ont pas fais le qcm (ceux qui ont fait un qcm, avec ceux qui ont reçues une invitation à un QCM, cas particulier pour les particuliers)
	
-- vue pour avoir le point total maximum lors d'un qcm
create
or replace view v_pts_max_qcm as
select
	q.idQCM,
	q.intituleQCM,
	SUM(max_points) as points_maximum
from
	qcm q
	join qcm_questions qq on q.idQCM = qq.idQCM
	join (
		select
			idQuestion,
			MAX(points) as max_points
		from
			qcm_reponses
		group by
			idQuestion
	) r on qq.idQuestion = r.idQuestion
group by
	q.idQCM,
	q.intituleQCM;
-- vue pour avoir le point total maximum lors d'un qcm

-- vue sur les opérations sur les crédits
create or replace
algorithm = UNDEFINED view `v_credit_operations` as
select
    `th`.`idTransaction` as `transaction_id`,
    `th`.`idUser` as `transaction_user_id`,
    `th`.`transaction_ref` as `transaction_ref`,
    `th`.`montant` as `transaction_amount`,
    `th`.`typeTransaction` as `transaction_type`,
    `th`.`description` as `transaction_description`,
    `th`.`created_at` as `transaction_created_at`,
    `edc`.`idDebitEmpEtp` as `debit_emp_id`,
    `edc`.`idUser` as `employee_id`,
    `edc`.`montant` as `debit_amount`,
    `edc`.`typeTransaction` as `debit_type`,
    `edc`.`description` as `debit_description`,
    `edc`.`created_at` as `debit_created_at`,
    case
        when `ru`.`role_id` = 6 and `edc`.`idTransaction` is not null and `th`.`typeTransaction` = 'debit' then 'EntrepriseDebit'
        when `ru`.`role_id` = 6 and `edc`.`idTransaction` is null and `th`.`typeTransaction` = 'credit' then 'EntrepriseCredit'
        when `th`.`typeTransaction` = 'credit' then 'DirectCredit'
        when `th`.`typeTransaction` = 'debit' then 'DirectDebit'
        else 'Unknown'
    end as `operation_origin`
from
    ((`transaction_history` `th`
left join `emp_debit_credit` `edc` on
    (`th`.`idTransaction` = `edc`.`idTransaction`))
left join `role_users` `ru` on
    (`th`.`idUser` = `ru`.`user_id`
        and `ru`.`role_id` = 6))
order by
    `th`.`idTransaction`;
-- vue sur les opérations sur les crédits

-- vue sur les opérations de credits
create or replace
view v_credit_transactions as
select
	th.idTransaction as transactionId,
	th.idUser as userId,
	th.transaction_ref,
	th.montant as transactionAmount,
	th.typeTransaction,
	th.description as companyDescription,
	DATE(th.created_at) as transactionDate,
	th.created_at
from
	transaction_history as th
where
	th.typeTransaction = 'credit';

-- vue sur les opérations de débits
create or replace
view v_debit_transactions as
select
	th.idTransaction as transactionId,
	th.idUser as userId,
	th.transaction_ref,
	th.montant as transactionAmount,
	th.typeTransaction,
	th.description as companyDescription,
	DATE(th.created_at) as transactionDate,
	th.created_at,
	edc.idUser as employeeId,
	edc.montant as employeeDebitAmount,
	edc.description as employeeDescription,
	case
        when edc.idUser is null then th.idUser
		else null
	end as particularId,
    case
		when edc.idUser is null then th.montant
		else null
	end as particularDebitAmount
from
	transaction_history as th
left join emp_debit_credit as edc on
	th.idTransaction = edc.idTransaction
where
	th.typeTransaction = 'debit';

-- vue pour pour les invitations à participer à un qcm
create or replace
view v_invitations_sended as
select
	qi.idInvitation,
	qi.valid_from,
	qi.valid_until,
	qi.status as invitation_status,
	qi.custom_message,
	qi.idQCM,
	q.intituleQCM,
	q.descriptionQCM,
	q.prixUnitaire,
	qi.idEmployeur,
	c.customerName as nom_entreprise,
	ue.email as employeur_email,
	qi.idEmploye,
	ud.name as employe_nom,
	ud.firstName as employe_prenom,
	ud.email as employe_email,
    coalesce(camps.idInvitCamp, 'notassigned') as idInvitCamp,
	coalesce(camps.name, 'notassigned') as campaign_name
from
	qcm_invitations qi
join 
    users ue on
	qi.idEmployeur = ue.id
join 
    users ud on
	qi.idEmploye = ud.id
join 
    qcm q on
	qi.idQCM = q.idQCM
join 
    employes e on
	qi.idEmploye = e.idEmploye
join 
    customers c on
	e.idCustomer = c.idCustomer
left join 
    qcm_invit_camp_invitations camp_invit on
	qi.idInvitation = camp_invit.invitation_id
left join 
    qcm_invit_camps camps on
	camp_invit.invit_camp_id = camps.idInvitCamp;

-- listes des salles CFP et ETP (mbola mila verification id cfp or etp bdb
CREATE OR REPLACE VIEW v_list_salles AS
SELECT
	s.idSalle, s.salle_name, l.li_quartier AS salle_quartier, l.li_rue AS salle_rue, s.salle_image, s.idLieu,
    l.li_name AS lieu_name, l.idVille, l.idLieuType, l.idVilleCoded, v.ville, vc.ville_name AS ville_name_coded, vc.vi_code_postal, lt.lt_name, lp.idCustomer
FROM salles AS s
INNER JOIN lieux AS l ON s.idLieu = l.idLieu
INNER JOIN villes AS v ON l.idVille = v.idVille
INNER JOIN ville_codeds AS vc ON l.idVilleCoded = vc.id
INNER JOIN lieu_types AS lt ON l.idLieuType = lt.idLieuType
LEFT JOIN lieu_privates AS lp ON l.idLieu = lp.idLieu;

-- listes des lieux CFP et ETP
CREATE OR REPLACE VIEW v_liste_lieux AS
SELECT
	l.idLieu, l.li_name, l.idVille, l.idLieuType, l.idVilleCoded, v.ville, lt.lt_name, vc.ville_name AS ville_name_coded, vc.vi_code_postal, lp.idCustomer, pefc.date_added, pefc.idCfp, pefc.idEntreprise
FROM lieux AS l
INNER JOIN villes AS v ON l.idVille = v.idVille
INNER JOIN lieu_types AS lt ON l.idLieuType = lt.idLieuType
INNER JOIN ville_codeds AS vc ON l.idVilleCoded = vc.id
LEFT JOIN lieu_privates AS lp ON l.idLieu = lp.idLieu
LEFT JOIN place_etp_from_cfps AS pefc ON l.idLieu = pefc.idLieu;

-- listes des agences CFP et ETP
CREATE OR REPLACE VIEW v_liste_agences AS
SELECT
	ag.idAgence, ag.ag_name, ag.idVilleCoded, ag.idCustomer, vc.ville_name AS ville_name_coded, vc.vi_code_postal, vc.idVille, v.ville, c.customerName AS customer_name, c.nif AS customer_nif, c.stat AS customer_stat, c.rcs AS customer_rcs, c.description AS customer_description, c.logo AS customer_logo, c.customerPhone AS customer_phone, c.customerEmail AS customer_email
FROM agences AS ag
INNER JOIN ville_codeds AS vc ON ag.idVilleCoded = vc.id
INNER JOIN villes AS v ON vc.idVille = v.idVille
JOIN customers AS c ON ag.idCustomer = c.idCustomer;

-- liste des employes du "Groupe d'entreprise" seulement (mpiasan'ilay entreprise parent irery ihany no ato)
-- fa tsy miaraka @ zanany
CREATE OR REPLACE VIEW v_employe_groupes AS SELECT
    e.idEmploye,
    c.customerName AS customer_name,
    c.nif AS customer_nif,
    c.stat AS customer_stat,
    c.rcs AS customer_rcs,
    c.description AS customer_description,
    c.siteWeb AS customer_siteweb,
    c.logo AS customer_logo,
    c.customerEmail AS customer_email,
    c.customerPhone AS customer_phone,
    SUBSTRING(c.customerName, 1, 1) AS customer_initial_name,
    etp.idTypeEtp,
    e.idFonction,
    e.idCustomer,
    e.idSexe,
    e.idNiveau,
    SUBSTRING(u.name, 1, 1) AS emp_initial_name,
    u.name AS emp_name,
    u.firstName AS emp_firstname,
    u.matricule AS emp_matricule,
    u.phone AS emp_phone,
    u.photo AS emp_photo,
    u.dateNais AS emp_date_nais,
    u.email AS emp_email,
    u.cin AS emp_cin,
    s.sexe AS emp_sexe,
    f.fonction AS emp_fonction,
    ru.isActive AS emp_is_active,
    ru.user_is_in_service,
    ru.role_id,
    ru.hasRole AS emp_has_role
FROM
    employes AS e
INNER JOIN users AS u
ON
    e.idEmploye = u.id
INNER JOIN role_users AS ru
ON
    ru.user_id = e.idEmploye
INNER JOIN customers AS c
ON
    e.idCustomer = c.idCustomer
INNER JOIN entreprises AS etp
ON
    c.idCustomer = etp.idCustomer
INNER JOIN fonctions AS f
ON
    e.idFonction = f.idFonction
INNER JOIN sexes AS s
ON
    e.idSexe = s.idSexe
WHERE
    ru.role_id = 4 AND etp.idTypeEtp = 2
ORDER BY
    e.idEmploye ASC;

-- liste des employes du "Groupe d'entreprise" avec ses "enfants"
-- mpiasa ao @lay groupe + zanany (role_id = 4)
CREATE OR REPLACE VIEW v_union_emp_grps AS SELECT
    idEmploye,
    idEntreprise,
    idEntrepriseParent,
    etp_name_parent,
    etp_name,
    emp_initial_name,
    emp_name,
    emp_firstname,
    emp_email,
    emp_matricule,
    emp_phone,
    emp_photo,
    emp_cin,
    emp_sexe,
    emp_is_active,
    emp_has_role,
    user_is_in_service,
    role_id
FROM
    v_list_emp_grps AS vl
WHERE
    vl.role_id = 4
UNION
SELECT
    idEmploye,
    idCustomer AS idEntreprise,
    idCustomer AS idEntrepriseParent,
    customer_name AS etp_name_parent,
    "--" AS etp_name,
    emp_initial_name,
    emp_name,
    emp_firstname,
    emp_email,
    emp_matricule,
    emp_phone,
    emp_photo,
    emp_cin,
    emp_sexe,
    emp_is_active,
    emp_has_role,
    user_is_in_service,
    role_id
FROM
    v_employe_groupes AS vg
ORDER BY
    idEmploye ASC;


-- Eqivalent v_apprenant_etp_all fa etp group

CREATE OR REPLACE VIEW v_apprenant_etp_all_groups AS
SELECT users.id AS idEmploye,
	egp.idEntrepriseParent, 
	c_parent.customerName AS etp_name_parent, 
	c_parent.nif AS etp_nif_parent, 
	c_parent.stat AS etp_stat_parent, 
	c_parent.rcs AS etp_rcs_parent, 
	c_parent.description AS etp_description_parent, 
	c_parent.siteWeb AS etp_siteweb_parent, 
	c_parent.logo AS etp_logo_parent, c_parent.customerEmail AS etp_email_parent, 
	c_parent.customerPhone AS etp_phone_parent, 
	c_parent.customer_slogan AS etp_slogan_parent,
	cfp_etps.idEtp,
	cfp_etps.idCfp,
	cfp_etps.activiteEtp AS etp_is_active,
	cfp_etps.activiteCfp AS cfp_is_active,
	c_child.customerName AS etp_name,
	c_child.customerEmail AS etp_email,
	c_child.idTypeCustomer,
	SUBSTRING(users.name, 1, 1) AS emp_initial_name,
	users.name AS emp_name,
	users.firstName AS emp_firstname,
	users.email AS emp_email,
	users.matricule AS emp_matricule,
	users.phone AS emp_phone,
	users.cin AS emp_cin,
	users.photo AS emp_photo,
	e.idFonction,
	fct.fonction AS emp_fonction,
	users.user_addr_lot,
	users.user_addr_quartier,
	users.user_addr_rue,
	users.user_addr_code_postal,
	ru.user_is_in_service,
	ru.role_id,
	ru.isActive AS emp_is_active,
	ru.hasRole,
	c_emps.id_cfp,
	users.idVille,
	villes.ville,
	da.idProjet,
    da.id_cfp_appr,
	vp.dateDebut,
	vp.project_type,
	vp.dateFin,
	vp.idSalle,
	vp.salle_name,
	vp.salle_quartier,
	vp.salle_rue,
	vp.salle_code_postal,
	vp.idVille AS project_id_ville,
	vp.ville AS project_ville,
	vp.project_status,
	vp.modalite AS project_modality,
	vp.idModule,
	vp.module_name,
	MONTH(vp.dateDebut) AS project_month,
	MONTH(CURDATE()) AS current_month,
	ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AS p_prev_three_month,
	ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AS p_prev_six_month,
	ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AS p_prev_twelve_month,
	ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH) AS p_prev_tw_four_month,
	ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AS p_next_three_month,
	ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AS p_next_six_month,
	ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AS p_next_twelve_month,
	ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH) AS p_next_tw_four_month,
	CASE WHEN(vp.dateDebut < CURRENT_DATE AND vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month" WHEN(vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month" WHEN(vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AND vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month" WHEN(vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month" WHEN(vp.dateDebut >= CURRENT_DATE AND vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month" WHEN(vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)	AND vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month" WHEN(vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AND vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month" WHEN(vp.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND vp.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
	END p_id_periode

FROM cfp_etps 
INNER JOIN etp_groupeds AS egp ON  egp.idEntreprise = cfp_etps.idEtp
INNER JOIN customers AS c_parent ON egp.idEntrepriseParent = c_parent.idCustomer
INNER JOIN customers AS c_child ON egp.idEntreprise = c_child.idCustomer
INNER JOIN employes AS e ON egp.idEntreprise = e.idCustomer
INNER JOIN users ON e.idEmploye = users.id
INNER JOIN sexes ON e.idSexe = sexes.idSexe
INNER JOIN users AS u ON e.idEmploye = u.id
INNER JOIN role_users AS ru ON ru.user_id = u.id
INNER JOIN villes ON users.idVille = villes.idVille
INNER JOIN fonctions AS fct ON e.idFonction = fct.idFonction

LEFT JOIN detail_apprenants AS da ON da.idEmploye = e.idEmploye
LEFT JOIN v_projet_cfps AS vp ON vp.idProjet = da.idProjet
LEFT JOIN c_emps ON e.idEmploye = c_emps.idEmploye

ORDER BY e.idEmploye ASC;

CREATE OR REPLACE VIEW v_periode_groups AS SELECT
	va.idEmploye, va.idEtp, va.idCfp, va.idEntrepriseParent,va.id_cfp, va.etp_name, va.emp_name, va.emp_firstname, va.idFonction, va.emp_fonction, va.p_id_periode, va.project_id_ville, va.project_ville
FROM v_apprenant_etp_all_groups AS va
WHERE va.role_id = 4
AND va.p_id_periode != "null"
GROUP BY va.idEmploye, va.idEtp, va.idCfp,va.idEntrepriseParent, va.id_cfp, va.etp_name, va.emp_name, va.emp_firstname, va.idFonction, va.emp_fonction, va.p_id_periode, va.project_id_ville, va.ville
ORDER BY va.idEmploye ASC;


CREATE OR REPLACE VIEW v_apprenant_etp_all_filter_groups AS SELECT
	va.idEmploye, va.idEtp, va.idCfp, va.idEntrepriseParent,va.id_cfp, va.etp_name, va.emp_name, va.emp_firstname, va.idFonction, va.emp_fonction
FROM v_apprenant_etp_all_groups AS va
WHERE va.role_id = 4
GROUP BY va.idEmploye, va.idEtp, va.idCfp,va.idEntrepriseParent, va.id_cfp, va.etp_name, va.emp_name, va.emp_firstname, va.idFonction, va.emp_fonction
ORDER BY va.idEmploye ASC;

CREATE OR REPLACE VIEW v_apprenant_etp_alls2 AS
SELECT 
	D.idEmploye, 
    U.name AS emp_name, 
    U.firstName AS emp_firstname, 
    U.email AS emp_email, 
    U.matricule AS emp_matricule, 
    U.phone AS emp_phone, 
    U.cin AS emp_cin, 
    U.photo AS emp_photo,
    U.user_addr_lot, 
    U.user_addr_quartier, 
    U.user_addr_rue, U.user_addr_code_postal, 
    SUBSTRING(U.name, 1, 1) AS emp_initial_name, 
    F.fonction AS emp_fonction, 
    U.idVille, 
    V.ville, 
    VC.ville_name as project_ville,
    VC.vi_code_postal as project_code_postal,
    P.idVilleCoded as project_id_ville,
    M.moduleName as module_name, 
    C.customerName as cfp_name, 
    ETP.customerName as etp_name, 
    ETP.idCustomer as idEtp, 
    ETP.customerEmail as etp_email, 
    C.idCustomer as idCfp,
    M.idModule, 
    P.idProjet, 
    P.dateDebut, P.dateFin,CASE
		WHEN (P.project_is_archived = 1) THEN "Archivé"
		WHEN (P.project_is_trashed = 1) THEN "Supprimé"
		WHEN (P.project_is_closed = 1) THEN "Cloturé"
		WHEN (P.project_is_active = 1
								AND P.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (P.project_is_active = 0
								AND P.project_is_cancelled = 0
								AND P.project_is_repported = 0
								AND P.project_is_reserved = 0
								AND P.project_is_archived = 0
								AND P.project_is_trashed = 0) THEN "En préparation"
		WHEN (P.project_is_cancelled = 1) THEN "Annulé"
		WHEN (P.project_is_repported = 1) THEN "Reporté"
		WHEN (P.project_is_reserved = 1) THEN "Réservé"
		WHEN (P.project_is_active = 1
								AND P.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END as project_status, MD.modalite project_modality, MONTH(P.dateDebut) AS project_month,
	MONTH(CURDATE()) AS current_month,
	ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AS p_prev_three_month,
	ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AS p_prev_six_month,
	ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AS p_prev_twelve_month,
	ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH) AS p_prev_tw_four_month,
	ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AS p_next_three_month,
	ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AS p_next_six_month,
	ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AS p_next_twelve_month,
	ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH) AS p_next_tw_four_month,
	CASE WHEN(P.dateDebut < CURRENT_DATE AND P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month" WHEN(P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month" WHEN(P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AND P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month" WHEN(P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month" WHEN(P.dateDebut >= CURRENT_DATE AND P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month" WHEN(P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)	AND P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month" WHEN(P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AND P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month" WHEN(P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
	END p_id_periode
FROM detail_apprenants as D
JOIN projets as P ON P.idProjet = D.idProjet
JOIN modalites as MD ON MD.idModalite = P.idModalite
JOIN mdls as M ON M.idModule = P.idModule
JOIN customers as C ON C.idCustomer = M.idCustomer
JOIN users as U ON U.id = D.idEmploye
JOIN role_users as RU ON RU.user_id = U.id
JOIN employes as E ON E.idEmploye = U.id
JOIN customers as ETP ON ETP.idCustomer = E.idCustomer
JOIN villes as V ON V.idVille = U.idVille
JOIN ville_codeds as VC ON VC.id = P.idVilleCoded
JOIN fonctions as F ON E.idFonction = F.idFonction
WHERE RU.role_id = 4
AND P.project_is_trashed = 0;

-- liste projet-formateurs 
CREATE OR REPLACE VIEW v_projet_form_apprenants AS
 SELECT
 p.idProjet, p.idCustomer AS idCfp, p.dateDebut, p.dateFin, tp.type AS project_type, ADDDATE(p.dateDebut, INTERVAL -7 DAY) AS p_start_date_minus_7, ADDDATE(p.dateFin, INTERVAL 3 DAY) AS p_end_date_plus_3, p.project_is_active, i.idEtp, c.customerName AS etp_name, c.customerEmail AS etp_email, pf.idFormateur, p.project_is_trashed,
	CASE
		WHEN (p.project_is_archived = 1) THEN "Archivé"
		WHEN (p.project_is_trashed = 1) THEN "Supprimé"
		WHEN (p.project_is_closed = 1) THEN "Cloturé"
		WHEN (p.project_is_active = 1
								AND p.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (p.project_is_active = 0
								AND p.project_is_cancelled = 0
								AND p.project_is_repported = 0
								AND p.project_is_reserved = 0
								AND p.project_is_archived = 0
								AND p.project_is_trashed = 0) THEN "En préparation"
		WHEN (p.project_is_cancelled = 1) THEN "Annulé"
		WHEN (p.project_is_repported = 1) THEN "Reporté"
		WHEN (p.project_is_reserved = 1) THEN "Réservé"
		WHEN (p.project_is_active = 1
								AND p.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status
FROM projets AS p
JOIN type_projets AS tp ON p.idTypeProjet = tp.idTypeProjet
LEFT JOIN intras AS i ON i.idProjet = p.idProjet
LEFT JOIN customers AS c ON i.idEtp = c.idCustomer
LEFT JOIN project_forms AS pf ON p.idProjet = pf.idProjet
WHERE p.dateDebut IS NOT NULL;

CREATE OR REPLACE VIEW v_apprenant_etp_all_without_project AS
SELECT 
	CE.idEmploye,
	U.name AS emp_name, 
    U.firstName AS emp_firstname, 
    U.email AS emp_email, 
    U.matricule AS emp_matricule, 
    U.phone AS emp_phone, 
    U.cin AS emp_cin, 
    U.photo AS emp_photo,
    U.user_addr_lot, 
    U.user_addr_quartier, 
    U.user_addr_rue, U.user_addr_code_postal, 
    SUBSTRING(U.name, 1, 1) AS emp_initial_name, 
    F.fonction AS emp_fonction, 
    U.idVille, 
    V.ville, 
    null as project_ville,
    null as project_code_postal,
    null project_id_ville,
    null as module_name, 
    CFP.customerName as cfp_name, 
    ETP.customerName as etp_name, 
    ETP.idCustomer as idEtp, 
    ETP.customerEmail as etp_email, 
    CFP.idCustomer as idCfp,
    null as idModule, 
    null as idProjet, 
    null as dateDebut, 
    null as dateFin,
    null as project_status, null as project_modality, null AS project_month,
	MONTH(CURDATE()) AS current_month,
	null AS p_prev_three_month,
	null AS p_prev_six_month,
	null AS p_prev_twelve_month,
	null AS p_prev_tw_four_month,
	null AS p_next_three_month,
	null AS p_next_six_month,
	null AS p_next_twelve_month,
	null AS p_next_tw_four_month,
	null as  p_id_periode
FROM c_emps as CE
JOIN employes as E ON E.idEmploye = CE.idEmploye
JOIN users as U ON U.id = CE.idEmploye
JOIN role_users as RU ON RU.user_id = U.id
JOIN customers as ETP ON ETP.idCustomer = E.idCustomer
JOIN customers as CFP ON CE.id_cfp = CFP.idCustomer
JOIN villes as V ON V.idVille = U.idVille
JOIN fonctions as F ON E.idFonction = F.idFonction
WHERE RU.role_id = 4;

CREATE OR REPLACE VIEW v_apprenant_union AS 
SELECT 
	CE.idEmploye,
	U.name AS emp_name, 
    U.firstName AS emp_firstname, 
    U.email AS emp_email, 
    U.matricule AS emp_matricule, 
    U.phone AS emp_phone, 
    U.cin AS emp_cin, 
    U.photo AS emp_photo,
    U.user_addr_lot, 
    U.user_addr_quartier, 
    U.user_addr_rue, U.user_addr_code_postal, 
    SUBSTRING(U.name, 1, 1) AS emp_initial_name, 
    F.fonction AS emp_fonction, 
    U.idVille, 
    V.ville, 
    null as project_ville,
    null as project_code_postal,
    null project_id_ville,
    null as module_name, 
    CFP.customerName as cfp_name, 
    ETP.customerName as etp_name, 
    ETP.idCustomer as idEtp, 
    ETP.customerEmail as etp_email, 
    CFP.idCustomer as idCfp,
    null as idModule, 
    null as idProjet, 
    null as dateDebut, 
    null as dateFin,
    null as project_status, null as project_modality, null AS project_month,
	MONTH(CURDATE()) AS current_month,
	null AS p_prev_three_month,
	null AS p_prev_six_month,
	null AS p_prev_twelve_month,
	null AS p_prev_tw_four_month,
	null AS p_next_three_month,
	null AS p_next_six_month,
	null AS p_next_twelve_month,
	null AS p_next_tw_four_month,
	null as  p_id_periode
FROM c_emps as CE
JOIN employes as E ON E.idEmploye = CE.idEmploye
JOIN users as U ON U.id = CE.idEmploye
JOIN role_users as RU ON RU.user_id = U.id
JOIN customers as ETP ON ETP.idCustomer = E.idCustomer
JOIN customers as CFP ON CE.id_cfp = CFP.idCustomer
JOIN villes as V ON V.idVille = U.idVille
JOIN fonctions as F ON E.idFonction = F.idFonction
WHERE RU.role_id = 4
UNION
SELECT 
	D.idEmploye, 
    U.name AS emp_name, 
    U.firstName AS emp_firstname, 
    U.email AS emp_email, 
    U.matricule AS emp_matricule, 
    U.phone AS emp_phone, 
    U.cin AS emp_cin, 
    U.photo AS emp_photo,
    U.user_addr_lot, 
    U.user_addr_quartier, 
    U.user_addr_rue, U.user_addr_code_postal, 
    SUBSTRING(U.name, 1, 1) AS emp_initial_name, 
    F.fonction AS emp_fonction, 
    U.idVille, 
    V.ville, 
    VC.ville_name as project_ville,
    VC.vi_code_postal as project_code_postal,
    P.idVilleCoded as project_id_ville,
    M.moduleName as module_name, 
    C.customerName as cfp_name, 
    ETP.customerName as etp_name, 
    ETP.idCustomer as idEtp, 
    ETP.customerEmail as etp_email, 
    C.idCustomer as idCfp,
    M.idModule, 
    P.idProjet, 
    P.dateDebut, P.dateFin,CASE
		WHEN (P.project_is_archived = 1) THEN "Archivé"
		WHEN (P.project_is_trashed = 1) THEN "Supprimé"
		WHEN (P.project_is_closed = 1) THEN "Cloturé"
		WHEN (P.project_is_active = 1
								AND P.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (P.project_is_active = 0
								AND P.project_is_cancelled = 0
								AND P.project_is_repported = 0
								AND P.project_is_reserved = 0
								AND P.project_is_archived = 0
								AND P.project_is_trashed = 0) THEN "En préparation"
		WHEN (P.project_is_cancelled = 1) THEN "Annulé"
		WHEN (P.project_is_repported = 1) THEN "Reporté"
		WHEN (P.project_is_reserved = 1) THEN "Réservé"
		WHEN (P.project_is_active = 1
								AND P.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END as project_status, MD.modalite project_modality, MONTH(P.dateDebut) AS project_month,
	MONTH(CURDATE()) AS current_month,
	ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AS p_prev_three_month,
	ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AS p_prev_six_month,
	ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AS p_prev_twelve_month,
	ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH) AS p_prev_tw_four_month,
	ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH) AS p_next_three_month,
	ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AS p_next_six_month,
	ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AS p_next_twelve_month,
	ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH) AS p_next_tw_four_month,
	CASE WHEN(P.dateDebut < CURRENT_DATE AND P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH)) THEN "prev_3_month" WHEN(P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -3 MONTH) AND P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH)) THEN "prev_6_month" WHEN(P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -6 MONTH) AND P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH)) THEN "prev_12_month" WHEN(P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL -12 MONTH) AND P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL -24 MONTH)) THEN "prev_24_month" WHEN(P.dateDebut >= CURRENT_DATE AND P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)) THEN "next_3_month" WHEN(P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 3 MONTH)	AND P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH)) THEN "next_6_month" WHEN(P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 6 MONTH) AND P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH)) THEN "next_12_month" WHEN(P.dateDebut >= ADDDATE(CURRENT_DATE, INTERVAL 12 MONTH) AND P.dateDebut < ADDDATE(CURRENT_DATE, INTERVAL 24 MONTH)) THEN "next_24_month"
	END p_id_periode
FROM detail_apprenants as D
JOIN projets as P ON P.idProjet = D.idProjet
JOIN modalites as MD ON MD.idModalite = P.idModalite
JOIN mdls as M ON M.idModule = P.idModule
JOIN customers as C ON C.idCustomer = M.idCustomer
JOIN users as U ON U.id = D.idEmploye
JOIN role_users as RU ON RU.user_id = U.id
JOIN employes as E ON E.idEmploye = U.id
JOIN customers as ETP ON ETP.idCustomer = E.idCustomer
JOIN villes as V ON V.idVille = U.idVille
JOIN ville_codeds as VC ON VC.id = P.idVilleCoded
JOIN fonctions as F ON E.idFonction = F.idFonction
WHERE RU.role_id = 4
AND P.project_is_trashed = 0;

CREATE OR REPLACE VIEW v_attestation_projet_employe AS
SELECT u.name, u.firstName, u.email, m.moduleName, p.dateDebut, p.dateFin, cu.idCustomer as idEtp, cu.logo,c.idCustomer as idCfp, cu.customerName as etpName,p.idProjet, att.idAttestation, att.file_path, att.file_name, att.number_attestation, att.idEmploye
FROM attestations as att
JOIN projets as p ON p.idProjet = att.idProjet
JOIN mdls as m ON m.idModule = p.idModule
JOIN employes as e ON e.idEmploye = att.idEmploye
JOIN customers as cu ON cu.idCustomer = e.idCustomer
join users as u ON u.id = e.idEmploye
JOIN customers as c ON c.idCustomer = att.idCfp;

-- Atao ahoana ilay "Sous-traitants"?!!!!!!!!!!!!!!!!!
-- listes Résultats EVALUATION à FROID 
CREATE OR REPLACE VIEW v_result_evaluation_froids AS
SELECT
    ef.*,
    p.dateDebut AS projet_date_debut,
    p.dateFin AS projet_date_fin,
    p.idModule,
    c.customerName AS cfp_name,
    c.customerEmail AS cfp_email,
    c.logo AS cfp_logo,
    c.idCustomer AS idCfp,
    mdls.moduleName AS module_name,
    mdls.module_image,
    CASE WHEN ef.note = 0 THEN "Non" WHEN ef.note = 1 THEN "Oui" WHEN ef.note = 2 THEN "Partiellement" END note_libelle,
	CASE WHEN ef.general_recomand = 0 THEN "Non" WHEN ef.general_recomand = 1 THEN "Oui" END general_recomand_libelle,
    psc.idSubContractor,
    cs.customerName AS sub_name,
    cs.customerEmail AS sub_email,
    i.idEtp,
    ce.customerName AS etp_name,
    ce.customerEmail AS etp_email,
    ce.logo AS etp_logo,
    itr.idCfp AS idCfpInter,
    tp.type AS projet_type,
    u.name AS emp_name,
    u.firstName AS emp_firstname,
    u.email AS emp_email,
    u.phone AS emp_phone,
    u.photo AS emp_photo,
    qc.quizz_cold_name,
    qc.idQuizzType,
    qt.quizz_name AS quiz_type_name
FROM
    `eval_froids` AS ef
INNER JOIN projets AS p
ON
    ef.idProjet = p.idProjet
    INNER JOIN type_projets AS tp ON p.idTypeProjet = tp.idTypeProjet
INNER JOIN customers AS c
ON
    p.idCustomer = c.idCustomer
    INNER JOIN quizz_colds AS qc ON ef.idQuizzCold = qc.id
    INNER JOIN quizz_types AS qt ON qc.idQuizzType = qt.id
INNER JOIN mdls ON p.idModule = mdls.idModule
INNER JOIN users AS u ON ef.idEmploye = u.id
LEFT JOIN project_sub_contracts AS psc ON p.idProjet = psc.idProjet
LEFT JOIN customers AS cs ON psc.idSubContractor = cs.idCustomer
LEFT JOIN intras AS i ON p.idProjet = i.idProjet
LEFT JOIN customers AS ce ON i.idEtp = ce.idCustomer
LEFT JOIN inters AS itr ON p.idProjet = itr.idProjet;

CREATE OR REPLACE VIEW v_collaboration_cfp_particuliers AS
SELECT
 cp.idCfp, cp.idParticulier, cp.is_active_cfp, cp.is_active_particulier, cp.date_collaboration, up.name AS part_name, up.firstName AS part_firstname, up.email AS part_email, up.matricule AS part_matricule, up.cin AS part_cin, up.phone AS part_phone, up.photo AS part_photo, up.user_is_deleted AS part_is_deleted, c.customerName AS cfp_name, c.customerEmail AS cfp_email, c.customerPhone AS cfp_phone, c.nif AS cfp_nif, c.stat AS cfp_stat, c.rcs AS cfp_rcs, c.description AS cfp_description, c.logo AS cfp_logo, c.siteWeb AS cfp_site_web, c.idTypeCustomer, c.idVilleCoded, tc.typeCustomer, SUBSTRING(up.name, 1, 1) AS part_initial_name, SUBSTRING(c.customerName, 1, 1) AS cfp_initial_name
FROM cfp_particuliers AS cp
LEFT JOIN users AS up ON cp.idParticulier = up.id
LEFT JOIN customers AS c ON cp.idCfp = c.idCustomer
LEFT JOIN type_customers AS tc ON c.idTypeCustomer = tc.idTypeCustomer;

CREATE OR REPLACE VIEW v_badge_projet AS
SELECT 
    badges.idBadge,
    projets.idProjet,
    projets.dateDebut,
    projets.dateFin,
    projets.project_reference,
    intras.idEtp,
    intras.idCfp,  
    CASE
        WHEN projets.project_is_archived = 1 THEN "Archivé"
        WHEN projets.project_is_trashed = 1 THEN "Supprimé"
        WHEN projets.project_is_closed = 1 THEN "Clôturé"
        WHEN projets.project_is_active = 1 AND projets.dateFin < CURRENT_DATE THEN "Terminé"
        WHEN projets.project_is_active = 0 
            AND projets.project_is_cancelled = 0
            AND projets.project_is_repported = 0
            AND projets.project_is_reserved = 0
            AND projets.project_is_archived = 0
            AND projets.project_is_trashed = 0 THEN "En préparation"
        WHEN projets.project_is_cancelled = 1 THEN "Annulé"
        WHEN projets.project_is_repported = 1 THEN "Reporté"
        WHEN projets.project_is_reserved = 1 THEN "Réservé"
        WHEN projets.project_is_active = 1 AND projets.dateDebut > CURRENT_DATE THEN "Planifié"
        ELSE "En cours"
    END AS project_status,
    SUBSTRING(customers.customerName, 1, 1) AS etp_initial_name,
    customers.customerName AS etp_name,
    projets.idModule,
    mdls.moduleName AS module_name,
	mdls.module_image
FROM projets
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN entreprises ON intras.idEtp = entreprises.idCustomer
LEFT JOIN customers ON entreprises.idCustomer = customers.idCustomer
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN badges ON badges.idModule = mdls.idModule
WHERE projets.dateDebut IS NOT NULL;


CREATE OR REPLACE VIEW v_evaluation_alls AS
SELECT eval_chauds.idProjet,
    eval_chauds.idEmploye,
    eval_chauds.note,
    eval_chauds.com1,
    eval_chauds.com2,
    eval_chauds.temoignage,
    eval_chauds.generalApreciate,
  eval_chauds.idExaminer,
  eval_chauds.idValComment,
    questions.idQuestion,
    questions.question,
    questions.idTypeQuestion,
    type_questions.typeQuestion,
    users.name AS name_examiner,
    users.firstName AS firstname_examiner
FROM eval_chauds
INNER JOIN questions ON eval_chauds.idQuestion = questions.idQuestion
INNER JOIN type_questions ON questions.idTypeQuestion = type_questions.idTypeQuestion
INNER JOIN users ON eval_chauds.idExaminer = users.id;

 CREATE OR REPLACE VIEW v_general_note_evaluation AS
SELECT eval_chauds.idProjet,
    eval_chauds.idEmploye,
    eval_chauds.note,
    eval_chauds.com1,
    eval_chauds.com2,
    eval_chauds.temoignage,
    eval_chauds.generalApreciate,
  eval_chauds.idExaminer,
    questions.idQuestion,
    questions.question,
    questions.idTypeQuestion,
    type_questions.typeQuestion,
    users.name,
    users.firstName
FROM eval_chauds
INNER JOIN questions ON eval_chauds.idQuestion = questions.idQuestion
INNER JOIN type_questions ON questions.idTypeQuestion = type_questions.idTypeQuestion
INNER JOIN users ON eval_chauds.idExaminer = users.id
GROUP BY eval_chauds.idProjet, eval_chauds.idEmploye;


CREATE OR REPLACE VIEW v_images AS SELECT
    img.idImages,
    img.idProjet,
    img.id_added_by,
    ru.role_id,
    roles.roleName AS role_name_adder,
    img.nomImage AS img_name,
    img.url AS img_url,
    img.path AS img_path,
	img.mediaType AS img_media_type,
    img.description AS img_description,
    img.created_at AS img_created_at,
    SUBSTRING(u.name, 1, 1) AS owner_initial_name,
    u.name AS owner_name,
    u.firstName AS owner_firstname,
    u.email AS owner_email,
    u.cin AS owner_cin,
    u.phone AS owner_phone,
    u.photo AS owner_photo,
    p.projectName AS project_name,
    p.dateDebut AS project_start_date,
    p.dateFin AS project_end_date,
    p.project_description,
    p.idModule,
    mdl.moduleName AS module_name,
    p.idCustomer,
    p.idDossier,
    ds.nomDossier AS folder_name,
    ds.note AS folder_note
FROM
    images AS img
LEFT JOIN users AS u
ON
    img.id_added_by = u.id
LEFT JOIN projets AS p
ON
    img.idProjet = p.idProjet
LEFT JOIN mdls AS mdl
ON
    p.idModule = mdl.idModule
LEFT JOIN dossiers AS ds
ON
    p.idDossier = ds.idDossier
LEFT JOIN role_users AS ru
ON
    img.id_added_by = ru.user_id
LEFT JOIN roles ON ru.role_id = roles.id
ORDER BY
    img.idImages
DESC;


CREATE OR REPLACE VIEW v_modules  AS 
SELECT 
  mdls.idCustomer AS idCustomer,
  mdls.idModule AS idModule,
  mdls.module_image AS module_image,
  mdls.reference AS reference,
  mdls.moduleName AS moduleName,
  mdls.module_tag AS module_tag,
  mdls.module_subtitle AS module_subtitle,
  mdls.description AS description,
  mdls.minApprenant AS minApprenant,
  mdls.maxApprenant AS maxApprenant,
  mdls.dureeJ AS dureeJ,
  mdls.dureeH AS dureeH,
  mdls.moduleStatut AS moduleStatut,
  mdls.idTypeModule AS idTypeModule,
  mdls.module_is_complete AS module_is_complete,
  mdls.is_public AS is_public,
  mdl.prix AS prix,
  mdl.prixGroupe AS prixGroupe,
  substr(customers.customerName,1,1) AS customer_initial_name,
  customers.customerName AS customer_name,
  customers.logo AS logo,
  mdls.idDomaine AS idDomaine,	
  domaine_formations.nomDomaine AS nomDomaine,
  mdls.idLevel AS idLevel,
  module_levels.module_level_name AS module_level_name,
  mdls.idTypeModule AS id_type_module,
  tm.typeModule AS type_module
FROM mdls
INNER JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN modules mdl ON mdls.idModule = mdl.idModule
LEFT JOIN module_internes mdli ON mdls.idModule = mdli.idModule
LEFT JOIN customers ON mdls.idCustomer = customers.idCustomer
LEFT JOIN module_levels ON mdls.idLevel = module_levels.idLevel
LEFT JOIN type_modules tm ON mdls.idTypeModule = tm.idTypeModule;

select * from v_modules
join employes as e on e.idCustomer = v_modules.idCustomer;
-- Reservation cfp
CREATE OR REPLACE VIEW v_reservations_cfp AS
SELECT 
    I.id,
	I.idProjet,
	I.idEtp,
	I.nbPlaceReserved,
	c.customerName AS etp_name,
	c.customerEmail AS etp_email,
	c.logo AS etp_logo,
	c.customerPhone AS etp_phone,
	c.customer_addr_lot AS etp_addr_lot,
	p.project_reference,
	p.dateDebut AS project_start_date,
	p.dateFin AS project_end_date,
	p.idCustomer AS project_idCfp,
	p.project_description,
	tp.type AS project_type,
	vc.ville_name AS project_ville,
	m.moduleName AS project_module_name,
	m.module_image AS project_module_logo,
	df.nomDomaine AS project_module_domaine_name 
FROM inter_entreprises AS I
INNER JOIN customers AS c ON I.idEtp = c.idCustomer
INNER JOIN projets AS p ON I.idProjet = p.idProjet
INNER JOIN mdls AS m ON p.idModule = m.idModule
INNER JOIN ville_codeds AS vc ON p.idVilleCoded = vc.id 
INNER JOIN type_projets AS tp ON p.idTypeProjet = tp.idTypeProjet
INNER JOIN domaine_formations AS df ON m.idDomaine = df.idDomaine;

-- Reservation Entreprises
CREATE OR REPLACE VIEW v_reservations_etp AS
SELECT 
   	I.id,
   	I.idProjet,
  	I.idEtp,
  	I.nbPlaceReserved,
   	c.customerName AS cfp_name,
   	c.customerEmail AS cfp_email,
   	c.customerPhone AS cfp_phone,
   	m.moduleName AS project_module_name,
	m.module_image AS project_module_logo,
	p.dateDebut AS project_start_date,
	p.dateFin AS project_end_date,
	p.project_reference,
	p.project_description,
	df.nomDomaine AS project_module_domaine_name,
	vc.ville_name AS project_ville,
	tp.type AS project_type
FROM inter_entreprises AS I
INNER JOIN projets AS p ON I.idProjet = p.idProjet
INNER JOIN customers AS c ON p.idCustomer = c.idCustomer
INNER JOIN mdls AS m ON p.idModule = m.idModule
INNER JOIN domaine_formations AS df ON m.idDomaine = df.idDomaine
INNER JOIN type_projets AS tp ON p.idTypeProjet = tp.idTypeProjet
INNER JOIN ville_codeds AS vc ON p.idVilleCoded = vc.id ;


CREATE OR REPLACE VIEW v_reward_results AS SELECT
    mr.id AS id_module_reward,
    mr.name AS reward_name,
    mr.expired_date,
    mr.description AS reward_description,
    mr.place_number AS reward_place_number,
    mr.reduction AS reward_reduction,
    mr.normal_price_per_place,
    SUM(
        mr.normal_price_per_place * mr.reduction / 100
    ) * mr.place_number AS price_reduction,
    IF(
        mr.id_reward_type = 2,
        SUM(
            mr.normal_price_per_place -(
                mr.reduction * mr.normal_price_per_place / 100
            )
        ) * mr.place_number,
        mr.normal_price_per_place * mr.place_number
    ) AS price_with_reduction,
    mr.idCfp,
    cst.customerName AS cfp_name,
    cst.customerEmail AS cfp_email,
    cst.logo AS cfp_logo,
    cst.customerPhone AS cfp_phone_number,
    mr.idEtp,
    mr.id_reward_scope,
    rs.name AS reward_scope_name,
    mr.id_reward_type,
    rt.name AS reward_type_name,
    users.name AS cfp_referent_name,
    users.firstName AS cfp_referent_firstname,
    users.photo AS cfp_referent_photo
FROM
    `module_rewards` AS mr
JOIN reward_scopes AS rs
ON
    mr.id_reward_scope = rs.id
JOIN reward_types AS rt
ON
    mr.id_reward_type = rt.id
JOIN customers AS cst
ON
    mr.idCfp = cst.idCustomer
JOIN users ON mr.idCfp = users.id
GROUP BY
    mr.id;

	CREATE OR REPLACE VIEW v_reward_results_cfp AS SELECT
    mr.id AS id_module_reward,
    mr.name AS reward_name,
    mr.expired_date,
    mr.description AS reward_description,
    mr.place_number AS reward_place_number,
    mr.reduction AS reward_reduction,
    mr.normal_price_per_place,
    SUM(
        mr.normal_price_per_place * mr.reduction / 100
    ) * mr.place_number AS price_reduction,
    IF(
        mr.id_reward_type = 2,
        SUM(
            mr.normal_price_per_place -(
                mr.reduction * mr.normal_price_per_place / 100
            )
        ) * mr.place_number,
        mr.normal_price_per_place * mr.place_number
    ) AS price_with_reduction,
    mr.idCfp,
    cst.customerName AS etp_name,
    cst.customerEmail AS etp_email,
    mr.idEtp,
    mr.id_reward_scope,
    rs.name AS reward_scope_name,
    rs.description AS reward_scope_description,
    mr.id_reward_type,
    rt.name AS reward_type_name
FROM
    `module_rewards` AS mr
JOIN reward_scopes AS rs
ON
    mr.id_reward_scope = rs.id
JOIN reward_types AS rt
ON
    mr.id_reward_type = rt.id
JOIN customers AS cst
ON
    mr.idEtp = cst.idCustomer
GROUP BY
    mr.id;

CREATE OR REPLACE VIEW v_skill_matrix_results AS
SELECT
    skm.id AS skill_matrix_id,
    skm.idProjet,
    skm.skill_score_before,
    skm.skill_score_after,
    skm.id_module_skill,
    ms.name AS module_skill_name,
    skm.idEmploye,
    users.name AS emp_name,
    users.firstName AS emp_firstname,
    users.photo AS emp_photo
FROM
    skill_matrix AS skm
JOIN users ON skm.idEmploye = users.id
JOIN module_skills AS ms
ON
    skm.id_module_skill = ms.id
ORDER BY
    skm.id ASC;

CREATE OR REPLACE VIEW v_skill_intras AS SELECT
    intras.idProjet,
    p.projectName AS project_name,
    p.dateDebut AS start_date,
    p.dateFin AS end_date,
    intras.idCfp,
    cst.customerName AS cfp_name,
    cst.customerEmail AS cfp_email,
    cst.logo AS cfp_logo,
    intras.idEtp,
    cste.customerName AS etp_name,
    cste.customerEmail AS etp_email,
    cste.logo AS etp_logo,
    p.idModule,
    mdls.moduleName AS module_name,
    mdls.description AS module_description,
    mdls.module_image,
    CASE WHEN(p.project_is_archived = 1) THEN "Archivé" WHEN(p.project_is_trashed = 1) THEN "Supprimé" WHEN(p.project_is_closed = 1) THEN "Cloturé" WHEN(
        p.project_is_active = 1 AND p.dateFin < CURRENT_DATE
    ) THEN "Terminé" WHEN(
        p.project_is_active = 0 AND p.project_is_cancelled = 0 AND p.project_is_repported = 0 AND p.project_is_reserved = 0 AND p.project_is_archived = 0 AND p.project_is_trashed = 0
    ) THEN "En préparation" WHEN(p.project_is_cancelled = 1) THEN "Annulé" WHEN(p.project_is_repported = 1) THEN "Reporté" WHEN(p.project_is_reserved = 1) THEN "Réservé" WHEN(
        p.project_is_active = 1 AND p.dateDebut > CURRENT_DATE
    ) THEN "Planifié" ELSE "En cours"
END project_status
FROM
    intras
JOIN projets AS p
ON
    intras.idProjet = p.idProjet
JOIN mdls ON p.idModule = mdls.idModule
JOIN customers AS cst
ON
    intras.idCfp = cst.idCustomer
JOIN customers AS cste
ON
    intras.idEtp = cste.idCustomer
ORDER BY
    intras.idProjet ASC;

CREATE OR REPLACE VIEW v_skill_inters AS SELECT
    ie.idProjet,
    p.projectName AS project_name,
    p.dateDebut AS start_date,
    p.dateFin AS end_date,
    ie.idEtp,
    cste.customerName AS etp_name,
    cste.customerEmail AS etp_email,
    cste.logo AS etp_logo,
    p.idCustomer AS idCfp,
    cst.customerName AS cfp_name,
    cst.customerEmail AS cfp_email,
    cst.logo AS cfp_logo,
    p.idModule,
    mdls.moduleName AS module_name,
    mdls.description AS module_description,
    mdls.module_image,
    CASE WHEN(p.project_is_archived = 1) THEN "Archivé" WHEN(p.project_is_trashed = 1) THEN "Supprimé" WHEN(p.project_is_closed = 1) THEN "Cloturé" WHEN(
        p.project_is_active = 1 AND p.dateFin < CURRENT_DATE
    ) THEN "Terminé" WHEN(
        p.project_is_active = 0 AND p.project_is_cancelled = 0 AND p.project_is_repported = 0 AND p.project_is_reserved = 0 AND p.project_is_archived = 0 AND p.project_is_trashed = 0
    ) THEN "En préparation" WHEN(p.project_is_cancelled = 1) THEN "Annulé" WHEN(p.project_is_repported = 1) THEN "Reporté" WHEN(p.project_is_reserved = 1) THEN "Réservé" WHEN(
        p.project_is_active = 1 AND p.dateDebut > CURRENT_DATE
    ) THEN "Planifié" ELSE "En cours"
END project_status
FROM
    inter_entreprises AS ie
JOIN projets AS p
ON
    ie.idProjet = p.idProjet
JOIN mdls ON p.idModule = mdls.idModule
JOIN customers AS cst
ON
    p.idCustomer = cst.idCustomer
JOIN customers AS cste
ON
    ie.idEtp = cste.idCustomer
ORDER BY
    ie.idProjet ASC;

-- --apprenants formateurs
CREATE OR REPLACE VIEW v_trainer_learners AS SELECT
	da.idProjet, p.project_title, p.projectName AS project_name, p.dateDebut AS project_start_date, p.dateFin AS project_end_date, da.idEmploye, users.name AS emp_name, users.firstName AS emp_firstname, users.email AS emp_email, users.photo AS emp_photo, p.idModule, mdls.moduleName AS module_name, mdls.module_image, mdls.description AS module_description, cst.customerName AS etp_name, cst.customerEmail AS etp_email, cst.logo AS etp_logo
FROM detail_apprenants AS da
JOIN users ON da.idEmploye = users.id
JOIN projets AS p ON da.idProjet = p.idProjet
JOIN mdls ON p.idModule = mdls.idModule
JOIN employes AS emp ON da.idEmploye = emp.idEmploye
JOIN customers AS cst ON emp.idCustomer = cst.idCustomer
UNION
SELECT
	da.idProjet, p.project_title, p.projectName AS project_name, p.dateDebut AS project_start_date, p.dateFin AS project_end_date, da.idEmploye, users.name AS emp_name, users.firstName AS emp_firstname, users.email AS emp_email, users.photo AS emp_photo, p.idModule, mdls.moduleName AS module_name, mdls.module_image, mdls.description AS module_description, cst.customerName AS etp_name, cst.customerEmail AS etp_email, cst.logo AS etp_logo
FROM detail_apprenant_inters AS da
JOIN users ON da.idEmploye = users.id
JOIN projets AS p ON da.idProjet = p.idProjet
JOIN mdls ON p.idModule = mdls.idModule
JOIN employes AS emp ON da.idEmploye = emp.idEmploye
JOIN customers AS cst ON emp.idCustomer = cst.idCustomer;
-- --end apprenants formateurs

CREATE OR REPLACE VIEW v_bon_commande AS
SELECT
	bc.idBC,
	bc.idDevis,
	bcs.idStatus,
	bcs.status_name,
	bcs.status_color,
    i.invoice_number AS numero_devis,
    bc.numero AS numero_bc,
    i.invoice_total_amount AS montant_devis,
    bc.montant AS montant_bc,
    i.invoice_date AS date_devis,
    bc.date AS date_bc,
    cu.idCustomer as idEtp,
    cu.customerName as etp_name,
    cu.customerEmail as etp_email,
    c.contact_name,
    c.contact_mail,
    c.contact_phone,
	bc.idCfp
FROM bon_commandes bc
JOIN invoices i ON bc.idDevis = i.idInvoice
JOIN customers cu ON i.idEntreprise = cu.idCustomer
JOIN bc_status bcs ON bc.idStatus = bcs.idStatus
LEFT JOIN bc_contacts c ON bc.idContact = c.idContact;

-- FMFP
CREATE OR REPLACE VIEW v_fmfp AS 
SELECT 
   fp.id,
   fp.type,
   fp.idCfp,
   fp.idEtp,
   fp.code,
   fp.first_deposit,
   fp.date_first_deposit,
   fp.second_deposit,
   fp.date_second_deposit,
   fp.requested_amount,
   fp.approved_amount,
   fp.request_date,
   fp.start_date,
   fp.end_date,
   fp.idStatus,
   fs.status_name,
   ftp.type_description AS type_fmfp_description,
   ftp.type AS type_fmfp,
   cu.customerName AS etp_name,
   cu.customerEmail AS etp_email,
   cu.customerPhone AS etp_phone,
   ap.name AS ap_name
FROM fmfp_projects fp
JOIN fmfp_type_project AS ftp ON fp.type = ftp.idType
JOIN fmfp_status AS fs ON fp.idStatus = fs.idStatus
JOIN customers cu ON fp.idEtp = cu.idCustomer
LEFT JOIN fmfp_ap_type AS ap ON ap.idfmfp = fp.id;

CREATE OR REPLACE VIEW `v_cfp_formateurs`  AS SELECT `cf`.`idFormateur` AS `idFormateur`, `cf`.`idCfp` AS `idCfp`, `cf`.`dateCollaboration` AS `dateCollaboration`, `cf`.`isActiveFormateur` AS `isActiveFormateur`, `cf`.`isActiveCfp` AS `isActiveCfp`, substr(`uf`.`name`,1,1) AS `form_initial_name`, `uf`.`name` AS `form_name`, `uf`.`firstName` AS `form_firstname`, `uf`.`email` AS `form_email`, `uf`.`photo` AS `form_photo`, `uf`.`phone` AS `form_phone`, `uf`.`matricule` AS `form_matricule`, `uf`.`cin` AS `form_cin`, substr(`cc`.`customerName`,1,1) AS `cfp_initial_name`, `cc`.`customerName` AS `cfp_name`, `cc`.`nif` AS `cfp_nif`, `cc`.`stat` AS `cfp_stat`, `cc`.`rcs` AS `cfp_rcs`, `cc`.`customerEmail` AS `cfp_email`, `cc`.`customerPhone` AS `cfp_phone`, `cc`.`description` AS `cfp_description`, `cc`.`siteWeb` AS `cfp_siteweb`, `cc`.`customer_slogan` AS `cfp_slogan`, `cc`.`logo` AS `cfp_logo` FROM ((`cfp_formateurs` `cf` join `users` `uf` on(`cf`.`idFormateur` = `uf`.`id`)) join `customers` `cc` on(`cf`.`idCfp` = `cc`.`idCustomer`)) ORDER BY `cf`.`idFormateur` ASC ;

CREATE OR REPLACE VIEW v_seances_materiel AS
SELECT seances.idSeance,
	seances.id_google_seance,
	seances.dateSeance,
	seances.heureDebut,
	seances.heureFin,
	seances.idProjet,
	TIMEDIFF(seances.heureFin,seances.heureDebut) AS intervalle_raw,
	type_projets.type AS project_type,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	internes.idEtp, 
	intras.idEtp AS idEtp_intra, 
	inter_entreprises.idEtp AS idEtp_inter, 
	projets.idSalle,
	salles.salle_name,
	lieux.li_quartier AS salle_quartier,
	lieux.li_rue AS salle_rue,
	vc.vi_code_postal AS salle_code_postal,
	projets.idCustomer AS idCfp,
	projets.idTypeProjet,
	projets.referenceEtp AS project_reference_etp,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.projectName AS project_name,
	projets.project_is_active,
	projets.project_is_reserved,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	projets.dateDebut AS project_date_debut,
	projets.dateFin AS project_date_fin,
    projets.idVilleCoded,
	vc.idVille,
	villes.ville,
	projets.idModule,
	mdls.moduleName AS module_name,
	mdls.module_subtitle,
    materials.name AS materiel_name,
    project_materials.number AS material_number,
    materials.stock_number AS material_stock,
    material_types.name AS material_types
	
FROM seances
INNER JOIN projets ON seances.idProjet = projets.idProjet
INNER JOIN salles ON projets.idSalle = salles.idSalle
INNER JOIN lieux ON salles.idLieu = lieux.idLieu
INNER JOIN ville_codeds AS vc ON projets.idVilleCoded = vc.id
INNER JOIN villes ON vc.idVille = villes.idVille
INNER JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN internes ON internes.idProjet = projets.idProjet
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inter_entreprises ON inter_entreprises.idProjet = projets.idProjet
LEFT JOIN project_materials ON project_materials.project_id = projets.idProjet
LEFT JOIN materials ON materials.id = project_materials.material_id
LEFT JOIN material_types ON material_types.id = materials.material_type_id
WHERE projets.dateDebut IS NOT NULL;

CREATE OR REPLACE VIEW v_projects AS
SELECT projets.idProjet,
    projets.idBc,
    projets.referenceEtp,
    projets.projectName AS project_name,
    projets.dateDebut,
    projets.dateFin,
	projets.taxe,
	projets.project_reference,
	projets.project_title,
	projets.project_description,
	projets.project_is_reserved,
	projets.project_is_active,
	projets.project_is_trashed,
	projets.project_is_cancelled,
	projets.project_is_repported,
	projets.project_is_closed,
	projets.project_price_pedagogique,
	projets.project_price_annexe,
	projets.total_ht,
	projets.total_ttc,
	projets.total_ht_sub_contractor,
	projets.total_ttc_sub_contractor,
	projets.idDossier,
	projets.link,
	projets.secret_code,
	projets.idTypeProjet,
	projets.idCustomer as idCfp,
	CASE
		WHEN (projets.project_is_archived = 1) THEN "Archivé"
		WHEN (projets.project_is_trashed = 1) THEN "Supprimé"
		WHEN (projets.project_is_closed = 1) THEN "Cloturé"
		WHEN (projets.project_is_active = 1
								AND projets.dateFin < CURRENT_DATE) THEN "Terminé"
		WHEN (projets.project_is_active = 0
								AND projets.project_is_cancelled = 0
								AND projets.project_is_repported = 0
								AND projets.project_is_reserved = 0
								AND projets.project_is_archived = 0
								AND projets.project_is_trashed = 0) THEN "En préparation"
		WHEN (projets.project_is_cancelled = 1) THEN "Annulé"
		WHEN (projets.project_is_repported = 1) THEN "Reporté"
		WHEN (projets.project_is_reserved = 1) THEN "Réservé"
		WHEN (projets.project_is_active = 1
								AND projets.dateDebut > CURRENT_DATE) THEN "Planifié"
		ELSE "En cours"
	END project_status,
	type_projets.type AS project_type,
	modalites.idModalite,
	modalites.modalite,
	ville_codeds.idVille,
	ville_codeds.ville_name AS ville,
	projets.idModule,
	l.idLieu,
	l.li_name,
	mdls.moduleName AS module_name,
	mdls.description AS module_description,
	mdls.module_image,
	paiements.idPaiement AS idPaiement,
	paiements.paiement,
	mdls.idDomaine,
	domaine_formations.nomDomaine AS domaine_name,
	projets.idSalle,
	salles.salle_name,
	l.li_quartier AS salle_quartier,
	l.li_rue AS salle_rue,
	salles.salle_image,
	ville_codeds.vi_code_postal AS salle_code_postal,
	DATE_FORMAT(dateDebut, "%M, %Y") AS headDate,
	DATE_FORMAT(dateFin, "%d") AS headDayFin, 
	psc.idSubContractor, 
	SUBSTRING(sub.customerName, 1, 1) AS sub_initial_name, 
	sub.customerName AS sub_name, sub.nif AS sub_nif, 
	sub.stat AS sub_stat, sub.rcs AS sub_rsc, 
	sub.description AS sub_description, 
	sub.logo AS sub_logo, 
	sub.customerEmail AS sub_email, 
	sub.customerPhone AS sub_phone,
	bon_commandes.numero as numero_bc
FROM projets
LEFT JOIN intras ON intras.idProjet = projets.idProjet
LEFT JOIN inters ON inters.idProjet = projets.idProjet
LEFT JOIN paiements ON intras.idPaiement = paiements.idPaiement OR inters.idPaiement = paiements.idPaiement
LEFT JOIN mdls ON projets.idModule = mdls.idModule
LEFT JOIN domaine_formations ON mdls.idDomaine = domaine_formations.idDomaine
LEFT JOIN type_projets ON projets.idTypeProjet = type_projets.idTypeProjet
LEFT JOIN modalites ON projets.idModalite = modalites.idModalite
LEFT JOIN salles ON projets.idSalle = salles.idSalle
LEFT JOIN project_sub_contracts AS psc ON projets.idProjet = psc.idProjet
LEFT JOIN customers AS sub ON psc.idSubContractor = sub.idCustomer
LEFT JOIN lieux AS l ON salles.idLieu = l.idLieu
LEFT JOIN ville_codeds ON l.idVilleCoded = ville_codeds.id
LEFT JOIN villes ON ville_codeds.idVille = villes.idVille
LEFT JOIN bon_commandes ON projets.idBC = bon_commandes.idBc
WHERE projets.dateDebut IS NOT NULL;