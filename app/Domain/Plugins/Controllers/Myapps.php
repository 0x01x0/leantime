<?php

namespace Leantime\Domain\Plugins\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller;
    use Leantime\Domain\Plugins\Services\Plugins as PluginService;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Auth\Models\Roles;

    /**
     *
     */
    class Myapps extends Controller
    {
        private PluginService $pluginService;

        /**
         * @param PluginService $pluginService
         * @return void
         */
        public function init(PluginService $pluginService): void
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin]);
            $this->pluginService = $pluginService;
        }

        /**
         * @return void
         * @throws BindingResolutionException
         */
        public function get(): void
        {
            foreach (['install', 'enable', 'disable', 'remove'] as $varName) {
                if (empty($_GET[$varName])) {
                    continue;
                }

                try {
                    $notification = $this->pluginService->{"{$varName}Plugin"}($_GET[$varName])
                        ? ["notification.plugin_{$varName}_success", "success"]
                        : ["notification.plugin_{$varName}_error", "error"];

                    $this->tpl->setNotification(...$notification);
                    $this->tpl->redirect(BASE_URL . "/plugins/myapps");
                } catch (\Exception $e) {
                    $this->tpl->setNotification($e->getMessage(), "error");
                    $this->tpl->redirect(BASE_URL . "/plugins/myapps");
                }
            }

            $newPlugins = $this->pluginService->discoverNewPlugins();
            $installedPlugins = $this->pluginService->getAllPlugins();

            $this->tpl->assign("newPlugins", $newPlugins);
            $this->tpl->assign("installedPlugins", $installedPlugins);
            $this->tpl->display("plugins.myapps");
        }

        /**
         * @param $params
         * @return void
         */
        public function post($params): void
        {
            $this->tpl->redirect(BASE_URL . "/plugins/myapps");
        }
    }
}
