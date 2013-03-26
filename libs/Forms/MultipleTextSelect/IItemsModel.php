<?php

/**
 *
 * @author   Michal Koutny
 */

namespace OOB\Forms;

use Nette;
use Nette\Forms;

interface IItemsModel {
   /**
    * @param   int   id
    * @return  string
    */
   public function IdToName($id);

   /**
    * @param   string	name
    * @param   bool	specifies whether to insert unknown values into DB or return NULL
    * @return  int|NULL	int when conversion succeeded, NULL whe fails (name doesn't exit and cannot be insered)
    */
   public function NameToId($name, $insert = false);

   /**
    * @return array  array of id => text
    */
   public function GetAllItems();
}
