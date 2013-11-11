<?php
$item = $attachment->getItem();
$caption = $attachment->caption;
$file_id = $attachment->file_id;
$files = $item->Files;
if ($file_id === null && $files) {
    $file_id = $files[0]->id;
}
if (!metadata($item, 'public')) {
    $private = ' ' . __('(Private)');
} else {
    $private = '';
}
?>
<?php echo $this->formHidden('item_id', $item->id); ?>
<h2><?php echo __('Selected Item: %s', metadata($item, array('Dublin Core', 'Title'))) . $private; ?></h2>
<?php if ($files): ?>
<div class="file-select">
    <?php if (count($files) > 1): ?>
    <p class="direction"><?php echo __('Select a file to use.'); ?></p>
    <?php endif; ?>
    <div class="inputs">
        <ul>
            <?php foreach ($files as $index => $file): ?>
            <?php $selected = $file_id == $file->id; ?>
            <li class="item-file <?php if ($selected) echo 'selected'; ?>">
                <label>
                    <?php echo file_image('square_thumbnail', array(), $file); ?>
                    <input id="file-<?php echo $index; ?>" type="radio" name="file_id" title="<?php echo metadata($file, 'display_title'); ?>" value="<?php echo html_escape($file->id); ?>" <?php if ($selected) echo 'checked'; ?>>
                    <div class="file-title"><?php echo metadata($file, 'display_title'); ?></div>
                </label>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

