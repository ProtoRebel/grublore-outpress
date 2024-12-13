<?php
$controlSelectBack = '<button id="control-select-back" type="button" class="dark"><span>Back</span></button>';
$controlSelectInfo = '<p><em>For the meal</em><strong></strong></p>';
$controlSelect = sprintf('<nav id="control-select">%s%s</nav>', $controlSelectBack, $controlSelectInfo);
$controlPreviewBack = '<button id="control-preview-back" class="dark" type="button"><span>Back</span></button>';
$controlPreviewAdd = '<button id="control-preview-add" type="button"><span>Add to Meal</span></button>';
$controlPreviewAdded = '<p id="control-preview-added"><em>Added to</em><strong></strong></p>';
$controlPreview = sprintf('<nav id="control-preview">%s%s%s</nav>', $controlPreviewBack, $controlPreviewAdd, $controlPreviewAdded);
printf('<aside id="control">%s%s</aside>', $controlSelect, $controlPreview);
?>