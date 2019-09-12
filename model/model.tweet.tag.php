<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelTweetTag
{
    const MAX_PHOTO_NUM = 8;
    const ES_MAP = 'tweet';

    private $allowed = array(
        'name',
        'group',
        'description',
        'priority',
        'invite',
        'cat_id',
        'cat_name',
		'statu',
		'date',
        'create_time'
    );

    /**
     *
     * @return object
     */
    public function collection() {
        return SwimAdmin::db('tweet_tag');
    }

    /**
     * @param $data  array
     *
     * @return false | string
     */
    public function insert($data) {
        $collection = $this->collection();
		$data['create_time'] = time();
        $data = Helper::allowed($data, $this->allowed);
        $result = $collection->insert($data);

        return $data;
    }

    /**
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
		
        return $result;
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
		try
		{
			$id = new MongoId($id);
			$matcher = array('_id' => $id);

			return $this->collection()->findOne($matcher);
		}
		catch(MongoException $e) 
		{
		    return FALSE;    
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
        $sort    = Helper::popValue($params, 'sort', 'priority');
        $order   = Helper::popValue($params, 'order', -1);
        $filters = array();

        $name = Helper::popValue($params, 'name');
        if($name!=='') {
            $filters['name'] = new MongoRegex('/'.$name.'/');
        }

        $data = SwimAdmin::pagination(
            $url,
            $this->collection(),
            is_null($pnValue)? $pn: $pnValue,
            $filters,
            array($sort => intval($order) > 0? 1: -1, 'create_time' => -1)
        );

        $tags   = array();
        foreach($data['items'] as $item) {
            $item       = (array)$item;
            $item['id'] = (string)$item['_id'];
            $tags[]   = $item;
        }
        $data['items'] = $tags;

        return $data;
    }
}
