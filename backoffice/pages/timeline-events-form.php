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
