<?php
/**
Copyright 2013 James Brian McGill

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_BAIL,1);
assert_options(ASSERT_QUIET_EVAL, 1);

function test($code, $desc){ return "$code /** $desc */";}
function assert_handler($file, $line, $code, $desc=null){
  echo "[FAIL] $file:$line: $code $desc\n";
}
assert_options(ASSERT_CALLBACK, 'assert_handler');

assert("true /**people overcomplicate unit testing*/");

require_once 'TypedArray.php';

$desc = "when constructed with an array TypedArray should be populated"; 
$arry = array(1,2,3,4,5);
$a = new TypedArray($arry);
$barry = $a->asArray();
assert(test('$arry===$barry', $desc));

$desc ="when constructed with a packed buffer TypedArray should be populated";
$buff = pack("LLL", 5,55,500);
$a = new TypedArray($buff);
$arry = array(5,55,500);
$barry = $a->asArray();
assert(test('$arry==$barry', $desc));

$desc = "count() should return number of items in TypedArray";
$a = new TypedArray(array(3,4,5));
assert(test('$a->count()===3', $desc));

$desc = "slice(offset,limit) should return a TypedArray that contains a slice of the array";
$a = new TypedArray(array(1,2,3,4,5,6,7,8,9,10));
$b = $a->slice(2,2);
assert(test('$b->asArray() === array(3,4)', $desc));

$desc = "slice(offset) should return a TypedArray that contains a slice of the array to the end";
$a = new TypedArray(array(1,2,3,4,5,6,7,8,9,10));
$b = $a->slice(8);
assert(test('$b->asArray() === array(9,10)', $desc));

$desc = "merge() should work with a TypedArray";
$a = new TypedArray(array(1,2,3));
$b = new TypedArray(array(4,5,6));
$a->merge($b);
assert(test('$a->asArray() === array(1,2,3,4,5,6)', $desc));

$desc = "merge() should work with a buffer";
$a = new TypedArray(array(1,2,3));
$a->merge(pack("LLL",4,5,6));
assert(test('$a->asArray() === array(1,2,3,4,5,6)', $desc));

$desc = "merge() should work with an array";
$a = new TypedArray(array(1,2,3));
$a->merge(array(4,5,6));
assert(test('$a->asArray() == array(1,2,3,4,5,6)', $desc));

$desc = "should be able to add items using array [] syntax";
$a = new TypedArray();
$a[] = 1;
$a[] = 2;
$a[] = 3;
assert(test('$a->asArray() === array(1,2,3)', $desc));

$desc = "BUGFIX: should be able to initialize buffer with [0] syntax";
$a = new TypedArray();
$a[0]=1;
assert(test('$a->buffer==pack("L",1)', $desc));

$desc = "should be able to add items using array[n] syntax";
$a = new TypedArray();
$a[0]=1;
$a[1]=2;
$a[2]=3;
assert(test('$a->asArray() === array(1,2,3)', $desc));

$desc = "should be able to read items out using array[n] syntax";
$a = new TypedArray(array(1,2,3,4));
assert(test('$a[2]===3', $desc));

$desc = "should be able to iterate over TypedArray";
$a = new TypedArray(array(1,2,3));
$buff = "";
foreach( $a as $item ){
  $buff .= $item;
}
assert(test('$buff==="123"', $desc));


$desc = "sort() should sort items";
$a = new TypedArray(array(3,2,1));
$a->sort();
assert(test('$a->asArray()===array(1,2,3)', $desc));


$desc = "should be able to create a TypedArray of type unsigned char";
$a = new TypedArray(array(1,2,3), "C");
assert(test('$a->asArray()===array(1,2,3)', $desc));

$a = new TypedArray(pack("CCC",1,2,3), "C");
assert(test('$a->asArray()===array(1,2,3)', $desc));

$desc = "should not be allowed to merge TypedArrays of different types";
$a = new TypedArray(array(1,2,3), "L");
$b = new TypedArray(array(4,5,6), "C");
$exceptionThrown = false;
try{
  $a->merge($b);
}catch( TypedArrayNotSupportedException $e){
  $exceptionThrown = true;
}
assert(test('$exceptionThrown == true', $desc));
print "[OK] all tests passed\n";
