<?php
$icon = $args['icon'];

switch($icon) {
    case 'close':
        printf('
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 15">
  <path fill-rule="evenodd" d="M0 1.574 1.574 0 7.5 5.926 13.426 0 15 1.574 9.074 7.5 15 13.426 13.426 15 7.5 9.074 1.574 15 0 13.426 5.926 7.5Z"/>
</svg>
        ');
        break;
}