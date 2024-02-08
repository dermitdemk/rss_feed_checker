# fuctions_needed_for_testng
Das soll eine ort sein in dem alle funkitonen die man zu bauen von tests brauhct gespeichert werden. Diese können dann per composer reingeladen werden

das ist der code welcher in die composer.json geschrieben werden muss. dannach einfach kurz `composer update` ausführen und alles sollte gehen
```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:funke-pe/functions_needed_for_testing.git"
        }
    ],
    "require": {
        "funke-pe/functions_needed_for_testing": "*"
    },
    "minimum-stability": "dev"
}
```
liste an funcitonen 
- get_random_articel_out_of_news_sitemap($webseite, $anzahl_an_artikel = 5) return $zufällige_articel;

- navBarForTest()

- getMyDomain()

- didTestPass($json) return true/false

- ensureTrailingSlash($url) return $url

- sitename_to_cluster($site) return $cluster

- get_Site_and_Cluster_info_out_of_url($url) return array($site, $env, $cluster);

- getUrlOutOfUrl($default_url = 'https://www.derwesten.de/') return $url