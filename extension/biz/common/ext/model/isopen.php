<?php
function isOpenMethod($module, $method)
    {
        if(in_array("$module.$method", $this->config->openMethods)) return true;

        if($module == 'block' and $method == 'main' and isset($_GET['hash'])) return true;

        if($this->loadModel('user')->isLogon() or ($this->app->company->guest and $this->app->user->account == 'guest'))
        {
            if(stripos($method, 'ajax') !== false) return true;
            if($module == 'my' and $method == 'guidechangetheme') return true;
            if($module == 'testcase' and $method == 'editapi') return true;
			if($module == 'misc' and $method == 'downloadclient') return true;
            if($module == 'misc' and $method == 'changelog')  return true;
		    if($module == 'testtask' and $method == 'auto')  return true;
            if($module == 'tutorial' and $method == 'start')  return true;
            if($module == 'tutorial' and $method == 'index')  return true;
            if($module == 'tutorial' and $method == 'quit')   return true;
            if($module == 'tutorial' and $method == 'wizard') return true;
            if($module == 'block' and $method == 'admin') return true;
            if($module == 'block' and $method == 'set') return true;
            if($module == 'block' and $method == 'sort') return true;
            if($module == 'block' and $method == 'resize') return true;
            if($module == 'block' and $method == 'dashboard') return true;
            if($module == 'block' and $method == 'printblock') return true;
            if($module == 'block' and $method == 'main') return true;
            if($module == 'block' and $method == 'delete') return true;
            if($module == 'product' and $method == 'showerrornone') return true;
            if($module == 'report' and $method == 'annualdata') return true;
        }
        return false;
    }
