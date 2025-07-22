<?php

if ($argc < 2) {
    echo "\n\033[31m[ERRO]\033[0m Migration name is required.\n";
    echo "USE: composer make:migration MigrationName \n\n";
    exit;
}

$nameParts = array_slice($argv, 1);
$formattedName = implode('', $nameParts);
$timestamp = date('dmY_His');
$filename = "{$timestamp}_{$formattedName}.php";

$directory = __DIR__ . '/../Database/migrations';
$filepath = "$directory/$filename";

if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

$template = <<<PHP
<?php

return new class {
    public function up(PDO \$pdo)
    {
        //  implement up()
    }

    public function down(PDO \$pdo)
    {
        //  implement down()
    }
};
PHP;

file_put_contents($filepath, $template);
$message = "app/Database/migrations/$filename";
echo "\n\033[32m[MIGRATION CREATED]\033[0m {$message}\n\n";
