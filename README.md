EasyWordpressBundle
===================

# 1 - Prerequisites and dependencies
PHP 7.0 is required.<br>
PHP 7.1 must not be used (WPML WordPress extension doesn't support this PHP version for the moment).

This bundle is to use with symfony 3.

`composer require extremesensio/easywordpressbundle`

# 2 - Installation & usage
## 2.1 With Commands
Enable the bundle `new EasyWordpressBundle\EasyWordpressBundle(),` in your `AppKernel.php`.

Use wp-cli to install wordpress in `web/wp` : `wp-ci core download --path=project/web/wp`.<br>
More informations on wp-cli: http://wp-cli.org/fr/

You can use `easywordpress:install-wordpress` to automatically (to do inside docker):
 - copy `wp-content` in `web/content`
 - move `web/wp-content/themes` to `app/Resources` and symlink to `web/content/themes`

You can use `easywordpress:generate:files` to automatically:
 - create `web/index.php` file
 - create `web/boot.php` file
 - create `app/config/parameters.php` file
 - create `.env` file at root directory


For every page template you can use `easywordpress:generate:templates` to generate wp themes file using this annotation (do not generate twig file).
For a page post type:
```php
use EasyWordpressBundle\Annotation\TemplateName;
use EasyWordpressBundle\Controller\WordpressController;
...

class HomeController extends WordpressController
{
    /**
      * @TemplateName(name="Accueil")
    */
    public function homeAction(Request $request)
    {
        return $this->render(
            'AppBundle:Accueil:index.html.twig',
            [
                'base_dir' => realpath(
                    $this->container
                        ->getParameter('kernel.root_dir').'/..'
                ),
                ...
            ]
        );
    }
}
```

For a custom post type:
```php
use EasyWordpressBundle\Annotation\CustomPostType;
use EasyWordpressBundle\Controller\WordpressController;
...

class HomeController extends WordpressController
{
    /**
      * @TemplateName(name="customPostType")
    */
    public function homeAction(Request $request)
    {
        return $this->render(
            'AppBundle:Custom:index.html.twig',
            [
                'base_dir' => realpath(
                    $this->container
                        ->getParameter('kernel.root_dir').'/..'
                ),
                ...
            ]
        );
    }
}
```
Generate `app/Resource/themes/mythemename/page-templates/page_accueil.php`

## 2.2 Without Commands
Example files are available in doc folder.

Follow listed operations above and modify `web/.htaccess` too.

## 2.3 Update some files
### routing.yml
```yaml
wordpress:
    resource: "@EasyWordpressBundle/Resources/config/routing.yml"
```

### config_dev.yml
```yaml
imports:    
    - { resource: parameters.php }
```

### config.yml
```yaml
easy_wordpress:
    wordpress_directory: '%kernel.root_dir%/../web/wp/'
    controllers_namespace: AppBundle\\Controller
    theme_directory: '%kernel.root_dir%/Resources/themes/mytheme/'
    yoast_title_override: %easywordpressbundle.yoast_title_override%
```

### web/.htaccess
You can use `doc/.htaccess.example`.

### functions.php
For every themes you need to add this 2 lines in `functions.php` :
```php
require_once __DIR__.'/../../../../web/boot.php';
symfony('wordpress.helper')->boot();
```

### configure your virtualhost
```apacheconfig
<Directory /var/www/html/web>
    AllowOverride All
    Require all granted

    <IfModule mod_rewrite.c>
        Options -Indexes
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ index.php [QSA,L]
    </IfModule>
</Directory>
```


# 3 - Features
## 3.1 Use EasyWordpress Controller methods
You have to extend your controllers with the EasyWordpress Controller :
```php
namespace AppBundle\Controller;

use EasyWordpressBundle\Annotation\TemplateName;
use EasyWordpressBundle\Controller\WordpressController;

public function homeAction()
{
    // Get WordPress posts from the current page
    $posts = $this->getPosts();

    // Render in Twig view
    return $this->render(
        ':home:index.html.twig',
        [
            'base_dir' => realpath(
                $this
                    ->container
                    ->getParameter('kernel.root_dir').'/..'
            ),
            'posts' => $posts
        ]
    );
}
```

## 3.3 EasyWordpressBundle services

- CustomPostType
- Transient
- Helper
- Sitemap
- Nav

## 3.4 Twig functions
#### wp_head
```twig
  wp_head
```
Includes WordPress head (styles, scripts, meta and misc. tags).<br>
More informations: https://codex.wordpress.org/Plugin_API/Action_Reference/wp_head

#### wp_footer
```twig
  wp_footer
```
Includes WordPress footer (scripts tags).<br>
More informations: https://codex.wordpress.org/Function_Reference/wp_footer

#### get_content

#### wp_nav_menu
```twig
  wp_nav_menu(themeLocation, menu, menuClass, menuId, container, containerClass, containerId, echo, wp_page_menu, before, after, linkBefore, linkAfter, depth, walker)
```
Creates nav from a WordPress menu created from the theme option Appearance.<br>
Only `themeLocation` and `menu` variables are required.<br>
More informations: https://developer.wordpress.org/reference/functions/wp_nav_menu/

#### _e
```twig
  _e(text, domain)
```
Display translated translated text.<br>
More informations: https://developer.wordpress.org/reference/functions/_e/

### __
```twig
  __(text, domain)
```
Retrieves the translation of text.<br>
More informations: https://developer.wordpress.org/reference/functions/__/

#### bloginfo
```twig
  bloginfo(info)
```
Displays information about the current site.<br>
More informations: https://developer.wordpress.org/reference/functions/bloginfo/

#### get_field
```twig
    get_field(selector, (postId), (formatValue))
```
Displays the content of a ACF field.<br>
More informations: https://www.advancedcustomfields.com/resources/get_field/

#### wp_thumbnail
```twig
  wp_thumbnail(id)
```
Returns the thumbnail url from the post id.

## 3.5 Twig filters

#### do_shortcode
```twig
    content|do_shortcode
```
Interprets a shortcode in html code. To display html tags, you have to use the Twig filter `|raw` or `|purify`.<br>
More informations about purify Twig filter: https://github.com/Exercise/HTMLPurifierBundle

## 3.6 Wordpress Yoast SEO plugin
If you use `Yoast SEO plugin`, you can override the `<title>` tag content inside the `<head>`. You have to enable the feature :
```yml
  easy_wordpress:
      yoast_title_override: true
```



Enjoy.

![](https://media.giphy.com/media/nNxT5qXR02FOM/giphy.gif)
