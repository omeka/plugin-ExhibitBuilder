<?php
$item = $attachment->getItem();
$caption = $attachment->caption;
$file_id = $attachment->file_id;
$files = $item->Files;
?>
<?php echo $this->formHidden('item_id', $item->id); ?>
<h4><?php echo __('Selected Item: %s', metadata($item, array('Dublin Core', 'Title'))); ?></h4>
<?php if ($files): ?>
<div class="file-select">
    <?php if (count($files) == 1): ?>
        <?php $file = $files[0]; ?>
        <div class="item-file">
            <label>
                <?php echo file_image('square_thumbnail', array(), $file); ?>
                <input id="file" type="radio" name="file_id" value="<?php echo html_escape($file->id); ?>" checked>
                <?php echo metadata($file, 'display_title'); ?>
            </label>
        </div>
    </div>
    <?php else: ?>
    <p class="direction"><?php echo __('Select a file to use.'); ?></p>
    <div class="inputs">
        <ul>
            <?php foreach ($files as $index => $file): ?>
            <li class="item-file">
                <label>
                    <?php echo file_image('square_thumbnail', array(), $file); ?>
                    <input id="file-<?php echo $index; ?>" type="radio" name="file_id" value="<?php echo html_escape($file->id); ?>" <?php if ($file_id == $file->id) echo 'checked'; ?>>
                    <?php echo metadata($file, 'display_title'); ?>
                </label>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<div class="item-caption">
    <p class="direction"><?php echo __('Provide a caption.'); ?></p>
    <div class="inputs">
        <?php echo $this->formTextarea('caption', $caption, array('rows' => 3)); ?>
    </div>
</div>
