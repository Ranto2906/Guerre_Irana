<?php

declare(strict_types=1);

session_start();

function loadDotEnv(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        $parts = explode('=', $trimmed, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $value = trim($value, "\"'");

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function envValue(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }

    return (string) $value;
}

function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = envValue('DB_HOST', '127.0.0.1');
    $port = envValue('DB_PORT', '3306');
    $database = envValue('DB_DATABASE', 'iran_info_site');
    $user = envValue('DB_USER', 'app_user');
    $password = envValue('DB_PASSWORD', 'app_password');

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return 'item-' . date('YmdHis');
    }

    if (function_exists('iconv')) {
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($transliterated !== false) {
            $value = $transliterated;
        }
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;
    $value = trim($value, '-');

    if ($value === '') {
        return 'item-' . date('YmdHis');
    }

    return $value;
}

function normalizeNullableInt(mixed $value): ?int
{
    $stringValue = trim((string) $value);
    if ($stringValue === '') {
        return null;
    }

    if (!ctype_digit($stringValue)) {
        return null;
    }

    $intValue = (int) $stringValue;

    return $intValue > 0 ? $intValue : null;
}

function normalizeNullableText(mixed $value): ?string
{
    $stringValue = trim((string) $value);

    return $stringValue === '' ? null : $stringValue;
}

function normalizeDateTimeLocal(mixed $value): ?string
{
    $stringValue = trim((string) $value);
    if ($stringValue === '') {
        return null;
    }

    $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $stringValue);
    if (!$dateTime) {
        return null;
    }

    return $dateTime->format('Y-m-d H:i:s');
}

function toDateTimeLocal(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }

    return date('Y-m-d\TH:i', $timestamp);
}

function formatDateTime(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return '-';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    return date('d/m/Y H:i', $timestamp);
}

function articleUploadDirectory(): string
{
    return dirname(__DIR__)
        . DIRECTORY_SEPARATOR
        . 'frontoffice'
        . DIRECTORY_SEPARATOR
        . 'package'
        . DIRECTORY_SEPARATOR
        . 'assets'
        . DIRECTORY_SEPARATOR
        . 'images'
        . DIRECTORY_SEPARATOR
        . 'uploads';
}

function articleUploadPublicPrefix(): string
{
    return '/package/assets/images/uploads/';
}

function processArticleCoverUpload(?array $file, string $slug): array
{
    $result = [
        'url' => null,
        'mime_type' => null,
        'width' => null,
        'height' => null,
        'error' => null,
    ];

    if (!is_array($file) || !isset($file['error'])) {
        return $result;
    }

    $uploadError = (int) $file['error'];

    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        return $result;
    }

    if ($uploadError !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (limite serveur).',
            UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (limite formulaire).',
            UPLOAD_ERR_PARTIAL => 'Televersement incomplet.',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire absent sur le serveur.',
            UPLOAD_ERR_CANT_WRITE => 'Impossible d ecrire le fichier sur le disque.',
            UPLOAD_ERR_EXTENSION => 'Televersement bloque par une extension PHP.',
        ];

        $result['error'] = $messages[$uploadError] ?? 'Erreur de televersement inconnue.';

        return $result;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        $result['error'] = 'Le fichier transmis est invalide.';

        return $result;
    }

    if ($size <= 0 || $size > (5 * 1024 * 1024)) {
        $result['error'] = 'Le fichier doit faire moins de 5 Mo.';

        return $result;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = (string) $finfo->file($tmpName);

    $allowedMimeToExt = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!array_key_exists($mimeType, $allowedMimeToExt)) {
        $result['error'] = 'Format non autorise. Utilise JPG, PNG ou WEBP.';

        return $result;
    }

    $directory = articleUploadDirectory();
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        $result['error'] = 'Impossible de preparer le dossier de televersement.';

        return $result;
    }

    $fileName = slugify($slug) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimeToExt[$mimeType];
    $destination = $directory . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        $result['error'] = 'Echec lors de l enregistrement du fichier.';

        return $result;
    }

    $sizeInfo = @getimagesize($destination);

    $result['url'] = articleUploadPublicPrefix() . $fileName;
    $result['mime_type'] = $mimeType;
    $result['width'] = is_array($sizeInfo) && isset($sizeInfo[0]) ? (int) $sizeInfo[0] : null;
    $result['height'] = is_array($sizeInfo) && isset($sizeInfo[1]) ? (int) $sizeInfo[1] : null;

    return $result;
}

function saveArticleUploadedMediaAsset(
    PDO $pdo,
    int $articleId,
    string $filePath,
    ?string $altText,
    ?string $mimeType,
    ?int $width,
    ?int $height
): void {
    $resolvedAlt = trim((string) $altText);
    if ($resolvedAlt === '') {
        $resolvedAlt = 'Image de couverture';
    }

    $stmt = $pdo->prepare('
        INSERT INTO media_assets (
            article_id,
            file_path,
            title,
            alt_text,
            caption,
            credit,
            mime_type,
            width,
            height
        ) VALUES (
            :article_id,
            :file_path,
            :title,
            :alt_text,
            NULL,
            NULL,
            :mime_type,
            :width,
            :height
        )
    ');

    $stmt->bindValue(':article_id', $articleId, PDO::PARAM_INT);
    $stmt->bindValue(':file_path', $filePath);
    $stmt->bindValue(':title', 'Image de couverture');
    $stmt->bindValue(':alt_text', $resolvedAlt);

    if ($mimeType !== null) {
        $stmt->bindValue(':mime_type', $mimeType);
    } else {
        $stmt->bindValue(':mime_type', null, PDO::PARAM_NULL);
    }

    if ($width !== null) {
        $stmt->bindValue(':width', $width, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':width', null, PDO::PARAM_NULL);
    }

    if ($height !== null) {
        $stmt->bindValue(':height', $height, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':height', null, PDO::PARAM_NULL);
    }

    $stmt->execute();
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function isValidCsrfToken(?string $token): bool
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        return false;
    }

    if ($token === null) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pullFlash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function asIntArray(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $result = [];

    foreach ($value as $item) {
        $candidate = normalizeNullableInt($item);
        if ($candidate !== null) {
            $result[] = $candidate;
        }
    }

    return array_values(array_unique($result));
}

function appBasePath(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $directory = rtrim(dirname($scriptName), '/');

    if ($directory === '' || $directory === '.' || $directory === '/') {
        return '';
    }

    return $directory;
}

function parseCurrentRoute(array $allowedModules): array
{
    $moduleFromQuery = (string) ($_GET['module'] ?? '');

    if ($moduleFromQuery !== '') {
        $module = in_array($moduleFromQuery, $allowedModules, true) ? $moduleFromQuery : 'dashboard';
        $defaultAction = $module === 'dashboard' ? 'view' : 'list';
        $action = (string) ($_GET['action'] ?? $defaultAction);

        if (!in_array($action, ['view', 'list', 'create', 'edit'], true)) {
            $action = $defaultAction;
        }

        return [
            'module' => $module,
            'action' => $action,
            'id' => normalizeNullableInt($_GET['id'] ?? null) ?? 0,
        ];
    }

    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $path = (string) (parse_url($requestUri, PHP_URL_PATH) ?? '/');
    $basePath = appBasePath();

    if ($basePath !== '' && str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath));
    }

    $path = trim($path, '/');

    if ($path === '' || $path === 'index.php') {
        return ['module' => 'dashboard', 'action' => 'view', 'id' => 0];
    }

    $segments = explode('/', $path);

    if (($segments[0] ?? '') === 'index.php') {
        array_shift($segments);
    }

    $module = (string) ($segments[0] ?? 'dashboard');

    if (!in_array($module, $allowedModules, true)) {
        return ['module' => 'dashboard', 'action' => 'view', 'id' => 0];
    }

    if ($module === 'dashboard') {
        return ['module' => 'dashboard', 'action' => 'view', 'id' => 0];
    }

    $action = 'list';
    $id = 0;
    $second = (string) ($segments[1] ?? '');
    $third = (string) ($segments[2] ?? '');

    if ($second === 'create') {
        $action = 'create';
    } elseif (ctype_digit($second)) {
        $id = (int) $second;
        if ($third === 'edit') {
            $action = 'edit';
        }
    } elseif (in_array($second, ['list', 'create'], true)) {
        $action = $second;
    }

    return [
        'module' => $module,
        'action' => $action,
        'id' => $id,
    ];
}

function resolveCoverPreviewUrl(?string $coverImageUrl, string $frontofficeBaseUrl): ?string
{
    $resolvedUrl = trim((string) $coverImageUrl);

    if ($resolvedUrl === '') {
        return null;
    }

    if (preg_match('#^https?://#i', $resolvedUrl) === 1) {
        return $resolvedUrl;
    }

    if (str_starts_with($resolvedUrl, '/')) {
        return $frontofficeBaseUrl . $resolvedUrl;
    }

    return $resolvedUrl;
}

function redirectTo(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function buildUrl(string $module, string $action = 'list', array $params = []): string
{
    $basePath = appBasePath();

    if ($module === 'dashboard') {
        $path = '/';
    } elseif ($action === 'create') {
        $path = '/' . rawurlencode($module) . '/create';
    } elseif ($action === 'edit') {
        $id = normalizeNullableInt($params['id'] ?? null);
        if ($id !== null) {
            $path = '/' . rawurlencode($module) . '/' . $id . '/edit';
            unset($params['id']);
        } else {
            $path = '/' . rawurlencode($module);
        }
    } else {
        $path = '/' . rawurlencode($module);
    }

    $query = [];

    foreach ($params as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }

        $query[$key] = $value;
    }

    $queryString = $query !== [] ? ('?' . http_build_query($query)) : '';

    return ($basePath === '' ? '' : $basePath) . $path . $queryString;
}

function countTable(PDO $pdo, string $table): int
{
    $allowed = ['articles', 'categories', 'tags', 'timeline_events', 'media_assets', 'sources'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Table non autorisee.');
    }

    $sql = sprintf('SELECT COUNT(*) AS total FROM %s', $table);
    $row = $pdo->query($sql)->fetch();

    return (int) ($row['total'] ?? 0);
}

function fetchLookupRows(PDO $pdo, string $sql): array
{
    return $pdo->query($sql)->fetchAll() ?: [];
}

function fetchCategories(PDO $pdo): array
{
    return fetchLookupRows($pdo, 'SELECT id, name FROM categories ORDER BY name ASC');
}

function fetchTags(PDO $pdo): array
{
    return fetchLookupRows($pdo, 'SELECT id, name FROM tags ORDER BY name ASC');
}

function fetchConflicts(PDO $pdo): array
{
    return fetchLookupRows($pdo, 'SELECT id, title FROM conflicts ORDER BY title ASC');
}

function fetchUsers(PDO $pdo): array
{
    return fetchLookupRows($pdo, 'SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name ASC');
}

function fetchLocations(PDO $pdo): array
{
    return fetchLookupRows($pdo, 'SELECT id, name, country FROM locations ORDER BY country ASC, name ASC');
}

function fetchSources(PDO $pdo): array
{
    return fetchLookupRows($pdo, 'SELECT id, title FROM sources ORDER BY created_at DESC, id DESC');
}

function fetchCategoryList(PDO $pdo): array
{
    $sql = 'SELECT id, name, slug, description, updated_at FROM categories ORDER BY name ASC';

    return $pdo->query($sql)->fetchAll() ?: [];
}

function fetchTagList(PDO $pdo): array
{
    $sql = 'SELECT id, name, slug, created_at FROM tags ORDER BY name ASC';

    return $pdo->query($sql)->fetchAll() ?: [];
}

function fetchArticleList(PDO $pdo): array
{
    $sql = '
        SELECT
            a.id,
            a.title,
            a.slug,
            a.status,
            a.published_at,
            c.name AS category_name,
            u.full_name AS author_name,
            COALESCE(cf.title, "-") AS conflict_title,
            GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ", ") AS tag_names
        FROM articles a
        INNER JOIN categories c ON c.id = a.category_id
        INNER JOIN users u ON u.id = a.author_id
        LEFT JOIN conflicts cf ON cf.id = a.conflict_id
        LEFT JOIN article_tags at ON at.article_id = a.id
        LEFT JOIN tags t ON t.id = at.tag_id
        GROUP BY a.id
        ORDER BY COALESCE(a.published_at, a.created_at) DESC, a.id DESC
        LIMIT 300
    ';

    return $pdo->query($sql)->fetchAll() ?: [];
}

function fetchTimelineEventList(PDO $pdo): array
{
    $sql = '
        SELECT
            t.id,
            t.title,
            t.slug,
            t.event_date,
            t.verification_status,
            c.title AS conflict_title,
            COALESCE(CONCAT(l.name, " (", l.country, ")"), "-") AS location_label
        FROM timeline_events t
        INNER JOIN conflicts c ON c.id = t.conflict_id
        LEFT JOIN locations l ON l.id = t.location_id
        ORDER BY t.event_date DESC, t.id DESC
        LIMIT 300
    ';

    return $pdo->query($sql)->fetchAll() ?: [];
}

function fetchCategoryById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, name, slug, description FROM categories WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch();

    return $row ?: null;
}

function fetchTagById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, name, slug FROM tags WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch();

    return $row ?: null;
}

function fetchArticleById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('
        SELECT
            id,
            conflict_id,
            category_id,
            author_id,
            title,
            slug,
            excerpt,
            content,
            cover_image_url,
            cover_image_alt,
            status,
            published_at
        FROM articles
        WHERE id = :id
        LIMIT 1
    ');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch();

    return $row ?: null;
}

function fetchTimelineEventById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('
        SELECT
            id,
            conflict_id,
            location_id,
            title,
            slug,
            event_date,
            summary,
            details,
            verification_status,
            primary_source_id
        FROM timeline_events
        WHERE id = :id
        LIMIT 1
    ');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch();

    return $row ?: null;
}

function fetchArticleTagIds(PDO $pdo, int $articleId): array
{
    $stmt = $pdo->prepare('SELECT tag_id FROM article_tags WHERE article_id = :article_id');
    $stmt->bindValue(':article_id', $articleId, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll() ?: [];

    return array_map(static fn(array $row): int => (int) $row['tag_id'], $rows);
}

function saveCategory(PDO $pdo, int $id, string $name, string $slug, ?string $description): void
{
    if ($id > 0) {
        $stmt = $pdo->prepare('
            UPDATE categories
            SET name = :name, slug = :slug, description = :description
            WHERE id = :id
        ');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO categories (name, slug, description)
            VALUES (:name, :slug, :description)
        ');
    }

    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':slug', $slug);
    $stmt->bindValue(':description', $description);
    $stmt->execute();
}

function saveTag(PDO $pdo, int $id, string $name, string $slug): void
{
    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE tags SET name = :name, slug = :slug WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    } else {
        $stmt = $pdo->prepare('INSERT INTO tags (name, slug) VALUES (:name, :slug)');
    }

    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':slug', $slug);
    $stmt->execute();
}

function saveArticle(
    PDO $pdo,
    int $id,
    ?int $conflictId,
    int $categoryId,
    int $authorId,
    string $title,
    string $slug,
    string $excerpt,
    string $content,
    ?string $coverImageUrl,
    ?string $coverImageAlt,
    string $status,
    ?string $publishedAt,
    array $tagIds
): int {
    $pdo->beginTransaction();

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare('
                UPDATE articles
                SET conflict_id = :conflict_id,
                    category_id = :category_id,
                    author_id = :author_id,
                    title = :title,
                    slug = :slug,
                    excerpt = :excerpt,
                    content = :content,
                    cover_image_url = :cover_image_url,
                    cover_image_alt = :cover_image_alt,
                    status = :status,
                    published_at = :published_at
                WHERE id = :id
            ');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO articles (
                    conflict_id,
                    category_id,
                    author_id,
                    title,
                    slug,
                    excerpt,
                    content,
                    cover_image_url,
                    cover_image_alt,
                    status,
                    published_at
                ) VALUES (
                    :conflict_id,
                    :category_id,
                    :author_id,
                    :title,
                    :slug,
                    :excerpt,
                    :content,
                    :cover_image_url,
                    :cover_image_alt,
                    :status,
                    :published_at
                )
            ');
        }

        if ($conflictId !== null) {
            $stmt->bindValue(':conflict_id', $conflictId, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':conflict_id', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':author_id', $authorId, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':slug', $slug);
        $stmt->bindValue(':excerpt', $excerpt);
        $stmt->bindValue(':content', $content);
        $stmt->bindValue(':cover_image_url', $coverImageUrl);
        $stmt->bindValue(':cover_image_alt', $coverImageAlt);
        $stmt->bindValue(':status', $status);

        if ($publishedAt !== null) {
            $stmt->bindValue(':published_at', $publishedAt);
        } else {
            $stmt->bindValue(':published_at', null, PDO::PARAM_NULL);
        }

        $stmt->execute();

        $articleId = $id > 0 ? $id : (int) $pdo->lastInsertId();

        $deleteTags = $pdo->prepare('DELETE FROM article_tags WHERE article_id = :article_id');
        $deleteTags->bindValue(':article_id', $articleId, PDO::PARAM_INT);
        $deleteTags->execute();

        if ($tagIds !== []) {
            $insertTag = $pdo->prepare('INSERT INTO article_tags (article_id, tag_id) VALUES (:article_id, :tag_id)');
            foreach ($tagIds as $tagId) {
                $insertTag->bindValue(':article_id', $articleId, PDO::PARAM_INT);
                $insertTag->bindValue(':tag_id', $tagId, PDO::PARAM_INT);
                $insertTag->execute();
            }
        }

        $pdo->commit();

        return $articleId;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function saveTimelineEvent(
    PDO $pdo,
    int $id,
    int $conflictId,
    ?int $locationId,
    string $title,
    string $slug,
    string $eventDate,
    string $summary,
    ?string $details,
    string $verificationStatus,
    ?int $primarySourceId
): void {
    if ($id > 0) {
        $stmt = $pdo->prepare('
            UPDATE timeline_events
            SET conflict_id = :conflict_id,
                location_id = :location_id,
                title = :title,
                slug = :slug,
                event_date = :event_date,
                summary = :summary,
                details = :details,
                verification_status = :verification_status,
                primary_source_id = :primary_source_id
            WHERE id = :id
        ');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO timeline_events (
                conflict_id,
                location_id,
                title,
                slug,
                event_date,
                summary,
                details,
                verification_status,
                primary_source_id
            ) VALUES (
                :conflict_id,
                :location_id,
                :title,
                :slug,
                :event_date,
                :summary,
                :details,
                :verification_status,
                :primary_source_id
            )
        ');
    }

    $stmt->bindValue(':conflict_id', $conflictId, PDO::PARAM_INT);

    if ($locationId !== null) {
        $stmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':location_id', null, PDO::PARAM_NULL);
    }

    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':slug', $slug);
    $stmt->bindValue(':event_date', $eventDate);
    $stmt->bindValue(':summary', $summary);
    $stmt->bindValue(':details', $details);
    $stmt->bindValue(':verification_status', $verificationStatus);

    if ($primarySourceId !== null) {
        $stmt->bindValue(':primary_source_id', $primarySourceId, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':primary_source_id', null, PDO::PARAM_NULL);
    }

    $stmt->execute();
}

function deleteById(PDO $pdo, string $table, int $id): void
{
    $allowed = ['categories', 'tags', 'articles', 'timeline_events'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Suppression non autorisee.');
    }

    $sql = sprintf('DELETE FROM %s WHERE id = :id', $table);
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

loadDotEnv(__DIR__ . '/.env');

$moduleLabels = [
    'dashboard' => 'Tableau de bord',
    'articles' => 'Articles',
    'categories' => 'Categories',
    'tags' => 'Tags',
    'timeline_events' => 'Chronologie',
];

$allowedModules = array_keys($moduleLabels);
$route = parseCurrentRoute($allowedModules);

$module = (string) ($route['module'] ?? 'dashboard');
$action = (string) ($route['action'] ?? ($module === 'dashboard' ? 'view' : 'list'));

$fatalError = null;
$pdo = null;

try {
    $pdo = getDbConnection();
} catch (Throwable $exception) {
    $fatalError = 'Connexion impossible a la base de donnees: ' . $exception->getMessage();
}

$errors = [];
$formState = [];

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
$pageContext = $module === 'dashboard'
    ? 'Tableau de bord'
    : (($actionLabels[$action] ?? 'Gestion') . ' ' . ($moduleLabels[$module] ?? 'Administration'));
$pageTitle = $pageContext . ' | Backoffice Guerre Irana';
$metaDescription = 'Backoffice pour la gestion des contenus editoriaux du projet Guerre Irana: articles, categories, tags et chronologie.';
$coverPreviewUrl = null;

if ($module === 'articles' && ($action === 'create' || $action === 'edit')) {
    $coverPreviewUrl = resolveCoverPreviewUrl(
        normalizeNullableText($formValues['cover_image_url'] ?? null),
        $frontofficeBaseUrl
    );
}

$token = csrfToken();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo e($metaDescription); ?>">
    <meta name="author" content="Guerre Irana">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#0f3d62">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <h1>Backoffice</h1>
            <p class="subtitle">Gestion des contenus</p>
            <nav class="menu">
                <?php foreach ($moduleLabels as $moduleKey => $label): ?>
                    <a class="menu-link <?php echo $module === $moduleKey ? 'active' : ''; ?>" href="<?php echo e(buildUrl($moduleKey)); ?>">
                        <?php echo e($label); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-foot">
                <a href="../frontoffice/package/modules.php">Voir le FrontOffice</a>
            </div>
        </aside>

        <main class="content">
            <header class="page-head">
                <h2><?php echo e($moduleLabels[$module] ?? 'Administration'); ?></h2>
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
                    <p>Cette version est orientee gestion de contenus editoriaux sans authentification.</p>
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
                <section class="panel">
                    <h3>Formulaire categorie</h3>
                    <form method="post" action="<?php echo e(buildUrl('categories', $action)); ?>" class="form-grid">
                        <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                        <input type="hidden" name="module" value="categories">
                        <input type="hidden" name="form_action" value="save">
                        <input type="hidden" name="return_action" value="<?php echo e($action); ?>">
                        <input type="hidden" name="id" value="<?php echo e((string) ($formValues['id'] ?? 0)); ?>">

                        <label>
                            Nom
                            <input type="text" name="name" required value="<?php echo e((string) ($formValues['name'] ?? '')); ?>">
                        </label>

                        <label>
                            Slug
                            <input type="text" name="slug" value="<?php echo e((string) ($formValues['slug'] ?? '')); ?>">
                        </label>

                        <label class="full">
                            Description
                            <textarea name="description" rows="4"><?php echo e((string) ($formValues['description'] ?? '')); ?></textarea>
                        </label>

                        <div class="full">
                            <button class="btn btn-primary" type="submit">Enregistrer</button>
                        </div>
                    </form>
                </section>
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
                <section class="panel">
                    <h3>Formulaire tag</h3>
                    <form method="post" action="<?php echo e(buildUrl('tags', $action)); ?>" class="form-grid">
                        <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                        <input type="hidden" name="module" value="tags">
                        <input type="hidden" name="form_action" value="save">
                        <input type="hidden" name="return_action" value="<?php echo e($action); ?>">
                        <input type="hidden" name="id" value="<?php echo e((string) ($formValues['id'] ?? 0)); ?>">

                        <label>
                            Nom
                            <input type="text" name="name" required value="<?php echo e((string) ($formValues['name'] ?? '')); ?>">
                        </label>

                        <label>
                            Slug
                            <input type="text" name="slug" value="<?php echo e((string) ($formValues['slug'] ?? '')); ?>">
                        </label>

                        <div class="full">
                            <button class="btn btn-primary" type="submit">Enregistrer</button>
                        </div>
                    </form>
                </section>
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
                <section class="panel">
                    <h3>Formulaire article</h3>
                    <form method="post" action="<?php echo e(buildUrl('articles', $action)); ?>" class="form-grid" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                        <input type="hidden" name="module" value="articles">
                        <input type="hidden" name="form_action" value="save">
                        <input type="hidden" name="return_action" value="<?php echo e($action); ?>">
                        <input type="hidden" name="id" value="<?php echo e((string) ($formValues['id'] ?? 0)); ?>">

                        <label>
                            Titre
                            <input type="text" name="title" required value="<?php echo e((string) ($formValues['title'] ?? '')); ?>">
                        </label>

                        <label>
                            Slug
                            <input type="text" name="slug" value="<?php echo e((string) ($formValues['slug'] ?? '')); ?>">
                        </label>

                        <label>
                            Conflit
                            <select name="conflict_id">
                                <option value="">Aucun</option>
                                <?php foreach ($conflictOptions as $option): ?>
                                    <option value="<?php echo e((string) $option['id']); ?>" <?php echo (string) ($formValues['conflict_id'] ?? '') === (string) $option['id'] ? 'selected' : ''; ?>>
                                        <?php echo e((string) $option['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Categorie
                            <select name="category_id" required>
                                <option value="">Choisir</option>
                                <?php foreach ($categoryOptions as $option): ?>
                                    <option value="<?php echo e((string) $option['id']); ?>" <?php echo (string) ($formValues['category_id'] ?? '') === (string) $option['id'] ? 'selected' : ''; ?>>
                                        <?php echo e((string) $option['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Auteur
                            <select name="author_id" required>
                                <option value="">Choisir</option>
                                <?php foreach ($userOptions as $option): ?>
                                    <option value="<?php echo e((string) $option['id']); ?>" <?php echo (string) ($formValues['author_id'] ?? '') === (string) $option['id'] ? 'selected' : ''; ?>>
                                        <?php echo e((string) $option['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Statut
                            <select name="status" required>
                                <?php foreach (['draft' => 'Brouillon', 'published' => 'Publie', 'archived' => 'Archive'] as $value => $label): ?>
                                    <option value="<?php echo e($value); ?>" <?php echo (string) ($formValues['status'] ?? 'draft') === $value ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Date de publication
                            <input type="datetime-local" name="published_at" value="<?php echo e((string) ($formValues['published_at'] ?? '')); ?>">
                        </label>

                        <label class="full">
                            Extrait
                            <textarea name="excerpt" rows="3" required><?php echo e((string) ($formValues['excerpt'] ?? '')); ?></textarea>
                        </label>

                        <label class="full">
                            Contenu
                            <textarea name="content" rows="12" required><?php echo e((string) ($formValues['content'] ?? '')); ?></textarea>
                        </label>

                        <label class="full">
                            URL image de couverture
                            <input type="text" name="cover_image_url" value="<?php echo e((string) ($formValues['cover_image_url'] ?? '')); ?>">
                        </label>

                        <label class="full">
                            Televerser une image de couverture (JPG, PNG, WEBP - 5 Mo max)
                            <input type="file" name="cover_image_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <small>Si un fichier est choisi, il remplacera l URL image de couverture.</small>
                        </label>

                        <label class="full">
                            Texte alt image de couverture
                            <input type="text" name="cover_image_alt" value="<?php echo e((string) ($formValues['cover_image_alt'] ?? '')); ?>">
                        </label>

                        <?php if ($coverPreviewUrl !== null): ?>
                            <div class="full">
                                <h4>Apercu image de couverture</h4>
                                <img
                                    class="cover-preview"
                                    src="<?php echo e($coverPreviewUrl); ?>"
                                    alt="<?php echo e((string) (($formValues['cover_image_alt'] ?? '') !== '' ? $formValues['cover_image_alt'] : (($formValues['title'] ?? '') !== '' ? $formValues['title'] : 'Image de couverture article'))); ?>"
                                    loading="lazy"
                                >
                            </div>
                        <?php endif; ?>

                        <label class="full">
                            Tags
                            <?php $selectedTagIds = asIntArray($formValues['tag_ids'] ?? []); ?>
                            <select name="tag_ids[]" multiple size="8">
                                <?php foreach ($tagOptions as $option): ?>
                                    <option value="<?php echo e((string) $option['id']); ?>" <?php echo in_array((int) $option['id'], $selectedTagIds, true) ? 'selected' : ''; ?>>
                                        <?php echo e((string) $option['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <div class="full">
                            <button class="btn btn-primary" type="submit">Enregistrer</button>
                        </div>
                    </form>
                </section>
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
                <section class="panel">
                    <h3>Formulaire evenement</h3>
                    <form method="post" action="<?php echo e(buildUrl('timeline_events', $action)); ?>" class="form-grid">
                        <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                        <input type="hidden" name="module" value="timeline_events">
                        <input type="hidden" name="form_action" value="save">
                        <input type="hidden" name="return_action" value="<?php echo e($action); ?>">
                        <input type="hidden" name="id" value="<?php echo e((string) ($formValues['id'] ?? 0)); ?>">

                        <label>
                            Titre
                            <input type="text" name="title" required value="<?php echo e((string) ($formValues['title'] ?? '')); ?>">
                        </label>

                        <label>
                            Slug
                            <input type="text" name="slug" value="<?php echo e((string) ($formValues['slug'] ?? '')); ?>">
                        </label>

                        <label>
                            Conflit
                            <select name="conflict_id" required>
                                <option value="">Choisir</option>
                                <?php foreach ($conflictOptions as $option): ?>
                                    <option value="<?php echo e((string) $option['id']); ?>" <?php echo (string) ($formValues['conflict_id'] ?? '') === (string) $option['id'] ? 'selected' : ''; ?>>
                                        <?php echo e((string) $option['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Date evenement
                            <input type="date" name="event_date" required value="<?php echo e((string) ($formValues['event_date'] ?? '')); ?>">
                        </label>

                        <label>
                            Lieu
                            <select name="location_id">
                                <option value="">Aucun</option>
                                <?php foreach ($locationOptions as $option): ?>
                                    <?php $label = (string) $option['name'] . ' (' . (string) $option['country'] . ')'; ?>
                                    <option value="<?php echo e((string) $option['id']); ?>" <?php echo (string) ($formValues['location_id'] ?? '') === (string) $option['id'] ? 'selected' : ''; ?>>
                                        <?php echo e($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Source primaire
                            <select name="primary_source_id">
                                <option value="">Aucune</option>
                                <?php foreach ($sourceOptions as $option): ?>
                                    <option value="<?php echo e((string) $option['id']); ?>" <?php echo (string) ($formValues['primary_source_id'] ?? '') === (string) $option['id'] ? 'selected' : ''; ?>>
                                        <?php echo e((string) $option['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Verification
                            <select name="verification_status" required>
                                <?php foreach (['under_review' => 'En verification', 'verified' => 'Verifie'] as $value => $label): ?>
                                    <option value="<?php echo e($value); ?>" <?php echo (string) ($formValues['verification_status'] ?? 'under_review') === $value ? 'selected' : ''; ?>>
                                        <?php echo e($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="full">
                            Resume
                            <textarea name="summary" rows="4" required><?php echo e((string) ($formValues['summary'] ?? '')); ?></textarea>
                        </label>

                        <label class="full">
                            Details
                            <textarea name="details" rows="8"><?php echo e((string) ($formValues['details'] ?? '')); ?></textarea>
                        </label>

                        <div class="full">
                            <button class="btn btn-primary" type="submit">Enregistrer</button>
                        </div>
                    </form>
                </section>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
