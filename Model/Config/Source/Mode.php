<?php
/**
 * Copyright © 2022 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace PixieMedia\RegistrationCC\Model\Config\Source;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [['value' => 'cc', 'label' => __('cc')],['value' => 'bcc', 'label' => __('bcc')]];
    }

    public function toArray()
    {
        return ['cc' => __('cc'),'bcc' => __('bcc')];
    }
}
