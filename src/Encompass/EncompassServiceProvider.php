<?php

namespace Encompass;

use Illuminate\Support\ServiceProvider;

class EncompassServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishConfiguration();
    }

    /**
     * Configure config path.
     */
    protected function publishConfiguration()
    {
        $this->mergeConfigFrom($this->getConfigFileStub(), 'encompass');
        $this->publishes([$this->getConfigFileStub() => $this->getConfigFile()], 'config');
    }

    protected function getConfigFile()
    {
        return function_exists('config_path')
            ? config_path('encompass.php')
            : base_path('config/encompass.php');
    }

    /**
     * Get the original config file.
     *
     * @return string
     */
    protected function getConfigFileStub()
    {
        return  __DIR__ . '/../../config/encompass.php';
    }
}
