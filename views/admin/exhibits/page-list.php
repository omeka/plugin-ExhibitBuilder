<?php if ($exhibit->TopPages): ?>
<?php $pageCount = count($exhibit->TopPages); ?>

    <?php foreach($exhibit->TopPages as $index=>$page): ?>

        <?php 
            $pageId = $page['id']; 
            $pageOrder = $page['order'];
        ?>

        <li class="page">
            <div class="sortable-item">
            <a href="../edit-page-content/<?php echo $pageId; ?>"><?php echo $page->title; ?></a>
            <?php echo $this->formHidden("pages[$pageId][order]", $pageOrder, array('size'=>2, 'class' => 'page-order')); ?>
            <a id="return-element-link-<?php echo html_escape($pageId); ?>" href="" class="undo-delete"><?php echo __('Undo'); ?></a>
            <a id="remove-element-link-<?php echo html_escape($pageId); ?>" href="" class="delete-element"><?php echo __('Remove'); ?></a>
            </div>
        </li>
    
    <?php endforeach; ?>

<script type="text/javascript">
Omeka.addReadyCallback(Omeka.ExhibitBuilder.enableSorting);
Omeka.addReadyCallback(Omeka.ExhibitBuilder.addHideButtons);
Omeka.addReadyCallback(Omeka.ExhibitBuilder.setUpFormSubmission);
</script>
<?php endif; ?>
