<?php


require_once(__DIR__ . '/app/fun.class.php');

ini_set('max_execution_time', '0');    //设置运行不超时

$d = new Fun();

//获得公司资质信息
// $d->getfile('91520400683986162U');

//获得公司人才信息
// echo 123;exit;

if($_GET['type']==0 && $_GET['content']==1){
	// echo json_encode($d->getFile('91330102717691662X'));
	echo json_encode($d->getFile('91330102717691662X'));
}else{
	echo "参数异常";
}


// echo "<pre>";
// var_dump($d->getFile('91330102717691662X'));





	