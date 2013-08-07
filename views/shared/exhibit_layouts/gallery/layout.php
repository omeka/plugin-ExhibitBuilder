<?php
$position = isset($options['aside-position'])
    ? html_escape($options['aside-position'])
    : 'left';
$showcaseFile = isset($options['showcase-file'])
    ? (bool) $options['showcase-file']
    : false;
$showAside = !empty($text) || ($showcaseFile && !empty($attachments));
?>
<?php if ($showAside): ?>
<div class="gallery-aside <?php echo $position; ?>">
    <?php
    if ($showcaseFile):
        $attachment = array_shift($attachments);
        echo $this->exhibitAttachment($attachment, array('imageSize' => 'fullsize'));
    endif;
    ?>
</div>
<?php endif; ?>
<div class="gallery <?php if ($showAside) echo 'with-aside'; ?> <?php echo $position; ?>">
    <?php echo $this->exhibitAttachmentGallery($attachments); ?>
</div>
<?php echo $text; ?>
