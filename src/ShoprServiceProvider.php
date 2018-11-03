<?php

namespace Happypixels\Shopr;

use Illuminate\Support\ServiceProvider;
use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Repositories\SessionCartRepository;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Observers\OrderObserver;
use Illuminate\Support\Facades\Event;

class ShoprServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/shopr.php' => config_path('shopr.php'),
        ], 'config');

        $this->publishMigration('CreateOrderTables', 'create_order_tables');
        $this->publishMigration('CreateDiscountCouponsTable', 'create_discount_coupons_table');

        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/Views', 'shopr');

        // We manually register the events here rather than automatically registering the observer
        // because we want to be in control of when the events are fired.
        Event::listen('shopr.orders.created', function (Order $order) {
            (new OrderObserver)->created($order);
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Cart::class, SessionCartRepository::class);
    }

    /**
     * Attempts to publish a migration file.
     *
     * @param  string $classname
     * @param  string $filename
     * @return boolean
     */
    private function publishMigration($classname, $filename)
    {
        if (class_exists($classname)) {
            return false;
        }

        $this->publishes([
            __DIR__ . '/../database/migrations/'.$filename.'.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_'.$filename.'.php'),
        ], 'migrations');

        return true;
    }
}
