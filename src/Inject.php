<?php

declare(strict_types=1);

namespace Spacetab\Server;

use Amp\Promise;
use Amp\File;
use Amp\Success;
use DOMDocument;
use DOMElement;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spacetab\Configuration\ConfigurationInterface;
use Spacetab\Server\Exception\InjectException;
use function Amp\call;

final class Inject
{
    /**
     * Injects the __config.js to top of <head> tag.
     * It will be block content rendering, so not recommended.
     */
    public const INJECT_TO_HEAD = 'head';

    /**
     * Injects the __config.js before first <script> tag in DOM document.
     * Better than `head` variant.
     */
    public const INJECT_BEFORE_SCRIPT = 'before_script';

    private ConfigurationInterface $conf;
    private ?LoggerInterface $logger;

    /**
     * Inject constructor.
     *
     * @param \Spacetab\Configuration\ConfigurationInterface $conf
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(ConfigurationInterface $conf, ?LoggerInterface $logger = null)
    {
        $this->conf   = $conf;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @return \Amp\Promise<null>
     */
    public function injectSecurityTxtFile(): Promise
    {
        $path = rtrim($this->conf->get('server.root'), '/') . '/.well-known/security.txt';
        $contact = $this->conf->get('server.securityTxt.contact');
        $lang = $this->conf->get('server.securityTxt.preferredLang');

        $this->logger->info("Inject the security.txt file to `$path`");

        return call(static function () use ($path, $contact, $lang) {
            $dir = pathinfo($path, PATHINFO_DIRNAME);

            if (! yield File\exists($dir)) {
                yield File\createDirectory($dir);
            }

            yield File\write($path, <<<CONTENT
            Contact: {$contact}
            Preferred-Languages: {$lang}

            CONTENT);
        });
    }

    /**
     * @param string $stage
     * @param string $vcsSha1
     * @return \Amp\Promise<null>
     */
    public function injectConfigJsFile(string $stage, string $vcsSha1): Promise
    {
        $path = rtrim($this->conf->get('server.root'), '/') . '/__config.js';
        $format = $this->conf->get('server.modify.consoleLog');
        $message = sprintf($format, $stage, $vcsSha1);

        $config = $this->conf->all();
        unset($config['server']);

        $template = "window.__stage='%s';window.__config=JSON.parse('%s');window.__vcs='%s';console.log('%s','color:#F44336','color:#009688');";
        $content = sprintf($template, $stage, json_encode($config), $vcsSha1, $message);

        $this->logger->info("Inject the __config.js file to `$path`");

        return call(static function() use ($path, $content) {
            yield File\write($path, $content);
        });
    }

    public function injectConfigJsScriptToIndexFile(): Promise
    {
        $path = rtrim($this->conf->get('server.root'), '/') . '/index.html';

        $this->logger->info('Inject script tag to index.html file.');

        return call(function () use ($path) {
            $dom = new DOMDocument('1.0', 'utf-8');
            $dom->loadHTML(yield File\read($path));

            $script = $dom->createElement('script');
            $script->setAttribute('src', '/__config.js');

            $how = $this->conf->get('server.modify.inject');

            if ($how === self::INJECT_TO_HEAD) {
                return $this->toTopOfHead($dom, $script, $path);
            }

            if ($how === self::INJECT_BEFORE_SCRIPT) {
                return $this->beforeFirstScript($dom, $script, $path);
            }

            throw InjectException::invalidOptionName($how);
        });
    }

    /**
     * Updates this file, where $changed object may be contains changes
     * from previous Modifier and where $origin object contains first
     * state of original file.
     * Injects the __config.js to top of <head> tag.
     * If <head> tag not found injecting will be skipped.
     *
     * @param \DOMDocument $dom
     * @param \DOMElement $script
     * @param string $path
     * @return \Amp\Promise
     */
    private function toTopOfHead(DOMDocument $dom, DOMElement $script, string $path): Promise
    {
        $head = $dom->getElementsByTagName('head');

        // ignore injecting if <head> tag not found in the html file.
        if ($head->length < 1) {
            return new Success();
        }

        $first = null;
        foreach ($head->item(0)->childNodes as $node) {
            if ($node instanceof DOMElement) {
                $first = $node;
                break;
            }
        }

        // if <head> contains child tags we will be inserted script before the first tag.
        if ($first !== null) {
            $first->parentNode->insertBefore($script, $first);
        } else {
            // otherwise <head> tag is empty
            $head->item(0)->appendChild($script);
        }

        return call(static fn() => yield File\write($path, $dom->saveHTML()));
    }

    /**
     * Injects the __config.js before first <script> tag in DOM document.
     *
     * @param \DOMDocument $dom
     * @param \DOMElement $script
     * @param string $path
     * @return \Amp\Promise
     */
    private function beforeFirstScript(DOMDocument $dom, DOMElement $script, string $path): Promise
    {
        $this->configPreload($dom);

        $scripts = $dom->getElementsByTagName('script');

        // If can't found any <script> tag, we will skip injecting.
        if ($scripts->length < 1) {
            return new Success();
        }

        $first = $scripts->item(0);
        $first->parentNode->insertBefore($script, $first);

        return call(static fn() => yield File\write($path, $dom->saveHTML()));
    }

    /**
     * Preloading __config.js
     *
     * https://developers.google.com/web/tools/lighthouse/audits/preload
     *
     * @param \DOMDocument $dom
     *
     * @return void
     */
    private function configPreload(DOMDocument $dom): void
    {
        $preload = $dom->createElement('link');
        $preload->setAttribute('rel', 'preload');
        $preload->setAttribute('href', '/__config.js');
        $preload->setAttribute('as', 'script');

        $link = $dom->getElementsByTagName('link');

        if ($link->length > 0) {
            $link->item(0)->parentNode->insertBefore($preload, $link->item(0));
            return;
        }

        $head = $dom->getElementsByTagName('head');

        if ($head->length > 0) {
            $head->item(0)->appendChild($preload);
            return;
        }
    }
}
