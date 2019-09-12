<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelCourseRegion
{
    private $errMsg;
    private $allowed = array(
        'name',
        'priority',
        'pc_cover',
        'status'
    );

    public function collection() {
        return SwimAdmin::db('course_region');
    }

    /**
     * 添加校区
     *
     * @param $data  array
     *
     * @return array
     */
    public function insert($data) {
        $collection = $this->collection();
        $data = Helper::allowed($data, $this->allowed);
        $data['create_time'] = time();
        $result = $collection->insert($data);
        return $result;
    }

    /**
     * 修改校区
     *
     * @param $data  array
     * @param $id  string
     *
     * @return array
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
     * 删除校区
     *
     * @param $ids array
     *
     * @return array
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
     * 查询校区
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
