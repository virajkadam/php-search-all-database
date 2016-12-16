<?php

$search_keyword = "keyword"; 			// search Keyword
$table_array = ['',''];					// table names in array format
php_search_all_database($search_keyword, $table_array); // call this Awesomme function

function php_search_all_database($search_keyword,$table_array){

	global $conn;

	$db_hostname = ''; 					// database hostname (default value: localhost)
	$db_username = ''; 					// database username (default value: root)
	$db_password = ''; 					// database password (default value: password)
	$db_database_name = ''; 				// database name

	$conn = mysqli_connect($db_hostname, $db_username, $db_password, $db_database_name);
	
		if(mysqli_connect_errno()){					// Check connection
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}

		$rt = $table_array;
		echo "<b>Given Keyword :</b> ".$search_keyword . '<br>';
		echo "<b>Given tables :</b> " . implode($rt,', ') . '<br>';

		if(count($rt) > 0){ 							// check weather table column is not empty
			foreach($rt as $r){ 						// iterate column name table
				$table = $r;
				$sql2 = "SHOW COLUMNS FROM ".$table;
				$rs2 = $conn->query($sql2);
				if($rs2->num_rows > 0){ 				// check weather column name provided exist in database
					$count =0;
					while($r2 = $rs2->fetch_array()){ 	// fetch data from respective column name

						$colum = $r2[0];
						$sql_search_fields = $colum . " LIKE ('%" . $search_keyword . "%')";
						$sql_search = "SELECT * FROM " . $table . " WHERE " . $sql_search_fields;
						$rs3 = $conn->query($sql_search);

						if($rs3->num_rows > 0){ 				// check weather 'keyword' found or not
							
							echo "<ul><u>Table :" . $table . '</u>';
							while($r3 = $rs3->fetch_array()){ 	// fetch result from respective data
								$count++;
								echo "<li> Column Name : " . $colum . "</li>";
								echo "<li> Row  : " . $r3['id'] . "</li>";	// primary key column name
								echo "<li> Value : " . $r3[$colum] . "</li><br>";
							}
						
						}
						echo "</ul>";
				 	}
				}

				if($rs3->num_rows > 0){ 		// check weather total count is not zero
					echo "&rarr; Result Count :" . $count;// display total result count
				}
				$rs3->close(); 					// reset resultset (check keyword found or not)
				$rs2->close(); 					// reset resultset (check given column names found or not)
			}
		}
}
