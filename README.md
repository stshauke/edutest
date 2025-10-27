"# edutest" 

🎓 EduTest — Plateforme d’évaluation en ligne

EduTest est une application web développée avec Symfony permettant la gestion complète d’examens en ligne pour enseignants, étudiants et administrateurs.

🚀 Fonctionnalités principales
👩‍🏫 Enseignant
    * Création, édition et suppression d’examens
    * Affectation d’examens aux étudiants
    * Suivi des résultats et statistiques

👨‍🎓 Étudiant
    * Visualisation des examens assignés
    * Passation d’examens avec chronomètre
    * Consultation des notes et corrections

🛠️ Administrateur
    * Tableau de bord global avec statistiques
    * Gestion des utilisateurs (approbation, suppression, désactivation)
    * Export des résultats PDF / CSV

⚙️ Installation locale
1. Cloner le projet
   git clone https://github.com/stshauke/edutest.git
   cd edutest

2. Installer les dépendances
   composer install

3. Configurer la base de données
    * Créez un fichier .env.local
    * Mettez à jour la ligne :
    DATABASE_URL="mysql://user:password@127.0.0.1:3306/edutest?serverVersion=8.0"

4. Créer la base
    php bin/console doctrine:database:create

5. Importer la base fournie
    mysql -u user -p edutest < var/db/EduTest.sql

6. Lancer le serveur
    symfony server:start -d

    ou 
    php -S 127.0.0.1:8000 -t public

➡️ Accéder à l’application : http://127.0.0.1:8000

🧰 Technologies utilisées
Symfony 6+ / PHP 8.1+
MySQL / Doctrine ORM
Twig / Bootstrap 5
Chart.js, mPDF, league/csv

✉️ Repo GitHub : https://github.com/stshauke/edutest


