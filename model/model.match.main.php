<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelMatchMain
{
    private $errMsg;
    private $allowed = array(
        'title',
		'author',
		'date',
		'summary',
        'type',
        'promotion',
        'link',
        'icon',
        'cover',
		'photos', 
        'category',
        'contents',
        'res_type',
        'created_on',
        'created_by',
        'modified_on',
        'modified_by',
        'comments_counter',
        'region'
    );

    /**
     * 返回collection
     *
     * @return object
     */
    public function collection() {
        return SwimAdmin::db('match');
    }

    /**
     * 添加赛事活动
     *
     * @param $data  array
     *
     * @return false | string
     */
    public function insert($data) {
        $collection = $this->collection();
        // $data['cover'] = $data['icon'];
        $data['created_on'] = time();
        $data['created_by'] = $_SESSION[S_USER];
        $data = Helper::allowed($data, $this->allowed);
        $data['create_time'] = time();
        $data['comments_counter'] = 0;
        $result = $collection->insert($data);
        return $result;
    }

    /**
     * 修改赛事活动
     *
     * @param $data  array
     * @param $id  string
     *
     * @return bool
     */
    public function update($data, $id) {
        $collection = $this->collection();
        $id = new MongoId($id);
        // $data['cover'] = $data['icon'];
        $data['modified_on'] = time();
        $data['modified_by'] = $_SESSION[S_USER];
        $data = Helper::allowed($data, $this->allowed);
        $result = $collection->update(array('_id' => $id), array('$set' => $data));
        return $result;
    }

    /**
     * 删除赛事活动
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
     * 查询赛事活动
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
            
            $collection = $this->collection();
            
            $item = $collection->findOne($matcher);

            if($item)
            {
                $item['id'] = (string)$item['_id'];

                unset($item['_id']);

                return $item;
            }
        }
        catch(MongoException $e)
        {

        }

        return FALSE;
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
        $sort    = Helper::popValue($params, 'sort', '');
        $type    = Helper::popValue($params, 'type', '');
        $order   = Helper::popValue($params, 'order', -1);
        $filters = array();
        if($type) {
            if($type == 'link') {
                $filters['type'] = 'link';
            } else {
                $filters['type'] = array('$ne' => 'link');
            }
        }
        if($sort) {
            $sort = array($sort => intval($order) > 0? 1: -1);
        } else {
            $sort = array('promotion' => -1, 'create_time' => -1);
        }
        return SwimAdmin::pagination(
            $url,
            $this->collection(),
            is_null($pnValue)? $pn: $pnValue,
            $filters,
            $sort
        );
    }
}
