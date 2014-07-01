<?php

/**
 * 公告栏挂件
 *
 * @return  array
 */
class Tcz_serverWidget extends BaseWidget
{
    var $_name = 'tcz_server';
    var $_ttl  = 86400;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
		$article_mod=&m('article');
        if($data ==false)
        {
			$amount = array();
			$article=array();
			for($i=0;$i<=5;$i++)
			{
				$amount[$i] = $this->options['amount_'.$i]? intval($this->options['amount_'.$i]) : 6;
				$article[$i]['article'] = $article_mod->find(array(
				'conditions'=>'cate_id='.intval($this->options['a_cate_id_'.$i]).' AND if_show = 1',
				'limit' =>$amount[$i],
				));
				
				$article[$i]['module_name']=$this->options['module_name_'.$i];
			}
			$data=array(
				'article'=>$article,
				'tel'    =>$this->options['tel'],
				'tel2'    =>$this->options['tel2'],
				'tel3'    =>$this->options['tel3'],
				'email'    =>$this->options['email'],
			);
            $cache_server->set($key, $data, $this->_ttl);
        }
		//print_r($data);
        return $data;
    }
	function get_config_datasrc()
    {
        $a_mod = &m('acategory');
        $this->assign('acategories', $a_mod->find());
    }

    function parse_config($input)
    {
       return $input;
    }
    
}
?>
