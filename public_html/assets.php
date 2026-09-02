<?php
$path = dirname(getcwd());
$target = $path.'/backend/dating-platform/storage/app/assets';
$shortcut = 'assets'; 

symlink($target, $shortcut);
echo 'Done'; 
?>