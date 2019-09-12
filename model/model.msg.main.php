<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelMsgMain
{
	const  ACTION_SEND_MSG        = 1; //发送小纸条
	const  ACTION_REPLY_MSG       = 2; //回复小纸条
	const  ACTION_TWEET_COMMENT   = 3; //评论泳圈动态
	const  ACTION_TWEET_LIKE      = 4; //赞泳圈动态
	const  ACTION_COMMENT_REPLY   = 5; //评论被回复
	const  ACTION_NEW_FANS        = 6; //被关注
	const  ACTION_SYSTEM_MSG      = 7; //系统消息
	const  ACTION_TECHNICAL_COMMENT_REPLY = 8; //回复教学评论
	const  ACTION_INTERVIEW_COMMENT_REPLY = 9; //回复趣看点评论
	const  ACTION_NEWS_COMMENT_REPLY = 10; //回复鲜资讯评论
	const  ACTION_MATCH_COMMENT_REPLY = 11; //回复同城活动评论
	const  ACTION_ASK_PEOPLE = 12; //邀请回答
    
	//对应模块要显示的提醒数
	const BELONG_SWIMER  = 1;
	const BELONG_COMMENT = 2;
	const BELONG_HOME    = 3;

	//系统消息类型
	const SYS_MSG_TYPE_NORMAL  = 0;
	const SYS_MSG_TYPE_WELCOME = 1; //欢迎信息
    const SYS_MSG_TYPE_STAR    = 2; //获得达人认证

	private $belong_allowed = array(
	    self::BELONG_SWIMER,
		self::BELONG_COMMENT,
		self::BELONG_HOME
	);

    private $handler = array(
		self::ACTION_SEND_MSG      => 'note.main',
		self::ACTION_COMMENT_REPLY => 'tweet.comment',
		self::ACTION_REPLY_MSG     => 'note.main',
		self::ACTION_TWEET_COMMENT => 'tweet.comment',
		self::ACTION_NEW_FANS      => 'friend.main',
		self::ACTION_SYSTEM_MSG    => 'broadcast.main',
		self::ACTION_TWEET_LIKE    => 'tweet.like',
    );

    public function collection() {
        return SwimAdmin::db('message');
    }

	/**
	 * 发消息
	 *
	 * @param $sender    string
	 * @param $receptor  string
	 * @param $objId     string
	 * @param $action    int
	 * @param $belong    int
	 *
	 * @return bool
	 */
    public function send($sender, $receptor, $objId, $action, $belong)
	{
		$action_allowed = array_keys($this->handler);

        if(!in_array($action, $action_allowed))
        {
            return FALSE;
        }

		if(!in_array($belong, $this->belong_allowed))
		{
		    return FALSE;
		}
        
        $doc = array(
            'acceptor'    => $receptor,
			'sender'      => $sender,
			'object_id'   => $objId,
			'action'      => $action,
			'belong'      => $belong,
			'has_read'    => 0,
			'statu'       => 0,
			'create_time' => time()
        );        
        
		$res = $this->collection()->insert($doc);

		if(isset($res['ok']) AND $res['ok']>0)
		{
		    $this->freshMsgCounter($receptor, $belong);

			return TRUE;
	    }	

		return FALSE;
	}
    
    /**
	 * 更新消息数量
	 *
	 * @param $receptor string
	 * @param $belong   int 
	 * @param $step     int
	 *
	 * @return bool
	 */
	public function freshMsgCounter($receptor, $belong, $step=1)
	{
		if(!in_array($belong, $this->belong_allowed))
		{
		    return FALSE;
		}

		$num = $step > 0 ? 0 : 1;
	    	
		$matcher = array(
		    'acceptor' => $receptor,
			'belong'   => $belong,
			'total'    => array('$gte'=>$num)
		);
		$update = array(
			'$set'=> array(
			    'acceptor' => $receptor,
				'belong'   => $belong
			),
			'$inc' => array(
				'total' => $step
			)
		);
		$option = array('upsert'=> TRUE);
	    return SwimAdmin::db('msg_counter')->update($matcher, $update, $option);
	}


}