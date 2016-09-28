<?php if (! empty($columns)): ?>

    <thead>
        <tr>
            <?php foreach ($columns as $column): ?>

                <th<?php echo (! empty($column['class'])) ? ' class="' . $column['class'] . '"' : ''; ?>>
                    <?php if (! empty($column['label'])): ?>
                        <?php echo $lang->get($column['label']); ?>
                    <?php elseif (! isset($column['label']) && (isset($column['field']) && ! is_array($column['field']))): ?>
                        <?php echo $lang->get($column['field']); ?>
                    <?php else: ?>
                        &nbsp;
                    <?php endif; ?>
                </th>

            <?php endforeach; ?>
        </tr>
    </thead>

<?php endif; ?>