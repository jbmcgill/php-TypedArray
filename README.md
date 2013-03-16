PHP TypedArray
===============

PHP TypedArray - create 'array-like-objects' that can hold millions of items in PHP with a significantly smaller memory footprint.

Background
----------

PHP's arrays are [greedy](http://nikic.github.com/2011/12/12/How-big-are-PHP-arrays-really-Hint-BIG.html) and use a lot of memory. While this usually isn't a problem as folks don't normally seem to use PHP for dealing with large amounts of data it seems to be coming up more and more often in my team's work. I guess we're just dealing with bigger data these days.

On my 32 bit laptop creating an array of 1 million numbers uses a whopping 100 MBytes which puts me very close to the default max memory config. On my workstation it actually Fatal Errors out with the dreaded "tried to allocate .... blah blah" fault.

It occurred to me one day after a particularly rambunctious discussion about sorting large arrays that it would be pretty straight forward to store the array items in a buffer as raw binary data using pack(). This reduced the memory consumption from 100+ MBytes to 4 and set me up with a very nice buffer I could send into my PHP extension as char* for my radix sorting algorithm. I was impressed and so was born the PHP TypedArray class.

Performance on an array of 1 million numbers
---------------------------------------------

Here's an example to compare against storing 1 million numbers in a PHP array. Notice the RAM use of 103.5 MBytes when storing huge quantities of numbers in an array. This was on an old 32 bit laptop, RAM use is significantly higher on 64 bit systems.

    (0.000s) 0.314M array() with php sort() function
    (0.000s) 0.314M --------------------------------
    (0.000s) 0.314M initializing array
    (0.000s) 0.314M array initialized
    (0.000s) 0.315M populating array with 1000000 longs 
    (1.575s) 103.497M finished population
    (0.000s) 103.497M starting sort
    (2.679s) 103.497M finished sort
    
    
    (0.000s) 0.315M pack('L') buffer with radix C algorithm as PHP extension
    (0.000s) 0.315M --------------------------------------------------------
    (0.000s) 0.315M initializing buffer
    (0.000s) 0.315M buffer initialized
    (0.000s) 0.315M populating buffer with 1000000 longs 
    (1.765s) 4.130M finished population
    (0.000s) 4.130M starting radix ext sort
    (0.090s) 4.130M finished radix ext sort

Usage
-----
Usage is pretty straight forward: 

    require_once 'TypedArray.php';
    
    $stuff = new TypedArray();
    $stuff[] = 1;
    $stuff[] = 2;
    $stuff[] = 3;
    foreach( $stuff as $item ){
      print "item: $item\n";
    }

You can use TypedArray like an array with the caveat that PHP does not support sending objects into the array_* functions.

By default TypedArray assumes that the type of data you're storing is a 32 bit unsigned long but you can tell it that you want to store smaller types:

    $l = new TypedArray();                     // create an empty TypedArray for 32 bit unsigned longs
    $c = new TypedArray(array(1,2,3), "C");    // prepopulate $c with 8 bit unsigned chars
    $i = new TypedArray(pack("III",1,2,3));    // prepopulate $i with 16 bit unsigned integers
     
There's also a couple of convenience functions:

    $a = new TypedArray(array(1,2,3));
    $a->merge(array(4,5,6));                   // appends new array to the end of TypedArray
    $a->merge(pack("LLL", 7,8,9));             // same thing but with packed binary buffer 
    $a->merge(new TypedArray(array(1,4,6,8))); // same thing but with another TypedArray
    
    $num = $a->count();                        // count the number of items in the TypedArray
    $b = $a->slice(2,3);                       // slice out a subset into a new TypedArray
    $b = $a->slice(2);                         // if limit is excluded it slices to the end
    $arry = $b->asArray();                     // export a true PHP array (watch out, it might be big) 

    $a->sort();                                // sort() is very inefficient. I use my radix sort PHP extension
                                               // for sorting so I just stuck in a code path for anybody that doesn't
                                               // have a good fast sort extension 

And you probably want to take that buffer and store it somewhere so you don't have to recreate it everytime you need it:

  $myApp->set("some/awesome/key.bin", $a->buffer);
  $a = new TypedArray($myApp->get("some/awesome/key.bin"));


Caveats
-------
* You can hurt yourself with this class. 
* Don't mix different types of TypedArrays or planes will fall out of the sky. 
* There is currently no support for named / ordered keys. I will probably never support named keys but I would like to support numeric keys that are preserved after sorting.
* if you're loading up a lot of data at once you should probably pack() it yourself and pass it to the constructor. The array accessor stuff in PHP is pretty slow when you're hitting them hard.

TODO
----
* numeric keys that are preserved after sorting
* FoR compression might be cool
* add convenience array methods as I need them

Thanks
------
Thanks for taking a couple of minutes out of your day to consider saving a little memory on your PHP app servers!

cheers,
james
