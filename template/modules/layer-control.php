<?php
$controlSelectBack = '<button id="control-select-back"><span>Back to Meal</span></button>';
$controlSelect = sprintf('<nav id="control-select">%s</nav>', $controlSelectBack);
$controlPreviewBack = '<button id="control-preview-back" class="dark"><span>Back</span></button>';
$controlPreviewAdd = '<button id="control-preview-add"><span>Add to Meal</span></button>';
$controlPreviewAdded = '<p id="control-preview-added"><em>Added to</em><strong></strong></p>';
$controlPreview = sprintf('<nav id="control-preview">%s%s%s</nav>', $controlPreviewBack, $controlPreviewAdd, $controlPreviewAdded);
printf('<aside id="control">%s%s</aside>', $controlSelect, $controlPreview);
?>