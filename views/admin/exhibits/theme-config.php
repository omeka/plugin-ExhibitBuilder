<?php
if ($exhibit->title) {
    $exhibitTitle = 'Configure Theme for Exhibit: "' . $exhibit->title . '"';
} else {
    $exhibitTitle = 'Configure Theme for Exhibit';
}
?>
<?php head(array('title'=> html_escape($exhibitTitle), 'bodyclass'=>'exhibits')); ?>
<?php echo js('listsort'); ?>
<?php echo js('jquery'); ?>
<script type="text/javascript">
    jQuery.noConflict();
    
    jQuery(document).ready(function() {
    
        var files = jQuery("input[type=file]");

        files.each( function(i, val) {
           fileInput = jQuery(val);
           fileInputName = fileInput.attr("name");
           
           hiddenFile = jQuery("#hidden_file_" + fileInputName);
           hiddenFileName = jQuery.trim(hiddenFile.attr("value"));
           if (hiddenFileName != "") {
                              
               var fileNameDiv = jQuery(document.createElement('div'));
               fileNameDiv.attr('id', 'x_hidden_file_' + fileInputName);
               fileNameDiv.text(hiddenFileName);
               
               var changeFileButton = createChangeFileButton(fileInputName);
               fileNameDiv.append(changeFileButton);
               
               fileInput.after(fileNameDiv);
               fileInput.hide();
           }
        });
    });
    
    function createChangeFileButton(fileInputName)
    {
        var button = jQuery(document.createElement('a'));
        button.text('Change');
        button.attr('class', 'submit');
        button.click(function () {
              hiddenFile = jQuery("#hidden_file_" + fileInputName);
              hiddenFile.attr("value", "");                     
              
              fileInput = jQuery("#" + fileInputName);
              fileInput.show();
              
              fileNameDiv = jQuery("#x_hidden_file_" + fileInputName);
              fileNameDiv.hide(); 
              
              jQuery(this).hide();
        });
        return button;
    }

</script>

<h1><?php echo html_escape($exhibitTitle); ?></h1>

<div id="primary">
    <div id="exhibits-breadcrumb">
        <a href="<?php echo html_escape(uri('exhibits')); ?>">Exhibits</a> &gt; <?php echo html_escape('Configure Theme for Exhibit'); ?>
    </div>
    <h2><?php echo $theme->title; ?> Configuration</h2>    
<?php flash(); ?>
<?php echo $form; ?>
</div>
<?php foot(); ?>