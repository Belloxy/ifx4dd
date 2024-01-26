<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\DBAL\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\API\Informix;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\InformixPlatform;
use Doctrine\DBAL\Schema\InformixSchemaManager;
use Doctrine\DBAL\VersionAwarePlatformDriver;

/**
 * Abstract base implementation of the {@link Doctrine\DBAL\Driver} interface
 * for IBM Informix based drivers.
 *
 */
abstract class AbstractInformixDriver implements VersionAwarePlatformDriver
{
    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return new InformixPlatform();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(Connection $conn, AbstractPlatform $platform)
    {
        assert($platform instanceof InformixPlatform);

        return new InformixSchemaManager($conn, $platform);
    }

    public function getExceptionConverter(): ExceptionConverter
    {
        return new Informix\ExceptionConverter();
    }

    /**
     * {@inheritdoc}
     *
     * @see http://www-01.ibm.com/support/knowledgecenter/SSGU8G_11.50.0/com.ibm.sqls.doc/ids_sqs_1491.htm
     */
    public function createDatabasePlatformForVersion($version)
    {
        $regex = '/^(?P<server_type>.*)
            (?i:\s+Version\s+)
            (?P<major>\d+)\.
            (?P<minor>\d+)\.
            (?P<so>F|H|T|U)
            (?P<level>[[:alnum:]]+)/x';

        if ( ! preg_match($regex, $version, $versionParts) ) {
            throw Exception::invalidPlatformVersionSpecified(
                $version,
                '<server_type> Version <major>.<minor><os><level>'
            );
        }

        // Right now only exists one platform for all versions
        return $this->getDatabasePlatform();
    }
}
