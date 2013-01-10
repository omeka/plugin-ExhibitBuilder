<ul id="page-list" class="sortable">
    <?php foreach($exhibit->TopPages as $page): ?>
        <?php echo exhibit_builder_edit_page_list($page); ?>
    <?php endforeach; ?>
    <?php echo $this->formHidden('pages-hidden'); ?>
    <?php echo $this->formHidden('pages-delete-hidden'); ?>
</ul>

<script type="text/javascript">
Omeka.addReadyCallback(Omeka.ExhibitBuilder.enableSorting);
Omeka.addReadyCallback(Omeka.ExhibitBuilder.activateDeleteLinks);
Omeka.addReadyCallback(Omeka.ExhibitBuilder.setUpFormSubmission);
</script>
