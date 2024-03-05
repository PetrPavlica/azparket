<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Caching\Cache;


final class RouterFactory
{
	use Nette\StaticClass;

    /**
     * @param Nette\Database\Context $db
     * @param Nette\Caching\Cache $cache
     * @param string $locale
     * @return array
     */
    public static function getProductUrl($db, $cache, $locale)
    {
        $key = 'getProductUrl-'.$locale;
        $output = $cache->load($key);
        if ($output == null) {
            $output = [];
            $menu = $db->query('
                SELECT p.id, pl.url
                FROM product p
                LEFT JOIN product_language pl ON p.id = pl.product_id
                LEFT JOIN language l ON pl.lang_id = l.id
                WHERE l.code = ?', $locale)->fetchAll();
            if ($menu) {
                foreach($menu as $m) {
                    $output[$m['id']] = $m['url'];
                }
            }
            $cache->save($key, $output, [
                Cache::TAGS => ["productUrl"],
                Cache::EXPIRE => '+ 1 day'
            ]);
        }
        return $output;
    }

    /**
     * @param Nette\Database\Context $db
     * @param Nette\Caching\Cache $cache
     * @param string $locale
     * @return array
     */
    public static function getMenuUrl($db, $cache, $locale)
    {
        $key = 'getMenuUrl-'.$locale;
        $output = $cache->load($key);
        if ($output == null) {
            $output = [];
            $menu = $db->query('
                SELECT m.id, ml.url
                FROM menu m
                LEFT JOIN menu_language ml ON m.id = ml.menu_id
                LEFT JOIN language l ON ml.lang_id = l.id
                WHERE l.code = ?', $locale)->fetchAll();
            if ($menu) {
                foreach($menu as $m) {
                    $output[$m['id']] = $m['url'];
                }
            }
            $cache->save($key, $output, [
                Cache::TAGS => ["menuUrl"],
                Cache::EXPIRE => '+ 1 day'
            ]);
        }
        return $output;
    }

    /**
     * @param Nette\Database\Context $db
     * @param Nette\Caching\Cache $cache
     * @return array
     */
    public static function getLanguages($db, $cache)
    {
        $key = 'getLanguages';
        $output = $cache->load($key);
        if ($output == null) {
            $output = [];
            $langs = [];
            $defaultCode = null;
            $languages = $db->query('SELECT id, code, default_code FROM language ORDER BY order_code')->fetchAll();
            if ($languages) {
                foreach($languages as $l) {
                    if ($l['default_code']) {
                        $defaultCode = $l['code'];
                    }
                    $langs[$l['id']] = $l['code'];
                }
                $output = [
                    $defaultCode,
                    $langs,
                ];
            }
            $cache->save($key, $output, [
                Cache::TAGS => ["languages"],
                Cache::EXPIRE => '+ 1 day'
            ]);
        }
        return $output;
    }

    /**
     * @param Nette\Database\Context $db
     * @param Nette\Caching\IStorage $storage
     * @return Nette\Application\IRouter
     */
	public static function createRouter(Nette\Database\Context $db, Nette\Caching\IStorage $storage): RouteList
	{
        $cache = new Cache($storage);
        list($defaultCode, $languages) = self::getLanguages($db, $cache);

        $router = new RouteList;
        $api = new RouteList('Api');
        $api->addRoute('[<locale=cs cs|en>/]api/event/<action>[/<id>]', "EventApi:default");
        $router->add($api);

        $router[] = new Route('[<locale='.$defaultCode.' '.implode('|', $languages).'>/]sitemap.xml', 'Homepage:sitemap');
        $router[] = new Route('[<locale='.$defaultCode.' '.implode('|', $languages).'>/]robots.txt', 'Homepage:robots');

        // Intra module
        $intra = new RouteList('Intra');
        $intra->addRoute('[<locale=cs cs>/]admin', 'Sign:default');
        $intra->addRoute('[<locale=cs cs>/]intra/<presenter>/<action>[/<id>]', 'Homepage:default');
        $router->add($intra);


        // product
        $router[] = new Route('[<locale='.$defaultCode.' '.implode('|', $languages).'>/]product/<id>', [
            null => [
                Route::FILTER_IN => function (array $params) use ($db, $cache) {
                    $id = $params['id'];
                    $locale = $params['locale'];
                    $productUrl = self::getProductUrl($db, $cache, $locale);
                    if (is_numeric($id)) {
                        if (isset($productUrl[$id])) {
                            $params['id'] = $productUrl[$id];
                        } else {
                            return null;
                        }
                    } else {
                        $search = array_search($id, $productUrl);
                        if ($search) {
                            $params['id'] = $search;
                        } else {
                            return null;
                        }
                    }
                    return $params;
                },
                Route::FILTER_OUT => function (array $params) use ($db, $cache) {
                    $id = $params['id'];
                    $locale = (isset($params['locale']) ? $params['locale'] : 'cs') ;
                    $productUrl = self::getProductUrl($db, $cache, $locale);
                    if (!is_numeric($id)) {
                        $params['id'] = $id;
                    } else {
                        if (isset($productUrl[$id])) {
                            $params['id'] = $productUrl[$id];
                        } else {
                            return null;
                        }
                    }
                    return $params;
                }
            ],
            'presenter' => 'Product',
            'action' => 'default',
        ]);

        // menu
        $router[] = new Route('[<locale='.$defaultCode.' '.implode('|', $languages).'>/]<id>', [
            null => [
                Route::FILTER_IN => function (array $params) use ($db, $cache, $defaultCode) {
                    $id = $params['id'];
                    $locale = (isset($params['locale']) ? $params['locale'] : $defaultCode) ;
                    $menuUrl = self::getMenuUrl($db, $cache, $locale);
                    if (is_numeric($id)) {
                        if (isset($menuUrl[$id])) {
                            $params['id'] = $menuUrl[$id];
                        } else {
                            return null;
                        }
                    } else {
                        $search = array_search($id, $menuUrl);
                        if ($search) {
                            $params['id'] = $search;
                        } else {
                            return null;
                        }
                    }
                    return $params;
                },
                Route::FILTER_OUT => function (array $params) use ($db, $cache, $defaultCode) {
                    $id = $params['id'];
                    $locale = (isset($params['locale']) ? $params['locale'] : $defaultCode) ;
                    $menuUrl = self::getMenuUrl($db, $cache, $locale);
                    if (!is_numeric($id)) {
                        $params['id'] = $id;
                    } else {
                        if (isset($menuUrl[$id])) {
                            $params['id'] = $menuUrl[$id];
                        } else {
                            return null;
                        }
                    }
                    return $params;
                }
            ],
            'presenter' => 'Page',
            'action' => 'default',
        ]);

        $router[] = new Route('[<locale='.$defaultCode.' '.implode('|', $languages).'>/]akce/<id [0-9]+>-<slug>', 'Events:default');
        $router[] = new Route('[<locale='.$defaultCode.' '.implode('|', $languages).'>/]novinky/<id [0-9]+>-<slug>', 'News:default');
        $router[] = new Route('[<locale='.$defaultCode.' '.implode('|', $languages).'>/]clanek/<id [0-9]+>-<slug>', 'Article:default');
        $router[] = new Route('[<locale='.$defaultCode.' '.implode('|', $languages).'>/]<presenter>/<action>', 'Homepage:default');

		return $router;
	}
}
