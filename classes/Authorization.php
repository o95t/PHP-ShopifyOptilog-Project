<?php
namespace Aramex;

/**
 * Class for user authorization
 */
class Authorization
{
    private $shop_domain;
    /**
     * prepered query for select information about user by login/password
     * @access private
     * @var PDOStatement
     */
    private $qUserCheck;     
    
    /**
     * prepered query for updating user last visit
     * @access private
     * @var PDOStatement
     */
    private $qUpdLastVisit;
    
    /**
     * constructor
     * create table users and add user if table not exist
     * prepare queries for future using
     */
    public function __construct($shop_domain)
    {
        $this->shop_domain = $shop_domain;


       // $this->qUpdLastVisit = $this->queryUpdLastVisit();
    }
    
    /**
     * prepare select information about user by login/password
     * @uses Database::getInstance() for access to DB
     * @return PDOStatement 
     */
    public function queryUserCheck()
    {
        $db = Database::getInstance();
        $arr = $db->query("SELECT * FROM users WHERE shop = '{$this->shop_domain}' ")->fetchAll(\PDO::FETCH_ASSOC);
        $salt = md5(substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5)),0,32));
        if(count($arr)>0){
            // Update user
           $db->query("UPDATE users SET salt = '{$salt}' WHERE shop = '{$this->shop_domain}'");
        }
        else{
            // Add user
            $db->query("INSERT INTO users (shop, salt, last_visit) ". "VALUES ('$this->shop_domain', '$salt',". "'1980-11-01T12:19:16+00:00')");
        }
       $this->setUserSession();
        setcookie("SecureCookie", "1" , time() + 3600);  /* срок действия 1 час */
        $_SESSION['SecureCookie'] = $salt;
    }

    private function setUserSession()
    {
        $db = Database::getInstance();
        //get user_id for session
        $arr = $db->query("SELECT * FROM users WHERE shop = '{$this->shop_domain}' ")->fetchAll(\PDO::FETCH_ASSOC);
        $_SESSION['user_id'] = $arr[0]['id'];
    
     } 
    
    /**
     * prepare query for updating user last visit
     * @uses Database::getInstance() for access to DB
     * @return PDOStatement 
     */
    private function queryUpdLastVisit()
    {
        $db = Database::getInstance();
        return $db->prepare('UPDATE users SET last_visit = NOW()'
                    .' WHERE id = :user_id');
    }
    
    
    /**
     * get user password by email
     * @param string $email user email 
     * @uses Database::getInstance() for access to DB
     * @return array
     */
    public function getUser($shop)
    {
        $db = Database::getInstance();
        $q = $db->prepare("SELECT id, salt, last_visit FROM users "
                    . "WHERE shop = ?");
        $q->execute(array($shop));
        if ($q->rowCount()){
            return $q->fetch(\PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }
    
    /**
     * create table 'users' and add test user if table 'users' not exist
     * @uses Database::getInstance() for access to DB
     * @return void
     */
    public function createUserTable()
    {
        $db = Database::getInstance();
        // check existing table users

	$arr = $db->query("SELECT * FROM users LIMIT 1")->fetchAll(\PDO::FETCH_ASSOC);
  /*
         // check existing table orders
        $statement_orders = "SELECT * FROM information_schema.tables
                      WHERE table_schema = 'DB_NAME' AND table_name = 'users'
                      LIMIT 1;";
        $q = $db->query($statement_orders);
        // if table not exist - create table and add user
*/
   if (!(count($arr)>0)){
            // 1. Create table
            $db->query("CREATE TABLE users (
                            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            shop VARCHAR(50) NOT NULL,
                            last_visit VARCHAR(50) NOT NULL,
                            salt VARCHAR(255) NOT NULL
                            )");
       }
    }
    

}