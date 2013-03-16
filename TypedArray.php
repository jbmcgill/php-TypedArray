<?
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

class TypedArrayNotSupportedException extends Exception {}

class TypedArray implements ArrayAccess, Iterator{
  public $buffer;
  private $index = 0;
  private $type = "L";
  private $typeSize = 4;
  public function __construct($a=null, $type="L"){
    $this->type = $type;
    switch( $type ){
      case "c":
      case "C": $this->typeSize=1; break;
      case "s":
      case "S": $this->typeSize=2; break;
      case "l":
      case "L": $this->typeSize=4; break;
      default: throw new TypedArrayNotSupportedException("type not supported");
    }
    
    if(  is_array($a) ){
      $size = count($a);
      $this->buffer = "";
      for( $i=0; $i< $size; $i++ ){
        $this->buffer .= pack($this->type,$a[$i]);
      }
    }else if ( ! is_null($a) ){
      $this->buffer = $a;
    }
  }
  public function offsetSet($offset,$value){
    if( ! is_numeric($value) ){
      throw new TypedArrayUnsupportedException("non-numeric keys not supported");
    }
    if( is_null($offset) || $offset == strlen($this->buffer)/$this->typeSize +1 || $offset == 0){
      $this->buffer .= pack($this->type, $value);
    }else if ($offset <= strlen($this->buffer)/$this->typeSize ){
      $offsetBytes = $offset*$this->typeSize;
      $word = pack($this->type,$value);
      $this->buffer{$offsetBytes} = $word{0};
      $this->buffer{$offsetBytes+1} = $word{1};
      $this->buffer{$offsetBytes+2} = $word{2};
      $this->buffer{$offsetBytes+3} = $word{3};
    }else{
      throw new TypedArrayNotSupportedException("monkey man doesn't want to set this offset");
    }
  }
  public function offsetExists($offset){
    return strlen($this->buffer)/$this->typeSize > $offset;
  }
  public function offsetUnset($offset){
    throw new TypedArrayNotSupportedException("unsetting offset not supported");
  }
  public function offsetGet($offset){
    $offsetBytes = $offset * $this->typeSize ;
    return (strlen($this->buffer)/$this->typeSize > $offset) ? ord($this->buffer{$offsetBytes}) | 
                                                 (ord($this->buffer{$offsetBytes+1})<<8) | 
                                                 (ord($this->buffer{$offsetBytes+2})<<16) | 
                                                 (ord($this->buffer{$offsetBytes+3})<<24) : null;
  }
  public function asArray(){
    return array_slice(unpack($this->type."*",$this->buffer),0);
  }
  public function slice($offset,$length=null){
    return (is_null($length)) ? new TypedArray(substr($this->buffer,$offset*$this->typeSize)) : new TypedArray(substr($this->buffer,$offset*$this->typeSize,$length*$this->typeSize));
  }
  public function merge($a){
    if( $a instanceOf TypedArray ){
      if( $a->type != $this->type ) throw new TypedArrayNotSupportedException("merging different types not supported");
      $this->buffer .= $a->buffer;
    }else if( is_array($a) ){
      $size = count($a);
      for( $i=0; $i<$size; $i++ ){
        $this->buffer .= pack($this->type,$a[$i]);
      } 
    }else if( ! is_null($a) ){
      $this->buffer .= $a;
    }
  }
  public function count(){
    return ( empty($this->buffer) ) ? 0 : strlen($this->buffer)/$this->typeSize;
  }
  public function sort(){
    if( ! extension_loaded('RadixSort') ){
      $a = unpack($this->type."*",$this->buffer);
      sort($a);
      $this->buffer = "";
      $size = count($a);
      for( $i=0; $i<$size; $i++ ){
        $this->buffer .= pack($this->type,$a[$i]);
      }
    }else{
      radixsort($this->buffer,strlen($this->buffer)/$this->typeSize);
    }
  }
  public function rewind(){$this->index=0;}
  public function current(){ return $this->offsetGet($this->index); }
  public function key(){ return $this->index; }
  public function next(){ $this->index++; }
  public function valid(){ return (strlen($this->buffer)/$this->typeSize) > $this->index;}
}

