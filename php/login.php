<?php
#it will mean nobody in the database if it returns 9
#or it will return the id in the database
include_once("php.db.php");
if($_POST['studentid']&&$_POST['password']){
	$db=new WJRdb();
	$db->connect();
	$stu=$db->query("select id from user where studentid='".$_POST['studentid']."' and password='".$_POST['password']."'");
	if(count($stu)>0)
	{
		echo $stu[0]['id'];
		$_SESSION=array(
			"userid"=>$stu[0]['id'],
			"time"=>time(),
		);
	}
	else echo 0;
}
?>