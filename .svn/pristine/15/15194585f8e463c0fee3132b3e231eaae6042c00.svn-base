<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelFavoritesMain {
    public function collection() {
        return SwimAdmin::db('favorites');
    }    

    public function delete($filter, $options=array()) {
        return $this->collection()->remove($filter, $options);
    }
}
