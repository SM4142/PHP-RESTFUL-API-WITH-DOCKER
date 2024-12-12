<?php 
namespace app\classes;

use app\classes\Schema;

class Migration{
   
    public static function upTables() {

        $directory = 'database/migrations';

        if (!file_exists($directory)) {
            echo "No migrations found\n";
            return;
        }
    
        $files = glob($directory . "/*.php");

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

            if ($item === true) {
                echo "$className migrated successfully\n";
            }
          
        }
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