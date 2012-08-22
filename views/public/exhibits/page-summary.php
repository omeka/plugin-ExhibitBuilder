<div class='exhibit-builder-page-summary'>
<h3><a href="<?php echo exhibit_builder_exhibit_uri(get_current_exhibit(), get_current_exhibit_page()); ?>">
    <?php echo metadata('exhibitPage', 'title'); ?></a></h3>
<?php while(exhibit_builder_page_loop_children()): ?>
<?php exhibit_builder_render_page_summary(); ?>
<?php endwhile; ?>
</div>
