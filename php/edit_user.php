<?php
require_once("db.php");
$db=new WJRdb();
$db->connect();
if(isset($_FILES)){
    $FILE=$_FILES[0];
    $tmp_name=$pic['tmp_name'];
    $dir="pic/user/";
    smkdir("../".$dir);
    move_uploaded_file($tmp_name,"../".$dir);
    $db->query("update user (photopath) values ('$dir')");
}
$db->query("update user (address,telephone,nickname) values (".$_POST['address'].",".$_POST['telephone'].",".$_POST['nickname'].")");
?>