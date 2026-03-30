<?php

declare(strict_types=1);

require_once __DIR__ . '/function/models/index.php';

function e(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatDate(?string $date): string
{
	if ($date === null || $date === '') {
		return '';
	}

	$timestamp = strtotime($date);
	if ($timestamp === false) {
		return $date;
	}

	return date('d/m/Y H:i', $timestamp);
}

function buildNewsUrl(int $page): string
{
	if ($page <= 1) {
		return '/actualites.html';
	}

	return '/actualites-' . $page . '.html';
}

function buildArticleUrl(string $slug): string
{
	$slug = trim($slug);
	if ($slug === '') {
		return '/actualites.html';
	}

	return '/article/' . rawurlencode($slug) . '.html';
}

function stripHtmlForMeta(string $value): string
{
	$text = html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8');
	$text = preg_replace('/\s+/u', ' ', $text);

	return trim((string) $text);
}

function truncateMeta(string $value, int $maxLength = 160): string
{
	$clean = stripHtmlForMeta($value);
	if ($clean === '') {
		return '';
	}

	if (function_exists('mb_strlen') && function_exists('mb_substr')) {
		if (mb_strlen($clean) <= $maxLength) {
			return $clean;
		}

		return rtrim(mb_substr($clean, 0, $maxLength - 1)) . '…';
	}

	if (strlen($clean) <= $maxLength) {
		return $clean;
	}

	return rtrim(substr($clean, 0, $maxLength - 1)) . '...';
}

function buildAbsoluteUrl(string $path): string
{
	if (preg_match('#^https?://#i', $path) === 1) {
		return $path;
	}

	$https = $_SERVER['HTTPS'] ?? '';
	$scheme = (!empty($https) && strtolower((string) $https) !== 'off') ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';

	return $scheme . '://' . $host . '/' . ltrim($path, '/');
}

function getPreferredImageSource(array $article): string
{
	$coverUrl = trim((string) ($article['cover_image_url'] ?? ''));
	$slug = (string) ($article['slug'] ?? '');

	$localBySlug = [
		'chronologie-debut-guerre-iran-irak' => '/package/assets/images/real/chronologie-khorramshahr.jpg',
		'analyse-impact-humain-guerre-iran-irak' => '/package/assets/images/real/impact-humain-defapress.jpg',
		'diplomatie-regionale-discussions-recentes' => '/package/assets/images/real/diplomatie-iran-turquie.jpg',
		'actualites-nouvelles-sanctions-energie-iran' => '/package/assets/images/real/diplomatie-iran-turquie.jpg',
		'humanitaire-aide-medicale-zones-frontalieres' => '/package/assets/images/real/impact-humain-defapress.jpg',
		'diplomatie-reprise-negociations-indirectes-oman' => '/package/assets/images/real/diplomatie-iran-turquie.jpg',
		'analyse-cybersecurite-infrastructures-critiques-iran' => '/package/assets/images/real/diplomatie-iran-turquie.jpg',
		'actualites-tensions-maritimes-golfe' => '/package/assets/images/real/chronologie-khorramshahr.jpg',
	];

	if (isset($localBySlug[$slug])) {
		return $localBySlug[$slug];
	}

	if ($coverUrl !== '') {
		return $coverUrl;
	}

	return '/package/assets/images/real/impact-humain-defapress.jpg';
}

function buildLocalVariantImageUrl(string $sourceUrl, int $targetWidth): ?string
{
	$path = parse_url($sourceUrl, PHP_URL_PATH);
	if (!is_string($path) || $path === '') {
		$path = $sourceUrl;
	}

	if ($path === '') {
		return null;
	}

	$normalizedPath = '/' . ltrim($path, '/');
	$publicPrefix = '/package/assets/images/real/';

	if (strpos($normalizedPath, $publicPrefix) !== 0) {
		return null;
	}

	$fileName = basename($normalizedPath);
	if (!preg_match('/^(.+?)(?:-(320|520|760))?\\.jpg$/', $fileName, $matches)) {
		return $normalizedPath;
	}

	$baseName = $matches[1];
	$availableSizes = [320, 520, 760];
	$selectedSize = 760;

	foreach ($availableSizes as $size) {
		if ($targetWidth <= $size) {
			$selectedSize = $size;
			break;
		}
	}

	$variantPublicPath = $publicPrefix . $baseName . '-' . $selectedSize . '.jpg';
	$variantFilePath = __DIR__ . '/assets/images/real/' . $baseName . '-' . $selectedSize . '.jpg';
	if (is_file($variantFilePath)) {
		return $variantPublicPath;
	}

	$originalPublicPath = $publicPrefix . $baseName . '.jpg';
	$originalFilePath = __DIR__ . '/assets/images/real/' . $baseName . '.jpg';
	if (is_file($originalFilePath)) {
		return $originalPublicPath;
	}

	return $normalizedPath;
}

function buildOptimizedImageUrl(string $sourceUrl, int $targetWidth = 760): string
{
	$sourceUrl = trim($sourceUrl);
	if ($sourceUrl === '') {
		return '';
	}

	$targetWidth = max(120, min($targetWidth, 1600));
	$localVariant = buildLocalVariantImageUrl($sourceUrl, $targetWidth);
	if ($localVariant !== null) {
		return $localVariant;
	}

	$wikiPrefix = 'https://upload.wikimedia.org/wikipedia/commons/';

	if (strpos($sourceUrl, $wikiPrefix) !== 0 || strpos($sourceUrl, '/thumb/') !== false) {
		return $sourceUrl;
	}

	$path = parse_url($sourceUrl, PHP_URL_PATH);
	if (!is_string($path) || $path === '') {
		return $sourceUrl;
	}

	$relativePath = ltrim($path, '/');
	$commonsPrefix = 'wikipedia/commons/';
	if (strpos($relativePath, $commonsPrefix) !== 0) {
		return $sourceUrl;
	}

	$originalPath = substr($relativePath, strlen($commonsPrefix));
	$segments = explode('/', $originalPath);
	$fileName = rawurldecode((string) end($segments));
	$directory = implode('/', array_slice($segments, 0, -1));

	if ($fileName === '' || $directory === '') {
		return $sourceUrl;
	}

	$fileNameEncoded = rawurlencode($fileName);

	return 'https://upload.wikimedia.org/wikipedia/commons/thumb/'
		. $directory
		. '/'
		. $fileNameEncoded
		. '/'
		. $targetWidth
		. 'px-'
		. $fileNameEncoded;
}

$scriptPath = $_SERVER['PHP_SELF'] ?? '/package/modules.php';
$pageParam = isset($_GET['page']) ? (string) $_GET['page'] : 'home';
$articleSlug = trim((string) ($_GET['slug'] ?? ''));
$view = 'home';

if ($pageParam === 'actualites') {
	$view = 'actualites';
} elseif ($pageParam === 'article') {
	$view = 'article';
}

$currentPage = max(1, (int) ($_GET['p'] ?? 1));

$homeData = ['latest_articles' => [], 'featured_dossiers' => []];
$newsData = [
	'items' => [],
	'pagination' => ['page' => 1, 'per_page' => 6, 'total_items' => 0, 'total_pages' => 0],
];
$articleData = null;
$errorMessage = '';

try {
	$frontOfficeModel = new FrontOfficeModel();

	if ($view === 'home') {
		$homeData = $frontOfficeModel->getHomePageData(6, 3);
	} elseif ($view === 'actualites') {
		$newsData = $frontOfficeModel->getArticlesPage($currentPage, 6);
	} else {
		$articleData = $frontOfficeModel->getArticleDetailBySlug($articleSlug);
	}
} catch (Throwable $exception) {
	$errorMessage = 'Erreur de connexion ou de lecture des donnees: ' . $exception->getMessage();
}

$homeUrl = '/';
$newsUrl = buildNewsUrl(1);
$styleUrl = '/package/assets/css/style.css';

$pageTitle = 'Iran Info - Guerre Iran-Irak';
$metaDescription = 'Informations verifiees sur la guerre Iran-Irak: actualites, analyses, chronologie et impacts humanitaires.';
$canonicalPath = '/';
$ogType = 'website';
$ogImageUrl = '/package/assets/images/real/impact-humain-defapress-320.jpg';

if ($view === 'actualites') {
	$pageTitle = $currentPage > 1
		? 'Actualites guerre Iran-Irak - Page ' . $currentPage . ' | Iran Info'
		: 'Actualites guerre Iran-Irak | Iran Info';
	$metaDescription = 'Suivez les actualites recentes de la guerre Iran-Irak: evolutions diplomatiques, chronologie et analyse des impacts.';
	$canonicalPath = buildNewsUrl($currentPage);
	$ogImageUrl = '/package/assets/images/real/chronologie-khorramshahr-320.jpg';
} elseif ($view === 'article') {
	if ($articleData !== null) {
		$pageTitle = (string) $articleData['title'] . ' | Iran Info';
		$descriptionSource = (string) ($articleData['excerpt'] ?? '');
		if ($descriptionSource === '') {
			$descriptionSource = (string) ($articleData['content'] ?? '');
		}
		$metaDescription = truncateMeta($descriptionSource, 155);
		$canonicalPath = buildArticleUrl((string) ($articleData['slug'] ?? ''));
		$ogType = 'article';
		$ogImageUrl = buildOptimizedImageUrl(getPreferredImageSource($articleData), 520);
	} else {
		http_response_code(404);
		$pageTitle = 'Article introuvable | Iran Info';
		$metaDescription = 'L article demande est introuvable. Consultez les actualites recentes sur la guerre Iran-Irak.';
		$canonicalPath = '/actualites.html';
		$ogImageUrl = '/package/assets/images/real/chronologie-khorramshahr-320.jpg';
	}
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo e($pageTitle); ?></title>
	<meta name="description" content="<?php echo e($metaDescription); ?>">
	<meta name="robots" content="index,follow">
	<link rel="canonical" href="<?php echo e(buildAbsoluteUrl($canonicalPath)); ?>">
	<meta property="og:type" content="<?php echo e($ogType); ?>">
	<meta property="og:title" content="<?php echo e($pageTitle); ?>">
	<meta property="og:description" content="<?php echo e($metaDescription); ?>">
	<meta property="og:url" content="<?php echo e(buildAbsoluteUrl($canonicalPath)); ?>">
	<meta property="og:image" content="<?php echo e(buildAbsoluteUrl($ogImageUrl)); ?>">
	<meta name="twitter:card" content="summary_large_image">
	<link rel="stylesheet" href="<?php echo e($styleUrl); ?>">
</head>
<body>
	<header>
		<div class="wrap topbar">
			<p class="brand"><a href="<?php echo e($homeUrl); ?>">Iran Info</a></p>
			<nav>
				<a class="nav-link <?php echo $view === 'home' ? 'active' : ''; ?>" href="<?php echo e($homeUrl); ?>">Accueil</a>
				<a class="nav-link <?php echo ($view === 'actualites' || $view === 'article') ? 'active' : ''; ?>" href="<?php echo e($newsUrl); ?>">Actualites</a>
			</nav>
		</div>
	</header>

	<main class="wrap">
		<?php if ($errorMessage !== ''): ?>
			<div class="notice"><?php echo e($errorMessage); ?></div>
		<?php endif; ?>

		<?php if ($view === 'home'): ?>
			<section class="hero">
				<h1>Guerre Iran-Irak: dernieres actualites et dossiers</h1>
				<p>FrontOffice d information sur le conflit Iran-Irak: suivi editorial, chronologie, analyses et impacts humanitaires.</p>
			</section>

			<section>
				<h2 class="section-title">Dernieres actualites</h2>
				<div class="grid">
					<?php foreach ($homeData['latest_articles'] as $article): ?>
						<article class="card">
							<?php $coverUrl = getPreferredImageSource($article); ?>
							<?php $coverAlt = (string) ($article['cover_image_alt'] ?? $article['title'] ?? 'Image article'); ?>
							<?php if ($coverUrl !== ''): ?>
								<?php $coverHomeUrl = buildOptimizedImageUrl($coverUrl, 320); ?>
								<img
									class="card-image"
									src="<?php echo e($coverHomeUrl); ?>"
									srcset="<?php echo e($coverHomeUrl); ?> 320w"
									sizes="320px"
									width="320"
									height="180"
									alt="<?php echo e($coverAlt); ?>"
									loading="lazy"
									decoding="async"
								>
							<?php endif; ?>
							<h3><a class="card-title-link" href="<?php echo e(buildArticleUrl((string) $article['slug'])); ?>"><?php echo e((string) $article['title']); ?></a></h3>
							<div class="meta">
								<?php echo e((string) $article['category_name']); ?> | <?php echo e((string) $article['author_name']); ?> | <?php echo e(formatDate((string) $article['published_at'])); ?>
							</div>
							<p class="excerpt"><?php echo e((string) $article['excerpt']); ?></p>
							<a class="card-read" href="<?php echo e(buildArticleUrl((string) $article['slug'])); ?>">Lire l article</a>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<section>
				<h2 class="section-title">Dossiers mis en avant</h2>
				<div class="grid">
					<?php foreach ($homeData['featured_dossiers'] as $article): ?>
						<article class="card">
							<?php $coverUrl = getPreferredImageSource($article); ?>
							<?php $coverAlt = (string) ($article['cover_image_alt'] ?? $article['title'] ?? 'Image article'); ?>
							<?php if ($coverUrl !== ''): ?>
								<?php $coverHomeUrl = buildOptimizedImageUrl($coverUrl, 320); ?>
								<img
									class="card-image"
									src="<?php echo e($coverHomeUrl); ?>"
									srcset="<?php echo e($coverHomeUrl); ?> 320w"
									sizes="320px"
									width="320"
									height="180"
									alt="<?php echo e($coverAlt); ?>"
									loading="lazy"
									decoding="async"
								>
							<?php endif; ?>
							<h3><a class="card-title-link" href="<?php echo e(buildArticleUrl((string) $article['slug'])); ?>"><?php echo e((string) $article['title']); ?></a></h3>
							<div class="meta">
								<?php echo e((string) $article['category_name']); ?> | <?php echo e(formatDate((string) $article['published_at'])); ?>
							</div>
							<p class="excerpt"><?php echo e((string) $article['excerpt']); ?></p>
							<a class="card-read" href="<?php echo e(buildArticleUrl((string) $article['slug'])); ?>">Lire l article</a>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
		<?php elseif ($view === 'actualites'): ?>
			<section class="hero">
				<h1>Actualites sur la guerre Iran-Irak</h1>
				<p>Liste des articles publies avec pagination.</p>
			</section>

			<section>
				<div class="grid">
					<?php foreach ($newsData['items'] as $article): ?>
						<article class="card">
							<?php $coverUrl = getPreferredImageSource($article); ?>
							<?php $coverAlt = (string) ($article['cover_image_alt'] ?? $article['title'] ?? 'Image article'); ?>
							<?php if ($coverUrl !== ''): ?>
								<?php $coverSmallUrl = buildOptimizedImageUrl($coverUrl, 320); ?>
								<?php $coverMediumUrl = buildOptimizedImageUrl($coverUrl, 520); ?>
								<?php $coverLargeUrl = buildOptimizedImageUrl($coverUrl, 760); ?>
								<img
									class="card-image"
									src="<?php echo e($coverMediumUrl); ?>"
									srcset="<?php echo e($coverSmallUrl); ?> 320w, <?php echo e($coverMediumUrl); ?> 520w, <?php echo e($coverLargeUrl); ?> 760w"
									sizes="(max-width: 768px) 92vw, (max-width: 1200px) 42vw, 320px"
									width="520"
									height="293"
									alt="<?php echo e($coverAlt); ?>"
									loading="lazy"
									decoding="async"
								>
							<?php endif; ?>
							<h2><a class="card-title-link" href="<?php echo e(buildArticleUrl((string) $article['slug'])); ?>"><?php echo e((string) $article['title']); ?></a></h2>
							<div class="meta">
								<?php echo e((string) $article['category_name']); ?> | <?php echo e((string) $article['author_name']); ?> | <?php echo e(formatDate((string) $article['published_at'])); ?>
							</div>
							<p class="excerpt"><?php echo e((string) $article['excerpt']); ?></p>
							<a class="card-read" href="<?php echo e(buildArticleUrl((string) $article['slug'])); ?>">Lire l article</a>
						</article>
					<?php endforeach; ?>
				</div>

				<?php
				$totalPages = (int) ($newsData['pagination']['total_pages'] ?? 0);
				$activePage = (int) ($newsData['pagination']['page'] ?? 1);
				?>

				<?php if ($totalPages > 1): ?>
					<nav class="pagination" aria-label="Pagination">
						<?php for ($i = 1; $i <= $totalPages; $i++): ?>
							<?php if ($i === $activePage): ?>
								<span><?php echo $i; ?></span>
							<?php else: ?>
								<a href="<?php echo e(buildNewsUrl($i)); ?>"><?php echo $i; ?></a>
							<?php endif; ?>
						<?php endfor; ?>
					</nav>
				<?php endif; ?>
			</section>
		<?php else: ?>
			<?php if ($articleData === null): ?>
				<section class="hero">
					<h1>Article introuvable</h1>
					<p>L article demande est indisponible. Consultez la <a href="<?php echo e($newsUrl); ?>">liste des actualites</a>.</p>
				</section>
			<?php else: ?>
				<?php $articleCoverUrl = getPreferredImageSource($articleData); ?>
				<?php $articleCoverAlt = (string) ($articleData['cover_image_alt'] ?? $articleData['title'] ?? 'Image article'); ?>
				<?php $articleContent = (string) ($articleData['content'] ?? ''); ?>
				<?php $articleContent = preg_replace('/<\/?h1\b[^>]*>/i', '', $articleContent) ?? $articleContent; ?>

				<article class="article-page">
					<header class="hero article-hero">
						<h1><?php echo e((string) $articleData['title']); ?></h1>
						<p class="meta">
							<?php echo e((string) ($articleData['category_name'] ?? 'Actualites')); ?>
							|
							<?php echo e((string) ($articleData['author_name'] ?? 'Redaction')); ?>
							|
							<?php echo e(formatDate((string) ($articleData['published_at'] ?? ''))); ?>
						</p>
						<p><?php echo e((string) ($articleData['excerpt'] ?? '')); ?></p>
					</header>

					<?php if ($articleCoverUrl !== ''): ?>
						<?php $articleImageSmall = buildOptimizedImageUrl($articleCoverUrl, 320); ?>
						<?php $articleImageMedium = buildOptimizedImageUrl($articleCoverUrl, 520); ?>
						<?php $articleImageLarge = buildOptimizedImageUrl($articleCoverUrl, 760); ?>
						<img
							class="card-image article-main-image"
							src="<?php echo e($articleImageLarge); ?>"
							srcset="<?php echo e($articleImageSmall); ?> 320w, <?php echo e($articleImageMedium); ?> 520w, <?php echo e($articleImageLarge); ?> 760w"
							sizes="(max-width: 768px) 94vw, 760px"
							width="760"
							height="428"
							alt="<?php echo e($articleCoverAlt); ?>"
							loading="eager"
							decoding="async"
						>
					<?php endif; ?>

					<section class="article-content">
						<?php echo $articleContent; ?>
					</section>

					<?php if (!empty($articleData['related_articles'])): ?>
						<section>
							<h2 class="section-title">Articles lies</h2>
							<div class="grid">
								<?php foreach ((array) $articleData['related_articles'] as $related): ?>
									<article class="card">
										<?php $relatedCover = getPreferredImageSource($related); ?>
										<?php $relatedAlt = (string) ($related['cover_image_alt'] ?? $related['title'] ?? 'Image article'); ?>
										<?php if ($relatedCover !== ''): ?>
											<?php $relatedImg = buildOptimizedImageUrl($relatedCover, 320); ?>
											<img class="card-image" src="<?php echo e($relatedImg); ?>" width="320" height="180" alt="<?php echo e($relatedAlt); ?>" loading="lazy" decoding="async">
										<?php endif; ?>
										<h3><a class="card-title-link" href="<?php echo e(buildArticleUrl((string) ($related['slug'] ?? ''))); ?>"><?php echo e((string) ($related['title'] ?? 'Article')); ?></a></h3>
										<p class="excerpt"><?php echo e((string) ($related['excerpt'] ?? '')); ?></p>
									</article>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endif; ?>
				</article>
			<?php endif; ?>
		<?php endif; ?>
	</main>

	<footer>
		<div class="wrap">Projet FrontOffice - Etape 1 (Accueil + Liste)</div>
	</footer>
</body>
</html>