<?php

namespace core\actions;

use Yii;

class CaptchaAction extends \yii\captcha\CaptchaAction
{
    public $validDuration = 0;

    public function run()
    {
        $code = $this->generateCode();
        Yii::$app->cache->set(
            $this->generateCacheKey($code),
            $code,
            $this->validDuration
        );
        return $this->generateImageEncodeString($code);
    }

    public function generateImageEncodeString($code)
    {
        return "data:image/png;base64," . base64_encode($this->renderImage($code));
    }

    public function generateCode()
    {
        if ($this->fixedVerifyCode !== null) {
            return $this->fixedVerifyCode;
        }
        return $this->generateVerifyCode();
    }

    public function validate($input, $caseSensitive)
    {
        if (!$caseSensitive) {
            $input = strtolower($input);
        }

        $cacheKey = $this->generateCacheKey($input);
        if (Yii::$app->cache->get($cacheKey) === $input) {
            Yii::$app->cache->delete($cacheKey);
            return true;
        }
        return false;
    }

    private function generateCacheKey($code)
    {
        return base64_encode(
            Yii::$app->request->getRemoteIP()
            . Yii::$app->request->getUserAgent()
            . $this->getUniqueId()
            . $code
        );
    }
}
