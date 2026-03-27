<?php
$output = shell_exec('php artisan 2>&1');
file_put_contents('err.log', $output);
