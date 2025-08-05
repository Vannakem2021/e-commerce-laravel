<?php

namespace App\Exceptions;

use Exception;

class CartException extends Exception
{
    /**
     * Error code for the exception
     */
    protected string $errorCode;

    /**
     * Additional error data
     */
    protected array $errorData;

    public function __construct(
        string $message = 'Cart operation failed',
        string $errorCode = 'CART_ERROR',
        array $errorData = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->errorData = $errorData;
    }

    /**
     * Get the error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get the error data
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * Create a cart limit exceeded exception
     */
    public static function limitExceeded(string $message, array $data = []): self
    {
        return new self($message, 'CART_LIMIT_EXCEEDED', $data);
    }

    /**
     * Create a stock insufficient exception
     */
    public static function stockInsufficient(string $message, array $data = []): self
    {
        return new self($message, 'STOCK_INSUFFICIENT', $data);
    }

    /**
     * Create a product unavailable exception
     */
    public static function productUnavailable(string $message, array $data = []): self
    {
        return new self($message, 'PRODUCT_UNAVAILABLE', $data);
    }

    /**
     * Create an unauthorized access exception
     */
    public static function unauthorized(string $message = 'Unauthorized cart access', array $data = []): self
    {
        return new self($message, 'CART_UNAUTHORIZED', $data);
    }
}
