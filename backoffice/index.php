<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/function.php';

loadDotEnv(__DIR__ . '/.env');

$moduleLabels = [
    'dashboard' => 'Tableau de bord',
    'articles' => 'Articles',
    'categories' => 'Categories',
    'tags' => 'Tags',
    'timeline_events' => 'Chronologie',
];

$allowedModules = array_merge(array_keys($moduleLabels), ['login', 'logout']);
$route = parseCurrentRoute($allowedModules);

$module = (string) ($route['module'] ?? 'dashboard');
$action = (string) ($route['action'] ?? ($module === 'dashboard' ? 'view' : 'list'));

$token = csrfToken();

$errors = [];
$formState = [];

if ($module === 'logout') {
    logoutBackoffice();
    setFlash('success', 'Deconnexion reussie.');
    redirectTo(buildUrl('login', 'view'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $module === 'login') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!isValidCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Token CSRF invalide. Recharge la page puis reessaie.';
    }

    if ($username === '') {
        $errors[] = 'Le nom d utilisateur est obligatoire.';
    }

    if ($password === '') {
        $errors[] = 'Le mot de passe est obligatoire.';
    }

    if ($errors === []) {
        if (attemptBackofficeLogin($username, $password)) {
            setFlash('success', 'Connexion reussie.');
            redirectTo(buildUrl('dashboard', 'view'));
        }

        $errors[] = 'Identifiants invalides.';
    }

    $formState = [
        'username' => $username,
    ];
}

$isAuthenticated = isBackofficeAuthenticated();

if (!$isAuthenticated && $module !== 'login') {
    redirectTo(buildUrl('login', 'view'));
}

if ($isAuthenticated && $module === 'login') {
    redirectTo(buildUrl('dashboard', 'view'));
}

$fatalError = null;
$pdo = null;

if ($module !== 'login') {
    try {
        $pdo = getDbConnection();
    } catch (Throwable $exception) {
        $fatalError = 'Connexion impossible a la base de donnees: ' . $exception->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO && $fatalError === null) {
    $postedModule = (string) ($_POST['module'] ?? $module);
    $formAction = (string) ($_POST['form_action'] ?? '');
    $returnAction = (string) ($_POST['return_action'] ?? 'list');

    if (!in_array($postedModule, $allowedModules, true)) {
        $errors[] = 'Module invalide.';
    }

    if (!isValidCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Token CSRF invalide. Recharge la page puis reessaie.';
    }

    if ($errors === []) {
        try {
            if ($postedModule === 'categories') {
                $id = (int) ($_POST['id'] ?? 0);

                if ($formAction === 'save') {
                    $name = trim((string) ($_POST['name'] ?? ''));
                    $slugInput = trim((string) ($_POST['slug'] ?? ''));
                    $description = normalizeNullableText($_POST['description'] ?? null);
                    $slug = slugify($slugInput !== '' ? $slugInput : $name);

                    if ($name === '') {
                        $errors[] = 'Le nom de la categorie est obligatoire.';
                    }

                    if ($errors === []) {
                        saveCategory($pdo, $id, $name, $slug, $description);
                        setFlash('success', $id > 0 ? 'Categorie mise a jour.' : 'Categorie creee.');
                        redirectTo(buildUrl('categories', 'list'));
                    }

                    $module = 'categories';
                    $action = $returnAction === 'edit' ? 'edit' : 'create';
                    $formState = $_POST;
                } elseif ($formAction === 'delete') {
                    if ($id <= 0) {
                        throw new InvalidArgumentException('Identifiant categorie invalide.');
                    }

                    deleteById($pdo, 'categories', $id);
                    setFlash('success', 'Categorie supprimee.');
                    redirectTo(buildUrl('categories', 'list'));
                }
            }

            if ($postedModule === 'tags') {
                $id = (int) ($_POST['id'] ?? 0);

                if ($formAction === 'save') {
                    $name = trim((string) ($_POST['name'] ?? ''));
                    $slugInput = trim((string) ($_POST['slug'] ?? ''));
                    $slug = slugify($slugInput !== '' ? $slugInput : $name);

                    if ($name === '') {
                        $errors[] = 'Le nom du tag est obligatoire.';
                    }

                    if ($errors === []) {
                        saveTag($pdo, $id, $name, $slug);
                        setFlash('success', $id > 0 ? 'Tag mis a jour.' : 'Tag cree.');
                        redirectTo(buildUrl('tags', 'list'));
                    }

                    $module = 'tags';
                    $action = $returnAction === 'edit' ? 'edit' : 'create';
                    $formState = $_POST;
                } elseif ($formAction === 'delete') {
                    if ($id <= 0) {
                        throw new InvalidArgumentException('Identifiant tag invalide.');
                    }

                    deleteById($pdo, 'tags', $id);
                    setFlash('success', 'Tag supprime.');
                    redirectTo(buildUrl('tags', 'list'));
                }
            }

            if ($postedModule === 'articles') {
                $id = (int) ($_POST['id'] ?? 0);

                if ($formAction === 'save') {
                    $conflictId = normalizeNullableInt($_POST['conflict_id'] ?? null);
                    $categoryId = normalizeNullableInt($_POST['category_id'] ?? null) ?? 0;
                    $authorId = normalizeNullableInt($_POST['author_id'] ?? null) ?? 0;
                    $title = trim((string) ($_POST['title'] ?? ''));
                    $slugInput = trim((string) ($_POST['slug'] ?? ''));
                    $slug = slugify($slugInput !== '' ? $slugInput : $title);
                    $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
                    $content = trim((string) ($_POST['content'] ?? ''));
                    $coverImageUrl = normalizeNullableText($_POST['cover_image_url'] ?? null);
                    $coverImageAlt = normalizeNullableText($_POST['cover_image_alt'] ?? null);
                    $status = (string) ($_POST['status'] ?? 'draft');
                    $publishedAtInput = normalizeDateTimeLocal($_POST['published_at'] ?? null);
                    $tagIds = asIntArray($_POST['tag_ids'] ?? []);
                    $coverUpload = [
                        'url' => null,
                        'mime_type' => null,
                        'width' => null,
                        'height' => null,
                        'error' => null,
                    ];

                    $statusWhitelist = ['draft', 'published', 'archived'];
                    if (!in_array($status, $statusWhitelist, true)) {
                        $status = 'draft';
                    }

                    if ($status === 'published' && $publishedAtInput === null) {
                        $publishedAtInput = date('Y-m-d H:i:s');
                    }

                    if ($title === '') {
                        $errors[] = 'Le titre de l article est obligatoire.';
                    }

                    if ($categoryId <= 0) {
                        $errors[] = 'La categorie est obligatoire.';
                    }

                    if ($authorId <= 0) {
                        $errors[] = 'L auteur est obligatoire.';
                    }

                    if ($excerpt === '') {
                        $errors[] = 'L extrait est obligatoire.';
                    }

                    if ($content === '') {
                        $errors[] = 'Le contenu est obligatoire.';
                    }

                    if ($errors === []) {
                        $coverUpload = processArticleCoverUpload($_FILES['cover_image_file'] ?? null, $slug);

                        if ($coverUpload['error'] !== null) {
                            $errors[] = (string) $coverUpload['error'];
                        }

                        if ($coverUpload['url'] !== null) {
                            $coverImageUrl = (string) $coverUpload['url'];
                            if ($coverImageAlt === null || trim($coverImageAlt) === '') {
                                $coverImageAlt = $title;
                            }
                        }
                    }

                    if ($errors === []) {
                        $articleId = saveArticle(
                            $pdo,
                            $id,
                            $conflictId,
                            $categoryId,
                            $authorId,
                            $title,
                            $slug,
                            $excerpt,
                            $content,
                            $coverImageUrl,
                            $coverImageAlt,
                            $status,
                            $publishedAtInput,
                            $tagIds
                        );

                        if ($coverUpload['url'] !== null) {
                            saveArticleUploadedMediaAsset(
                                $pdo,
                                $articleId,
                                (string) $coverUpload['url'],
                                $coverImageAlt,
                                is_string($coverUpload['mime_type']) ? $coverUpload['mime_type'] : null,
                                is_int($coverUpload['width']) ? $coverUpload['width'] : null,
                                is_int($coverUpload['height']) ? $coverUpload['height'] : null
                            );
                        }

                        setFlash('success', $id > 0 ? 'Article mis a jour.' : 'Article cree.');
                        redirectTo(buildUrl('articles', 'list'));
                    }

                    $module = 'articles';
                    $action = $returnAction === 'edit' ? 'edit' : 'create';
                    $formState = $_POST;
                } elseif ($formAction === 'delete') {
                    if ($id <= 0) {
                        throw new InvalidArgumentException('Identifiant article invalide.');
                    }

                    deleteById($pdo, 'articles', $id);
                    setFlash('success', 'Article supprime.');
                    redirectTo(buildUrl('articles', 'list'));
                }
            }

            if ($postedModule === 'timeline_events') {
                $id = (int) ($_POST['id'] ?? 0);

                if ($formAction === 'save') {
                    $conflictId = normalizeNullableInt($_POST['conflict_id'] ?? null) ?? 0;
                    $locationId = normalizeNullableInt($_POST['location_id'] ?? null);
                    $title = trim((string) ($_POST['title'] ?? ''));
                    $slugInput = trim((string) ($_POST['slug'] ?? ''));
                    $slug = slugify($slugInput !== '' ? $slugInput : $title);
                    $eventDate = trim((string) ($_POST['event_date'] ?? ''));
                    $summary = trim((string) ($_POST['summary'] ?? ''));
                    $details = normalizeNullableText($_POST['details'] ?? null);
                    $verificationStatus = (string) ($_POST['verification_status'] ?? 'under_review');
                    $primarySourceId = normalizeNullableInt($_POST['primary_source_id'] ?? null);

                    if ($conflictId <= 0) {
                        $errors[] = 'Le conflit est obligatoire.';
                    }

                    if ($title === '') {
                        $errors[] = 'Le titre de l evenement est obligatoire.';
                    }

                    if ($eventDate === '') {
                        $errors[] = 'La date de l evenement est obligatoire.';
                    }

                    if ($summary === '') {
                        $errors[] = 'Le resume est obligatoire.';
                    }

                    $verificationWhitelist = ['verified', 'under_review'];
                    if (!in_array($verificationStatus, $verificationWhitelist, true)) {
                        $verificationStatus = 'under_review';
                    }

                    if ($errors === []) {
                        saveTimelineEvent(
                            $pdo,
                            $id,
                            $conflictId,
                            $locationId,
                            $title,
                            $slug,
                            $eventDate,
                            $summary,
                            $details,
                            $verificationStatus,
                            $primarySourceId
                        );

                        setFlash('success', $id > 0 ? 'Evenement mis a jour.' : 'Evenement cree.');
                        redirectTo(buildUrl('timeline_events', 'list'));
                    }

                    $module = 'timeline_events';
                    $action = $returnAction === 'edit' ? 'edit' : 'create';
                    $formState = $_POST;
                } elseif ($formAction === 'delete') {
                    if ($id <= 0) {
                        throw new InvalidArgumentException('Identifiant evenement invalide.');
                    }

                    deleteById($pdo, 'timeline_events', $id);
                    setFlash('success', 'Evenement supprime.');
                    redirectTo(buildUrl('timeline_events', 'list'));
                }
            }
        } catch (Throwable $exception) {
            $errors[] = 'Erreur: ' . $exception->getMessage();
            $module = $postedModule;
            if ($action !== 'edit' && $action !== 'create') {
                $action = 'list';
            }
        }
    }
}

$flash = pullFlash();

$dashboardStats = [];
$listRows = [];
$formValues = [];
$editingId = (int) ($route['id'] ?? 0);

$categoryOptions = [];
$tagOptions = [];
$conflictOptions = [];
$userOptions = [];
$locationOptions = [];
$sourceOptions = [];

if ($pdo instanceof PDO && $fatalError === null) {
    if ($module === 'dashboard') {
        $dashboardStats = [
            'Articles' => countTable($pdo, 'articles'),
            'Categories' => countTable($pdo, 'categories'),
            'Tags' => countTable($pdo, 'tags'),
            'Evenements chrono' => countTable($pdo, 'timeline_events'),
            'Sources' => countTable($pdo, 'sources'),
            'Medias' => countTable($pdo, 'media_assets'),
        ];
    }

    if ($module === 'categories') {
        if ($action === 'list') {
            $listRows = fetchCategoryList($pdo);
        }

        if ($action === 'create') {
            $formValues = [
                'id' => 0,
                'name' => '',
                'slug' => '',
                'description' => '',
            ];
        }

        if ($action === 'edit') {
            if ($formState !== []) {
                $formValues = [
                    'id' => (int) ($formState['id'] ?? 0),
                    'name' => (string) ($formState['name'] ?? ''),
                    'slug' => (string) ($formState['slug'] ?? ''),
                    'description' => (string) ($formState['description'] ?? ''),
                ];
            } else {
                if ($editingId <= 0) {
                    setFlash('error', 'Categorie introuvable.');
                    redirectTo(buildUrl('categories', 'list'));
                }

                $entity = fetchCategoryById($pdo, $editingId);
                if ($entity === null) {
                    setFlash('error', 'Categorie introuvable.');
                    redirectTo(buildUrl('categories', 'list'));
                }

                $formValues = $entity;
            }
        }
    }

    if ($module === 'tags') {
        if ($action === 'list') {
            $listRows = fetchTagList($pdo);
        }

        if ($action === 'create') {
            $formValues = [
                'id' => 0,
                'name' => '',
                'slug' => '',
            ];
        }

        if ($action === 'edit') {
            if ($formState !== []) {
                $formValues = [
                    'id' => (int) ($formState['id'] ?? 0),
                    'name' => (string) ($formState['name'] ?? ''),
                    'slug' => (string) ($formState['slug'] ?? ''),
                ];
            } else {
                if ($editingId <= 0) {
                    setFlash('error', 'Tag introuvable.');
                    redirectTo(buildUrl('tags', 'list'));
                }

                $entity = fetchTagById($pdo, $editingId);
                if ($entity === null) {
                    setFlash('error', 'Tag introuvable.');
                    redirectTo(buildUrl('tags', 'list'));
                }

                $formValues = $entity;
            }
        }
    }

    if ($module === 'articles') {
        $categoryOptions = fetchCategories($pdo);
        $tagOptions = fetchTags($pdo);
        $conflictOptions = fetchConflicts($pdo);
        $userOptions = fetchUsers($pdo);

        if ($action === 'list') {
            $listRows = fetchArticleList($pdo);
        }

        if ($action === 'create') {
            $formValues = [
                'id' => 0,
                'conflict_id' => '',
                'category_id' => '',
                'author_id' => '',
                'title' => '',
                'slug' => '',
                'excerpt' => '',
                'content' => '',
                'cover_image_url' => '',
                'cover_image_alt' => '',
                'status' => 'draft',
                'published_at' => '',
                'tag_ids' => [],
            ];
        }

        if ($action === 'edit') {
            if ($formState !== []) {
                $formValues = [
                    'id' => (int) ($formState['id'] ?? 0),
                    'conflict_id' => (string) ($formState['conflict_id'] ?? ''),
                    'category_id' => (string) ($formState['category_id'] ?? ''),
                    'author_id' => (string) ($formState['author_id'] ?? ''),
                    'title' => (string) ($formState['title'] ?? ''),
                    'slug' => (string) ($formState['slug'] ?? ''),
                    'excerpt' => (string) ($formState['excerpt'] ?? ''),
                    'content' => (string) ($formState['content'] ?? ''),
                    'cover_image_url' => (string) ($formState['cover_image_url'] ?? ''),
                    'cover_image_alt' => (string) ($formState['cover_image_alt'] ?? ''),
                    'status' => (string) ($formState['status'] ?? 'draft'),
                    'published_at' => (string) ($formState['published_at'] ?? ''),
                    'tag_ids' => asIntArray($formState['tag_ids'] ?? []),
                ];
            } else {
                if ($editingId <= 0) {
                    setFlash('error', 'Article introuvable.');
                    redirectTo(buildUrl('articles', 'list'));
                }

                $entity = fetchArticleById($pdo, $editingId);
                if ($entity === null) {
                    setFlash('error', 'Article introuvable.');
                    redirectTo(buildUrl('articles', 'list'));
                }

                $entity['tag_ids'] = fetchArticleTagIds($pdo, $editingId);
                $entity['published_at'] = toDateTimeLocal((string) ($entity['published_at'] ?? ''));
                $formValues = $entity;
            }
        }
    }

    if ($module === 'timeline_events') {
        $conflictOptions = fetchConflicts($pdo);
        $locationOptions = fetchLocations($pdo);
        $sourceOptions = fetchSources($pdo);

        if ($action === 'list') {
            $listRows = fetchTimelineEventList($pdo);
        }

        if ($action === 'create') {
            $formValues = [
                'id' => 0,
                'conflict_id' => '',
                'location_id' => '',
                'title' => '',
                'slug' => '',
                'event_date' => '',
                'summary' => '',
                'details' => '',
                'verification_status' => 'under_review',
                'primary_source_id' => '',
            ];
        }

        if ($action === 'edit') {
            if ($formState !== []) {
                $formValues = [
                    'id' => (int) ($formState['id'] ?? 0),
                    'conflict_id' => (string) ($formState['conflict_id'] ?? ''),
                    'location_id' => (string) ($formState['location_id'] ?? ''),
                    'title' => (string) ($formState['title'] ?? ''),
                    'slug' => (string) ($formState['slug'] ?? ''),
                    'event_date' => (string) ($formState['event_date'] ?? ''),
                    'summary' => (string) ($formState['summary'] ?? ''),
                    'details' => (string) ($formState['details'] ?? ''),
                    'verification_status' => (string) ($formState['verification_status'] ?? 'under_review'),
                    'primary_source_id' => (string) ($formState['primary_source_id'] ?? ''),
                ];
            } else {
                if ($editingId <= 0) {
                    setFlash('error', 'Evenement introuvable.');
                    redirectTo(buildUrl('timeline_events', 'list'));
                }

                $entity = fetchTimelineEventById($pdo, $editingId);
                if ($entity === null) {
                    setFlash('error', 'Evenement introuvable.');
                    redirectTo(buildUrl('timeline_events', 'list'));
                }

                $formValues = [
                    'id' => (int) $entity['id'],
                    'conflict_id' => (string) $entity['conflict_id'],
                    'location_id' => (string) ($entity['location_id'] ?? ''),
                    'title' => (string) $entity['title'],
                    'slug' => (string) $entity['slug'],
                    'event_date' => (string) $entity['event_date'],
                    'summary' => (string) $entity['summary'],
                    'details' => (string) ($entity['details'] ?? ''),
                    'verification_status' => (string) $entity['verification_status'],
                    'primary_source_id' => (string) ($entity['primary_source_id'] ?? ''),
                ];
            }
        }
    }
}

$actionLabels = [
    'view' => 'Vue',
    'list' => 'Liste',
    'create' => 'Creation',
    'edit' => 'Edition',
];

$frontofficeBaseUrl = rtrim(envValue('FRONTOFFICE_BASE_URL', 'http://localhost:8080'), '/');
$stylesheetUrl = (appBasePath() === '' ? '' : appBasePath()) . '/assets/style.css';
$frontofficeModulesUrl = $frontofficeBaseUrl . '/package/modules.php';
$currentModuleLabel = $moduleLabels[$module] ?? ($module === 'login' ? 'Connexion' : 'Administration');
$pageContext = $module === 'login'
    ? 'Connexion'
    : ($module === 'dashboard'
        ? 'Tableau de bord'
        : (($actionLabels[$action] ?? 'Gestion') . ' ' . ($moduleLabels[$module] ?? 'Administration')));
$pageTitle = $pageContext . ' | Backoffice Guerre Irana';
$metaDescription = 'Backoffice pour la gestion des contenus editoriaux du projet Guerre Irana: articles, categories, tags et chronologie.';
$coverPreviewUrl = null;

if ($module === 'articles' && ($action === 'create' || $action === 'edit')) {
    $coverPreviewUrl = resolveCoverPreviewUrl(
        normalizeNullableText($formValues['cover_image_url'] ?? null),
        $frontofficeBaseUrl
    );
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo e($metaDescription); ?>">
    <meta name="author" content="Guerre Irana">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#c23b22">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo e($stylesheetUrl); ?>">
</head>
<body>
    <header class="admin-header">
        <div class="wrap topbar admin-topbar">
            <p class="brand"><a href="<?php echo e(buildUrl('dashboard', 'view')); ?>">Backoffice</a></p>

            <nav class="admin-nav" aria-label="Navigation backoffice">
                <?php if ($isAuthenticated): ?>
                    <?php foreach ($moduleLabels as $moduleKey => $label): ?>
                        <a class="nav-link <?php echo $module === $moduleKey ? 'active' : ''; ?>" href="<?php echo e(buildUrl($moduleKey)); ?>">
                            <?php echo e($label); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>

            <div class="admin-tools">
                <a class="nav-link" href="<?php echo e($frontofficeModulesUrl); ?>">FrontOffice</a>
                <?php if ($isAuthenticated): ?>
                    <a class="nav-link nav-link-danger" href="<?php echo e(buildUrl('logout', 'view')); ?>">Deconnexion</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="wrap admin-main">
            <header class="page-head">
                <h2><?php echo e($currentModuleLabel); ?></h2>
                <?php if ($module !== 'dashboard' && $action === 'list'): ?>
                    <a class="btn btn-primary" href="<?php echo e(buildUrl($module, 'create')); ?>">Ajouter</a>
                <?php endif; ?>
                <?php if ($module !== 'dashboard' && ($action === 'create' || $action === 'edit')): ?>
                    <a class="btn" href="<?php echo e(buildUrl($module, 'list')); ?>">Retour a la liste</a>
                <?php endif; ?>
            </header>

            <?php if ($fatalError !== null): ?>
                <div class="alert alert-error"><?php echo e($fatalError); ?></div>
            <?php endif; ?>

            <?php if ($flash !== null): ?>
                <div class="alert <?php echo ($flash['type'] ?? '') === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo e((string) ($flash['message'] ?? '')); ?>
                </div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e((string) $error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($module === 'login'): ?>
                <section class="panel login-panel">
                    <h3>Connexion au backoffice</h3>
                    <form method="post" action="<?php echo e(buildUrl('login', 'view')); ?>" class="form-grid login-form">
                        <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">

                        <label class="full">
                            Utilisateur
                            <input
                                type="text"
                                name="username"
                                required
                                autocomplete="username"
                                value="<?php echo e((string) ($formState['username'] ?? '')); ?>"
                            >
                        </label>

                        <label class="full">
                            Mot de passe
                            <input type="password" name="password" required autocomplete="current-password">
                        </label>

                        <div class="full">
                            <button class="btn btn-primary" type="submit">Se connecter</button>
                        </div>
                    </form>
                    <p>Identifiants configures via BACKOFFICE_AUTH_USER et BACKOFFICE_AUTH_PASS.</p>
                </section>
            <?php endif; ?>

            <?php if ($module === 'dashboard'): ?>
                <section class="cards">
                    <?php foreach ($dashboardStats as $label => $value): ?>
                        <article class="card">
                            <p class="card-label"><?php echo e((string) $label); ?></p>
                            <p class="card-value"><?php echo e((string) $value); ?></p>
                        </article>
                    <?php endforeach; ?>
                </section>

                <section class="panel">
                    <h3>Acces rapide</h3>
                    <p>Utilise le menu pour gerer les articles, la taxonomie et les evenements de chronologie.</p>
                    <p>Cette version est protegee par authentification utilisateur / mot de passe.</p>
                </section>
            <?php endif; ?>

            <?php if ($module === 'categories' && $action === 'list'): ?>
                <section class="panel">
                    <h3>Liste des categories</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th>Mise a jour</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listRows as $row): ?>
                                <tr>
                                    <td><?php echo e((string) $row['id']); ?></td>
                                    <td><?php echo e((string) $row['name']); ?></td>
                                    <td><?php echo e((string) $row['slug']); ?></td>
                                    <td><?php echo e((string) ($row['description'] ?? '')); ?></td>
                                    <td><?php echo e(formatDateTime((string) ($row['updated_at'] ?? ''))); ?></td>
                                    <td class="actions">
                                        <a class="btn" href="<?php echo e(buildUrl('categories', 'edit', ['id' => $row['id']])); ?>">Modifier</a>
                                        <form method="post" action="<?php echo e(buildUrl('categories', 'list')); ?>" onsubmit="return confirm('Supprimer cette categorie ?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                                            <input type="hidden" name="module" value="categories">
                                            <input type="hidden" name="form_action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo e((string) $row['id']); ?>">
                                            <button class="btn btn-danger" type="submit">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endif; ?>

            <?php if ($module === 'categories' && ($action === 'create' || $action === 'edit')): ?>
                <?php require __DIR__ . '/pages/categories-form.php'; ?>
            <?php endif; ?>

            <?php if ($module === 'tags' && $action === 'list'): ?>
                <section class="panel">
                    <h3>Liste des tags</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Slug</th>
                                <th>Creation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listRows as $row): ?>
                                <tr>
                                    <td><?php echo e((string) $row['id']); ?></td>
                                    <td><?php echo e((string) $row['name']); ?></td>
                                    <td><?php echo e((string) $row['slug']); ?></td>
                                    <td><?php echo e(formatDateTime((string) ($row['created_at'] ?? ''))); ?></td>
                                    <td class="actions">
                                        <a class="btn" href="<?php echo e(buildUrl('tags', 'edit', ['id' => $row['id']])); ?>">Modifier</a>
                                        <form method="post" action="<?php echo e(buildUrl('tags', 'list')); ?>" onsubmit="return confirm('Supprimer ce tag ?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                                            <input type="hidden" name="module" value="tags">
                                            <input type="hidden" name="form_action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo e((string) $row['id']); ?>">
                                            <button class="btn btn-danger" type="submit">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endif; ?>

            <?php if ($module === 'tags' && ($action === 'create' || $action === 'edit')): ?>
                <?php require __DIR__ . '/pages/tags-form.php'; ?>
            <?php endif; ?>

            <?php if ($module === 'articles' && $action === 'list'): ?>
                <section class="panel">
                    <h3>Liste des articles</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Categorie</th>
                                <th>Auteur</th>
                                <th>Conflit</th>
                                <th>Status</th>
                                <th>Publication</th>
                                <th>Tags</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listRows as $row): ?>
                                <tr>
                                    <td><?php echo e((string) $row['id']); ?></td>
                                    <td>
                                        <strong><?php echo e((string) $row['title']); ?></strong>
                                        <br>
                                        <small><?php echo e((string) $row['slug']); ?></small>
                                    </td>
                                    <td><?php echo e((string) $row['category_name']); ?></td>
                                    <td><?php echo e((string) $row['author_name']); ?></td>
                                    <td><?php echo e((string) $row['conflict_title']); ?></td>
                                    <td><?php echo e((string) $row['status']); ?></td>
                                    <td><?php echo e(formatDateTime((string) ($row['published_at'] ?? ''))); ?></td>
                                    <td><?php echo e((string) ($row['tag_names'] ?? '')); ?></td>
                                    <td class="actions">
                                        <a class="btn" href="<?php echo e(buildUrl('articles', 'edit', ['id' => $row['id']])); ?>">Modifier</a>
                                        <form method="post" action="<?php echo e(buildUrl('articles', 'list')); ?>" onsubmit="return confirm('Supprimer cet article ?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                                            <input type="hidden" name="module" value="articles">
                                            <input type="hidden" name="form_action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo e((string) $row['id']); ?>">
                                            <button class="btn btn-danger" type="submit">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endif; ?>

            <?php if ($module === 'articles' && ($action === 'create' || $action === 'edit')): ?>
                <?php require __DIR__ . '/pages/articles-form.php'; ?>
            <?php endif; ?>

            <?php if ($module === 'timeline_events' && $action === 'list'): ?>
                <section class="panel">
                    <h3>Liste des evenements</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Conflit</th>
                                <th>Date</th>
                                <th>Lieu</th>
                                <th>Verification</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listRows as $row): ?>
                                <tr>
                                    <td><?php echo e((string) $row['id']); ?></td>
                                    <td>
                                        <strong><?php echo e((string) $row['title']); ?></strong>
                                        <br>
                                        <small><?php echo e((string) $row['slug']); ?></small>
                                    </td>
                                    <td><?php echo e((string) $row['conflict_title']); ?></td>
                                    <td><?php echo e((string) $row['event_date']); ?></td>
                                    <td><?php echo e((string) $row['location_label']); ?></td>
                                    <td><?php echo e((string) $row['verification_status']); ?></td>
                                    <td class="actions">
                                        <a class="btn" href="<?php echo e(buildUrl('timeline_events', 'edit', ['id' => $row['id']])); ?>">Modifier</a>
                                        <form method="post" action="<?php echo e(buildUrl('timeline_events', 'list')); ?>" onsubmit="return confirm('Supprimer cet evenement ?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                                            <input type="hidden" name="module" value="timeline_events">
                                            <input type="hidden" name="form_action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo e((string) $row['id']); ?>">
                                            <button class="btn btn-danger" type="submit">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endif; ?>

            <?php if ($module === 'timeline_events' && ($action === 'create' || $action === 'edit')): ?>
                <?php require __DIR__ . '/pages/timeline-events-form.php'; ?>
            <?php endif; ?>
    </main>
</body>
</html>
