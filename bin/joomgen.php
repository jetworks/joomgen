<?php
    if(basename(getcwd()) == 'bin')
        chdir('..');
    include_once('lib'.DIRECTORY_SEPARATOR.'generator.php');
    $config = prepare_config();
    $models = prepare_models();
    $views = prepare_frontend();
    generate_output($config, $models, $views);
?>