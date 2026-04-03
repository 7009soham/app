<?php file_put_contents('composer_error_out.txt', shell_exec('php composer.phar install 2>&1')); ?>
