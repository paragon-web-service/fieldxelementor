<?php
/*
 * This file is part of the PHPASN1 library.
 *
 * Copyright © Friedrich Große <friedrich.grosse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace iThemesSecurity\Strauss\FG\ASN1\Universal;

use iThemesSecurity\Strauss\FG\ASN1\Construct;
use iThemesSecurity\Strauss\FG\ASN1\Parsable;
use iThemesSecurity\Strauss\FG\ASN1\Identifier;

class Sequence extends Construct implements Parsable
{
    public function getType()
    {
        return Identifier::SEQUENCE;
    }
}
