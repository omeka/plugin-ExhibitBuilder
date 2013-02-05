<?php
$title = ($actionName == 'Add') ? __('Add Page') : __('Edit Page "%s"', $exhibit_page->title);
echo head(array('title'=> $title, 'bodyclass'=>'exhibits'));
?>
<?php echo flash(); ?>
<form method="post" id="choose-layout">
    <div id="exhibits-breadcrumb">
        <a href="<?php echo html_escape(url('exhibits')); ?>"><?php echo __('Exhibits'); ?></a> &gt;
        <a href="<?php echo html_escape(url('exhibits/edit/' . $exhibit['id']));?>"><?php echo html_escape($exhibit['title']); ?></a>  &gt;
        <?php echo html_escape($title); ?>
    </div>
    <div class="seven columns alpha">
    <fieldset>
        <legend><?php echo __('Page Metadata'); ?></legend>
        <div class="field">
            <div class="two columns alpha">
            <?php echo $this->formLabel('title', __('Title')); ?>
            </div>
            <div class="inputs five columns omega">
            <?php echo $this->formText('title', $exhibit_page->title); ?>
            </div>
        </div>
        <div class="field">
            <div class="two columns alpha">
                <?php echo $this->formLabel('slug', __('Slug')); ?>
            </div>
            <div class="inputs five columns omega">
                <p class="explanation"><?php echo __('No spaces or special characters allowed'); ?></p>
                <?php echo $this->formText('slug', $exhibit_page->slug); ?>
            </div>
        </div>
    </fieldset>

    <fieldset id="layouts">
        <legend><?php echo __('Layouts'); ?></legend>

        <div id="layout-thumbs">
        <?php
            $layouts = exhibit_builder_get_layouts();
            foreach ($layouts as $layout) {
                echo exhibit_builder_layout($layout);
            }
        ?>
        </div>
    </fieldset>
    </div>
    <div id="save" class="three columns omega panel">
        <?php echo $this->formSubmit('save_page_metadata', __('Save Changes'), array('class'=>'submit big green button')); ?>
        <?php if ($exhibit_page->exists()): ?>
            <?php echo exhibit_builder_link_to_exhibit($exhibit, __('View Public Page'), array('class' => 'big blue button', 'target' => '_blank'), $exhibit_page); ?>
        <?php endif; ?>
        <div id="chosen_layout">
        <h4><?php echo __('Layout'); ?></h4>
        <?php
        if ($layout = $exhibit_page->layout) {
            echo exhibit_builder_layout($layout, false);
        } else {
            echo '<p>' . __('Choose a layout by selecting a thumbnail on the right.') . '</p>';
        }
        ?>
        </div>
    </div>
</form>
<script type="text/javascript" charset="utf-8">
//<![CDATA[

    jQuery(document).ready(function() {
        makeLayoutSelectable();
    });

    function makeLayoutSelectable() {
        //Make each layout clickable
        jQuery('div.layout').bind('click', function(e) {
            jQuery('#layout-thumbs').find('div.current-layout').removeClass('current-layout');
            jQuery(this).addClass('current-layout');

            // Remove the old chosen layout
            jQuery('#chosen_layout').find('div.layout').remove()
            jQuery('#chosen_layout').find('p').remove();

            // Copy the chosen layout
            var copyLayout = jQuery(this).clone();

            // Take the form input out of the copy (so no messed up forms).
            copyLayout.find('input').remove();

            // Change the id of the copy
            copyLayout.attr('id', 'chosen_' + copyLayout.attr('id')).removeClass('current-layout');

            // Append the copy layout to the chosen_layout div
            copyLayout.appendTo('#chosen_layout');

            // Check the radio input for the layout
            jQuery(this).find('input').attr('checked', true);
        });
    }
//]]>
</script>
<?php echo foot(); ?>
