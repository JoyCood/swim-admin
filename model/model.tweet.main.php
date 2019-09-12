<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelTweetMain
{
    const MAX_PHOTO_NUM = 8;
    const ES_MAP = 'tweet';

    private $allowed = array(
        'title',
        'type',
        'content',
        'create_time',
        'views_counter',
        'likes_counter',
        'comments_counter',
        'hot',
        'recommand',
        'top',
        'group_id',
		'group_title',
		'topic',
        'statu',
        'user_id',
		'author',
        'create_time',
		'pushed'
    );

    /**
     *
     * @return object
     */
    public function collection() {
        return SwimAdmin::db('tweet');
    }


    /**
     *
     * @param $data  array
     *
     * @return false | string
     */
    public function insert($data) {
        $collection = $this->collection();
        $data = Helper::allowed($data, $this->allowed);
        $result = $collection->insert($data);

        if($result['ok'] == 1)
        {
            $this->freshTweetCounter($data['user_id']);
        }

        return $data;
    }

    /**
     *
     * @param $data  array
     * @param $id  string
     *
     * @return bool
     */
    public function update($data, $id) {
        $collection = $this->collection();
        $id = new MongoId($id);
        $data = Helper::allowed($data, $this->allowed);
        $result = $collection->update(array('_id' => $id), array('$set' => $data));
        return $result;
    }

    public function delete($filter, $options=array()) {
        return $this->collection()->remove($filter, $options);
    }

    /**
     * @param $id  string | array
     *
     * @return bool
     */
    public function deleteById($ids) {
		$ids = (array)$ids;
		$ids2 = $ids;
        for($idx=0, $l=sizeof($ids); $idx<$l; $idx++) {
            $ids[$idx] = new MongoId($ids[$idx]);
        }
        $collection = $this->collection();
        $result = $collection->remove(array(
            '_id' => array('$in' => $ids)
		));
		
		SwimAdmin::db('tweet_recommand')->remove(array(
			'tweet_id'=>array('$in'=>$ids2)
		));

        return $result;
    }

    public function freshCommentCounter($tweetId, $n = 1) {
	    $num = $n > 0 ? -1 : 0;  
		$collection = $this->collection();
      
        $data = array(
            '$inc' => array(
                'comments_counter' => $n
            )
        );
        return $collection->update(array(
			'_id' => new MongoId($tweetId),
			'comments_counter'=>array('$gt'=>$num)
        ), $data);
    }

    /**
     * 更新发文数量
     * 
     * @param $userId  string
     * @param $n       int
     * @return bool
     */
    public function freshTweetCounter($userId, $n=1) {
        $member = SwimAdmin::model('member.main');

        try
        {
            $num= $n > 0 ? 0 : 1; 
			$matcher = array(
				'_id' => new MongoId($userId), 
				'tweets_counter' => array('$gte' => $num)
			);
            $update = array('$inc' => array('tweets_counter' => $n));

            $res = $member->collection()->update($matcher, $update);

            return $res['nModified']>0;
        }
        catch(MongoException $e)
        {
            return FALSE;
        }
    }

    /**
     *
     * @param $params  array
     *
     * @return array
     */
    public function find($params = array()) {
        $collection = $this->collection();
        return $collection->find($params)->sort(array('create_time' => -1));
    }

    /**
     * 根据id查找
     *
     * @param string $id
     *
     * @return false | array
     */
    public function findOneById($id)
    {
        // try
        // {
            $id = new MongoId($id);
            $matcher = array('_id' => $id);

            $item = $this->collection()->findOne($matcher);

            if($item)
            {
                $item['id'] = (string)$item['_id'];
                unset($item['_id']);
    
                $recoCollection    = SwimAdmin::db('tweet_recommand');
                $recommand         = $recoCollection->findOne(array('tweet_id' => $item['id']));
                $item['recommand'] = $recommand? $recommand: array();

                return $item;
            }
        // }
        // catch(MongoException $e)
        // {

        // }

        return FALSE;
    }    
 /**
     * 根据用户id查找发帖
     *
     * @param string $id
     *
     * @return false | array
     */
    public function getUserTweet($id)
    {
           $matcher = array('user_id' => $id);
           $sort = array('create_time' => -1);
           $cursor = $this->collection()->find($matcher)->sort($sort);
           $posts = array();
           foreach($cursor as $item)
            {
                $item['id'] = (string)$item['_id'];
                $posts[]   = $item;
            }
            return $posts;
    }
    public function getUserCount($id) {
                $macher = array('user_id' => $id);
                return $this->collection()->find($macher)->count();
            }

    public function addTop($id, $doc) {
        $data = $this->findOneById($id);
        $rs   = false;
        if($data) {
            $recoCollection          = SwimAdmin::db('tweet_top');
            $doc['update_time']      = time();
			$doc['likes_counter']    = $data['likes_counter'];
			$doc['comments_counter'] = $data['comments_counter'];
            $data2 = $recoCollection->findOne(array('tweet_id'=> $id));

			if($data2) {
                $rs = $recoCollection->update(array('tweet_id' => $id), array('$set' => $doc));
            } else {
                $doc['tweet_id']    = $id;
                $doc['create_time'] = time();
				
                $rs = $recoCollection->insert($doc);
            }

            $taskId = '8';
            $Task = SwimAdmin::model('task.main');
            $filter = array('taskId'=> $taskId, 'itemId'=> $id);
            $taskData = $Task->findOne($filter);
            if(!$taskData) {
                $reward = Config::$taskReward;
                $taskData = array(
                    'userId'   => $data['user_id'],
                    'taskId'   => $taskId, 
                    'itemId'   => $id,
                    'quantity' => 1,
                    'status'   => 2,
                    'type'     => 2,
                    'times'    => 1,
                    'time'     => strtotime('today midnight')
                ); 
                $Task->insert($taskData);
                $filter = array('_id' => new MongoId($data['user_id']));
                $update = array('$inc'=> array(
                    'exp' => $reward[$taskId]['exp'],
                    'points' => $reward[$taskId]['points'] 
                ));
                SwimAdmin::model('member.main')->findAndModify($filter, $update);
                $pointsData = array(
                    'user_id' => $data['user_id'],
                    'action'  => $reward[$taskId]['action'],
                    'points'  => $reward[$taskId]['points'],
                    'desc'    => $reward[$taskId]['desc']
                );
                SwimAdmin::model('points.main')->insert($pointsData);
            }

        }
        return $rs;
    }

    public function getOneTop($filter, $projection=array()) {
        return SwimAdmin::db('tweet_top')->findOne($filter, $projection);
    }

    public function detail($id) {
        return $this->collection()->findOne(array('_id' => new MongoId($id)));
    }

    public function insertElasticSearch($doc,$id) {
		try{
	        $params = array();
		    $params['index'] = Config::$elastic['index'];
		    $params['type']  = self::ES_MAP;
		    $params['id']    = $id;
		    $params['body']  = $doc;
		    return SwimAdmin::elastic()->index($params);
		}catch(Exception $e){
	        return FALSE;	
		}
	}

	public function updateElasticSearch($data, $id) {
		try{
			$params = array();
			$params['index'] = Config::$elastic['index'];
			$params['type']  = self::ES_MAP;
			$params['id']    = $id;
			$params['body']['doc'] = $data;
			
			return SwimAdmin::elastic()->update($params);
		}catch(Exception $e){
		    return FALSE;
		}
	}

	public function deleteElasticSearch($ids) {
		try{
			$ids = (array)$ids;
			$params = array();
			$params['index'] = Config::$elastic['index'];
			$params['type'] = self::ES_MAP;

			foreach($ids as $id) {
				$params['id'] = (string)$id;
                SwimAdmin::elastic()->delete($params);
			}
		}catch(Exception $e){
		
		}
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
        $sort    = Helper::popValue($params, 'sort', 'create_time');
        $order   = Helper::popValue($params, 'order', -1);
        $type    = Helper::popValue($params, 'tweet-type', '');
        $start   = Helper::popValue($params, 'start', '');
        $end     = Helper::popValue($params, 'end',   '');
        $filters = array();
        $members = array();

		$author = Helper::popValue($params, 'author');
		if($author!='') {
		    $filters['nick'] = new MongoRegex('/'.$author.'/');
		}
        if($filters) {
            $cursor = SwimAdmin::model('member.main')->collection()->find($filters);
            foreach($cursor as $item) {
                $mid = (string)$item['_id'];
                $members[$mid] = $item;
            }
            if($members) {
                $filters['user_id'] = array('$in' => array_keys($members));
            }
            unset($filters['nick']);
        }

        $groupId = Helper::popValue($params, 'group-id');
        if($groupId) {
            $filters['group_id'] = $groupId;
		}

        $topicId = Helper::popValue($params, 'topic-id');
		if($topicId) {
		    $filters['topic.id'] = $topicId;
		}
        $title = Helper::popValue($params, 'title');
        $str = explode(' ', $title);
        $num = count($str);
        $ArrayList = array();
        for($i=0;$i<$num;++$i){
            Array_push($ArrayList, array('content.title' => new MongoRegex('/'.$str[$i].'/'),),array('content.text' => new MongoRegex('/'.$str[$i].'/'),));
        } 
        if($title!=='') {
            $filters['$or'] = $ArrayList;
        }
        if($type == 'top') {
            $filters['top'] = 1;
        } else if($type == 'hot') {
            $filters['hot'] = 1;
        } else if($type == 'recommand') {
            $filters['recommand'] = 1;
		} else if($type == 'pushed') {
		    $filters['pushed'] = 1;
		}

        if($start && $end) {
			$filters['$and'] = array( 
				array('create_time' => array('$gte'=>strtotime($start))),
				array('create_time' => array('$lt'=>strtotime($end)+3600*24))
			);
        }else if($start) {
			$filters['create_time'] = array('$gte'=>strtotime($start));
		}else if($end) {
			$filters['create_time'] = array('$lt'=>strtotime($end)+3600*24);
		}

        $tagId = Helper::popValue($params, 'tag-id');
        if($tagId) {
            $filters['content.tweet_tag.id'] = $tagId;
        }
        $data = SwimAdmin::pagination(
            $url,
            $this->collection(),
            is_null($pnValue)? $pn: $pnValue,
            $filters,
            array($sort => intval($order) > 0? 1: -1)
        );

        $tweetIds = array();
        $tweets   = array();
        foreach($data['items'] as $item) {
            $item       = (array)$item;
            $item['id'] = (string)$item['_id'];
            $tweetIds[] = $item['id'];
            $tweets[]   = $item;
			if(empty($item['user_id'])) {
			    continue;
			}
            if(empty($members)) {
                $mids[] = new MongoId($item['user_id']);
            }
        }
        $recoCollection = SwimAdmin::db('tweet_top');
        $inTops = array();
        $result = $recoCollection->find(array('tweet_id' => array('$in' => $tweetIds)));
        foreach($result as $item) {
            $inTops[$item['tweet_id']] = $item;
        }
        foreach($tweets as & $item) {
            $item['inTop'] = isset($inTops[$item['id']])? $inTops[$item['id']]: array();
        }
        if(!empty($mids)) {
            $filters = array('_id'=>array('$in'=>$mids)); 
            $cursor = SwimAdmin::model('member.main')->collection()->find($filters);
            foreach($cursor as $item) {
                $mid = (string)$item['_id'];
                $members[$mid] = $item;
            }
        }
        $data['items'] = $tweets;
        $data['members'] = $members;

        return $data;
    }
}
