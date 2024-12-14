<?php

$alertMessage = '<p id="alert-message"></p><p>This cannot be undone.</p>';
$alertConfirm = '<button id="alert-confirm" type="button" class="danger"></button>';
$alertCancel = sprintf('<button id="alert-cancel" type="button" class="clear">%s<em></em></button>', util_icon('cancel'));

printf('<aside id="alert"><header><h1></h1></header>%s<footer>%s</footer></aside>%s', $alertMessage, $alertConfirm, $alertCancel);
?>