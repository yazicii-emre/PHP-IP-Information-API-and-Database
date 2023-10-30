<?php

// Include our IP class
require_once 'class.db.php';

class ip extends db {
    // Specify the table name to use
    private $tableName = 'ip_api';

    // This method will return an array of IP information if the API connection is successful
    public function ipConnection() {
        try {
            // Fetch IP information from the IP-API service
            $query = unserialize(file_get_contents("http://ip-api.com/php/{$this->get_client_ip()}?fields=124411"));

            // Check if the API connection was successful
            if ($query['status'] != 'success') {
                throw new PDOException('API connection failed...');
            }

            // Check if the table exists, if not, create it
            $table_exists = $this->db->prepare("DESCRIBE $this->tableName");

            if (!$table_exists->execute()) {
                $createTable = "CREATE TABLE $this->tableName (
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
                    mobile ENUM('0', '1') DEFAULT '0',
                    query VARCHAR(75) NOT NULL
                )";
                $isCreate = $this->db->exec($createTable);
            }

            return $query;
        } catch (PDOException $e) {
            return 'An error occurred! ' . $e->getMessage();
        }
    }

    // Use this method to perform IP record insertion/update
    public function setIp() {
        try {
            $ipInfo = $this->ipConnection();

            if (is_array($ipInfo)) {
                $select = $this->db->prepare("SELECT * FROM $this->tableName WHERE query=?");
                $select->execute([$this->get_client_ip()]);

                if (!$select->rowCount()) {
                    // If the IP address is not in the database, insert a new record
                    $stmt = $this->db->prepare("INSERT INTO $this->tableName SET
                        status=?, country=?, countryCode=?, regionName=?, city=?, lat=?, lon=?, timezone=?, org=?, mobile=?, query=?
                    ");
                } else {
                    // If the IP address is in the database and more than a month old, update the record
                    $stmt = $this->db->prepare("UPDATE $this->tableName SET
                        insert_time = NOW(),
                        status=?, country=?, countryCode=?, regionName=?, city=?, lat=?, lon=?, timezone=?, org=?, mobile=?
                        WHERE insert_time < DATE_SUB(NOW(), INTERVAL 1 MONTH) AND query=?
                    ");
                }

                $stmt->execute([
                    $ipInfo['status'], $ipInfo['country'], $ipInfo['countryCode'], $ipInfo['regionName'], $ipInfo['city'],
                    $ipInfo['lat'], $ipInfo['lon'], $ipInfo['timezone'], $ipInfo['org'], $ipInfo['mobile'], $ipInfo['query']
                ]);
            } else {
                echo $ipInfo;
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    // Get the client's IP address
    private function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        elseif (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        elseif (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        elseif (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        elseif (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        elseif (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}
?>
