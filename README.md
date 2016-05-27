# ClampBolt
A Laravel package to attach/detach files to/from model.  
(This is for Laravel 4.2. [For Laravel 5+](https://github.com/SUKOHI/ClampBolt))

# Installation

Execute composer command.

    composer update sukohi/clamp-bolt:1.*

# Preparation

First of all, execute `migrate` command from the package.

    php artisan migrate --package="sukohi/clamp-bolt"

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

# When attached/detached?

When called save(), attachment or detachment will be executed.

# License

This package is licensed under the MIT License.

Copyright 2016 Sukohi Kuhoh