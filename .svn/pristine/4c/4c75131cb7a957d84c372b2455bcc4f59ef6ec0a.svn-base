<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelReportMain
{
    private $allowed = array(
        'content',
        'create_time',
        'views_counter',
        'statu',
        'user_id',
        'create_time'
    );

    /**
     *
     * @return object
     */
    public function collection() {
        return SwimAdmin::db('report');
    }

    public function updateStatus($ids, $status) {
        $ids        = (array)$ids;
        $collection = $this->collection();
        foreach($ids as $id) {
            $id = new MongoId($id);
            $collection->update(array('_id' => $id), array('$set' => array(
                'statu'       => $status,
                'modified_by' => $_SESSION[S_USER],
                'modified_on' => time()
            )));
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
        return $collection->find($params)->sort(array('total_counter' => 1));
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
        $sort    = Helper::popValue($params, 'sort', 'total_counter');
        $order   = Helper::popValue($params, 'order', 1);
        $filters = array();
        $data = SwimAdmin::pagination(
            $url,
            $this->collection(),
            is_null($pnValue)? $pn: $pnValue,
            $filters,
            array($sort => intval($order) > 0? 1: -1)
        );

        $tweet   = SwimAdmin::db('tweet');
        $comment = SwimAdmin::db('tweet_comment');
        $data['list'] = array();
        foreach($data['items'] as $item) {
            $objId = $item['obj_id'];
            $obj   = null;
            switch($item['type']) {
                case 'tweet':
                    $obj = $tweet;
                    break;
                case 'tweet_comment':
                    $obj = $comment;
                    break;
            }
            if($obj) {
                try {
                    $data['list'][$objId] = $tweet->findOne(array('_id' => new MongoId($objId)));
                } catch(Exception $e) {
                    $data['list'][$objId] = array();
                }
            }
        }
        
        return $data;
    }
}
