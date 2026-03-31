# 🚀 Déploiement avec Docker Compose

## Prérequis
- Docker & Docker Compose installés
- Port 8080, 3306 disponibles (configurable dans `.env`)
- Port 8090 optionnel (pour accès backoffice en dev autonome seulement)

## Démarrage rapide

### 1️⃣ Clone ou extraction du projet
```bash
cd frontoffice
```

### 2️⃣ Configuration (optionnel - les defaults conviennent pour dev)
Vérifier que les fichiers `.env` existent :
- `frontoffice/.env`
- `backoffice/.env`

Si besoin de modifier les ports ou identifiants, éditer les fichiers `.env`.

### 3️⃣ Lancer les conteneurs
```bash
docker-compose up -d
```

Attendre ~15 secondes que MySQL soit "healthy" et que les scripts d'initialisation s'exécutent.

## Accès aux services

https://github.com/Ranto2906/Guerre_Irana.git

- **FrontOffice** : http://localhost:8080/
- **BackOffice** : http://localhost:8080/admin/
- **Base de données** (MySQL) : localhost:3306

### Identifiants BackOffice
```
Utilisateur : user
Mot de passe : pass
```

### Base de données
```
Nom : iran_info_site
Utilisateur : app_user
Mot de passe : app_password
```

## À la première exécution

✅ MySQL crée automatiquement :
- Base de données `iran_info_site`
- Tables complètes (schéma)
- 8 articles publiés avec données seed
- Utilisateurs, catégories, tags, conflits, chronologie

✅ FrontOffice affiche les articles + pagination

✅ BackOffice permet de créer/modifier/supprimer :
- Articles (avec upload d'images)
- Catégories
- Tags
- Événements chronologie

## Vérifier que tout fonctionne

### Terminal 1 : Voir les logs
```bash
docker-compose logs -f
```

### Terminal 2 : Tester une requête
```bash
curl http://localhost:8080/
# Doit retourner du HTML avec "Iran Info"
```

### Tester l'upload d'image

1. Accéder au BackOffice : http://localhost:8080/admin/
2. Se connecter (user / pass)
3. Articles → Créer ou Modifier
4. Upload une image JPG, PNG ou WEBP
5. Vérifier qu'elle s'enregistre dans la base de données

Si erreur d'upload, vérifier les logs :
```bash
docker logs guerre_irana_backoffice
```

## Arrêter les conteneurs

```bash
docker-compose down
```

Pour aussi supprimer les données de la base (et tout recréer à la prochaine exécution) :
```bash
docker-compose down -v
```

## Troubleshooting

### "Connection refused" sur la base de données
→ Attendre que MySQL soit ready (voir logs Docker)

### BackOffice inaccessible via 8080/admin/
→ Vérifier que `../backoffice` est bien monté dans `volumes` du service `web`

### Upload d'images échoue
→ Vérifier dossier `frontoffice/package/assets/images/uploads/` existe et permissions 777

### Scripts SQL ne s'exécutent pas
→ S'assure que le chemin des volumes dans `docker-compose.yml` est bon :
```yaml
volumes:
  - ./database/iran_conflict_schema_seed.sql:/docker-entrypoint-initdb.d/01-init.sql:ro
  - ./database/update_content_and_images.sql:/docker-entrypoint-initdb.d/02-update.sql:ro
```

---

✅ **Ready to deploy !**
