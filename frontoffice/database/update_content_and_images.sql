USE iran_info_site;

-- ----------------------------
-- Fix image paths for existing content
-- ----------------------------
UPDATE articles
SET cover_image_url = CASE slug
  WHEN 'chronologie-debut-guerre-iran-irak' THEN 'https://upload.wikimedia.org/wikipedia/commons/e/e0/Battle_of_Khorramshahr%2C_1980_by_Saeed_Sadeghi.jpg'
  WHEN 'analyse-impact-humain-guerre-iran-irak' THEN 'https://upload.wikimedia.org/wikipedia/commons/6/6d/Iran-Iraq_War%2C_color_photos_%E2%80%93_Defapress_%2824%29.jpg'
  WHEN 'diplomatie-regionale-discussions-recentes' THEN 'https://upload.wikimedia.org/wikipedia/commons/4/45/Meeting_of_the_Foreign_Ministers_of_Iran_and_Turkey_Avash_%28November_30%2C_2025%29_12.jpg'
  WHEN 'actualites-nouvelles-sanctions-energie-iran' THEN 'https://upload.wikimedia.org/wikipedia/commons/d/d9/Tankers_at_the_Iraqi_Al_Basra_Oil_Terminal_in_the_Northern_Arabian_Gulf.jpg'
  WHEN 'humanitaire-aide-medicale-zones-frontalieres' THEN 'https://upload.wikimedia.org/wikipedia/commons/f/f0/Desmond_Swayne_with_Syrian_refugees_at_Azraq_Refugee_Camp_Jordan.jpg'
  WHEN 'diplomatie-reprise-negociations-indirectes-oman' THEN 'https://upload.wikimedia.org/wikipedia/commons/a/a7/Munich_Security_Conference_2015_by_Olaf_Kosinsky-153.jpg'
  WHEN 'analyse-cybersecurite-infrastructures-critiques-iran' THEN 'https://upload.wikimedia.org/wikipedia/commons/7/72/Tehran_Night_Panorama.jpg'
  WHEN 'actualites-tensions-maritimes-golfe' THEN 'https://upload.wikimedia.org/wikipedia/commons/d/db/Flickr_-_Official_U.S._Navy_Imagery_-_U.S._Navy_ships_transit_the_Strait_of_Hormuz..jpg'
  ELSE cover_image_url
END
WHERE slug IN (
  'chronologie-debut-guerre-iran-irak',
  'analyse-impact-humain-guerre-iran-irak',
  'diplomatie-regionale-discussions-recentes',
  'actualites-nouvelles-sanctions-energie-iran',
  'humanitaire-aide-medicale-zones-frontalieres',
  'diplomatie-reprise-negociations-indirectes-oman',
  'analyse-cybersecurite-infrastructures-critiques-iran',
  'actualites-tensions-maritimes-golfe'
);

UPDATE media_assets ma
INNER JOIN articles a ON a.id = ma.article_id
SET ma.file_path = CASE a.slug
  WHEN 'chronologie-debut-guerre-iran-irak' THEN 'https://upload.wikimedia.org/wikipedia/commons/e/e0/Battle_of_Khorramshahr%2C_1980_by_Saeed_Sadeghi.jpg'
  WHEN 'analyse-impact-humain-guerre-iran-irak' THEN 'https://upload.wikimedia.org/wikipedia/commons/6/6d/Iran-Iraq_War%2C_color_photos_%E2%80%93_Defapress_%2824%29.jpg'
  WHEN 'diplomatie-regionale-discussions-recentes' THEN 'https://upload.wikimedia.org/wikipedia/commons/4/45/Meeting_of_the_Foreign_Ministers_of_Iran_and_Turkey_Avash_%28November_30%2C_2025%29_12.jpg'
  WHEN 'actualites-nouvelles-sanctions-energie-iran' THEN 'https://upload.wikimedia.org/wikipedia/commons/d/d9/Tankers_at_the_Iraqi_Al_Basra_Oil_Terminal_in_the_Northern_Arabian_Gulf.jpg'
  WHEN 'humanitaire-aide-medicale-zones-frontalieres' THEN 'https://upload.wikimedia.org/wikipedia/commons/f/f0/Desmond_Swayne_with_Syrian_refugees_at_Azraq_Refugee_Camp_Jordan.jpg'
  WHEN 'diplomatie-reprise-negociations-indirectes-oman' THEN 'https://upload.wikimedia.org/wikipedia/commons/a/a7/Munich_Security_Conference_2015_by_Olaf_Kosinsky-153.jpg'
  WHEN 'analyse-cybersecurite-infrastructures-critiques-iran' THEN 'https://upload.wikimedia.org/wikipedia/commons/7/72/Tehran_Night_Panorama.jpg'
  WHEN 'actualites-tensions-maritimes-golfe' THEN 'https://upload.wikimedia.org/wikipedia/commons/d/db/Flickr_-_Official_U.S._Navy_Imagery_-_U.S._Navy_ships_transit_the_Strait_of_Hormuz..jpg'
  ELSE ma.file_path
END,
ma.mime_type = 'image/jpeg'
WHERE a.slug IN (
  'chronologie-debut-guerre-iran-irak',
  'analyse-impact-humain-guerre-iran-irak',
  'diplomatie-regionale-discussions-recentes',
  'actualites-nouvelles-sanctions-energie-iran',
  'humanitaire-aide-medicale-zones-frontalieres',
  'diplomatie-reprise-negociations-indirectes-oman',
  'analyse-cybersecurite-infrastructures-critiques-iran',
  'actualites-tensions-maritimes-golfe'
);

UPDATE seo_pages
SET og_image_url = 'https://upload.wikimedia.org/wikipedia/commons/8/8c/2004-11-22_Tehran-Nord.jpg'
WHERE page_type = 'home' AND page_ref_id IS NULL;

UPDATE seo_pages s
INNER JOIN articles a ON s.page_type = 'article' AND s.page_ref_id = a.id
SET s.og_image_url = CASE a.slug
    WHEN 'chronologie-debut-guerre-iran-irak' THEN 'https://upload.wikimedia.org/wikipedia/commons/e/e0/Battle_of_Khorramshahr%2C_1980_by_Saeed_Sadeghi.jpg'
    WHEN 'analyse-impact-humain-guerre-iran-irak' THEN 'https://upload.wikimedia.org/wikipedia/commons/6/6d/Iran-Iraq_War%2C_color_photos_%E2%80%93_Defapress_%2824%29.jpg'
    WHEN 'diplomatie-regionale-discussions-recentes' THEN 'https://upload.wikimedia.org/wikipedia/commons/4/45/Meeting_of_the_Foreign_Ministers_of_Iran_and_Turkey_Avash_%28November_30%2C_2025%29_12.jpg'
  WHEN 'actualites-nouvelles-sanctions-energie-iran' THEN 'https://upload.wikimedia.org/wikipedia/commons/d/d9/Tankers_at_the_Iraqi_Al_Basra_Oil_Terminal_in_the_Northern_Arabian_Gulf.jpg'
  WHEN 'humanitaire-aide-medicale-zones-frontalieres' THEN 'https://upload.wikimedia.org/wikipedia/commons/f/f0/Desmond_Swayne_with_Syrian_refugees_at_Azraq_Refugee_Camp_Jordan.jpg'
  WHEN 'diplomatie-reprise-negociations-indirectes-oman' THEN 'https://upload.wikimedia.org/wikipedia/commons/a/a7/Munich_Security_Conference_2015_by_Olaf_Kosinsky-153.jpg'
  WHEN 'analyse-cybersecurite-infrastructures-critiques-iran' THEN 'https://upload.wikimedia.org/wikipedia/commons/7/72/Tehran_Night_Panorama.jpg'
  WHEN 'actualites-tensions-maritimes-golfe' THEN 'https://upload.wikimedia.org/wikipedia/commons/d/db/Flickr_-_Official_U.S._Navy_Imagery_-_U.S._Navy_ships_transit_the_Strait_of_Hormuz..jpg'
    ELSE s.og_image_url
END
WHERE a.slug IN (
    'chronologie-debut-guerre-iran-irak',
    'analyse-impact-humain-guerre-iran-irak',
  'diplomatie-regionale-discussions-recentes',
  'actualites-nouvelles-sanctions-energie-iran',
  'humanitaire-aide-medicale-zones-frontalieres',
  'diplomatie-reprise-negociations-indirectes-oman',
  'analyse-cybersecurite-infrastructures-critiques-iran',
  'actualites-tensions-maritimes-golfe'
);

-- ----------------------------
-- Additional published articles
-- ----------------------------
INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
    (SELECT id FROM conflicts WHERE slug = 'tensions-regionales-iran' LIMIT 1),
    (SELECT id FROM categories WHERE slug = 'actualites' LIMIT 1),
    (SELECT id FROM users WHERE email = 'admin@iran-info.local' LIMIT 1),
    'Actualites: nouvelles sanctions sur le secteur de l energie',
    'actualites-nouvelles-sanctions-energie-iran',
    'Synthese des annonces de sanctions et des effets attendus sur l energie.',
    '<h1>Nouvelles sanctions energetiques</h1><h2>Contexte international</h2><p>De nouvelles mesures ciblent des chaines logistiques et financieres.</p><h2>Effets possibles</h2><p>Les flux commerciaux regionaux pourraient etre ralentis a court terme.</p>',
    'https://upload.wikimedia.org/wikipedia/commons/d/d9/Tankers_at_the_Iraqi_Al_Basra_Oil_Terminal_in_the_Northern_Arabian_Gulf.jpg',
    'Illustration des flux energetiques sous sanctions',
    'published',
    '2026-03-12 08:30:00'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM articles WHERE slug = 'actualites-nouvelles-sanctions-energie-iran'
);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
    (SELECT id FROM conflicts WHERE slug = 'tensions-regionales-iran' LIMIT 1),
    (SELECT id FROM categories WHERE slug = 'humanitaire' LIMIT 1),
    (SELECT id FROM users WHERE email = 'redaction@iran-info.local' LIMIT 1),
    'Humanitaire: aide medicale dans les zones frontalieres',
    'humanitaire-aide-medicale-zones-frontalieres',
    'Point de situation sur les corridors d aide et les besoins medicaux.',
    '<h1>Aide medicale</h1><h2>Besoins prioritaires</h2><p>Les structures de soins locales signalent un manque de materiel.</p><h2>Coordination</h2><p>Des organisations internationales renforcent la logistique humanitaire.</p>',
    'https://upload.wikimedia.org/wikipedia/commons/f/f0/Desmond_Swayne_with_Syrian_refugees_at_Azraq_Refugee_Camp_Jordan.jpg',
    'Equipe medicale et corridor humanitaire stylise',
    'published',
    '2026-03-14 11:15:00'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM articles WHERE slug = 'humanitaire-aide-medicale-zones-frontalieres'
);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
    (SELECT id FROM conflicts WHERE slug = 'tensions-regionales-iran' LIMIT 1),
    (SELECT id FROM categories WHERE slug = 'diplomatie' LIMIT 1),
    (SELECT id FROM users WHERE email = 'admin@iran-info.local' LIMIT 1),
    'Diplomatie: reprise des negociations indirectes a Oman',
    'diplomatie-reprise-negociations-indirectes-oman',
    'Les delegations relancent un cycle de discussions techniques.',
    '<h1>Reprise des negociations</h1><h2>Format des discussions</h2><p>Les echanges se concentrent sur des points techniques de securite.</p><h2>Calendrier</h2><p>Un nouveau round est annonce pour les prochaines semaines.</p>',
    'https://upload.wikimedia.org/wikipedia/commons/a/a7/Munich_Security_Conference_2015_by_Olaf_Kosinsky-153.jpg',
    'Table ronde diplomatique en format indirect',
    'published',
    '2026-03-16 16:45:00'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM articles WHERE slug = 'diplomatie-reprise-negociations-indirectes-oman'
);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
    (SELECT id FROM conflicts WHERE slug = 'tensions-regionales-iran' LIMIT 1),
    (SELECT id FROM categories WHERE slug = 'analyses' LIMIT 1),
    (SELECT id FROM users WHERE email = 'redaction@iran-info.local' LIMIT 1),
    'Analyse: cybersecurite et infrastructures critiques',
    'analyse-cybersecurite-infrastructures-critiques-iran',
    'Analyse des risques cyber sur les infrastructures civiles et energetiques.',
    '<h1>Cybersecurite et infrastructures</h1><h2>Menaces observees</h2><p>Les attaques ciblent principalement les services essentiels.</p><h2>Reponses possibles</h2><p>Le renforcement des systemes de detection reste prioritaire.</p>',
    'https://upload.wikimedia.org/wikipedia/commons/7/72/Tehran_Night_Panorama.jpg',
    'Schema de cybersecurite applique aux infrastructures critiques',
    'published',
    '2026-03-18 09:20:00'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM articles WHERE slug = 'analyse-cybersecurite-infrastructures-critiques-iran'
);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
    (SELECT id FROM conflicts WHERE slug = 'tensions-regionales-iran' LIMIT 1),
    (SELECT id FROM categories WHERE slug = 'actualites' LIMIT 1),
    (SELECT id FROM users WHERE email = 'admin@iran-info.local' LIMIT 1),
    'Actualites: tensions maritimes dans le golfe',
    'actualites-tensions-maritimes-golfe',
    'Resume des incidents maritimes recents et des reactions diplomatiques.',
    '<h1>Tensions maritimes</h1><h2>Incidents recents</h2><p>Plusieurs alertes ont ete signalees dans des couloirs strategiques.</p><h2>Reactions</h2><p>Les acteurs regionaux appellent a une desescalade rapide.</p>',
    'https://upload.wikimedia.org/wikipedia/commons/d/db/Flickr_-_Official_U.S._Navy_Imagery_-_U.S._Navy_ships_transit_the_Strait_of_Hormuz..jpg',
    'Navigation commerciale dans une zone de tension',
    'published',
    '2026-03-20 07:50:00'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM articles WHERE slug = 'actualites-tensions-maritimes-golfe'
);

-- ----------------------------
-- Links: tags, sources, actors
-- ----------------------------
INSERT INTO article_tags (article_id, tag_id)
SELECT a.id, t.id
FROM articles a
INNER JOIN tags t ON t.slug = 'sanctions'
WHERE a.slug = 'actualites-nouvelles-sanctions-energie-iran'
  AND NOT EXISTS (
      SELECT 1 FROM article_tags x WHERE x.article_id = a.id AND x.tag_id = t.id
  );

INSERT INTO article_tags (article_id, tag_id)
SELECT a.id, t.id
FROM articles a
INNER JOIN tags t ON t.slug = 'energie'
WHERE a.slug = 'actualites-nouvelles-sanctions-energie-iran'
  AND NOT EXISTS (
      SELECT 1 FROM article_tags x WHERE x.article_id = a.id AND x.tag_id = t.id
  );

INSERT INTO article_tags (article_id, tag_id)
SELECT a.id, t.id
FROM articles a
INNER JOIN tags t ON t.slug = 'cesser-le-feu'
WHERE a.slug = 'humanitaire-aide-medicale-zones-frontalieres'
  AND NOT EXISTS (
      SELECT 1 FROM article_tags x WHERE x.article_id = a.id AND x.tag_id = t.id
  );

INSERT INTO article_tags (article_id, tag_id)
SELECT a.id, t.id
FROM articles a
INNER JOIN tags t ON t.slug = 'onu'
WHERE a.slug = 'humanitaire-aide-medicale-zones-frontalieres'
  AND NOT EXISTS (
      SELECT 1 FROM article_tags x WHERE x.article_id = a.id AND x.tag_id = t.id
  );

INSERT INTO article_tags (article_id, tag_id)
SELECT a.id, t.id
FROM articles a
INNER JOIN tags t ON t.slug = 'negociations'
WHERE a.slug = 'diplomatie-reprise-negociations-indirectes-oman'
  AND NOT EXISTS (
      SELECT 1 FROM article_tags x WHERE x.article_id = a.id AND x.tag_id = t.id
  );

INSERT INTO article_tags (article_id, tag_id)
SELECT a.id, t.id
FROM articles a
INNER JOIN tags t ON t.slug = 'nucleaire'
WHERE a.slug = 'diplomatie-reprise-negociations-indirectes-oman'
  AND NOT EXISTS (
      SELECT 1 FROM article_tags x WHERE x.article_id = a.id AND x.tag_id = t.id
  );

INSERT INTO article_tags (article_id, tag_id)
SELECT a.id, t.id
FROM articles a
INNER JOIN tags t ON t.slug = 'energie'
WHERE a.slug = 'analyse-cybersecurite-infrastructures-critiques-iran'
  AND NOT EXISTS (
      SELECT 1 FROM article_tags x WHERE x.article_id = a.id AND x.tag_id = t.id
  );

INSERT INTO article_tags (article_id, tag_id)
SELECT a.id, t.id
FROM articles a
INNER JOIN tags t ON t.slug = 'nucleaire'
WHERE a.slug = 'analyse-cybersecurite-infrastructures-critiques-iran'
  AND NOT EXISTS (
      SELECT 1 FROM article_tags x WHERE x.article_id = a.id AND x.tag_id = t.id
  );

INSERT INTO article_tags (article_id, tag_id)
SELECT a.id, t.id
FROM articles a
INNER JOIN tags t ON t.slug = 'sanctions'
WHERE a.slug = 'actualites-tensions-maritimes-golfe'
  AND NOT EXISTS (
      SELECT 1 FROM article_tags x WHERE x.article_id = a.id AND x.tag_id = t.id
  );

INSERT INTO article_tags (article_id, tag_id)
SELECT a.id, t.id
FROM articles a
INNER JOIN tags t ON t.slug = 'negociations'
WHERE a.slug = 'actualites-tensions-maritimes-golfe'
  AND NOT EXISTS (
      SELECT 1 FROM article_tags x WHERE x.article_id = a.id AND x.tag_id = t.id
  );

INSERT INTO article_sources (article_id, source_id, note)
SELECT a.id, s.id, 'Contexte macroeconomique'
FROM articles a
INNER JOIN sources s ON s.publisher = 'Banque mondiale'
WHERE a.slug = 'actualites-nouvelles-sanctions-energie-iran'
  AND NOT EXISTS (
      SELECT 1 FROM article_sources x WHERE x.article_id = a.id AND x.source_id = s.id
  );

INSERT INTO article_sources (article_id, source_id, note)
SELECT a.id, s.id, 'Reaction diplomatique initiale'
FROM articles a
INNER JOIN sources s ON s.publisher = 'Ministere des Affaires etrangeres'
WHERE a.slug = 'actualites-nouvelles-sanctions-energie-iran'
  AND NOT EXISTS (
      SELECT 1 FROM article_sources x WHERE x.article_id = a.id AND x.source_id = s.id
  );

INSERT INTO article_sources (article_id, source_id, note)
SELECT a.id, s.id, 'Coordination humanitaire'
FROM articles a
INNER JOIN sources s ON s.publisher = 'Ministere des Affaires etrangeres'
WHERE a.slug = 'humanitaire-aide-medicale-zones-frontalieres'
  AND NOT EXISTS (
      SELECT 1 FROM article_sources x WHERE x.article_id = a.id AND x.source_id = s.id
  );

INSERT INTO article_sources (article_id, source_id, note)
SELECT a.id, s.id, 'Canaux de dialogue'
FROM articles a
INNER JOIN sources s ON s.publisher = 'Ministere des Affaires etrangeres'
WHERE a.slug = 'diplomatie-reprise-negociations-indirectes-oman'
  AND NOT EXISTS (
      SELECT 1 FROM article_sources x WHERE x.article_id = a.id AND x.source_id = s.id
  );

INSERT INTO article_sources (article_id, source_id, note)
SELECT a.id, s.id, 'Base analytique'
FROM articles a
INNER JOIN sources s ON s.publisher = 'Britannica'
WHERE a.slug = 'analyse-cybersecurite-infrastructures-critiques-iran'
  AND NOT EXISTS (
      SELECT 1 FROM article_sources x WHERE x.article_id = a.id AND x.source_id = s.id
  );

INSERT INTO article_sources (article_id, source_id, note)
SELECT a.id, s.id, 'Contexte geoeconomique'
FROM articles a
INNER JOIN sources s ON s.publisher = 'Banque mondiale'
WHERE a.slug = 'actualites-tensions-maritimes-golfe'
  AND NOT EXISTS (
      SELECT 1 FROM article_sources x WHERE x.article_id = a.id AND x.source_id = s.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'iran'
WHERE a.slug = 'actualites-nouvelles-sanctions-energie-iran'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'aiea'
WHERE a.slug = 'actualites-nouvelles-sanctions-energie-iran'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'iran'
WHERE a.slug = 'humanitaire-aide-medicale-zones-frontalieres'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'nations-unies'
WHERE a.slug = 'humanitaire-aide-medicale-zones-frontalieres'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'iran'
WHERE a.slug = 'diplomatie-reprise-negociations-indirectes-oman'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'nations-unies'
WHERE a.slug = 'diplomatie-reprise-negociations-indirectes-oman'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'aiea'
WHERE a.slug = 'diplomatie-reprise-negociations-indirectes-oman'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'iran'
WHERE a.slug = 'analyse-cybersecurite-infrastructures-critiques-iran'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'aiea'
WHERE a.slug = 'analyse-cybersecurite-infrastructures-critiques-iran'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'iran'
WHERE a.slug = 'actualites-tensions-maritimes-golfe'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'irak'
WHERE a.slug = 'actualites-tensions-maritimes-golfe'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

INSERT INTO article_actors (article_id, actor_id)
SELECT a.id, ac.id
FROM articles a
INNER JOIN actors ac ON ac.slug = 'nations-unies'
WHERE a.slug = 'actualites-tensions-maritimes-golfe'
  AND NOT EXISTS (
      SELECT 1 FROM article_actors x WHERE x.article_id = a.id AND x.actor_id = ac.id
  );

-- ----------------------------
-- Media assets for added content
-- ----------------------------
INSERT INTO media_assets (article_id, file_path, title, alt_text, caption, credit, mime_type, width, height)
SELECT a.id, 'https://upload.wikimedia.org/wikipedia/commons/d/d9/Tankers_at_the_Iraqi_Al_Basra_Oil_Terminal_in_the_Northern_Arabian_Gulf.jpg', 'Sanctions energie', 'Cartographie stylisee des flux energetiques', 'Synthese visuelle des pressions economiques', 'Iran Info', 'image/jpeg', 1600, 900
FROM articles a
WHERE a.slug = 'actualites-nouvelles-sanctions-energie-iran'
  AND NOT EXISTS (
      SELECT 1 FROM media_assets m WHERE m.article_id = a.id AND m.file_path = 'https://upload.wikimedia.org/wikipedia/commons/d/d9/Tankers_at_the_Iraqi_Al_Basra_Oil_Terminal_in_the_Northern_Arabian_Gulf.jpg'
  );

INSERT INTO media_assets (article_id, file_path, title, alt_text, caption, credit, mime_type, width, height)
SELECT a.id, 'https://upload.wikimedia.org/wikipedia/commons/f/f0/Desmond_Swayne_with_Syrian_refugees_at_Azraq_Refugee_Camp_Jordan.jpg', 'Aide humanitaire', 'Corridor humanitaire et equipements medicaux', 'Logistique humanitaire dans les zones sensibles', 'Iran Info', 'image/jpeg', 1600, 900
FROM articles a
WHERE a.slug = 'humanitaire-aide-medicale-zones-frontalieres'
  AND NOT EXISTS (
      SELECT 1 FROM media_assets m WHERE m.article_id = a.id AND m.file_path = 'https://upload.wikimedia.org/wikipedia/commons/f/f0/Desmond_Swayne_with_Syrian_refugees_at_Azraq_Refugee_Camp_Jordan.jpg'
  );

INSERT INTO media_assets (article_id, file_path, title, alt_text, caption, credit, mime_type, width, height)
SELECT a.id, 'https://upload.wikimedia.org/wikipedia/commons/a/a7/Munich_Security_Conference_2015_by_Olaf_Kosinsky-153.jpg', 'Negociations Oman', 'Table de discussion diplomatique', 'Relance des discussions indirectes', 'Iran Info', 'image/jpeg', 1600, 900
FROM articles a
WHERE a.slug = 'diplomatie-reprise-negociations-indirectes-oman'
  AND NOT EXISTS (
      SELECT 1 FROM media_assets m WHERE m.article_id = a.id AND m.file_path = 'https://upload.wikimedia.org/wikipedia/commons/a/a7/Munich_Security_Conference_2015_by_Olaf_Kosinsky-153.jpg'
  );

INSERT INTO media_assets (article_id, file_path, title, alt_text, caption, credit, mime_type, width, height)
SELECT a.id, 'https://upload.wikimedia.org/wikipedia/commons/7/72/Tehran_Night_Panorama.jpg', 'Cyber securite', 'Reseau numerique et infrastructures critiques', 'Risques cyber sur les services essentiels', 'Iran Info', 'image/jpeg', 1600, 900
FROM articles a
WHERE a.slug = 'analyse-cybersecurite-infrastructures-critiques-iran'
  AND NOT EXISTS (
      SELECT 1 FROM media_assets m WHERE m.article_id = a.id AND m.file_path = 'https://upload.wikimedia.org/wikipedia/commons/7/72/Tehran_Night_Panorama.jpg'
  );

INSERT INTO media_assets (article_id, file_path, title, alt_text, caption, credit, mime_type, width, height)
SELECT a.id, 'https://upload.wikimedia.org/wikipedia/commons/d/db/Flickr_-_Official_U.S._Navy_Imagery_-_U.S._Navy_ships_transit_the_Strait_of_Hormuz..jpg', 'Tensions maritimes', 'Navigation dans un couloir strategique', 'Suivi des incidents maritimes regionaux', 'Iran Info', 'image/jpeg', 1600, 900
FROM articles a
WHERE a.slug = 'actualites-tensions-maritimes-golfe'
  AND NOT EXISTS (
      SELECT 1 FROM media_assets m WHERE m.article_id = a.id AND m.file_path = 'https://upload.wikimedia.org/wikipedia/commons/d/db/Flickr_-_Official_U.S._Navy_Imagery_-_U.S._Navy_ships_transit_the_Strait_of_Hormuz..jpg'
  );

DELETE m1
FROM media_assets m1
INNER JOIN media_assets m2
    ON m1.article_id = m2.article_id
   AND m1.file_path = m2.file_path
   AND m1.id > m2.id;

-- ----------------------------
-- SEO entries for added articles
-- ----------------------------
INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
    'article', a.id,
    'Sanctions sur l energie: impacts regionaux',
    'Lecture des nouvelles sanctions energetiques et de leurs effets potentiels.',
    '/article/actualites-nouvelles-sanctions-energie-iran',
    'Sanctions et energie',
    'Impacts attendus sur les flux regionaux.',
    'https://upload.wikimedia.org/wikipedia/commons/d/d9/Tankers_at_the_Iraqi_Al_Basra_Oil_Terminal_in_the_Northern_Arabian_Gulf.jpg',
    1,
    1
FROM articles a
WHERE a.slug = 'actualites-nouvelles-sanctions-energie-iran'
  AND NOT EXISTS (
      SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
    'article', a.id,
    'Aide medicale: situation dans les zones frontalieres',
    'Point d etape sur les besoins humanitaires et la coordination medicale.',
    '/article/humanitaire-aide-medicale-zones-frontalieres',
    'Aide humanitaire frontaliere',
    'Etat des besoins medicaux prioritaires.',
    'https://upload.wikimedia.org/wikipedia/commons/f/f0/Desmond_Swayne_with_Syrian_refugees_at_Azraq_Refugee_Camp_Jordan.jpg',
    1,
    1
FROM articles a
WHERE a.slug = 'humanitaire-aide-medicale-zones-frontalieres'
  AND NOT EXISTS (
      SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
    'article', a.id,
    'Negociations indirectes: nouveau cycle a Oman',
    'Suivi du calendrier diplomatique et des points en discussion.',
    '/article/diplomatie-reprise-negociations-indirectes-oman',
    'Negociations a Oman',
    'Reprise du dialogue indirect sur les dossiers sensibles.',
    'https://upload.wikimedia.org/wikipedia/commons/a/a7/Munich_Security_Conference_2015_by_Olaf_Kosinsky-153.jpg',
    1,
    1
FROM articles a
WHERE a.slug = 'diplomatie-reprise-negociations-indirectes-oman'
  AND NOT EXISTS (
      SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
    'article', a.id,
    'Cybersecurite et infrastructures critiques: analyse',
    'Evaluation des menaces cyber sur les services essentiels.',
    '/article/analyse-cybersecurite-infrastructures-critiques-iran',
    'Cybersecurite infrastructures',
    'Analyse des risques numeriques et mesures prioritaires.',
    'https://upload.wikimedia.org/wikipedia/commons/7/72/Tehran_Night_Panorama.jpg',
    1,
    1
FROM articles a
WHERE a.slug = 'analyse-cybersecurite-infrastructures-critiques-iran'
  AND NOT EXISTS (
      SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
    'article', a.id,
    'Tensions maritimes dans le golfe: derniers incidents',
    'Resume des incidents recents et des reactions diplomatiques.',
    '/article/actualites-tensions-maritimes-golfe',
    'Tensions maritimes',
    'Suivi des incidents dans les couloirs strategiques.',
    'https://upload.wikimedia.org/wikipedia/commons/d/db/Flickr_-_Official_U.S._Navy_Imagery_-_U.S._Navy_ships_transit_the_Strait_of_Hormuz..jpg',
    1,
    1
FROM articles a
WHERE a.slug = 'actualites-tensions-maritimes-golfe'
  AND NOT EXISTS (
      SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

-- ----------------------------
-- More Iran-Iraq war articles
-- ----------------------------
INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
  (SELECT id FROM conflicts WHERE slug = 'guerre-iran-irak' LIMIT 1),
  (SELECT id FROM categories WHERE slug = 'actualites' LIMIT 1),
  (SELECT id FROM users WHERE email = 'admin@iran-info.local' LIMIT 1),
  'Actualites: evolution du front sud au printemps 1982',
  'actualites-evolution-front-sud-printemps-1982',
  'Point de situation sur les mouvements du front sud et les objectifs tactiques des deux camps.',
  '<h1>Front sud au printemps 1982</h1><h2>Situation generale</h2><p>Les operations se concentrent autour des axes routiers et des zones fluviales strategiques.</p><h3>Objectifs tactiques</h3><p>Les deux armees cherchent a stabiliser les positions defensives et a limiter les pertes.</p><h4>Effets immediats</h4><p>Les lignes de ravitaillement deviennent un facteur central dans la capacite de projection.</p>',
  '/package/assets/images/real/chronologie-khorramshahr.jpg',
  'Soldats sur une position defensive pendant la guerre Iran-Irak',
  'published',
  '2026-03-21 09:00:00'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE slug = 'actualites-evolution-front-sud-printemps-1982'
);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
  (SELECT id FROM conflicts WHERE slug = 'guerre-iran-irak' LIMIT 1),
  (SELECT id FROM categories WHERE slug = 'chronologie' LIMIT 1),
  (SELECT id FROM users WHERE email = 'redaction@iran-info.local' LIMIT 1),
  'Chronologie: offensive d Al Faw en 1986',
  'chronologie-offensive-al-faw-1986',
  'Repere chronologique des phases de l offensive d Al Faw et de ses consequences operationnelles.',
  '<h1>Offensive d Al Faw</h1><h2>Phase de preparation</h2><p>Les preparatifs logistiques precedents expliquent le rythme rapide de l offensive initiale.</p><h3>Prise de positions</h3><p>Le controle des zones humides modifie les conditions tactiques des semaines suivantes.</p><h4>Reactions regionales</h4><p>Les capitales voisines suivent de pres les risques d extension du conflit.</p>',
  '/package/assets/images/real/chronologie-khorramshahr.jpg',
  'Carte operationnelle et troupes sur le theatre Al Faw',
  'published',
  '2026-03-23 10:15:00'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE slug = 'chronologie-offensive-al-faw-1986'
);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
  (SELECT id FROM conflicts WHERE slug = 'guerre-iran-irak' LIMIT 1),
  (SELECT id FROM categories WHERE slug = 'analyses' LIMIT 1),
  (SELECT id FROM users WHERE email = 'admin@iran-info.local' LIMIT 1),
  'Analyse: economie de guerre entre 1984 et 1988',
  'analyse-economie-guerre-1984-1988',
  'Analyse des couts logistiques, industriels et humains dans la phase longue du conflit.',
  '<h1>Economie de guerre 1984-1988</h1><h2>Pression budgetaire</h2><p>Les depenses militaires perturbent les investissements civils et la capacite de reconstruction.</p><h3>Chaine logistique</h3><p>Le transport, le carburant et les importations critiques deviennent des points de tension.</p><h4>Impact social</h4><p>Les menages supportent une inflation forte et une baisse durable du revenu reel.</p>',
  '/package/assets/images/real/impact-humain-defapress.jpg',
  'Population civile et contraintes economiques en periode de guerre',
  'published',
  '2026-03-24 14:00:00'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE slug = 'analyse-economie-guerre-1984-1988'
);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
  (SELECT id FROM conflicts WHERE slug = 'guerre-iran-irak' LIMIT 1),
  (SELECT id FROM categories WHERE slug = 'diplomatie' LIMIT 1),
  (SELECT id FROM users WHERE email = 'redaction@iran-info.local' LIMIT 1),
  'Diplomatie: application de la resolution 598',
  'diplomatie-application-resolution-598',
  'Lecture des etapes politiques et des garanties necessaires pour appliquer le cessez le feu.',
  '<h1>Resolution 598</h1><h2>Cadre diplomatique</h2><p>La resolution sert de base commune pour la desescalade et les mecanismes de verification.</p><h3>Points de blocage</h3><p>Le calendrier, les observateurs et les garanties mutuelles restent des sujets sensibles.</p><h4>Resultat diplomatique</h4><p>Le cadre onusien facilite la transition vers une reduction progressive des hostilites.</p>',
  '/package/assets/images/real/diplomatie-iran-turquie.jpg',
  'Responsable diplomatique iranien lors dune declaration officielle',
  'published',
  '2026-03-25 08:45:00'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE slug = 'diplomatie-application-resolution-598'
);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
  (SELECT id FROM conflicts WHERE slug = 'guerre-iran-irak' LIMIT 1),
  (SELECT id FROM categories WHERE slug = 'humanitaire' LIMIT 1),
  (SELECT id FROM users WHERE email = 'admin@iran-info.local' LIMIT 1),
  'Humanitaire: mines et rehabilitation des zones frontalieres',
  'humanitaire-mines-rehabilitation-zones-frontieres',
  'Etat des besoins de deminage, de soins et de reconstruction dans les localites frontalieres.',
  '<h1>Mines et rehabilitation</h1><h2>Risque pour les civils</h2><p>Les mines retardent le retour des familles et augmentent les blessures de longue duree.</p><h3>Priorites medicales</h3><p>Le traitement des traumatismes et la readaptation fonctionnelle sont des enjeux majeurs.</p><h4>Reconstruction locale</h4><p>Routes, eau et reseaux de base doivent etre retablis pour relancer l activite.</p>',
  '/package/assets/images/real/impact-humain-defapress.jpg',
  'Civils touches par les consequences humanitaires du conflit',
  'published',
  '2026-03-26 11:20:00'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE slug = 'humanitaire-mines-rehabilitation-zones-frontieres'
);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
  (SELECT id FROM conflicts WHERE slug = 'guerre-iran-irak' LIMIT 1),
  (SELECT id FROM categories WHERE slug = 'actualites' LIMIT 1),
  (SELECT id FROM users WHERE email = 'redaction@iran-info.local' LIMIT 1),
  'Actualites: suivi des echanges de prisonniers apres conflit',
  'actualites-suivi-echanges-prisonniers-apres-conflit',
  'Retour sur les mecanismes d echange de prisonniers et les verifications internationales.',
  '<h1>Echanges de prisonniers</h1><h2>Mecanismes d echange</h2><p>Les accords techniques organisent les listes, les points de passage et les calendriers.</p><h3>Verification internationale</h3><p>Des observateurs documentent chaque phase pour limiter les litiges.</p><h4>Impact humain direct</h4><p>Les reunifications familiales constituent une etape cle de l apres conflit.</p>',
  '/package/assets/images/real/impact-humain-defapress.jpg',
  'Groupe de combattants transportes durant la guerre Iran-Irak',
  'published',
  '2026-03-27 09:10:00'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE slug = 'actualites-suivi-echanges-prisonniers-apres-conflit'
);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
  (SELECT id FROM conflicts WHERE slug = 'guerre-iran-irak' LIMIT 1),
  (SELECT id FROM categories WHERE slug = 'analyses' LIMIT 1),
  (SELECT id FROM users WHERE email = 'admin@iran-info.local' LIMIT 1),
  'Analyse: lecons strategiques du conflit Iran-Irak',
  'analyse-lecons-strategiques-conflit-iran-irak',
  'Analyse des enseignements operationnels, politiques et doctrinaux pour la securite regionale.',
  '<h1>Lecons strategiques</h1><h2>Niveau operatif</h2><h3>Gestion des lignes</h3><h4>Profondeur defensive</h4><h5>Coordination interarmes</h5><h6>Verification de terrain</h6><p>La synchronisation des unites et des moyens logistiques apparait comme un facteur determinant.</p><h2>Niveau politique</h2><p>Le couplage entre decision militaire et calendrier diplomatique influe sur la duree du conflit.</p>',
  '/package/assets/images/real/chronologie-khorramshahr.jpg',
  'Soldats et fortifications illustrant les enseignements tactiques',
  'published',
  '2026-03-28 15:35:00'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE slug = 'analyse-lecons-strategiques-conflit-iran-irak'
);

INSERT INTO articles (conflict_id, category_id, author_id, title, slug, excerpt, content, cover_image_url, cover_image_alt, status, published_at)
SELECT
  (SELECT id FROM conflicts WHERE slug = 'guerre-iran-irak' LIMIT 1),
  (SELECT id FROM categories WHERE slug = 'chronologie' LIMIT 1),
  (SELECT id FROM users WHERE email = 'redaction@iran-info.local' LIMIT 1),
  'Chronologie: phase finale et cessez le feu de 1988',
  'chronologie-phase-finale-cessez-le-feu-1988',
  'Synthese datee des derniers mois du conflit jusqu a l entree en vigueur du cessez le feu.',
  '<h1>Phase finale de 1988</h1><h2>Escalade finale</h2><p>Les derniers mois combinent pression militaire, fatigue des forces et mediation accrue.</p><h3>Decision politique</h3><p>La sequence diplomatique accelere autour du cessez le feu sous cadre onusien.</p><h4>Apres le cessez le feu</h4><p>La stabilisation du front ouvre une longue phase de normalisation progressive.</p>',
  '/package/assets/images/real/chronologie-khorramshahr.jpg',
  'Tranche chronologique des derniers mois de la guerre Iran-Irak',
  'published',
  '2026-03-29 18:10:00'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE slug = 'chronologie-phase-finale-cessez-le-feu-1988'
);

-- ----------------------------
-- More timeline events
-- ----------------------------
INSERT INTO timeline_events (conflict_id, location_id, title, slug, event_date, summary, details, verification_status, primary_source_id)
SELECT
  (SELECT id FROM conflicts WHERE slug = 'guerre-iran-irak' LIMIT 1),
  (SELECT id FROM locations WHERE name = 'Frontiere Iran-Irak' LIMIT 1),
  'Intensification de la guerre des villes',
  'intensification-guerre-villes-1987',
  '1987-03-15',
  'Acceleration des frappes sur des zones urbaines et infrastructures civiles.',
  'Les archives convergent sur une periode de forte pression psychologique et politique.',
  'verified',
  (SELECT id FROM sources WHERE publisher = 'Britannica' LIMIT 1)
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM timeline_events WHERE slug = 'intensification-guerre-villes-1987'
);

INSERT INTO timeline_events (conflict_id, location_id, title, slug, event_date, summary, details, verification_status, primary_source_id)
SELECT
  (SELECT id FROM conflicts WHERE slug = 'guerre-iran-irak' LIMIT 1),
  (SELECT id FROM locations WHERE name = 'Frontiere Iran-Irak' LIMIT 1),
  'Offensive d Al Faw et reconfiguration du front',
  'offensive-al-faw-reconfiguration-front-1986',
  '1986-02-09',
  'Reconfiguration tactique majeure autour de la peninsule d Al Faw.',
  'Les changements de positions influencent durablement la phase suivante du conflit.',
  'verified',
  (SELECT id FROM sources WHERE publisher = 'Britannica' LIMIT 1)
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM timeline_events WHERE slug = 'offensive-al-faw-reconfiguration-front-1986'
);

INSERT INTO timeline_events (conflict_id, location_id, title, slug, event_date, summary, details, verification_status, primary_source_id)
SELECT
  (SELECT id FROM conflicts WHERE slug = 'guerre-iran-irak' LIMIT 1),
  (SELECT id FROM locations WHERE name = 'Bagdad' LIMIT 1),
  'Mise en oeuvre des echanges de prisonniers',
  'mise-en-oeuvre-echanges-prisonniers-1990',
  '1990-08-17',
  'Phase de normalisation avec echanges progressifs de prisonniers.',
  'La sequence confirme le passage d une logique militaire a une logique de reglement humain.',
  'verified',
  (SELECT id FROM sources WHERE publisher = 'Nations Unies' LIMIT 1)
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM timeline_events WHERE slug = 'mise-en-oeuvre-echanges-prisonniers-1990'
);

-- ----------------------------
-- SEO entries for newly added articles
-- ----------------------------
INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
  'article', a.id,
  'Front sud 1982: evolution tactique du conflit',
  'Point de situation sur les mouvements du front sud pendant la guerre Iran-Irak.',
  '/article/actualites-evolution-front-sud-printemps-1982',
  'Front sud 1982',
  'Lecture tactique des operations du front sud.',
  '/package/assets/images/real/chronologie-khorramshahr.jpg',
  1,
  1
FROM articles a
WHERE a.slug = 'actualites-evolution-front-sud-printemps-1982'
  AND NOT EXISTS (
    SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
  'article', a.id,
  'Offensive d Al Faw 1986: chronologie complete',
  'Repere chronologique des phases de l offensive d Al Faw en 1986.',
  '/article/chronologie-offensive-al-faw-1986',
  'Chronologie Al Faw',
  'Etapes cle de l offensive et effets sur le front.',
  '/package/assets/images/real/chronologie-khorramshahr.jpg',
  1,
  1
FROM articles a
WHERE a.slug = 'chronologie-offensive-al-faw-1986'
  AND NOT EXISTS (
    SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
  'article', a.id,
  'Economie de guerre 1984-1988: analyse',
  'Analyse des couts logistiques, industriels et sociaux de la guerre Iran-Irak.',
  '/article/analyse-economie-guerre-1984-1988',
  'Economie de guerre',
  'Impacts economiques et sociaux de la phase longue du conflit.',
  '/package/assets/images/real/impact-humain-defapress.jpg',
  1,
  1
FROM articles a
WHERE a.slug = 'analyse-economie-guerre-1984-1988'
  AND NOT EXISTS (
    SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
  'article', a.id,
  'Resolution 598: mise en application diplomatique',
  'Analyse des conditions diplomatiques de mise en oeuvre du cessez le feu.',
  '/article/diplomatie-application-resolution-598',
  'Resolution 598',
  'Etapes diplomatiques de la desescalade finale.',
  '/package/assets/images/real/diplomatie-iran-turquie.jpg',
  1,
  1
FROM articles a
WHERE a.slug = 'diplomatie-application-resolution-598'
  AND NOT EXISTS (
    SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
  'article', a.id,
  'Mines et rehabilitation des zones frontalieres',
  'Etat des besoins de deminage et de reconstruction apres guerre.',
  '/article/humanitaire-mines-rehabilitation-zones-frontieres',
  'Mines et rehabilitation',
  'Priorites humanitaires dans les zones frontalieres.',
  '/package/assets/images/real/impact-humain-defapress.jpg',
  1,
  1
FROM articles a
WHERE a.slug = 'humanitaire-mines-rehabilitation-zones-frontieres'
  AND NOT EXISTS (
    SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
  'article', a.id,
  'Echanges de prisonniers apres conflit: suivi',
  'Mecanismes d echange de prisonniers et verification internationale.',
  '/article/actualites-suivi-echanges-prisonniers-apres-conflit',
  'Echanges de prisonniers',
  'Suivi de la phase de normalisation apres conflit.',
  '/package/assets/images/real/impact-humain-defapress.jpg',
  1,
  1
FROM articles a
WHERE a.slug = 'actualites-suivi-echanges-prisonniers-apres-conflit'
  AND NOT EXISTS (
    SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
  'article', a.id,
  'Lecons strategiques du conflit Iran-Irak',
  'Enseignements operationnels et politiques de la guerre Iran-Irak.',
  '/article/analyse-lecons-strategiques-conflit-iran-irak',
  'Lecons strategiques',
  'Synthese des enseignements tactiques et diplomatiques.',
  '/package/assets/images/real/chronologie-khorramshahr.jpg',
  1,
  1
FROM articles a
WHERE a.slug = 'analyse-lecons-strategiques-conflit-iran-irak'
  AND NOT EXISTS (
    SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );

INSERT INTO seo_pages (page_type, page_ref_id, title_tag, meta_description, canonical_url, og_title, og_description, og_image_url, robots_index, robots_follow)
SELECT
  'article', a.id,
  'Phase finale 1988: chronologie du cessez le feu',
  'Chronologie des derniers mois du conflit jusqu au cessez le feu de 1988.',
  '/article/chronologie-phase-finale-cessez-le-feu-1988',
  'Phase finale 1988',
  'Dernieres etapes menant au cessez le feu.',
  '/package/assets/images/real/chronologie-khorramshahr.jpg',
  1,
  1
FROM articles a
WHERE a.slug = 'chronologie-phase-finale-cessez-le-feu-1988'
  AND NOT EXISTS (
    SELECT 1 FROM seo_pages s WHERE s.page_type = 'article' AND s.page_ref_id = a.id
  );
