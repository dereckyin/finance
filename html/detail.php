<?php

require_once __DIR__ . '/security.php';
$decoded = require_jwt_user();
require_once "header.php";

$AccountId = (int) $decoded->data->id;

$smarty = create_smarty_instance();
$smarty->assign('spend', get_item_data($AccountId));
$smarty->assign('item', get_item($AccountId));

    $smarty->display('detail.html');

    function get_item($AccountId)
	{
	
	    if (empty($AccountId)) {
	        return false;
	    }
	
	    try {
	        $_query = "select Item, sum(Amount) as amount, strftime('%Y/%m', UseDate) UseDate from Detail group by Item, AccountId, strftime('%Y/%m', UseDate) having AccountId = :id order by UseDate desc";
	        $rows = db_rows($_query, array(':id' => (int) $AccountId));
	
	     
	        $item_set = array();
	        $item = array();

	        array_push($item, "UseDate");
	        
	        foreach ( $rows as $value)
	        {
	            $item_set[$value["Item"]] = "1";
	        }
	        
	        foreach ($item_set as $key => $value) {
	            array_push($item, $key);
	        }

	        array_push($item, "subtotal");
	        
	        return $item;
	    }
	    catch ( PDOException $e ) {
	        error_log($e->getMessage());
	        return array();
	    }
	
	    return true;
	}


    function get_item_data($AccountId)
	{
	
	    if (empty($AccountId)) {
	        return false;
	    }
	
	    try {
	        $_query = "select Item, sum(Amount) as amount, strftime('%Y/%m', UseDate) UseDate from Detail group by Item, AccountId, strftime('%Y/%m', UseDate) having AccountId = :id order by UseDate desc";
	        $rows = db_rows($_query, array(':id' => (int) $AccountId));
	
	        $data_date = "";
	        $item = array();
	        $sub_total = 0;
	        $dim = -1;
	        
            $array = array();
            $_data = array();
	        
	        foreach ( $rows as $value)
	        {
	            $item[$value["Item"]] = "1";
	            $_data[] = $value;
	        }

	        if (empty($_data)) {
	            return array();
	        }
	        
	        foreach ($_data as $row) {
	            // $arr[3] will be updated with each value from $arr...
	            if($data_date != $row["UseDate"])
	            {
	                $data_date = $row["UseDate"];
	                
	                $dim++;
	                
	                $array[$dim] = array();
	                
	                $array[$dim]["UseDate"] = $data_date;
	                
	                if($dim != 0)
	                {
	                    $array[$dim - 1]["subtotal"] = $sub_total;
	                    foreach($item as $key => $value)
	                    {
	                        if(empty($array[$dim - 1][$key]))
	                            $array[$dim - 1][$key] = 0;
	                    }
	                }
	             
	                $sub_total = 0;
	            }
	               
	            $colum_name = $row["Item"];
	            $array[$dim][$colum_name] = (float) $row["amount"] * -1;
	            
	            $sub_total += (float) $row["amount"] * -1;
	            //echo "{$key} => {$value} ";
	            //print_r($item);
	        }
	        
	        $array[$dim]["subtotal"] = $sub_total;
			foreach($item as $key => $value)
            {
                if(empty($array[$dim][$key]))
                    $array[$dim][$key] = 0;
            }
	        
	        //print_r($array);
	        return $array;
	
	    }
	    catch ( PDOException $e ) {
	        error_log($e->getMessage());
	        return array();
	    }
	
	    return true;
	}
	
?>