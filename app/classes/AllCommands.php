<?php 
namespace app\classes;

use DateTime;

class AllCommands{
 
    public static function Create(array $commands){
       switch ($commands[1]) {
           case 'create-controller':
                if(! isset($commands[2])){
                    echo "Write a controller name\n";
                }
                AllCommands::CreateController($commands[2]);
                break;
            case 'create-model':
                if(! isset($commands[2])){
                    echo "Write a migration name\n";
                }
                AllCommands::CreateModel($commands[2]);
                break;
            case 'create-migration':
                if(! isset($commands[2])){
                    echo "Write a migration name\n";
                }
                AllCommands::CreateMigration($commands[2]);
                break;
                break;
            default:
                echo "Unknown command\n";
                break;
       }
    }
    private static  function CreateController(string $name){

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
        "use app\\classes\\Schema;\n\n" .
        "class $name\n" .
        "{\n" .
        "    protected static \$tableName = '$newName[0]';\n" .
        "    public static function up(Schema \$colum)\n" .
        "    {\n" .
        "        \$colum->Id();\n" .
        "        return \$colum->GetSchema();\n" .
        "    }\n" .
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
        "use app\model\Model;\n\n" .
        "class $name extends Model\n " .
        "{\n" .
        "     protected  static \$table = '$name';\n" .
        "}\n";

        file_put_contents($controllerFileName, $content);

        echo "Model $name created\n";
        AllCommands::CreateMigration($name . "Migration");
    }

}
?>