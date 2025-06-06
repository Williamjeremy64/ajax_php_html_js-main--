# Système de Gestion de Budget Personnel

## Description
Ce projet est une application web de gestion de budget personnel qui permet aux utilisateurs de :
- Consulter leur solde
- Ajouter des revenus et des dépenses
- Voir l'historique des transactions
- Consulter des statistiques

## Structure de la Base de Données

### Table `utilisateurs`
- `id` : Identifiant unique (AUTO_INCREMENT)
- `nom` : Nom de l'utilisateur
- `telephone` : Numéro de téléphone (VARCHAR, UNIQUE)

### Table `categories`
- `id` : Identifiant unique (AUTO_INCREMENT)
- `nom` : Nom de la catégorie
- `type` : Type de catégorie ('revenu' ou 'depense')

### Table `transactions`
- `id` : Identifiant unique (AUTO_INCREMENT)
- `id_utilisateur` : ID de l'utilisateur (FOREIGN KEY)
- `id_categorie` : ID de la catégorie (FOREIGN KEY)
- `montant` : Montant de la transaction
- `date` : Date de la transaction
- `description` : Description de la transaction

## Fonctionnalités Principales

### 1. Authentification
- Vérification du numéro de téléphone
- Code d'accès par défaut : #9999#
- Création automatique du compte si l'utilisateur n'existe pas
- Gestion de session pour maintenir l'utilisateur connecté

### 2. Gestion des Transactions
- Ajout de revenus (catégorie automatique)
- Ajout de dépenses (catégorie automatique)
- Historique des 5 dernières transactions
- Affichage simplifié des transactions (Revenu/Dépense)

### 3. Statistiques
- Affichage du solde total
- Total des revenus
- Total des dépenses

## Installation

1. Créer la base de données :
```sql
CREATE DATABASE dbbudget;
USE dbbudget;
```

2. Créer les tables :
```sql
-- Table des utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) NOT NULL UNIQUE
);

-- Table des catégories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    type ENUM('revenu', 'depense') NOT NULL
);

-- Table des transactions
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_categorie INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
    FOREIGN KEY (id_categorie) REFERENCES categories(id)
);
```

3. Initialiser les catégories :
```bash
php php/init_categories.php
```

## Utilisation

1. Accéder à l'application via : `http://localhost/jeremy-php/`
2. Se connecter avec :
   - Un numéro de téléphone (ex: 777030202)
   - Le code d'accès : #9999#
3. Utiliser le menu pour :
   - Voir le solde
   - Ajouter un revenu (montant et description uniquement)
   - Ajouter une dépense (montant et description uniquement)
   - Voir l'historique des transactions
   - Consulter les statistiques 