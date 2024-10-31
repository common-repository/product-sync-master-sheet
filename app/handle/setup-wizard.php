<?php
namespace PSSG_Sync_Sheet\App\Handle;

use PSSG_Sync_Sheet\App\Core\Admin_Base;
use PSSG_Sync_Sheet\App\Service\Standalone;

class Setup_Wizard extends Admin_Base 
{

    use Standalone;

    
    public $Admin_Base;
    public $Admin_Base_Settings;

    public function __construct()
    {
        parent::__construct();
        $this->Admin_Base = $this->Sheet->Admin_Base;
        $this->Admin_Base_Settings = $this->Admin_Base->settings ?? [];
        
        
        
    }

    
}