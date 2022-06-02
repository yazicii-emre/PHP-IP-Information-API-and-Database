<?php

// İp Sınıfımızı Dahil Ediyoruz.
require_once 'class.db.php';


class ip extends db {


// Oluşturulacak Tablo Adını Buraya Yazıyoruz.
private $tableName='ipapi';



// Bu Metod , Eğer İp Api Bağlantısı Başarılı İse İp Bilgilerinin Olduğu Bir Dizi Değer Döndürecek
public function ipConnection(){

	try {

	$query = unserialize(file_get_contents("http://ip-api.com/php/{$this->get_client_ip()}?fields=124411"));

	if ($query['status']!='success') {
	throw new PDOException ('Api bağlantısı başarısız ...');
	}


   // $this->tableName'de Belirtilen Tablo Adında Bir Tablomuz Yok İse Tablo Oluşturuyor
	 $table_exists =$this->db->prepare( "DESCRIBE $this->tableName ");

if ( !$table_exists->execute() ) {

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


// Bu Metodu Çağırarak İp Kayıt/Güncelleme İşlemlerini Gerçekleştiriyoruz
public function setIp() {

	try {

	if (is_array($this->ipConnection())) {

		$select=$this->db->prepare("SELECT * FROM $this->tableName WHERE query=?");
		$select->execute([ $this->get_client_ip() ]);


		if (!$select->rowCount()) {

			// İp Adresi Kayıtlı Değilse Kayıt İşlemi Gerçekleşiyor
        $stmt=$this->db->prepare("INSERT INTO $this->tableName SET
        status=? ,country=?, countryCode=?, regionName=?,city=?, lat=?,lon=?,timezone=?,org=?,mobile=?,query=?
        ");

		}else {

// İp Adresi Kayıtlı İse Ve Kayıt Üzerinden 1 Hafta Geçmiş İse Güncelleme İşlemi Gerçekleşiyor
			$stmt=$this->db->prepare("UPDATE $this->tableName SET
			insert_time={date('Y-m-d H:i:s')},status=? ,country=?, countryCode=?, regionName=?,city=?, lat=?,lon=?,timezone=?,org=?,mobile=? where insert_time < DATE_SUB(Now(),INTERVAL 1 MONTH) and  query=?
			");

		}


		$stmt->execute( [
  $this->ipConnection()['status'],$this->ipConnection()['country'],$this->ipConnection()['countryCode'],$this->ipConnection()['regionName'],$this->ipConnection()['city'],
	$this->ipConnection()['lat'],$this->ipConnection()['lon'],$this->ipConnection()['timezone'],$this->ipConnection()['org'],$this->ipConnection()['mobile'],
	$this->ipConnection()['query']

			] );


	}else {
		echo $this->ipConnection();
	}


	}catch (PDOException $e) {
		return $e->getMessage();
	}

}



// İp Adresini Döndürüyor.
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
