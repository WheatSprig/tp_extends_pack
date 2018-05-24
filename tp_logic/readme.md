## 逻辑层基类 需要继承使用
### 开启操作记录

记录表SQL已经提供
---
    protected $isRecord =true;  //是否记录
    protected $recordTable = "mk_log_service_operate" ;
    protected $recordConnect;
---
####  逻辑层使用示例

---
    public function getTokenForInspect($data){
        //注入方法名和请求参数
        $this->functionName = __FUNCTION__;
        $this->args = $data;
        //检验数据
        if ( ! isset($data[OptionsCenter::$fieldChargeToken]) || empty($data[OptionsCenter::$fieldChargeToken] )){
            $this->addError("登录Token参数缺失或已经失效");
            return ShowCode::jsonCodeWithoutData(1022,$this->error);
        }
        $tokenInfo = TokenInfoCenter::instance($data[OptionsCenter::$fieldChargeToken])->getInfoList();
        if (!$tokenInfo || !isset($tokenInfo[OptionsCenter::$idTerminal] )){
            $this->addError("登录Token参数已经失效");
            return ShowCode::jsonCodeWithoutData(1022,$this->error);
        }
        $token = md5( RandNumCenter::getTimeString().$data[OptionsCenter::$fieldChargeToken]);
        //注销设备信息
        unset($tokenInfo[OptionsCenter::$idTerminal]);
        TokenInfoCenter::instance($token)->setInfoArray( $tokenInfo);
        TokenInfoCenter::instance($token)->setExpire(60);

        //写入结果
        $this->result = [OptionsCenter::$fieldChargeToken=>$token];
        return ShowCode::jsonCode(1001,$this->result );
    }
---
`通过mikkle\tp_api\ApiCenter 将外部请求统一引导至 逻辑层处理`
---
    /**
     * @title 收费员登录班次站点
     * @description 终端测试号 861373020508882
     * @author Mikkle
     * @url  /api/app.charge/chargeDispatchLogin
     * @method POST
     * @param_send name:dispatch_id type:int require:1 default:15000002 other:8位数字 desc:班次编号ID
     * @param_send name:charge_id type:int require:1 default:17000002 other:8位数字 desc:收费员ID
     * @param_send name:charge_password type:string require:1 default:1111 other:40字符串 desc:收费员密码
     * @param_send name:terminal_mac type:string require:1 default: other:40字符串 desc:设备终端码
     * @param_return charge_name:收费员姓名
     * @param_return charge_token:收费员登录码
     */
    public function chargeDispatchLogin(){
        try{
            $operate = ApiCent::instance();
            $result = $operate->setParameter([
                OptionsCenter::$idDispatch =>OptionsCenter::$idDispatch,
                OptionsCenter::$idCharge =>OptionsCenter::$idCharge,
                OptionsCenter::$fieldChargePassword=>OptionsCenter::$fieldChargePassword,
                OptionsCenter::$fieldTerminalMac => OptionsCenter::$fieldTerminalMac,
            ])->setValidate(false)
                ->setModel("base/app/Charge")
                ->setModelType("logic")
                ->execModelAction("chargeDispatchLogin");
            if ($result===false){
                throw new Exception($operate->getError());
            }
            return $result;
        }catch (Exception $e){
            Log::error($e);
            return ShowCode::jsonCodeWithoutData(1008,$e->getMessage());
        }
    }
---

_____________
版权说明
本扩展基础类库为本人多年编写整理

未经许可请勿传播 转载

相关使用教程 http://www.mikkle.cn


tp_wechat 微信类库
tp_wxpay  微信支付类库
TP5专用微信sdk下载及使用教程
https://www.kancloud.cn/mikkle/thinkphp5_study/447624


