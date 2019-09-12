<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelMallCategory
{
    private $errMsg;
    private $allowed = array(
        'cover',
        'cover_big',
        'cover_small',
        'name',
        'subtitle',
        'city',
        'price',
        'priority',
        'list_type',
        'status',
        'promotion',
        'sort',
        'order',
        'photos'
    );

    public function collection() {
        return SwimAdmin::db('mall_category');
    }

    /**
     * 添加
     *
     * @param $data  array
     *
     * @return false | string
     */
    public function insert($data) {
        $collection = $this->collection();
        $data = Helper::allowed($data, $this->allowed);
        $data['create_time'] = time();
        $result = $collection->insert($data);
        return $result;
    }

    /**
     * 修改
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
        $data['modified_on'] = time();
        $result = $collection->update(array('_id' => $id), $data);
        return $result;
    }

    /**
     * 删除
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
     * 查询
     *
     * @param $params  array
     *
     * @return array
     */
    public function find($params = array()) {
        $collection = $this->collection();
        return $collection->find($params)->sort(array('priority' => -1, 'name' => 1));
    }
}
