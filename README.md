# ClampBolt
A Laravel package to attach/detach files to/from model.  
(This is for Laravel 5+. [For Laravel 4.2](https://github.com/SUKOHI/ClampBolt/tree/1.0))

# Installation

Execute composer command.

    composer update sukohi/clamp-bolt:2.*

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
    
    class Item extends Eloquent {
    
        use ClampBoltTrait;
        
    }
    
That's all.  
Now you can use new methods called `attach` and `detach` with your model.

# Usage

***Attachment***
    
[Basic way]:  
    
    $item = Item::find(1);
    $item->attach('/PATH/TO/YOUR/FILE');
    $item->save();

[Multiple way]: You can call `attach()` repeatedly.  

    $item = Item::find(1);
    $item->attach('/PATH/TO/YOUR/FILE1');
    $item->attach('/PATH/TO/YOUR/FILE2');
    $item->attach('/PATH/TO/YOUR/FILE3');
    $item->save();

[Parameters]: You can add parameters to each attachments.
    
    $parameters = [
        'key_1' => 'value_1', 
        'key_2' => 'value_2', 
        'key_3' => 'value_3'
    ];
    $item = Item::find(1);
    $item->attach('/PATH/TO/YOUR/FILE', $parameters);
    $item->save();

**Detachment**  

[Attachment ID]:

    $attachment_id = 1; // This is an ID of the table called `attachments`.

    $item = Item::find(1);
    $item->detach($attachment_id);
    $item->save();

[File path]: You also can use file path to detach.

    $item = Item::find(1);
    $item->detach('/PATH/TO/YOUR/FILE');
    $item->save();

[Remove file]: You can remove file by setting `true` as the 2nd argument like so.

    $item->detach(1, true);

[In iterator]: 

    $item = Item::find(1);
    
    foreach ($item->attachments as $attachment) {
    
        $remove_file = true;
        $attachment->delete($remove_file);
    
    }

**Retrieve attachment data**

You can get attachment data through an attribute called `attachments`.

    $item = Item::find(1);

    foreach ($item->attachments as $attachment) {
    
        echo $attachment->id;
        echo $attachment->model;
        echo $attachment->model_id;
        echo $attachment->path;
        echo $attachment->filename;
        echo $attachment->full_path;
        echo $attachment->extension;
        echo $attachment->mime_type;
        echo $attachment->size;
        echo $attachment->parameters;
        echo $attachment->created_at;
        echo $attachment->updated_at;
        echo $attachment->parameters;   // Here is array.
    
    }

# When attached/detached?

When called save(), attachment or detachment will be executed.

# License

This package is licensed under the MIT License.

Copyright 2016 Sukohi Kuhoh