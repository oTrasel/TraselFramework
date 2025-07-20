<?php

if ($argc < 2) {
    echo "Nome da migration é obrigatório.\n";
    echo "Uso: php make-migration.php NomeDaMigration [descricao_opcional]\n";
    exit(1);
}

$nameParts = array_slice($argv, 1);
$formattedName = implode('', $nameParts);
$timestamp = date('dmY_His');
$filename = "{$timestamp}_{$formattedName}.php";

$directory = __DIR__ . '/app/database/migrations';
$filepath = "$directory/$filename";

if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

$template = <<<PHP
<?php

use Helpers\Database;

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
echo "Migration criada: app/database/migrations/$filename\n";
