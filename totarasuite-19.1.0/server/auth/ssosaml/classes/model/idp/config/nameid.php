<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 */

namespace auth_ssosaml\model\idp\config;

/**
 * SAML NameIds
 *
 * Specified in SAML Core spec, see here for list and meanings:
 * https://www.oasis-open.org/committees/download.php/56776/sstc-saml-core-errata-2.0-wd-07.pdf, section 8.3
 */
class nameid {

    public const FORMAT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';

    public const FORMAT_EMAIL_ADDRESS = 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress';

    public const FORMAT_X509_SUBJECT_NAME = 'urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName';

    public const FORMAT_WINDOWS_DOMAIN_QUALIFIED_NAME = 'urn:oasis:names:tc:SAML:1.1:nameid-format:WindowsDomainQualifiedName';

    public const FORMAT_KERBEROS = 'urn:oasis:names:tc:SAML:2.0:nameid-format:kerberos';

    public const FORMAT_ENTITY = 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity';

    public const FORMAT_PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';

    public const FORMAT_TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';

    /**
     * Get a list of all NameId formats.
     *
     * @return string[]
     */
    public static function get_all(): array {
        return [
            static::FORMAT_UNSPECIFIED,
            static::FORMAT_EMAIL_ADDRESS,
            static::FORMAT_X509_SUBJECT_NAME,
            static::FORMAT_WINDOWS_DOMAIN_QUALIFIED_NAME,
            static::FORMAT_KERBEROS,
            static::FORMAT_ENTITY,
            static::FORMAT_PERSISTENT,
            static::FORMAT_TRANSIENT,
        ];
    }
}
