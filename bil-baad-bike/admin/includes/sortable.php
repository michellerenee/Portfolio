<?php
include 'config.php';

$result = false;

if( isset($_POST['type'], $_POST['data']) && !empty($_POST['type']) ){
    // Do switch on the value from type
    switch ($_POST['type']) {
        case 'kategorier':
            foreach ($_POST['data'] as $order => $page_content){
                $order = intval($order) + 1;
                $id = intval($page_content['id']);

                $query = "UPDATE kategorier 
                          SET kategori_raekkefolge = $order 
                          WHERE kategori_id = $id";
                $result = $mysqli->query($query);

                // If result return false, user the function query_error to show debugging info
                if(!$result){
                    query_error($query, __LINE__, __FILE__);
                }
            }
            break;
    }
}

// Return the bool value from $result in assoc array, with the key status and use json_encode to output data as a
// json object
echo json_encode(['status' => $result]);

