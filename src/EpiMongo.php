<?php
class EpiMongo
{
  const MONGO = 'mongo';

  private static $instances = array(), $type, $dbname, $host, $port, $pass;
  private $_type, $_dbname, $_host, $_port, $_pass;
  public $mongo, $db;


  private function __construct(){}
  
  public static function getInstance($type, $dbname, $host = 'localhost', $port = '27017')
  {
    $args = func_get_args();
    $hash = md5(implode('~', $args));

    if(isset(self::$instances[$hash]))
      return self::$instances[$hash];

    self::$instances[$hash] = new EpiMongo();
    self::$instances[$hash]->_type = $type;
    self::$instances[$hash]->_dbname = $dbname;
    self::$instances[$hash]->_host = $host;
    self::$instances[$hash]->_port = $port;
    self::$instances[$hash]->_port = $port;

    return self::$instances[$hash];
  }
 
  public static function employ($type = null, $dbname = null, $host = 'localhost', $port = '27017')
  {
    if(!empty($type) && !empty($dbname))
    {
      self::$type = $type;
      self::$dbname = $dbname;
      self::$host = $host;
      self::$port = $port;
    }

    return array('type' => self::$type, 'dbname' => self::$dbname, 'host' => self::$host, 'port' => self::$port);
  } 


  public function all($dbcollection = '', $params = array())
  {

    $this->init();
    $return = array();

    $collection = $this->mongo->selectCollection($this->_dbname, $dbcollection);
    $cursor = $collection->find($params);

    foreach ($cursor as $document) {
      unset($document['_id']);
      array_push($return, $document);
    }

    return $return;
  }

  public function create($dbcollection = '', $params = array())
  {

    $this->init();
    $return = array();

    $collection = $this->mongo->selectCollection($this->_dbname, $dbcollection);
    $cursor = $collection->insert($params);

    return $cursor;
  }

  public function count($dbcollection = '', $params = array())
  {

    $this->init();
    $result = $this->db->command(array("distinct" => $dbcollection,"query" => $params));  

    return $result['stats']['n'];
  }



  private function init()
  {

    if(!isset($this->mongo)){
      $this->mongo = new MongoClient('mongodb://'.$this->_host.':'.$this->_port);
      $this->db = $this->mongo->selectDB($this->_dbname);
    }

  }



}
 
 function getMongo()
{
  $employ = extract(EpiMongo::employ());

  if(empty($type) || empty($dbname) || empty($host) || empty($port))
    EpiException::raise(new EpiCacheTypeDoesNotExistException('Could not determine which database module to load', 404));
  else
    return EpiMongo::getInstance($type, $dbname, $host, $port);
}
