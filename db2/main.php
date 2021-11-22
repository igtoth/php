// IBM DB2 funcitons like MySQL (ODBC based)
// "Ighor Toth" <igtoth@gmail.com>
// Date: 08/05/2014

// usage:
// db2_connect(verbose,instance,username,password); -> also reads config file if nothing declared db2.conf.inc.php
// db2_query(db2_connect_return,sql)
// db2_fetch_array(result);
// db2_fetch_object(result);
// db2_display_table(db2_connect_return,sql); // select only

function db2_connect($verbose = null,$db2name = null,$username = null,$password = null) {
    if(!isset($verbose)){
        $verbose = TRUE; // TRUE or FALSE, if not set TRUE
    }
    if(!isset($db2name)){ // NOT DECLARED
        include("db2.conf.inc.php"); // CHECK CONFIG FILE
        if(!isset($db2name)){
            if ($verbose == TRUE){
                echo ("DB2 Instance not selected");
                exit();
            } else {
                exit();
            }
        }
    } else if (!isset($username)){
        echo ("DB2 Instance username not specified");
        exit();   
    }
    $db2conn = odbc_connect($db2name, $username, $password);
    if (($verbose == TRUE) && ($db2conn == 0)) {
        echo("Connection to database failed.");
        $sqlerror = odbc_errormsg($db2conn);
        echo($sqlerror);
    }
    return($db2conn);
}

function db2_query($db2conn,$sql){
    $result = odbc_exec($db2conn, $sql);
    if ($result == 0) {
        echo("QUERY = '$sql' FAILED.<br>\n");
        $sqlerror = odbc_errormsg($db2conn);
        echo($sqlerror);
    } else {
        // odbc_result_all prints all of the rows
        // for a result set ID as an HTML table
        return $result;
    }
}

function db2_fetch_array($result, $rownumber=null){
    $array = array();
    if (!($cols = odbc_fetch_into($result, $result_array, $rownumber))) {
        return false;
    }
    for ($i = 1; $i <= $cols; $i++) {
        $array[odbc_field_name($result, $i)] = $result_array[$i - 1];
    }
    return $array;
}

function db2_fetch_object($result){
    if(function_exists("db2_fetch_object")) return db2_fetch_object($result);
    $rs = array();
    $rs_obj = false;
    if( odbc_fetch_into($result, $rs) ){
        foreach( $rs as $key=>$value ){
            $fkey = odbc_field_name($result, $key+1);
            $rs_obj->$fkey = trim($value);
        }
    }
    return $rs_obj;
}

function db2_display_table($db2conn,$sql) {
    // select all rows from the table
    if(!isset($db2conn)||!isset($sql)){
        echo("ERROR db2_display_table: Function missing arguments");
        exit();
    }
    $check = explode(" ",$sql);
    if($check[0]!="SELECT"){
        echo("ERROR db2_display_table: Not SELECT SQL query");
    }
    if ($db2conn != 0) {
        // odbc_exec returns 0 if the statement fails;
        // otherwise it returns a result set ID
        $result = odbc_exec($db2conn, $sql);
        if ($result == 0) {
            echo("SELECT statement failed.");
            $sqlerror = odbc_errormsg($db2conn);
            echo($sqlerror);
        } else {
            // odbc_result_all prints all of the rows
            // for a result set ID as an HTML table
            odbc_result_all($result);
        }
    }
}
