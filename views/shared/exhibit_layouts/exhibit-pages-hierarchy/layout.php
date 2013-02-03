<div class="text-full">
    <div class="primary">
        <div class="exhibit-page-text">
            <?php echo exhibit_builder_page_text(1); ?>
        </div>
    </div>
    <div class="exhibit-page-child list">
        <ul>
            <?php
            foreach (get_current_record('exhibit_page')->getChildPages() as $childrenPage) {
                $childrenPageUri = html_escape(exhibit_builder_exhibit_uri(null, $childrenPage));
                echo "<li><a href='$childrenPageUri'>$childrenPage->title</a></li>";
            }
            ?>
        </ul>
    </div>
</div>
