-- =====================================================
-- Projet: Site d'information sur les conflits impliquant l'Iran
-- SGBD cible: MySQL 8+
-- Ce script contient:
-- 1) Schema des tables
-- 2) Donnees initiales (seed)
-- =====================================================

CREATE DATABASE IF NOT EXISTS iran_info_site
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE iran_info_site;

SET NAMES utf8mb4;

-- -----------------------------------------------------
-- Nettoyage (utile en dev)
-- -----------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS redirect_rules;
DROP TABLE IF EXISTS seo_pages;
DROP TABLE IF EXISTS media_assets;
DROP TABLE IF EXISTS event_actors;
DROP TABLE IF EXISTS timeline_events;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS article_actors;
DROP TABLE IF EXISTS actors;
DROP TABLE IF EXISTS article_sources;
DROP TABLE IF EXISTS sources;
DROP TABLE IF EXISTS article_tags;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS conflicts;
SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------
-- Utilisateurs (backoffice)
-- -----------------------------------------------------
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'editor', 'author') NOT NULL DEFAULT 'author',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Conflits (niveau macro)
-- -----------------------------------------------------
CREATE TABLE conflicts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  start_date DATE NULL,
  end_date DATE NULL,
  status ENUM('ongoing', 'ended', 'paused') NOT NULL DEFAULT 'ongoing',
  summary TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Taxonomie editoriale
-- -----------------------------------------------------
CREATE TABLE categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  description TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE tags (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- Articles FrontOffice
-- -----------------------------------------------------
CREATE TABLE articles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conflict_id BIGINT UNSIGNED NULL,
  category_id BIGINT UNSIGNED NOT NULL,
  author_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  excerpt TEXT NOT NULL,
  content LONGTEXT NOT NULL,
  cover_image_url VARCHAR(255) NULL,
  cover_image_alt VARCHAR(255) NULL,
  status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_articles_conflict FOREIGN KEY (conflict_id) REFERENCES conflicts(id) ON DELETE SET NULL,
  CONSTRAINT fk_articles_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
  CONSTRAINT fk_articles_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_articles_status_published (status, published_at),
  INDEX idx_articles_category (category_id)
);

CREATE TABLE article_tags (
  article_id BIGINT UNSIGNED NOT NULL,
  tag_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (article_id, tag_id),
  CONSTRAINT fk_article_tags_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
  CONSTRAINT fk_article_tags_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Sources et verification
-- -----------------------------------------------------
CREATE TABLE sources (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  publisher VARCHAR(140) NOT NULL,
  source_url VARCHAR(500) NOT NULL,
  source_type ENUM('official', 'media', 'ngo', 'research') NOT NULL,
  language_code CHAR(2) NOT NULL DEFAULT 'fr',
  published_at DATETIME NULL,
  reliability_score TINYINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sources_publisher (publisher)
);

CREATE TABLE article_sources (
  article_id BIGINT UNSIGNED NOT NULL,
  source_id BIGINT UNSIGNED NOT NULL,
  note VARCHAR(255) NULL,
  PRIMARY KEY (article_id, source_id),
  CONSTRAINT fk_article_sources_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
  CONSTRAINT fk_article_sources_source FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Acteurs (Etats, institutions, groupes)
-- -----------------------------------------------------
CREATE TABLE actors (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(140) NOT NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  actor_type ENUM('state', 'institution', 'group', 'person') NOT NULL,
  country VARCHAR(100) NULL,
  description TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE article_actors (
  article_id BIGINT UNSIGNED NOT NULL,
  actor_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (article_id, actor_id),
  CONSTRAINT fk_article_actors_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
  CONSTRAINT fk_article_actors_actor FOREIGN KEY (actor_id) REFERENCES actors(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Lieux + chronologie
-- -----------------------------------------------------
CREATE TABLE locations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(140) NOT NULL,
  country VARCHAR(100) NOT NULL,
  latitude DECIMAL(10, 7) NULL,
  longitude DECIMAL(10, 7) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE timeline_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conflict_id BIGINT UNSIGNED NOT NULL,
  location_id BIGINT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  event_date DATE NOT NULL,
  summary TEXT NOT NULL,
  details LONGTEXT NULL,
  verification_status ENUM('verified', 'under_review') NOT NULL DEFAULT 'under_review',
  primary_source_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_timeline_conflict FOREIGN KEY (conflict_id) REFERENCES conflicts(id) ON DELETE CASCADE,
  CONSTRAINT fk_timeline_location FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
  CONSTRAINT fk_timeline_source FOREIGN KEY (primary_source_id) REFERENCES sources(id) ON DELETE SET NULL,
  INDEX idx_timeline_date (event_date)
);

CREATE TABLE event_actors (
  event_id BIGINT UNSIGNED NOT NULL,
  actor_id BIGINT UNSIGNED NOT NULL,
  role_in_event VARCHAR(120) NULL,
  PRIMARY KEY (event_id, actor_id),
  CONSTRAINT fk_event_actors_event FOREIGN KEY (event_id) REFERENCES timeline_events(id) ON DELETE CASCADE,
  CONSTRAINT fk_event_actors_actor FOREIGN KEY (actor_id) REFERENCES actors(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Medias (images)
-- -----------------------------------------------------
CREATE TABLE media_assets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  article_id BIGINT UNSIGNED NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  title VARCHAR(180) NULL,
  alt_text VARCHAR(255) NOT NULL,
  caption VARCHAR(255) NULL,
  credit VARCHAR(180) NULL,
  mime_type VARCHAR(60) NULL,
  width INT UNSIGNED NULL,
  height INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_media_assets_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- SEO avance + redirections
-- -----------------------------------------------------
CREATE TABLE seo_pages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_type ENUM('home', 'category', 'article', 'tag', 'static') NOT NULL,
  page_ref_id BIGINT UNSIGNED NULL,
  title_tag VARCHAR(70) NOT NULL,
  meta_description VARCHAR(160) NOT NULL,
  canonical_url VARCHAR(255) NOT NULL,
  og_title VARCHAR(90) NULL,
  og_description VARCHAR(200) NULL,
  og_image_url VARCHAR(255) NULL,
  robots_index TINYINT(1) NOT NULL DEFAULT 1,
  robots_follow TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_seo_page (page_type, page_ref_id)
);

CREATE TABLE redirect_rules (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  old_path VARCHAR(255) NOT NULL,
  new_path VARCHAR(255) NOT NULL,
  http_code SMALLINT UNSIGNED NOT NULL DEFAULT 301,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_redirect_old_path (old_path)
);

-- =====================================================
-- DONNEES INITIALES (SEED)
-- Note: base editoriale de depart. Completer avec sources verifiees.
-- =====================================================

INSERT INTO users (full_name, email, password_hash, role) VALUES
('Admin Site', 'admin@iran-info.local', '$2y$10$replace_with_real_bcrypt_hash', 'admin'),
('Redaction', 'redaction@iran-info.local', '$2y$10$replace_with_real_bcrypt_hash', 'editor');

INSERT INTO conflicts (title, slug, start_date, end_date, status, summary) VALUES
('Guerre Iran-Irak', 'guerre-iran-irak', '1980-09-22', '1988-08-20', 'ended', 'Conflit majeur entre l Iran et l Irak de 1980 a 1988.'),
('Tensions regionales impliquant l Iran', 'tensions-regionales-iran', '2019-01-01', NULL, 'ongoing', 'Serie de tensions diplomatiques et militaires dans la region.');

INSERT INTO categories (name, slug, description) VALUES
('Actualites', 'actualites', 'Dernieres informations et mises a jour.'),
('Analyses', 'analyses', 'Contextes et decryptages.'),
('Chronologie', 'chronologie', 'Evenements classes par date.'),
('Diplomatie', 'diplomatie', 'Negociations et relations internationales.'),
('Humanitaire', 'humanitaire', 'Impacts sur les populations civiles.');

INSERT INTO tags (name, slug) VALUES
('cesser-le-feu', 'cesser-le-feu'),
('onu', 'onu'),
('sanctions', 'sanctions'),
('negociations', 'negociations'),
('energie', 'energie'),
('nucleaire', 'nucleaire');

INSERT INTO sources (title, publisher, source_url, source_type, language_code, published_at, reliability_score) VALUES
('Resolution 598 du Conseil de securite', 'Nations Unies', 'https://www.un.org/securitycouncil/', 'official', 'fr', '1987-07-20 00:00:00', 5),
('Dossier historique sur la guerre Iran-Irak', 'Britannica', 'https://www.britannica.com/', 'research', 'en', '2024-01-01 00:00:00', 4),
('Fiche pays et contexte Iran', 'Banque mondiale', 'https://www.worldbank.org/', 'research', 'fr', '2024-01-01 00:00:00', 4),
('Communiques diplomatiques', 'Ministere des Affaires etrangeres', 'https://www.diplomatie.gouv.fr/', 'official', 'fr', '2025-01-15 00:00:00', 4);

INSERT INTO actors (name, slug, actor_type, country, description) VALUES
('Iran', 'iran', 'state', 'Iran', 'Etat de la region moyen-orientale.'),
('Irak', 'irak', 'state', 'Irak', 'Etat frontalier de l Iran.'),
('Nations Unies', 'nations-unies', 'institution', NULL, 'Organisation internationale.'),
('AIEA', 'aiea', 'institution', NULL, 'Agence internationale de l energie atomique.');

INSERT INTO locations (name, country, latitude, longitude) VALUES
('Teheran', 'Iran', 35.6891980, 51.3889736),
('Bagdad', 'Irak', 33.3152410, 44.3660653),
('Khorramshahr', 'Iran', 30.4256210, 48.1891185),
('Frontiere Iran-Irak', 'Iran', NULL, NULL);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at) VALUES
(1, 3, 1,
'Chronologie: debut de la guerre Iran-Irak',
'chronologie-debut-guerre-iran-irak',
'Retour sur le declenchement du conflit en 1980.',
'<h1>Chronologie du debut du conflit</h1><h2>Contexte</h2><p>Le conflit eclate en septembre 1980 dans un contexte regional tendu.</p><h2>Consequences initiales</h2><p>Le front se stabilise apres une phase d avances rapides.</p>',
'/uploads/chronologie-1980.jpg',
'Archive historique de la frontiere Iran-Irak en 1980',
'published',
'2026-03-01 09:00:00'),
(1, 2, 2,
'Analyse: impact humain de la guerre Iran-Irak',
'analyse-impact-humain-guerre-iran-irak',
'Bilan humain et consequences sociales du conflit.',
'<h1>Impact humain</h1><h2>Pertes civiles</h2><p>Le conflit a eu des repercussions durables sur les populations.</p><h2>Deplacements</h2><p>Des deplacements de population ont ete observes dans plusieurs zones.</p>',
'/uploads/impact-humain.jpg',
'Population civile affectee par le conflit',
'published',
'2026-03-05 10:30:00'),
(2, 4, 1,
'Diplomatie regionale: etat des discussions recentes',
'diplomatie-regionale-discussions-recentes',
'Synthese des canaux diplomatiques et des points de blocage.',
'<h1>Etat de la diplomatie regionale</h1><h2>Canaux de dialogue</h2><p>Plusieurs canaux indirects restent actifs.</p><h2>Points de blocage</h2><p>Les divergences portent sur securite et sanctions.</p>',
'/uploads/diplomatie.jpg',
'Table de discussion diplomatique',
'published',
'2026-03-10 14:00:00');

INSERT INTO article_tags (article_id, tag_id) VALUES
(1, 2),
(1, 1),
(2, 1),
(3, 4),
(3, 3),
(3, 6);

INSERT INTO article_sources (article_id, source_id, note) VALUES
(1, 1, 'Texte de reference institutionnel'),
(1, 2, 'Contexte historique'),
(2, 2, 'Base de synthese historique'),
(3, 3, 'Contexte socio-economique'),
(3, 4, 'Suivi diplomatique');

INSERT INTO article_actors (article_id, actor_id) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 2),
(3, 1),
(3, 3),
(3, 4);

INSERT INTO timeline_events (conflict_id, location_id, title, slug, event_date, summary, details, verification_status, primary_source_id) VALUES
(1, 4,
'Invasion initiale et ouverture du front',
'invasion-initiale-ouverture-front',
'1980-09-22',
'Ouverture officielle des hostilites entre Iran et Irak.',
'Resumes historiques convergent sur une phase d escalade rapide en 1980.',
'verified',
2),
(1, 3,
'Reprise de Khorramshahr par l Iran',
'reprise-khorramshahr-par-iran',
'1982-05-24',
'Contre-offensive iranienne et reprise de la ville.',
'Evenement cle de la chronologie militaire du conflit.',
'verified',
2),
(1, 1,
'Acceptation du cessez-le-feu sous egide de l ONU',
'acceptation-cessez-le-feu-onu',
'1988-08-20',
'Entree en vigueur du cessez-le-feu sur la base de la resolution 598.',
'La resolution 598 sert de cadre politique a l arret des combats.',
'verified',
1),
(2, 1,
'Cycle de discussions indirectes',
'cycle-discussions-indirectes',
'2025-02-10',
'Reprise d echanges indirects sur les dossiers securitaires.',
'Donnees de depart a mettre a jour selon les communiques officiels.',
'under_review',
4);

INSERT INTO event_actors (event_id, actor_id, role_in_event) VALUES
(1, 1, 'partie au conflit'),
(1, 2, 'partie au conflit'),
(2, 1, 'offensive'),
(2, 2, 'defense'),
(3, 1, 'acceptation cessez-le-feu'),
(3, 2, 'acceptation cessez-le-feu'),
(3, 3, 'mediation'),
(4, 1, 'delegation diplomatique'),
(4, 3, 'cadre international');

INSERT INTO media_assets (article_id, file_path, title, alt_text, caption, credit, mime_type, width, height) VALUES
(1, '/uploads/chronologie-1980.jpg', 'Archive front 1980', 'Image d archive du front en 1980', 'Contexte historique au debut du conflit', 'Archive publique', 'image/jpeg', 1600, 900),
(2, '/uploads/impact-humain.jpg', 'Impact humain', 'Population civile dans une zone touchee', 'Illustration de l impact civil', 'Photojournalisme', 'image/jpeg', 1600, 900),
(3, '/uploads/diplomatie.jpg', 'Discussion diplomatique', 'Participants autour d une table de negociation', 'Discussion multilaterale', 'Service communication', 'image/jpeg', 1600, 900);

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow) VALUES
('home', NULL, 'Actualites Iran: conflits, analyses et chronologie', 'Suivez les informations verifiees sur les conflits impliquant l Iran: chronologie, analyses, diplomatie.', '/','Actualites Iran','Informations verifiees sur les conflits impliquant l Iran.','/uploads/og-home.jpg',1,1),
('article', 1, 'Chronologie du debut de la guerre Iran-Irak', 'Comprendre les premieres etapes de la guerre Iran-Irak a travers une chronologie claire.', '/article/chronologie-debut-guerre-iran-irak','Chronologie guerre Iran-Irak','Les faits essentiels du debut du conflit.','/uploads/chronologie-1980.jpg',1,1),
('article', 2, 'Impact humain de la guerre Iran-Irak', 'Analyse des impacts humains et sociaux de la guerre Iran-Irak.', '/article/analyse-impact-humain-guerre-iran-irak','Impact humain guerre Iran-Irak','Synthese des consequences humaines.','/uploads/impact-humain.jpg',1,1),
('article', 3, 'Diplomatie regionale et discussions recentes', 'Point sur les discussions diplomatiques et les principaux enjeux regionaux.', '/article/diplomatie-regionale-discussions-recentes','Diplomatie regionale Iran','Etat des discussions recentes et des blocages.','/uploads/diplomatie.jpg',1,1);

INSERT INTO redirect_rules (old_path, new_path, http_code, is_active) VALUES
('/pages/modules.php?id=1&idcat=3', '/article/chronologie-debut-guerre-iran-irak', 301, 1),
('/pages/modules.php?id=2&idcat=2', '/article/analyse-impact-humain-guerre-iran-irak', 301, 1);

-- =====================================================
-- REQUETES PRATIQUES (exemples)
-- =====================================================

-- Lister les articles publies avec categorie et auteur
-- SELECT a.id, a.title, a.slug, c.name AS categorie, u.full_name AS auteur, a.published_at
-- FROM articles a
-- JOIN categories c ON c.id = a.category_id
-- JOIN users u ON u.id = a.author_id
-- WHERE a.status = 'published'
-- ORDER BY a.published_at DESC;

-- Lister les evenements chronologiques verifies
-- SELECT te.event_date, te.title, l.name AS lieu
-- FROM timeline_events te
-- LEFT JOIN locations l ON l.id = te.location_id
-- WHERE te.verification_status = 'verified'
-- ORDER BY te.event_date ASC;
