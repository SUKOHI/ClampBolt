<?php

namespace Sukohi\ClampBolt\Commands;

use Illuminate\Console\Command;
use \Sukohi\ClampBolt\App\Attachment;

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

            $attachments = Attachment::all();

            foreach ($attachments as $attachment) {

                $path = $attachment->path;

                if(file_exists($path)) {

                    $result = @unlink($path);

                    if(!$result) {

                        $this->error("[Error]: Failed to delete an attached file.\n". $path);
                        $this->warn('Command stopped.');
                        die();

                    }

                }

            }

            Attachment::truncate();
            $this->info('Done!');

        }
    }
}
