<?php
	

class Fun
{
    private $pdo = null;
    private $start_time = 0;

    /**
     * 构造方法连接数据库
     * @param 
     * @throws 
     */
    public function __construct(){
        ini_set('max_execution_time', '0');    //设置运行不超时
        $this->start_time = time();

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
     * 查询省内所有建筑业在注册人员信息
     * @param string $code 公司统一社会信用代码
     * @throws Exception
     */
    public function getProvincePerson(){
        $curl=curl_init();

        $data = [
            'Pager1'=>'Go',
            '__VIEWSTATE' => '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'
        ];
        $data['Pager1_input'] = '600';

        curl_setopt($curl, CURLOPT_URL, 'http://220.197.219.123:88/Web/dataapp/per_dataResult.aspx');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec()获取的信息以字符串返回
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $body=curl_exec($curl);
        preg_match("/第.*\/(.*?)页，/", $body,$arr);  //获取总数
        $num = 0;
        $length = (int)(trim($arr[1]));

         $useragent = array(
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)',
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2)',
            'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2) Gecko/2008070208 Firefox/3.0.1',
            'Opera/9.27 (Windows NT 5.2; U; zh-cn)',
            'Opera/8.0 (Macintosh; PPC Mac OS X; U; en)',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.2.149.27 Safari/525.13 ',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Version/3.1 Safari/525.13'

        );

        for($i=990;$i<$length;$i++){
            
            $data['Pager1_input'] = $i;
            curl_setopt($curl, CURLOPT_URL, 'http://220.197.219.123:88/Web/dataapp/per_dataResult.aspx');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec()获取的信息以字符串返回
            curl_setopt($curl, CURLOPT_POST, 1);
            if($i%2==0){
                curl_setopt($curl, CURLOPT_PROXY,'218.14.115.211:3128');
            }
                curl_setopt($curl, CURLOPT_USERAGENT, array_rand($useragent));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $body=curl_exec($curl);
            preg_match_all("/\"100\">(.*?)<\/td><td>(.*?)<\/td><td>(.*?)<\/td><td align=\"center\">(.*?)<\/td><td align=\"center\">(.*?)</", $body,$arr);
            array_shift($arr);
            foreach ($arr[0] as $k => $v) {
                $time = time();
                $sql = "insert into company_person (company_name,source,name,reg_num,major,end_time,created_at) value ('{$arr[1][$k]}','贵州住建平台','{$arr[0][$k]}','{$arr[3][$k]}','{$arr[2][$k]}','{$arr[4][$k]}','{$time}')";
                $res = $this->pdo->exec($sql);
                if($res){
                    $num++;
                }
            }

        }

        return ['state'=>'success','time'=>time()-$this->start_time,'num'=>$num];

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
        curl_close($curl);

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
            // return $array[1];
            foreach ($array[1] as $k => $v) {
                $arr[$j][0] = $v;
                $arr[$j][1] = $array[2][$k];
                $arr[$j][2] = $array[3][$k];
                $arr[$j][3] = $array[4][$k];
                $arr[$j][4] = $array[5][$k];
                $arr[$j][5] = $array[6][$k];
                $j++;
                $cid = 1;   //通过id查询cid
                $time = time();
                $sql = "insert into company_person (cid,`name`,reg_num,start_time,end_time,`range`,state,remarks,created_at) values ('{$cid}','{$p[0]}','{$p[1]}','{$p[2]}','{$p[3]}','{$p[4]}','1','','{$time}')";
                $res[] = $this->pdo->exec($sql);
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

    /**
     * 四库一平台ID
     * @param string $code 
     * @throws Exception
     */
    public function getSikuId(){
        $sql = "select credit_code,company_name from company_excel where siku_num=''";
        $stmt = $this->pdo->query($sql);
        $res=$stmt->fetchAll(PDO::FETCH_NUM);//索引键
        foreach ($res as $k => $v) {
            $temp = $v[0];
            $code = $this->getIndexId($temp);
            if($code){
                $sql = "update company_excel set siku_num='{$code}' where credit_code='{$temp}'";
                $this->pdo->exec($sql);
            }else{
                $response = $this->getIndexId($v[1]);
                if($response){
                    $sql = "update company_excel set siku_num='{$code}' where credit_code='{$temp}'";
                    $this->pdo->exec($sql);
                }else{
                    $sql = "update company_excel set siku_num='0' where credit_code='{$temp}'";
                    $this->pdo->exec($sql);
                }
                
            }
        }
        return 'success';
    }

}

	