<div class="field">
    <div class="two columns alpha">
        <?php echo $this->formLabel('exhibit', __('Search by Exhibit')); ?>
    </div>
    <div class="five columns omega inputs">
        <?php echo $this->formSelect('exhibit', @$_GET['exhibit'], array(), get_table_options('Exhibit')); ?>
    </div>
</div>
