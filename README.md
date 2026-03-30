# Projet Guerre Irana - Lancement avec Docker

Cette configuration demarre:
- PHP 8.2 + Apache
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

```bash
docker compose up -d --build
```

## 3) Acces

- Site: http://localhost:8080
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
