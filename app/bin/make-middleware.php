<?php

if ($argc < 2) {
    echo "\n\033[31m[ERRO]\033[0m Middleware name is required.\n";
    echo "USE: composer make:middleware Middleware \n\n";
    exit;
}

$nameParts = array_slice($argv, 1);
$formattedName = implode('', $nameParts);
$middlewareName = $formattedName . 'Middleware';
$filename = "$middlewareName.php";

$directory = __DIR__ . '/../Middlewares';
$filepath = "$directory/$filename";

if (file_exists($filepath)){
    echo "\n\033[31m[ERRO]\033[0m Middleware already exists.\n";
    exit;
}

if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}
$template = <<<PHP
<?php

namespace Middlewares;

class $middlewareName {

    public function handle(\$request){
    
    }

};
PHP;

file_put_contents($filepath, $template);
$message = "app/Middlewares/$filename";
echo "\n\033[32m[MIDDLEWARE CREATED]\033[0m $message\n\n";
