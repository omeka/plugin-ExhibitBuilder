<div class="field">
    <div class="two columns alpha">
        <label for="exhibit_builder_sort_browse"><?php echo __('Sorting Exhibits'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            <?php echo __("The default method by which you wish to sort the listing of exhibits on the exhibits/browse page. Default is 'Date Added'."); ?>
        </p>
        <?php echo get_view()->formSelect('exhibit_builder_sort_browse', get_option('exhibit_builder_sort_browse'), null, array('added' => __('Date Added'), 'alpha' => __('Alphabetical'), 'recent' => __('Recent'))); ?>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <label for="exhibit_builder_researcher_permissions"><?php echo __('Contributor/Researcher Permissions'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            <?php echo __('Allow Contributor and Researcher users to view unpublished exhibits.'); ?>
        </p>
        <?php echo get_view()->formCheckbox('exhibit_builder_researcher_permissions', get_option('exhibit_builder_researcher_permissions'), null, array('checked' => 1)); ?> 
    </div>
</div>
