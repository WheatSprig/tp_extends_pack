_____________
版权说明
本扩展基础类库为本人多年编写整理 
使用请保留备注
未经许可请勿传播 转载
tp_redis Redis类库
tp_worker 队列类库
tp_wechat 微信类库
tp_wxpay  微信支付类库
---
$wxpay = \mikkle\tp_wxpay\Wxpay::instance($options);
$result =$wxpay ->unifiedOrder()->setParam($resultData)->getPrepayId();
 $jsApiSetParams = $wxpay ->jsApi()->getJsPayParamsByPrepayId($result);
---