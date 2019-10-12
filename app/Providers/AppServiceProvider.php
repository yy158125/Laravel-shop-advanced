<?php

namespace App\Providers;

use App\Http\ViewComposers\CategoryTreeComposer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Monolog\Logger;
use Yansongda\Pay\Pay;
use Elasticsearch\ClientBuilder as ESClientBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \View::composer(['products.index', 'products.show'],CategoryTreeComposer::class);
        Carbon::setLocale('zh');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if(app()->environment('local')){
            DB::listen(function ($query){
                // Log::info(Str::replaceArray('?',$query->bindings,$query->sql));
//                Log::info($query->sql);
            });
        }


        $this->app->singleton('alipay',function (){
            // 判断当前项目运行环境是否为线上环境
            $config = config('pay.alipay');
            // $config['notify_url'] = route('payment.alipay.notify');
            $config['notify_url'] = ngrok_url('payment.alipay.notify');
            $config['return_url'] = route('payment.alipay.return');
            if (app()->environment() !== 'production'){
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            }else{
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个支付宝支付对象
            return Pay::alipay($config);
        });
        $this->app->singleton('wechat_pay', function () {
            $config = config('pay.wechat');
            if (app()->environment() !== 'production') {
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个微信支付对象
            return Pay::wechat($config);
        });

        $this->app->singleton('es',function (){
            $builder = ESClientBuilder::create()->setHosts(config('database.elasticsearch.hosts'));
            if (app()->environment() === 'local'){
                // 配置日志，Elasticsearch 的请求和返回数据将打印到日志文件中，方便我们调试
                $builder->setLogger(app('log')->getMonolog());
            }
            return $builder->build();
        });

    }
}
