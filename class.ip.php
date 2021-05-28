<?php 

require_once 'class.db.php';


class ip extends db {
	

public $ip;

private $tableName='ipapi';
	
	
public function ipConnection(){
	
	try {
		
	$query = unserialize(file_get_contents("http://ip-api.com/php/{$this->get_client_ip()}?fields=124411"));
 
	if ($query['status']!='success') { 
	throw new PDOException ('Api bağlantısı başarısız ...');		
	}
		
	 $table_exists =$this->db->prepare( "DESCRIBE $this->tableName ");
		
if ( !$table_exists->execute() ) {
    // my_table exists
	
	$createTable= "CREATE TABLE $this->tableName (
	ip_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	insert_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	status VARCHAR(7) NOT NULL,
	country VARCHAR(25) NOT NULL,
	countryCode VARCHAR(5) NOT NULL,
	regionName VARCHAR(75) NOT NULL,
	city VARCHAR(75) NOT NULL,
	zip INT(10) NOT NULL,
	lat FLOAT(10) NOT NULL,
	lon FLOAT(10) NOT NULL,
	timezone VARCHAR(75) NOT NULL,
	org VARCHAR(75) NOT NULL,
	mobile ENUM('0','1') DEFAULT '0',
	query VARCHAR(75) NOT NULL
	
	)	
	";
	

	$isCreate=$this->db->exec($createTable);		
	
} 
		
			
	return $query;	
		
	}catch (PDOException $e) {
		
		return 'Bir hata oluştu! '.$e->getMessage();
	}
	
	
}	
	
	
public function timeDifference($firstTime,$secondTime) {
	
 date_default_timezone_get("Europe/İstanbul");
	
 $first=strtotime($firstTime);	
 $second=strtotime($secondTime);
	
 $totalTime=($second-$first)/86400;
	
 return round($totalTime);	
	
}	
	
public function setIp() {
	
	
	try {
		
	if (is_array($this->ipConnection())) {

		
		$select=$this->db->prepare("SELECT * FROM $this->tableName WHERE query=?");
		$select->execute([ $this->get_client_ip() ]);
		
		if ($select->rowCount()==0) {
			
        $insert=$this->db->prepare("INSERT INTO $this->tableName SET {$this->setValue($this->ipConnection())}");
		$insert->execute( $this->addValue($this->ipConnection()) ); 
			
			
		}
			
		
	
	}else {
		echo $this->ipConnection();
	}	

		
	
	}catch (PDOException $e) {		
		return $e->getMessage();
	}
	
}	
	
	
 private function get_client_ip() {
    $ipaddress = '';
    if ( getenv( 'HTTP_CLIENT_IP' ) )
      $ipaddress = getenv( 'HTTP_CLIENT_IP' );
    else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) )
      $ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
    else if ( getenv( 'HTTP_X_FORWARDED' ) )
      $ipaddress = getenv( 'HTTP_X_FORWARDED' );
    else if ( getenv( 'HTTP_FORWARDED_FOR' ) )
      $ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
    else if ( getenv( 'HTTP_FORWARDED' ) )
      $ipaddress = getenv( 'HTTP_FORWARDED' );
    else if ( getenv( 'REMOTE_ADDR' ) )
      $ipaddress = getenv( 'REMOTE_ADDR' );
    else
      $ipaddress = 'UNKNOWN';
    return $ipaddress;
  }	
	
	
	
}

?>
