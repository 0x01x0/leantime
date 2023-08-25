<?php

namespace leantime\core;

use leantime\core\template;
use leantime\core\events;
use leantime\core\language;

/**
 * Controller Class - Base class for all controllers
 *
 * @package    leantime
 * @subpackage core
 */
abstract class controller
{
    use eventhelpers;

    /**
     * @var template
     */
    protected template $tpl;

    /**
     * @var language
     */
    protected language $language;

    /**
     * constructor - initialize private variables
     *
     * @access public
     *
     * @param IncomingRequest $incomingRequest The request to be initialized.
     * @param template        $tpl             The template to be initialized.
     * @param language        $language        The language to be initialized.
     * @return self
     */
    public function __construct(
        IncomingRequest $incomingRequest,
        template $tpl,
        language $language
    ) {
        self::dispatch_event('begin');

        $this->incomingRequest = $incomingRequest;
        $this->tpl = $tpl;
        $this->language = $language;

        // initialize
        $this->executeActions(
            $incomingRequest->getMethod(),
            $incomingRequest->getRequestParams()
        );

        self::dispatch_event('end', $this);
    }

    /**
     * Allows hooking into all controllers with events
     *
     * @access private
     *
     * @param string       $method
     * @param array|object $params
     *
     * @return void
     */
    private function executeActions($method, $params): void
    {
        $available_params = [
            'controller' => $this,
            'method' => $method,
            'params' => $params,
        ];

        self::dispatch_event('before_init', $available_params);
        if (method_exists($this, 'init')) {
            app()->call([$this, 'init']);
        }

        self::dispatch_event('before_action', $available_params);
        if (method_exists($this, $method)) {
            /**
             * @todo non GET requests should only be accessible from HTMX and API requests
             * if ($method !== 'get') && ! $incomingRequest instanceof HtmxRequest|ApiRequest) {
             *    self::redirect(BASE_URL . "/errors/error400", 400);
             * }
             */

            $this->$method($params);
        } else {
            $this->run();
        }
    }
}
