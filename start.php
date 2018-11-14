<?php


require_once(__DIR__ . '/app/fun.class.php');

ini_set('max_execution_time', '0');    //设置运行不超时
// echo $_POST['str'];exit;
$d = new Fun();

var_dump($d->getSikuId());

// switch ($_POST['str']) {
// 	case '0,0,2,0':
// 		var_dump($d->getProvincePerson());
// 		break;

// 	case '0,0,3,3':
// 		var_dump($d->getSikuId());
// 		break;
	
// 	default:
// 		echo "{error:0000}";
// 		break;
// }


// var_dump($d->getPerson($_GET['code']));exit;
//获得公司资质信息
// $d->getfile('91520400683986162U');

//获得公司人才信息
// echo 123;exit;

// if($_GET['type']==0 && $_GET['content']==1){
// 	// echo json_encode($d->getFile('91330102717691662X'));
// 	echo json_encode($d->getFile($_GET['code']));
// }else if($_GET['type']==0 && $_GET['content']==0){
// 	echo json_encode($d->getPerson($_GET['code']));
// }else{
// 	echo '参数异常';
// }


// echo "<pre>";
// var_dump($d->getFile('91330102717691662X'));





	