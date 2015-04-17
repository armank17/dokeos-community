<?php

class appcore_db_DB {

    private static $conex = NULL;
    private $_host;
    private $_user;
    private $_password;
    private $_motor;    
        
    public function __construct($dbHost, $dbUser, $dbPwd, $motor = 'mysql') {        
        $this->_host = $dbHost;
        $this->_user = $dbUser;
        $this->_password = $dbPwd;
        $this->_motor = $motor;      
    }

    public function dbConnection($dbName) {
        $conn = NULL;
        try {                  
            $conn = NewADOConnection($this->_motor);
            $conn->Connect($this->_host, $this->_user, $this->_password, $dbName);
            $conn->EXECUTE("set names 'utf8'"); 
            $conn->setFetchMode(ADODB_FETCH_ASSOC);
            ADODB_Active_Record::SetDatabaseAdapter($conn);
        } 
        catch (ADODB_Exception $e) {
            echo "Error to connect to database : " . $e->getMessage();
            error_log("Error to connect to database : " . $e->getMessage());
        } 
            catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            error_log("Error: " . $e->getMessage());
        }
        return $conn;
    }
    
    public static function conn() {
        self::setValue();
        $returnValue = NULL;

        if (!self::$conex) {
            try {
                $dbHost = VAR_HOST;
                $port = "";
                if (strpos(VAR_HOST, ':') !== FALSE ) {
                  list($dbHost, $port) = explode(":", VAR_HOST);
                  if (!empty($port)) {
                    $port = ";port=$port";
                  }
                }
                $driver = VAR_MOTOR.':host='.$dbHost.$port; 
                self::$conex =& NewADOConnection('pdo');
                self::$conex->Connect($driver,VAR_USER,VAR_PASSWORD,VAR_DATA_BASE);
                self::$conex->EXECUTE("set names 'utf8'"); 
                self::$conex->setFetchMode(ADODB_FETCH_ASSOC);
                ADODB_Active_Record::SetDatabaseAdapter(self::$conex);
            } catch (ADODB_Exception $e) {
                echo "Error to connect to database : " . $e->getMessage();
                error_log("Error to connect to database : " . $e->getMessage());
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
                error_log("Error: " . $e->getMessage());
            }
        }

        $returnValue = self::$conex;

        return $returnValue;
    }
    
    public static function setValue()
    {
        if (!self::$conex) {
            global $_configuration;
            //CONNECT MYSQL
            define("VAR_HOST", $_configuration['db_host']);
            define("VAR_USER", $_configuration['db_user']);
            define("VAR_PASSWORD", $_configuration['db_password']);
            define("VAR_DATA_BASE", $_configuration['main_database']);
            define("VAR_MOTOR", "mysql");
        }
    }

}