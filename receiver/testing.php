<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require './bootstrap.php';
foreach(PDO::getAvailableDrivers() as $driver)
    {
    echo $driver.'<br />';
    }
$x = new Model_DbHandle(array('hostname'=>'localhost','database'=>'gather','username'=>'test','password'=>'test',));
$result = $x->query("select * from transmissions");
var_dump($result);

?>
