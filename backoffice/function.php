<?php

declare(strict_types=1);

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

function backofficeAuthUsername(): string
{
    return envValue('BACKOFFICE_AUTH_USER', 'user');
}

function backofficeAuthPassword(): string
{
    return envValue('BACKOFFICE_AUTH_PASS', 'pass');
}

function isBackofficeAuthenticated(): bool
{
    return isset($_SESSION['backoffice_authenticated']) && $_SESSION['backoffice_authenticated'] === true;
}

function attemptBackofficeLogin(string $username, string $password): bool
{
    $expectedUsername = backofficeAuthUsername();
    $expectedPassword = backofficeAuthPassword();

    $isValid = hash_equals($expectedUsername, $username) && hash_equals($expectedPassword, $password);

    if (!$isValid) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['backoffice_authenticated'] = true;
    $_SESSION['backoffice_username'] = $expectedUsername;

    return true;
}

function logoutBackoffice(): void
{
    unset($_SESSION['backoffice_authenticated'], $_SESSION['backoffice_username']);
    session_regenerate_id(true);
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
    $uploadsDir = null;
    
    if (is_dir('/var/www/html/package/assets/images/uploads')) {
        $uploadsDir = '/var/www/html/package/assets/images/uploads';
    } elseif (is_dir('/var/www/frontoffice/package/assets/images/uploads')) {
        $uploadsDir = '/var/www/frontoffice/package/assets/images/uploads';
    } elseif (is_dir('/var/www/html/package/assets/images')) {
        $uploadsDir = '/var/www/html/package/assets/images/uploads';
    } elseif (is_dir('/var/www/frontoffice/package/assets')) {
        $uploadsDir = '/var/www/frontoffice/package/assets/images/uploads';
    } else {
        $uploadsDir = '/var/www/html/package/assets/images/uploads';
    }
    
    return $uploadsDir;
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
        $result['error'] = 'Aucun fichier envoye';
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
        $result['error'] = 'Le fichier transmis est invalide. tmp_name=' . $tmpName;

        return $result;
    }

    if ($size <= 0 || $size > (5 * 1024 * 1024)) {
        $result['error'] = 'Le fichier doit faire moins de 5 Mo. size=' . $size;

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
        $result['error'] = 'Format non autorise. Utilise JPG, PNG ou WEBP. mime=' . $mimeType;

        return $result;
    }

    $directory = articleUploadDirectory();
    
    if (!is_dir($directory)) {
        $parentDir = dirname($directory);
        if (!is_dir($parentDir)) {
            @mkdir($parentDir, 0777, true);
        }
        @mkdir($directory, 0777, true);
        @chmod($directory, 0777);
    }
    
    if (!is_dir($directory)) {
        $result['error'] = 'Le dossier de televersement n existe pas et n a pas pu etre cree: ' . $directory;
        return $result;
    }
    
    if (!is_writable($directory)) {
        @chmod($directory, 0777);
        if (!is_writable($directory)) {
            $result['error'] = 'Le dossier de televersement n est pas accessible en ecriture: ' . $directory;
            return $result;
        }
    }

    $fileName = slugify($slug) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimeToExt[$mimeType];
    $destination = $directory . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        $result['error'] = 'Echec lors de l enregistrement du fichier. dest=' . $destination;

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

        if (in_array($module, ['login', 'logout'], true)) {
            return [
                'module' => $module,
                'action' => 'view',
                'id' => 0,
            ];
        }

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

    if (in_array($module, ['login', 'logout'], true)) {
        return ['module' => $module, 'action' => 'view', 'id' => 0];
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

