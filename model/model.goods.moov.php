<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelGoodsMoov {

    public function collection() {
	    return SwimAdmin::db('goods_moov_order');
	}

	public function insert($data) {
	    return $this->collection()->insert($data);
	}

	public function findOne($filters) {
	    return $this->collection()->findOne($filters);
	}

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

	public function pagination($url='', $pnValue=null) {
        $params = Helper::parseQueryString($url? $url: $_SERVER['REQUEST_URI']);	
		$pn = Helper::popValue($params, 'pn', 1);
		$filters = array();
		$sort = array('create_time' => -1);

		return SwimAdmin::pagination(
		    $url,
			$this->collection(),
			is_null($pnValue)? $pn: $pnValue,
			$filters,
			$sort
		);
	}
}
