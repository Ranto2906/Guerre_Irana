# Todo list - Projet site d'informations sur la guerre en Iran

## 1. Cadrage du projet
- [ ] Definir les objectifs du site (information, neutralite, cible, langue)
- [ ] Definir les types de contenus (articles, analyses, chronologie, dossiers, medias)
- [ ] Definir les roles du backoffice (admin, redacteur, editeur)
- [ ] Choisir la stack technique (langage, framework, base de donnees)
- [ ] Definir l'arborescence globale du site

## 2. Base de donnees
- [ ] Creer le schema de base de donnees
- [ ] Creer la table users (id, nom, email, mot_de_passe_hash, role, created_at)
- [ ] Creer la table categories (id, nom, slug, description)
- [ ] Creer la table articles (id, titre, slug, resume, contenu, image, auteur_id, statut, published_at, created_at, updated_at)
- [ ] Creer la table tags (id, nom, slug)
- [ ] Creer la table article_tags (article_id, tag_id)
- [ ] Ajouter index sur slug, published_at, categorie, statut
- [ ] Ajouter contraintes FK et regles de suppression coherentes
- [ ] Ajouter un jeu de donnees de test

## 3. URL rewriting et normalisation
- [ ] Definir une convention d'URL SEO (minuscules, tirets, sans accents)
- [ ] Mettre en place les routes lisibles:
- [ ] /
- [ ] /actualites
- [ ] /categorie/{slug}
- [ ] /article/{slug}
- [ ] /tag/{slug}
- [ ] /contact
- [ ] Gerer les redirections 301 vers l'URL canonique
- [ ] Eviter les doublons avec et sans slash final
- [ ] Bloquer ou normaliser les parametres inutiles (?id=...)

Brancher ces modèles dans modules.php pour afficher une vraie page accueil et liste.
Créer les routes FrontOffice via votre .htaccess vers des contrôleurs/pages dédiés.
Ajouter des vues simples dans assets et les pages 404/500.

## 4. FrontOffice
- [ ] Creer la page d'accueil (dernieres actualites + dossiers mis en avant)
- [ ] Creer la liste des articles avec pagination
- [ ] Creer la page detail article
- [ ] Creer les pages categorie et tag
- [ ] Ajouter recherche simple (titre, resume, contenu)
- [ ] Ajouter pages statiques (A propos, Contact, Mentions legales)
- [ ] Ajouter fil d'Ariane
- [ ] Ajouter gestion des erreurs 404 / 500

## 5. BackOffice
- [ ] Creer ecran de connexion (authentification securisee)
- [ ] Creer tableau de bord (statistiques de contenu)
- [ ] Creer CRUD categories
- [ ] Creer CRUD tags
- [ ] Creer CRUD articles
- [ ] Ajouter upload image avec validation (taille, type)
- [ ] Ajouter brouillon / publication / depublication
- [ ] Ajouter gestion des utilisateurs et roles
- [ ] Ajouter historique minimal (date creation, modif, auteur)

## 6. SEO on-page (obligatoire)
- [ ] Verifier structure semantique des pages (header, main, section, article, footer)
- [ ] Utiliser un seul H1 par page
- [ ] Utiliser H2 a H6 de facon logique et hierarchique
- [ ] Ajouter title unique sur chaque page
- [ ] Ajouter meta description unique sur chaque page
- [ ] Ajouter balise canonical
- [ ] Ajouter balises Open Graph (og:title, og:description, og:image, og:url)
- [ ] Ajouter attribut alt pertinent sur toutes les images
- [ ] Ajouter sitemap.xml
- [ ] Ajouter robots.txt

## 7. Performance et qualite
- [ ] Compresser les images (WebP/AVIF si possible)
- [ ] Activer cache navigateur
- [ ] Minifier CSS/JS
- [ ] Charger les images en lazy loading
- [ ] Eviter les scripts bloquants
- [ ] Verifier responsive mobile/tablette/desktop

## 8. Tests Lighthouse local (mobile et ordinateur)
- [ ] Lancer Lighthouse en local sur la page d'accueil (mobile)
- [ ] Lancer Lighthouse en local sur la page article (mobile)
- [ ] Lancer Lighthouse en local sur la page d'accueil (desktop)
- [ ] Lancer Lighthouse en local sur la page article (desktop)
- [ ] Corriger les points bloquants SEO/Performance/Accessibilite
- [ ] Repasser les tests jusqu'a score cible (ex: >= 90)

## 9. Securite minimale
- [ ] Valider et nettoyer toutes les entrees utilisateur
- [ ] Proteger le backoffice par controle de role
- [ ] Limiter les tentatives de connexion

## 10. Livraison
- [ ] Preparer script SQL d'installation
- [ ] Rediger README (installation, config, lancement)
- [ ] Ajouter .env.example
- [ ] Faire une recette fonctionnelle finale
- [ ] Preparer demonstration (frontoffice + backoffice + SEO)

## Bonus (si temps)
- [ ] Ajouter systeme multi-langue
- [ ] Ajouter newsletter
- [ ] Ajouter export CSV des articles
- [ ] Ajouter plan de publication editoriale
