<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelInterviewCategory
{
    private $errMsg;
    private $allowed = array(
        'name', 
        'priority',
        'status'
    );

    public function collection() {
        return SwimAdmin::db('interview_category');
    }

    /**
     * 添加教练
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
     * 修改教练
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
        $result = $collection->update(array('_id' => $id), $data);
        return $result;
    }

    /**
     * 删除教练
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
     * 查询教练
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
