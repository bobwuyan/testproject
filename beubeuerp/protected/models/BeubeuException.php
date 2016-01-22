<?php
/**
 * 自定义一个异常处理类
 */
class BeubeuException extends Exception
{
	
    const TOLONG    = 100;
    const TOSHORT   = 101;
    const THROW_DEFAULT = 102;
    const FIELD_EXIST=203;//字段不存在
    const FIELD_EMPTY=204;//字段为空
    const FIELD_INT=204;//字段是否为整形
    const METHOD =301; //方法错误
    const FIELD_FORMAT =205; //字段格式错误
    const SQL_INSERT_ERR=500;//数据库插入数据错误
    const SQL_SELECT_ERR=501;//数据库查找数据错误
    const SQL_UPDATE_ERR=502;//数据库修改数据错误
    const SQL_DELETE_ERR=502;//数据库删除数据错误
    const FUN_EXECUTE_ERR=550;//方法执行错误
    
    // 重定义构造器使 message 变为必须被指定的属性
    public function __construct($message, $code = 0) {
        // 自定义的代码

        // 确保所有变量都被正确赋值
        parent::__construct($message, $code);
    }
		/*
    // 自定义字符串输出的样式
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction() {
        echo "A Custom function for this type of exception\n";
		*/

}