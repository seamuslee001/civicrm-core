<?php
namespace Civi\Token;

use Civi\Token\Event\TokenRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TokenCompatSubscriber
 * @package Civi\Token
 *
 * This class handles the smarty processing of tokens.
 */
class TidySubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      'civi.token.render' => ['tidyHtml', 1000],
    ];
  }

  /**
   * Cleanup html issues.
   *
   * Currently we only clean up double https as can be generated by ckeditor
   * in conjunction with a url token - eg https://{action.url} results in
   * https:://https:://example.com.
   *
   * @param \Civi\Token\Event\TokenRenderEvent $e
   *
   * @noinspection HttpUrlsUsage
   * @noinspection PhpUnused
   */
  public function tidyHtml(TokenRenderEvent $e): void {
    if (strpos($e->string, 'http') !== FALSE) {
      $e->string = str_replace(
        [
          'https://https://',
          'http://https://',
          'http://http://',
          'https://http://',
        ],
        ['https://', 'https://', 'http://', 'http://'],
        $e->string
      );
    }
  }

}