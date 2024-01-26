<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Driver\API\Informix;

use Doctrine\DBAL\Driver\API\ExceptionConverter as ExceptionConverterInterface;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Query;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use Doctrine\DBAL\Exception\NonUniqueFieldNameException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\SyntaxErrorException;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class ExceptionConverter implements ExceptionConverterInterface
{
    public function convert(Exception $exception, ?Query $query): DriverException
    {
        switch ($exception->getCode()) {
            case '-239':
            case '-268':
                return new UniqueConstraintViolationException($exception, $query);

            case '-206':
                return new TableNotFoundException($exception, $query);

            case '-310':
                return new TableExistsException($exception, $query);

            case '-691':
            case '-692':
            case '-26018':
                return new ForeignKeyConstraintViolationException($exception, $query);

            case '-391':
                return new NotNullConstraintViolationException($exception, $query);

            case '-217':
                return new InvalidFieldNameException($exception, $query);

            case '-324':
                return new NonUniqueFieldNameException($exception, $query);

            case '-201':
                return new SyntaxErrorException($exception, $query);

            case '-908':
            case '-930':
            case '-951':
                return new ConnectionException($exception, $query);

        }

        // In some cases the exception doesn't have the driver-specific error code

        if ( self::isErrorAccessDeniedMessage($exception->getMessage()) ) {
            return new ConnectionException($exception, $query);
        }

        return new DriverException($exception, $query);
    }

    /**
     * Checks if a message means an "access denied error".
     *
     * @param string
     * @return boolean
     */
    protected static function isErrorAccessDeniedMessage($message)
    {
        if ( strpos($message, 'Incorrect password or user') !== false ||
            strpos($message, 'Cannot connect to database server') !== false ||
            preg_match('/Attempt to connect to database server (.*) failed/', $message) ) {
            return true;
        }

        return false;
    }
}
