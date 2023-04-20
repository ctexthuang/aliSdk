<?php

namespace Ctexthuang\AliSdk\Alipay;

class AliAppPay extends BasicAliPay
{
    /**
     * 配置发起支付请求所需要的参数
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        $this->options->offsetSet('method', 'alipay.trade.app.pay');
        $this->method = str_replace('.', '_', $this->options['method']) . '_response';
    }

    /**
     * 发起支付
     * @param array $options
     * @return mixed
     */
    public function apply(array $options): mixed
    {
        $this->options->offsetSet('biz_content', json_encode($this->params->merge($options), JSON_UNESCAPED_UNICODE));
        $this->options->offsetSet('sign', $this->getAlipaySign());

        return http_build_query($this->options->offsetGet());
    }
}