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

function countParents(node) {
    if (node.parent) {
        return 1 + countParents(node.parent);
    } else {
        return 0;
    }
}

function countChildren(node) {
    if (node.children.length === 0) {
        return 0;
    }

    childrenCounts = [];
    for (var i = 0; i < node.children.length; i++) {
        childrenCounts[i] = 1 + countChildren(node.children[i]);
    }
    return Math.max.apply(Math, childrenCounts);
}
        
jQuery('#pagetree').tree({
    data: treedata,
    dragAndDrop: true,
    autoOpen: true,

    onCreateLi: function (node, li) {
        if(node.children.length === 0) {
            li.addClass('empty');
        }

        title = li.find('.jqtree-title');

        editUrl = "<?php echo html_escape(url('exhibits/edit-page-content/')); ?>" + node.id;
        deleteUrl = "<?php echo html_escape(url('exhibits/delete-page/')); ?>" + node.id;
        actionsHTML = '<span class="page-actions">';
        actionsHTML += '<span class="page-edit"><a href="' + editUrl + '" class="edit"><?php echo __('Edit'); ?></a></span>';
        actionsHTML += '<span class="page-delete"><a href="' + deleteUrl + '" class="delete-page"><?php echo __('Delete'); ?></a></span>';
        actionsHTML += '</span>';
        title.after(actionsHTML);
    },

    onCanMoveTo: function (moved_node, target_node, position) {
        var currentDepth = countChildren(moved_node) + 1, targetDepth;
        if (position === 'none') {
            return false;
        }
        if (position === 'inside') {
            targetDepth = countParents(target_node);
        } else {
            targetDepth = countParents(target_node) - 1;
        }

        return currentDepth + targetDepth <= 3;
    },

    onIsMoveHandle: function (element) {
        return element.is('.jqtree-title');
    }
});

jQuery('#pagetree').bind('tree.move', function(event) {
    event.preventDefault();
    event.move_info.do_move();
    data = {data : jQuery(this).tree('toJson') };
    jQuery.post(webRoot + '/admin/exhibits/update-page-order', data)
    // Todo: implement success and error handlers for user feedback
        .success(function() {})
        .error(function() {});
});

</script>

<?php endif; ?>
