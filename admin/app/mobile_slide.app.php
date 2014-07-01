<?php
class Mobile_slideApp extends BackendApp{
	var $_mobileslide_mod;
	
	function __construct()
	{
		$this->Mobile_slideApp();
	}
	
	function Mobile_slideApp()
	{
		parent::__construct();
		$this->_mobileslide_mod =& m('mobileslide');
	}
	
	function index(){
		$page = $this->_get_page();
		$slides = $this->_mobileslide_mod->find(array(
				'fields'=> 'this.*',
				'limit' => $page['limit'],
				'count' => true,
				'order' => "sort_order ASC"
		));
		$this->assign('slides', $slides);
		
		$page['item_count'] = $this->_mobileslide_mod->getCount();
		$this->import_resource(array('script' => 'inline_edit.js'));
		$this->_format_page($page);
		$this->assign('page_info', $page);
		$this->display('mobile_slide.index.html');
	}
	
	function add(){
		if (!IS_POST){
            $slide = array(
                'sort_order' => 255,
            );

            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->assign('slide', $slide);
			$this->display('mobile_slide.form.html');
		}else{
			$data = array();
			$data['sort_order']     = $_POST['sort_order'];
			$data['url'] = $_POST['url'];
			/* 处理上传的图片 */
			$image     =   $this->_upload_image();
			if ($image === false)
			{
				return;
			}
			$data['image'] = $image;
			
			$item_id = $this->_mobileslide_mod->add($data);

			if (!$item_id)
			{
				$this->show_warning($this->_mobileslide_mod->get_error());
			
				return;
			}
			
			$this->show_message('添加幻灯片陈功',
					'back_list',    'index.php?app=mobile_slide',
					'continue_add', 'index.php?app=mobile_slide&amp;act=add'
			);
		}
	}
	
	function edit(){
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if (!IS_POST)
		{
			/* 是否存在 */
			$slide = $this->_mobileslide_mod->get_info($id);
			$this->assign('slide', $slide);
						
			/* 导入jQuery的表单验证插件 */
			$this->import_resource(array(
					'script' => 'jquery.plugins/jquery.validate.js,mlselection.js'
			));
			$this->display('mobile_slide.form.html');
		}
		else
		{
			
			$slide = $this->_mobileslide_mod->get_info($id);
			
		
			$data = array(
					'sort_order'   => $_POST['sort_order'],
			);
			$data['url'] = $_POST['url'];
			/* 处理上传的图片 */
			$image     =   $this->_upload_image();
			if ($image)
			{
				$data['image'] = $image;
			}
			
				
			$this->_mobileslide_mod->edit($id,$data);
			
			$ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
			$this->show_message('编辑成功',
					'back_list',    'index.php?app=mobile_slide&page=' . $ret_page,
					'再次编辑',   'index.php?app=mobile_slide&amp;act=edit&amp;id=' . $id
			);
		}
	}
	
	//异步修改数据
	function ajax_col()
	{
		$id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
		$column = empty($_GET['column']) ? '' : trim($_GET['column']);
		$value  = isset($_GET['value']) ? trim($_GET['value']) : '';
		$data   = array();
		if (in_array($column ,array('sort_order')))
		{
			$data[$column] = $value;
			$this->_mobileslide_mod->edit($id, $data);
			if(!$this->_mobileslide_mod->has_error())
			{
				echo ecm_json_encode(true);
			}
		}
		else
		{
			return ;
		}
		return ;
	}
	
	function drop()
	{
		$id = isset($_GET['id']) ? trim($_GET['id']) : '';
		if (!$id)
		{
			$this->show_warning('没有数据');
			return;
		}
	
		$ids = explode(',', $id);

		if (!$this->_mobileslide_mod->drop($ids))
		{
			$this->show_warning($this->_mobileslide_mod->get_error());
			return;
		}
	
		$this->show_message('删除成功');
	}
	
	function _upload_image()
	{
		$file = $_FILES['image'];
		if ($file['error'] == UPLOAD_ERR_NO_FILE) // 没有文件被上传
		{
			return '';
		}
		import('uploader.lib');             //导入上传类
		$uploader = new Uploader();
		$uploader->allowed_type(IMAGE_FILE_TYPE); //限制文件类型
		$uploader->addFile($_FILES['image']);//上传logo
		$filename  = $uploader->random_filename();
		if (!$uploader->file_info())
		{
			$this->show_warning($uploader->get_error() , 'go_back', 'index.php?app=mobile_slide&amp;act=edit&amp;');
			return false;
		}
		/* 指定保存位置的根目录 */
		$uploader->root_dir(ROOT_PATH);
	
		/* 上传 */
		if ($file_path = $uploader->save('data/files/mall/mobile',$filename))
		{
			return $file_path;
		}
		else
		{
			return false;
		}
	}
}