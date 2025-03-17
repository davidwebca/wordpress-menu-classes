# WordPress Menu Classes

Allow adding custom classes to WordPress li, a and submenus at different depths. Perfect for TailwindCSS and AlpineJS usage.

This package adds WordPress filters to allow custom arguments to wp_nav_menu to, in turn, allow custom classes to every element of a menu. You can apply a class only to certain depth of your menu as well.

This is probably as far as you can go without creating a custom nav walker which I've always hated to do.

## Requirements

- [Composer](https://getcomposer.org/download/)

## Installation

Install via Composer:

```bash
$ composer require davidwebca/wordpress-menu-classes
```

If your theme already uses composer, the filters will be automatically added thanks to the auto-loading and auto-instantiating class. Otherwise, add this to the top of your functions.php: 

```php 
require_once 'vendor/autoload.php';
```

And if you're looking for a standalone file you want to add to your theme, look for src/WordPressMenuClasses.php in this repository.

## Instructions

The filters use the depth argument given by WordPress which is an index, thus starts with level 0 (zero).

Here's a list of the custom arguments you can pass to wp_nav_menu that are supported by this package : 

- ```a_atts``` or ```a_atts_$depth``` or ```a_atts_order_$order```
- ```a_class``` or ```a_class_$depth``` or ```a_class_order_$order```
  - Add any attribute or class to ```<a>``` elements

- ```li_atts``` or ```li_atts_$depth``` or ```li_atts_order_$order```
- ```li_class``` or ```li_class_$depth``` or ```li_class_order_$order```
  - Add any attribute or class to ```<li>``` elements

- ```submenu_atts``` or ```submenu_atts_$depth```
- ```submenu_class``` or ```submenu_class_$depth```
  - Add any attribute or class to submenu ```<ul>``` elements. Note that submenus do not support order.

Ex.: add a "text-black" class to all links and "text-blue" class only to 3rd level links

```php
wp_nav_menu([
    'theme_location' => 'primary_navigation',
    'a_class' => 'text-black',
    'a_class_2'
    // ...
]);
```

Ex.: Supports classes as array (this is non-native to WordPress, but provided by this package as a convenience)

```php
wp_nav_menu([
    'theme_location' => 'primary_navigation',
    'a_class' => ['text-white', 'bg-blue-500'],
    'li_atts' => [
        'class' => ['focus:ring-2', 'ring-orange-500']
    ]
    // ...
]);
```

Ex.: More complete example with some TailwindCSS classes and AlpineJS sugar. This is a fully functional accordion navigation without additional JavaScript (requires Alpine's x-collapse plugin).

```php
wp_nav_menu([
    'theme_location' => 'primary_navigation',
    'container' => 'nav',
    'menu_class' => 'list-none p-0 m-0',
    'a_class_0' => "font-bold inline-flex items-center text-xl",
    'li_atts_0' => [
        'class' => "w-full px-6 before:mr-4 before:cursor-pointer before:shrink-0 before:grow-0 before:inline-flex before:justify-center before:items-center before:w-6 before:h-6 before:rounded before:bg-black before:text-white before:p-1 before:hover:opacity-50 before:transition",
        ':class' => "{'before:content-[\'+\']': !opened, 'before:content-[\'-\']': opened}",
        'x-data' => "{opened: false}",
        'x-on:click' => 'opened = !opened'
    ],
    'submenu_class_0' => 'wowza',
    'submenu_atts_0' => [
        'x-show' => 'opened',
        'x-collapse' => "_",
        'class' => 'list-none pl-10'
    ]
]);
```

## Widgets menus

This package allows you to pass custom arguments to wp_nav_menu calls that are found in your theme, but what about that case where you want to use a menu inside a widget? Well, there's a WordPress filter for that! 

Since the filters declared by this package work with any wp_nav_menu call, which the widgets do internally, we can simply add a filter per sidebar and declare how we'd like to style them here.

```php
add_filter('widget_nav_menu_args', function($nav_menu_args, $nav_menu, $args, $instance) {
    if($args['id'] == 'sidebar-footer') {
        $nav_menu_args['menu_class'] = 'mt-5 font-alt text-sm font-normal leading-7 opacity-60';
        $nav_menu_args['a_class'] = 'text-gray-300 opacity-60 hover:opacity-100';
    }
    return $nav_menu_args;
}, 10, 4);
```


## Known issues

If you're using TailwindCSS like I am, you'll encouter a [weird limitation](https://core.trac.wordpress.org/ticket/33924) that is still being tracked by the WP internal team which doesn't allow you to specify some CSS spec allowable characters like the colon which is heavily used by Tailwind, but only inside the WordPress admin. In other words, they parse out special characters that shouldn't be removed when you enter classes in the multiple "classes" fields everywhere, such as in the Nav Menus.

Because of that, I've added a default replacement for triple underscores '___' to ':'. If you encounter some other limitaitons with different characters, I've provided a custom filter to add your own replacements in the form of preg_replace arguments.


```php
add_filter('nav_menu_css_class_unescape_patterns', function($patterns) {
    $patterns = '/___/';
    // $patterns = array('/___/', '/\*\*\*/');
    return $patterns;
}, 10, 1);

add_filter('nav_menu_css_class_unescape_replacements', function($replacements) {
    $replacements = ':';
    // $replacements = array(':', '!');
    return $replacements;
}, 10, 1);
```

Those are passed straight to [preg_replace](https://www.php.net/manual/en/function.preg-replace.php) which accepts either an array or a string for either values. The classes that you add to your nav menus in the admin can now use Tailwind css classes like so : ```sm___bg-white sm___hover___opacity-100```.

## Bug Reports and contributions

All issues can be reported right here on github and I'll take a look at it. Make sure to give as many details as possible since I'm working full-time and will only look at them once in a while. Feel free to add the code yourself with a pull request.

## License

This code is provided under the [MIT License](https://github.com/davidwebca/wordpress-menu-classes/blob/main/LICENSE).