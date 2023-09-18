<?php
namespace Aramex;

/**
 * Class for connection to DataBase
 */
class Database 
{
    /**
     * DataBase object
     * @access private
     * @var PDO
     */
    private $_db;
    
    /**
     * instance of database
     * @var PDO
     */
    static $_instance;
    
    /**
     * constructor
     * create new PDO connection 
     * @return void
     */
    private function __construct()
    {
        $this->_db = new \PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME, DB_USERNAME, DB_PASSWORD);
        $this->_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Forbid clone
     * @return void
     */
    private function __clone(){}

    /**
     * connect to DataBase
     * @return PDO
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * run query with PDO
     * @param string $sql
     * @return PDOStatement
     */
    public function query($sql)
    {
        return $this->_db->query($sql);
    }
    
    /**
     * use PDO prepare
     * @param string $sql
     * @return PDOStatement
     */
    public function prepare($sql)
    {
        return $this->_db->prepare($sql);
    }
        
}
