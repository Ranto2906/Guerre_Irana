# Todo list - Projet site d'informations sur la guerre en Iran (format detaille)

Colonnes: ID | Lot | Sous-lot | Page/Module | Tache | Type | Responsable | Est(h) | Reel(h) | Ecart | Avancement

| ID | Lot | Sous-lot | Page/Module | Tache | Type | Responsable | Est(h) | Reel(h) | Ecart | Avancement |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | CADRAGE | Objectifs | Doc | Definir objectifs du site (info, neutralite, cible, langue) | Documentation | BINOME | 1 | 0 | 1 | 0,00% |
| 2 | CADRAGE | Contenus | Doc | Definir types de contenus (articles, analyses, chronologie, dossiers, medias) | Documentation | BINOME | 1 | 0 | 1 | 0,00% |
| 3 | CADRAGE | Roles | Doc | Definir roles backoffice (admin, redacteur, editeur) | Documentation | BINOME | 1 | 0 | 1 | 0,00% |
| 4 | CADRAGE | Stack | Env | Choisir stack technique (PHP/Apache/MySQL/Docker) | Integration | BINOME | 1 | 1 | 0 | 100,00% |
| 5 | CADRAGE | Arbo | Repo | Definir arborescence globale (frontoffice/backoffice) | Integration | BINOME | 1 | 1 | 0 | 100,00% |
| 6 | DB | Schema | Database | Script schema + seed | Integration | ETU003103 | 1 | 1 | 0 | 100,00% |
| 7 | DB | Donnees test | Database | Patch update content/images | Integration | ETU003103 | 1 | 1 | 0 | 100,00% |
| 8 | DB | Tables | Database | Verifier tables users/categories/articles/tags/article_tags | Integration | ETU003103 | 1 | 0 | 1 | 0,00% |
| 9 | DB | Index | Database | Verifier index slug/published_at/status | Integration | ETU003103 | 1 | 0 | 1 | 0,00% |
| 10 | DB | FK | Database | Verifier contraintes et cascades | Integration | ETU003103 | 1 | 0 | 1 | 0,00% |
| 11 | ROUTING | Rewrite | .htaccess | Routes / et /actualites | Integration | ETU003248 | 1 | 1 | 0 | 100,00% |
| 12 | ROUTING | Rewrite | .htaccess | Route /article/{slug} | Integration | ETU003248 | 1 | 1 | 0 | 100,00% |
| 13 | ROUTING | Rewrite | .htaccess | Route /categorie/{slug} | Integration | ETU003248 | 1 | 0 | 1 | 0,00% |
| 14 | ROUTING | Rewrite | .htaccess | Route /tag/{slug} | Integration | ETU003248 | 1 | 0 | 1 | 0,00% |
| 15 | ROUTING | Rewrite | .htaccess | Route /contact | Integration | ETU003248 | 1 | 0 | 1 | 0,00% |
| 16 | ROUTING | Canonical | .htaccess | Redirections 301 canonique (partiel) | Integration | ETU003248 | 1 | 0.5 | 0.5 | 50,00% |
| 17 | ROUTING | Clean URLs | .htaccess | Normaliser slash final | Integration | ETU003248 | 1 | 0 | 1 | 0,00% |
| 18 | ROUTING | Params | .htaccess | Bloquer/normaliser parametres inutiles (?id=...) | Integration | ETU003248 | 1 | 0 | 1 | 0,00% |
| 19 | FO | Home | modules.php | Page accueil (latest + featured) | Affichage | ETU003248 | 1 | 1 | 0 | 100,00% |
| 20 | FO | Actualites | modules.php | Liste articles + pagination | Affichage | ETU003248 | 1 | 1 | 0 | 100,00% |
| 21 | FO | Article | modules.php | Page detail article | Affichage | ETU003248 | 1 | 1 | 0 | 100,00% |
| 22 | FO | Categories | modules.php | Page categorie | Affichage | ETU003248 | 1 | 0 | 1 | 0,00% |
| 23 | FO | Tags | modules.php | Page tag | Affichage | ETU003248 | 1 | 0 | 1 | 0,00% |
| 24 | FO | Recherche | modules.php | Recherche simple (titre/resume/contenu) | Metier | ETU003248 | 1 | 0 | 1 | 0,00% |
| 25 | FO | Statiques | pages | Page a propos | Affichage | ETU003248 | 1 | 0 | 1 | 0,00% |
| 26 | FO | Statiques | pages | Page contact | Affichage | ETU003248 | 1 | 0 | 1 | 0,00% |
| 27 | FO | Statiques | pages | Page mentions legales | Affichage | ETU003248 | 1 | 0 | 1 | 0,00% |
| 28 | FO | Navigation | UI | Fil d Ariane | Affichage | ETU003248 | 1 | 0 | 1 | 0,00% |
| 29 | FO | Erreurs | UI | Pages 404 et 500 | Affichage | ETU003248 | 1 | 0 | 1 | 0,00% |
| 30 | BO | Auth | index.php | Auth basique + CSRF | Integration | ETU003103 | 1 | 1 | 0 | 100,00% |
| 31 | BO | Forms | pages | Formulaires articles/categories/tags/chronologie | Affichage | ETU003103 | 1 | 1 | 0 | 100,00% |
| 32 | BO | CRUD | categories | Verifier CRUD categories (liste/create/edit/delete) | Metier | ETU003103 | 1 | 0 | 1 | 0,00% |
| 33 | BO | CRUD | tags | Verifier CRUD tags (liste/create/edit/delete) | Metier | ETU003103 | 1 | 0 | 1 | 0,00% |
| 34 | BO | CRUD | articles | Verifier CRUD articles (liste/create/edit/delete) | Metier | ETU003103 | 1 | 0 | 1 | 0,00% |
| 35 | BO | CRUD | timeline | Verifier CRUD chronologie | Metier | ETU003103 | 1 | 0 | 1 | 0,00% |
| 36 | BO | Upload | articles | Ajouter validation type/taille image | Metier | ETU003103 | 1 | 0 | 1 | 0,00% |
| 37 | BO | Statut | articles | Brouillon/publication/depublication | Metier | ETU003103 | 1 | 0 | 1 | 0,00% |
| 38 | BO | Users | admin | Gestion utilisateurs/roles | Metier | ETU003103 | 1 | 0 | 1 | 0,00% |
| 39 | BO | Audit | admin | Historique creation/modif/auteur | Metier | ETU003103 | 1 | 0 | 1 | 0,00% |
| 40 | BO | Security | auth | Limiter tentatives login | Metier | ETU003103 | 1 | 0 | 1 | 0,00% |
| 41 | SEO | Head | modules.php | Title + meta description | SEO | ETU003248 | 1 | 1 | 0 | 100,00% |
| 42 | SEO | Canonical | modules.php | Canonical + og tags + twitter card | SEO | ETU003248 | 1 | 1 | 0 | 100,00% |
| 43 | SEO | Images | modules.php | Alt sur images principales | SEO | ETU003248 | 1 | 1 | 0 | 100,00% |
| 44 | SEO | Structure | templates | Verifier structure semantique | SEO | ETU003248 | 1 | 0 | 1 | 0,00% |
| 45 | SEO | Structure | templates | Un seul H1 par page | SEO | ETU003248 | 1 | 0 | 1 | 0,00% |
| 46 | SEO | Structure | templates | Hierarchie H2 a H6 coherente | SEO | ETU003248 | 1 | 0 | 1 | 0,00% |
| 47 | SEO | Files | root | Ajouter sitemap.xml | SEO | ETU003248 | 1 | 0 | 1 | 0,00% |
| 48 | SEO | Files | root | Ajouter robots.txt | SEO | ETU003248 | 1 | 0 | 1 | 0,00% |
| 49 | PERF | Images | assets | Lazy loading images | Perf | ETU003248 | 1 | 1 | 0 | 100,00% |
| 50 | PERF | Images | assets | Compression WebP/AVIF | Perf | ETU003248 | 1 | 0 | 1 | 0,00% |
| 51 | PERF | Cache | apache | Cache navigateur | Perf | ETU003248 | 1 | 0 | 1 | 0,00% |
| 52 | PERF | Assets | css/js | Minifier CSS/JS | Perf | ETU003248 | 1 | 0 | 1 | 0,00% |
| 53 | PERF | JS | pages | Eviter scripts bloquants | Perf | ETU003248 | 1 | 0 | 1 | 0,00% |
| 54 | PERF | UI | responsive | Tester mobile/tablet/desktop | Perf | ETU003248 | 1 | 0 | 1 | 0,00% |
| 55 | TESTS | Lighthouse | local | Run mobile home | QA | BINOME | 1 | 0 | 1 | 0,00% |
| 56 | TESTS | Lighthouse | local | Run mobile article | QA | BINOME | 1 | 0 | 1 | 0,00% |
| 57 | TESTS | Lighthouse | local | Run desktop home | QA | BINOME | 1 | 0 | 1 | 0,00% |
| 58 | TESTS | Lighthouse | local | Run desktop article | QA | BINOME | 1 | 0 | 1 | 0,00% |
| 59 | TESTS | Fixes | local | Corriger points bloquants SEO/Perf/Accessibilite | QA | BINOME | 1 | 0 | 1 | 0,00% |
| 60 | TESTS | Retest | local | Repasser tests jusqua score cible (>= 90) | QA | BINOME | 1 | 0 | 1 | 0,00% |
| 61 | SECURITE | Inputs | app | Validation et nettoyage des entrees | Metier | ETU003103 | 1 | 0 | 1 | 0,00% |
| 62 | SECURITE | Roles | backoffice | Controle de role | Metier | ETU003103 | 1 | 0 | 1 | 0,00% |
| 63 | DOC | Technique | doc | Liste membres + ETU | Documentation | BINOME | 1 | 0 | 1 | 0,00% |
| 64 | DOC | Technique | doc | Stack technique | Documentation | BINOME | 1 | 0 | 1 | 0,00% |
| 65 | DOC | Technique | doc | MCD / modelisation base | Documentation | BINOME | 1 | 0 | 1 | 0,00% |
| 66 | DOC | Technique | doc | Scenarios utilisation | Documentation | BINOME | 1 | 0 | 1 | 0,00% |
| 67 | DOC | Technique | doc | Captures FO/BO | Documentation | BINOME | 1 | 0 | 1 | 0,00% |
| 68 | DOC | Technique | doc | Login BO user/pass par defaut | Documentation | BINOME | 1 | 0 | 1 | 0,00% |
| 69 | DOC | README | repo | README final (install/config/lancement) | Documentation | BINOME | 1 | 0 | 1 | 0,00% |
| 70 | DELIV | Docker | compose | Docker compose front + back + DB | Integration | BINOME | 1 | 1 | 0 | 100,00% |
| 71 | DELIV | Env | repo | .env.example frontoffice + backoffice | Integration | BINOME | 1 | 1 | 0 | 100,00% |
| 72 | DELIV | SQL | database | Script installation SQL (seed) | Integration | BINOME | 1 | 1 | 0 | 100,00% |
| 73 | DELIV | Zip | livraison | Zip livrable (site + conteneurs) | Integration | BINOME | 1 | 0 | 1 | 0,00% |
| 74 | DELIV | Repo | git | Depot public (gitlab/github) | Integration | BINOME | 1 | 0 | 1 | 0,00% |
| 75 | DELIV | Demo | final | Recette finale + demo | QA | BINOME | 1 | 0 | 1 | 0,00% |
