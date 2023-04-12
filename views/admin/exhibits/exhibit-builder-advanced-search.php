<div id="search-by-exhibit" class="field">
    <div class="field-meta"><?php echo $this->formLabel('exhibit', __('Search by Exhibit')); ?></div>
    <div class="inputs">
        <?php echo $this->formSelect('exhibit', @$_GET['exhibit'], array(), get_table_options('Exhibit')); ?>
    </div>
</div>
