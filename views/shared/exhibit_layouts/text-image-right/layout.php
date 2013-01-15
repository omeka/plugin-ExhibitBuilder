<div class="text-image-right">
    <div class="image-right">
        <?php if ($attachment = exhibit_builder_page_attachment(1)): ?>
        <div class="exhibit-item">
            <?php echo exhibit_builder_attachment_markup($attachment, array('imageSize' => 'fullsize'), array('class' => 'permalink')); ?>
        </div>
        <?php endif; ?>
    </div>
    <?php echo exhibit_builder_page_text(1); ?>
</div>
