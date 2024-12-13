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
    case 'pencil':
        printf('
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path d="M20 3.9 16.1 0l-2.7 2.7 3.9 3.9ZM4 16l4.293-.39 7.387-7.387-3.9-3.9-7.39 7.384Z"/>
    <path fill-rule="evenodd" d="M13 20H0v-2h13Z"/>
</svg>
        ');
        break;
    case 'cancel':
        printf('
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 15">
    <path d="M7.5 0A7.5 7.5 0 1 0 15 7.5 7.522 7.522 0 0 0 7.5 0Zm0 1.875a5.611 5.611 0 0 1 3.281 1.05l-7.856 7.856A5.632 5.632 0 0 1 7.5 1.875Zm4.575 2.344a5.632 5.632 0 0 1-7.856 7.856Z"/>
</svg>
        ');
        break;
}