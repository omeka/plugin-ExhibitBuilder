<div class="gallery-full-right">
    <div class="primary">
        <?php if ($attachment = exhibit_builder_page_attachment(1)): ?>
        <div class="exhibit-item">
            <?php echo exhibit_builder_attachment_markup($attachment, array('imageSize' => 'fullsize'), array('class' => 'permalink')); ?>
        </div>
        <?php endif; ?>
        <?php if ($text = exhibit_builder_page_text(1)): ?>
        <div class="exhibit-text">
            <?php echo $text; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="secondary gallery">
        <?php echo exhibit_builder_thumbnail_gallery(2, 9, array('class'=>'permalink')); ?>
    </div>
</div>
