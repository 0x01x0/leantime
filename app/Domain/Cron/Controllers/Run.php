<?php

namespace Leantime\Domain\Cron\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Core\Environment;
    use Leantime\Core\Events;
    use PHPMailer\PHPMailer\Exception;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Run extends Controller
    {
        private Environment $config;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(Environment $config)
        {
            $this->config = $config;
        }

        /**
         * The Poor Man's Cron Endpoint
         *
         * @return Response
         * @throws Exception
         */
        public function run(): Response
        {
            if (! $this->config->poorMansCron) {
                return new Response();
            }

            Events::add_event_listener('leantime.core.httpkernel.terminate.request_terminated', function () {
                ignore_user_abort(true);

                // Removes script execution limit
                set_time_limit(0);

                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                } else {
                    flush();
                }

                $output = new \Symfony\Component\Console\Output\BufferedOutput();

                if ($this->config->debug) {
                    register_shutdown_function(function () use ($output) {
                        error_log("Command Output: " . $output->fetch());
                        error_log("Cron run finished");
                    });

                    error_log("Cron run started");
                }

                /** @return never **/
                (new \Leantime\Core\ConsoleKernel())->call('schedule:run', [], $output);
            });

            return tap(new Response(), function ($response) {
                $response->headers->set('Content-Length', '0');
                $response->headers->set('Connection', 'close');
            });
        }
    }
}
