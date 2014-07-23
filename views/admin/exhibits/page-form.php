<?php
$title = ($actionName == 'Add') ? __('Add Page') : __('Edit Page "%s"', $exhibit_page->title);
echo head(array('title'=> $title, 'bodyclass'=>'exhibits'));
?>
<div id="exhibits-breadcrumb">
    <a href="<?php echo html_escape(url('exhibits')); ?>"><?php echo __('Exhibits'); ?></a> &gt;
    <a href="<?php echo html_escape(url('exhibits/edit/' . $exhibit['id']));?>"><?php echo html_escape($exhibit['title']); ?></a>  &gt;
    <?php echo html_escape($title); ?>
</div>
<?php echo flash(); ?>
<form id="exhibit-page-form" method="post">
    <div class="seven columns alpha">
    <fieldset>
        <div class="field">
            <div class="two columns alpha">
            <?php echo $this->formLabel('title', __('Page Title')); ?>
            </div>
            <div class="inputs five columns omega">
            <?php echo $this->formText('title', $exhibit_page->title); ?>
            </div>
        </div>
        <div class="field">
            <div class="two columns alpha">
                <?php echo $this->formLabel('slug', __('Page Slug')); ?>
            </div>
            <div class="inputs five columns omega">
                <p class="explanation"><?php echo __('No spaces or special characters allowed'); ?></p>
                <?php echo $this->formText('slug', $exhibit_page->slug); ?>
            </div>
        </div>
    </fieldset>
    <fieldset id="block-container">
        <h2><?php echo __('Content'); ?></h2>
        <span class="collapse"><?php echo __('Collapse All'); ?></span>
        <span class="expand"><?php echo __('Expand All'); ?></span>
        <p class="instructions"><?php echo __('To reorder blocks and items, click and drag them to the preferred location.'); ?></p>
        <?php
        foreach ($exhibit_page->getPageBlocks() as $index => $block):
            $block->order = $index + 1;
            echo $this->partial('exhibits/block-form.php', array('block' => $block));
        endforeach;
        ?>
        <div class="add-block">
            <h2><?php echo __('New Block'); ?></h2>
            <div class="layout-select">
                <h4><?php echo __('Select layout'); ?></h3>
                <div class="layout-thumbs">
                <?php
                    $layouts = ExhibitLayout::getLayouts();
                    foreach ($layouts as $layout) {
                        $layout_id = html_escape($layout->id);
                        echo '<div class="layout" id="' . $layout_id . '">';
                        echo '<img src="' . html_escape($layout->getIconUrl()) . '">';
                        echo '<span class="layout-name">' . $layout->name . '</span>';
                        echo '<input type="radio" name="new-block-layout" value="'. $layout_id .'">';
                        echo '</div>';
                    }
                    foreach ($layouts as $layout) {
                        echo '<div class="'.html_escape($layout->id).' layout-description">';
                        echo $layout->description;
                        echo '</div>';
                    }
                ?>
                <a class="add-link big button" href="#"><?php echo __('Add new content block'); ?></a>
                </div>
            </div>
        </div>
    </fieldset>
    </div>
    <?php echo $csrf; ?>
    <div class="three columns omega">
        <div id="save" class="panel">
            <?php echo $this->formSubmit('continue', __('Save Changes'), array('class'=>'submit big green button')); ?>
            <?php echo $this->formSubmit('add-another-page', __('Save and Add Another Page'), array('class'=>'submit big green button')); ?>
            <?php if ($exhibit_page->exists()): ?>
                <?php echo exhibit_builder_link_to_exhibit($exhibit, __('View Public Page'), array('class' => 'big blue button', 'target' => '_blank'), $exhibit_page); ?>
            <?php endif; ?>
        </div>
    </div>
</form>
<?php //This item-select div must be outside the <form> tag for this page, b/c IE7 can't handle nested form tags. ?>
<div id="attachment-panel" title="<?php echo html_escape(__('Attach an Item')); ?>">
    <div id="item-form">
        <button type="button" id="revert-selected-item"><?php echo __('Revert to Selected Item'); ?></button>
        <button type="button" id="show-or-hide-search" class="show-form blue">
            <span class="show-search-label"><?php echo __('Show Search Form'); ?></span>
            <span class="hide-search-label"><?php echo __('Hide Search Form'); ?></span>
        </button>
        <a href="<?php echo url('exhibit-builder/items/browse'); ?>" id="view-all-items" class="green button"><?php echo __('View All Items'); ?></a>
        <div id="page-search-form" class="container-twelve">
        <?php
            $action = url(array('module' => 'exhibit-builder',
                'controller' => 'items', 'action' => 'browse'), 'default', array(), true);
            echo items_search_form(array('id' => 'search'), $action);
        ?>
        </div>
        <div id="item-select"></div>
    </div>
    <div id="attachment-options">
        <button type="button" id="change-selected-item"><?php echo __('Change Selected Item'); ?></button>
        <div class="options">
            <div id="attachment-item-options"></div>
            <div class="item-caption">
                <p class="direction"><?php echo __('Provide a caption.'); ?></p>
                <div class="inputs">
                    <?php echo $this->formTextarea('caption', '', array('rows' => 3, 'id' => 'attachment-caption')); ?>
                </div>
            </div>
        </div>
        <div id="attachment-save">
            <button type="submit" id="apply-attachment"><?php echo __('Apply'); ?></button>
        </div>
    </div>
    <div id="attachment-panel-loading"><span class="spinner"></span></div>
</div>
<script type="text/javascript">
jQuery(document).ready(function () {
    Omeka.ExhibitBuilder.setUpBlocks(<?php echo json_encode(url('exhibits/block-form')); ?>);
    Omeka.ExhibitBuilder.setUpItemsSelect(<?php echo js_escape(url('exhibits/attachment-item-options')); ?>);
    Omeka.ExhibitBuilder.setUpAttachments(<?php echo js_escape(url('exhibits/attachment')); ?>);
    <?php
    if ($exhibit_page->exists()) {
        $validateUrl = url(
            array('action' => 'validate-page', 'id' => $exhibit_page->id),
            'exhibitStandard', array(), true);
    } else {
        $validateUrl = url(
            array('action' => 'validate-page', 'exhibit_id' => $exhibit_page->exhibit_id,
                'parent_id' => $exhibit_page->parent_id),
            'exhibitAction', array(), true);
    }
    ?>
    Omeka.ExhibitBuilder.setUpPageValidate(<?php echo js_escape($validateUrl); ?>);

    Omeka.wysiwyg();
    jQuery(document).on('exhibit-builder-refresh-wysiwyg', function (event) {
        // Add tinyMCE to all textareas in the div where the item was attached.
        jQuery(event.target).find('textarea').each(function () {
            tinyMCE.execCommand('mceAddControl', false, this.id);
        });
    });
});
</script>
<?php echo foot(); ?>
