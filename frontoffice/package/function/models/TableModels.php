<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseSelectModel.php';

final class UsersModel extends BaseSelectModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'full_name', 'email', 'role', 'is_active', 'created_at', 'updated_at'];

    public function selectByEmail(string $email): ?array
    {
        return $this->selectOneWhere(['email' => $email]);
    }

    public function selectByRole(string $role, int $limit = 100, int $offset = 0): array
    {
        return $this->selectWhere(['role' => $role], $limit, $offset, 'created_at', 'DESC');
    }
}

final class ConflictsModel extends BaseSelectModel
{
    protected string $table = 'conflicts';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'title', 'slug', 'start_date', 'end_date', 'status', 'created_at', 'updated_at'];

    public function selectBySlug(string $slug): ?array
    {
        return $this->selectOneWhere(['slug' => $slug]);
    }

    public function selectByStatus(string $status, int $limit = 100, int $offset = 0): array
    {
        return $this->selectWhere(['status' => $status], $limit, $offset, 'start_date', 'DESC');
    }
}

final class CategoriesModel extends BaseSelectModel
{
    protected string $table = 'categories';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'name', 'slug', 'created_at', 'updated_at'];

    public function selectBySlug(string $slug): ?array
    {
        return $this->selectOneWhere(['slug' => $slug]);
    }
}

final class TagsModel extends BaseSelectModel
{
    protected string $table = 'tags';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'name', 'slug', 'created_at'];

    public function selectBySlug(string $slug): ?array
    {
        return $this->selectOneWhere(['slug' => $slug]);
    }
}

final class ArticlesModel extends BaseSelectModel
{
    protected string $table = 'articles';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'title', 'slug', 'category_id', 'author_id', 'status', 'published_at', 'created_at', 'updated_at'];

    public function selectBySlug(string $slug): ?array
    {
        return $this->selectOneWhere(['slug' => $slug]);
    }

    public function selectByStatus(string $status, int $limit = 100, int $offset = 0): array
    {
        return $this->selectWhere(['status' => $status], $limit, $offset, 'published_at', 'DESC');
    }

    public function selectByCategory(int $categoryId, int $limit = 100, int $offset = 0): array
    {
        return $this->selectWhere(['category_id' => $categoryId], $limit, $offset, 'published_at', 'DESC');
    }

    public function selectByAuthor(int $authorId, int $limit = 100, int $offset = 0): array
    {
        return $this->selectWhere(['author_id' => $authorId], $limit, $offset, 'published_at', 'DESC');
    }
}

final class ArticleTagsModel extends BaseSelectModel
{
    protected string $table = 'article_tags';
    protected string $primaryKey = 'article_id';
    protected array $allowedOrderBy = ['article_id', 'tag_id'];

    public function selectByArticleId(int $articleId): array
    {
        return $this->selectWhere(['article_id' => $articleId], 500, 0, 'tag_id', 'ASC');
    }

    public function selectByTagId(int $tagId): array
    {
        return $this->selectWhere(['tag_id' => $tagId], 500, 0, 'article_id', 'ASC');
    }
}

final class SourcesModel extends BaseSelectModel
{
    protected string $table = 'sources';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'title', 'publisher', 'source_type', 'language_code', 'published_at', 'reliability_score', 'created_at'];

    public function selectByPublisher(string $publisher, int $limit = 100, int $offset = 0): array
    {
        return $this->selectWhere(['publisher' => $publisher], $limit, $offset, 'published_at', 'DESC');
    }
}

final class ArticleSourcesModel extends BaseSelectModel
{
    protected string $table = 'article_sources';
    protected string $primaryKey = 'article_id';
    protected array $allowedOrderBy = ['article_id', 'source_id'];

    public function selectByArticleId(int $articleId): array
    {
        return $this->selectWhere(['article_id' => $articleId], 500, 0, 'source_id', 'ASC');
    }

    public function selectBySourceId(int $sourceId): array
    {
        return $this->selectWhere(['source_id' => $sourceId], 500, 0, 'article_id', 'ASC');
    }
}

final class ActorsModel extends BaseSelectModel
{
    protected string $table = 'actors';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'name', 'slug', 'actor_type', 'country', 'created_at'];

    public function selectBySlug(string $slug): ?array
    {
        return $this->selectOneWhere(['slug' => $slug]);
    }

    public function selectByType(string $type, int $limit = 100, int $offset = 0): array
    {
        return $this->selectWhere(['actor_type' => $type], $limit, $offset, 'name', 'ASC');
    }
}

final class ArticleActorsModel extends BaseSelectModel
{
    protected string $table = 'article_actors';
    protected string $primaryKey = 'article_id';
    protected array $allowedOrderBy = ['article_id', 'actor_id'];

    public function selectByArticleId(int $articleId): array
    {
        return $this->selectWhere(['article_id' => $articleId], 500, 0, 'actor_id', 'ASC');
    }

    public function selectByActorId(int $actorId): array
    {
        return $this->selectWhere(['actor_id' => $actorId], 500, 0, 'article_id', 'ASC');
    }
}

final class LocationsModel extends BaseSelectModel
{
    protected string $table = 'locations';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'name', 'country', 'created_at'];

    public function selectByCountry(string $country, int $limit = 100, int $offset = 0): array
    {
        return $this->selectWhere(['country' => $country], $limit, $offset, 'name', 'ASC');
    }
}

final class TimelineEventsModel extends BaseSelectModel
{
    protected string $table = 'timeline_events';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'conflict_id', 'location_id', 'title', 'slug', 'event_date', 'verification_status', 'primary_source_id', 'created_at', 'updated_at'];

    public function selectBySlug(string $slug): ?array
    {
        return $this->selectOneWhere(['slug' => $slug]);
    }

    public function selectByConflict(int $conflictId, int $limit = 100, int $offset = 0): array
    {
        return $this->selectWhere(['conflict_id' => $conflictId], $limit, $offset, 'event_date', 'DESC');
    }

    public function selectVerified(int $limit = 100, int $offset = 0): array
    {
        return $this->selectWhere(['verification_status' => 'verified'], $limit, $offset, 'event_date', 'DESC');
    }
}

final class EventActorsModel extends BaseSelectModel
{
    protected string $table = 'event_actors';
    protected string $primaryKey = 'event_id';
    protected array $allowedOrderBy = ['event_id', 'actor_id', 'role_in_event'];

    public function selectByEventId(int $eventId): array
    {
        return $this->selectWhere(['event_id' => $eventId], 500, 0, 'actor_id', 'ASC');
    }

    public function selectByActorId(int $actorId): array
    {
        return $this->selectWhere(['actor_id' => $actorId], 500, 0, 'event_id', 'ASC');
    }
}

final class MediaAssetsModel extends BaseSelectModel
{
    protected string $table = 'media_assets';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'article_id', 'created_at'];

    public function selectByArticleId(int $articleId, int $limit = 100, int $offset = 0): array
    {
        return $this->selectWhere(['article_id' => $articleId], $limit, $offset, 'id', 'ASC');
    }
}

final class SeoPagesModel extends BaseSelectModel
{
    protected string $table = 'seo_pages';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'page_type', 'page_ref_id', 'updated_at'];

    public function selectByPage(string $pageType, ?int $pageRefId = null): ?array
    {
        $filters = ['page_type' => $pageType];

        if ($pageRefId === null) {
            $filters['page_ref_id'] = null;
        } else {
            $filters['page_ref_id'] = $pageRefId;
        }

        return $this->selectOneWhere($filters);
    }
}

final class RedirectRulesModel extends BaseSelectModel
{
    protected string $table = 'redirect_rules';
    protected string $primaryKey = 'id';
    protected array $allowedOrderBy = ['id', 'old_path', 'new_path', 'http_code', 'is_active', 'created_at'];

    public function selectActiveByOldPath(string $oldPath): ?array
    {
        return $this->selectOneWhere(['old_path' => $oldPath, 'is_active' => 1]);
    }
}
