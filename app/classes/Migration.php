<?php 
namespace app\classes;

use app\classes\Schema;

class Migration{
    private static $db = null;
    public static function upTables() {

        $directory = 'database/migrations';

        if (!file_exists($directory)) {
            
            echo "No migrations found\n";
            return;
        }
    
        $files = glob($directory . "/*.php");

        $fkArray = [];

        foreach ($files as $file) {
    
            $fileName = basename($file, '.php');

            if (strpos($fileName, '-') !== false) {

                $parts = explode('-', $fileName);

                $className = end($parts); 

            } else {

                $className = $fileName;

            }

            $newClassName = 'database\\migrations\\' . $className;
    
            require_once $file;
    
            if (!class_exists( $newClassName )) {

                echo "Class $newClassName  not found in $file\n";

                continue;
            }
    
            if (!method_exists( $newClassName , 'up')) {

                echo "Class $newClassName  does not have an up method\n";

                continue;
            }

            $itemClass = $newClassName::up(new Schema());

            $item = $itemClass->create();

            if ($item === false) {
                continue;
            }
            
            echo "$className migrated successfully\n";

            // we use foreign_array to finsh the table
            if(isset($item["foreign_array"])) {

                $query = sprintf("ALTER TABLE %s \n", $item["table_name"]);

                foreach ($item["foreign_array"] as $key => $value) {
                    //checking here if it is the last key 

                    $query .= sprintf("ADD CONSTRAINT %s %s ", $item["fk"][$key] ,$value ) . (count($item["foreign_array"]) > $key + 1 ? " , \n" : "\n");

                }

                $fkArray[] =  $query ;
                
            }

        }

        if(count($fkArray) > 0) {
            
            if(self::$db == null) {

                self::$db  = Database::connect();
    
            }

            foreach ($fkArray as $key => $value) {

                self::$db->exec($value);

            }

            self::CloseConnection() ;

        }
    }
    private static function CloseConnection(){
        self::$db = null;
    }

    public static function downTables() {

        $directory = 'database/migrations';

        if (!file_exists($directory)) {
            
            echo "No migrations found\n";
            return;
        }
    
        $files = glob($directory . "/*.php");
        
        $fkArray = [];

        $classNameArray = [];

        $classes = [];

        foreach ($files as $file) {
    
            $fileName = basename($file, '.php');
            if (strpos($fileName, '-') !== false) {
                $parts = explode('-', $fileName);
                $className = end($parts); 
            } else {
                $className = $fileName;
            }

            $newClassName = 'database\\migrations\\' . $className;
    
            require_once $file;
    
            if (!class_exists( $newClassName )) {

                echo "Class $newClassName  not found in $file\n";
                continue;
            
            }
            
            if (!method_exists( $newClassName , 'up')) {

                echo "Class $newClassName  does not have an up method\n";
                continue;
                
            }

            if (!method_exists( $newClassName , 'down')) {

                echo "Class $newClassName  does not have an down method\n";
                continue;

            }

            $classes[]  = [
                "class" => $newClassName,
                "name" => $className
            ] ;

            $schema = $newClassName::up(new Schema());

            $columns = $schema->returnSchema();

            $table_name = $schema->returnTableName();
            
            if(self::$db == null) {

                self::$db  = Database::connect();
            }

           foreach( $columns as $key => $value) {

                if(isset($value["foreign_key"])){

                    
                    $fkName = "fk_" . explode("_", $value["foreign_key"])[0];
            
                    $query = "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = ? AND CONSTRAINT_NAME = ?";

                    $stmt = self::$db->prepare($query);

                    $stmt->execute([$table_name, $fkName]);
                    
                    if ($stmt->fetch()) {

                        $query = sprintf("ALTER TABLE %s DROP CONSTRAINT %s", $table_name, $fkName);
                        self::$db->exec($query);
                      
                    } 
                    
                
                }
                
            }
            
            self::CloseConnection();

        }

        if(count($fkArray) > 0) {
            
            foreach ($fkArray as $key => $array) {
               
                foreach ($array as $array_key => $value) {

                    if(isset($value["foreign_key"])){
                        echo $classNameArray[$key];
                        $fkName = "fk_" . explode("_", $value["foreign_key"])[0];
                      
                    }

                }
            }
        }

        if(count($classes) > 0) {

            foreach ($classes as $values) {
    
                $schema = $values["class"]::down(new Schema());
    
                $schema->Drop();
    
                echo "$values[name] deleted successfully \n";
    
            }
        }

    }


}
?>