<?php
$position = isset($options['file-position'])
    ? html_escape($options['file-position'])
    : 'left';
$size = isset($options['file-size'])
    ? html_escape($options['file-size'])
    : 'fullsize';
?>
<div class="exhibit-items <?php echo $position; ?> <?php echo $size; ?>">
    <?php foreach ($attachments as $attachment): ?>
        <?php echo $this->exhibitAttachment($attachment, array('imageSize' => $size)); ?>
    <?php endforeach; ?>
</div>
<?php echo $text; ?>
