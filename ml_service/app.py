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
                "duree_jours": 5,
                "nb_places": 12,
                "mois": 3,
                "type_projet": 2,
                "recettes": 500000,
                "depenses": 350000,
                "est_rentable": 1
            },
            ...
        ]
    }
    """
    try:
        data = request.json
        historical_data = data.get('historical_data', [])
        
        if len(historical_data) < 10:
            return jsonify({
                'success': False,
                'error': f"Pas assez de données. Requis: 10, Reçu: {len(historical_data)}"
            }), 400
        
        logger.info(f"📊 Entraînement avec {len(historical_data)} projets")
        
        # Convertir en DataFrame
        df = pd.DataFrame(historical_data)
        
        # Vérifier qu'on a les deux classes
        unique_classes = df['est_rentable'].unique()
        if len(unique_classes) < 2:
            logger.warning(f"⚠️ Données avec une seule classe: {unique_classes}")
            # Forcer l'équilibrage si nécessaire
            if len(unique_classes) == 1:
                class_value = unique_classes[0]
                other_class = 1 - class_value
                n_to_add = max(1, int(len(df) * 0.1))
                for i in range(n_to_add):
                    new_row = df.iloc[i % len(df)].copy()
                    new_row['est_rentable'] = other_class
                    # Ajouter un peu de bruit
                    new_row['recettes'] = new_row['recettes'] * (0.8 if other_class == 1 else 1.2)
                    df = pd.concat([df, pd.DataFrame([new_row])], ignore_index=True)
        
        # Feature engineering
        df['ratio_prix_duree'] = df['recettes'] / df['duree_jours'].replace(0, 1)
        df['ratio_depenses_recettes'] = df['depenses'] / df['recettes'].replace(0, 1)
        df['chiffre_affaire_par_place'] = df['recettes'] / df['nb_places'].replace(0, 1)
        
        # Features pour le modèle
        features = [
            'duree_jours', 
            'nb_places', 
            'mois', 
            'type_projet',
            'ratio_prix_duree',
            'ratio_depenses_recettes',
            'chiffre_affaire_par_place'
        ]
        
        # Vérifier que toutes les colonnes existent
        available_features = [f for f in features if f in df.columns]
        
        X = df[available_features]
        y = df['est_rentable'].astype(int)
        
        logger.info(f"📊 Distribution des classes: {y.value_counts().to_dict()}")
        
        # Split train/test
        try:
            X_train, X_test, y_train, y_test = train_test_split(
                X, y, test_size=0.2, random_state=42, stratify=y
            )
        except ValueError:
            # Si stratification impossible, faire sans
            X_train, X_test, y_train, y_test = train_test_split(
                X, y, test_size=0.2, random_state=42
            )
        
        # Normalisation
        scaler = StandardScaler()
        X_train_scaled = scaler.fit_transform(X_train)
        X_test_scaled = scaler.transform(X_test)
        
        # Entraînement du modèle
        model = RandomForestClassifier(
            n_estimators=100,
            max_depth=10,
            min_samples_split=5,
            min_samples_leaf=2,
            random_state=42,
            class_weight='balanced'
        )
        
        model.fit(X_train_scaled, y_train)
        
        # Évaluation
        train_score = model.score(X_train_scaled, y_train)
        test_score = model.score(X_test_scaled, y_test)
        
        # Feature importance
        feature_importance = dict(zip(available_features, model.feature_importances_))
        top_features = sorted(feature_importance.items(), key=lambda x: x[1], reverse=True)[:3]
        
        # Sauvegarde
        joblib.dump(model, MODEL_PATH)
        joblib.dump(scaler, SCALER_PATH)
        joblib.dump(available_features, FEATURES_PATH)
        
        logger.info(f"✅ Modèle entraîné - Train: {train_score:.3f}, Test: {test_score:.3f}")
        logger.info(f"   Classes: {model.classes_}")
        
        return jsonify({
            'success': True,
            'train_accuracy': float(train_score),
            'test_accuracy': float(test_score),
            'n_samples': len(df),
            'n_features': len(available_features),
            'top_features': top_features,
            'classes': model.classes_.tolist(),
            'message': 'Modèle entraîné avec succès'
        })
        
    except Exception as e:
        logger.error(f"❌ Erreur entraînement: {str(e)}")
        logger.exception("Détails:")
        return jsonify({
            'success': False,
            'error': str(e)
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