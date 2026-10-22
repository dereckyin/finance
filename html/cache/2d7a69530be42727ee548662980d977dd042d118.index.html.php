<?php
/* Smarty version 3.1.46, created on 2025-09-17 04:28:56
  from '/var/www/html/index.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.46',
  'unifunc' => 'content_68ca39084dd007_91068230',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '15ca607cf98af82521b79014867d28ddd0640b52' => 
    array (
      0 => '/var/www/html/index.html',
      1 => 1758082462,
      2 => 'file',
    ),
  ),
  'cache_lifetime' => 120,
),true)) {
function content_68ca39084dd007_91068230 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>家庭速記簿</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" type="text/css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="class/jQuery-Validation-Engine/js/languages/jquery.validationEngine-zh_TW.js" type="text/javascript" charset="utf-8"></script>
	<script src="class/jQuery-Validation-Engine/js/jquery.validationEngine.js" type="text/javascript" charset="utf-8"></script>
	<link rel="stylesheet" href="class/jQuery-Validation-Engine/css/validationEngine.jquery.css" type="text/css"/>
    
<script type="text/javascript">
    function validate() {
			
		}
		function formSuccess() {
			alert('Success!');
		}
		
		function formFailure() {
			alert('Failure!');
		}
		jQuery(document).ready(function(){
			// binds form submission and fields to the validation engine
			jQuery("#login_form").validationEngine({
				onFormSuccess:formSuccess,
				onFormFailure:formFailure
			});
		});
</script>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="container-fluid" id="wrap"><img src="images/ledger-1428230_1280.jpg" alt="Placeholder image" class="img-responsive" >
    <div class="container contentwrap">
    <div class="row">
      <div class="col-md-6">
    	<div class="panel panel-primary">
       	  <div class="panel-heading">登入</div>
            <div class="panel-body">
            	<form action="index.php" id="login_form" method="post">
                	<div class="form-group">
      <label class="col-md-4 control-label" for="txtId">帳號：</label>
      <div class="col-md-8">
        <input type="text" class="form-control validate[required]" name="txtId" id="txtId" placeholder="請輸入帳號" >
      </div>
      <label class="col-md-4 control-label" for="txtPwd">密碼：</label>
      <div class="col-md-8 has-error">
        <input type="password" class="form-control validate[required]" name="txtPwd" id="txtPwd" placeholder="請輸入密碼">
      </div>
    </div>
    
    <div class="form-group">
    <label class="col-md-4">
      
    </label>
    <div class="col-md-8">
      <div class="form-inline" style="margin-bottom:10px;">
        <img src="captcha.php" alt="captcha" onclick="this.src='captcha.php?'+Date.now();" style="cursor:pointer;border:1px solid #ccc;border-radius:4px;" title="點擊更新驗證碼">
        <input type="text" class="form-control" name="captcha" placeholder="請輸入圖形驗證碼" style="margin-left:10px;" required>
      </div>
      <input type="hidden" name="op" value="user_login">
      <button type="button" name="button" onclick="jQuery('#login_form').submit();" class="btn btn-primary btn-block">登入</button>
    </div>
  </div>
                </form>
                </div>
                </div>
                </div>   
  		
  <div class="col-md-6">
    <article>
      <div class="list-group" id="list_padding"> <a href="#" class="list-group-item">
        <h4 class="list-group-item-heading">記帳控制支出</h4>
        <p class="list-group-item-text yellow_title">記帳控制支出
    「當錢不夠用時，節流比開源還重要」理財專家建議，家庭開銷應該要透過「記帳」的方式，先掌握每月的固定開銷有多少，再慢慢地從中減少不必要的支出，自然就有餘錢可以存下來。

    而記帳需要耐心與方便的工具，之前一直找不到適當的記帳軟體，常常一出遠門，或是一忙時就忘記記帳了，所以寫了這個網站。

     用網站記帳的好處，就是不論隨時隨地，只要有連結網路的電腦，都可以記錄自己的開銷，如此，不管是出國玩，或是在家中或辦公室，都可以隨時記錄，而且電腦重灌，也不怕資料遺失。</p>
        </a>
        <h4 class="list-group-item-heading">使用說明</h4>
       
        <a class="list-group-item">
          <h4 class="list-group-item-heading">關於帳號申請</h4>
          <p class="list-group-item-text">對不起 網站目前私人使用中 暫不公開</p>
        </a></div>
</article>
    
  </div>

         
          
    	</div>
        </div>
        <footer class="footer_style">Copyright &copy; 2007, www.finance.myvnc.com. All Rights Reserved. </footer>
    </div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html><?php }
}
