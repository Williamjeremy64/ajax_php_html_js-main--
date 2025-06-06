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
- `telephone` : Numéro de compte (VARCHAR, UNIQUE)
- `nom` : Nom de l'utilisateur
- `code` : Code d'accès

### Table `comptes`
- `id` : Identifiant unique (AUTO_INCREMENT)
- `numero_compte` : Numéro de compte (VARCHAR, UNIQUE)
- `solde` : Solde actuel (DECIMAL)
- Clé étrangère vers `utilisateurs(telephone)`

### Table `transactions`
- `id` : Identifiant unique (AUTO_INCREMENT)
- `numero_compte` : Numéro de compte
- `type` : Type de transaction ('revenu' ou 'depense')
- `montant` : Montant de la transaction
- `description` : Description de la transaction
- `date_transaction` : Date et heure de la transaction
- Clé étrangère vers `comptes(numero_compte)`

## Fonctionnalités Principales

### 1. Authentification
- Vérification du numéro de compte et du code
- Accès au menu principal après authentification

### 2. Gestion du Solde
- Affichage du solde actuel
- Mise à jour automatique après chaque transaction

### 3. Transactions
- Ajout de revenus
- Ajout de dépenses
- Vérification du solde suffisant pour les dépenses
- Historique des 5 dernières transactions

### 4. Statistiques
- Affichage du solde total
- Total des revenus
- Total des dépenses

## Installation

1. Créer la base de données :
```sql
CREATE DATABASE dbbudget;
```

2. Créer les tables :
```sql
-- Table des utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telephone VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL
);

-- Table des comptes
CREATE TABLE comptes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_compte VARCHAR(20) NOT NULL UNIQUE,
    solde DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (numero_compte) REFERENCES utilisateurs(telephone)
);

-- Table des transactions
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_compte VARCHAR(20) NOT NULL,
    type ENUM('revenu', 'depense') NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    description TEXT,
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (numero_compte) REFERENCES comptes(numero_compte)
);
```

3. Insérer un utilisateur de test :
```sql
INSERT INTO utilisateurs (telephone, nom, code) VALUES 
('123456', 'Test User', '#9999#');

INSERT INTO comptes (numero_compte, solde) VALUES 
('123456', 100000.00);
```

## Utilisation

1. Accéder à l'application via : `http://localhost/jeremy-php/`
2. Se connecter avec :
   - Numéro de compte : 123456
   - Code : #9999#
3. Utiliser le menu pour :
   - Voir le solde (1)
   - Ajouter un revenu (2)
   - Ajouter une dépense (3)
   - Voir les transactions (4)
   - Voir les statistiques (5) 