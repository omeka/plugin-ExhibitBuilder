<?php if ($exhibit->TopPages): ?>
<?php $pageCount = count($exhibit->TopPages); ?>
<?php $treeData = "var treedata = [ "; ?>
<?php foreach($exhibit->TopPages as $index=>$page) {
    $treeData .= $page->toTreeJson();
    if($index < $pageCount) {
        $treeData .= ', ';
    }
}
$treeData .= "]";
?>

<div id="pagetree"></div>

<script type='text/javascript'>
<?php echo $treeData; ?>

function handleSuccess(response, status, jqxhr ) {
    //TODO: something if status is fail!fail!fail!
}

jQuery('#pagetree').tree({
    data: treedata,
    dragAndDrop: true,
    autoOpen: true,
    selectable: true
});

jQuery('#pagetree').bind('tree.move',
    function(event) {
        data = {data : jQuery('#pagetree').tree('toJson') };

        jQuery.post('http://localhost/Omeka/admin/exhibits/update-page-order', data, handleSuccess );
});


</script>

<p>TODO: add back in deleting the page, and editing the page content</p>
<p>styling and the nifty icon</p>
<span class="handle"><img src="<?php echo html_escape(img('silk-icons/page_go.png')); ?>" alt="Move" /></span>



<!--
                <span class="right">
                    <span class="page-edit"><a href="<?php echo html_escape(uri('exhibits/edit-page-content/'.$exhibitPage->id)); ?>" class="edit"><?php echo __('Edit'); ?></a></span>
                    <span class="page-delete"><a href="<?php echo html_escape(uri('exhibits/delete-page/'.$exhibitPage->id)); ?>" class="delete-page"><?php echo __('Delete'); ?></a></span>
                </span>


-->

<?php endif; ?>
