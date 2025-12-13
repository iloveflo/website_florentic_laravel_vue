<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        News::create([
            'title' => 'New Florentic Collection Released',
            'slug' => Str::slug('New Florentic Collection Released'),
            'content' => 'Brand new products are now available',
            'author_id' => 1,
            'status' => 'published'
        ]);
    }
}
