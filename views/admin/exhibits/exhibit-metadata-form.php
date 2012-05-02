<?php
if ($exhibit->title) {
    $exhibitTitle = __('Edit Exhibit: "%s"', $exhibit->title);
} else {
    $exhibitTitle = ($actionName == 'Add') ? __('Add Exhibit') : __('Edit Exhibit');
}
?>
<?php head(array('title'=> html_escape($exhibitTitle), 'bodyclass'=>'exhibits')); ?>


<script type="text/javascript" charset="utf-8">
//<![CDATA[
    var listSorter = {};

    jQuery(window).load(function() {
        Omeka.ExhibitBuilder.wysiwyg();
        Omeka.ExhibitBuilder.addStyling();

    });
//]]>
</script>

<h1><?php echo html_escape($exhibitTitle); ?></h1>

<div id="primary">
    <div id="exhibits-breadcrumb">
        <a href="<?php echo html_escape(uri('exhibits')); ?>"><?php echo __('Exhibits'); ?></a> &gt;
        <?php echo html_escape($exhibitTitle); ?>
    </div>

<?php echo flash();?>

    <form id="exhibit-metadata-form" method="post" class="exhibit-builder">

        <fieldset>
            <legend><?php echo __('Exhibit Metadata'); ?></legend>
            <div class="field">
            <?php echo $this->formLabel('title', __('Title')); ?>
            <?php echo $this->formText('title', $exhibit->title, array( 'class'=>'textinput', 'id'=>'title')); ?>

            </div>
            <div class="field">
            <?php echo $this->formLabel('slug', __('Slug')); ?>
            <?php echo $this->formText('slug', $exhibit->slug, array( 'id'=>'slug', 'class'=>'textinput') ); ?>
            <p class="explanation"><?php echo __('No spaces or special characters allowed.'); ?></p>
            </div>
            <div class="field">
            <?php echo $this->formLabel('credits', __('Credits')); ?>
            <?php echo $this->formText('credits', $exhibit->credits, array( 'id'=>'credits', 'class'=>'textinput') ); ?>
            </div>
            <div class="field">
            <?php echo $this->formLabel('description', __('Description')); ?>
            <?php echo $this->formTextarea('description', $exhibit->description, array( 'id'=>'description', 'class'=>'textinput','rows'=>'10','cols'=>'40') ); ?>
            </div>
            <?php $exhibitTagList = join(', ', pluck('name', $exhibit->Tags)); ?>
            <div class="field">
            <?php echo $this->formLabel('tags', __('Tags')); ?>
            <?php echo $this->formText('tags', $exhibitTagList, array('name'=>'tags', 'id'=>'tags', 'class'=>'textinput') ); ?>
            </div>
            <div class="field">
                <?php echo $this->formLabel('featured', __('Featured')); ?>
                <?php if($exhibit->featured == 1) {
                    $atts = array('checked'=>'checked', 'id'=>'featured');
                } else {
                    $atts = array('id'=>'featured');
                };
                echo $this->formCheckbox('featured', null, $atts );
                ?>

            </div>
            <div class="field">
                <?php echo $this->formLabel('public', __('Public') ); ?>

                <?php if($exhibit->public == 1) {
                    $atts = array('checked'=>'checked', 'id'=>'public');
                } else {
                    $atts = array('id'=>'public');
                }
                 echo $this->formCheckbox('public', null, $atts ); ?>
            </div>
            <div class="field">
                <label for="theme"><?php echo __('Theme'); ?></label>
                <?php $values = array('' => __('Current Public Theme')) + exhibit_builder_get_ex_themes(); ?>
                <div class="select"><?php echo __v()->formSelect('theme', $exhibit->theme, array('id'=>'theme'), $values); ?>
                <?php if ($theme && $theme->hasConfig): ?><a href="<?php echo html_escape(uri("exhibits/theme-config/$exhibit->id")); ?>" class="configure-button button"><?php echo __('Configure'); ?></a><?php endif;?>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend><?php echo __('Pages'); ?></legend>
            <div id="pages-list-container">
                <?php if (!$exhibit->TopPages): ?>
                    <p><?php echo __('There are no pages.'); ?></p>
                <?php else: ?>
                <p id="reorder-instructions"><?php echo __('To reorder pages, click and drag the page up or down to the preferred location.'); ?></p>
                <?php endif; ?>
                <ul class="page-list">
                    <?php common('page-list', compact('exhibit'), 'exhibits'); ?>
                </ul>
            </div>
            <div id="page-add">
                <input type="submit" name="add_page" id="add-page" value="<?php echo __('Add Page'); ?>" />
            </div>
        </fieldset>
        <fieldset>
            <p id="exhibit-builder-save-changes">
                <input type="submit" name="save_exhibit" id="save_exhibit" value="<?php echo __('Save Changes'); ?>" /> <?php echo __('or'); ?>
                <a href="<?php echo html_escape(uri('exhibits')); ?>" class="cancel"><?php echo __('Cancel'); ?></a>
            </p>
        </fieldset>
    </form>
</div>
<?php foot(); ?>
