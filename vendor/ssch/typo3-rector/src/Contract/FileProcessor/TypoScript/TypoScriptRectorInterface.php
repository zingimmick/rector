<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript;

use RectorPrefix20210719\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use Rector\Core\Contract\Rector\RectorInterface;
interface TypoScriptRectorInterface extends \RectorPrefix20210719\Helmich\TypoScriptParser\Parser\Traverser\Visitor, \Rector\Core\Contract\Rector\RectorInterface
{
}
