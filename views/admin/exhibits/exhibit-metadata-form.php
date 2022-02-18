<form id="exhibit-metadata-form" method="post" class="exhibit-builder">
    <section class="seven columns alpha">
    <fieldset>
        <legend><?php echo __('Exhibit Metadata'); ?></legend>
        <div class="field">
            <div class="two columns alpha">
                <?php echo $this->formLabel('title', __('Title')); ?>
            </div>
            <div class="five columns omega inputs">
                <?php echo $this->formText('title', $exhibit->title); ?>
            </div>
        </div>
        <div class="field">
            <div class="two columns alpha">
                <?php echo $this->formLabel('slug', __('Slug')); ?>
            </div>
            <div class="five columns omega inputs">
                <p class="explanation"><?php echo __('No spaces or special characters allowed'); ?></p>
                <?php echo $this->formText('slug', $exhibit->slug); ?>
            </div>
        </div>
        <div class="field">
            <div class="two columns alpha">
                <?php echo $this->formLabel('credits', __('Credits')); ?>
            </div>
            <div class="five columns omega inputs">
                <?php echo $this->formText('credits', $exhibit->credits); ?>
            </div>
        </div>
        <div class="field">
            <div class="two columns alpha">
                <?php echo $this->formLabel('description', __('Description')); ?>
            </div>
            <div class="five columns omega inputs">
                <?php echo $this->formTextarea('description', $exhibit->description, array('rows'=>'8','cols'=>'40')); ?>
            </div>
        </div>
        <div class="field">
            <div class="two columns alpha">
                <?php echo $this->formLabel('tags', __('Tags')); ?>
            </div>
            <div class="five columns omega inputs">
                <?php $exhibitTagList = join(', ', pluck('name', $exhibit->Tags)); ?>
                <?php echo $this->formText('tags', $exhibitTagList); ?>
            </div>
        </div>
        <div class="field">
            <div class="two columns alpha">
                <?php echo $this->formLabel('theme', __('Theme')); ?>
            </div>
            <div class="five columns omega inputs">
                <?php $values = array('' => __('Current Public Theme')) + exhibit_builder_get_themes(); ?>
                <?php echo get_view()->formSelect('theme', $exhibit->theme, array(), $values); ?>
                    <input type="submit" class="configure-button" name="configure-theme" value="<?php echo __('Configure'); ?>">
            </div>
        </div>
        <div class="field">
            <div class="two columns alpha">
                <?php echo $this->formLabel('use_summary_page', __('Use Summary Page?')); ?>
            </div>
            <div class="five columns omega inputs">
                <p class="explanation"><?php echo __("Start the exhibit on the summary page. If unchecked, start on the first exhibit page if it exists."); ?></p>
                <?php echo $this->formCheckbox('use_summary_page', $exhibit->use_summary_page, array(), array('1', '0')); ?>
            </div>
        </div>
        <div id="cover-image-container" class="field">
            <div class="two columns alpha">
                <?php echo $this->formLabel('cover_image', __('Cover Image')); ?>
            </div>
            <div class="five columns omega inputs">
                <p class="explanation">
                  <?php echo __('Choose a file to represent this exhibit. The selected file will serve as the thumbnail for the exhibit.'); ?>
                </p>
                <?php echo $this->partial('files/cover-image.php', array('file' => $exhibit->getCoverImage())); ?>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend><?php echo __('Pages'); ?></legend>
        <div id="pages-list-container">
            <?php if (!$exhibit->TopPages): ?>
                <p><?php echo __('There are no pages.'); ?></p>
            <?php else: ?>
                <p class="instructions"><?php echo __('To reorder or nest pages, click and drag a page to the preferred location.'); ?></p>
                <?php echo common('page-list', array('exhibit' => $exhibit), 'exhibits'); ?>
            <?php endif; ?>
        </div>
        <div id="page-add">
            <input type="submit" name="add_page" id="add-page" value="<?php echo __('Add Page'); ?>" />
        </div>
    </fieldset>
    </section>
    <?php echo $csrf; ?>
    <section class="three columns omega">
        <div id="save" class="panel">
            <?php echo $this->formSubmit('save_exhibit', __('Save Changes'), array('class'=>'submit big green button')); ?>
            <?php if ($exhibit->exists()): ?>
                <?php echo exhibit_builder_link_to_exhibit($exhibit, __('View Public Page'), array('class' => 'big blue button', 'target' => '_blank')); ?>
                <?php if (is_allowed($exhibit, 'delete')): ?>
                    <?php echo link_to($exhibit, 'delete-confirm', __('Delete Exhibit'), array('class' => 'big red button delete-confirm')); ?>
                <?php endif; ?>
            <?php endif; ?>
            <div id="public-featured">
                <div class="public">
                    <label for="public"><?php echo __('Public'); ?>:</label> 
                    <?php echo $this->formCheckbox('public', $exhibit->public, array(), array('1', '0')); ?>
                </div>
                <div class="featured">
                    <label for="featured"><?php echo __('Featured'); ?>:</label> 
                    <?php echo $this->formCheckbox('featured', $exhibit->featured, array(), array('1', '0')); ?>
                </div>
            </div>
        </div>
    </section>
</form>
<div id="cover-image-panel" title="<?php echo html_escape(__('Choose a Cover Image')); ?>">
    <div id="item-form">
        <div class="browse-controls">
            <button type="button" id="revert-selected-item">Revert to Selected Item</button>
            <button type="button" class="search-toggle show-form blue active" aria-label="<?php echo __('Show Search Form'); ?>" title="<?php echo __('Show Search Form'); ?>"></button>
            <button type="button" class="search-toggle hide-form blue" aria-label="<?php echo __('Hide Search Form'); ?>" title="<?php echo __('Hide Search Form'); ?>"></button>
        </div>
        <div id="page-search-form" class="container-twelve">
        <?php
            $action = url(array('module' => 'exhibit-builder',
                'controller' => 'items', 'action' => 'browse'), 'default', array(), true);
            echo items_search_form(array('id' => 'search'), $action);
        ?>
        </div>
        <div id="item-select"></div>
    </div>
    <div id="cover-image-options">
      <button type="button" id="change-selected-item"><?php echo __('Change Selected Item'); ?></button>
      <div class="options">
        <div id="cover-image-item-options"></div>
      </div>
      <div id="attachment-save">
        <button type="submit" id="choose-cover-image"><?php echo __('Choose'); ?></button>
      </div>
    </div>
    <div id="cover-image-panel-loading"><span class="spinner"></span></div>
</div>
<script type="text/javascript" charset="utf-8">
    jQuery(document).ready(function(){
        Omeka.wysiwyg();
        Omeka.ExhibitBuilder.setUpCoverImageChooser(
          <?php echo json_encode(url('exhibit-builder/files/cover-image')); ?>,
          <?php echo js_escape(url('exhibits/attachment-item-options')); ?>
        );
        Omeka.ExhibitBuilder.setUpCoverImageSelect(<?php echo json_encode(url('exhibit-builder/items/browse')); ?>);
    });
</script>
