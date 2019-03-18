# ClampBolt
A Laravel package to attach/detach files to/from model.  
(This package is maintained under L5.7)

# Installation

Execute composer command.

    composer require sukohi/clamp-bolt:4.*

Register `ClampBoltServiceProvider` in `app.php` if your Laravel version is less than 5.4.

    'providers' => [
        // ...Others...,  
        Sukohi\ClampBolt\ClampBoltServiceProvider::class,
    ]

# Preparation

First of all, execute `publish` and `migrate` command.

    php artisan vendor:publish --provider="Sukohi\ClampBolt\ClampBoltServiceProvider"
    php artisan migrate

And set `ClampBoltTrait` into your model.

    use Sukohi\ClampBolt\ClampBoltTrait;
    
    class Item extends Model
    {
        use ClampBoltTrait;
    }
    
That's all.  
Now your model has 2 methods called `attach()` and `detach()`.

# Usage

## Attachment
    
[Basic way]:  
    
    $item = \App\Item::find(1);
    $item->attach('attachment_key', '/PATH/TO/YOUR/FILE');
    $item->save();

[Multiple way]:  

    $item = \App\Item::find(1);
    $item->attach('attachment_key_1', '/PATH/TO/YOUR/FILE1');
    $item->attach('attachment_key_2', '/PATH/TO/YOUR/FILE2');
    $item->attach('attachment_key_3', '/PATH/TO/YOUR/FILE3');
    $item->save();
    
    // or
    
    $item->attach([
        'attachment_key_1' => '/PATH/TO/YOUR/FILE1',
        'attachment_key_2' => '/PATH/TO/YOUR/FILE2',
        'attachment_key_3' => '/PATH/TO/YOUR/FILE3'
    ]);

[Parameters]: 

You can add parameters to each attachments.
    
    $parameters = [
        'key_1' => 'value_1', 
        'key_2' => 'value_2', 
        'key_3' => 'value_3'
    ];
    $item = \App\Item::find(1);
    $item->attach('attachment_key', '/PATH/TO/YOUR/FILE', $parameters);
    $item->save();

[Auto-Saving]:

If you directly set `Request` parameter like the below, ClampBolt will automatically save the file in `storage` folder.

    public function upload(Request $request, Item $item) {

        $item->attach('attachment_key', $request->file('photo'));
        $item->save();

    }
    
Note: The file path is `/storage/app/attachment_key/` in this case.

And if you use dot notation as follows, all of the files will be saved in `photos`.  
I mean in the same folder.
    
    public function upload(Request $request, Item $item) {

        $item->attach('photos.1', $request->file('photo_1'));
        $item->attach('photos.2', $request->file('photo_2'));
        $item->attach('photos.3', $request->file('photo_3'));
        $item->save();
        
        // or 
        
        $item->attach('photos.*', $path); // Refer to "Wildcard key"
        $item->save();

    }

[Wildcard key]:

You can use `*` to attach file(s).

    $item->attach('photos.*', $path);
    $item->save();
    
In this case, this package will automatically save new attachment key like `photos.0` or `photos.1`.  
You also can multiply attach files.

    $item->attach('photos.*', $path_1);
    $item->attach('photos.*', $path_2);
    $item->save();

And wildcard key is available for retrieving and detach files.

    // Retrieve
    $attachments = $item->getAttachment('photos.*');

    // Detach
    $item->detach('photos.*');
    $user->save();

[Deleting old file]:  

If you'd like to delete old file, set `true` in the 4th argument.

    $item->attach('attachment_key', '/PATH/TO/YOUR/NEW/FILE', [], true);

## Detachment 

[Basic way]:  

    $item = \App\Item::find(1);
    $item->detach('key');
    $item->save();

[Multiple way]:  

    $item = \App\Item::find(1);
    $item->detach('key');
    $item->detach('key2');
    $item->detach('key3');
    $item->save();
    
    // or
    
    $item->detach(['key', 'key2', 'key3']);
    $item->save();
    
[All]:

    $item = \App\Item::find(1);
    $item->detachAll();
    $item->save();

[with Deleting file(s)]

    $item->detach('key', true);
    $item->save();
    
    // or
    
    $item->detach(['key', 'key2', 'key3'], true);
    $item->save();
    
    // or 
    
    $item->detachAll(true);
    $item->save();
    

## Retrieve attachment

    $item = \App\Item::find(1);
    $attachment = $item->getAttachment($key);
    
    echo $attachment->id;
    echo $attachment->model;
    echo $attachment->model_id;
    echo $attachment->key;
    echo $attachment->dir;
    echo $attachment->filename;
    echo $attachment->path;
    echo $attachment->extension;
    echo $attachment->mime_type;
    echo $attachment->size;
    echo $attachment->public_url;
    echo $attachment->parameters;   // Array
    echo $attachment->created_at;   // DateTime
    echo $attachment->updated_at;   // DateTime

Note: `public_url` attribute is availabe if the file is stored in /storage/public. This means that you need to make a symbolic link in your Laravel app.  
See [here](https://laravel.com/docs/5.7/filesystem#the-public-disk).


You also can get all attachments at once as follows.

    $item = \App\Item::find(1);
    
    foreach ($item->attachments as $key => $attachment) {
    
        // Do something..
    
    }

[Filenames]:

    $filenames = $item->attachment_filenames;

[Paths]:

    $paths = $item->attachment_paths;

[Public URLs]

    $public_urls = $item->attachment_public_urls;
    
Note: 

If you use `dot-notation` like `array_key.0` for attachment key, `attachment_filenames`, `attachment_paths` and `attachment_public_urls` attributes return multi-dimensional array.  
And values of `attachment_public_urls` is empty if they are not stored in /storage/public.  
See `Retrieve attachment` in this file.

## Check if attachment exists

    if($item->hasAttachment('attachment_key')) {

        // has it!

    }
    
    // or multiply
    
    if($item->hasAttachment('attachment_key.*')) {

        // has it!

    }


## Download

Call `download()` in your controller or routing.

    return $attachment->download();

    // or
    
    return $attachment->download('filename.jpg');  

## Response

Call `response()` in your controller or routing.

    return $attachment->response();  

# Events

You can call `attached` and `detached` events.

    class Item extends Model
    {
        protected $dispatchesEvents = [
            'attached' => ItemAttached::class,
            'detached' => ItemDetached::class
        ];

(in Your event)

    class ItemAttached
    {
        public function __construct(Attachment $attachment)
        {
            // Do someting..
        }

Note: The first argument of constructor is `$attachment` instance. Not parent model instance.

# Where clause

`whereHasAttachment()` allows you to retrieve item(s) that have at least one attachment .

    $users = \App\User::whereHasAttachment('attachment_key')->get();

or you can use wildcard as follows.

    $users = \App\User::whereHasAttachment('attachment_key.*')->get();

# Validation rule

#### total_attachment:table,id,attachment_key,min,max,detaching_count

The total number of files under validation must be between `:min` and `:max`.  
This total number is including files that already be stored in your app.

    'photos' => 'total_attachment:users,1,photos,1,5'

Add `detaching_count` at the end of the parameters if you are detaching file at the same time of attaching.

    'photos' => 'total_attachment:users,1,photos,1,5,3'

Then add an error message in `/resources/lang/**/validation.php`.

    // en
    'total_attachment' => 'The total number of :attribute must be between :min and :max.'

    // ja
    'total_attachment' => ':attributeの全件数は、:min個から:max個にしてください。'

# Commands

## Clear attachment

You can delete all attachment files and clear `attachments` table by running `attachment:clear` command.

    php artisan attachment:clear

Or without confirmation

    php artisan attachment:clear --force

Note: Folder will not be deleted.

# Relationship

## ClampBoltTrait

You have two relationships called `attachments` and `public_attachments`.  
The first one has all of data but `public_attachments` has only some data so that you can publish even for visitors like ajax data.  
The keys are as follows.

* id
* model_id
* key
* filename
* extension
* mime_type
* parameters
* public_url

And usage with `public_attachments`.  

    $user = \App\User::with('public_attachments')->find(1);

## Attachment

    $user = \App\User::find(1);

    foreach($user->attachments as $attachment) {

        $parent = $attachment->parent;  // \App\User

    }

# Thumbnail

You can generate thumbnail if attaching file is image as follows.

    $user->attach('image', $file, [], true)->thumbnail([
        '500x300',
        '300x300'
    ]);
    
In this case, the attachment keys are `image_thumbnail_500x300` and `image_thumbnail_300x300`.

Of course, wildcard is available.

    $user->attach('photos.*', $file, [], true)->thumbnail([
        '700x500',
        '150x150'
    ]);

Or Attachment also has `thumbnail()`.

    $width = 500;
    $height = 300;
    $attachment->thumbnail($width, $height);

# When deleting

This package detaches all of attachment data corresponding to a record that you are deleting.
If you also would like to delete attachment files, set `true` in the 1st argument.

    $item->delete(true);

# Set directory

If you'd like to save your uploaded file in a specific directory, use `setAttachmentDir()`.

    $user->setAttachmentDir('public/nested_1/nested_2/nested_3');
    $user->attach('attachment_key', $request->file('profile'));
    $user->save();

In this case, the file will be stored in `/storage/app/public/nested_1/nested_2/nested_3/attachment_key/`.

# License

This package is licensed under the MIT License.

Copyright 2016 Sukohi Kuhoh