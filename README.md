<img width="2752" height="1536" alt="Dishari Menu Management" src="https://github.com/user-attachments/assets/4cbf1c1f-dd6f-4b06-aea3-1570a0dae195" />

# Dishari Menu Management

A powerful and flexible menu management package for Laravel applications built with Inertia.js and Vue 3. This package provides a drag-and-drop interface for managing nested menus, complete with icon support, groups, and active status toggling.

## Features

- 📱 **Drag & Drop Interface**: Intuitive UI for reordering and nesting menu items.
- 🌳 **Nested Structure**: Support for unlimited levels of nested submenus.
- 📂 **Menu Groups**: Organize menus into logical groups (e.g., Platform, Settings).
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

Run the `dishari:install` command to publish the configuration, migrations, and frontend assets.

```bash
php artisan dishari:install
```

During installation, you will be asked to provide a **directory name** (default: `dishari`).
This determines where the frontend files will be published:

- **Pages**: `resources/js/pages/{directoryName}`
- **Components**: `resources/js/components/{directoryName}`

### 3. Run Migrations

Run the migrations to create the menu tables:

```bash
php artisan migrate
```

### 4. Compile Assets

Recompile your assets to include the new components:

```bash
npm run dev
```

## Frontend Integration

To display the dynamic menu in your application, you need to update your Sidebar component (usually `resources/js/components/AppSidebar.vue` or similar).

### 1. Update Menu Data Source

Locate your sidebar component and replace the static menu items with the `useDishari` hook.

**Remove static data like this:**

```typescript
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];
```

**Add the dynamic hook:**

```typescript
import { useDishari } from '@/lib/useDishari';

const { menus: mainNavItems } = useDishari();
```

### 2. Import the NavMain Component

You need to import the `NavMain` component that was published to your project. The path depends on the **directory name** you chose during installation.

If you chose `dishari` (default):

```typescript
import NavMain from '@/components/dishari/NavMain.vue';
```

If you chose `menu`:

```typescript
import NavMain from '@/components/menu/NavMain.vue';
```

**Full Example (`AppSidebar.vue`):**

```vue
<script setup lang="ts">
import { useDishari } from '@/lib/useDishari';
// Import from the folder you chose during installation (e.g., 'dishari' or 'menu')
import NavMain from '@/components/dishari/NavMain.vue';

const { menus: mainNavItems } = useDishari();
</script>

<template>
    <Sidebar>
        <SidebarContent>
            <!-- Pass the dynamic items to the component -->
            <NavMain :items="mainNavItems" />
        </SidebarContent>
    </Sidebar>
</template>
```

## Usage

### Accessing the Management Interface

Once installed, you can access the menu management interface at:

```
/menu-management
```

### Configuration

The configuration file is located at `config/dishari.php`. You can customize the directory name, cache settings, and authentication requirements.

```php
return [
    'directory_name' => 'dishari', // The folder name for published Vue files
    'auto_share' => true,          // Automatically share menu data with Inertia
    // ...
];
```

## Requirements

- PHP 8.2+
- Laravel 11+
- Inertia.js
- Vue 3
- Tailwind CSS
- Shadcn Vue (recommended)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
