<?php

namespace Sukohi\ClampBolt\Commands;

use Illuminate\Console\Command;
use function PHPSTORM_META\type;

class AttachmentClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attachment:clear {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all stored attachment files and attachments table';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $force = $this->option('force');

        if ($force || $this->confirm('Do you wish to continue?')) {

            $attachments = \Sukohi\ClampBolt\App\Attachment::all();

            foreach ($attachments as $attachment) {

                $path = $attachment->path;

                if(file_exists($path)) {

                    $result = @unlink($path);

                    if($result) {

                        $attachment->delete();

                    } else {

                        $this->error("[Error]: Failed to delete an attached file.\n". $path);
                        $this->warn('Command stopped.');
                        die();

                    }

                }

            }

            $this->info('Done!');

        }
    }
}
