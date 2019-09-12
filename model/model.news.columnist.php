<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelNewsColumnist {

	private $allowed = array(
	    'name',
	    'cintro',
	    'cover',
	    'auname',
	    'aintro',
	    'icon',
	    'priority',
		'status',
		'create_time'
	);

    public function collection() {
	    return SwimAdmin::db('columnist');
	}

	/**
	 * 添加认证标识
	 *
	 * @param $data array
	 *
	 * @return false | string
	 */
	public function insert($data) {
	    $collection = $this->collection();
		return $collection->insert($data);
	}

	public function update($data, $id) {
	    $collection = $this->collection();
		$id = new MongoId($id);
        $data = Helper::allowed($data, $this->allowed);

		return $collection->update(array('_id' => $id), $data);
	}

	/**
	 * 删除认证
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
		return $collection->remove(array(
		    '_id' => array('$in' => $ids)
		));
	}

	/**
	 * 查询认证名称
	 *
	 * @param $filter array
	 *
	 * @return array
	 */
	public function find($filter=array()) {
	    $collection = $this->collection();

		return $collection->find($filter)->sort(array('create_time'=>-1));
	}

	/**
	 * 查询一条记录
	 *
	 * @param $filter array
	 * 
	 * @return array | null
	 */
	public function findOne($filter=array()) {
	    $collection = $this->collection();
		return $collection->findOne($filter);
	}

}
