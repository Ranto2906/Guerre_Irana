# Backoffice - Gestion des contenus

Ce backoffice permet de gerer rapidement:
- les articles
- les categories
- les tags
- les evenements de chronologie
- le televersement d image de couverture pour les articles

## 1) Prerequis

- PHP 8.1+
- MySQL accessible (la base du frontoffice)

Si tu utilises Docker cote frontoffice, assure-toi que le conteneur DB est lance.

## 2) Configuration

Copier le fichier d exemple:

```powershell
Copy-Item .env.example .env
```

Adapter les variables si besoin:
- DB_HOST
- DB_PORT
- DB_DATABASE
- DB_USER
- DB_PASSWORD

## 3) Lancement Docker (front + back + DB)

Depuis la racine du projet, une seule commande demarre tout:

```powershell
docker compose -f frontoffice/docker-compose.yml up -d --build
```

Acces:
- FrontOffice: http://localhost:8080
- BackOffice: http://localhost:8090/index.php

## 4) Lancement local

Depuis le dossier backoffice:

```powershell
php -S localhost:8090 -t .
```

Puis ouvrir:
- http://localhost:8090/index.php

## 5) Notes

- Cette version est orientee demo/projet scolaire: pas d authentification.
- Les suppressions peuvent echouer si des contraintes de cle etrangere bloquent l operation.
- Le menu "Voir le FrontOffice" pointe vers ../frontoffice/package/modules.php si les deux projets sont sur la meme machine.
- Les images televersees sont stockees dans ../frontoffice/package/assets/images/uploads et la valeur sauvegardee dans l article est /package/assets/images/uploads/nom_fichier.
