<?php declare(strict_types=1);

namespace StaticServer\Modifier;

use Spacetab\Configuration\ConfigurationAwareInterface;
use Spacetab\Configuration\ConfigurationAwareTrait;
use StaticServer\Modifier\Iterator\Transfer;

final class SecurityTxtModify implements ModifyInterface, ConfigurationAwareInterface
{
    use ConfigurationAwareTrait;

    /**
     * Updates this file, where $changed object may be contains changes
     * from previous Modifier and where $origin object contains first
     * state of original file.
     *
     * Prepares security.txt file from stub.
     *
     * @param Transfer $changed
     * @param Transfer $origin
     *
     * @return Transfer
     */
    public function __invoke(Transfer $changed, Transfer $origin): Transfer
    {
        if ($origin->filename !== 'security.txt') {
            return $changed;
        }

        $changed->content = sprintf(
            trim($changed->content),
            $this->configuration->get('server.securityTxt.contact'),
            $this->configuration->get('server.securityTxt.preferredLang'),
        );

        return $changed;
    }
}
