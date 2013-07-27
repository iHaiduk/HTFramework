<?php
/**
 * Group          HT Group
 * Author 	    Igor Gayduk
 * Datecreate   5.12.2012
 * Timecreate:  20:05
 * Daterewrite  27.07.2013
 * Timerewrite  21:10
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
    private $query;
    private $sQuery;
    private $parameters;
    private $bindParam;

    public $tableName;
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

    /**
     * Constructed, Initialize Params Class
     */

    /**
     * @param   array $localArgs
     * @job     initialize function SetArguments, Connect
     */

    public function __construct(array $localArgs = array())
    {
        $this->SetArguments($localArgs);
        $this->Connect();
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

            $this->tableName = (!empty($this->tableName)) ? $this->tableName : false;
            $this->expression = (is_array($this->expression)) ? implode(",",$this->expression) : (!empty($this->expression)) ? $this->expression : "*";
            $this->where = (!empty($this->where)) ? "WHERE ".preg_replace("/,/"," AND ",$this->where) : false;
            $this->group = (!empty($this->group)) ? "GROUP BY ".$this->group : false;
            $this->limit = (!empty($this->limit)) ? "LIMIT ".$this->limit : false;
            $this->sql_cache = ($this->sql_cache) ? "SQL_CACHE" : false;

            if(!empty($this->order)){
                $this->order = "ORDER BY ".$this->order;
                $this->str_join = "STRAIGHT_JOIN";
            }else $this->order = $this->str_join = false;


            $query = "SELECT ".$this->str_join." ".$this->sql_cache." ".$this->expression." FROM `".$this->tablePrefix.$this->tableName."`
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
     * @return mixed
     * @return_variable one row in base
     */
    public function OneResult(array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $this->bindMore($parameters);
        return $this->SelectQuery($localArgs)->fetch();
    }

    /**
     * @param array $localArgs
     * @return mixed
     * @return_variable all row in base
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
    public function CountROW(array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $this->bindMore($parameters);
        return $this->SelectQuery($localArgs)->rowCount();
    }

    /**
     * @param array $localArgs
     * @return bool
     */
    public function UpdateRow(array $localArgs = array()){
        $this->SetArguments($localArgs);
        $values_update = "";
        foreach($this->values as $key=>$value){
            $values_update .= "`{$key}` = '$value',";
        }
        $values_update = substr($values_update,0,-1);
        try
        {
            $this->where = (!empty($this->where)) ? "WHERE ".preg_replace("/,/"," AND ",$this->where) : false;
            $this->order = (!empty($this->order)) ? "ORDER BY ".$this->order : false;
            $this->limit = (!empty($this->limit)) ? "LIMIT ".$this->limit : false;
            $this->sQuery = "UPDATE `".$this->tablePrefix.$this->tableName."` SET {$values_update} {$this->where} {$this->order} {$this->limit}";
            if($this->exit) $this->exitCode();
            else{
                if($this->sQuery == $this->Init($this->sQuery)->queryString) return true;
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
     * @param string $query
     * @param array $localArgs
     * @return mixed
     * @return_variable one row in base
     */
    public function QueryResult($query="", array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $this->bindMore($parameters);
        return $this->Init($query)->fetch();
    }

    /**
     * @param string $query
     * @param array $localArgs
     * @return mixed
     * @return_variable all row in base
     */
    public function QueryArrayResult($query="", array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $this->bindMore($parameters);
        return $this->Init($query)->fetchAll();
    }

    /**
     * @param string $query
     * @param array $localArgs
     * @param array $parameters
     * @return int
     */
    public function QueryCountRow($query="", array $localArgs = array(), array $parameters = array()){
        $this->SetArguments($localArgs);
        $this->bindMore($parameters);
        return $this->Init($query)->rowCount();
    }

    public function QueryUpdateRow($query="", array $parameters = array()){
        $this->bindMore($parameters);
        if($query == $this->Init($query)->queryString) return true;
        else false;
    }




    /**
     * Transaction's operation
     */

    public function beginTransaction()
    {
        if(!$this->transactionCounter++)
            return $this->pdo->beginTransaction();
        return $this->transactionCounter >= 0;
    }

    public function commit()
    {
        if(!--$this->transactionCounter)
            return $this->pdo->commit();
        return $this->transactionCounter >= 0;
    }

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
        if(isset($localArgs)) foreach($localArgs as $key=>$value) $this->$key = $value;
    }

    /**
     * @param   mixed $localArgs
     * @job     clear variable
     */
    public function ClearArguments(){
        $localArgs = func_get_args();
        if(count($localArgs)>0){
            foreach($localArgs as $value) $this->$value = false;
        }else $this->tableName=$this->expression=$this->str_join=$this->where=$this->group=$this->order=$this->limit=$this->exit=$this->sql_cache=$this->values=false;
        $this->bindParam = array();
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
                $this->ClearArguments("expression","str_join","where","group","order","limit","exit","sql_cache");
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

    /**
     * @set     Set variables for replace in query
     * @param   array $parameters
     */
    public function bindMore(array $parameters = array()){ if(count($parameters)>0 && !empty($parameters)) $this->bindParam = $parameters; }

    private function exitCode(){
        $this->ClearArguments("expression","str_join","where","group","order","limit","exit","sql_cache");
        exit($this->sQuery."\n".print_r($this->bindParam,true));
    }


}
