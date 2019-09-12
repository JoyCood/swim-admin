<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelCourseNatatorium
{
    private $errMsg;
    private $allowed = array(
        'cover',
        'name',
        'photos',
        'coords',
        'city',
        'address',
        'phone',
        'service_time',
        'price',
        'score',
        'priority',
        'status',
        'remark'
    );

    public function collection() {
        return SwimAdmin::db('course_natatorium');
    }

    /**
     * 添加泳馆
     *
     * @param $data  array
     *
     * @return false | string
     */
    public function insert($data) {
        $collection = $this->collection();
        if(isset($data['coordinates'])) {
            $coords = explode(',', $data['coordinates']);
            $data['coords'] = array(
                'type' => 'Point',
                'coordinates' => array(
                    trim($coords[0]) * 1,
                    trim($coords[1]) * 1
                )
            );
        }
        $data = Helper::allowed($data, $this->allowed);
        $data['create_time'] = time();
        $result = $collection->insert($data);
        return $result;
    }

    /**
     * 更新泳馆
     *
     * @param $data  array
     * @param $id  string
     *
     * @return bool
     */
    public function update($data, $id) {
        $collection = $this->collection();
        $id = new MongoId($id);
        if(isset($data['coordinates'])) {
            $coords = explode(',', $data['coordinates']);
            $data['coords'] = array(
                'type' => 'Point',
                'coordinates' => array(
                    trim($coords[0]) * 1,
                    trim($coords[1]) * 1
                )
            );
        }
        $data = Helper::allowed($data, $this->allowed);
        $data['modified_on'] = time();
        $result = $collection->update(array('_id' => $id), $data);
        return $result;
    }

    /**
     * 删除泳馆
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
     * 查询泳馆
     *
     * @param $params  array
     *
     * @return array
     */
    public function find($params = array(), $projection=array()) {
        $collection = $this->collection();
        return $collection->find($params)->sort(array('priority' => -1, 'name' => 1));
    }

    /**
     * 查询泳馆
     *
     */
    public function findOne($params=array(), $projection=array()) {
        return $this->collection()->findOne($params, $projection);
    }
}
