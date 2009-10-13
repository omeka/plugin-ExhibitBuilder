<?php
if ($exhibit->title) {
    $exhibitTitle = $actionName . ' Exhibit: "' . $exhibit->title . '"';
} else {
    $sectionTitle = $actionName . ' Exhibit';
}
?>
<?php head(array('title'=> html_escape($exhibitTitle), 'bodyclass'=>'exhibits')); ?>
<?php echo js('listsort'); ?>

<script type="text/javascript" charset="utf-8"> 
//<![CDATA[
    
    Event.observe(window, 'load', Omeka.ExhibitBuilder.wysiwyg);
    
    var urls = {
        sectionForm: <?php echo Zend_Json::encode(uri('exhibits/section-form')); ?>,
        addSection: <?php echo Zend_Json::encode(uri(array('controller'=>'exhibits','action'=>'add-section'), 'default')); ?>,
        sectionList: <?php echo Zend_Json::encode(uri(array('controller'=>'exhibits','action'=>'section-list'), 'default')); ?>,
        edit: <?php echo Zend_Json::encode(uri('exhibits/edit')); ?>
    };
    
    Event.observe(window, 'load', function() {  
        makeSectionListDraggable();
    });
    
    function makeSectionListDraggable()
    {       
        var list = $('section-list');
        
        var exhibit_id = <?php echo $exhibit->exists() ? $exhibit->id : 'null'; ?>; 
        listSorter.list = list;
        listSorter.recordId = exhibit_id;
        listSorter.form = $('exhibit-metadata-form');
        listSorter.editUri = <?php echo Zend_Json::encode(uri(array('controller'=>'exhibits','action'=>'edit'),'default')); ?> + "/" + exhibit_id;
        listSorter.partialUri = <?php echo  Zend_Json::encode(uri(array('controller'=>'exhibits', 'action'=>'section-list'))); ?> + "?id=" + exhibit_id;
        listSorter.tag = 'li';
        listSorter.handle = 'handle';
        listSorter.confirmation = 'Are you sure you want to delete this section?';
        listSorter.deleteLinks = '.section-delete a';
        listSorter.callback = Omeka.ExhibitBuilder.addStyling;

        if(listSorter.list) {
            //Create the sortable list
            makeSortable(listSorter.list);
        }       
    }   
    
    var listSorter = {};

//]]>   
</script>

<h1><?php echo html_escape($exhibitTitle); ?></h1>

<div id="primary">
    <div id="exhibits-breadcrumb">
        <a href="<?php echo html_escape(uri('exhibits')); ?>">Exhibits</a> &gt; <?php echo html_escape($actionName . ' Exhibit'); ?>
    </div>

<form id="exhibit-metadata-form" method="post" class="exhibit-builder">

    <fieldset>
        <legend>Exhibit Metadata</legend>
        <?php echo flash();?>
    <div class="field">
    <?php echo text(array('name'=>'title', 'class'=>'textinput', 'id'=>'title'), $exhibit->title, 'Exhibit Title'); ?>
    <?php echo form_error('title'); ?>
    </div>
    <div class="field"><?php echo text(array('name'=>'slug', 'id'=>'slug', 'class'=>'textinput'), $exhibit->slug, 'Exhibit Slug (no spaces or special characters)'); ?>
    <?php echo form_error('slug'); ?>
    </div>
    <div class="field"><?php echo text(array('name'=>'credits', 'id'=>'credits', 'class'=>'textinput'), $exhibit->credits,'Exhibit Credits'); ?></div>
    <div class="field"><?php echo textarea(array('name'=>'description', 'id'=>'description', 'class'=>'textinput','rows'=>'10','cols'=>'40'), $exhibit->description, 'Exhibit Description'); ?></div>   
    <?php $exhibitTagList = join(', ', pluck('name', $exhibit->Tags)); ?>
    <div class="field"><?php echo text(array('name'=>'tags', 'id'=>'tags', 'class'=>'textinput'), $exhibitTagList, 'Exhibit Tags'); ?></div>
    <div class="field">
        <label for="featured">Exhibit is featured:</label>
        <div class="radio"><?php echo radio(array('name'=>'featured', 'id'=>'featured'), array('0'=>'No','1'=>'Yes'), $exhibit->featured); ?></div>
    </div>
    
    <div class="field">
        <label for="featured">Exhibit is public:</label>
        <div class="radio"><?php echo radio(array('name'=>'public', 'id'=>'public'), array('0'=>'No','1'=>'Yes'), $exhibit->public); ?></div>
    </div>
        <div class="field">
            <label for="theme">Exhibit Theme</label>            
            <?php $values = array('' => 'Current Public Theme') + exhibit_builder_get_ex_themes(); ?>
            <div class="select"><?php echo __v()->formSelect('theme', $exhibit->theme, array('id'=>'theme'), $values); ?></div>
        </div>
        </fieldset>
    <fieldset>
        <legend>Exhibit Sections</legend>
        
        <div id="section-list-container">
            <?php if (!$exhibit->Sections): ?>
                <p>There are no sections.</p>
            <?php else: ?>
            <ol id="section-list">
                <?php common('section-list', compact('exhibit'), 'exhibits'); ?>
            </ol>
            <?php endif;?>
        </div>
        </fieldset>
        
        <fieldset>
        <p><input type="submit" name="save_exhibit" id="save_exhibit" value="Save Changes" /> or 
            <input type="submit" name="add_section" value="Add Section" /> or 
            <a href="<?php echo html_escape(uri('exhibits')); ?>" class="cancel">Cancel</a></p>
        </fieldset>
</form>     
</div>
<?php foot(); ?>