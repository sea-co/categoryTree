<?php

namespace Category\Tree;

use Illuminate\Database\Eloquent\Model;

class tree
{

    /**
     * 树ID
     *
     * @var string model字段
     */
    public $id = 'id';

    /**
     * 名称
     * @var string model字段
     */
    public $title = 'title';

    /**
     * 父级ID
     *
     * @var string model字段
     */
    public $parent_id = 'parent_id';


    /**
     * 排序字段
     *
     * @var string model字段
     */
    public $sort = 'sort';

    /**
     * 描述
     * @var string model字段
     */
    public $desc = 'desc';

    /**
     * 是否为导航
     *
     * @var string model字段
     */
    public $is_nav = 'is_nav';


    /**
     * model显示哪些字段
     *
     * @var array
     */
    public $column = ['id', 'parent_id', 'desc', 'is_nav', 'sort'];

    /**
     * @var string tree子级符号
     */
    public $sub_level_mark = '├─ ';

    /**
     * @var string tree子级结尾符号
     */
    public $sub_level_end_mark = '└─ ';

    // ['id', 'desc', 'is_nav',,'sort']
    /**
     * 树显示字段
     *
     * @var string
     */
    public $tree_id = 'id';
    /**
     * 树显示字段
     *
     * @var string
     */
    public $tree_title = 'title';
    /**
     * 树显示字段
     *
     * @var string
     */
    public $tree_desc = 'desc';

    /**
     * 树显示字段
     *
     * @var string
     */
    public $tree_is_nav = 'is_nav';

    /**
     * 树显示字段
     *
     * @var string
     */
    public $tree_sort = 'sort';


    /**
     * 数据数组设置层级.
     * @param $type
     * @param int $parent_id
     * @param int $level
     * @return array .
     */
    public function typeTree($type, $parent_id = 0, $level = 0)
    {
        static $res = array();

        foreach ($type as $v) {

            if ($v[$this->parent_id] == $parent_id) {

                $v['level'] = $level;
                $res[] = $v;

                $this->typeTree($type, $v[$this->id], $level + 1);
            }
        }
        return $res;
    }


    /**
     *
     *
     * @param $lists
     * @return array
     */
    /**
     * 下拉框栏目树排序
     *
     * @param Model $model
     * @param $lists
     * @return array
     */
    public function sortList(Model $model, $lists)
    {
        $data = array();
        $index = 0;

        $parent_id_arr = [];
        foreach ($lists as $item => $vo) {

            if ($vo['level'] != 0) {

                $parent_id_arr[$vo[$this->parent_id]] = $vo[$this->parent_id];
            }
        }

        $parent_query_arr = $model::query()
            ->whereIn($this->parent_id, $parent_id_arr)
            ->get($this->column)
            ->groupBy($this->parent_id)
            ->toArray();


        foreach ($parent_query_arr as $index => $item) {

            $parent = [];

            foreach ($item as $key => $value) {

                $parent[$this->id][] = $value[$this->id];
                $parent[$this->sort][] = $value[$this->id];
            }

            $parent_query_arr[$index] = $parent;

        }

        foreach ($lists as $key => $list) {

            if ($list['level'] != 0) {
                $index++;
                $name = null;
                if (max($parent_query_arr[$list[$this->parent_id]][$this->sort]) != 1) {
                    $keys = array_search(max($parent_query_arr[$list[$this->parent_id]][$this->sort]), $parent_query_arr[$list[$this->parent_id]][$this->sort]);

                    $parent_id = $parent_query_arr[$list[$this->parent_id]][$this->id][$keys];

                } else {
                    $parent_id = max($parent_query_arr[$list[$this->parent_id]][$this->id]);
                }

                for ($i = 0; $i < $list['level']; $i++) {

                    $name .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                }

                if ($parent_id == $list[$this->id]) {

                    $title = $name . $this->sub_level_end_mark . $list[$this->title];
                    $index = 0;

                } else {

                    $title = $name . $this->sub_level_mark . $list[$this->title];
                }


            } else {

                $title = $list[$this->title];
            }

            $data[$list[$this->id]] = $this->store($title, $list[$this->id], $list[$this->desc],
                $list[$this->sort], $list[$this->is_nav]);
        }

        return $data;
    }


    /**
     * 组装树显示数据
     * @param $title
     * @param $id
     * @param string $desc
     * @param string $sort
     * @param string $is_nav
     * @return array
     */
    public function store($title, $id, $desc = '', $sort = '', $is_nav = '')
    {

        $data = [
            $this->tree_title => $title, $this->tree_desc => $desc,
            $this->tree_sort => $sort, $this->tree_id => $id,
            $this->tree_is_nav => $is_nav,
        ];

        if (!$desc) {

            unset($data[$this->tree_desc]);
        }

        if (!$sort) {

            unset($data[$this->tree_sort]);
        }
        if (!$sort) {

            unset($data[$this->tree_is_nav]);
        }


        return $data;
    }


}
