<div class="field">
    <label for="exhibit_builder_use_browse_exhibits_for_homepage">
        <?php echo __('Use Exhibit Browse Page For Homepage?'); ?>
    </label>
    <div class="inputs">
    <?php echo __v()->formCheckbox('exhibit_builder_use_browse_exhibits_for_homepage', true, 
    array('checked'=>(boolean)get_option('exhibit_builder_use_browse_exhibits_for_homepage'))); ?>
    <p class="explanation">
        <?php echo __('If checked, Omeka will use the exhibit browse page for the homepage.'); ?>
    </p>
    </div>
</div>
<div class="field">
    <label for="exhibit_builder_sort_browse"><?php echo __('Sorting Exhibits'); ?></label>
    <div class="inputs">
        <?php echo __v()->formSelect('exhibit_builder_sort_browse', get_option('exhibit_builder_sort_browse'), null, array('added' => 'Date Added', 'alpha' => 'Alphabetical', 'recent' => 'Recent')); ?>
        
        <p class="explanation">
            <?php echo __("The default method by which you wish to sort the listing of exhibits on the exhibits/browse page. Default is 'Date Added'."); ?>
        </p>
    </div>
</div>
