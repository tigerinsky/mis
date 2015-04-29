<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
* 通用的树型类，可以生成任何树型结构
*/
class Tree {
	/**
	* 生成树型结构所需要的2维数组
	* @var array
	*/
	public $arr = array();

	/**
	* 生成树型结构所需修饰符号，可以换成图片
	* @var array
	*/
	public $icon = array('│','├','└');
	public $nbsp = "&nbsp;";

	/**
	* @access private
	*/
	public $ret = '';

	/**
	* 构造函数，初始化类
	* @param array 2维数组，例如：
	* array(
	*      1 => array('id'=>'1','parentid'=>0,'name'=>'一级栏目一'),
	*      2 => array('id'=>'2','parentid'=>0,'name'=>'一级栏目二'),
	*      3 => array('id'=>'3','parentid'=>1,'name'=>'二级栏目一'),
	*      4 => array('id'=>'4','parentid'=>1,'name'=>'二级栏目二'),
	*      5 => array('id'=>'5','parentid'=>2,'name'=>'二级栏目三'),
	*      6 => array('id'=>'6','parentid'=>3,'name'=>'三级栏目一'),
	*      7 => array('id'=>'7','parentid'=>3,'name'=>'三级栏目二')
	*      )
	*/
	public function init($arr=array()){
       $this->arr = $arr;
	   $this->ret = '';
	   return is_array($arr);
	}

    /**
	* 得到父级数组
	* @param int
	* @return array
	*/
	public function get_parent($myid){
		$newarr = array();
		if(!isset($this->arr[$myid])) return false;
		$pid = $this->arr[$myid]['parentid'];
		$pid = $this->arr[$pid]['parentid'];
		if(is_array($this->arr)){
			foreach($this->arr as $id => $a){
				if($a['parentid'] == $pid) $newarr[$id] = $a;
			}
		}
		return $newarr;
	}

    /**
	* 得到子级数组
	* @param int
	* @return array
	*/
	public function get_child($myid){
		$a = $newarr = array();
		if(is_array($this->arr)){
			foreach($this->arr as $id => $a){
				if($a['parentid'] == $myid) $newarr[$id] = $a;
			}
		}
		return $newarr ? $newarr : false;
	}

	
    /**
	* 得到当前位置数组
	* @param int
	* @return array
	*/
	public function get_pos($myid,&$newarr){
		$a = array();
		if(!isset($this->arr[$myid])) return false;
        $newarr[] = $this->arr[$myid];
		$pid = $this->arr[$myid]['parentid'];
		if(isset($this->arr[$pid])){
		    $this->get_pos($pid,$newarr);
		}
		if(is_array($newarr)){
			krsort($newarr);
			foreach($newarr as $v){
				$a[$v['id']] = $v;
			}
		}
		return $a;
	}

    /**
	* 得到树型结构
	* @param $myid int 表示获得这个ID下的所有子级
	* @param $str string 生成树型结构的基本代码，例如："<option value=\$id \$selected>\$spacer\$name</option>"
	* @param $sid int  被选中的ID，比如在做树型下拉框的时候需要用到
	* @param $adds string 列表符号前边增加字符
	* @param $str_group string 单作为分组菜单显示，没有实际作用的情况下，显示此内容，和str类似
	* @return string
	*/
	public function get_tree($myid, $str, $sid = 0, $adds = '', $str_group = ''){
		$number=1;
		//获取父ID为$myid的数组
		$child = $this->get_child($myid);
		if(is_array($child)){
		    $total = count($child);
			foreach($child as $id=>$value){
				$j=$k='';
				//根据是否为分组最后一个元素，确定前置符号
				if($number==$total){
					$j .= $this->icon[2];
				}else{
					$j .= $this->icon[1];
					$k = $adds ? $this->icon[0] : '';
				}
				$spacer = $adds ? $adds.$j : '';
				$selected = $id==$sid ? 'selected' : '';//选中标识
				@extract($value);
				$parentid == 0 && $str_group ? eval("\$nstr = \"$str_group\";") : eval("\$nstr = \"$str\";");
				$this->ret .= $nstr;
				$nbsp = $this->nbsp;
				$this->get_tree($id, $str, $sid, $adds.$k.$nbsp,$str_group);
				$number++;
			}
		}
		return $this->ret;
	}
	
	/**
	* 得到树型结构,针对rbac所做调整
	* @param $myid int 表示获得这个ID下的所有子级
	* @param $str string 生成树型结构的基本代码
	* @param $sid int  被选中的ID，比如在做树型下拉框的时候需要用到
	* @param $adds string 列表符号前边增加字符
	* @param $str_group string 单作为分组菜单显示，没有实际作用的情况下，显示此内容，和str类似
	* @return string
	*/
	public function get_trees($myid, $str, $sid = 0, $adds = '', $str_group = ''){
		$number=1;
		$this->level=0;//仅做一层遍历
		//获取父ID为$myid的数组
		$child = $this->get_child($myid);
		if(is_array($child)){
		    $total = count($child);
			foreach($child as $value){
				$this->level=1;//仅做一层遍历
				$j=$k='';
				//根据是否为分组最后一个元素，确定前置符号
				if($number==$total){
					$j .= $this->icon[2];
				}else{
					$j .= $this->icon[1];
					$k = $adds ? $this->icon[0] : '';
				}
				$spacer = $adds ? $adds.$j : '';
				$selected = $value['id']==$sid ? 'selected' : '';//选中标识
				@extract($value);
				$parentid == 0 && $str_group ? eval("\$nstr = \"$str_group\";") : eval("\$nstr = \"$str\";");
				$this->ret .= $nstr;
				$nbsp = $this->nbsp;
				if($value['level']==0){
					$this->get_trees($id, $str, $sid, $adds.$k.$nbsp,$str_group);
				}
				$number++;
			}
		}
		return $this->ret;
	}
	
}
// END Smarty Class