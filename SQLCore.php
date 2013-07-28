<?php
/**
 * Group          HT Group
 * Author 	    Igor Gayduk
 * Datecreate   5.12.2012
 * Timecreate:  20:05
 * Daterewrite  28.07.2013
 * Timerewrite  22:40
 * Codefile     UTF-8
 * Copyright    HT Group 2012-2013
 * Name		    SQLCore
 * Infofile     Class for database operations
 * Version      1.0.5 RC1
 */

class SQLCore extends \PDO{

    private $engine;
    private $host;
    private $port;
    private $user;
    private $password;
    private $dbname;
    private $charset;
    private $tablePrefix;

    private $pdo;
    private $settings;
    private $settingsFile="settings.ini.php";
    private $bConnected = false;
    private $sQuery;
    private $bindParam;

    public $table;
    public $str_join=false;
    public $expression="*";
    public $where;
    public $group;
    public $order;
    public $limit;
    public $exit = false;
    public $sql_cache = false;
    public $values;

    public $type_array_result = true;
    protected $transactionCounter = 0;
    private $level = 0;

    /**
     * Constructed, Initialize Params Class
     */

    /**
     * @param   array $localArgs
     * @job     initialize function SetArguments, Connect
     */
    function __construct(array $localArgs = array())
    {
        $this->SetArguments($localArgs);
        $this->Connect();
    }

    function __destruct() {
        $this->CloseConnect();
    }

    /**
     * @param array $localArgs
     * @job connected to base
     * @get_param where create Object and get in Array
     * @get_param from $settingsFile
     * @default $settingsFile = "settings.ini.php"
     */
    protected function Connect(array $localArgs = array()){
        $this->settingsFile = (isset($localArgs["settingsFile"])) ? $localArgs["settingsFile"] : $this->settingsFile;
        $this->settings = parse_ini_file($this->settingsFile);
        foreach($this->settings as $key=>$value) $this->$key = $value;

        $this->port = (!empty($this->port)) ? ';port='.$this->port : $this->port;

        $dsn = $this->engine.':dbname='.$this->dbname.';host='.$this->host.$this->port;
        try
        {
            $this->pdo = new PDO($dsn, $this->user, $this->password, array(parent::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \''.$this->charset.'\''));

            $this->pdo->setAttribute(parent::ATTR_ERRMODE, parent::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(parent::ATTR_EMULATE_PREPARES, false);
            //$this->pdo->query ( 'SET character_set_connection = '.$this->charset );
            //$this->pdo->query ( 'SET character_set_client = '.$this->charset );
            //$this->pdo->query ( 'SET character_set_results = '.$this->charset );
            $this->bConnected = true;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            die();
        }
    }

    /**
     * OPERATION ROWS BASE
     */

    /**
     * @param array $localArgs
     * @return object
     */
    public function SelectQuery(array $localArgs = array()){
        try
        {
            $this->SetArguments($localArgs);

            $this->table = (!empty($this->table)) ? $this->table : false;
            $this->expression = (is_array($this->expression)) ? implode(",",$this->expression) : (!empty($this->expression)) ? $this->expression : "*";
            $this->where = (!empty($this->where)) ? "WHERE ".$this->where : false;
            $this->group = (!empty($this->group)) ? "GROUP BY ".$this->group : false;
            $this->limit = (!empty($this->limit)) ? "LIMIT ".$this->limit : false;
            $this->sql_cache = ($this->sql_cache) ? "SQL_CACHE" : false;

            if(!empty($this->order)){
                $this->order = "ORDER BY ".$this->order;
                $this->str_join = "STRAIGHT_JOIN";
            }else $this->order = $this->str_join = false;


            $query = "SELECT ".$this->str_join." ".$this->sql_cache." ".$this->expression." FROM `".$this->tablePrefix.$this->table."`
            ".$this->where." ".$this->group." ".$this->order." ".$this->limit;
            return $this->Init($query);
        }
        catch (PDOException $e)
        {
            echo $e->getMessage(); die();
        }
    }

    /**
     * @param array $localArgs
     * @param array $parameters
     * @return mixed
     */
    public function OneResult(array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $this->bindMore($parameters);
        return $this->SelectQuery($localArgs)->fetch();
    }

    /**
     * @param array $localArgs
     * @param array $parameters
     * @return mixed
     */
    public function ArrayResults(array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $this->bindMore($parameters);
        return $this->SelectQuery($localArgs)->fetchAll();
    }

    /**
     * @param array $localArgs
     * @param array $parameters
     * @return int
     */
    public function CountRow(array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $this->bindMore($parameters);
        return $this->SelectQuery($localArgs)->rowCount();
    }

    /**
     * @param array $localArgs
     * @param array $parameters
     * @return bool
     */
    public function UpdateRow(array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $this->bindMore($parameters);
        $values_update = "";
        foreach($this->values as $key=>$value){
            $value = (preg_match("/.*?(\\(.*\\))/is",$value)) ? $value : "'{$value}'";
            $values_update .= "`{$key}` = {$value},";
        }
        $values_update = substr($values_update,0,-1);
        try
        {
            $this->where = (!empty($this->where)) ? "WHERE ".preg_replace("/,/"," AND ",$this->where) : false;
            $this->order = (!empty($this->order)) ? "ORDER BY ".$this->order : false;
            $this->limit = (!empty($this->limit)) ? "LIMIT ".$this->limit : false;
            $this->sQuery = "UPDATE `".$this->tablePrefix.$this->table."` SET {$values_update} {$this->where} {$this->order} {$this->limit}";
            if($this->exit) $this->exitCode();
            else{
                if($this->sQuery == $this->Init($this->sQuery)->queryString){
                    $this->OptimizeTable();
                    return true;
                }
                else false;
            }
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            die();
        }
    }

    /**
     * @param array $localArgs
     * @param array $parameters
     * @return int (last id)
     */
    public function InsertRow(array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $this->bindMore($parameters);
        $this->level=0;
        $this->levelArray($this->values);

        $values = $keys = "";
        if($this->level){
            foreach($this->values as $value_level_one){
                $values .= "(";
                foreach($value_level_one as $value_level_two){
                    $value_level_two = (preg_match("/.*?(\\(.*\\))/is",$value_level_two)) ? $value_level_two : "'{$value_level_two}'";
                    $values .= $value_level_two.",";
                }
                $values = substr($values,0,-1)."),";
            }
            $values = substr($values,0,-1);
            $keys = implode(",",array_keys(current($this->values)));
        }else{
            $values = "(";
            foreach($this->values as $value_level_one){
                $value_level_one = (preg_match("/.*?(\\(.*\\))/is",$value_level_one)) ? $value_level_one : "'{$value_level_one}'";
                $values .= $value_level_one.",";
            }
            $values = substr($values,0,-1).")";
            $keys = implode(",",array_keys($this->values));
        }
        try
        {
            $this->sQuery = "INSERT INTO ".$this->tablePrefix.$this->table."({$keys}) VALUES {$values}";
            if($this->exit) $this->exitCode();
            else{
                if($this->sQuery == $this->Init($this->sQuery)->queryString){
                    $this->OptimizeTable();
                    return $this->pdo->lastInsertId();
                }
                else false;
            }
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            die();
        }
    }

    /**
     * @param array $localArgs
     * @param array $parameters
     * @return bool
     */
    public function DeleteRow(array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $this->bindMore($parameters);
        try{
            $this->where = (!empty($this->where)) ? "WHERE ".preg_replace("/,/"," AND ",$this->where) : false;
            $this->order = (!empty($this->order)) ? "ORDER BY ".$this->order : false;
            $this->limit = (!empty($this->limit)) ? "LIMIT ".$this->limit : false;
            $this->sQuery = "DELETE FROM `".$this->tablePrefix.$this->table."` {$this->where} {$this->order} {$this->limit}";
            if($this->exit) $this->exitCode();
            else{
                if($this->sQuery == $this->Init($this->sQuery)->queryString){
                    $this->OptimizeTable();
                    return true;
                }
                else false;
            }
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            die();
        }
    }

    /**
     * @param array $localArgs
     * @param array $parameters
     * @return bool
     */
    public function HasRow(array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $temp = $this->type_array_result;
        $this->type_array_result = true;
        $this->bindMore($parameters);
        $id = $this->OneResult($localArgs, $parameters);
        $this->type_array_result = $temp;
        if(is_array($id) && current($id)!=0) return true;
        else return false;
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return mixed
     * @return_variable one row in base
     */
    public function QueryResult($query="", array $parameters = array()){
        $this->bindMore($parameters);
        return $this->Init($query)->fetch();
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return mixed
     * @return_variable all row in base
     */
    public function QueryArrayResult($query="", array $parameters = array()){
        $this->bindMore($parameters);
        return $this->Init($query)->fetchAll();
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return int
     */
    public function QueryCountRow($query="", array $parameters = array()){
        $this->bindMore($parameters);
        return $this->Init($query)->rowCount();
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return bool
     */
    public function QueryUpdateRow($query="", array $parameters = array()){
        $this->bindMore($parameters);
        if($query == $this->Init($query)->queryString){
            $this->OptimizeTable();
            return true;
        }
        else false;
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return int
     */
    public function QueryInsertRow($query="", array $parameters = array()){
        $this->bindMore($parameters);
        if($query == $this->Init($query)->queryString){
            $this->OptimizeTable();
            return $this->pdo->lastInsertId();
        }
        else false;
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return bool
     */
    public function QueryDeleteRow($query="", array $parameters = array()){
        $this->bindMore($parameters);
        if($query == $this->Init($query)->queryString){
            $this->OptimizeTable();
            return true;
        }
        else false;
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return bool
     */
    public function QueryHasRow($query="", array $parameters = array()){
        $this->bindMore($parameters);
        $temp = $this->type_array_result;
        $this->type_array_result = true;
        $id = $this->QueryResult($query,$parameters);
        $this->type_array_result = $temp;
        if(is_array($id) && current($id)!=0) return true;
        else return false;
    }


    /**
     * OPERATION TABLE,COLUMN, OTHER ROW BASE
     */

    /**
     * @param string $table
     * @return mixed
     */
    public function ColumnShow($table=""){
        $table = (!empty($table)) ? $table : $this->table;
        return $this->Init("DESCRIBE `".$this->tablePrefix.$table."`")->fetchAll();
    }

    /**
     * @param string $table
     * @param string $field
     * @return mixed
     */
    public function GetMax($table="", $field=""){
        $table = (!empty($table)) ? $table : $this->table;
        $field = (!empty($field)) ? $field : $this->expression;
        $max = $this->Init("SELECT MAX($field) AS `$field` FROM `".$this->tablePrefix.$table."`")->fetch();
        if($this->type_array_result) return current($max);
        else return $max->$field;
    }

    public function GetMin($table="", $field=""){
        $table = (!empty($table)) ? $table : $this->table;
        $field = (!empty($field)) ? $field : $this->expression;
        $max = $this->Init("SELECT MIN($field) AS `$field` FROM `".$this->tablePrefix.$table."`")->fetch();
        if($this->type_array_result) return current($max);
        else return $max->$field;
    }

    public function GetArrayById($table="",array $array = array(1), $field="id"){
        $this->table = (!empty($table)) ? $table : $this->table;
        $this->where = (empty($this->where)) ? "`$field` IN (".implode(',',array_map('intval',$array)).")" : " AND `$field` IN (".implode(',',array_map('intval',$array)).")";
        if(count($array)==1) return $this->OneResult();
        else return $this->ArrayResults();
    }
    /**
     * Transaction's operation
     */

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        if(!$this->transactionCounter++)
            return $this->pdo->beginTransaction();
        return $this->transactionCounter >= 0;
    }

    /**
     * @return bool
     */
    public function commit()
    {
        if(!--$this->transactionCounter)
            return $this->pdo->commit();
        return $this->transactionCounter >= 0;
    }

    /**
     * @return bool
     */
    public function rollback()
    {
        if($this->transactionCounter >= 0)
        {
            $this->transactionCounter = 0;
            return $this->pdo->rollback();
        }
        $this->transactionCounter = 0;
        return false;
    }

    /**
     * OTHER Public, Private functions
     */

    /**
     * @param   array $localArgs
     * @job     set content to variable
     */

    private function SetArguments(array $localArgs = array()){
        if(isset($localArgs)) foreach($localArgs as $key=>$value) if(!empty($value)) $this->$key = $value;
    }

    public function ClearArguments(){
        $localArgs = func_get_args();
        if(count($localArgs)>0){
            foreach($localArgs as $value) $this->$value = false;
        }else $this->table=$this->expression=$this->str_join=$this->where=$this->group=$this->order=$this->limit=$this->exit=$this->sql_cache=$this->values=false;
        $this->bindParam = array();
        $this->level = 0;
    }

    /**
     * @param       string $query
     * @get_param   String param for Base
     * @return      object PDO::query
     */
    public function Init($query=""){
        if(!$this->bConnected) $this->Connect();
        try {
            if(is_string($query)) {
                $this->sQuery = $query;
                if(count($this->bindParam)>0){
                    if($this->exit) $this->exitCode();
                    $this->sQuery = $this->pdo->prepare($query);
                    $this->sQuery->execute($this->bindParam);
                }else{
                    if($this->exit) $this->exitCode();
                    $this->sQuery = $this->pdo->query($this->sQuery);
                }

                if($this->type_array_result) $this->sQuery->setFetchMode(parent::FETCH_ASSOC);
                else $this->sQuery->setFetchMode(parent::FETCH_OBJ);
                //$this->ClearArguments("expression","str_join","where","group","order","limit","exit","sql_cache");
                return $this->sQuery;
            }
        }
        catch(PDOException $e)
        {
            # Write into log and display Exception
            echo $e->getMessage();
            die();
        }
    }

    public function CloseConnect(){
        $this->bConnected = false;
        $this->pdo = null;
    }

    /**
     * @param array $localArgs
     * @return bool
     */
    public function OptimizeTable(array $localArgs = array()){
        $this->SetArguments($localArgs);
        if($this->Init("OPTIMIZE TABLE `".$this->tablePrefix.$this->table."`")->queryString) return true;
        else false;
    }

    /**
     * @set     Set variables for replace in query
     * @param   array $parameters
     */
    public function bindMore(array $parameters = array()){ if(count($parameters)>0 && !empty($parameters)) $this->bindParam = $parameters; }

    private function exitCode(){
        $this->ClearArguments("expression","str_join","where","group","order","limit","exit","sql_cache");
        echo $this->sQuery."\n";
        if(!empty($this->bindParam)) print_r($this->bindParam,true);
        exit;
    }

    private function levelArray($array){
        $v = current($array);
        if(is_array($v)){
            $this->level++;
            $this->levelArray($v);
        }
    }
}
