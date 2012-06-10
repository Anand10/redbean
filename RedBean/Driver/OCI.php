<?php

class RedBean_Driver_OCI implements RedBean_Driver {

    private $dsn;

    /**
     * 
     * @var unknown_type
     */
    private static $instance;

    /**
     * 
     * @var boolean
     */
    private $debug = false;



    /**
     * 
     * @var unknown_type
     */
    private $affected_rows;

    /**
     * 
     * @var unknown_type
     */
    private $rs;

    /**
     * 
     * @var unknown_type
     */
    private $exc = 0;
    private $autocommit = true;

    /* Hold the statement for the last query */
    private $statement;
	
	 /* Hold the last inserted Id for the last statement*/
	private $lastInsertedId;
	
	/**
	 * Whether we are currently connected or not.
	 * This flag is being used to delay the connection until necessary.
	 * Delaying connections is a good practice to speed up scripts that
	 * don't need database connectivity but for some reason want to
	 * init RedbeanPHP.
	 * @var boolean
	 */
	protected $isConnected = false;	

    /**
     * Returns an instance of the PDO Driver.
     * @param $dsn
     * @param $user
     * @param $pass
     * @param $dbname
     * @return unknown_type
     */
    public static function getInstance($dsn, $user, $pass, $dbname) {
        if (is_null(self::$instance)) {
            self::$instance = new RedBean_Driver_OCI($dbname, $user, $pass);
        }
        return self::$instance;
    }

//    /**
//     * Constructor.
//     * @param $dsn
//     * @param $user
//     * @param $pass
//     * @return unknown_type
//     */
//    public function __construct($db, $user, $pass) {
//        echo "$user, $pass, $db";
//        $conn = oci_connect($user, $pass); //todo add handling of $db
//        $this->connection = $conn;
//    }
	
	/**
	 * Constructor. You may either specify dsn, user and password or
	 * just give an existing PDO connection.
	 * Examples:
	 *    $driver = new RedBean_Driver_PDO($dsn, $user, $password);
	 *    $driver = new RedBean_Driver_PDO($existingConnection);
	 *
	 * @param string|PDO  $dsn	 database connection string
	 * @param string      $user optional
	 * @param string      $pass optional
	 *
	 * @return void
	 */
	public function __construct($dsn, $user = null, $pass = null) {
		if ($dsn instanceof PDO) {
			$this->pdo = $dsn;
			$this->isConnected = true;
			$this->pdo->setAttribute(1002, 'SET NAMES utf8');
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			// make sure that the dsn at least contains the type
			$this->dsn = $this->getDatabaseType();
		} else {
			$this->dsn = $dsn;
			$this->connectInfo = array( 'pass'=>$pass, 'user'=>$user );
		}
	}	

    public function setAutoCommit($toggle) {
        $this->autocommit = (bool) $toggle;
    }
	
	
	/**
	 * Establishes a connection to the database using PHP PDO
	 * functionality. If a connection has already been established this
	 * method will simply return directly. This method also turns on
	 * UTF8 for the database and PDO-ERRMODE-EXCEPTION as well as
	 * PDO-FETCH-ASSOC.
	 *
	 * @return void
	 */
	public function connect() {
		if ($this->isConnected) return;
		$user = $this->connectInfo['user'];
		$pass = $this->connectInfo['pass'];
        
		$this->connection =oci_connect($user, $pass); //todo add handling of $db
		$this->isConnected = true;
	}	
	/**
	 * Runs a query. Internal function, available for subclasses. This method
	 * runs the actual SQL query and binds a list of parameters to the query.
	 * slots. The result of the query will be stored in the protected property
	 * $rs (always array). The number of rows affected (result of rowcount, if supported by database)
	 * is stored in protected property $affected_rows. If the debug flag is set
	 * this function will send debugging output to screen buffer.
	 * 
	 * @throws RedBean_Exception_SQL 
	 * 
	 * @param string $sql     the SQL string to be send to database server
	 * @param array  $aValues the values that need to get bound to the query slots
	 */
	protected function runQuery($sql,$aValues) {

		
		$this->connect();
		if ($this->debug && $this->logger) {
			$this->logger->log($sql, $aValues);
		}
		try {

			$this->Execute($sql, $aValues);
			$this->affected_rows = oci_num_rows($this->statement);
			if (oci_num_fields($this->statement)) {
				$rows = array();
		    	oci_fetch_all($this->statement, $rows, 0 ,  -1 , OCI_FETCHSTATEMENT_BY_ROW );
				foreach ($rows as $key=>$row){
					foreach($row as $field => $value){
					   unset ($rows[$key][$field]);
					   $new_key = strtolower($field);
					   $rows[$key][$new_key] = $value;						
					}
				}
				$this->rs = $rows;
		    	if ($this->debug && $this->logger) $this->logger->log('resultset: ' . count($this->rs) . ' rows');
	    	}
		  	else {
		    	$this->rs = array();
		  	}
		}catch(PDOException $e) {
			//Unfortunately the code field is supposed to be int by default (php)
			//So we need a property to convey the SQL State code.
			$x = new RedBean_Exception_SQL( $e->getMessage(), 0);
			$x->setSQLState( $e->getCode() );
			throw $x;
		}
	}	
	/**
	 * Runs a query and fetches results as a multi dimensional array.
	 *
	 * @param  string $sql SQL to be executed
	 *
	 * @return array $results result
	 */
	public function GetAll( $sql, $aValues=array() ) {
		$this->runQuery($sql,$aValues);
		return $this->rs;
	}

	 /**
	 * Runs a query and fetches results as a column.
	 *
	 * @param  string $sql SQL Code to execute
	 *
	 * @return array	$results Resultset
	 */
	public function GetCol($sql, $aValues=array()) {
		$rows = $this->GetAll($sql,$aValues);
		$cols = array();
		if ($rows && is_array($rows) && count($rows)>0) {
			foreach ($rows as $row) {
				$cols[] = array_shift($row);
			}
		}
		return $cols;
	}

    /**
     * (non-PHPdoc)
     * @see RedBean/RedBean_Driver#GetCell()
     */
    public function GetCell($sql, $aValues = array()) {

        $arr = $this->GetAll($sql, $aValues);
        $row1 = array_shift($arr);
        $col1 = array_shift($row1);
        return $col1;
    }

    /**
     * (non-PHPdoc)
     * @see RedBean/RedBean_Driver#GetRow()
     */
    public function GetRow($sql, $aValues = array()) {

        $arr = $this->GetAll($sql, $aValues);
        return array_shift($arr);
    }

    /**
     * (non-PHPdoc)
     * @see RedBean/RedBean_Driver#ErrorNo()
     */
    public function ErrorNo() {
        
    }

    /**
     * (non-PHPdoc)
     * @see RedBean/RedBean_Driver#Errormsg()
     */
    public function Errormsg() {
        
    }

    /**
     * (non-PHPdoc)
     * @see RedBean/RedBean_Driver#Execute()
     */
    public function Execute($sql, $aValues = array()) {
        echo $sql.PHP_EOL;
        foreach ($aValues as $key => $value) {
            $sql = preg_replace('/\?/', ' :SLOT' . $key . ' ', $sql, 1);
        }
		
       //if we insert we fetch the inserted id
		$isInsert = preg_match('/^INSERT/', $sql);
		if ($isInsert){
			$sql .= ' RETURN ID INTO :ID'; 
		}
        $stid = oci_parse($this->connection, $sql);

        foreach ($aValues as $key => $value) {
            ${'SLOT' . $key} = $value;
            oci_bind_by_name($stid, ':SLOT' . $key, ${'SLOT' . $key});
        }

		if ($isInsert){
			oci_bind_by_name($stid,':ID', $this->lastInsertedId,20, SQLT_INT);
		}


        if (!$this->autocommit)
            $result = oci_execute($stid, OCI_NO_AUTO_COMMIT);  // data not committed
        else
            $result = oci_execute($stid);

		if (!$result){
			$error = oci_error($stid);
			throw new RedBean_Exception_OCI($error['message']);
		}
        $this->statement = $stid;
    }

    /**
     * (non-PHPdoc)
     * @see RedBean/RedBean_Driver#Escape()
     */
    public function Escape($str) {
        return $str;
    }

	/**
	 * Returns the latest insert ID if driver does support this
	 * feature.
	 *
	 * @return integer $id primary key ID
	 */
	public function GetInsertID() {
		//$this->connect();
		return $this->lastInsertedId;
	}

    /**
     * (non-PHPdoc)
     * @see RedBean/RedBean_Driver#Affected_Rows()
     */
    public function Affected_Rows() {
        throw new Exception('TO BE IMPLEMENTED');
    }

    /**
     * (non-PHPdoc)
     * @see RedBean/RedBean_Driver#setDebugMode()
     */
    public function setDebugMode($tf) {
        
    }

    /**
     * (non-PHPdoc)
     * @see RedBean/RedBean_Driver#GetRaw()
     */
    public function GetRaw() {
        //return $this->rs;
    }

    /**
     * Starts a transaction.
     */
    public function StartTrans() {
        
    }

    /**
     * Commits a transaction.
     */
    public function CommitTrans() {
        oci_commit($this->connection);
    }

    /**
     * Rolls back a transaction.
     */
    public function FailTrans() {
        oci_rollback($this->connection);
    }

    /**
     * Returns the name of the database type/brand: i.e. mysql, db2 etc.
     * @return string $typeName
     */
    public function getDatabaseType() {
        return "OCI";
    }

    /**
     * Returns the version number of the database.
     * @return mixed $version 
     */
    public function getDatabaseVersion() {
        return "8";
    }

}

?>
