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
