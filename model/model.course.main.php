<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelCourseMain
{
	public $property = array(
        array(
            'name' => '种类',
            'values' => array(
                '1' => '不含教练学员门票',
                '2' => '含教练学员门票',
				'7' => '含教练门票不含学员门票',
            ),
            'tips' => array(
                '1' => '学员需额外购买教练及学员入场门票',
                '2' => '学员不需要额外购买教练及学员入场门票',
                '7' => '学员只需购买自己入场门票'
            )
        ),
        array(
            'name' => '规格',
            'values'  => array(
                '3'   => '1v1',
                '4'   => '1v2',
                '5'   => '1v3',
				'6'   => '团课',
            ),
            'tips' => array(
                '3'   => '一人成班 随时开课',
                '4'   => '两人成班 需学员自行寻找同学组成2人对教班',
                '5'   => '三人成班 需学员自行寻找同学组成3人对教班',
                '6'   => '小班教学 可单独报名 等待成班时间较长',
            )
		),
		array(
		  'name' => '团购',
		  'values' => array(
				'100' => '2人团',
				'101' => '3人团',
				'102' => '5人团',
			),
			'tips' => array(
				'100' => '2人团优惠价格',
				'101' => '3人团优惠价格',
				'102' => '5人团优惠价格',
			),
			'require' => array(
			    '100' => 2,
				'101' => 3,
				'102' => 5,
			),
		)
	);

    private $allowed = array(
        'title',
        'price',
        'price_subject',
        'price_group',
		'sale_count',
        'training_time',
        'training_step',
        'schedule',
        'teacher',
        'teacher_extra',
        'teacher_phone',
        'score',
        'cover',
        'cover_big',
        'cover_small',
        'photos',
        'statu',
        'extra',
        'contents',
        'belong',
		'type',
		'promotion',
        'flag',
        'space',
        'priority',
        'created_on',
        'created_by',
        'modified_on',
        'modified_by',
		'comments_counter',
		'is_tuangou',
		'huodong_desc',
		'discount_desc',
		'discount_price',
		'tuangou_people',
		'tuangou_discount',
		'tuangou_start',
		'tuangou_expire',
		'tuangou_status',
		'tuangou_people_counter'
    );

    /**
     * 返回collection
     *
     * @return object
     */
    public function collection() {
        return SwimAdmin::db('course');
    }

    /**
     * 添加课程
     *
     * @param $data  array
     *
     * @return array
     */
    public function insert($data) {
        $collection = $this->collection();
        $data['created_on'] = time();
        $data['created_by'] = $_SESSION[S_USER];
        $data = Helper::allowed($data, $this->allowed);
        $data['create_time'] = time();
        $data['comments_counter'] = 0;
        $result = $collection->insert($data);
        return $result;
    }

    /**
     * 修改课程
     *
     * @param $data  array
     * @param $id  string
     *
     * @return array
     */
    public function update($data, $id) {
        $collection = $this->collection();
        $id = new MongoId($id);
        $data['modified_on'] = time();
        $data['modified_by'] = $_SESSION[S_USER];
        $data = Helper::allowed($data, $this->allowed);
        $result = $collection->update(array('_id' => $id), array('$set' => $data));
        return $result;
    }

    /**
     * 删除课程
     *
     * @param $id  string | array
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
     * 查询资讯
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
            
            $collection = $this->collection();
            
            $item = $collection->findOne($matcher);
            $item = $this->getPriceGroupVals($item);
            return $item;
        }
        catch(MongoException $e)
        {

        }

        return FALSE;
    }

    public function detail($id) {
        $item = $this->collection()->findOne(array('_id' => new MongoId($id)));
        $item = $this->getPriceGroupVals($item);
        return $item;
    }

    /**
     * 组合价格
     * @param mixed $item 课程数据
     * @return mixed 返回包含 price_group_values 字段的课程数据
     */
    protected function getPriceGroupVals($item) {
        $data = array();
        if(!empty($item['space']) && !empty($item['space']['region'])) {
            foreach($item['space']['region'] as $region) {
                if(!empty($region['id'])) {
                    $data[$region['id']] = array(
                        'type'   => '校区',
                        'name'   => $region['name'],
                        'values' => array()
                    );
                    $values = & $data[$region['id']]['values'];
                    foreach($this->property[0]['values'] as $pid1 => $prop1) {
                        $values[$pid1] = array(
                            'type'   => $this->property[0]['name'],
                            'name'   => $prop1,
                            'values' => array()
                        );
                        foreach($this->property[1]['values'] as $pid2 => $prop2) {
                            $row = empty($item['price_group'][$region['id']][$pid1][$pid2])?
                                    0:
                                    $item['price_group'][$region['id']][$pid1][$pid2];
                            if(!is_array($row)) {
                                $row = array(
                                    'value' => $row,
                                    'stock' => 0
                                );
                            }
                            $values[$pid1]['values'][$pid2] = array(
                                'name'  => $prop2,
                                'value' => empty($row['value'])? 0: $row['value'],
                                'stock' => empty($row['stock'])? 0: $row['stock'],
                            );
                        }
                    }
                }
            }
        }
        $item['price_group_values'] = $data;

        return $item;
    }

    public function freshCommentCounter($courseId, $step=1) {
        try {
            $num = $step > 0? -1 : 0;
            $filter = array(
                '_id' => new MongoId($courseId),
                'comments_counter' => array('$gt' => $num)
            );   
            $update = array(
                '$inc' => array(
                    'comments_counter' => $step    
                )
            );
            return $this->collection()->update($filter, $update);
        } catch(MongoException $e) {
            return FALSE;
        }
    }

    /**
     * 分页查询
     * @param  $pn      integer 页码
     * @param  $filters array   过滤条件
     * @param  $sort    array   排序
     * @return array
     */
    public function pagination($url = '', $pnValue = null) {
        $params   = Helper::parseQueryString($url? $url: $_SERVER['REQUEST_URI']);
        $pn       = Helper::popValue($params, 'pn', 1);
        $sort     = Helper::popValue($params, 'sort', 'create_time');
        $type     = Helper::popValue($params, 'type', '');
        $order    = Helper::popValue($params, 'order', -1);
        $category = Helper::popValue($params, 'category');
		$courseType = Helper::popValue($params, 'courseType');
        $filters  = array();
        if($category) {
            $filters['belong.cate_id'] = $category;
		}
		if($courseType) {
		    $filters['is_tuangou'] = 1;
			$filters['source'] = array('$exists'=>true);
            $sort = array($sort => intval($order) > 0? 1: -1);
		}else{
			$filters['$or'] = array(
				array('is_tuangou' => array('$exists'=>false)),
				array('source' => 0),
				array('source' => array('$exists'=>false))
			);
            $sort = array('priority' => -1, $sort => intval($order) > 0? 1: -1);
		}
        return SwimAdmin::pagination(
            $url,
            $this->collection(),
            is_null($pnValue)? $pn: $pnValue,
            $filters,
			$sort
        );
    }
}
