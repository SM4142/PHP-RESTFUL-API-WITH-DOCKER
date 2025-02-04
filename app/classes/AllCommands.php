<?php 
namespace app\classes;

use database\migrations\UserMigration;
use DateTime;

class AllCommands{

 
    public static function Create(array $commands){
       $result = match ($commands[1]) {
         'create::controller'=> 
         (! isset($commands[2])) ? "Write a controller name\n" : AllCommands::CreateController($commands[2]),
         'create::model'=> 
         (! isset($commands[2])) ? "Write a model name\n" : AllCommands::CreateModel($commands[2]),
         'create::migration'=> 
         (! isset($commands[2])) ? "Write a migration name\n" : AllCommands::CreateMigration($commands[2]),
         'migrate::up'=> AllCommands::MigrateUp(),
         'migrate::down'=> AllCommands::MigrateDown(),
         'migrate::restart'=> AllCommands::MigrateRestart(),
         default => 'unknown method ',
       };
       echo $result ;
    }
    private static  function CreateController(string $name ){

        $directory = 'app/controllers';
        if (!file_exists($directory)) {
            mkdir($directory);
        }

        $controllerFileName = $directory . "/" . $name . '.php';
        if (file_exists($controllerFileName)) {
            echo "Controller $name already exists\n";
            return;
        }

        $content = "<?php\n\n" .
        "namespace app\\controllers;\n\n" .
        "use app\\classes\\Request;\n" .
        "use app\\classes\\Response;\n\n" .
        "class $name\n" .
        "{\n" .
        "    public function index()\n" .
        "    {\n" .
        "        Response::sendResponse(['message' => 'Hello, World!']);\n" .
        "    }\n" .
        "}\n";

        file_put_contents($controllerFileName, $content);

        echo "Controller $name created\n";
    }
    private static function CreateMigration(string $name){

        $directory = 'database/migrations';
        if (!file_exists($directory)) {
            mkdir($directory);
        }

        $date = new DateTime();
        $date = $date->format('d-m-Y-H-i');
        $controllerFileName = $directory . "/" . $date . '-' . $name . '.php';

        if (file_exists($controllerFileName)) {
            echo "Migration $name already exists\n";
            return;
        }

        $newName[] = $name;
        if(! strpos($name, 'Migration') === false){
            $newName = explode('Migration', $name);
        }

        $content = "<?php\n\n" .
        "namespace database\\migrations;\n\n" .
        "use app\\classes\\Schema;\n" .
        "use app\classes\Migration;\n\n".
        "class $name extends Migration \n" .
        "{\n" .
        "    protected static \$tableName = '$newName[0]';\n" .
        "    public static function up(Schema \$colum) {\n" .
        "        \$colum->Table(self::\$tableName);\n".
        "        \$colum->Id();\n" .
        "        \$colum->SetTimestamps(); \n" .
        "        return \$colum;\n" .
        "    }\n\n" .
        "    public static function down(Schema \$colum) : Schema {\n" .
        "       \$colum->Table(self::\$tableName);\n".
        "       return \$colum;\n" .
        "    }\n\n" .
        "}\n";

        file_put_contents($controllerFileName, $content);

        echo "Migaration $name created\n";
    }
    private static function CreateModel(string $name){

        $directory = 'app/models';
        if (!file_exists($directory)) {
            mkdir($directory);
        }

        $controllerFileName = $directory . "/" . $name . '.php';
        
        if (file_exists($controllerFileName)) {
            echo "Model $name already exists\n";
            AllCommands::CreateMigration($name . "Migration");
            return;
        }


        $content = "<?php\n\n" .
        "namespace app\\models;\n\n" .
        "use app\classes\Model;\n\n" .
        "class $name extends Model  {\n\n " .
        "     protected  static \$table = '$name';\n\n" .
        "}\n";

        file_put_contents($controllerFileName, $content);

        echo "Model $name created\n";
        AllCommands::CreateMigration($name . "Migration");
    }
    public static function MigrateUp() {
        Migration::upTables();
    }
    
    
    public static function MigrateDown(){
       Migration::downTables();
    }
    public static function MigrateRestart(){
        AllCommands::MigrateDown();
        AllCommands::MigrateUp();
    }

}
?>