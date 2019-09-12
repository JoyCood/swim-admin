<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelMemberMain {
    const MEMBER_REAL    = 0; // 会员
    const MEMBER_TEACHER = 1; // 教练
    const MEMBER_FAKE    = 2; // 马甲

    private $errMsg;
    private $allowed = array(
        'nick',
        'sex',
        'age',
        'height',
        'weight',
        'birthday',
        'bmr',
        'pwd',
        'phone',
        'mail',
        'sig',
        'title',
        'hometown',
        'location',
        'avatar_small',
        'avatar_mid',
        'avatar_big',
        'level',
        'type',
        'vip',
        'reg_time',
        'last_login',
        'statu',
        'sns',
        'secret',
        'device_id',
        'tweets_counter'    => 0, //泳圈数量
        'fans_counter'      => 0, //粉丝数量
        'following_counter' => 0, //关注数
		'likes_counter'     => 0, //收到的赞数量
		'comments_counter'  => 0, //发的评论数量
		'idents',
		'inviteable',
        'dumb'
    );

    public function collection() {
        return SwimAdmin::db('member');
    }

    /**
     * 添加会员
     *
     * @param $data  array
     *
     * @return false | string
     */
    public function insert($data) {
        $collection                = $this->collection();
        $recommand                 = Helper::popValue($data, 'recommand');
        $data['reg_time']          = time();
        $data                      = Helper::allowed($data, $this->allowed);
        $data['tweets_counter']    = 0;
        $data['fans_counter']      = 0;
        $data['following_counter'] = 0;
		$data['likes_counter']     = 0;
		$data['comments_counter']  = 0;
        $result                    = $collection->insert($data);
        if($result && $recommand) {
            $this->addRecommand((string)$data['_id']);
        }

        return $result;
    }
    public function getMembers() {
        $members = array();
        $cursor = $this->collection()->find();
        foreach($cursor as $row) {
            $id          = (string)$row['_id'];
            $members[$id] = isset($row['nick'])? $row['nick']:'';
        }
        return $members;
    }
    /**
     * 修改会员
     *
     * @param $data  array
     * @param $id    string
     *
     * @return bool
     */
    public function update($data, $id) {
        $collection = $this->collection();
        $id         = new MongoId($id);
        $recommand  = Helper::popValue($data, 'recommand');
        $data       = Helper::allowed($data, $this->allowed);
        $options    = array('upsert' => true);
        $result     = $collection->update(array('_id' => $id), array('$set' => $data), $options);
        if($result) {
            if($recommand) {
                $this->addRecommand((string)$id);
            } else {
                $this->removeRecommand((string)$id);
            }
        }

        return $result;
    }

    public function update2($filter, $data) {
        return $this->collection()->update($filter, $data);
    }

    public function findAndModify($filter, $update) {
        return $this->collection()->findAndModify($filter, $update);
    }

    public function removeRecommand($id) {
        $recoCollection = SwimAdmin::db('member_recommand');

        return $recoCollection->remove(array('user_id' => $id));
    }

    public function addRecommand($id) {
        $member = $this->collection()->findOne(array('_id' => new MongoId($id)));
        $rs     = true;
        if($member && $member['type'] != self::MEMBER_TEACHER) { //  教练不能推荐达人
            $recoCollection = SwimAdmin::db('member_recommand');
            $recommand      = $recoCollection->findOne(array('user_id' => $id));
            if($recommand) {
                $rs = $recoCollection->update(array('user_id' => $id), array(
                    '$set' => array(
                        'sex'          => $member['sex'],
                        'type'         => $member['type'],
                        'sig'          => $member['sig'],
                        'avatar_small' => $member['avatar_small'],
                        'avatar_mid'   => $member['avatar_mid'],
                        'avatar_big'   => $member['avatar_big']
                    )
                ));
            } else {
                $rs = $recoCollection->insert(array(
                    'user_id'      => $id,
                    'nick'         => $member['nick'],
                    'sex'          => $member['sex'],
                    'type'         => $member['type'],
                    'sig'          => $member['sig'],
                    'avatar_small' => $member['avatar_small'],
                    'avatar_mid'   => $member['avatar_mid'],
                    'avatar_big'   => $member['avatar_big'],
                    'create_time'  => time(),
                ));
            }
        }

        return $rs;
    }

    /**
     * 更新会员状态
     *
     * @param $items  array  会员id
     * @param $status int 状态 0或1
     *
     * @return bool
     */
    public function updateStatus($items, $status) {
        $collection = $this->collection();
        $result     = true;
        foreach($items as $id) {
            $id = new MongoId($id);
            $collection->update(array('_id' => $id), array('$set' => array('block' => $status))// array('multi' => true)
            );
        }

        return $result;
    }

    /**
     * 删除会员
     *
     * @param $ids
     * @return bool
     */
    public function deleteById($ids) {
        $ids = (array)$ids;
        for($idx = 0, $l = sizeof($ids); $idx < $l; $idx++) {
            $ids[$idx] = new MongoId($ids[$idx]);
        }
        if(empty($ids)) {
            return false;
        }
        $collection = $this->collection();
        $result     = $collection->remove(array(
            '_id' => array('$in' => $ids)
        ));

        return $result;
    }

    /**
     * 查询会员
     *
     * @param $params  array
     *
     * @return array
     */
    public function find($params = array()) {
        $collection = $this->collection();

        return $collection->find($params)->sort(array('statu' => 1, 'reg_time' => -1));
    }

    /**
     * 查询设备号
     *
     * @param  $userId string
     *
     * @return false|array
     */
    public function getDeviceToken($userId) {
        $matcher = array('user_id' => $userId);
        $sort    = array('create_time' => -1);
        $cursor  = SwimAdmin::db('device_tokens')->find($matcher)->sort($sort)->limit(1);

        foreach($cursor as $item) //返回最后登录的设备号
        {
            return $item;
        }

        return FALSE;
    }

    /**
     * 分页查询
     * @param string $url
     * @param null   $pnValue
     * @return array
     */
    public function pagination($url = '', $pnValue = null) {
        $params  = Helper::parseQueryString($url? $url: $_SERVER['REQUEST_URI']);  //解析url
        $pn      = Helper::popValue($params, 'pn', 1);
        $order   = Helper::popValue($params, 'order', -1);
        $filters = array();

        $type  = Helper::getValue($params, 'type', '');
        $statu = Helper::getValue($params, 'statu', '');
        $query = Helper::getValue($params, 'query', '');
        $title = Helper::getValue($params, 'title', '');
        $phone = Helper::getValue($params, 'phone', '');
        if($type === '') {
            $filters['type'] = array('$not' => array('$eq' => self::MEMBER_TEACHER));
        } else {
            //if($type == 2) $type = -1; // 不过滤出教练数据
            $filters['type'] = intval($type);
        }
        if($statu === '0') {
            $filters['block'] = array('$not' => array('$eq' => 1));
        } else if($statu) {
            $filters['block'] = 1;
        }
        if($query!=='') {
            $filters['nick'] = new MongoRegex('/'.$query.'/');
        }
        if($title) {
            $filters['title'] = new MongoRegex('/'.$title.'/');
        }
        if($phone) {
            $filters['phone'] = new MongoRegex('/'.$phone.'/'); 
        }
        $data = SwimAdmin::pagination($url, $this->collection(), is_null($pnValue)? $pn: $pnValue, $filters, array(
            'block'    => 1,
            'type'     => self::MEMBER_TEACHER,
            'reg_time' => -1
        ));

        $ids     = array();
        $members = array();
        foreach($data['items'] as $item) {
            $item        = (array)$item;
            $item['_id'] = (string)$item['_id'];
            $ids[]       = $item['_id'];
            $members[]   = $item;
        }
        $recoCollection = SwimAdmin::db('member_recommand');
        $recommands     = array();
        $result         = $recoCollection->find(array('user_id' => array('$in' => $ids)));
        foreach($result as $item) {
            $recommands[$item['user_id']] = $item;
        }
        foreach($members as & $item) {
            $item['recommand'] = isset($recommands[$item['_id']])? $recommands[$item['_id']]: array();
        }
        $data['items'] = $members;

        $data['params'] = $params;

        return $data;
    }

    /**
     * 通过id获取会员资料
     *
     * @param $id string
     * @return array
     *
     */
    public function findOneById($id) {
        $id         = new MongoId($id);
        $collection = $this->collection();
        $member     = $collection->findOne(array('_id' => $id));
        if($member) {
            $recoCollection      = SwimAdmin::db('member_recommand');
            $recommand           = $recoCollection->findOne(array('nick' => $member['nick']));
            $member['recommand'] = $recommand? $recommand: array();
        }

        return $member;
    }

	public function findOne($filters, $projection=array()) {
	    return $this->collection()->findOne($filters, $projection);
	}
}