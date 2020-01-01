<?php

require_once 'connect.php';

mysql_connect($dbhost, $dbuser, $dbpass);//連結伺服器
mysql_select_db("stock");//選擇資料庫
mysql_query("set names utf8");//以utf8讀取資料，讓資料可以讀取中文
if (date('w')==0){
    $getDate = date('Y-m-d',strtotime("-2 days"));
}
elseif (date('w')==6)
{
    $getDate = date('Y-m-d',strtotime("-1 days"));
}
else{
    $getDate= date("Y-m-d");
}


if (isset($_POST['start_date']) && isset($_POST['end_date']))
{
//$conn = mysql_connect("localhost","DBusername","DBpassword");
//mysql_select_db("DBname",$conn);
$start_date = mysql_real_escape_string($_POST['start_date']);
$end_date = mysql_real_escape_string($_POST['end_date']);
//$result = mysql_query("SELECT * FROM table WHERE username='$uname' and password ='$pass'") or die(mysql_error());
//$row  = mysql_fetch_array($result);

$data=mysql_query("SELECT * FROM `legalperson` WHERE date BETWEEN '$start_date' AND '$end_date'");//從contact資料庫中選擇所有的資料表
}
else 
{ 
$data=mysql_query("SELECT * FROM `legalperson` WHERE date='$getDate' ORDER BY date DESC");//從contact資料庫中選擇所有的資料表
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>三大法人買賣超股數</title>
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>    

<body>
<a href="legal_person.php" >三大法人買賣超股數</a>
     <a href="price.php" >股票漲跌</a>
     
<p>

<!--<input type="text" id="start_date" value="" />
<input type="text" id="end_date" value="" />-->
<form name="Filter" method="POST" action="legal_person.php">
    From:
    <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" />
    
    To:
    <input type="date" name="end_date" value="<?php echo date('Y-m-d'); ?>" />
    <input type="submit" name="submit" value="GO"/>
</form>
<?php 
if (isset($_POST['start_date']) && isset($_POST['end_date']))
{
    echo $start_date.' ~ '.$end_date;
}
else
{
    echo $getDate;
}
?>

</p>
<form method='post' action='download_csv.php'>
  <input type='submit' value='Export' name='Export'>
<table  border="1">
  <tr>

<td>證券代號</td>
<td>證券名稱</td>
<td>外陸資買進股數(不含外資自營商)</td>
<td>外陸資賣出股數(不含外資自營商)</td>
<td>外陸資買賣超股數(不含外資自營商)</td>
<td>外資自營商買進股數</td>
<td>外資自營商賣出股數</td>
<td>外資自營商買賣超股數</td>
<td>投信買進股數</td>
<td>投信賣出股數</td>
<td>投信買賣超股數</td>
<td>自營商買賣超股數</td>
<td>自營商買進股數(自行買賣)</td>
<td>自營商賣出股數(自行買賣)</td>
<td>自營商買賣超股數(自行買賣)</td>
<td>自營商買進股數(避險)</td>
<td>自營商賣出股數(避險)</td>
<td>自營商買賣超股數(避險)</td>
<td>三大法人買賣超股數</td>
<td>日期</td>
  </tr>
<?php
$csv_output .="證券代號,證券名稱,外陸資買進股數(不含外資自營商),外陸資賣出股數(不含外資自營商),外陸資買賣超股數(不含外資自營商),外資自營商買進股數,外資自營商賣出股數,外資自營商買賣超股數,投信買進股數,投信賣出股數,投信買賣超股數,自營商買賣超股數,自營商買進股數(自行買賣),自營商賣出股數(自行買賣),自營商買賣超股數(自行買賣),自營商買進股數(避險),自營商賣出股數(避險),自營商買賣超股數(避險),三大法人買賣超股數,日期\n";


for($i=1;$i<=mysql_num_rows($data);$i++){
$rs=mysql_fetch_row($data);
?>
<!--
  <tr>
    <td><?php echo $rs[0]?></td>
    <td><?php echo $rs[1]?></td>
    <td><?php echo $rs[2]?></td>
    <td><?php echo $rs[3]?></td>
    <td><?php echo $rs[4]?></td>
    <td><?php echo $rs[5]?></td>
    <td><?php echo $rs[6]?></td>
    <td><?php echo $rs[7]?></td>
    <td><?php echo $rs[8]?></td>
    <td><?php echo $rs[9]?></td>
    <td><?php echo $rs[10]?></td>
    <td><?php echo $rs[11]?></td>
    <td><?php echo $rs[12]?></td>
    <td><?php echo $rs[13]?></td>
    <td><?php echo $rs[14]?></td>
    <td><?php echo $rs[15]?></td>
    <td><?php echo $rs[16]?></td>
    <td><?php echo $rs[17]?></td>
    <td><?php echo $rs[18]?></td>
    <td><?php echo $rs[19]?></td>
  </tr>
  -->
    <tr>
  <?php
    foreach ($rs as $key) {
    
        echo '<td>'.$key.'</td>';
    $csv_output .=str_replace(',','',$key).", ";
    
    }
  ?>
  </tr>
<?php
$csv_output .="\n";
}
?>
</table>
<input type="hidden" value="<?php echo $csv_output ?>" name="csv_output" id="csv_output">
<input type="hidden" value="legal_person" name="name" id="name">
</form>
<p>&nbsp;</p>
</body>
</html&gt