<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelTweetComment
{
    private static $instance;
     
    /**
     * @return object
     */
    public function collection() {
        return SwimAdmin::db('tweet_comment');
    }

    /**
     * 发评论
     *
     * @param $userId  string  评论者id
     * @param $tweetId string  动态id
     * @param $content mix     评论内容
     *
     * @return array
     */
    public function post($userId, $tweetId, $content, $time = null)
    {
     	$time = $time? $time: time();
        $doc  = array(
                    'user_id'       => $userId,
                    'tweet_id'      => $tweetId, 
					'content'       => $content,
					'likes_counter' => 0,
                    'statu'         => 0,
                    'create_time'   => $time
                );

        $result = $this->collection()->insert($doc);

        if($result['ok'] != 1)
        {
            return FALSE;
        }
        $doc['id'] = (string)$doc['_id'];
        unset($doc['_id']);
        
        //更新动态被评论数量
        SwimAdmin::model('tweet.main')->freshCommentCounter($tweetId);
        
        return $doc;
    }

    public function delete($filter, $options=array()) {
        return $this->collection()->remove($filter, $options);
    }
     
    /**
     * 获取动态评论列表
     *
     * @param $tweetId  string  动态id
     * @param $offset   int     分页时间戳
     * @param $limit    int     每页显示数 
     *
     * @return array
     */
    public function getComments($tweetId, $offset=0, $limit=10)
    {
     	$offset = (int)$offset;

        $limit = Helper::uint($limit);
        $limit = $limit ? min(20, $limit) : 20;

     	$offset = $offset ? array('create_time' => array('$lt' => $offset)) : array();

        $macher = array('tweet_id' => $tweetId);

        $macher = array_merge($macher, $offset);
         
        $sort = array('create_time' => -1);

        $cursor = $this->collection()->find($macher)->sort($sort); //->limit($limit);
         
        $comments = array();

        foreach($cursor as $item)
        {
         	$item['id'] = (string)$item['_id'];
         	unset($item['_id']);

            $userId = $item['user_id'];
            $member = SwimAdmin::model('member.main')->findOneById($userId);
             
            if(!$member)
            {
                continue;
            }

            $field  = array('id', 'nick', 'sex', 'avatar_small');
            $member = Helper::allowed($member, $field);      
             
            $item['user'] = $member;
            $comments[]   = $item;
        }

        return $comments;
    }

    public function getCommentCount($tweetId) {
        $macher = array('tweet_id' => $tweetId);
        return $this->collection()->find($macher)->count();
    }

    /**
     * 获取用户评论列表
     *
     * @param $userId  string  用户id
     *
     * @return array
     */
    public function getRepliesComment($userId) {
        $macher = array('user_id' => $userId);
        $sort   = array('create_time'=>-1);
        $cursor = $this->collection()->find($macher)->sort($sort);
        $comments = array();
        foreach($cursor as $item)
            {
                $item['id'] = (string)$item['_id'];
                $tweetId = $item['tweet_id'];
                $Replies = SwimAdmin::model('tweet.main')->findOneById($tweetId);
                if($Replies)
                            {
                               $field  = array('content');
                               $Replies = Helper::allowed($Replies, $field);

                               $item['Replies'] = $Replies;
                            }
                $comments[]   = $item;

            }
            return $comments;
        }
    public function getRepliesCount($userId) {
            $macher = array('user_id' => $userId);
            return $this->collection()->find($macher)->count();
        }
    public function findOne($filter) {
        return $this->collection()->findOne($filter);
    }

    public function find($filter) {
        return $this->collection()->find($filter);
    }

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

    public function detail($commentId)
    {
        try
        {
            $id = new MongoId($commentId);

            $macher = array(
                '_id' => $id
            );

            $comment = $this->collection()->findOne($macher);

            if($comment)
            {
                $member = SwimAdmin::model('member.main')->findOneById($comment['user_id']);
                
                if(!$member)
                {
                    return FALSE;
                }

                $tweet = SwimAdmin::model('tweet.main')->detail($comment['tweet_id']);
				if(!$tweet)
				{
				    return FALSE;
				}

                $allowed = array('id', 'nick', 'sex', 'avatar_small', 'avatar_mid', 'avatar_big', 'sns');

                $comment['user']  = Helper::allowed($member, $allowed);
                $comment['id']    = (string)$comment['_id'];
                $comment['tweet'] = $tweet;
                
                unset($comment['_id']);

                return $comment; 
            }
        }
        catch(MongoException $e)
        {

        }

        return FALSE;
    }


	public function freshMemberCommentsCounter($userId, $step=1)
	{
		try{
			$num = $step > 0 ? 0 : 1;
			$filter = array(
				'_id' => new MongoId($userId), 
				'comments_counter'=>array('$gte'=>$num)
			);

			$update = array('$inc' => array('comments_counter' => $step));
			$res = SwimAdmin::model('member.main')->update($filter, $update);

			return $res['nModified'] > 0;

		}catch(MongoException $e) {
		    return FALSE;
		}
	}
 }