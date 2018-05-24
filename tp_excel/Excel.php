<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/06/22
 * Time: 8:43
 */

namespace mikkle\tp_excel;



use think\Db;
use think\Exception;
use think\Loader;
use think\Log;

class Excel
{
    /**
     * TP5 Excel专用类库
     * $excel=new Excel();
     * $table_name="mk_material_list_edit";
     * $field=["id"=>"序号","guid"=>"项目代码","name"=>"项目名称"];
     * $map=["status"=>1];
     * $map2=["status"=>-1];
     * $excel->setExcelName("下载装修项目")
     * ->createSheet("装修项目",$table_name,$field,$map)
     * ->createSheet("已删除装修项目",$table_name,$field,$map2)
     * ->downloadExcel();
     *
     * Power: Mikkle
     * Email：776329498@qq.com
     * @var \PHPExcel
     */

    protected $objPHPExcel;
    public $xlsReader;
    public static $instance;
    protected $sheetNum=0;
    protected $error;
    protected $columnWidth;
    protected $rowHeight=20;
    protected $excelName;
    protected $isLoad=false;
    //如果你的字段列数超过26字母 会报错
    protected $letterArray=["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];

    /**
     * 项目初始化
     * Excel constructor.
     */
    public function __construct()
    {
        Loader::import("com/PHPExcel/PHPExcel");
        $this->objPHPExcel=new \PHPExcel();
        if(!$this->isLoad){
            //新建时删除默认页面
            $this->objPHPExcel->disconnectWorksheets();
        }
    }

    /**
     * 静态初始化方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return static
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }


    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $path
     * @return static
     * @throws Exception
     * @throws \PHPExcel_Reader_Exception
     */
    static public function loadExcel($path="/test.xls"){
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        $path=ROOT_PATH."public_html/".$path;
        $excel = self::$instance;

        try {
            try {
                $xlsReader = \PHPExcel_IOFactory::createReader("Excel2007");
                $xlsReader->setReadDataOnly(true); //
                $xlsReader->setLoadSheetsOnly(true);

                $excel->xlsReader=$xlsReader->load($path);
            } catch (Exception $e) {

                $xlsReader = \PHPExcel_IOFactory::createReader("Excel5");
                $xlsReader->setReadDataOnly(true); //
                $xlsReader->setLoadSheetsOnly(true);

                $excel->xlsReader=$xlsReader->load($path);

            }
        } catch (Exception $e) {
            throw new Exception("读取EXCEL失败");
        }
        return $excel;

    }

    public function getSheetByName($name){
        if (isset($this->xlsReader)){
            return $this->xlsReader->getSheetByName($name);
        }else{
            return false;
        }

    }

    public function getSheetNames(){
        if (isset($this->xlsReader)){
            return $this->xlsReader->getSheetNames();
        }else{
            return false;
        }
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return mixed
     */
    public function getExcelObject(){
        return $this->xlsReader;
    }
    public function getAllSheets(){
        if (isset($this->xlsReader)){
            return $this->xlsReader->getAllSheets();
        }else{
            return false;
        }
    }

    public function getSheetCount(){
        if (isset($this->xlsReader)){
            return $this->xlsReader->getSheetCount();
        }else{
            return false;
        }
    }

    public function getSheetArrayByIndex($index=0){
        if (isset($this->xlsReader)){
            return $this->xlsReader->getSheet($index)->toArray();
        }else{
            return false;
        }
    }

    /**
     * 设置下载的Excel名称
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $name
     * @return $this
     */
    public function setExcelName($name){
        $this->excelName=$name;
        return $this;
    }

    /**
     * 返回EXCEL名称
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    public function getExcelName()
    {
        return $this->excelName ? $this->excelName : "新建的数据表格";
    }

    /**
     * 创建新的Sheet 支持链式操作
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $sheet_title
     * @param string $table   数据库表名称
     * @param array $field    要导出的字段
     * @param array $map      查询参数
     * @return $this
     * @throws Exception
     * @throws \PHPExcel_Exception
     */
    public function createSheet($sheet_title="sheet",$table="",$field=[],$map=[]){

        if (empty($table) ||empty($field)||!is_string($table)||!is_array($field)){
            $this->error="生成Excel的[table]或[field]参数不正确";
            throw new Exception("生成Excel的[table]或[field]参数不正确");
            return $this;
        }
        $sheet_num = $this->getNewSheetNum();
        $objPHPExcel=$this->objPHPExcel;
        $objPHPExcel->createSheet($sheet_num);
        $objPHPExcel->setActiveSheetIndex($sheet_num);
        $objPHPExcel->getActiveSheet()->setTitle($sheet_title);
        $sheet=$objPHPExcel->getActiveSheet();

        //设置默认行高
        $sheet->getDefaultRowDimension()->setRowHeight($this->rowHeight);
        $titleStyleArray = [
            'font' => [
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ]
        ];
        $width = count($field);
        $sheet->mergeCells("A1:{$this->letterArray[ $width -1 ]}1");
        $sheet->setCellValue('A1',$sheet_title);
        $sheet->getStyle('A1')->applyFromArray($titleStyleArray);
        $sheet->getStyle('A1')->getFont()->setSize(18);
        $sheet->getRowDimension('1')->setRowHeight(24);

        $field_title=array_values($field);

        $letter_array = $this->letterArray;
        foreach($field_title as $item=>$value){
            if(isset($this->columnWidth)){
                if(is_array($this->columnWidth) && count($field)==count($this->columnWidth)){
                    $sheet->getColumnDimension($letter_array[$item])->setWidth($this->columnWidth[$item]);
                }elseif(is_integer($this->columnWidth)){
                    $sheet->getColumnDimension($letter_array[$item])->setWidth($this->columnWidth);
                }else{
                    $sheet->getColumnDimension($letter_array[$item])->setAutoSize(true);
                }
            }else{
                $sheet->getColumnDimension($letter_array[$item])->setAutoSize(true);
            }
            //标题加粗
            $sheet->getStyle($letter_array[$item]."2")->getFont()->setBold(true);
            $sheet->setCellValue($letter_array[$item]."2",$value);

        }


        $list=Db::table($table)->field($field)->where($map)->select();
        if ($list){
            foreach($list as $item=>$value ){
                $value=array_values($value);
                foreach($value as $i=>$v)
                    $sheet->setCellValue($letter_array[$i].($item+3),$value[$i]);
            }
        }

        $color='FFFF0000';
        $width = count($field_title)-1;
        $rows = count($list)+2;
        //边框样式
        $styleArray = [
            'borders' => [
                'allborders' => [
                    //  'style' => \PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
                       'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框
             //       'color' => array('argb' => $color),
                ],
            ],
        ];
        $objPHPExcel->getActiveSheet()->getStyle("A1:{$this->letterArray[ $width ]}{$rows}")->applyFromArray($styleArray);
        return $this;
    }


    public function createSheetByArray($sheet_title="sheet",array $list=[],array $title =[],$name = ""){

        if ( !is_array($list)){
            $this->error="生成Excel的数据不存在或者格式不正确";
            throw new Exception("生成Excel的数据不存在或者格式不正确");
        }
        $sheet_num = $this->getNewSheetNum();
        $objPHPExcel=$this->objPHPExcel;
        $objPHPExcel->createSheet($sheet_num);
        $objPHPExcel->setActiveSheetIndex($sheet_num);
        $objPHPExcel->getActiveSheet()->setTitle($sheet_title);

        //设置默认行高
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight($this->rowHeight);

        $sheet=$objPHPExcel->getActiveSheet();
        if ($title) {
            $titleStyleArray = [
                'font' => [
                    'bold' => true
                ],
                'alignment' => [
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                ]
            ];
            $width = count($title);
            $sheet->mergeCells("A1:{$this->letterArray[ $width -1 ]}1");
            $sheet->setCellValue('A1', $name ? $name : $sheet_title);
            $sheet->getStyle('A1')->applyFromArray($titleStyleArray);
            $sheet->getStyle('A1')->getFont()->setSize(18);
            $sheet->getRowDimension('1')->setRowHeight(24);

            $field_title = array_values($title);

            $letter_array = $this->letterArray;
            foreach ($field_title as $item => $value) {
                //标题加粗
                $sheet->getStyle($letter_array[$item] . "2")->getFont()->setBold(true);
                $sheet->setCellValue($letter_array[$item] . "2", $value);
                //$sheet->getColumnDimension($letter_array[$item])->setAutoSize(true);
                $sheet->getColumnDimension($letter_array[$item])->setWidth(3 * mb_strlen($value));
            }
        }
        if ($list){
            foreach($list as $item=>$value ){
                $value=array_values($value);
                foreach($value as $i=>$v)
                    $sheet->setCellValue($letter_array[$i].($item+3),$value[$i]);
            }
        }

        $color='FFFF0000';
        $width = count($field_title)-1;
        $rows = count($list)+2;
        //边框样式
        $styleArray = [
            'borders' => [
                'allborders' => [
                    //  'style' => \PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框
                    //       'color' => array('argb' => $color),
                ],
            ],
        ];
        $objPHPExcel->getActiveSheet()->getStyle("A1:{$this->letterArray[ $width ]}{$rows}")->applyFromArray($styleArray);
        return $this;
    }


    /**
     * 通过model生成Excel 获取器生效
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $sheet_title
     * @param string $model_name
     * @param array $field
     * @param array $map
     * @return $this
     * @throws Exception
     * @throws \PHPExcel_Exception
     */

    public function createSheetByModel($sheet_title="sheet",$model_name="",$field=[],$map=[]){

        if (empty($model_name) ||empty($field)||!is_string($model_name)||!is_array($field)){
            $this->error="生成Excel的[table]或[field]参数不正确";
            throw new Exception("生成Excel的[table]或[field]参数不正确");
            return $this;
        }
        $sheet_num = $this->getNewSheetNum();
        $objPHPExcel=$this->objPHPExcel;
        $objPHPExcel->createSheet($sheet_num);
        $objPHPExcel->setActiveSheetIndex($sheet_num);
        $objPHPExcel->getActiveSheet()->setTitle($sheet_title);

        //设置默认行高
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight($this->rowHeight);

        $sheet=$objPHPExcel->getActiveSheet();

        $titleStyleArray = [
            'font' => [
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ]
        ];
        $width = count($field);
        $sheet->mergeCells("A1:{$this->letterArray[ $width -1 ]}1");
        $sheet->setCellValue('A1',$sheet_title);
        $sheet->getStyle('A1')->applyFromArray($titleStyleArray);
        $sheet->getStyle('A1')->getFont()->setSize(18);
        $sheet->getRowDimension('1')->setRowHeight(24);
        $field_title=array_values($field);
        $letter_array = $this->letterArray;
        foreach($field_title as $item=>$value){
            if(isset($this->columnWidth)){
                if(is_array($this->columnWidth) && count($field)==count($this->columnWidth)){
                    $sheet->getColumnDimension($letter_array[$item])->setWidth($this->columnWidth[$item]);
                }elseif(is_integer($this->columnWidth)){
                    $sheet->getColumnDimension($letter_array[$item])->setWidth($this->columnWidth);
                }else{
                    $sheet->getColumnDimension($letter_array[$item])->setAutoSize(true);
                }
            }else{
                $sheet->getColumnDimension($letter_array[$item])->setAutoSize(true);
            }
            //标题加粗
            $sheet->getStyle($letter_array[$item]."2")->getFont()->setBold(true);
            $sheet->setCellValue($letter_array[$item]."2",$value);

        }


        $field=array_values(array_flip($field));
        $list=Loader::model($model_name)->field($field)->where($map)->select();
        if ($list){
            foreach($list as $item=>$value ){
                $value=array_values($value->toArray());
                foreach($value as $i=>$v)
                    if (is_array($v)) {
                        $sheet->setCellValue($letter_array[$i].($item+2),implode("--",$v));
                    }else{
                        $sheet->setCellValue($letter_array[$i].($item+3),$v);
                    }
            }
        }
        $color='FFFF0000';
        $width = count($field_title)-1;
        $rows = count($list)+2;
        //边框样式
        $styleArray = [
            'borders' => [
                'allborders' => [
                    //  'style' => \PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框
                    //       'color' => array('argb' => $color),
                ],
            ],
        ];
        $objPHPExcel->getActiveSheet()->getStyle("A1:{$this->letterArray[ $width ]}{$rows}")->applyFromArray($styleArray);
        return $this;
    }


    /**
     * 下载当前的EXCEL
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $save_name
     * @throws \PHPExcel_Reader_Exception
     */
    public function downloadExcel($save_name=""){

        ob_start();
        //最后通过浏览器输出
        $save_name=$this->getExcelName();
        $save_name = $save_name ? "$save_name.xls" : "导出信息.xls";
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header("Content-Disposition: attachment;filename=$save_name");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

        ob_end_flush();//输出全部内容到浏览器
        die();

    }
    /**
     * 保存当前的EXCEL
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $save_path
     * @throws \PHPExcel_Reader_Exception
     */
    public function saveExcel($save_path=""){
        $objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $save_name=$this->getExcelName();
        $save_name = $save_name ? "$save_name.xls" : "demo.xls";
        $save_path=$save_path?$save_path:ROOT_PATH.'runtime/excel/'.$save_name;
        if(!is_dir(dirname ($save_path))){
            mkdir(dirname ($save_path),0755,true);
        }
        $objWriter->save($save_path);
        die();
    }

    /**
     * 获取新的Sheet编号
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return int
     */
    protected function getNewSheetNum(){
        $sheet_num=$this->sheetNum;
        $this->sheetNum=$sheet_num+1;
        return $sheet_num;
    }

    /**
     * 设置行宽 未设置时候默认为自动
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $width
     * @return $this
     */
    public function setColumnWidth($width){
        if(is_integer($width)||is_array($width)){
            $this->columnWidth=$width;
        }
        return $this;
    }

    /**
     * 设置默认行高
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $height
     * @return $this
     */
    public function setRowHeight($height){
        if(is_numeric($height)){
            $this->rowHeight=$height;
        }
        return $this;
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        call_user_func_array([$this->objPHPExcel, $method], $args);
    }


}