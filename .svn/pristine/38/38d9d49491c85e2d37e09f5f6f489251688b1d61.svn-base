<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelNewcoachMain
{
    
    private $coachinfo= // 教练信息
    array(
        'jobtype',  //1 全职 0为兼职
    
        'working_age', // 工龄
    
        'backdrop1',  // 背景1 
        
        'backdrop2',  // 背景2
        
        'backdrop3',  // 背景3
        
        'backdrop4',  // 背景4
        
        'backdrop5',  // 背景5
    
        'soscard_img',  //求生员证图片
    
        'coachcard_img', // 教练资格证书
    
        'takingwork_time',  //入职时间
    
        'takingwork_city',  //入职城市
    
        'takingwork_remark' //教练备注
    );
    private $allowed = array(
        'name',     //姓名
        'sex',      //性别
        'id_card',  //身份证
        'birthday', //  生日
        'marriage',  //  婚姻
        'education',// 教育
        'city',     // 城市
        'address',   //地址
        'email',     //邮箱
        'phone',     //手机号码
        'wechat',    //微信号
        'recommend_name', //推荐人姓名
        'recommend_phone', //推荐人手机号码
        'coachinfo', //教练信息
        'status',  //0待审核 1审核通过  2  审核失败
        'remark', // 备注 比如 审核失败原因
        'create_time', //  创建时间
        'check_time'  // 审核时间  
    );

    public function collection() {
        return SwimAdmin::db('newcoach');
    }
    /**
     * 添加教练
     *
     * @param $data  array
     *
     * @return array
     */
    public function insert($data=array()) {
        $collection = $this->collection();
        $coachdata = Helper::allowed($data, $this->allowed);
        $coachdata['create_time'] = time();
        $coachdata['status']=0;
        $coachdata['coachinfo']=Helper::allowed($data, $this->coachinfo);
        $result = $collection->insert($coachdata);
        if($result){
            return  true;
        }else{
            return false;
        }
    }
    
    /**
    * 更新用户信息 ...
    * @param unknowtype
    * @return return_type
    * @author rabbit ling
    * @date 2018年4月3日下午9:11:47
    */
    public function update($data,$id){
        $collection =$this->collection();
        $id = new MongoId($id);
        $data = Helper::allowed($data, $this->allowed);
        $result = $collection->update(array('_id' => $id), array('$set'=>$data));
        return $result;
    }
    
    
    
    /**
     * 分页查询
     * @param  $pn      integer 页码
     * @param  $filters array   过滤条件
     * @param  $sort    array   排序
     * @return array
     */
    public function pagination($sort,$data=array(),$url = '',$pnValue = null) {
        $params   = Helper::parseQueryString($url? $url: $_SERVER['REQUEST_URI']);
        $pn       = Helper::popValue($params, 'pn', 1);
        $sort     = Helper::popValue($params, 'sort', $sort);
        $type     = Helper::popValue($params, 'type', '');
        $order    = Helper::popValue($params, 'order', -1);
        $category = Helper::popValue($params, 'category');
        $filters  = array();
        if($data) {
            $filters = $data;
        }
        return SwimAdmin::pagination(
            $url,
            $this->collection(),
            is_null($pnValue)? $pn: $pnValue,
            $filters,
            array('priority' => -1, $sort => intval($order) > 0? 1: -1)
            );
    }    
    
    
    /**
    * 查询一条数据 ...
    * @param array
    * @return array
    * @author rabbit ling
    * @date 2018年4月3日下午2:51:46
    */
    
    public function findOne($data=array()){
       if(isset($data['id'])){
           $data['_id']=new MongoId($data['id']);
           unset($data['id']);
       } 
       $result= $this->collection()->findOne($data);
       return $result; 
        
    }
    
 
}
