<?php
/**
 * Created by PhpStorm.
 * User: toby
 * Date: 25/02/15
 * Time: 17:03
 */

namespace CubicMushroom\Slim\Middleware\DoctrineORMMiddleware\Exception;

class InvalidOptionValueException extends AbstractException
{

    /**
     * @var string
     */
    protected $option;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var string
     */
    protected $reason;


    /**
     * Returns the default exception message
     *
     * @param array $additionalProperties
     *
     * @return string
     */
    protected static function getDefaultMessage(array $additionalProperties)
    {
        $message = "Options '{$additionalProperties['option']}' is invalid'";

        if (isset($additionalProperties['reason'])) {
            $message .= ' - ' . $additionalProperties['reason'];
        }

        return $message;
    }



    // -----------------------------------------------------------------------------------------------------------------
    // Getters and Setters
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getOption()
    {
        return $this->option;
    }


    /**
     * @param string $option
     *
     * @return $this
     */
    public function setOption($option)
    {
        $this->option = $option;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }


    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }


    /**
     * @param string $reason
     *
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }
}