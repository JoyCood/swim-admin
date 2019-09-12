<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelGoodsMain
{
    private $errMsg;
    private $allowed = array(
        'title',
        'price',
        'original_price',
        'discount',
        'express',
        'description',
        'photos',
        'create_time',
        'statu',
        'up_time',
        'down_time',
        'rates',
        'conver',
        'cover',
        'shop_url',
        'shop',
        'order',
        'contents',
        'belong',
        'created_on',
        'created_by',
        'modified_on',
        'modified_by',
        'likes_counter',
        'dislike_counter'
    );

    private $cats = array(
        'swimming'  => '游泳装备',
        'smart'     => '智能硬件'
    );
    private $belong = array(
        'home' => '首页',
        'tech' => '教程'
    );

    /**
     * 返回collection
     *
     * @return object
     */
    public function collection() {
        return SwimAdmin::db('goods');
    }

    public function getBelong() {
        return $this->belong;
    }

    public function getCats() {
        return $this->cats;
    }


    /**
     * 添加装备
     *
     * @param $data  array
     *
     * @return false | string
     */
    public function insert($data) {
        $collection = $this->collection();
        $data = Helper::allowed($data, $this->allowed);
        $data['cover'] = $data['conver'];
        $data['created_on'] = time();
        $data['create_time'] = time();
        $data['created_by'] = $_SESSION[S_USER];
        $result = $collection->insert($data);
        return $result;
    }

    /**
     * 修改装备
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
        $data['cover'] = $data['conver'];
        $data['modified_on'] = time();
        $data['modified_by'] = $_SESSION[S_USER];
        $result = $collection->update(array('_id' => $id), array('$set' => $data));
        return $result;
    }

    /**
     * 根据1个或多个id删除装备
     *
     * @param $ids  string | array
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
     * 查询装备
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
     * 根据id查找装备
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

            $item = $this->collection->findOne($matcher);

            if($item)
            {
                $item['id'] = $item['_id'];

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
        $sort    = Helper::popValue($params, 'sort', 'up_time');
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
