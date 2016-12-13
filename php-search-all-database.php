<?php

function php_search_all_database($search,$tab_array){

global $mysqli;

$db_hostname = '';
$db_username = '';
$db_password = '';
$db_database_name = '';

$mysqli = mysqli_connect($db_hostname, $db_username, $db_password, $db_database_name);
// Check connection
  if (mysqli_connect_errno()){
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

  $sql = "show tables";
  $rs = $mysqli->query($sql);
  $rt = $tab_array;

  echo "<h3>Given Keyword:".$search."</h3>";
  echo "<h4> Given tables : ".implode($rt,',')."</h4>";

  if(count($rt) > 0){
    foreach($rt as $r){
      $table = $r;
      $sql2 = "SHOW COLUMNS FROM ".$table;
            $rs2 = $mysqli->query($sql2); //print_r($rs2);
            if($rs2->num_rows > 0){ $count =0;
                while($r2 = $rs2->fetch_array()){

                     $colum = $r2[0];
                     $sql_search_fields = $colum." like('%".$search."%')";
                     $sql_search = "select * from ".$table." where ". $sql_search_fields;
                     $rs3 = $mysqli->query($sql_search);

                     if($rs3->num_rows > 0){
                      echo "<ul>Table :".$table;
                          while($r3 = $rs3->fetch_array()){
                              $count++;
                              echo "<li> Column Name :".$colum."</li>";
                              echo "<li> Row id :".$r3['id']."</li>";
                              echo "<li> Value :".$r3[$colum]."</li><br>";
                          }

                        }echo "</ul>";

                   }

            }  if($rs3->num_rows > 0){ echo "&rarr; Result Count :".$count; }
             $rs3->close();$rs2->close();
        }
        $rs->close();
    }

}

$search = "keyword"; // search Keyword
$tab_array = ['','']; //table names in array format
php_search_all_database($search, $tab_array);

?>
