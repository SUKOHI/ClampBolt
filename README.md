# ClampBolt
A Laravel package to attach/detach files to/from model.  
(This maintained in L5.4)

# Installation

Execute composer command.

    composer require sukohi/clamp-bolt:4.*

Register the service provider in app.php

    'providers' => [
        ...Others...,  
        Sukohi\ClampBolt\ClampBoltServiceProvider::class,
    ]

# Preparation

First of all, execute `migrate` command from the package.

    php artisan vendor:publish
    php artisan migrate

And you need to set `ClampBoltTrait` into your model like so.

    use Sukohi\ClampBolt\ClampBoltTrait;
    
    class Item extends Model
    {
        use ClampBoltTrait;
    }
    
That's all.  
Now you can use new methods called `attach` and `detach` with your model.

# Usage

***Attachment***
    
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

[Parameters]: You can add parameters to each attachments.
    
    $parameters = [
        'key_1' => 'value_1', 
        'key_2' => 'value_2', 
        'key_3' => 'value_3'
    ];
    $item = \App\Item::find(1);
    $item->attach('attachment_key', '/PATH/TO/YOUR/FILE', $parameters);
    $item->save();

[Auto-Saving]

If you directly set `Request` parameter like the below, ClampBolt will automatically save the file in `storage` folder.

    public function upload(Request $request, Item $item) {

        $item->attach('attachment_key', $request->photo);
        $item->save();

    }
    
[Note:] The file path is `/storage/app/attachment_key/` in this case.

And if you use dot notation like so, all of the files will be saved in `photos`.  
I mean in the same folder.
    
    public function upload(Request $request, Item $item) {

        $item->attach('photos.1', $request->photo_1);
        $item->attach('photos.2', $request->photo_2);
        $item->attach('photos.3', $request->photo_3);
        $item->save();

    }


[Deleting old file]: If you'd like to delete old file, set `true` in the 4th argument.

    $item->attach('attachment_key', '/PATH/TO/YOUR/NEW/FILE', [], true);

**Detachment**  

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
    

**Retrieve attachment** 

    $item = \App\Item::find(1);
    $attachment = $item->getAttachment($key);
    
    echo $attachment->id;
    echo $attachment->model;
    echo $attachment->model_id;
    echo $attachment->key;
    echo $attachment->dir;
    echo $attachment->filename;
    echo $attachment->full_path;
    echo $attachment->extension;
    echo $attachment->mime_type;
    echo $attachment->size;
    echo $attachment->parameters;   // Array
    echo $attachment->created_at;   // DateTime
    echo $attachment->updated_at;   // DateTime


You also can get all attachments at once like so.

    $item = \App\Item::find(1);
    
    foreach ($item->attachments as $key => $attachment) {
    
        // Do something..
    
    }

or 

    // Filenames
    $filenames = $item->attachment_filenames;
    
    // Paths
    $paths = $item->attachment_paths;
    
* If you use "dot-notation" like `array_key.0` for attachment key, `attachment_filenames` and `attachment_paths` attributes return multi-dimensional array.

**Check if attachment exists**
    
    $key = 'YOUR-KEY';
    
    if($item->hasAttachment($key)) {

        $attachment = $item->getAttachment($key);

    }


**Download**


    return $item->getAttachment($key)->download();  

    // or
    
    return $item->getAttachment($key)->download('filename.jpg');  

**Response**

    return $item->getAttachment($key)->response();  

# License

This package is licensed under the MIT License.

Copyright 2016 Sukohi Kuhoh