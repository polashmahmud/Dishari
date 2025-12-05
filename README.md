
<img width="2752" height="1536" alt="Gemini_Generated_Image_sovbr3sovbr3sovb" src="https://github.com/user-attachments/assets/4cbf1c1f-dd6f-4b06-aea3-1570a0dae195" />

# Dishari Menu Management

A powerful and flexible menu management package for Laravel applications built with Inertia.js and Vue 3. This package provides a drag-and-drop interface for managing nested menus, complete with icon support and active status toggling.

## Features

- 📱 **Drag & Drop Interface**: Intuitive UI for reordering and nesting menu items.
- 🌳 **Nested Structure**: Support for unlimited levels of nested submenus.
- 🎨 **Icon Integration**: Built-in support for Lucide icons with a searchable picker.
- ⚡ **Inertia.js & Vue 3**: Seamless integration with modern Laravel stacks.
- 🛠 **Fully Customizable**: Publishable Vue components to match your application's design.

## Installation

### 1. Require the Package

Install the package via Composer:

```bash
composer require polashmahmud/menu
```

### 2. Run the Installer

Run the `dishari:install` command to publish the configuration, migrations, and frontend assets. This command will also help you install necessary frontend dependencies.

```bash
php artisan dishari:install
```

During the installation, you will be prompted to:

- Publish the configuration file.
- Choose a directory name for the Vue pages (default: `dishari`).
- Publish the Vue pages and components.
- Publish the migration file.
- Install required NPM packages (`lucide-vue-next`, `vue-draggable-plus`, etc.).

### 3. Run Migrations

Run the migrations to create the `menus` table:

```bash
php artisan migrate
```

### 4. Compile Assets

Since this package publishes Vue components to your resources directory, you need to recompile your assets:

```bash
npm run dev
```

## Usage

### Accessing the Management Interface

Once installed, you can access the menu management interface at:

```
/menu-management
```

### Retrieving the Menu in Your Application

You can retrieve the hierarchical menu tree for use in your frontend (e.g., in your `HandleInertiaRequests` middleware or a specific controller):

```php
use Polashmahmud\Menu\Models\Menu;

// Get the full tree (active items only by default in some scopes, or filter manually)
$menuTree = Menu::tree();
```

### Configuration

The configuration file is located at `config/dishari.php`. You can customize the directory where the Vue files are stored:

```php
return [
    'directory_name' => 'dishari',
];
```

## Requirements

- PHP 8.2+
- Laravel 11+
- Inertia.js
- Vue 3
- Tailwind CSS
- Shadcn Vue (recommended for styling consistency)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
