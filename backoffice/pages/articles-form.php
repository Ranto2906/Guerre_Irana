
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
            <textarea id="content-editor" name="content" rows="12" required><?php echo e((string) ($formValues['content'] ?? '')); ?></textarea>
            <small>Utilise les titres H1 a H6 pour structurer le texte.</small>
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
