<?php
$search_keyword = "KEYWORD";			// search Keyword
$table_ass_array = array( 
			'TABLE NAME 1' => array( 			// TABLENAME 1 to search in
							'COLUMNNAME_A',		// Column Name A to search in
							'COLUMNNAME_B'		// Column Name B to search in
						),
			'TABLE NAME 2' => array(			// TABLENAME 2 to search in
							'COLUMNNAME_A',		// Column Name A to search in
							'COLUMNNAME_B'		// Column Name B to search in
						)
			);

php_search_all_database($search_keyword, $table_ass_array);		// call this Awesomme function

function php_search_all_database($search_keyword,$table_ass_array){

	global $conn;

	$db_hostname = 'DATABASE HOST NAME'; 				// database hostname (default value: localhost)
	$db_username = 'DATABASE USERNAME'; 				// database username (default value: root)
	$db_password = 'DATABASE PASSWORD'; 				// database password (default value: password)
	$db_database_name = 'DATABASE NAME'; 				// database name

	$conn = mysqli_connect($db_hostname, $db_username, $db_password, $db_database_name);

		if(mysqli_connect_errno()){		// Check connection
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}

		$rt = $table_ass_array;
		echo "<b>Given Keyword :</b> ".$search_keyword . '<br>';
		echo "<b>Given tables :</b> " . implode($rt,', ') . '<br>';

		if(count($rt) > 0){ 							// check weather table column is not empty
			foreach($rt as $k=>$v){ 						// iterate column name table
				echo $table = $k;
				echo $v;
				foreach($v as $r2){	// fetch data from respective column name

					$colum = $r2;	//[0];
					$sql_search_fields = $colum . " LIKE ('%" . $search_keyword . "%')";
					$sql_search = "SELECT * FROM " . $table . " WHERE " . $sql_search_fields;
					$rs3 = $conn->query($sql_search);

					if($rs3->num_rows > 0){ 				// check weather 'keyword' found or not

						echo "<ul><u>Table :" . $table . '</u>';
						while($r3 = $rs3->fetch_array()){ 	// fetch result from respective data
							$count++;
							echo "<li> Column Name : " . $colum . "</li>";
							echo "<li> Row  : " . $r3['ROW ID'] . "</li>";	// primary key column name
							echo "<li> Value : " . $r3[$colum] . "</li><br>";
						}

					}
					echo "</ul>";
				}
				echo $table." Searching End's Here<hr>";
			}

		}
}
