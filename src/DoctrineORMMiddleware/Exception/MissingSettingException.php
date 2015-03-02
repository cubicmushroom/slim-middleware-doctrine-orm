<?php
/**
 * Created by PhpStorm.
 * User: toby
 * Date: 23/02/15
 * Time: 13:43
 */

namespace CubicMushroom\Slim\Middleware\DoctrineORMMiddleware\Exception;

class MissingSettingException extends AbstractException
{

    /**
     * @var string
     */
    protected $setting;


    /**
     * Returns the message to use if no message is passed when building the exception
     *
     * @param array $additionalProperties
     *
     * @return string
     */
    protected static function getDefaultMessage(array $additionalProperties)
    {
        return "Slim application setting '{$additionalProperties['setting']}' is missing";
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Getters and Setters
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getSetting()
    {
        return $this->setting;
    }


    /**
     * @param string $setting
     *
     * @return $this
     */
    public function setSetting($setting)
    {
        $this->setting = $setting;

        return $this;
    }
}