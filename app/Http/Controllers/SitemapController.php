<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Template;

class SitemapController extends Controller
{
    /**
     * Contains route name and the associated priority
     *
     * @var array
     */
    protected $urls = [
        ['/', 1],
        ['/pricing', 0.9],
        ['/privacy-policy', 0.5],
        ['/terms-conditions', 0.5],
        ['/login', 0.4],
        ['/register', 0.4],
        ['/password/reset', 0.3],
        ['/form-templates', 0.9],
    ];

    public function getSitemap(Request $request)
    {
        $sitemap = Sitemap::create();
        foreach ($this->urls as $url) {
            $sitemap->add($this->createUrl($url[0], $url[1]));
        }
        $this->addTemplatesUrls($sitemap);
        $this->addTemplatesTypesUrls($sitemap);
        $this->addTemplatesIndustriesUrls($sitemap);

        return $sitemap->toResponse($request);
    }

    private function createUrl($url, $priority, $frequency = 'daily')
    {
        return Url::create($url)->setPriority($priority)->setChangeFrequency($frequency);
    }

    private function addTemplatesUrls(Sitemap $sitemap)
    {
        Template::where('publicly_listed', true)->chunk(100, function ($templates) use ($sitemap) {
            foreach ($templates as $template) {
                $sitemap->add($this->createUrl('/form-templates/' . $template->slug, 0.8));
            }
        });
    }

    private function addTemplatesTypesUrls(Sitemap $sitemap)
    {
        $types = json_decode(file_get_contents(resource_path('data/forms/templates/types.json')), true);
        foreach ($types as $type) {
            $sitemap->add($this->createUrl('/form-templates/types/' . $type['slug'], 0.7));
        }
    }

    private function addTemplatesIndustriesUrls(Sitemap $sitemap)
    {
        $industries = json_decode(file_get_contents(resource_path('data/forms/templates/industries.json')), true);
        foreach ($industries as $industry) {
            $sitemap->add($this->createUrl('/form-templates/industries/' . $industry['slug'], 0.7));
        }
    }
}
