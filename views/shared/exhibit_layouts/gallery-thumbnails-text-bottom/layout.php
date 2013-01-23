<div class="gallery-thumbnails-text-bottom">
    <div class="primary">
        <?php echo exhibit_builder_thumbnail_gallery(1, 12, array('class'=>'permalink')); ?>
    </div>
    <?php if (exhibit_builder_page_text(1)): ?>
    <div class="secondary">
        <div class="exhibit-text">
        <?php echo exhibit_builder_page_text(1); ?>
        </div>
    </div>
    <?php endif; ?>
</div>
