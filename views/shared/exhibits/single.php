<div class="exhibit record">
    <h3><?php echo exhibit_builder_link_to_exhibit($exhibit); ?></h3>
    <?php if ($exhibitImage = record_image($exhibit, 'square_thumbnail')): ?>
        <?php echo exhibit_builder_link_to_exhibit($exhibit, $exhibitImage, array('class' => 'image')); ?>
    <?php endif; ?>
    <p><?php echo snippet_by_word_count(metadata($exhibit, 'description', array('no_escape' => true))); ?></p>
</div>
