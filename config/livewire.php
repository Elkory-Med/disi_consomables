<?php

return [
    'class_namespace' => 'App\\Livewire',
    'view_path' => resource_path('views/livewire'),
    'layout' => 'components.layouts.app',
    'temporary_file_upload' => [
        'disk' => 'local',        // Changed to local disk for better security
        'rules' => [
            'required',
            'file',
            'max:2048',           // Reduced to 2MB to match our form validation
            'mimes:jpeg,png,jpg,gif'
        ],
        'directory' => 'livewire-tmp',
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'jpg', 'jpeg'  // Limited to just image formats we support
        ],
        'max_upload_time' => 30,     // Reduced to 30 seconds
    ],
    'manifest_path' => null,
    'lazy_placeholder' => null,
    'render_on_redirect' => false,
    'legacy_model_binding' => false,
    
    /*
    |--------------------------------------------------------------------------
    | Asset URL
    |--------------------------------------------------------------------------
    |
    | This value sets the path to Livewire JavaScript assets, for cases where
    | your app's domain root is not the correct path. By default, Livewire
    | will load its JavaScript assets from the app's "relative root".
    |
    | Examples: "/assets", "myurl.com/app".
    |
    */

    'asset_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Livewire App URL
    |--------------------------------------------------------------------------
    |
    | This value should be used if livewire assets are served from CDN.
    | Livewire will communicate with an app through this url.
    |
    | Examples: "https://my-app.com", "myurl.com/app".
    |
    */

    'app_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Livewire Inject Assets
    |--------------------------------------------------------------------------
    |
    | This value determines whether Livewire will automatically inject its
    | JavaScript assets into the view or not. By default, it's set to
    | true, but you can set it to false if you want to manage the
    | assets yourself.
    |
    */

    'inject_assets' => false,
    
    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#2299dd',
    ],
    'inject_morph_markers' => true,
    'pagination_theme' => 'tailwind',
];
