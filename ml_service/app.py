#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
API de prédiction de rentabilité pour projets de formation
Utilise un modèle Random Forest entraîné sur les données historiques
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import numpy as np
import joblib
import os
import logging
from datetime import datetime
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import StandardScaler
from sklearn.model_selection import train_test_split
import warnings
warnings.filterwarnings('ignore')

# Configuration des logs
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)  # Permet les requêtes depuis Laravel

# Chemins des modèles - CORRECTION IMPORTANTE
BASE_DIR = os.path.dirname(os.path.abspath(__file__))  # ml_service/
MODEL_DIR = os.path.join(BASE_DIR, 'models')  # ml_service/models/
MODEL_PATH = os.path.join(MODEL_DIR, 'rentability_model.pkl')
SCALER_PATH = os.path.join(MODEL_DIR, 'scaler.pkl')
FEATURES_PATH = os.path.join(MODEL_DIR, 'features.pkl')

# Créer le dossier models s'il n'existe pas
os.makedirs(MODEL_DIR, exist_ok=True)

# Variables globales pour le modèle
model = None
scaler = None
feature_names = None

def load_model():
    """Charge le modèle s'il existe"""
    global model, scaler, feature_names
    
    logger.info(f"🔍 Recherche du modèle dans: {MODEL_DIR}")
    
    if os.path.exists(MODEL_PATH) and os.path.exists(SCALER_PATH):
        try:
            model = joblib.load(MODEL_PATH)
            scaler = joblib.load(SCALER_PATH)
            feature_names = joblib.load(FEATURES_PATH) if os.path.exists(FEATURES_PATH) else None
            logger.info(f"✅ Modèle chargé avec succès depuis {MODEL_DIR}")
            logger.info(f"   - Type: {type(model).__name__}")
            logger.info(f"   - Features: {feature_names}")
            logger.info(f"   - Classes: {model.classes_}")
            return True
        except Exception as e:
            logger.error(f"❌ Erreur chargement modèle: {e}")
            return False
    else:
        logger.warning(f"⚠️ Aucun modèle trouvé dans {MODEL_DIR}")
        if not os.path.exists(MODEL_PATH):
            logger.warning(f"   - Fichier manquant: {MODEL_PATH}")
        if not os.path.exists(SCALER_PATH):
            logger.warning(f"   - Fichier manquant: {SCALER_PATH}")
        return False

# Charger au démarrage
load_model()

@app.route('/health', methods=['GET'])
def health():
    """Vérifie que l'API est en ligne"""
    return jsonify({
        'status': 'ok',
        'timestamp': datetime.now().isoformat(),
        'model_loaded': model is not None,
        'model_path': MODEL_PATH if os.path.exists(MODEL_PATH) else None,
        'model_dir': MODEL_DIR,
        'files': os.listdir(MODEL_DIR) if os.path.exists(MODEL_DIR) else []
    })

@app.route('/train', methods=['POST'])
def train():
    """
    Entraîne le modèle avec les données historiques
    Body: {
        "historical_data": [
            {
                "duree_jours": 5,           # durée réelle calculée
                "duree_standard": 5,          # durée standard du module
                "jours_ouvres": 5,             # jours ouvrés réels
                "nb_places": 12,
                "mois": 3,
                "type_projet": 2,
                "recettes": 500000,
                "depenses": 350000,
                "est_rentable": 1,
                "ratio_duree_reelle_standard": 1.0,
                "saison": "printemps"
            },
            ...
        ]
    }
    """
    try:
        data = request.json
        historical_data = data.get('historical_data', [])
        customer_id = data.get('customer_id', 'default')
        
        # Validation du nombre minimum de données
        min_samples = 10
        if len(historical_data) < min_samples:
            logger.warning(f"⚠️ Pas assez de données: {len(historical_data)} < {min_samples}")
            return jsonify({
                'success': False,
                'error': f"Pas assez de données. Requis: {min_samples}, Reçu: {len(historical_data)}"
            }), 400
        
        logger.info(f"📊 Entraînement avec {len(historical_data)} projets pour client {customer_id}")
        
        # Convertir en DataFrame
        df = pd.DataFrame(historical_data)
        
        # Analyse exploratoire rapide
        logger.info(f"📊 Aperçu des données:")
        logger.info(f"   - Colonnes: {list(df.columns)}")
        logger.info(f"   - Types: {df.dtypes.to_dict()}")
        logger.info(f"   - Stats durée: min={df['duree_jours'].min()}, max={df['duree_jours'].max()}, mean={df['duree_jours'].mean():.1f}")
        
        # Vérifier et traiter les valeurs manquantes
        initial_count = len(df)
        df = df.dropna(subset=['duree_jours', 'nb_places', 'recettes', 'depenses', 'est_rentable'])
        if len(df) < initial_count:
            logger.warning(f"⚠️ {initial_count - len(df)} lignes supprimées (valeurs manquantes)")
        
        # Vérifier les valeurs aberrantes (outliers)
        Q1 = df['recettes'].quantile(0.25)
        Q3 = df['recettes'].quantile(0.75)
        IQR = Q3 - Q1
        outliers = df[(df['recettes'] < Q1 - 3 * IQR) | (df['recettes'] > Q3 + 3 * IQR)]
        if len(outliers) > 0:
            logger.warning(f"⚠️ {len(outliers)} outliers détectés dans les recettes")
        
        # Feature engineering avancé
        logger.info("🔧 Feature engineering en cours...")
        
        # 1. Ratios de base (existants)
        df['ratio_prix_duree'] = df['recettes'] / df['duree_jours'].replace(0, 1)
        df['ratio_depenses_recettes'] = df['depenses'] / df['recettes'].replace(0, 1)
        df['chiffre_affaire_par_place'] = df['recettes'] / df['nb_places'].replace(0, 1)
        
        # 2. Nouvelles features basées sur la durée
        if 'duree_standard' in df.columns:
            df['ratio_duree_reelle_standard'] = df['duree_jours'] / df['duree_standard'].replace(0, 1)
            df['est_etale'] = (df['ratio_duree_reelle_standard'] > 1.3).astype(int)
            df['est_concentre'] = (df['ratio_duree_reelle_standard'] < 0.8).astype(int)
        else:
            df['ratio_duree_reelle_standard'] = 1.0
            df['est_etale'] = 0
            df['est_concentre'] = 0
        
        # 3. Features de rentabilité
        df['marge_brute'] = (df['recettes'] - df['depenses']) / df['recettes'].replace(0, 1) * 100
        df['marge_par_place'] = df['marge_brute'] / df['nb_places'].replace(0, 1)
        df['seuil_rentabilite_places'] = df['depenses'] / (df['recettes'] / df['nb_places']).replace(0, 1)
        
        # 4. Features temporelles enrichies
        if 'jours_ouvres' in df.columns:
            df['ratio_jours_ouvres'] = df['jours_ouvres'] / df['duree_jours'].replace(0, 1)
            df['intensite_weekend'] = 1 - df['ratio_jours_ouvres']
        else:
            df['ratio_jours_ouvres'] = 1.0
            df['intensite_weekend'] = 0
        
        # 5. Features de saisonnalité
        if 'saison' in df.columns:
            # One-hot encoding pour les saisons
            saison_dummies = pd.get_dummies(df['saison'], prefix='saison')
            df = pd.concat([df, saison_dummies], axis=1)
        else:
            # Saison à partir du mois
            df['saison_printemps'] = df['mois'].isin([3, 4, 5]).astype(int)
            df['saison_ete'] = df['mois'].isin([6, 7, 8]).astype(int)
            df['saison_automne'] = df['mois'].isin([9, 10, 11]).astype(int)
            df['saison_hiver'] = df['mois'].isin([12, 1, 2]).astype(int)
        
        # 6. Features d'interaction
        df['interaction_places_saison'] = df['nb_places'] * df['saison_printemps']
        df['interaction_duree_type'] = df['duree_jours'] * df['type_projet']
        df['interaction_prix_places'] = df['ratio_prix_duree'] * df['nb_places']
        
        # 7. Features catégorielles encodées
        df['type_projet_nom'] = df['type_projet'].map({1: 'INTRA', 2: 'INTER', 4: 'SUR_MESURE'})
        type_dummies = pd.get_dummies(df['type_projet_nom'], prefix='type')
        df = pd.concat([df, type_dummies], axis=1)
        
        # 8. Features de performance relative
        mean_marge = df['marge_brute'].mean()
        df['performance_relative'] = df['marge_brute'] - mean_marge
        df['est_sur_performant'] = (df['performance_relative'] > 10).astype(int)
        df['est_sous_performant'] = (df['performance_relative'] < -10).astype(int)
        
        logger.info("✅ Feature engineering terminé")
        
        # Vérifier la distribution des classes
        class_distribution = df['est_rentable'].value_counts()
        logger.info(f"📊 Distribution des classes: {class_distribution.to_dict()}")
        
        # Équilibrage des classes si nécessaire
        unique_classes = df['est_rentable'].unique()
        if len(unique_classes) < 2:
            logger.warning(f"⚠️ Données avec une seule classe: {unique_classes}")
            # Forcer l'équilibrage
            if len(unique_classes) == 1:
                class_value = unique_classes[0]
                other_class = 1 - class_value
                n_to_add = max(5, int(len(df) * 0.2))
                
                logger.info(f"🔄 Équilibrage: ajout de {n_to_add} exemples synthétiques de classe {other_class}")
                
                for i in range(n_to_add):
                    new_row = df.iloc[i % len(df)].copy()
                    new_row['est_rentable'] = other_class
                    # Ajouter du bruit réaliste
                    new_row['recettes'] = new_row['recettes'] * (0.7 if other_class == 1 else 1.3)
                    new_row['depenses'] = new_row['depenses'] * (1.3 if other_class == 1 else 0.7)
                    new_row['nb_places'] = max(1, int(new_row['nb_places'] * np.random.uniform(0.8, 1.2)))
                    df = pd.concat([df, pd.DataFrame([new_row])], ignore_index=True)
        
        # Sélection des features pour le modèle
        base_features = [
            'duree_jours',
            'nb_places',
            'mois',
            'ratio_prix_duree',
            'ratio_depenses_recettes',
            'chiffre_affaire_par_place',
            'marge_brute',
            'marge_par_place',
            'seuil_rentabilite_places',
            'ratio_duree_reelle_standard',
            'est_etale',
            'est_concentre',
            'ratio_jours_ouvres',
            'intensite_weekend',
            'interaction_places_saison',
            'interaction_duree_type',
            'interaction_prix_places',
            'performance_relative',
            'est_sur_performant',
            'est_sous_performant'
        ]
        
        # Ajouter les features one-hot encodées
        saison_features = [col for col in df.columns if col.startswith('saison_')]
        type_features = [col for col in df.columns if col.startswith('type_')]
        
        all_features = base_features + saison_features + type_features
        
        # Vérifier que toutes les features existent
        available_features = [f for f in all_features if f in df.columns]
        missing_features = set(all_features) - set(available_features)
        
        if missing_features:
            logger.warning(f"⚠️ Features manquantes: {missing_features}")
        
        logger.info(f"📊 Utilisation de {len(available_features)} features: {available_features}")
        
        # Préparer X et y
        X = df[available_features].copy()
        y = df['est_rentable'].astype(int)
        
        # Gérer les valeurs infinies
        X = X.replace([np.inf, -np.inf], np.nan)
        
        # Remplir les NaN avec la médiane
        for col in X.columns:
            if X[col].isna().any():
                median_val = X[col].median()
                X[col].fillna(median_val, inplace=True)
                logger.info(f"   - {col}: {X[col].isna().sum()} NaN remplis avec médiane={median_val:.2f}")
        
        # Split train/test avec stratification
        try:
            X_train, X_test, y_train, y_test = train_test_split(
                X, y, test_size=0.2, random_state=42, stratify=y
            )
            logger.info("✅ Split avec stratification réussi")
        except ValueError as e:
            logger.warning(f"⚠️ Stratification impossible: {e}")
            X_train, X_test, y_train, y_test = train_test_split(
                X, y, test_size=0.2, random_state=42
            )
        
        # Normalisation
        scaler = StandardScaler()
        X_train_scaled = scaler.fit_transform(X_train)
        X_test_scaled = scaler.transform(X_test)
        
        # Entraînement du modèle avec recherche d'hyperparamètres
        logger.info("🤖 Entraînement du modèle RandomForest...")
        
        # Version simple mais robuste
        model = RandomForestClassifier(
            n_estimators=200,
            max_depth=15,
            min_samples_split=5,
            min_samples_leaf=2,
            max_features='sqrt',
            bootstrap=True,
            oob_score=True,
            random_state=42,
            class_weight='balanced',
            n_jobs=-1
        )
        
        model.fit(X_train_scaled, y_train)
        
        # Évaluations détaillées
        train_score = model.score(X_train_scaled, y_train)
        test_score = model.score(X_test_scaled, y_test)
        oob_score = model.oob_score_ if hasattr(model, 'oob_score_') else None
        
        # Prédictions pour métriques avancées
        y_pred = model.predict(X_test_scaled)
        y_pred_proba = model.predict_proba(X_test_scaled)
        
        # Calcul des métriques
        from sklearn.metrics import (accuracy_score, precision_score, recall_score, 
                                   f1_score, roc_auc_score, confusion_matrix)
        
        accuracy = accuracy_score(y_test, y_pred)
        precision = precision_score(y_test, y_pred, zero_division=0)
        recall = recall_score(y_test, y_pred, zero_division=0)
        f1 = f1_score(y_test, y_pred, zero_division=0)
        
        # ROC AUC (si 2 classes)
        roc_auc = None
        if len(model.classes_) == 2:
            try:
                roc_auc = roc_auc_score(y_test, y_pred_proba[:, 1])
            except:
                roc_auc = None
        
        # Matrice de confusion
        cm = confusion_matrix(y_test, y_pred).tolist() if len(y_test) > 0 else []
        
        logger.info(f"✅ Métriques d'évaluation:")
        logger.info(f"   - Train Accuracy: {train_score:.3f}")
        logger.info(f"   - Test Accuracy: {test_score:.3f}")
        logger.info(f"   - OOB Score: {oob_score:.3f}" if oob_score else "   - OOB Score: N/A")
        logger.info(f"   - Precision: {precision:.3f}")
        logger.info(f"   - Recall: {recall:.3f}")
        logger.info(f"   - F1-Score: {f1:.3f}")
        logger.info(f"   - ROC AUC: {roc_auc:.3f}" if roc_auc else "   - ROC AUC: N/A")
        
        # Feature importance
        feature_importance = dict(zip(available_features, model.feature_importances_))
        top_features = sorted(feature_importance.items(), key=lambda x: x[1], reverse=True)[:5]
        
        logger.info(f"🔥 Top 5 features importantes:")
        for i, (feat, imp) in enumerate(top_features, 1):
            logger.info(f"   {i}. {feat}: {imp:.3f}")
        
        # Analyse des erreurs
        errors = X_test.copy()
        errors['true_value'] = y_test
        errors['predicted'] = y_pred
        errors['correct'] = (errors['true_value'] == errors['predicted'])
        
        false_positives = errors[(errors['true_value'] == 0) & (errors['predicted'] == 1)]
        false_negatives = errors[(errors['true_value'] == 1) & (errors['predicted'] == 0)]
        
        logger.info(f"📊 Analyse des erreurs:")
        logger.info(f"   - Faux positifs: {len(false_positives)}")
        logger.info(f"   - Faux négatifs: {len(false_negatives)}")
        
        # Sauvegarde du modèle avec métadonnées
        model_metadata = {
            'model': model,
            'scaler': scaler,
            'features': available_features,
            'training_date': datetime.now().isoformat(),
            'n_samples': len(df),
            'class_distribution': class_distribution.to_dict(),
            'metrics': {
                'accuracy': accuracy,
                'precision': precision,
                'recall': recall,
                'f1_score': f1,
                'roc_auc': roc_auc,
                'train_score': train_score,
                'test_score': test_score,
                'oob_score': oob_score
            },
            'top_features': top_features,
            'feature_importance': feature_importance,
            'confusion_matrix': cm,
            'version': '2.0.0',
            'customer_id': customer_id
        }
        
        # Sauvegarde
        joblib.dump(model_metadata, MODEL_PATH)
        joblib.dump(scaler, SCALER_PATH)
        joblib.dump(available_features, FEATURES_PATH)
        
        # Sauvegarde d'un backup horodaté
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        backup_path = os.path.join(MODEL_DIR, f'model_backup_{timestamp}.pkl')
        joblib.dump(model_metadata, backup_path)
        
        logger.info(f"💾 Modèle sauvegardé: {MODEL_PATH}")
        logger.info(f"💾 Backup sauvegardé: {backup_path}")
        
        # Préparer la réponse
        response = {
            'success': True,
            'message': 'Modèle entraîné avec succès',
            'training_date': model_metadata['training_date'],
            'n_samples': len(df),
            'n_features': len(available_features),
            'class_distribution': class_distribution.to_dict(),
            'metrics': {
                'accuracy': float(accuracy),
                'precision': float(precision),
                'recall': float(recall),
                'f1_score': float(f1),
                'roc_auc': float(roc_auc) if roc_auc else None,
                'train_accuracy': float(train_score),
                'test_accuracy': float(test_score),
                'oob_score': float(oob_score) if oob_score else None
            },
            'top_features': [(feat, float(imp)) for feat, imp in top_features],
            'confusion_matrix': cm,
            'feature_names': available_features,
            'classes': model.classes_.tolist(),
            'backup_path': backup_path,
            'model_version': '2.0.0'
        }
        
        logger.info(f"✅ Entraînement terminé avec succès")
        return jsonify(response)
        
    except Exception as e:
        logger.error(f"❌ Erreur entraînement: {str(e)}")
        logger.exception("Détails de l'erreur:")
        return jsonify({
            'success': False,
            'error': str(e),
            'error_type': type(e).__name__
        }), 500
        
@app.route('/predict', methods=['POST'])
def predict():
    """
    Prédit la rentabilité d'un projet
    Body: {
        "features": {
            "duree_jours": 5,
            "nb_places": 12,
            "mois": 3,
            "type_projet": 2,
            "prix_par_jour": 100000,
            "chiffre_affaire_potentiel": 1200000,
            "frais_totaux_estimes": 800000,
            "nb_entreprises_interessees": 4,
            "haute_saison": 1
        }
    }
    """
    try:
        data = request.json
        features = data.get('features', {})
        
        logger.info(f"📊 Prédiction demandée avec features: {features}")
        
        # Si pas de modèle, utiliser fallback
        if model is None:
            logger.warning("⚠️ Modèle non chargé, utilisation fallback")
            return fallback_prediction(features)
        
        # Préparer les features pour la prédiction
        feature_vector = []
        
        # Ordre doit correspondre à l'entraînement
        feature_order = feature_names if feature_names else [
            'duree_jours', 'nb_places', 'mois', 'type_projet',
            'ratio_prix_duree', 'ratio_depenses_recettes', 'chiffre_affaire_par_place'
        ]
        
        # Calculer les ratios
        duree = features.get('duree_jours', 1)
        nb_places = features.get('nb_places', 1)
        recettes = features.get('chiffre_affaire_potentiel', 0)
        depenses = features.get('frais_totaux_estimes', 0)
        
        # Éviter les divisions par zéro
        duree = max(duree, 1)
        nb_places = max(nb_places, 1)
        recettes = max(recettes, 1)
        
        ratio_prix_duree = recettes / duree
        ratio_depenses_recettes = depenses / recettes
        ca_par_place = recettes / nb_places
        
        # Construire le vecteur dans le bon ordre
        for feat in feature_order:
            if feat == 'duree_jours':
                feature_vector.append(features.get('duree_jours', 1))
            elif feat == 'nb_places':
                feature_vector.append(features.get('nb_places', 1))
            elif feat == 'mois':
                feature_vector.append(features.get('mois', 1))
            elif feat == 'type_projet':
                feature_vector.append(features.get('type_projet', 2))
            elif feat == 'ratio_prix_duree':
                feature_vector.append(ratio_prix_duree)
            elif feat == 'ratio_depenses_recettes':
                feature_vector.append(ratio_depenses_recettes)
            elif feat == 'chiffre_affaire_par_place':
                feature_vector.append(ca_par_place)
            else:
                feature_vector.append(0)
        
        logger.info(f"📊 Feature vector: {feature_vector}")
        
        # Vérifier la dimension
        if len(feature_vector) != model.n_features_in_:
            logger.error(f"❌ Dimension incorrecte: {len(feature_vector)} vs {model.n_features_in_}")
            return fallback_prediction(features, f"Dimension incorrecte")
        
        # Normaliser
        feature_vector_scaled = scaler.transform([feature_vector])
        
        # Prédire avec gestion d'erreur
        try:
            proba = model.predict_proba(feature_vector_scaled)[0]
            
            # Déterminer l'index de la classe positive (généralement 1)
            if len(proba) == 1:
                # Modèle avec une seule classe
                probability = proba[0]
                prediction = model.predict(feature_vector_scaled)[0]
                logger.warning(f"⚠️ Modèle avec une seule classe: {model.classes_}")
            else:
                # Modèle avec deux classes
                if 1 in model.classes_:
                    idx = list(model.classes_).index(1)
                    probability = proba[idx]
                else:
                    probability = proba[1]
                prediction = model.predict(feature_vector_scaled)[0]
                
        except Exception as e:
            logger.error(f"❌ Erreur lors de la prédiction: {e}")
            return fallback_prediction(features, f"Erreur de prédiction: {e}")
        
        # Obtenir les features importances
        importances = model.feature_importances_
        feature_imp = dict(zip(feature_order, importances))
        top_factors = sorted(feature_imp.items(), key=lambda x: x[1], reverse=True)[:3]
        
        # Estimer la marge
        margin = estimate_margin(features, probability)
        
        # Déterminer le niveau de confiance
        if probability > 0.8 or probability < 0.2:
            confidence = 'high'
        elif probability > 0.6 or probability < 0.4:
            confidence = 'medium'
        else:
            confidence = 'low'
        
        result = {
            'success': True,
            'probability': float(probability),
            'is_rentable': bool(prediction == 1),
            'confidence': confidence,
            'margin': float(margin),
            'top_factors': [f[0].replace('_', ' ') for f in top_factors],
            'factors': [f[0].replace('_', ' ') for f in top_factors],
            'method': 'ml'
        }
        
        logger.info(f"✅ Prédiction: {result}")
        return jsonify(result)
        
    except Exception as e:
        logger.error(f"❌ Erreur prédiction: {str(e)}")
        logger.exception("Détails:")
        return fallback_prediction(features, str(e))

def fallback_prediction(features, error_reason=""):
    """Règles métier de secours"""
    score = 0
    reasons = []
    
    # 1. Saisonnalité
    mois = features.get('mois', 1)
    haute_saison = features.get('haute_saison', 0)
    if haute_saison or mois in [3, 4, 9, 10]:
        score += 20
        reasons.append("haute saison")
    
    # 2. Prix par jour
    prix_par_jour = features.get('prix_par_jour', 0)
    if prix_par_jour > 50000:
        score += 25
        reasons.append("prix élevé")
    elif prix_par_jour > 30000:
        score += 15
        reasons.append("prix moyen")
    
    # 3. Nombre de places
    nb_places = features.get('nb_places', 0)
    if nb_places > 15:
        score += 25
        reasons.append("grand groupe")
    elif nb_places > 8:
        score += 15
        reasons.append("groupe moyen")
    
    # 4. Entreprises intéressées
    nb_entreprises = features.get('nb_entreprises_interessees', 0)
    if nb_entreprises > 3:
        score += 20
        reasons.append("fort intérêt")
    elif nb_entreprises > 0:
        score += 10
        reasons.append("intérêt confirmé")
    
    # 5. Frais raisonnables
    ca = features.get('chiffre_affaire_potentiel', 0)
    frais = features.get('frais_totaux_estimes', 0)
    if ca > 0:
        ratio_frais = frais / ca
        if ratio_frais < 0.3:
            score += 10
            reasons.append("frais maîtrisés")
    
    probability = min(score / 100, 0.95)
    
    # Calculer la marge estimée
    margin = 0
    if ca > 0:
        margin = ((ca - frais) / ca) * 100
    else:
        margin = 15.0
    
    result = {
        'success': True,
        'probability': probability,
        'is_rentable': probability > 0.6,
        'confidence': 'low',
        'margin': margin * probability,
        'top_factors': reasons[:3],
        'factors': reasons[:3],
        'method': 'fallback',
        'note': 'Prédiction basée sur règles métier'
    }
    
    if error_reason:
        result['error_details'] = error_reason
        result['note'] = f"Prédiction basée sur règles métier ({error_reason})"
    
    # Générer un conseil
    result['advice'] = generate_fallback_advice(probability, reasons)
    
    return jsonify(result)

def generate_fallback_advice(probability, factors):
    """Génère un conseil pour le fallback"""
    prob_percent = round(probability * 100)
    
    if probability > 0.6:
        if factors:
            return f"👍 Projet potentiellement rentable ({prob_percent}%). Points forts: " + ", ".join(factors[:2])
        else:
            return f"👍 Projet potentiellement rentable ({prob_percent}%)."
    else:
        if factors:
            return f"📊 Projet incertain ({prob_percent}%). Facteurs: " + ", ".join(factors[:2])
        else:
            return f"📊 Projet incertain ({prob_percent}%). Analyse complémentaire recommandée."

def estimate_margin(features, probability):
    """Estime la marge bénéficiaire"""
    ca = features.get('chiffre_affaire_potentiel', 0)
    frais = features.get('frais_totaux_estimes', 0)
    
    if ca > 0:
        base_margin = ((ca - frais) / ca) * 100
        return base_margin * probability
    return 15.0

@app.route('/features', methods=['GET'])
def get_features():
    """Retourne la liste des features utilisées par le modèle"""
    if feature_names:
        return jsonify({
            'success': True,
            'features': feature_names,
            'model_loaded': True,
            'n_features': len(feature_names)
        })
    else:
        return jsonify({
            'success': True,
            'features': [
                'duree_jours',
                'nb_places',
                'mois',
                'type_projet',
                'prix_par_jour',
                'haute_saison',
                'nb_entreprises_interessees'
            ],
            'model_loaded': False,
            'n_features': 7
        })

@app.route('/debug/model', methods=['GET'])
def debug_model():
    """Diagnostic du modèle"""
    result = {
        'model_loaded': model is not None,
        'model_dir': MODEL_DIR,
        'model_path': MODEL_PATH,
        'files_in_model_dir': []
    }
    
    if os.path.exists(MODEL_DIR):
        result['files_in_model_dir'] = os.listdir(MODEL_DIR)
    
    if model is not None:
        result['model_info'] = {
            'type': str(type(model)),
            'n_features': model.n_features_in_,
            'n_classes': len(model.classes_),
            'classes': model.classes_.tolist(),
            'feature_names': feature_names
        }
        
        # Tester une prédiction simple
        try:
            test_vector = [[0] * model.n_features_in_]
            test_scaled = scaler.transform(test_vector)
            pred_shape = model.predict_proba(test_scaled).shape
            result['test_prediction_shape'] = str(pred_shape)
        except Exception as e:
            result['test_error'] = str(e)
    
    return jsonify(result)

@app.route('/customer/<int:customer_id>/stats', methods=['GET'])
def customer_stats(customer_id):
    """Retourne les statistiques du modèle pour un client"""
    try:
        return jsonify({
            'success': True,
            'customer_id': customer_id,
            'model_version': '1.0',
            'features_used': feature_names,
            'feature_importance': dict(zip(feature_names, model.feature_importances_)) if model else None,
            'model_loaded': model is not None
        })
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/batch-predict', methods=['POST'])
def batch_predict():
    """Prédiction en lot pour plusieurs projets"""
    try:
        data = request.json
        projects = data.get('projects', [])
        
        results = []
        for project in projects:
            if model is not None:
                # Créer une requête interne
                with app.test_request_context('/predict', method='POST', json={'features': project.get('features', {})}):
                    response = predict()
                    if isinstance(response, tuple):
                        result = response[0].json
                    else:
                        result = response.json
                    results.append(result)
            else:
                pred = fallback_prediction(project.get('features', {}))
                results.append(pred.json)
        
        return jsonify({
            'success': True,
            'predictions': results,
            'count': len(results)
        })
    except Exception as e:
        logger.error(f"❌ Erreur batch predict: {e}")
        return jsonify({'success': False, 'error': str(e)}), 500

if __name__ == '__main__':
    port = int(os.environ.get('PORT', 5000))
    debug = os.environ.get('FLASK_DEBUG', 'False').lower() == 'true'
    
    logger.info(f"🚀 Démarrage du serveur ML sur http://localhost:{port}")
    logger.info(f"📁 Dossier des modèles: {MODEL_DIR}")
    logger.info(f"📁 Contenu du dossier: {os.listdir(MODEL_DIR) if os.path.exists(MODEL_DIR) else 'Dossier vide'}")
    
    app.run(host='0.0.0.0', port=port, debug=debug)