<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelTweetGroup
{
	public function collection() {
		return SwimAdmin::db('tweet_group');
	}

    /**
     * 添加泳圈版块
     *
     * @param $data  array
     *
     * @return false | array
     */
	public function insert(array $data)
	{
		$allowKeys  = array('region', 'leader', 'title', 'description', 'cover', 'priority', 'hot', 'member_num', 'tweet_num', 'statu'); 
        $data = Helper::allowed($data, $allowKeys);

        if(sizeof($data)==0)
        {
        	return FALSE;
        }

		if($data['leader'])
		{
			$member = SwimAdmin::model('member.main');
			$leader = $member->findOneById($data['leader']);

			if(!$leader)
			{
				return FALSE;
			}
		}

        $data['title']       = trim($data['title']);
        $data['description'] = trim($data['description']);
        $data['cover']       = trim($data['cover']);
        $data['priority']    = Helper::uint($data['priority']);
        $data['hot']         = Helper::uint($data['hot']);
        $data['member_num']  = Helper::uint($data['member_num']);
        $data['tweet_num']   = Helper::uint($data['tweet_num']);
        $data['statu']       = Helper::uint($data['statu']);
        $data['create_time'] = time();
        $data['update_time'] = time();
        
        return $this->collection()->insert($data);
	}

    /**
     * 更新泳圈版块
     *
     * @param $data  array 
     * @param $id    string
     *
     * @return array | false
     */
	public function update($data, $id)
	{
		$allowKeys = array('region', 'leader', 'title', 'description', 'cover', 'priority', 'hot', 'member_num', 'tweet_num', 'statu');
        $data = Helper::allowed($data, $allowKeys);

        if(sizeof($data)==0)
        {
        	return FALSE;
        }

		if($data['leader'])
		{
			$member = SwimAdmin::model('member.main');
			$leader = $member->findOneById($data['leader']);

			if(!$leader)
			{
				return FALSE;
			}
		}
		
        $data['title']       = trim($data['title']);
        $data['description'] = trim($data['description']);
        $data['cover']       = trim($data['cover']);
        $data['priority']    = Helper::uint($data['priority']);
        $data['hot']         = Helper::uint($data['hot']);
        $data['member_num']  = Helper::uint($data['member_num']);
        $data['tweet_num']   = Helper::uint($data['tweet_num']);
        $data['statu']       = Helper::uint($data['statu']);
        $data['update_time'] = time();

        $matcher = array('_id' => new MongoId($id));
        $update  = array('$set' => $data);

        return $this->collection()->update($matcher, $update);
	}
    
    /**
     * 根据id查找对应版块记录
     *
     * @param $id  string
     *
     * @return array|false
     */
    public function findOneById($id)
    {
        try
        {
            $matcher = array('_id'=>new MongoId($id));

            return $this->collection()->findOne($matcher);
        }
        catch(MongoException $e)
        {
            return FALSE;
        }
    }

	public function findOne($filter) 
	{
	    return $this->collection()->findOne($filter);
	}

    public function findAll() 
    {
        $groups = array();
        //$query  = array('statu' => 1);
        $cursor = $this->collection()->find();
        $cursor->sort(array('priority' => -1, 'create_time' => -1));
        foreach($cursor as $row) {
            $row['_id'] = (string)$row['_id'];
            $groups[]   = $row;
        }
        return $groups;
    }

    public function getGroups() {
        $groups = array();
        $cursor = $this->collection()->find();
        foreach($cursor as $row) {
            $id          = (string)$row['_id'];
            $groups[$id] = $row['title'];
        }
        return $groups;
    }

    /**
     * 删除圈子
     *
     * @param $id  string | array
     *
     * @return bool
     */
    public function deleteById($ids) {
        $ids = (array)$ids;
        for($idx=0, $l=sizeof($ids); $idx<$l; $idx++) {
            $ids[$idx] = new MongoId($ids[$idx]);
        }
        $collection = $this->collection();
        $result = $collection->remove(array(
            '_id' => array('$in' => $ids)
        ));
        return $result;
    }

    public function deleteRecommand($ids) {
        $ids = (array)$ids;
        for($idx=0, $l=sizeof($ids); $idx<$l; $idx++) {
            $ids[$idx] = new MongoId($ids[$idx]);
        }
        $collection = $this->collection();
        $result = $collection->remove(array(
            '_id' => array('$in' => $ids)
        ));
        return $result;
    }

    /**
     * 分页查询
     * @param  $pn      integer 页码
     * @param  $filters array   过滤条件
     * @param  $sort    array   排序
     * @return array
     */
    public function pagination($url = '', $pnValue = null) {
        $params  = Helper::parseQueryString($url? $url: $_SERVER['REQUEST_URI']);
        $pn      = Helper::popValue($params, 'pn', 1);
        $sort    = Helper::popValue($params, 'sort', 'priority');
        $order   = Helper::popValue($params, 'order', -1);
        $filters = array();
        return SwimAdmin::pagination(
            $url,
            $this->collection(),
            is_null($pnValue)? $pn: $pnValue,
            $filters,
            array($sort => intval($order) > 0? 1: -1, 'create_time' => -1)
        );
    }

}
