<?php

namespace App\Providers;

use App\Models\Category;
use App\Support\ExtensionMimeTypeGuesser;
use Illuminate\Filesystem\LocalFilesystemAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter as LocalAdapter;
use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use League\Flysystem\ReadOnly\ReadOnlyFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use Symfony\Component\Mime\MimeTypes;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 當 fileinfo 擴展不可用時，註冊基於副檔名的 MIME 猜測器，避免上傳封面圖等報錯
        if (!extension_loaded('fileinfo')) {
            $mimeTypes = new MimeTypes();
            $mimeTypes->registerGuesser(new ExtensionMimeTypeGuesser());
            MimeTypes::setDefault($mimeTypes);
        }

        // 當 fileinfo 擴展不可用時，使用 ExtensionMimeTypeDetector 避免 Class "finfo" not found 錯誤
        if (!extension_loaded('fileinfo')) {
            Storage::extend('local', function ($app, $config) {
                $visibility = PortableVisibilityConverter::fromArray(
                    $config['permissions'] ?? [],
                    $config['directory_visibility'] ?? $config['visibility'] ?? Visibility::PRIVATE
                );
                $links = ($config['links'] ?? null) === 'skip'
                    ? LocalAdapter::SKIP_LINKS
                    : LocalAdapter::DISALLOW_LINKS;
                $mimeDetector = new ExtensionMimeTypeDetector();
                $adapter = new LocalAdapter(
                    $config['root'],
                    $visibility,
                    $config['lock'] ?? LOCK_EX,
                    $links,
                    $mimeDetector
                );
                if ($config['read-only'] ?? false) {
                    $adapter = new ReadOnlyFilesystemAdapter($adapter);
                }
                if (!empty($config['prefix'])) {
                    $adapter = new PathPrefixedAdapter($adapter, $config['prefix']);
                }
                $flysystem = new Filesystem($adapter, Arr::only($config, [
                    'directory_visibility', 'disable_asserts', 'retain_visibility',
                    'temporary_url', 'url', 'visibility',
                ]));
                $name = $config['name'] ?? 'local';
                return (new LocalFilesystemAdapter($flysystem, $adapter, $config))
                    ->diskName($name)
                    ->shouldServeSignedUrls($config['serve'] ?? false, fn () => $app['url']);
            });
        }

        View::composer('front.layouts.master', function ($view) {
            $view->with('navCategories', Category::where('status', 1)
                ->whereNull('parent_id')
                ->whereNotNull('slug')
                ->orderBy('sort')
                ->with(['children' => fn ($q) => $q->where('status', 1)->whereNotNull('slug')->orderBy('sort')])
                ->get());
            if (request()->routeIs('front.categories.show') && $cat = request()->route('category')) {
                $view->with('currentCategory', $cat);
            }
        });
    }
}
