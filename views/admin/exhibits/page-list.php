<?php
echo $this->exhibitPageEditTree($exhibit);
echo $this->formHidden('pages-hidden');
echo $this->formHidden('pages-delete-hidden');
?>

<script type="text/javascript">
Omeka.addReadyCallback(Omeka.ExhibitBuilder.enableSorting);
Omeka.manageDrawers('#page-list', '.page');
Omeka.addReadyCallback(Omeka.ExhibitBuilder.setUpFormSubmission);
</script>
