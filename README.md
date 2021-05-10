# WordPress Menu Classes

Allow adding custom classes to WordPress menu ul, li, a and at different depths. Perfect for TailwindCSS and AlpineJS usage.

This package adds WordPress filters to allow custom arguments to wp_nav_menu to, in turn, allow custom classes to every element of a menu. You can apply a class only to certain depth of your menu as well.

This is probably as far as you can go without creating a custom nav walker which I've always hated to do.

## Requirements

- [Composer](https://getcomposer.org/download/)

## Installation

Install via Composer:

```bash
$ composer require davidwebca/wordpress-menu-classes
```

If your theme already uses composer, the filters will be automatically added thanks to the auto-loading and auto-instantiating class. Otherwise, if you're looking for a standalone file you want to add to your theme, either look for src/WordPressMenuClasses.php in this repository or add this [gist](https://gist.github.com/davidwebca/a7b278bbb0c0ce1d1ec5620126e863bb) in your theme's functions.php.

## Instructions

The filters use the depth argument given by WordPress which is an index, thus starts with level 0 (zero).

Here's a list of the custom arguments you can pass to wp_nav_menu that are supported by this package : 

- ```link_atts``` or ```link_atts_$depth```
  - Add any attribute to ```<a>``` elements
- ```a_class``` or ```a_class_$depth```
  - Add classes to ```<a>``` elements
- ```li_class``` or ```li_class_$depth```
  - Add classes to ```<li>``` elements
- ```submenu_class``` or ```submenu_class_$depth```
  - Add classes to submenu ```<ul>``` elements

Ex.: add a "text-black" class to all links and "text-blue" class only to 3rd level links

```php
wp_nav_menu([
    'theme_location' => 'primary_navigation',
    'a_class' => 'text-black',
    'a_class_2'
    // ...
]);
```

Ex.: More complete example with some TailwindCSS classes and AlpineJS sugar

```php
wp_nav_menu([
    'theme_location' => 'primary_navigation',
    'menu_class' => 'relative w-full z-10 pl-0 list-none flex',
    'link_atts_0' => [
        ":class" => "{ 'active': tab === 'foo' }",
        "@click" => "tab = 'foo'"
    ],
    'li_class' => 'w-full',
    'li_class_0' => 'mb-12',
    'a_class' => 'text-sm xl:text-xl text-white border-b hover:border-white',
    'a_class_0' => 'text-3xl xl:text-5xl relative dash-left js-stagger  a-mask after:bg-primary',
    'li_class_1' => 'js-stagger a-mask after:bg-primary hidden lg:block',
    'a_class_1' => 'flex h-full items-center uppercase py-2 relative border-white border-opacity-40 hover:border-opacity-100',
    'submenu_class' => 'list-none pl-0 grid grid-cols-1 lg:grid-cols-2 lg:gap-x-12 xl:gap-x-24 xxl:gap-x-32',
    'container'=>false
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

This code is provided under the [MIT License](https://github.com/log1x/sage-directives/blob/master/LICENSE.md).