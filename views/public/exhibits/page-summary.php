<div class='exhibit-builder-page-summary'>
<h3><a href="<?php echo exhibit_builder_exhibit_uri(get_current_exhibit(), get_current_exhibit_page()); ?>">
    <?php echo html_escape(exhibit_page('title')); ?></a></h3>
<?php echo exhibit_page('description'); ?>
<?php while(exhibit_builder_page_loop_children()): ?>
<?php exhibit_builder_render_page_summary(); ?>
<?php endwhile; ?>
</div>