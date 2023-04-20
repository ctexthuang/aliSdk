<?php

namespace Ctexthuang\AliSdk\Alipay\Tools;

trait GeneralTools
{
    /**
     * 证书操作
     * @param string $sign
     * @return string
     */
    protected function handleCert(string $sign): string
    {
        return preg_replace(['/\s+/', '/\-{5}.*?\-{5}/'], '', $sign);
    }
}