<?php

if ($argc < 2) {
    echo "\n\033[31m[ERRO]\033[0m Model name is required.\n";
    echo "USE: composer make:model ModelName \n\n";
    exit;
}

$nameParts = array_slice($argv, 1);
$formattedName = implode('', $nameParts);
$modelName = $formattedName . 'Model';
$filename = "{$modelName}.php";

$directory = __DIR__ . '/../Models';
$filepath = "$directory/$filename";

if (file_exists($filepath)){
    echo "\n\033[31m[ERRO]\033[0m Model already exists.\n";
    exit;
}

if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

$table = '$table = "' . strtolower($formattedName) . '"';
$primaryKey = '$primaryKey';

$template = <<<PHP
<?php

namespace Models;

use Helpers\Model;

class $modelName extends Model {

    protected static string $table;
    protected static string $primaryKey = "id"; 

};
PHP;

file_put_contents($filepath, $template);
$message = "app/Models/$filename";
echo "\n\033[32m[MODEL CREATED]\033[0m {$message}\n\n";
