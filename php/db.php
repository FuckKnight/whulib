<?php
session_start();
date_default_timezone_set("PRC");
function smkdir($path)
{
	$temp=explode('/',$path);
	$p='';
	$result=true;
	foreach($temp as $value)
	{
		$p.=$value.'/';
	    if(!is_dir($p)) $result=$result&&@mkdir($p);
    }return $result;
}
class WJRdb{
    function connect()
    {
        $con=mysql_connect("localhost","ivy","123456");
        if(!$con)return -1;
        mysql_select_db("whulib");
        return 0;
    }
    function query($sqlinfo)
    {
        if(strpos($sqlinfo,"delete")===0||strpos($sqlinfo,"update")===0)mysql_query($sqlinfo);
        else if(strpos($sqlinfo,"insert")===0){
            mysql_query($sqlinfo);
            $res=mysql_insert_id();
        }else if(strpos($sqlinfo,"select")===0)
        {
            $result=mysql_query($sqlinfo);
            $res=array();
            while($tmp=mysql_fetch_array($result))array_push($res,$tmp);
        }return $res;
    }
}
?>