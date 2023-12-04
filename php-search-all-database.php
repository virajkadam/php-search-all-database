<?php

$search_keyword = "KEYWORD";						// Enter keyword to search

$table_associative_array = array( 
			'TABLE NAME 1' => array(			// TABLENAME 1 to search in
				'columnN_NAME_A',			// column Name A to search in
				'columnN_NAME_B'			// column Name B to search in
			),
			'TABLE NAME 2' => array(			// TABLENAME 2 to search in
				'columnN_NAME_A',			// column Name A to search in
				'columnN_NAME_B'			// column Name B to search in
			)
		);


php_search_all_database( $search_keyword, $table_associative_array );	// call this Awesome function to run script



function php_search_all_database($search_keyword,$table_associative_array){

	global $conn;		// Declared global variable to store database connection

	$db_hostname = 'DATABASE HOST NAME';		// Database hostname (default value: localhost)
	$db_username = 'DATABASE USERNAME'; 		// Database username (default value: root)
	$db_password = 'DATABASE PASSWORD'; 		// Database password
	$db_database_name = 'DATABASE NAME'; 		// Database name

	$conn = mysqli_connect($db_hostname, $db_username, $db_password, $db_database_name);	// Establish Database Connection

		if(mysqli_connect_errno()){		// Check if database connection is ok
			echo "Failed to connect to MySQL: ".mysqli_connect_error();
		}


		echo "<b>Given Keyword :</b> ".$search_keyword.'<br>';
		echo "<b>Given tables :</b> ".implode($table_associative_array,', ').'<br>';


		if(count($table_associative_array) > 0){					// Check weather array of tables names and column names is not empty

			foreach($table_associative_array AS $table_name => $columnn_name){ 	// Iterate through array of table names

				echo $table_name;						// Name of table
				echo "<br>";
				echo $columnn_name;						// Name of Column in this table
				echo "<br><br>";

				foreach($columnn_name AS $column){				// Fetch data from array of column names

					$db_search_result_fields = $column." LIKE ('%".$search_keyword."%')";		// We have used wildcards as an example, You can replace as per your need
					$db_search_result = $conn->query("SELECT * FROM ".$table_name." WHERE ".$db_search_result_fields);

					if($db_search_result->num_rows > 0){ 			// Check weather 'keyword' found or not

						echo "<ul><u>Table :".$table_name.'</u>';

						while( $row = $db_search_result->fetch_array() ){ 	// Fetch final result from records found
							$count++;
							echo "<li>Column Name: ".$column."</li>";	// Respective column Name
							echo "<li>Row: ".$row['ROW ID']."</li>";	// Primary key of respective table name, For example id/rowId
							echo "<li>Value: ".$row[$column]."</li><br>";	// Data stored in respective columns. i.e. The actual keyword we found

						}	// End of while loop of final result 

						echo "</ul>";

					}	// End of if condition to check table data count

				}	// End of foreach of data fetching of every column

				echo $table_name." search End's Here<hr><br>";

			}	// End of foreach of data fetching of every table

		}	// End of if condition to check empty input data

	}	// Awesome function ends
