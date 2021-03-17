<?php

namespace Xanweb\C5\Request;

use Concrete\Core\Page\Page as ConcretePage;
use Concrete\Core\Support\Facade\Url;
use League\URL\URLInterface;
use Xanweb\Common\Traits\ApplicationTrait;
use Xanweb\Common\Traits\SingletonTrait;
use Xanweb\C5\Request\Page as RequestPage;
use Xanweb\C5\Request\Traits\AttributesTrait;

class Site
{
    use ApplicationTrait;
    use AttributesTrait;
    use SingletonTrait;

    /**
     * @var \Concrete\Core\Entity\Site\Site
     */
    private $site;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct()
    {
        $this->site = $this->app('site')->getSite();
    }

    public static function getSiteHomePageURL(): ?URLInterface
    {
        return Url::to(static::getSiteHomePageObject());
    }

    public static function getSiteHomePageObject(): ?ConcretePage
    {
        $rs = self::get();
        if (!isset($rs->cache['siteHomePageObject']) && $homePageID = static::getSiteHomePageID()) {
            $homePage = ConcretePage::getByID($homePageID, 'ACTIVE');
            if (is_object($homePage) && !$homePage->isError()) {
                $rs->cache['siteHomePageObject'] = $homePage;
            }
        }

        return $rs->cache['siteHomePageObject'];
    }

    public static function getSiteHomePageID(): int
    {
        $rs = self::get();

        return $rs->cache['siteHomePageID'] ?? $rs->cache['siteHomePageID'] = (int) $rs->site->getSiteHomePageID();
    }

    public static function getLocaleHomePageURL(): ?URLInterface
    {
        return Url::to(static::getLocaleHomePageObject());
    }

    public static function getLocaleHomePageObject(): ?ConcretePage
    {
        $rs = self::get();
        if (!isset($rs->cache['localeHomePageObject']) && $homePageID = static::getLocaleHomePageID()) {
            $homePage = ConcretePage::getByID($homePageID, 'ACTIVE');
            if (is_object($homePage) && !$homePage->isError()) {
                $rs->cache['localeHomePageObject'] = $homePage;
            }
        }

        return $rs->cache['localeHomePageObject'];
    }

    public static function getLocaleHomePageID(): int
    {
        $rs = self::get();
        if (!isset($rs->cache['localeHomePageID'])) {
            $localeHomePageID = 0;
            $activeLocale = RequestPage::getLocale();
            foreach ($rs->site->getLocales() as $locale) {
                if ($locale->getLocale() === $activeLocale) {
                    $localeHomePageID = $locale->getSiteTreeObject()->getSiteHomePageID();
                    break;
                }
            }

            $rs->cache['localeHomePageID'] = (int) $localeHomePageID;
        }

        return $rs->cache['localeHomePageID'];
    }

    public static function getDisplaySiteName(): string
    {
        return tc('SiteName', static::getSiteName());
    }

    public static function getSiteName(): string
    {
        $rs = self::get();

        return $rs->cache['siteName'] ?? $rs->cache['siteName'] = $rs->site->getSiteName();
    }

    public static function getAttribute($ak, $mode = false)
    {
        $rs = self::get();
        if ($rs->site === null) {
            return null;
        }

        return self::_getAttribute($rs->site, $ak, $mode);
    }

    public function __call($name, $arguments)
    {
        return $this->site->$name(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return self::get()->site->$name(...$arguments);
    }
}