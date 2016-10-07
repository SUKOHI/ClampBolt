# ClampBolt
A Laravel package to attach/detach files to/from model.  
(This is for Laravel 5+. [For Laravel 4.2](https://github.com/SUKOHI/ClampBolt/tree/1.0))

# Installation

Execute composer command.

    composer require sukohi/clamp-bolt:3.*

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

**Retrieve attachment data** 

You can get attachment data through an attribute called `attachments`.

    $item = \App\Item::find(1);
    
    foreach ($item->attachments as $attachment) {
    
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
    
    }


    // Array with key

    $filenames = $item->attachment_filenames;
    $paths = $item->attachment_paths;
    
    
    // Filter by key
    
    $attachment_key = 'YOUR-KEY';
    
    if($item->hasAttachment($attachment_key)) {

        $attachment = $item->getAttachment($attachment_key);
        echo $attachment->key;  // YOUR-KEY

    }

* If you use "dot-notion" like `array_key.0` for attachment key, `attachment_filenames` and `attachment_paths` attributes return multi-dimensional array.

**Unneeded Files**

If there are unneeded files after attaching, detaching or deleting, you can get file paths with key.

    $unneeded_paths = $item->unneeded_file_paths;
    
    or 
    
    $unneeded_multi_dimensional_paths = $item->unneeded_multi_dimensional_file_paths;

**File Stream**

You can return `response` for streaming like so.

    return $item->getAttachment($key)->stream();  

    // or
    
    $offset = 1024;
    return $item->getAttachment($key)->stream($offset);  

**Note**

This package actually does NOT manipulate files like saving and removing. 

# License

This package is licensed under the MIT License.

Copyright 2016 Sukohi Kuhoh