<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Console\Command;

class WpImportContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wp:import:content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * в файле перед импортом нужно сделать замену
     * wp: на wp_
     * :content: на _content
     * тогда спарсится все что надо
     *
     * @return int
     */
    public function handle()
    {
        $file = storage_path() . '/content.xml';
        $xmlObject = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);

        $data = json_decode(json_encode($xmlObject), true);
        $posts = [];
        $images = [];
        foreach ($data["channel"]["item"] as $datum) {
            if(isset($datum["wp_post_type"]) && $datum["wp_post_type"] == 'post') {
                $posts[] = $datum;
            }
            if(isset($datum["wp_post_type"]) && $datum["wp_post_type"] == 'attachment') {
                $images[$datum["wp_post_id"]] = $datum;
            }
        }

        Category::query()->truncate();
        $categories = collect($data["channel"]["wp_category"])->keyBy('wp_cat_name')->toArray();
        foreach ($categories as &$category) {
            $model = new Category();
            $model->title = $category['wp_cat_name'];
            $model->slug = 'category/' . $category['wp_category_nicename'];
            $model->save();
            $category['model_id'] = $model->id;
        }

        Post::query()->truncate();

        foreach ($posts as $post) {
            if($post["wp_status"] != 'publish') {
                continue;
            }

            $model = new Post();
            $model->title = ltrim(rtrim($post['title']));
            $model->slug = str_replace('https://www.cscscholarship.org/', '', ltrim(rtrim($post['link'])));
            if(is_array($post["category"])) {
                $category = $categories[ltrim(rtrim($post["category"][0]))] ?? null;
            } else {
                $category = $categories[ltrim(rtrim($post["category"]))] ?? null;
            }
            $model->category_id = $category ? $category['model_id'] : null;
            $model->content = is_string($post["content_encoded"]) ? $post["content_encoded"] : '';

            $thumbnailId = null;
            if(isset($post["wp_postmeta"])) {
                foreach ($post["wp_postmeta"] as $item) {
                    if($item["wp_meta_key"] == '_thumbnail_id') {
                        $thumbnailId = $item["wp_meta_value"];
                    }
                }
            }

            if($thumbnailId && isset($images[$thumbnailId])) {
                $image = $images[$thumbnailId];
                $url = ltrim(rtrim($image['wp_attachment_url']));
                $fileInfo = pathinfo($url);
                $localUrl = sprintf('/blog-images/%s.%s',$fileInfo["filename"], $fileInfo["extension"]);
                $localPath = public_path() . $localUrl;
                file_put_contents($localPath , file_get_contents($url));
                $model->image = $localUrl;
            }

            $model->save();
            if($model->id == 12) {
                $a = 0;
            }
        }
        $a = 0;

    }
}
