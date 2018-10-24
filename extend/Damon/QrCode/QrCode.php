<?php

namespace Damon\QrCode;

use Damon\Support\Gateway;

class QrCode extends Gateway
{
    const QR_TYPE_FIXED_BY_PERSON = 'QR_TYPE_FIXED_BY_PERSON';

    const QR_TYPE_NOLIMIT = 'QR_TYPE_NOLIMIT';

    const QR_TYPE_DYNAMIC = 'QR_TYPE_DYNAMIC';

    protected function gateway()
    {
        return 'youzan.pay.qrcode';
    }

    public function create(array $parameters = [])
    {
        return $this->setMethod('create')->request($parameters);
    }

    public function get(array $parameters = [])
    {
        return $this->setMethod('get')->request($parameters);
    }
}
