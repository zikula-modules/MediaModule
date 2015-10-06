<?php

namespace Cmfcmf\Module\MediaModule\Translation;

trait TranslationTrait
{
    protected $domain;

    /**
     * singular translation for modules.
     *
     * @param string $msg Message.
     *
     * @return string
     */
    public function __($msg)
    {
        return __($msg, $this->domain);
    }

    /**
     * Plural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param integer $n Count.
     *
     * @return string
     */
    public function _n($m1, $m2, $n)
    {
        return _n($m1, $m2, $n, $this->domain);
    }

    /**
     * Format translations for modules.
     *
     * @param string $msg Message.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function __f($msg, $param)
    {
        return __f($msg, $param, $this->domain);
    }

    /**
     * Format pural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param integer $n Count.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function _fn($m1, $m2, $n, $param)
    {
        return _fn($m1, $m2, $n, $param, $this->domain);
    }
}
