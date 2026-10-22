<?php
require_once __DIR__ . '/security.php';

function _safe_redirect_to_login() {
    if (!headers_sent()) {
        header("Location: index.php");
        exit;
    }
    echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=index.php"></head><body>';
    echo '<script>location.replace("index.php");</script>';
    echo '</body></html>';
    exit;
}

$decoded = require_jwt_user();

require_once "header.php";
	
	$op = isset($_REQUEST['op']) ? (string) $_REQUEST['op'] : '';
	$SId = isset($_REQUEST['SId']) ? (int) $_REQUEST['SId'] : 0;
	$strClass = isset($_REQUEST['strClass']) ? (string) $_REQUEST['strClass'] : '';
	$strItem = isset($_REQUEST['strItem']) ? (string) $_REQUEST['strItem'] : '';
	$txtUseDate = isset($_REQUEST['txtUseDate']) ? (string) $_REQUEST['txtUseDate'] : '';
	$nAmount = isset($_REQUEST['nAmount']) ? $_REQUEST['nAmount'] : '';
	$strDesc = isset($_REQUEST['strDesc']) ? (string) $_REQUEST['strDesc'] : '';
	$keyword = isset($_REQUEST['keyword']) ? (string) $_REQUEST['keyword'] : '';

	$qrydate = isset($_REQUEST['qrydate']) ? (string) $_REQUEST['qrydate'] : '';
	$type = isset($_REQUEST['type']) ? (string) $_REQUEST['type'] : '';
	$label = isset($_REQUEST['label']) ? (string) $_REQUEST['label'] : '';
	
	$AccountId = (int) $decoded->data->id;

	/*
	$op      = 'adddata';
	$strClass = '測試';
	$strItem = '測試';
	$txtUseDate = '2017/05/21';
	$nAmount = '100';
	$strDesc = 'test';
	
	$AccountId = 1;
	*/
	switch ($op) {
		case 'adddata':
			adddata($AccountId, $strClass, $strItem, $txtUseDate, $nAmount, $strDesc);
			break;
		case 'del':
			deletedata($AccountId, $SId);
			break;
		case 'onedit';
			 $smarty->assign('onedit', get_edit_data($AccountId, $SId));
			break;
		case 'onupdate';
			updatedata($AccountId, $SId, $strClass, $strItem, $txtUseDate, $nAmount, $strDesc);
			break;
	
	}

	
	require_once('/app/vendor/smarty/smarty/libs/Smarty.class.php');
    
    // required connect
    SmartyPaginate::connect();

    if($qrydate != "")
    {
    	// assign your db results to the template
    	$smarty->assign('qrys', get_qry_detail_results($AccountId, $qrydate, $type, $label));
    }
    else
    {
    	if($keyword != "")
	    {
	    	// assign your db results to the template
	    	$smarty->assign('qrys', get_qry_results($AccountId, $keyword));
	    }
	    else
	    {
            // set items per page
            SmartyPaginate::setLimit(25);
            // assign your db results to the template
            $smarty->assign('results', get_db_results($AccountId));
		}
	}
    

    
    $smarty->assign('items', get_item_results($AccountId));
    $smarty->assign('class', get_class_results($AccountId));
    $smarty->assign('spend', get_month_spend($AccountId));
	$smarty->assign('remider', get_task($AccountId));
	
    // assign {$paginate} var
    SmartyPaginate::assign($smarty);
    // display results
    $smarty->display('adddata.html');
	
	function adddata($AccountId, $strClass, $strItem, $txtUseDate, $nAmount, $strDesc)
	{

		if (empty($AccountId)) {
			return false;
		}
		
		try {
			$useDate = ledger_datetime($txtUseDate);
			if ($useDate === null) {
				return false;
			}
			$stmt = app_db()->prepare('INSERT INTO Detail(AccountId, Class, Item, Amount, Descri, UseDate, Need) VALUES (:id, :class, :item, :amount, :descri, :usedate, :need)');
			$stmt->execute(array(
				':id' => (int) $AccountId,
				':class' => $strClass,
				':item' => $strItem,
				':amount' => ledger_amount($nAmount),
				':descri' => $strDesc,
				':usedate' => $useDate,
				':need' => 'Y',
			));
		}
		catch ( PDOException $e ) {
			db_fail($e);
		}

		return true;
	}

	function updatedata($AccountId, $SId, $strClass, $strItem, $txtUseDate, $nAmount, $strDesc)
	{

		if (empty($AccountId)) {
			return false;
		}
		
		try {
			$useDate = ledger_datetime($txtUseDate);
			if ($useDate === null) {
				return false;
			}
			$stmt = app_db()->prepare('UPDATE Detail SET Class = :class, Item = :item, Amount = :amount, Descri = :descri, UseDate = :usedate WHERE AccountId = :id AND SId = :sid');
			$stmt->execute(array(
				':class' => $strClass,
				':item' => $strItem,
				':amount' => ledger_amount($nAmount),
				':descri' => $strDesc,
				':usedate' => $useDate,
				':id' => (int) $AccountId,
				':sid' => (int) $SId,
			));
		}
		catch ( PDOException $e ) {
			db_fail($e);
		}

		return true;
	}


	function deletedata($AccountId, $SId)
	{

		if (empty($AccountId)) {
			return false;
		}
		
		try {
			$stmt = app_db()->prepare('DELETE FROM Detail WHERE SId = :sid AND AccountId = :id');
			$stmt->execute(array(':sid' => (int) $SId, ':id' => (int) $AccountId));
		}
		catch ( PDOException $e ) {
			db_fail($e);
		}

		return true;
	}

	function get_edit_data($AccountId, $SId)
	{

		if (empty($AccountId)) {
			return false;
		}
		
		try {
			$_data = array();
			$rows = db_rows("SELECT AccountId, SId, Class, Item, Amount, Descri, strftime('%Y/%m/%d', UseDate) UseDate, Need FROM Detail WHERE AccountId = :id AND SId = :sid", array(':id' => (int) $AccountId, ':sid' => (int) $SId));
            foreach ( $rows as $value)
            {
                $_data[] = $value;
            };

			return $_data;
			 
		}
		catch ( PDOException $e ) {
			print( "Error connecting to SQL Server." );
			db_fail($e);
			return false;
		}

		return true;
	}
	
	function diff_date($date1, $date2)
	{
	    $sign = 1;
	    
	    $diff = strtotime($date1) - strtotime($date2);
	    
	    if($diff < 0)
	        $sign = -1;
	    
	    $diff = abs($diff);
	     
	    $days = floor($diff / (60*60*24));
	    
	    return $days * $sign;
	}
	
	function get_task($AccountId) {
	
	    // 三天後
	    $NewDate = date('Y-m-d', strtotime("+5 days"));
	    $ToDate = date('Y-m-d');
	
	    try {
	        $rows = db_rows('SELECT TaskId, Desci, ClassName, M1, M2, M3, M4, M5, M6, M7, M8, M9, M10, M11, M12, TaskDay, Amount FROM Task WHERE AccountId = :id', array(':id' => (int) $AccountId));
	
	        foreach ( $rows as $value)
	        {
	            $TaskId = $value[0];
	            $Desci = $value[1];
	            $ClassName = $value[2];
	            $M1 = $value[3];
	            $M2 = $value[4];
	            $M3 = $value[5];
	            $M4 = $value[6];
	            $M5 = $value[7];
	            $M6 = $value[8];
	            $M7 = $value[9];
	            $M8 = $value[10];
	            $M9 = $value[11];
	            $M10 = $value[12];
	            $M11 = $value[13];
	            $M12 = $value[14];
	            $TaskDay = $value[15];
	            $Amount = $value[16];
	
	            // 一月有可能跨年
	            if($M1 == "1")
	            {
	                if(date("Y",$NewDate) != date("Y",$ToDate))
	                {
	                    $TaskDate = substr($NewDate, 0, 4) . "-" . "01" . "-" . $TaskDay;
	                    $diff = diff_date($TaskDate, $NewDate);
	                    if (($diff >= -5) && ($diff < 1))
	                    {
	                        $REMINDER = $REMINDER . $Desci;
	                        $REMINDER = $REMINDER . "_";
	                        $REMINDER = $REMINDER . $TaskDate;
	                        $REMINDER = $REMINDER . "_";
	                        $REMINDER = $REMINDER . $Amount;
	                        $REMINDER = $REMINDER . "_";
	                    }
	                }
	                else
	                {
	                    $TaskDate = substr($ToDate, 0, 4) . "-" . "01" . "-" . $TaskDay;
	                    $diff = diff_date($TaskDate, $NewDate);
	                    if (($diff >= -5) && ($diff < 1))
	                    {
	                        $REMINDER = $REMINDER . $Desci;
	                        $REMINDER = $REMINDER . "_";
	                        $REMINDER = $REMINDER . $TaskDate;
	                        $REMINDER = $REMINDER . "_";
	                        $REMINDER = $REMINDER . $Amount;
	                        $REMINDER = $REMINDER . "_";
	                    }
	                    
	                }
	            }
	            
	            if($M2 == "1")
	            {
                    $TaskDate = substr($NewDate, 0, 4) . "-" . "02" . "-" . $TaskDay;
                    $diff = diff_date($TaskDate, $NewDate);
                    if (($diff >= -5) && ($diff < 1))
                    {
                        $REMINDER = $REMINDER . $Desci;
                        $REMINDER = $REMINDER . "_";
                        $REMINDER = $REMINDER . $TaskDate;
                        $REMINDER = $REMINDER . "_";
                        $REMINDER = $REMINDER . $Amount;
                        $REMINDER = $REMINDER . "_";
                    }
	            }
	            
	            if($M3 == "1")
	            {
	                $TaskDate = substr($NewDate, 0, 4) . "-" . "03" . "-" . $TaskDay;
	                $diff = diff_date($TaskDate, $NewDate);
	                if (($diff >= -5) && ($diff < 1))
	                {
	                    $REMINDER = $REMINDER . $Desci;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $TaskDate;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $Amount;
	                    $REMINDER = $REMINDER . "_";
	                }
	            }
	            
	            if($M4 == "1")
	            {
	                $TaskDate = substr($NewDate, 0, 4) . "-" . "04" . "-" . $TaskDay;
	                $diff = diff_date($TaskDate, $NewDate);
	                if (($diff >= -5) && ($diff < 1))
	                {
	                    $REMINDER = $REMINDER . $Desci;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $TaskDate;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $Amount;
	                    $REMINDER = $REMINDER . "_";
	                }
	            }
	            
	            if($M5 == "1")
	            {
	                $TaskDate = substr($NewDate, 0, 4) . "-" . "05" . "-" . $TaskDay;
	                $diff = diff_date($TaskDate, $NewDate);
	                if (($diff >= -5) && ($diff < 1))
	                {
	                    $REMINDER = $REMINDER . $Desci;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $TaskDate;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $Amount;
	                    $REMINDER = $REMINDER . "_";
	                }
	            }
	            
	            if($M6 == "1")
	            {
	                $TaskDate = substr($NewDate, 0, 4) . "-" . "06" . "-" . $TaskDay;
	                $diff = diff_date($TaskDate, $NewDate);
	                if (($diff >= -5) && ($diff < 1))
	                {
	                    $REMINDER = $REMINDER . $Desci;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $TaskDate;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $Amount;
	                    $REMINDER = $REMINDER . "_";
	                }
	            }
	            
	            if($M7 == "1")
	            {
	                $TaskDate = substr($NewDate, 0, 4) . "-" . "07" . "-" . $TaskDay;
	                $diff = diff_date($TaskDate, $NewDate);
	                if (($diff >= -5) && ($diff < 1))
	                {
	                    $REMINDER = $REMINDER . $Desci;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $TaskDate;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $Amount;
	                    $REMINDER = $REMINDER . "_";
	                }
	            }
	            
	            if($M8 == "1")
	            {
	                $TaskDate = substr($NewDate, 0, 4) . "-" . "08" . "-" . $TaskDay;
	                $diff = diff_date($TaskDate, $NewDate);
	                if (($diff >= -5) && ($diff < 1))
	                {
	                    $REMINDER = $REMINDER . $Desci;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $TaskDate;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $Amount;
	                    $REMINDER = $REMINDER . "_";
	                }
	            }
	            
	            if($M9 == "1")
	            {
	                $TaskDate = substr($NewDate, 0, 4) . "-" . "09" . "-" . $TaskDay;
	                $diff = diff_date($TaskDate, $NewDate);
	                if (($diff >= -5) && ($diff < 1))
	                {
	                    $REMINDER = $REMINDER . $Desci;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $TaskDate;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $Amount;
	                    $REMINDER = $REMINDER . "_";
	                }
	            }
	            
	            if($M10 == "1")
	            {
	                $TaskDate = substr($NewDate, 0, 4) . "-" . "10" . "-" . $TaskDay;
	                $diff = diff_date($TaskDate, $NewDate);
	                if (($diff >= -5) && ($diff < 1))
	                {
	                    $REMINDER = $REMINDER . $Desci;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $TaskDate;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $Amount;
	                    $REMINDER = $REMINDER . "_";
	                }
	            }
	            
	            if($M11 == "1")
	            {
	                $TaskDate = substr($NewDate, 0, 4) . "-" . "11" . "-" . $TaskDay;
	                $diff = diff_date($TaskDate, $NewDate);
	                if (($diff >= -5) && ($diff < 1))
	                {
	                    $REMINDER = $REMINDER . $Desci;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $TaskDate;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $Amount;
	                    $REMINDER = $REMINDER . "_";
	                }
	            }
	            
	            if($M12 == "1")
	            {
	                $TaskDate = substr($NewDate, 0, 4) . "-" . "12" . "-" . $TaskDay;
	                $diff = diff_date($TaskDate, $NewDate);
	                if (($diff >= -5) && ($diff < 1))
	                {
	                    $REMINDER = $REMINDER . $Desci;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $TaskDate;
	                    $REMINDER = $REMINDER . "_";
	                    $REMINDER = $REMINDER . $Amount;
	                    $REMINDER = $REMINDER . "_";
	                }
	            }
	        };
	    }
	    catch ( PDOException $e ) {
	        print( "Error connecting to SQL Server." );
	        db_fail($e);
	    }

	    return $REMINDER;
	}

	function get_month_spend($AccountId) {

		$sdate = date('Y/m/')."01";
		$edate = date("Y/m/t", strtotime($sdate));
    
        try {
            $stmt = app_db()->prepare("SELECT sum(Amount)*-1 AS spend FROM Detail WHERE AccountId = :id AND strftime('%Y/%m/%d', UseDate) >= :sdate AND strftime('%Y/%m/%d', UseDate) <= :edate");
            $stmt->execute(array(':id' => (int) $AccountId, ':sdate' => $sdate, ':edate' => $edate));
            $spend = $stmt->fetchColumn();
      

        }
        catch ( PDOException $e ) {
           print( "Error connecting to SQL Server." );
           db_fail($e);
        }
    
        
        return $spend;

    }

    
    function get_db_results($AccountId) {
    
        try {
            $_data = array();
            $rows = db_rows("SELECT AccountId, SId, Class, Item, Amount, Descri, strftime('%Y/%m/%d', UseDate) UseDate, Need FROM Detail WHERE AccountId = :id ORDER BY UseDate DESC, SId DESC", array(':id' => (int) $AccountId));

            foreach ( $rows as $value)
            {
                $_data[] = $value;
            };
           
            $stmt = app_db()->prepare('SELECT count(*) AS total FROM Detail WHERE AccountId = :id');
            $stmt->execute(array(':id' => (int) $AccountId));
            $rowCount = $stmt->fetchColumn();
      
            SmartyPaginate::setTotal($rowCount);

        }
        catch ( PDOException $e ) {
           print( "Error connecting to SQL Server." );
           db_fail($e);
        }
    
        
        return array_slice($_data, SmartyPaginate::getCurrentIndex(),
            SmartyPaginate::getLimit());

    }

	function get_qry_detail_results_log($AccountId, $qrydate, $type, $label) {
        return '';
    }

    function get_qry_detail_results($AccountId, $qrydate, $type, $label) {
    
        try {
            $_data = array();
            $rows = db_rows("SELECT AccountId, SId, Class, Item, Amount, Descri, strftime('%Y/%m/%d', UseDate) UseDate, Need FROM Detail WHERE AccountId = :id AND Item = :label AND strftime('%Y/%m', UseDate) = :qrydate ORDER BY UseDate DESC, SId DESC", array(':id' => (int) $AccountId, ':label' => $label, ':qrydate' => $qrydate));

            foreach ( $rows as $value)
            {
                $_data[] = $value;
            };
           
      
        }
        catch ( PDOException $e ) {
           print( "Error connecting to SQL Server." );
           db_fail($e);
        }
    
        
        return $_data;

    }

    function get_qry_results($AccountId, $keyword) {
    
        try {
            $_data = array();
            $rows = db_rows("SELECT AccountId, SId, Class, Item, Amount, Descri, strftime('%Y/%m/%d', UseDate) UseDate, Need FROM Detail WHERE AccountId = :id AND (Class LIKE :kw1 OR Item LIKE :kw2 OR Descri LIKE :kw3) ORDER BY UseDate DESC, SId DESC", array(':id' => (int) $AccountId, ':kw1' => '%' . $keyword . '%', ':kw2' => '%' . $keyword . '%', ':kw3' => '%' . $keyword . '%'));

            foreach ( $rows as $value)
            {
                $_data[] = $value;
            };
           
      
        }
        catch ( PDOException $e ) {
           print( "Error connecting to SQL Server." );
           db_fail($e);
        }
    
        
        return $_data;

    }

    function get_item_results($AccountId) {
    
        try {
            $_data = array();
            $rows = db_rows('SELECT DISTINCT item, class FROM detail WHERE AccountId = :id ORDER BY item COLLATE NOCASE DESC, class COLLATE NOCASE DESC', array(':id' => (int) $AccountId));

            foreach ( $rows as $value)
            {
                $class = $value[1];
                $item = $value[0];
                

                if(!is_null(@$_data[$item]))
                    $_data[$item] = array_merge($_data[$item], array($class => $class));
                else 
                    $_data[$item] = array($class => $class);
                    
            };
           
        }
        catch ( PDOException $e ) {
           print( "Error connecting to SQL Server." );
           db_fail($e);
        }
    
        
        return $_data;

    }


    function get_class_results($AccountId) {
    
        try {
            $_data = array();
            $rows = db_rows('SELECT ItemName FROM Item WHERE AccountId = :id ORDER BY ItemName DESC', array(':id' => (int) $AccountId));

            foreach ( $rows as $value)
            {
                $_data[] = $value; 
            };
           
        }
        catch ( PDOException $e ) {
           print( "Error connecting to SQL Server." );
           db_fail($e);
        }
    
        
        return $_data;

    }



// no closing tag to prevent accidental output