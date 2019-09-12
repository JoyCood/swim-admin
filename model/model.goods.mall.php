<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelGoodsMall {

	private $allowed = array(
	    'mall',
		'class',
		'member',
		"statu",
		'order'
	);

    public function collection() {
	    return SwimAdmin::db('goods_mall');
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

    public function findOneById()
    {
        try
        {
            $matcher = array('statu'=> "");

            return $this->collection()->findOne($matcher);
        }
        catch(MongoException $e)
        {
            return FALSE;
        }
    }
}
