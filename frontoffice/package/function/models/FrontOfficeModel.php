<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

final class FrontOfficeModel
{
    private PDO $db;
    private const FRONT_CONFLICT_SLUG = 'guerre-iran-irak';
    private int $frontConflictId = -1;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getDbConnection();
    }

    public function getHomePageData(int $latestLimit = 6, int $featuredLimit = 3): array
    {
        $latestLimit = max(1, min($latestLimit, 30));
        $featuredLimit = max(1, min($featuredLimit, 30));
        $conflictId = $this->getFrontConflictId();

        if ($conflictId === null) {
            return [
                'latest_articles' => [],
                'featured_dossiers' => [],
            ];
        }

        $latestSql = '
            SELECT a.id, a.title, a.slug, a.excerpt, a.cover_image_url, a.cover_image_alt, a.published_at,
                   c.id AS category_id, c.name AS category_name, c.slug AS category_slug,
                   u.id AS author_id, u.full_name AS author_name
            FROM articles a
            INNER JOIN categories c ON c.id = a.category_id
            INNER JOIN users u ON u.id = a.author_id
            WHERE a.status = :status
              AND a.conflict_id = :conflict_id
            ORDER BY a.published_at DESC
            LIMIT :limit
        ';

        $latestStmt = $this->db->prepare($latestSql);
        $latestStmt->bindValue(':status', 'published');
        $latestStmt->bindValue(':conflict_id', $conflictId, PDO::PARAM_INT);
        $latestStmt->bindValue(':limit', $latestLimit, PDO::PARAM_INT);
        $latestStmt->execute();

        $featuredSql = '
            SELECT a.id, a.title, a.slug, a.excerpt, a.cover_image_url, a.cover_image_alt, a.published_at,
                   c.id AS category_id, c.name AS category_name, c.slug AS category_slug
            FROM articles a
            INNER JOIN categories c ON c.id = a.category_id
            WHERE a.status = :status
                            AND a.conflict_id = :conflict_id
              AND c.slug IN ("analyses", "chronologie", "diplomatie")
            ORDER BY a.published_at DESC
            LIMIT :limit
        ';

        $featuredStmt = $this->db->prepare($featuredSql);
        $featuredStmt->bindValue(':status', 'published');
                $featuredStmt->bindValue(':conflict_id', $conflictId, PDO::PARAM_INT);
        $featuredStmt->bindValue(':limit', $featuredLimit, PDO::PARAM_INT);
        $featuredStmt->execute();

        return [
            'latest_articles' => $latestStmt->fetchAll() ?: [],
            'featured_dossiers' => $featuredStmt->fetchAll() ?: [],
        ];
    }

    public function getArticlesPage(int $page = 1, int $perPage = 10): array
    {
        return $this->paginatePublishedArticles($page, $perPage);
    }

    public function getArticleDetailBySlug(string $slug): ?array
    {
        $sql = '
            SELECT a.id, a.title, a.slug, a.excerpt, a.content, a.cover_image_url, a.cover_image_alt,
                   a.published_at, a.created_at, a.updated_at,
                   c.id AS category_id, c.name AS category_name, c.slug AS category_slug,
                   u.id AS author_id, u.full_name AS author_name,
                   cf.id AS conflict_id, cf.title AS conflict_title, cf.slug AS conflict_slug
            FROM articles a
            INNER JOIN categories c ON c.id = a.category_id
            INNER JOIN users u ON u.id = a.author_id
            LEFT JOIN conflicts cf ON cf.id = a.conflict_id
            WHERE a.slug = :slug
              AND a.status = :status
                            AND a.conflict_id = :conflict_id
            LIMIT 1
        ';

                $conflictId = $this->getFrontConflictId();
                if ($conflictId === null) {
                        return null;
                }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        $stmt->bindValue(':status', 'published');
                $stmt->bindValue(':conflict_id', $conflictId, PDO::PARAM_INT);
        $stmt->execute();

        $article = $stmt->fetch();

        if (!$article) {
            return null;
        }

        $articleId = (int) $article['id'];
        $categoryId = (int) $article['category_id'];

        $article['tags'] = $this->getTagsForArticle($articleId);
        $article['sources'] = $this->getSourcesForArticle($articleId);
        $article['media_assets'] = $this->getMediaForArticle($articleId);
        $article['related_articles'] = $this->getRelatedArticles($categoryId, $articleId);

        return $article;
    }

    public function getCategoryPageBySlug(string $categorySlug, int $page = 1, int $perPage = 10): ?array
    {
        $categoryStmt = $this->db->prepare('SELECT id, name, slug, description FROM categories WHERE slug = :slug LIMIT 1');
        $categoryStmt->bindValue(':slug', $categorySlug);
        $categoryStmt->execute();

        $category = $categoryStmt->fetch();
        if (!$category) {
            return null;
        }

        $data = $this->paginatePublishedArticles($page, $perPage, (int) $category['id']);

        return [
            'category' => $category,
            'articles' => $data['items'],
            'pagination' => $data['pagination'],
        ];
    }

    public function getTagPageBySlug(string $tagSlug, int $page = 1, int $perPage = 10): ?array
    {
        $tagStmt = $this->db->prepare('SELECT id, name, slug FROM tags WHERE slug = :slug LIMIT 1');
        $tagStmt->bindValue(':slug', $tagSlug);
        $tagStmt->execute();

        $tag = $tagStmt->fetch();
        if (!$tag) {
            return null;
        }

        $data = $this->paginatePublishedArticles($page, $perPage, null, (int) $tag['id']);

        return [
            'tag' => $tag,
            'articles' => $data['items'],
            'pagination' => $data['pagination'],
        ];
    }

    public function searchArticles(string $term, int $page = 1, int $perPage = 10): array
    {
        $cleanTerm = trim($term);

        if ($cleanTerm == '') {
            return [
                'query' => '',
                'articles' => [],
                'pagination' => [
                    'page' => 1,
                    'per_page' => $perPage,
                    'total_items' => 0,
                    'total_pages' => 0,
                ],
            ];
        }

        $data = $this->paginatePublishedArticles($page, $perPage, null, null, $cleanTerm);

        return [
            'query' => $cleanTerm,
            'articles' => $data['items'],
            'pagination' => $data['pagination'],
        ];
    }

    public function getBreadcrumbForArticle(string $articleSlug): array
    {
        $sql = '
            SELECT a.title, a.slug, c.name AS category_name, c.slug AS category_slug
            FROM articles a
            INNER JOIN categories c ON c.id = a.category_id
            WHERE a.slug = :slug
              AND a.status = :status
              AND a.conflict_id = :conflict_id
            LIMIT 1
        ';

        $conflictId = $this->getFrontConflictId();
        if ($conflictId === null) {
            return [
                ['label' => 'Accueil', 'url' => '/'],
                ['label' => 'Actualites', 'url' => '/actualites'],
            ];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':slug', $articleSlug);
        $stmt->bindValue(':status', 'published');
        $stmt->bindValue(':conflict_id', $conflictId, PDO::PARAM_INT);
        $stmt->execute();

        $article = $stmt->fetch();

        if (!$article) {
            return [
                ['label' => 'Accueil', 'url' => '/'],
                ['label' => 'Actualites', 'url' => '/actualites'],
            ];
        }

        return [
            ['label' => 'Accueil', 'url' => '/'],
            ['label' => 'Actualites', 'url' => '/actualites'],
            ['label' => $article['category_name'], 'url' => '/categorie/' . $article['category_slug']],
            ['label' => $article['title'], 'url' => '/article/' . $article['slug']],
        ];
    }

    public function getStaticPageSeo(string $pageSlug): ?array
    {
        $slug = trim($pageSlug, '/');

        if ($slug === '' || $slug === 'home') {
            $stmt = $this->db->prepare('SELECT * FROM seo_pages WHERE page_type = :page_type AND page_ref_id IS NULL LIMIT 1');
            $stmt->bindValue(':page_type', 'home');
            $stmt->execute();
            return $stmt->fetch() ?: null;
        }

        $stmt = $this->db->prepare('SELECT * FROM seo_pages WHERE page_type = :page_type AND canonical_url = :canonical_url LIMIT 1');
        $stmt->bindValue(':page_type', 'static');
        $stmt->bindValue(':canonical_url', '/' . $slug);
        $stmt->execute();

        return $stmt->fetch() ?: null;
    }

    public function getRedirectForPath(string $oldPath): ?array
    {
        $stmt = $this->db->prepare('
            SELECT old_path, new_path, http_code
            FROM redirect_rules
            WHERE old_path = :old_path
              AND is_active = 1
            LIMIT 1
        ');
        $stmt->bindValue(':old_path', $oldPath);
        $stmt->execute();

        return $stmt->fetch() ?: null;
    }

    public function getErrorPageMeta(int $statusCode): array
    {
        if ($statusCode === 500) {
            return [
                'title' => 'Erreur serveur',
                'message' => 'Une erreur interne est survenue.',
            ];
        }

        return [
            'title' => 'Page non trouvee',
            'message' => 'La page demandee est introuvable.',
        ];
    }

    private function paginatePublishedArticles(
        int $page,
        int $perPage,
        ?int $categoryId = null,
        ?int $tagId = null,
        ?string $searchTerm = null
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min($perPage, 100));
        $offset = ($page - 1) * $perPage;

        $joins = [
            'INNER JOIN categories c ON c.id = a.category_id',
            'INNER JOIN users u ON u.id = a.author_id',
        ];

        if ($tagId !== null) {
            $joins[] = 'INNER JOIN article_tags at ON at.article_id = a.id';
        }

        $conflictId = $this->getFrontConflictId();
        if ($conflictId === null) {
            return [
                'items' => [],
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_items' => 0,
                    'total_pages' => 0,
                ],
            ];
        }

        $conditions = ['a.status = :status'];
        $params = [
            ':status' => 'published',
            ':conflict_id' => $conflictId,
        ];
        $conditions[] = 'a.conflict_id = :conflict_id';

        if ($categoryId !== null) {
            $conditions[] = 'a.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        if ($tagId !== null) {
            $conditions[] = 'at.tag_id = :tag_id';
            $params[':tag_id'] = $tagId;
        }

        if ($searchTerm !== null && trim($searchTerm) !== '') {
            $conditions[] = '(a.title LIKE :search OR a.excerpt LIKE :search OR a.content LIKE :search)';
            $params[':search'] = '%' . trim($searchTerm) . '%';
        }

        $whereSql = ' WHERE ' . implode(' AND ', $conditions);
        $joinSql = ' ' . implode(' ', $joins);

        $countSql = 'SELECT COUNT(*) AS total_items FROM articles a' . $joinSql . $whereSql;
        $countStmt = $this->db->prepare($countSql);
        $this->bindParams($countStmt, $params);
        $countStmt->execute();
        $totalItems = (int) ($countStmt->fetch()['total_items'] ?? 0);
        $totalPages = (int) ceil($totalItems / $perPage);

        $listSql = '
            SELECT a.id, a.title, a.slug, a.excerpt, a.cover_image_url, a.cover_image_alt, a.published_at,
                   c.id AS category_id, c.name AS category_name, c.slug AS category_slug,
                   u.id AS author_id, u.full_name AS author_name
            FROM articles a' . $joinSql . $whereSql . '
            ORDER BY a.published_at DESC
            LIMIT :limit OFFSET :offset
        ';

        $listStmt = $this->db->prepare($listSql);
        $this->bindParams($listStmt, $params);
        $listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $listStmt->execute();

        return [
            'items' => $listStmt->fetchAll() ?: [],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
            ],
        ];
    }

    private function getTagsForArticle(int $articleId): array
    {
        $stmt = $this->db->prepare('
            SELECT t.id, t.name, t.slug
            FROM tags t
            INNER JOIN article_tags at ON at.tag_id = t.id
            WHERE at.article_id = :article_id
            ORDER BY t.name ASC
        ');
        $stmt->bindValue(':article_id', $articleId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    private function getSourcesForArticle(int $articleId): array
    {
        $stmt = $this->db->prepare('
            SELECT s.id, s.title, s.publisher, s.source_url, s.source_type, s.published_at, ars.note
            FROM sources s
            INNER JOIN article_sources ars ON ars.source_id = s.id
            WHERE ars.article_id = :article_id
            ORDER BY s.published_at DESC
        ');
        $stmt->bindValue(':article_id', $articleId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    private function getMediaForArticle(int $articleId): array
    {
        $stmt = $this->db->prepare('
            SELECT id, file_path, title, alt_text, caption, credit, mime_type, width, height
            FROM media_assets
            WHERE article_id = :article_id
            ORDER BY id ASC
        ');
        $stmt->bindValue(':article_id', $articleId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    private function getRelatedArticles(int $categoryId, int $excludedArticleId, int $limit = 3): array
    {
        $limit = max(1, min($limit, 12));
        $conflictId = $this->getFrontConflictId();

        if ($conflictId === null) {
            return [];
        }

        $stmt = $this->db->prepare('
            SELECT id, title, slug, excerpt, cover_image_url, cover_image_alt, published_at
            FROM articles
            WHERE status = :status
              AND conflict_id = :conflict_id
              AND category_id = :category_id
              AND id <> :excluded_id
            ORDER BY published_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':status', 'published');
        $stmt->bindValue(':conflict_id', $conflictId, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':excluded_id', $excludedArticleId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    private function getFrontConflictId(): ?int
    {
        if ($this->frontConflictId !== -1) {
            return $this->frontConflictId > 0 ? $this->frontConflictId : null;
        }

        $stmt = $this->db->prepare('SELECT id FROM conflicts WHERE slug = :slug LIMIT 1');
        $stmt->bindValue(':slug', self::FRONT_CONFLICT_SLUG);
        $stmt->execute();

        $row = $stmt->fetch();
        $this->frontConflictId = $row ? (int) $row['id'] : 0;

        return $this->frontConflictId > 0 ? $this->frontConflictId : null;
    }

    private function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $name => $value) {
            if (is_int($value)) {
                $stmt->bindValue($name, $value, PDO::PARAM_INT);
                continue;
            }

            $stmt->bindValue($name, (string) $value, PDO::PARAM_STR);
        }
    }
}
