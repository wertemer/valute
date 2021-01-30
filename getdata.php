<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once('./class.php');//класс для получения дат из ЦентраБанка
$host='localhost';
$db='kursgr_db';
$user='root';
$pas='gothic1321';
$err=0;
$mes="";
$usd=array();
$eur=array();
$dates=array();
$data=array();
$con=mysqli_connect($host,$user,$pas,$db);
if(!$con){
    $mes.=' Ошибка соединения с Базой данных!';
    die();
}
else{
   $today = new rbc(); //Курс сегодня
    //Проверка существует ли курс на сегодня в нашей базе
    $qtest=mysqli_query($con,"SELECT valdate FROM kursvalut WHERE valdate='".date("Y-m-d")."'");
    if($qtest){
        if(mysqli_num_rows($qtest)>0){
            $test=false;
        }
        else{
            $test=true;
        }
    }
    else{
        $test=false;
    }
	echo $test;
    if($test){
		echo 'now';
        $qaddnow=mysqli_query($con,"INSERT INTO kursvalut(valute_id,valdate,price)VALUES
                          (840,'".date("Y-m-d")."',".$today->curs(840)."),
                          (978,'".date("Y-m-d")."',".$today->curs(978).")");
        if(!$qaddnow){
            echo 'err';
            $mes.=" Ошибка добавления текущего курса валют!";
        }
        else{
            mysqli_commit($con);
        }
    }
    $tommorow= new rbc(strtotime("+1 day")); //Курс на завтра
    if(!$_POST['begin']&&!$_POST['end']){
        $qusd=mysqli_query($con,"SELECT valdate,price,valute_id FROM kursvalut ORDER BY valdate ASC");
    }
    else{
        $qusd=mysqli_query($con,"SELECT valdate,price,valute_id
                FROM kursvalut
                WHERE valdate BETWEEN '".$_POST['begin']."' AND '".$_POST['end']."'
                ORDER BY valdate ASC");
    }
    if(!$qusd){
        $err=1;
        $mes.=' Ошибка получения данных!';
        die();
    }
    else{
        if(mysqli_num_rows($qusd)>0){
            $u=0;
            $b=0;
            while($resd=$qusd->fetch_array(MYSQLI_ASSOC)){
                if($resd['valute_id']==840){
                    $usd[$u]=$resd['price'];
                    $dates[$u]=$resd['valdate'];
                    $u+=1;
                }
                elseif(($resd['valute_id']==978)){
                    $eur[$b]=$resd['price'];
                    $dates[$b]=$resd['valdate'];
                    $b+=1;
                }
            }
            $usd[count($usd)]=(double)$tommorow->curs(840);
            $eur[count($eur)]=(double)$tommorow->curs(978);
            $d = strtotime("+1 day");
            $dates[count($dates)]=date("Y-m-d",$d);
            $u=0;
            $b=0;
        }
        else{
            $err=1;
            $mes.=' Нет данных по доллару!';
        }
    }
    $con->close();
}
if($err!=1){
    $data['usd']=$usd;
    $data['eur']=$eur;
    $data['date']=$dates;
    echo json_encode($data);
}
?>
