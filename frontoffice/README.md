# Projet Guerre Irana - Lancement avec Docker

Cette configuration demarre:
- PHP 8.2 + Apache (frontoffice)
- PHP 8.2 + Apache (backoffice)
- MySQL 8.4

Le serveur Apache est configure pour etre compatible avec le fichier `.htaccess` du projet:
- `mod_rewrite` actif
- `AllowOverride All` actif
- `FollowSymLinks` autorise

## 1) Initialiser les variables

Copier le fichier d'exemple:

```bash
cp .env.example .env
```

Sur Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

## 2) Lancer les conteneurs

Depuis le dossier `frontoffice`:

```bash
docker compose up -d --build
```

Depuis la racine du projet (commande unique pour front + back + DB):

```bash
docker compose -f frontoffice/docker-compose.yml up -d --build
```

## 3) Acces

- FrontOffice: http://localhost:8080
- BackOffice: http://localhost:8090/index.php
- MySQL: localhost:3306

## 4) Arreter

```bash
docker compose down
```

## 5) Reinitialiser completement la base

```bash
docker compose down -v
docker compose up -d --build
```

Le script SQL est charge automatiquement au premier demarrage de MySQL:
- `database/iran_conflict_schema_seed.sql`

## 6) Mettre a jour les contenus/images sans reset

Si la base existe deja, vous pouvez appliquer le patch incremental:

```powershell
Get-Content -Raw .\database\update_content_and_images.sql | docker exec -i guerre_irana_db mysql -uroot -proot_password
```

Ce patch:
- ajoute des articles publies supplementaires
- met a jour les images vers des URLs reelles (Wikimedia Commons)
- enrichit les tables tags/sources/actors/media/seo

## 7) Images reelles (style editorial)

Le front utilise des photos reelles (Wikimedia Commons) avec licences compatibles.

Sources et licences:
- `package/assets/images/REAL_IMAGE_SOURCES.md`

## 8) Test Lighthouse local (desktop + mobile)

Prerequis:
- Node.js installe
- site actif sur `http://localhost:8080`

Commande desktop:

```powershell
npx --yes lighthouse "http://localhost:8080/" --quiet --no-enable-error-reporting --chrome-flags="--headless --no-sandbox" --preset=desktop --output=json --output-path=".\desktop-lighthouse-full.json"
```

Commande mobile:

```powershell
npx --yes lighthouse "http://localhost:8080/" --quiet --no-enable-error-reporting --chrome-flags="--headless --no-sandbox" --output=json --output-path=".\mobile-lighthouse-full.json"
```

Les rapports JSON sont generes a la racine du projet et peuvent etre exploites pour le compte rendu.
