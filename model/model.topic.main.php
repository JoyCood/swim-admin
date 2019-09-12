<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelTopicMain
{
    private $errMsg;
    private $allowed = array(
        'title',
        'title_type',
        'category',
        'create_at',
        'cover',
        'description',
        'start_at',
        'expire_at',
        'link',
        'belong',
        'statu',
		'order',
        'city'		
    );

    private $cats = array(
        'youzan'         => '友赞商城',
        'news' 			 => '文章',
        'swimgroup' 	 => '泳圈祥情',
		'web' 			 => '普通网页',
        'tag'            => '标签',
		'match'          => '赛事',
	//	'group'          => '泳圈板块',
	//	'topic'          => '话题精选'
    //  'technical' 	 => '教与学',
    //  'cowequip' 		 => '牛装备',
    //  'swimplace' 	 => '场馆',
    //  'swimtrainer' 	 => '教练',
	//  'promotion_news' => '最新内容',
	//  'course'         => '课程',
    );
	
    private $belong = array(
        'home'          => '首页',
        'home-bottom'   => '首页底部栏',
        'tech'          => '教程',
		'news'          => '鲜资讯'
    );

    /**
     * 返回collection
     *
     * @return object
     */
    public function collection() {
        return SwimAdmin::db('topic');
    }

    public function getBelong() {
        return $this->belong;
    }

    public function getCats() {
        return $this->cats;
    }


    /**
     * 添加游泳馆
     *
     * @param $data  array
     *
     * @return false | string
     */
    public function insert($data) {
        $collection = $this->collection();
        $data = Helper::allowed($data, $this->allowed);
        $result = $collection->insert($data);
        return $result;
    }

    /**
     * 修改游泳馆
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

    /**
     * 删除游泳馆
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

    /**
     * 查询游泳馆
     *
     * @param $params  array
     *
     * @return array
     */
    public function find($params = array()) {
        if(isset($params['mod'])) {
            $params['belong'] = array('$in' => array(trim($params['mod'])));
            unset($params['mod']);
        }
        $collection = $this->collection();
        return $collection->find($params)->sort(array('order' => -1));
    }

    public function findOne($filters=array(), $projection=array()) {
        return $this->collection()->findOne($filters, $projection);
    }

    public function findOneById($id) {
        $filters = array('_id' => new MongoId($id));
        return $this->collection()->findOne($filters);
    }
}