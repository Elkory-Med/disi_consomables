<?php


error_reporting(E_ERROR | E_PARSE);

define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/../autoload.php';

class LaravelVsCode
{
    public static function relativePath($path)
    {
        if (!str_contains($path, base_path())) {
            return (string) $path;
        }

        return ltrim(str_replace(base_path(), '', realpath($path)), DIRECTORY_SEPARATOR);
    }

    public static function outputMarker($key)
    {
        return '__VSCODE_LARAVEL_' . $key . '__';
    }

    public static function startupError(\Throwable $e)
    {
        throw new Error(self::outputMarker('STARTUP_ERROR') . ': ' . $e->getMessage());
    }
}

try {
    $app = require_once __DIR__ . '/../../bootstrap/app.php';
} catch (\Throwable $e) {
    LaravelVsCode::startupError($e);
    exit(1);
}

$app->register(new class($app) extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        config([
            'logging.channels.null' => [
                'driver' => 'monolog',
                'handler' => \Monolog\Handler\NullHandler::class,
            ],
            'logging.default' => 'null',
        ]);
    }
});

try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
} catch (\Throwable $e) {
    LaravelVsCode::startupError($e);
    exit(1);
}

echo LaravelVsCode::outputMarker('START_OUTPUT');

$blade = new class {
  public function findFiles($path)
  {
    $paths = [];

    if (!is_dir($path)) {
      return $paths;
    }

    $files = \Symfony\Component\Finder\Finder::create()
      ->files()
      ->name("*.blade.php")
      ->in($path);

    foreach ($files as $file) {
      $paths[] = [
        "path" => str_replace(base_path(DIRECTORY_SEPARATOR), '', $file->getRealPath()),
        "isVendor" => str_contains($file->getRealPath(), base_path("vendor")),
        "key" => \Illuminate\Support\Str::of($file->getRealPath())
          ->replace(realpath($path), "")
          ->replace(".blade.php", "")
          ->ltrim(DIRECTORY_SEPARATOR)
          ->replace(DIRECTORY_SEPARATOR, ".")
      ];
    }

    return $paths;
  }
};

$paths = collect(
  app("view")
    ->getFinder()
    ->getPaths()
)->flatMap(fn($path) => $blade->findFiles($path));

$hints = collect(
  app("view")
    ->getFinder()
    ->getHints()
)->flatMap(
  fn($paths, $key) => collect($paths)->flatMap(
    fn($path) => collect($blade->findFiles($path))->map(
      fn($value) => array_merge($value, ["key" => "{$key}::{$value["key"]}"])
    )
  )
);

[$local, $vendor] = $paths
  ->merge($hints)
  ->values()
  ->partition(fn($v) => !$v["isVendor"]);

echo $local
  ->sortBy("key", SORT_NATURAL)
  ->merge($vendor->sortBy("key", SORT_NATURAL))
  ->toJson();

echo LaravelVsCode::outputMarker('END_OUTPUT');

exit(0);
