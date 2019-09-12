<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelTechnicalCategory
{
	private $errMsg;
	private $allowed = array(
        'name', 
        'priority',
        'cover',
       /* 'light',
        'nothing',*/
        'banner',
        'type',
        'difficulty',
		'status',
		'tab'
    );

    private $types = array(
        'freestyle'         => '自由泳',
        'breaststroke'      => '蛙泳',
        'backstroke'        => '仰泳',
        'butterflystroke'   => '蝶泳',
        'knowledge'         => '准备课程',
        'other'             => '其他'
    );

	private $tabs = array(
	    array('title'=>'日常训练', 'selected'=>false),
		array('title'=>'基础学习', 'selected'=>false),
		array('title'=>'初级学习', 'selected'=>false)
	);

    public function collection() {
        return SwimAdmin::db('technical_category');
    }

    public function getTypes() {
        return $this->types;
    }

	public function getTabs() {
	    return $this->tabs;
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

            $item = $this->collection()->findOne($matcher);

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
}