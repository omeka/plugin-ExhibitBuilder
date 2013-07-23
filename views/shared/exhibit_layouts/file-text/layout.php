<div class="exhibit-items <?php echo html_escape($options['file-position']); ?>">
    <?php foreach ($attachments as $attachment): ?>
        <?php echo $this->exhibitAttachment($attachment, array('imageSize' => 'fullsize')); ?>
    <?php endforeach; ?>
</div>
<?php echo $text; ?>
