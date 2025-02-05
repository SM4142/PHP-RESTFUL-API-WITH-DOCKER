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

                $create_table_sql = "ALTER TABLE " . " " . $item["table_name"] . " \n";

                foreach ($item["foreign_array"] as $key => $value) {
                    //checking here if it is the last key 
                    explode("-", $value);
                    $create_table_sql .= " ADD CONSTRAINT ". $item["fk"][$key] . " ". $value . (count($item["foreign_array"]) > $key + 1 ? " , \n" : "\n");

                }
                
                $fkArray[] = $create_table_sql;

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
        
        $files  = array_reverse($files);
        
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
    
            if (!method_exists( $newClassName , 'down')) {
                echo "Class $newClassName  does not have an down method\n";
                continue;
            }

            $itemClass = $newClassName::down(new Schema());

            $item = $itemClass->Drop();
            
            if ($item === true) {
                echo "$className deleted successfully\n";
            }
        }
    }


}
?>