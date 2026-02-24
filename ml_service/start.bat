@echo off
title Service ML - Prediction de rentabilite
color 0A

echo ================================
echo Service ML - Prediction de rentabilite
echo ================================
echo.

echo Verification de Python...
python --version >nul 2>&1
if errorlevel 1 (
    echo ERREUR: Python n'est pas installe
    echo Telecharge-le depuis: https://www.python.org/downloads/
    pause
    exit /b 1
)

python --version
echo Python trouve
echo.

echo Configuration de l'environnement virtuel...
if not exist "venv" (
    python -m venv venv
    echo Environnement virtuel cree
) else (
    echo Environnement virtuel existant
)
echo.

echo Activation de l'environnement...
call venv\Scripts\activate.bat
echo.

echo Installation des dependances...
@REM pip install --upgrade pip
@REM pip install flask
@REM pip install flask-cors
@REM pip install pandas
@REM pip install numpy
@REM pip install scikit-learn
@REM pip install joblib
@REM pip install python-dotenv
echo Dependances installees
echo.

echo Creation des dossiers...
if not exist "models" mkdir models
if not exist "data" mkdir data
echo Dossiers crees
echo.

echo ================================
echo Demarrage du serveur sur http://localhost:5000
echo ================================
echo.

python app.py

pause
