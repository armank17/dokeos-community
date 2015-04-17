<?php
class application_upgrade_models_UpdateDbModel
{
   
    private $_db;
    private $_config;
    private $_debug = FALSE;
    
    public function __construct() {        
        global $_configuration;       
        $this->_config = $_configuration;
        $this->_db = new appcore_db_DB($this->_config['db_host'], $this->_config['db_user'], $this->_config['db_password']);
    }
    
    public function updateMainDbCharset() {
        $this->updateDbTablesCharset($this->_config['main_database']);
    }
    
    public function updateStatsDbCharset() {
        $this->updateDbTablesCharset($this->_config['statistics_database']);
    }
    
    public function updateUserDbCharset() {
        $this->updateDbTablesCharset($this->_config['user_personal_database']);
    }
    
    public function updateCoursesDbCharset() {
        $this->updateDbTablesCharset('prounicode_TEST1');
    }
    
    
    public function getDatabases() {
        $prefix = $this->_config['db_prefix'];
        $conn = $this->_db->dbConnection('');        
        $rows = $conn->GetAll("SHOW DATABASES LIKE '$prefix%'");
        $databases = array();        
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $dbRow = array_values($row);
                $database = Database::escape_string($dbRow[0]);
                // get database charset
                $charset = $conn->GetOne("SELECT default_character_set_name FROM information_schema.SCHEMATA S WHERE schema_name = '$database'");
                $databases[] = array('dbName' => $database, 'dbPrefix' => $prefix, 'dbCharset' => $charset);
            }
        }
        return $databases;
    }
    
    public function updateDbTablesCharset($database) {                
        
        $fromEncoding = 'latin1_swedish_ci';
        $toEncoding = 'utf8_general_ci';
        $newCollation = 'utf8';                     

        // connect to the database
        $conn = $this->_db->dbConnection($database);
        if ($conn) {
            $rows = array();            
            // First, get all rows of the tables            
            $tables = $conn->GetAll("SHOW TABLES");            
            if ($this->_debug) { echo "<p>We get the rows of ".count($tables)." tables</p>"; }
            foreach ($tables as $tableRow) {
                $tableRow = array_values($tableRow);
                $table = Database::escape_string($tableRow[0]);
                $tableRows = $conn->GetAll("SELECT * FROM `$table`");
                if (!empty($tableRows)) {
                    foreach ($tableRows as $tableRow) {
                        $rows[$table][] = $tableRow;
                    }
                }                                
            }

            // Now, We modify the charset of the database and tables
            // We modify database charset
            $sql = "ALTER DATABASE $database CHARACTER SET $newCollation COLLATE $toEncoding";
            if ($this->_debug) { echo "<p>Execute: $sql</p>"; }
            $conn->Execute($sql);    
            
            // We modify tables charset
            $indexes = array();
            foreach ($tables as $tableRow) {                
                $tableRow = array_values($tableRow);
                $table = Database::escape_string($tableRow[0]);                
               
                $sql = "ALTER TABLE `$table` DEFAULT CHARACTER SET $newCollation";
                if ($this->_debug) { echo "<p>Execute: $sql</p>"; }
                $conn->Execute($sql);
                
                // get index of the table
                $indexesRows = $conn->GetAll("SHOW INDEXES FROM `$table`");                
                if (!empty($indexesRows)) {
                    foreach ($indexesRows as $indexRow) {
                        if (strtoupper($indexRow['Key_name']) != 'PRIMARY' && $indexRow['Key_name'] != 'id') {
                            $indexes[$table]['key_name'] = $indexRow['Key_name'];
                            $indexes[$table][$indexRow['Key_name']][] = $indexRow['Column_name'];                                              
                        }
                    }                    
                }                
                
                // drop indexes                
                if (!empty($indexes[$table])) {
                    $indexesNames = array_unique(array_keys($indexes[$table]));
                    foreach ($indexesNames as $indexName) {
                        if ($indexName != 'key_name') {
                            $conn->Execute("DROP INDEX `$indexName` ON `$table`");
                        }
                    }
                }
                
                // We modify the table fields charset
                $fieldsChange = $fieldsModify = array();
                $fields = $conn->GetAll("SHOW FULL FIELDS FROM `$table`");
                if (!empty($fields)) {
                    foreach ($fields as $field) {
                       
                        if ($field['Collation'] != $fromEncoding) { continue; }                        
                        // Is the field allowed to be null?
                        $nullable = ($field['Null'] == 'YES')?' NULL ':' NOT NULL';
                        if ($field['Default'] == 'NULL') {
                            $default = " DEFAULT NULL";
                        } 
                        else if ($field['Default']!='') {
                            $default = " DEFAULT '".Database::escape_string($field['Default'])."'";
                        }
                        else {
                            $default = '';
                        }
                        // Alter field collation:
                        $fieldName = Database::escape_string($field['Field']);
                        $fieldsModify[] = "MODIFY `$fieldName` {$field['Type']} CHARACTER SET BINARY";
                        $fieldsChange[] = "CHANGE `$fieldName` `$fieldName` {$field['Type']} CHARACTER SET $newCollation COLLATE $toEncoding $nullable $default";
                    }                                                            
                }
                
                if (count($fieldsChange) > 0) {   
                    $sql = "ALTER TABLE `$table` ".implode(' , ', $fieldsModify)."";
                    if ($this->_debug) { echo "<p>Execute: $sql</p>"; }
                    $conn->Execute($sql);                   
                                        
                    $sql = "ALTER TABLE `$table` ".implode(' , ', $fieldsChange)."";
                    if ($this->_debug) { echo "<p>Execute: $sql</p>"; }
                    $conn->Execute($sql);
                }
            }
            
            // add the indexes
            $this->alterIndexes($conn, $indexes);
            // encode rows to utf8
            $this->encodeTablesRowsToUtf8($conn, $rows);            
        }
        
    }
    
    public function alterIndexes($conn, $indexes) {
        if (!empty($indexes)) {
            foreach ($indexes as $table => $index) {
                $keyName = $index['key_name'];
                if (strtoupper($keyName) == 'PRIMARY') { continue; }
                $sql  = "ALTER TABLE `$table` ADD INDEX `$keyName` (";
                if (count($index[$keyName]) > 1) {
                    $length = ceil(100 / count($index[$keyName]));
                    foreach ($index[$keyName] as &$keyValue) {  
                        $field = $conn->GetRow("SHOW FULL FIELDS FROM `$table` WHERE Field = '$keyValue'");
                        if (stripos($field['Type'], 'VARCHAR') !== FALSE) {
                            $keyValue = $keyValue."($length)";
                        }
                    }
                    $sql .= implode(',', $index[$keyName]);
                }
                else {
                    $sql .= $index[$keyName][0];
                }
                $sql .= ")";
                if ($this->_debug) { echo "<p>Execute: $sql</p>"; }
                $conn->Execute($sql);
            }
         }
    }
    
    public function encodeTablesRowsToUtf8($conn, $tablesRows) {        
        if (!empty($tablesRows)) {
            foreach ($tablesRows as $table => $rows) {
                // We truncate the table                
                $sql = "TRUNCATE `$table`";
                if ($this->_debug) { echo "<p>Execute: $sql</p>"; }
                $conn->Execute($sql);
                foreach ($rows as $row) {
                    // utf8 encode the field value
                    foreach ($row as &$value) {  
                        $value = $this->decodeHtmlEnt($value);                   
                    }                    
                    $insertSQL = $conn->AutoExecute($table, $row, 'INSERT');
                }
            }
        }        
    }

    public function decodeHtmlEnt($str) {
        $str = str_replace(array("a&#778;"), array("&aring;"), $str);
        $ret = html_entity_decode($str, ENT_COMPAT, 'ISO-8859-1');
        $p2 = -1;
        for(;;) {
            $p = strpos($ret, '&#', $p2+1);
            if ($p === FALSE)
                break;
            $p2 = strpos($ret, ';', $p);
            if ($p2 === FALSE)
                break;

            if (substr($ret, $p+2, 1) == 'x')
                $char = hexdec(substr($ret, $p+3, $p2-$p-3));
            else
                $char = intval(substr($ret, $p+2, $p2-$p-2));
            if (mb_detect_encoding($str) != 'UTF-8') {
                    $newchar = iconv(
                        'UCS-4', 'UTF-8',
                        chr(($char>>24)&0xFF).chr(($char>>16)&0xFF).chr(($char>>8)&0xFF).chr($char&0xFF)
                    );
                    $ret = substr_replace($ret, $newchar, $p, 1+$p2-$p);
                    $p2 = $p + strlen($newchar);
            }
        }
        return $ret;
    }
    public function vd($string) {
        echo '<pre>';
        print_r($string);
        echo '</pre>';
    }
    
}