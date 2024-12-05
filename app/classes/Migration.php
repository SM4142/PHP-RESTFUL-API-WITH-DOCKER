<?php 
namespace app\classes;

use app\classes\Schema;

class Migration{
    

    protected static $db = null;

    private static  function closeConnection() {
        self::$db = null;
    }
    
    public static function up() {
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
            $newClassName ::up(new Schema());
            echo "$className migrated successfully\n";
        }
    }
    

    public static function down() {
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
    
            if (!method_exists( $newClassName , 'up')) {
                echo "Class $newClassName  does not have an up method\n";
                continue;
            }
            $newClassName ::down(new Schema());
            echo "$className deleted successfully\n";
        }
    }
}
?>