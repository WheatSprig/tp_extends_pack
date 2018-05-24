<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/06/29
 * Time: 8:37
 */

namespace mikkle\tp_tools;

class DbDoc
{

    public $database = array(); //数据库配置

    public $tables = array(); //读取的表信息数组

    public $htmlTable = ''; //表格内容

    public $html = '';

    public $exportTables = array(); // 要导出的表



    public static function instance($host, $dbname, $user, $pwd)

    {

        return new self($host, $dbname, $user, $pwd);

    }



    function __construct($host, $dbname, $user, $pwd)

    {

        // 配置数据库


        $this->database['DB_HOST'] = $host;


        $this->database['DB_NAME'] = $dbname;

        $this->database['DB_USER'] = $user;

        $this->database['DB_PWD'] = $pwd;



    }



    public function build()

    {

        //链接MySQL


        $mysqli = mysqli_init();


        $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2); //超时2s

        $mysqli->options(MYSQLI_INIT_COMMAND, "set names utf8mb4;");

        $mysqli->real_connect($this->database['DB_HOST'], $this->database['DB_USER'], $this->database['DB_PWD'], $this->database['DB_NAME']) or die("Mysql connect is error.");



        // 取得所有表名


        $rs = $mysqli->query('show tables');


        $arrTableName = array_column($rs->fetch_all(), $value=0);



        // 取得所有表信息


        foreach ($arrTableName as $name) {





            if (!empty($this->exportTables) && !in_array($name, $this->exportTables)) {

                continue;

            }



            //表注释


            $sql = "select * from information_schema.tables where table_schema = '{$this->database['DB_NAME']}' and table_name = '{$name}' "; //查询表信息


            $rs = $mysqli->query($sql);


            $arrTableInfo = $rs->fetch_assoc();



            //各字段信息


            $sql = "select * from information_schema.columns where table_schema ='{$this->database['DB_NAME']}' and table_name = '{$name}' "; //查询字段信息


            $rs = $mysqli->query($sql);


            $arrColumnInfo = $rs->fetch_all(MYSQLI_ASSOC);



            //索引信息


            $sql = "show index from {$name}";


            $rs = $mysqli->query($sql);

            $arrIndexInfo = $rs->fetch_all(MYSQLI_ASSOC);



            $this->tables[] = array(

                'TABLE' => $arrTableInfo,

                'COLUMN' => $arrColumnInfo,

                'INDEX' => $this->getIndexInfo($arrIndexInfo)

            );

        }



        //组装HTML


        $html = '';


        foreach($this->tables as $v)

        {

            $html .= '<table border="1" cellspacing="0" cellpadding="0" align="center">';

            $html .= '<caption>' . $v['TABLE']['TABLE_NAME'] . ' ' . $v['TABLE']['TABLE_COMMENT'] . '</caption>';

            $html .= '<tbody><tr><th>字段名</th><th>数据类型</th><th>默认值</th><th>允许非空</th><th>索引/自增</th><th>备注(字段数: '. count($v['COLUMN']).')</th></tr>';



            foreach ($v['COLUMN'] AS $f) {

                $html .= '<tr>';

                $html .= '<td class="c1">' . $f['COLUMN_NAME']      . '</td>';

                $html .= '<td class="c2">' . $f['COLUMN_TYPE']      . '</td>';

                $html .= '<td class="c3">' . $f['COLUMN_DEFAULT']   . '</td>';

                $html .= '<td class="c4">' . $f['IS_NULLABLE']      . '</td>';

                $html .= '<td class="c5">' . $f['COLUMN_KEY'].' '.$f['EXTRA']. '</td>';

                $html .= '<td class="c6">' . $f['COLUMN_COMMENT']   . '</td>';

                $html .= '</tr>';

            }

            $html .= '</tbody></table>';



            $html .= '<table style="border-top:hidden"  cellspacing="0" cellpadding="0" align="center">';

            $html .= '<tr><th>索引名</th><th>索引顺序</th></tr>';

            foreach ($v['INDEX'] as $indexName => $indexContent) {

                $html .= '<tr>';

                $html .= '<td class="c7">' . $indexName . '</td>';

                $html .= '<td>' . implode('; ', $indexContent) . '</td>';

                $html .= '</tr>';

            }

            $html .= '</table><br>';

        }

        $this->htmlTable = $html;



        return $this;

    }





    //设置需要导出的表, 参数为单个表


    function setExportTable($tableName)


    {

        $this->exportTables[] = $tableName;

        return $this;

    }





    //设置需要导出的表, 参数为数组


    function setExportTableArray($arrTableName)


    {

        $this->exportTables = $arrTableName;

        return $this;

    }



    //整合单个表的所有索引(将复合索引归纳到一起)


    function getIndexInfo($arrIndexInfo)


    {

        $index = array();

        foreach ($arrIndexInfo as $v) {

            $unique = ($v['Non_unique'] == 0) ? '(unique)' : '';

            $index[$v['Key_name']][] = $v['Seq_in_index'].': '.$v['Column_name'].$unique;

        }



        return $index;

    }



    //输出到浏览器, 表格宽度用百分比


    function outForBrowser()


    {

        $this->build();



        header("Content-type:text/html;charset=utf-8");

        $html = '<html>

              <meta charset="utf-8">

              <title>自动生成数据字典</title>

              <style>

                body,td,th {font-family:"宋体"; font-size:14px;}

                table,h1,p{width:80%;margin:0px auto;}

                table{border-collapse:collapse;border:1px solid #CCC;background:#efefef;}

                table caption{text-align:left; background-color:#fff; line-height:2em; font-size:14px; font-weight:bold; }

                table th{text-align:left; font-weight:bold;height:26px; line-height:26px; font-size:14px; border:1px solid #CCC;padding-left:5px;}

                table td{height:20px; font-size:14px; border:1px solid #CCC;background-color:#fff;padding-left:5px;}

                .c1{ width: 10%;}

                .c2{ width: 10%;}

                .c3{ width: 5%;}

                .c4{ width: 5%;}

                .c5{ width: 10%;}

                .c6{ width: 60%;}

                .c7{ width: 10%;}

              </style>

              <body>';

        $html .= '<h1 style="text-align:center;">'.$this->database['DB_NAME'].'数据字典</h1>';

        $html .= '<p style="text-align:center;margin:20px auto;">生成时间：' . date('Y-m-d H:i:s') . '  总共：' . count($this->tables) . '个数据表</p>';

        $html .= $this->htmlTable;

        $html .= '</body></html>';



        $this->html = $html;

        echo $html;

        // return $this;


    }





    //输出到word文档, 固定宽度为720px


    function outForWord()


    {

        $this->build();



        /* 下载word */

        header("Content-type:text/html;charset=utf-8");

        header( "Content-type:application/vnd.ms-word" );

        header( "Content-Disposition:attachment;filename={$this->database['DB_NAME']}数据字典.doc" );



        $html = '<html>

              <meta charset="utf-8">

              <title>自动生成数据字典</title>

              <style>

                body,td,th {font-family:"宋体"; font-size:14px;}

                table,h1,p{width:720px;margin:0px auto;}

                table{border-collapse:collapse;border:1px solid #CCC;background:#efefef;}

                table caption{text-align:left; background-color:#fff; line-height:2em; font-size:14px; font-weight:bold; }

                table th{text-align:left; font-weight:bold;height:20px; line-height:20px; font-size:11px; border:1px solid #CCC;padding-left:5px;}

                table td{height:20px; font-size:11px; border:1px solid #CCC;background-color:#fff;padding-left:5px;}

                .c1{ width: 100px;}

                .c2{ width: 110px;}

                .c3{ width: 50px;}

                .c4{ width: 55px;}

                .c5{ width: 100px;}

                .c6{ width: 300px;}

                .c7{ width: 200px;}

              </style>

              <body>';

        $html .= '<h1 style="text-align:center;">'.$this->database['DB_NAME'].'数据字典</h1>';

        $html .= '<p style="text-align:center;margin:20px auto;">生成时间：' . date('Y-m-d H:i:s') . '  总共：' . count($this->tables) . '个数据表</p>';

        $html .= $this->htmlTable;

        $html .= '</body></html>';



        $this->html = $html;

        echo $html;

        // return $this;


    }





    function out()

    {

        // echo $this->html;


    }





}
