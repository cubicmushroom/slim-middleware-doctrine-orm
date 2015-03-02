<?php
/**
 * Created by PhpStorm.
 * User: toby
 * Date: 23/02/15
 * Time: 13:15
 */

namespace CubicMushroom\Slim\Middleware\DoctrineORMMiddleware\Exception;

class InvalidArgumentException extends AbstractException
{

    /**
     * @var string
     */
    protected $reason;


    /**
     * @param array $additionalProperties
     *
     * @return string
     */
    protected static function getDefaultMessage(array $additionalProperties)
    {
        $message = 'Invalid argument provided';

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