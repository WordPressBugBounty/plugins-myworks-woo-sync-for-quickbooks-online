<?php

QuickBooks_Loader::load('/QuickBooks/IPP/Object.php');

class QuickBooks_IPP_Object_BillEmailBcc extends QuickBooks_IPP_Object
{
	protected function _order()
	{
		return array(
			'Address' => true, 
			); 
	}
}
