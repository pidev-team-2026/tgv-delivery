# MyApp - Plateforme de Réclamations

Application Symfony 6.4 de gestion des réclamations clients.

## Structure

- **Front office** (template Clinic) : `/` — Les clients peuvent créer, consulter, modifier et supprimer leurs réclamations
- **Back office** (template Duralux) : `/admin` — Les administrateurs peuvent gérer les réclamations, répondre, modifier et supprimer les réponses

## Installation

1. **Base de données MySQL**
   - Créer la base `myapp` et importer `../myapp.sql` (depuis phpMyAdmin ou ligne de commande)
   - Configurer `DATABASE_URL` dans `.env` si nécessaire

2. **Dépendances**
   ```bash
   composer install
   ```

3. **Lancer le serveur**
   ```bash
   symfony server:start
   # ou
   php -S localhost:8000 -t public
   ```

## Utilisation

### Front office (client)
- Aller sur `/` (accueil)
- Cliquer sur "Se connecter / Choisir mon compte" pour sélectionner un utilisateur (comptes de démo dans la base)
- Créer une réclamation, la suivre, la modifier ou la supprimer

### Back office (admin)
- Aller sur `/admin` ou `/admin/reclamations`
- Rechercher et trier les réclamations
- Voir une réclamation, modifier son statut
- Ajouter, modifier ou supprimer des réponses

## Entités

- **User** : id, email, name (avec contraintes de validation)
- **Reclamation** : id, user_id, subject, message, status, created_at, updated_at
- **Reponse** : id, reclamation_id, content, author, created_at, updated_at

Les validations (Assert) sont définies dans les entités.
