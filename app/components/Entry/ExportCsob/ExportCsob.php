<?php

namespace OOB;

use Model\System\User;
use Model\App\Race;
use Model\App\Entry;
use Nette\Utils\Strings;

class ExportCsob extends \Nette\Application\UI\Control {

   /**
    *
    * @var \DibiFluent
    */
   protected $entries;

   public function getEntries() {
      return $this->entries;
   }

   /**
    * Required fields:
    * registration, categrory, SINumber, racer, licence, note
    * @param \DibiFluent $entries 
    */
   public function setEntries($entries) {
      $this->entries = $entries;
   }

   public function render() {

      $club = \Nette\Environment::getConfig("club");
      foreach ($this->entries->fetchAll() as $entry) {
	 $reg = $club["code"];
	 $reg .= $entry->registration ? $entry->registration : "xxxx";
	 $category = $entry->category;
	 $SI = $entry->SINumber;
	 $racer = $entry->racer;
	 $licence = $entry->licence ? $entry->licence : "X";
	 $note = Strings::replace(Strings::replace($entry->note, "/\r\n/", "\n"), "/\n/", " ");
	 
	 echo Strings::padRight($reg, 7);
	 echo " ";
	 echo Strings::padRight($category, 10);
	 echo " ";
	 echo Strings::padLeft($SI, 10, '0');
	 echo " ";
	 echo Strings::padRight($racer, 25);
	 echo " ";
	 echo $licence;
	 
	 if($note){
	    echo " ";
	    echo $note;
	 }
	 
	 echo "\r\n";	 
      }
      
   }

}