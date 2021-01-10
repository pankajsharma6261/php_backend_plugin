<?php 

class Database {
    private $db_host = 'hostname';
    private $db_user = "username";
    private $db_pass = "password";
    private $db_name = "database_name";

    protected $conn = false;
    private $query = null;

    // create connection 
    public function __construct(){
        try {

            $this->query = new PDO("mysql:host=$this->db_host;dbname=$this->db_name",$this->db_user,$this->db_pass);
            $this->query->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
            $this->conn = true;

        } catch (PDOException $e) {
            die('Connection Failed...' . $e->getMessage());
        }
    }

    // insert data code here 
    public function insert($table,$param=array()){

        $field_name = implode(', ' , array_keys($param));
        $name_place = implode(', :',array_keys($param));
        $data = array();
        foreach ($param as $key => $value) {
            if($key != 'id'){
                $data[] = "$key = :$key";
            }
        }
        $update_name = implode(', ',$data);

        $sql = "INSERT INTO $table ($field_name) VALUES(:$name_place) ON DUPLICATE KEY UPDATE $update_name ";

        $stm = $this->query->prepare($sql);
        $stm->execute($param);

        return true;
    }

    // show data code here 
    public function select($table,$where=array(),$column="*"){
        $data = array();
        $result = array();
        $sql = "SELECT $column FROM $table";
        if(key_exists("where",$where)){
            $condition = array_key_first($where['where']) .' =:' . array_key_first($where['where']);
            $sql .= " WHERE $condition ";
            $data[array_key_first($where['where'])] = $where['where'][array_key_first($where['where'])];
        }
        if(key_exists("order_by",$where)){
            $sql .= " ORDER BY ". $where['order_by']['column'];
            if(key_exists("order",$where['order_by'])){
                $sql .= " ".$where['order_by']['order'];
            }
        }
        if(key_exists("limit_by",$where)){
            $limit = $where['limit_by']['limit'];
            $page = 1;
            if(key_exists('page',$where['limit_by'])){
                $page = $where['limit_by']['page'];
            }
            $offset = ($page -1)*$limit;
            $sql .= " LIMIT ". $offset .','. $limit ;

            if(key_exists('pagination',$where) && $where['pagination'] == true){
                
                $query_one = "SELECT * FROM $table";
                $stm_one = $this->query->prepare($query_one);
                $stm_one->execute();
                if($stm_one->rowCount() > 0){
                    $total_records = $stm_one->rowCount();
                    $total_page = ceil($total_records / $limit );
                    $result['total_page'] = $total_page;
                }
            }
        }
        // echo $sql;
        $stm = $this->query->prepare($sql);
        $stm->execute($data);
        while($row = $stm->fetch()){
            $result[] = $row;
        }
        return $result;
    }

    // data update here 
    public function update($table,$param=array(),$where=array()){
        $data = array();
        foreach ($param as $key => $value) {
            $data[] = "$key = :$key";
        }
        $field_name = implode(', ',$data);
        $sql = "UPDATE $table SET $field_name ";
        if(!empty($where)){
            $condition = array_key_first($where) .' =:' .array_key_first($where);
            $sql .= " WHERE $condition ";
        }
        $stm = $this->query->prepare($sql);
        $stm->execute(array_merge($param,$where));

        return true;
    }

    public function delete($table,$where=array()){
        $sql = "DELETE FROM $table ";
        if(!empty($where)){
            $condition = array_key_first($where) .' =:' . array_key_first($where);
            $sql .= "WHERE $condition";
        }
        $stm = $this->query->prepare($sql);
        $stm->execute($where);
        return true;
    }

    // destroy connection 
    public function __destruct(){
        if($this->conn){
            $this->conn = null;
        }
    }
}

// $db = new Database;

// $db->insert('students',[
//     'id' => '',
//     'name' => 'pankaj sharma',
//     'mobile_no' => 9174069312,
//     'email' => 'example123@gmail.com',
//     'message' => "hello sir "
// ]);
// $db->update('students',[
//     'name' => 'sonu sharma',
//     'mobile_no' => 9174069312,
//     'email' => 'sonu123@gmail.com',
//     'message' => "hello sir  sorry my self vinod"
// ],['id' => 91 ]);

// $db->delete('students',['name' => 'pankaj sharma']);


// $data = $db->select("employees",[
//     'order_by' => ['column' => 'id', 'order' => 'DESC' ],
//     'limit_by' => ['limit' => 3 ,'page' => 1 ],
//     'pagination' => true,
// ]);

// // echo $data;
// echo "<pre>";
// print_r($data);
// echo "</pre>";



?>