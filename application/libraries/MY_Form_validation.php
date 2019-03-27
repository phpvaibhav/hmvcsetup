<?php
/**
 * Description of MY_Form_validation
 *
 * @author Moboweb Team
 */
class MY_Form_validation extends CI_Form_validation {

    private $_rest_validation = false;
    public $_config_rules = '';
    public $CI;  //required for callabck validation to work with hmvc modules

    function __construct($rules = array()) {
        parent::__construct($rules);
    }
    
}
