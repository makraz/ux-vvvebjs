# VvvebJs Bundle for Symfony using Symfony UX

Symfony UX Bundle implementing [VvvebJs](https://www.vvveb.com/vvvebjs/) — a drag-and-drop page builder and website editor.

Also working out of the box with EasyAdmin.

If you need a visual page builder (with drag-and-drop components) in a Symfony project, this is what you need.

* [Installation](#installation)
* [Basic Usage](#basic-usage)
* [Component Groups](#component-groups)
* [Plugins](#plugins)
* [Editor Options](#editor-options)
* [EasyAdmin Integration](#easyadmin-integration)
* [File Upload](#file-upload)
* [Data Format](#data-format)
* [CDN & Self-Hosting](#cdn--self-hosting)
* [JavaScript Events](#javascript-events)

## Installation

### Step 1: Require the bundle

```sh
composer require makraz/ux-vvvebjs
```

If you are using the **AssetMapper** component, you're done!

### Step 2: JavaScript dependencies (Webpack Encore only)

If you are using **Webpack Encore** (skip this step if using AssetMapper):

```sh
yarn install --force && yarn watch
```

Or with npm:

```sh
npm install --force && npm run watch
```

That's it. You can now use `VvvebJsType` in your Symfony forms.

## Basic Usage

In a form, use `VvvebJsType`. It works like a classic form type with additional options:

```php
use Makraz\VvvebJsBundle\Form\VvvebJsType;

public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('content', VvvebJsType::class)
    ;
}
```

By default, the editor comes with `Common`, `HTML`, `Elements`, and `Bootstrap 5` component groups enabled, plus the `CodeMirror` plugin for code editing.

## Component Groups

Component groups define which drag-and-drop blocks are available in the editor's sidebar.

### Available Groups

| Enum | Value | Description |
|------|-------|-------------|
| `VvvebJsComponentGroup::COMMON` | `common` | Common components (text, image, video, buttons, etc.) |
| `VvvebJsComponentGroup::HTML` | `html` | HTML elements (headings, paragraphs, lists, tables, etc.) |
| `VvvebJsComponentGroup::ELEMENTS` | `elements` | Advanced UI elements |
| `VvvebJsComponentGroup::BOOTSTRAP5` | `bootstrap5` | Bootstrap 5 components (navbar, cards, modals, carousel, etc.) |
| `VvvebJsComponentGroup::WIDGETS` | `widgets` | Widget components |
| `VvvebJsComponentGroup::EMBEDS` | `embeds` | Embed components (YouTube, maps, etc.) |

### Customizing Components

```php
use Makraz\VvvebJsBundle\Form\VvvebJsType;
use Makraz\VvvebJsBundle\DTO\Enums\VvvebJsComponentGroup;

$builder->add('content', VvvebJsType::class, [
    'vvvebjs_components' => [
        VvvebJsComponentGroup::COMMON,
        VvvebJsComponentGroup::BOOTSTRAP5,
        VvvebJsComponentGroup::EMBEDS,
    ],
]);
```

You can also pass string values directly:

```php
'vvvebjs_components' => ['common', 'bootstrap5', 'embeds'],
```

## Plugins

Plugins extend the editor with additional functionality.

### Available Plugins

| Enum | Value | Description |
|------|-------|-------------|
| `VvvebJsPlugin::CODE_MIRROR` | `codemirror` | Code editor with syntax highlighting for HTML source editing |
| `VvvebJsPlugin::GOOGLE_FONTS` | `google-fonts` | Google Fonts picker and integration |
| `VvvebJsPlugin::JSZIP` | `jszip` | Export page as a ZIP file |
| `VvvebJsPlugin::AOS` | `aos` | Animate On Scroll — add scroll animations to elements |
| `VvvebJsPlugin::AI_ASSISTANT` | `ai-assistant` | AI-powered content generation assistant |
| `VvvebJsPlugin::MEDIA` | `media` | Media manager for browsing and selecting uploaded files |

### Customizing Plugins

```php
use Makraz\VvvebJsBundle\Form\VvvebJsType;
use Makraz\VvvebJsBundle\DTO\Enums\VvvebJsPlugin;

$builder->add('content', VvvebJsType::class, [
    'vvvebjs_plugins' => [
        VvvebJsPlugin::CODE_MIRROR,
        VvvebJsPlugin::GOOGLE_FONTS,
        VvvebJsPlugin::AOS,
        VvvebJsPlugin::JSZIP,
    ],
]);
```

## Editor Options

Use the `vvvebjs_options` parameter to configure global editor behavior:

```php
$builder->add('content', VvvebJsType::class, [
    'vvvebjs_options' => [
        'height' => '800px',
        'border' => true,
        'designerMode' => false,
        'readOnly' => false,
        'uploadUrl' => '/vvvebjs/upload',
    ],
]);
```

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `height` | `int\|string` | `'600px'` | Height of the editor — integer for pixels, string for CSS values (e.g. `'80vh'`) |
| `border` | `bool\|string` | `true` | Show a border around the editor. `true` for default border (`1px solid #dee2e6`), or a CSS border string |
| `designerMode` | `bool` | `false` | Enable designer mode for advanced layout editing |
| `readOnly` | `bool` | `false` | Set the editor to preview/read-only mode |
| `uploadUrl` | `string` | `''` | URL for the media upload endpoint |

## EasyAdmin Integration

The bundle provides a dedicated `VvvebJsAdminField` for seamless EasyAdmin integration:

```php
use Makraz\VvvebJsBundle\Form\VvvebJsAdminField;

public function configureFields(string $pageName): iterable
{
    yield VvvebJsAdminField::new('content');
}
```

To customize components, plugins, and options, use `setFormTypeOptions`:

```php
use Makraz\VvvebJsBundle\DTO\Enums\VvvebJsComponentGroup;
use Makraz\VvvebJsBundle\DTO\Enums\VvvebJsPlugin;

yield VvvebJsAdminField::new('content')
    ->setFormTypeOptions([
        'vvvebjs_components' => [
            VvvebJsComponentGroup::COMMON,
            VvvebJsComponentGroup::BOOTSTRAP5,
        ],
        'vvvebjs_plugins' => [
            VvvebJsPlugin::CODE_MIRROR,
            VvvebJsPlugin::GOOGLE_FONTS,
        ],
        'vvvebjs_options' => [
            'height' => '700px',
            'uploadUrl' => '/vvvebjs/upload',
        ],
    ])
;
```

The field automatically registers the Twig form theme and works with both AssetMapper and Webpack Encore.

## File Upload

The bundle provides a built-in upload controller. Three storage options are available: **local filesystem**, **Flysystem**, or **your own custom handler**.

### Option 1: Local Filesystem (default)

Store uploads in your Symfony `public/` directory:

```yaml
# config/packages/vvvebjs.yaml
vvvebjs:
    upload:
        enabled: true
        handler: local
        local_dir: '%kernel.project_dir%/public/uploads/vvvebjs'
        local_public_path: '/uploads/vvvebjs'
        max_file_size: 10485760  # 10 MB
        allowed_mime_types:
            - image/jpeg
            - image/png
            - image/gif
            - image/webp
            - image/svg+xml
            - video/mp4
            - video/webm
            - application/pdf
```

Then import the bundle routes:

```yaml
# config/routes/vvvebjs.yaml
vvvebjs:
    resource: '@VvvebJsBundle/config/routes.php'
```

And set the upload URL in your form:

```php
$builder->add('content', VvvebJsType::class, [
    'vvvebjs_options' => [
        'uploadUrl' => '/vvvebjs/upload',
    ],
]);
```

### Option 2: Flysystem

Store uploads via [League Flysystem](https://flysystem.thephpleague.com/) (S3, GCS, Azure, SFTP, etc.):

```sh
composer require league/flysystem-bundle
```

```yaml
# config/packages/vvvebjs.yaml
vvvebjs:
    upload:
        enabled: true
        handler: flysystem
        flysystem_storage: 'default.storage'
        flysystem_path: 'uploads/vvvebjs'
        flysystem_public_url: 'https://cdn.example.com'
        max_file_size: 10485760  # 10 MB
```

### Option 3: Custom Handler

Implement your own upload logic by creating a service that implements `UploadHandlerInterface`:

```php
use Makraz\VvvebJsBundle\Upload\UploadHandlerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MyUploadHandler implements UploadHandlerInterface
{
    public function upload(UploadedFile $file): string
    {
        // Your upload logic here
        // Return the public URL of the uploaded file
        return 'https://example.com/path/to/file.jpg';
    }
}
```

```yaml
# config/packages/vvvebjs.yaml
vvvebjs:
    upload:
        enabled: true
        handler: custom
        custom_handler: App\Upload\MyUploadHandler
```

### Upload Configuration Reference

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enabled` | `bool` | `false` | Enable the built-in upload controller |
| `handler` | `string` | `'local'` | `'local'`, `'flysystem'`, or `'custom'` |
| `local_dir` | `string` | `'%kernel.project_dir%/public/uploads/vvvebjs'` | Local upload directory |
| `local_public_path` | `string` | `'/uploads/vvvebjs'` | Public URL path prefix |
| `flysystem_storage` | `string` | `null` | Flysystem storage service ID |
| `flysystem_path` | `string` | `'uploads/vvvebjs'` | Path within the Flysystem filesystem |
| `flysystem_public_url` | `string` | `''` | Public URL prefix for Flysystem files |
| `custom_handler` | `string` | `null` | Service ID of your `UploadHandlerInterface` |
| `max_file_size` | `int` | `10485760` | Maximum file size in bytes (10 MB) |
| `allowed_mime_types` | `array` | `['image/jpeg', 'image/png', ...]` | Allowed MIME types |

## Data Format

VvvebJs outputs full HTML. The value stored in your entity will be a complete HTML string including the `<html>`, `<head>`, and `<body>` tags:

```html
<html>
<head></head>
<body>
    <div class="container mt-4">
        <h1>My Page</h1>
        <p>Built with drag-and-drop.</p>
    </div>
</body>
</html>
```

### Rendering in Twig

To display VvvebJs content in your templates, render the HTML directly:

```twig
{{ myEntity.content|raw }}
```

Or embed it within an iframe for full-page previews:

```twig
<iframe srcdoc="{{ myEntity.content|e('html_attr') }}" style="width: 100%; height: 600px; border: none;"></iframe>
```

## CDN & Self-Hosting

By default, VvvebJs assets are loaded from the jsDelivr CDN:

```
https://cdn.jsdelivr.net/gh/givanz/VvvebJs@master
```

To self-host VvvebJs or use a specific version, configure the `cdn_url`:

```yaml
# config/packages/vvvebjs.yaml
vvvebjs:
    cdn_url: '/bundles/vvvebjs'  # Local path
    # or
    cdn_url: 'https://cdn.jsdelivr.net/gh/givanz/VvvebJs@v0.7.7'  # Pinned version
```

## JavaScript Events

The Stimulus controller loads VvvebJs assets dynamically and initializes the builder. The editor syncs HTML back to the hidden form input on form submit and before page unload.

## Symfony Live Component Compatibility

The editor container is built dynamically by the Stimulus controller, so it works correctly alongside Symfony Live Components.

## Requirements

- PHP >= 8.1
- Symfony 6.4, 7.x, or 8.x
- `symfony/stimulus-bundle` >= 2.9.1

## License

MIT
