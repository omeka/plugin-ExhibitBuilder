<?php if ($exhibit->TopPages): ?>
<?php $pageCount = count($exhibit->TopPages); ?>
<?php $treeData = "var treedata = [ "; ?>
<?php foreach($exhibit->TopPages as $index=>$page) {
    $treeData .= $page->toTreeJson();
    if($index < $pageCount) {
        $treeData .= ', ';
    }
}
$treeData .= "] ;";
?>

<div id="pagetree"></div>

<script type='text/javascript'>
<?php echo $treeData; ?>

var webRoot = "<?php echo WEB_ROOT; ?>";

function countMaxDepth(depth, node) {
    index = 0;
    prevDepth = depth;
    depth++;
    if(node.children) {
        node.children.forEach(function(el, index, array) {
            depth = countMaxDepth(depth, el);
            if(!depth) {
                return false;
            }
        });
    }
    console.log(depth);
    if(depth >3) {
        alert("Can't have more than three child pages.'");
        return false;
    }
    return prevDepth;
}

function handleSuccess(response, status, jqxhr ) {
    //TODO: something if status is fail!fail!fail!
}

jQuery('#pagetree').tree({
    data: treedata,
    dragAndDrop: true,
    autoOpen: true,
    selectable: true,

    onCreateLi: function(node, li) {

        if(node.children.length === 0) {
            li.addClass('empty');
        }


        // Add 'icon' span before title
        title = li.find('.title');
        title.before('<span><img src="<?php echo html_escape(img('silk-icons/page_go.png')); ?>" alt="Move" /></span>');

        editUrl = "<?php echo html_escape(uri('exhibits/edit-page-content/')); ?>" + node.id;
        deleteUrl = "<?php echo html_escape(uri('exhibits/delete-page/')); ?>" + node.id;
        actionsHTML = '<span>';
        actionsHTML += '<span class="page-edit"><a href="' + editUrl + '" class="edit"><?php echo __('Edit'); ?></a></span>';
        actionsHTML += '<span class="page-delete"><a href="' + deleteUrl + '" class="delete-page"><?php echo __('Delete'); ?></a></span>';
        actionsHTML += '</span>';
        title.after(actionsHTML);

    }
});

jQuery('#pagetree').bind('tree.move',
    function(event) {
        data = {data : jQuery('#pagetree').tree('toJson') };
        console.log(data.data);
        valid = countMaxDepth(1, JSON.parse(data.data)[0]);
        //make sure the depth never gets beyond 6 (or whatever arbitrary depth we decide on)

        if(valid) {
            jQuery.post(webRoot + '/admin/exhibits/update-page-order', data, handleSuccess );
        }

});


</script>

<?php endif; ?>
