<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateRootAssetDirectories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:directories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all necessary directories so that media, logs, etc. can be saved to disk from the  
    jump without errors';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! is_dir(base_path('public/media'))) {
            mkdir(base_path('public/media'), 0755, true);
        }
        if (! is_dir(base_path('public/media/users/image_avatar'))) {
            mkdir(base_path('public/media/users/image_avatar'), 0755, true);
        }
    }
}
