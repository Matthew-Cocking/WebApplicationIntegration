<?php
class pdoDB {
  private static $dbConnection = null;
 
  private function __construct() {
  }
  private function __clone() {
  }
 
  /**
   * Return DB connection or create initial connection
   *
   * @return object (PDO)
   */
  public static function getConnection() {

    $dbname = ApplicationRegistry::getDBName();

    if ( !self::$dbConnection ) {
        try {           
          self::$dbConnection = new PDO($dbname);
					self::$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         }
         catch( PDOException $e ) {
            echo $e->getMessage();
         }
    }
    return self::$dbConnection;
  }
 
}
?>
