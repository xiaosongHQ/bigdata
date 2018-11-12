<?php
	

class Fun
{
    private $pdo = null;

    /**
     * 构造方法连接数据库
     * @param 
     * @throws 
     */
    public function __construct(){
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=data','root','root');
            $this->pdo = $pdo;
        } catch (PDOException $e) {
            echo 'error:'.$e->getMessage();
        }
    }

	/**
     * 根据公司统一信用代码查询公司资质文件
     * @param string $code 公司统一社会信用代码
     * @throws Exception
     */
    public function getFile($code){

        $start_time = time();

        $id  = $this->getIndexId($code);

        if(!$id) return '未获取到相关id信息';
        
        $arr = $this->getFileNumber($id);

        if(!$arr) return '未获取到相关资质信息';

        $res = [];

        foreach ($arr as $k => $v) {
            $p = $this->getFileInfo($id,$v);
            $cid = 1;   //通过id查询cid
            $time = time();
            $sql = "insert into company_file (cid,`name`,reg_num,start_time,end_time,`range`,state,remarks,created_at) values ('{$cid}','{$p[0]}','{$p[1]}','{$p[2]}','{$p[3]}','{$p[4]}','1','','{$time}')";
            $res[] = $this->pdo->exec($sql);
        }

        return ['success'=>'ok','number'=>count($res),'time'=>(time()-$start_time)];

    }


	/**
     * 根据公司统一信用代码查询公司注册人员信息
     * @param string $code 公司统一社会信用代码
     * @throws Exception
     */
    public function getPerson($code){

        $id  = $this->getIndexId($code);

        if(!$id) return '未获取到相关id信息';

        $arr = $this->getPersonInfo($id);

        if(!$arr) return '未获取到相关注册人员信息';

        return $arr;

    }


    /**
     * 根据公司统一信用代码查询公司注册项目信息
     * @param string $code 公司统一社会信用代码
     * @throws Exception
     */
    public function getProject($code){

        $id  = $this->getIndexId($code);

        if(!$id) return '未获取到相关id信息';

        $arr = $this->getProjectInfo($id);

        return $arr;

    }



	/**
     * 根据公司统一信用代码查询公司id
     * @param string $code 公司统一社会信用代码
     * @throws Exception
     */
    private function getIndexId($code){
        $curl=curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://jzsc.mohurd.gov.cn/dataservice/query/comp/list?complexname='.$code);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec()获取的信息以字符串返回
        $body=curl_exec($curl);

        preg_match("/href=\"\/dataservice\/query\/comp\/compDetail\/(.*)\"/", $body,$arr);

        if (isset($arr[1])) {
            return $arr[1];
        }else{
            return false;
        }

    }


	/**
     * 根据公司id查询公司资质文件编号
     * @param string $id 公司id
     * @throws Exception
     */
    private function getFileNumber($id)
    {
        $curl=curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://jzsc.mohurd.gov.cn/dataservice/query/comp/caDetailList/'.$id);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec()获取的信息以字符串返回
        $body=curl_exec($curl);

        preg_match_all("/证书号\">(.*?)</", $body,$arr);
        if (isset($arr[1])) {
            return $arr[1];
        }else{
            return false;
        }
    }


     /**
     * 根据公司资质文件编号和公司id查询资质文件信息
     * @param string $id 公司id
     * @param string $number 资质文件编号
     * @throws Exception
     */
    private function getFileInfo($id,$number)
    {

        $curl=curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://jzsc.mohurd.gov.cn/dataservice/query/comp/caCertDetail/'.$id.'?certno='.$number);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec()获取的信息以字符串返回
        $body=curl_exec($curl);

        if(strstr($body,'备注')){     //判断有无备注信息
            preg_match("/企业名称[\s\S]*?<b>(.*?)<\/b>[\s\S]*?<\/dd[\s\S]*?证书编号[\s\S]*?<dd>(.*)[\s]*?<\/dd[\s\S]*?发证日期[\s\S]*?<dd>(.*)[\s]*?<\/dd[\s\S]*?有效期至：<\/dt>[\s\S]*?<dd>(.*)[\s]*<\/dd[\s\S]*?范围：<\/dt>[\s\S]*?<dd>(.*?)<\/dd[\s\S]*?备注：<\/dt>[\s\S]*?<dd>([\s\S]*?)<\/dd/", $body,$arr);
        }else{
            preg_match("/企业名称[\s\S]*?<b>(.*?)<\/b>[\s\S]*?<\/dd[\s\S]*?证书编号[\s\S]*?<dd>(.*)[\s]*?<\/dd[\s\S]*?发证日期[\s\S]*?<dd>(.*)[\s]*?<\/dd[\s\S]*?有效期至：<\/dt>[\s\S]*?<dd>(.*)[\s]*<\/dd[\s\S]*?范围：<\/dt>[\s\S]*?<dd>(.*?)<\/dd/", $body,$arr);
        }

        array_shift($arr);
        return $arr;
    }

    /**
     * 根据公司id查询公司注册人才信息
     * @param string $id 公司id
     * @throws Exception
     */
    private  function getPersonInfo($id){

        $curl=curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://jzsc.mohurd.gov.cn/dataservice/query/comp/regStaffList/'.$id);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec()获取的信息以字符串返回
        $body=curl_exec($curl);

        preg_match("/total\":(.*?),/", $body,$arr);  //获取总数

        if(count($arr)>0){  //判断有无分页
            $num = $arr[1];
        }else{
            $num = 25;
        }

        $page=1;
        $arr=[];
        $j=0;

        for($i=0;$i<=$num;$i+=25){
            curl_setopt($curl, CURLOPT_URL, 'http://jzsc.mohurd.gov.cn/dataservice/query/comp/regStaffList/'.$id.'?$pg='.$page);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $body=curl_exec($curl);

            preg_match_all("/l\/(.*?)\'\">(.*?)<[\s\S]*?证号\">(.*?)<[\s\S]*?类别\">(.*?)<[\s\S]*?章号）\">(.*?)<[\s\S]*?专业\">(.*?)</", $body,$array);

            if(!isset($array[1])){
	        	return false;
	        }

            foreach ($array[1] as $k => $v) {
                $arr[$j][0] = $v;
                $arr[$j][1] = $array[2][$k];
                $arr[$j][2] = $array[3][$k];
                $arr[$j][3] = $array[4][$k];
                $arr[$j][4] = $array[5][$k];
                $arr[$j][5] = $array[6][$k];
                $j++;
            }

            $page++;
        }
        return $arr;
    }

        /**
     * 根据公司id查询公司所有项目
     * @param string $id 公司id
     * @throws Exception
     */
    private function getProjectInfo($id){
        $curl=curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://jzsc.mohurd.gov.cn/dataservice/query/comp/compPerformanceListSys/'.$id);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec()获取的信息以字符串返回
        $body=curl_exec($curl);
        preg_match_all("/码\">(.*)</", $body,$array);


        preg_match("/total\":(.*?),/", $body,$arr);  //获取项目总数

        if(count($arr)>0){  //判断有无分页
            $num = $arr[1];
        }else{
            $num = 25;
        }

        $page=1;
        $arr=[];

        for($i=0;$i<=$num;$i+=25){
            $curl=curl_init();
            curl_setopt($curl, CURLOPT_URL, 'http://jzsc.mohurd.gov.cn/dataservice/query/comp/compPerformanceListSys/'.$id.'?$pg='.$page);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec()获取的信息以字符串返回
            $body=curl_exec($curl);
            preg_match_all("/码\">(.*)</", $body,$array);

            foreach ($array[1] as $key => $value) {
                $arr[] = $value;
            }

            $page++;
        }

        $project = [];
            
        foreach ($arr as $k => $v) {
        	$curl=curl_init();
	        curl_setopt($curl, CURLOPT_URL, 'http://jzsc.mohurd.gov.cn/dataservice/query/project/projectDetail/'.$v);
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec()获取的信息以字符串返回
	        $body=curl_exec($curl);

	        preg_match("/title=\".*i>(.*)<\/b>.*\s*.*\s*.*\s*.*\s*.*\s*.*<dl>.*\s*.*\s*.*<dd><span>项目编号：<\/span>(.*)<\/dd>\s*.*<dd><span>省级项目编号：<\/span>(.*)<\/dd>\s*.*\s*.*<dd><span>所在区划：<\/span>(.*)<\/dd>\s*.*<dd><span>建设单位：<\/span>(.*)<\/dd>\s*.*<dd style=\"width: 66%\"><span>建设单位组织机构代码（统一社会信用代码）：<\/span>(.*)<\/dd>\s*.*<dd><span>项目分类：<\/span>(.*)<\/dd>\s*.*<dd><span>建设性质：<\/span>(.*)<\/dd>\s*.*<dd><span>工程用途：<\/span>(.*)<\/dd>\s*.*<dd><span>总投资：<\/span>(.*)<\/dd>\s*.*<dd><span>总面积：<\/span>(.*)<\/dd>\s*.*<dd><span>立项级别：<\/span>(.*)<\/dd>\s*.*<dd><span>立项文号：<\/span>(.*)</", $body,$arr2);

	        array_shift($arr2);
            $project[] = $arr2;
        }

        return $project;
    }

}

	