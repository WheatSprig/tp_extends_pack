<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/06/13
 * Time: 11:10
 */

namespace mikkle\tp_tools;


class Tree
{
    private $dataList;
    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param null $list
     * @param string $pk
     * @param string $pid
     * @param string $child
     * @return array
     */
    static public function toTree($list=null, $pk='id',$pid = 'pid',$child = '_child'){

        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();

            foreach ($list as $key => $data) {
                $_key = is_object($data)?$data->$pk:$data[$pk];
                $refer[$_key] =& $list[$key];
            }

            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = is_object($data)?$data->$pid:$data[$pid];

                $is_exist_pid = false;
                foreach($refer as $k=>$v){
                    if($parentId==$k){
                        $is_exist_pid = true;
                        break;
                    }
                }
                if ($is_exist_pid) {
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                } else {
                    $tree[] =& $list[$key];
                }
            }
        }
        return $tree;
    }

    /**
     * 将格式数组转换为树
     *
     * @param array $list
     * @param integer $level 进行递归时传递用的参数
     */
    private $formatTree; //用于树型数组完成递归格式的全局变量

    private function _toFormatTree($list,$level=0,$title = 'title') {
        foreach($list as $key=>$val){
            $tmp_str=str_repeat("&nbsp;",$level*6);
            $tmp_str.='<i class="fa  fa-mail-reply fa-rotate-180"></i>';

            $val['level'] = $level;
            $val['title_show'] =$level==0?$val[$title]."&nbsp;":$tmp_str.$val[$title]."&nbsp;";
            //  $val['title_show'] = $val['id'].'|'.$level.'级|'.$val['title_show'];
            if(!array_key_exists('_child',$val)){
                array_push($this->formatTree,$val);
            }else{
                $tmp_ary = $val['_child'];
                unset($val['_child']);
                array_push($this->formatTree,$val);
                $this->_toFormatTree($tmp_ary,$level+1,$title); //进行下一层递归
            }
        }
        return;
    }

    public function toFormatTree($list,$title = 'name',$pk='id',$pid = 'pid',$root = 0){

        $list = $this->list_to_tree($list,$pk,$pid,'_child',$root);

        $this->formatTree = array();
        $this->_toFormatTree($list,0,$title);
        return $this->formatTree;
    }

    public function modelToFormatTree($model_param,$title = 'name',$pk='id',$pid = 'pid',$root = 0){
        if (!isset($model_param['name'])) return false;
        if (!isset($model_param['where'])) $model_param['where']='1=1';
        if (!isset($model_param['field'])) $model_param['field']=true;
        if (!isset($model_param['order'])) $model_param['order']='id';
        $tree_date=db($model_param['name'])->where($model_param['where'])->field($model_param['field'])->order($model_param['order'])->select();
        $tree=$this->toFormatTree($tree_date,$title,$pk,$pid,$root);
        if(isset($model_param['other'])) $tree=array_merge([0=>$model_param['other']], $tree);
        return array_column($tree, 'title_show', 'id');

    }

    /**
     * 把返回的数据集转换成Tree
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     * @return array
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    protected function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
    {
        // 创建Tree
        $tree = array();
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if (is_string($parentId)) $root=(string)$root;
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }

        return $tree;
    }


    //获取某个分类的所有子分类
    function getSubs($categorys,$catId=0,$level=1){
        $subs=array();
        foreach($categorys as $item){
            if($item['parentId']==$catId){
                $item['level']=$level;
                $subs[]=$item;
                $subs=array_merge($subs,$this->getSubs($categorys,$item['categoryId'],$level+1));
            }
        }
        return $subs;
    }

}