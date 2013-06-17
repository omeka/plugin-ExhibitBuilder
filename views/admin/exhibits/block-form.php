<?php
$layout = $block->getLayout();
?>
<div class="block-form">
<?php
echo $this->partial($layout->getViewPartial('form'), array('block' => $block));
?>
</div>
