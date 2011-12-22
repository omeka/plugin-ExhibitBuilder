<?php if ($exhibit->Sections): ?>
    <?php foreach($exhibit->Sections as $key => $exhibitSection ): ?>
    	<li id="section_<?php echo html_escape($exhibitSection->id); ?>" class="exhibit-section-item">
    		<div class="section-info">

        		    <span class="input"><?php echo text(array('name'=>"Sections[$key][order]",'size'=>2,'class'=>'order-input', 'id' => 'section-' . $exhibitSection->id . '-order'), $exhibitSection->order); ?></span>
        		    <h3 class="section-title"><?php echo html_escape(snippet($exhibitSection->title, 0, 40, '')); ?></h3>
        		    <?php echo $exhibitSection->description; ?>
        		    <div class="section-actions">
        		        <span class="section-edit"><a href="<?php echo html_escape(uri('exhibits/edit-section/'.$exhibitSection->id)); ?>" class="edit"><?php echo __('Edit Section'); ?></a></span>
            		    <span class="section-delete"><a href="<?php echo html_escape(uri('exhibits/delete-section/'.$exhibitSection->id)); ?>" class="delete-section"><?php echo __('Delete Section'); ?></a></span>
        		    </div>

    		</div>
    		<div class="section-pages-info">
    		<h4>Pages:</h4>
            <ul class="page-list"><?php
                    if (exhibit_builder_section_has_pages($exhibitSection)):
                        $fromExhibitPage = true;
                        common('page-list', compact('exhibitSection', 'fromExhibitPage'), 'exhibits');
                    endif;
            ?></ul>
			
			<div class="section-actions">
			 <span class="page-add"><a class="add" href="<?php echo html_escape(uri('exhibits/add-page/'.$exhibitSection->id)); ?>"><?php echo __('Add a Page'); ?></a></span>
			</div>
        	</div>
    	</li>
    <?php endforeach; ?>    
<?php endif; ?>
