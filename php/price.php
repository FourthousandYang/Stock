<?php
require_once 'connect.php';
mysql_connect($dbhost, $dbuser, $dbpass);//連結伺服器
mysql_select_db("stock");//選擇資料庫
mysql_query("set names utf8");//以utf8讀取資料，讓資料可以讀取中文
if (date('w')==0){
    $getDate = date('Ymd',strtotime("-2 days"));
}
elseif (date('w')==6)
{
    $getDate = date('Ymd',strtotime("-1 days"));
}
else{
    $getDate= date("Ymd");
}
if (isset($_POST['start_date']) && isset($_POST['end_date']))
{
//$conn = mysql_connect("localhost","DBusername","DBpassword");
//mysql_select_db("DBname",$conn);
$start_date = str_replace('-','',mysql_real_escape_string($_POST['start_date']));
$end_date = str_replace('-','',mysql_real_escape_string($_POST['end_date']));
//$result = mysql_query("SELECT * FROM table WHERE username='$uname' and password ='$pass'") or die(mysql_error());
//$row  = mysql_fetch_array($result);

$data=mysql_query("SELECT * FROM price WHERE date BETWEEN '$start_date' AND '$end_date' ORDER BY date DESC");//從contact資料庫中選擇所有的資料表
}
else 
{ 
$data=mysql_query("SELECT * FROM price WHERE date='$getDate' ORDER BY date DESC");//從contact資料庫中選擇所有的資料表
}

//$data=mysql_query("SELECT * FROM `test2` ORDER BY `test2`.`date` DESC");//從contact資料庫中選擇所有的資料表
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>股票漲跌</title>
</head>

<body>
<a href="legal_person.php" >三大法人買賣超股數</a>
     <a href="price.php" >股票漲跌</a>
     
<p>
<form name="Filter" method="POST" action="price.php">
    From:
    <input type="date" name="start_date" value="<?php echo date('Ymd'); ?>" />
    
    To:
    <input type="date" name="end_date" value="<?php echo date('Ymd'); ?>" />
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
    <td>成交股數</td>
    <td>成交筆數</td>
    <td>成交金額</td>
    <td>開盤價</td>
    <td>最高價</td>
    <td>最低價</td>
    <td>收盤價</td>
    <td>漲跌(+/-)</td>
    <td>漲跌價差</td>
    <td>最後揭示買價</td>
    <td>最後揭示買量</td>
    <td>最後揭示賣價</td>
    <td>最後揭示賣量</td>
    <td>本益比</td>

    <td>日期</td>
  </tr>
<?php
$csv_output .="證券代號,證券名稱,成交股數,成交筆數,成交金額,開盤價,最高價,最低價,收盤價,漲跌(+/-),漲跌價差,最後揭示買價,最後揭示買量,最後揭示賣價,最後揭示賣量,本益比,日期\n";

for($i=1;$i<=mysql_num_rows($data);$i++){
$rs=mysql_fetch_row($data);
?>
  <tr>
    <td><?php echo $rs[0];$csv_output .=str_replace(',','',$rs[0]).", ";?></td>
    <td><?php echo $rs[1];$csv_output .=str_replace(',','',$rs[1]).", ";?></td>
    <td><?php echo $rs[2];$csv_output .=str_replace(',','',$rs[2]).", ";?></td>
    <td><?php echo $rs[3];$csv_output .=str_replace(',','',$rs[3]).", ";?></td>
    <td><?php echo $rs[4];$csv_output .=str_replace(',','',$rs[4]).", ";?></td>
    <td><?php echo $rs[5];$csv_output .=str_replace(',','',$rs[5]).", ";?></td>
    <td><?php echo $rs[6];$csv_output .=str_replace(',','',$rs[6]).", ";?></td>
    <td><?php echo $rs[7];$csv_output .=str_replace(',','',$rs[7]).", ";?></td>
    <td><?php echo $rs[8];$csv_output .=str_replace(',','',$rs[8]).", ";?></td>
    <td><?php echo $rs[9];$csv_output .=str_replace(',','',$rs[9]).", ";?></td>
    <td><?php echo $rs[10];$csv_output .=str_replace(',','',$rs[10]).", ";?></td>
    <td><?php echo $rs[11];$csv_output .=str_replace(',','',$rs[11]).", ";?></td>
    <td><?php echo $rs[12];$csv_output .=str_replace(',','',$rs[12]).", ";?></td>
    <td><?php echo $rs[13];$csv_output .=str_replace(',','',$rs[13]).", ";?></td>
    <td><?php echo $rs[14];$csv_output .=str_replace(',','',$rs[14]).", ";?></td>
    <td><?php echo $rs[15];$csv_output .=str_replace(',','',$rs[15]).", ";?></td>
    
    <td><?php echo $rs[17];$csv_output .=str_replace(',','',$rs[17]).", ";?></td>
  </tr>
<?php
$csv_output .="\n";
}
?>
</table>
<input type="hidden" value="<?php echo $csv_output ?>" name="csv_output" id="csv_output">
<input type="hidden" value="price" name="name" id="name">
</form>
<p>&nbsp;</p>
</body>
</html&gt