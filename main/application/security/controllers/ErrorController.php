<?php

class application_security_controllers_Error extends appcore_command_Command {

    public function displayError() {
        $arrayError = unserialize($this->getRequest());
        return $arrayError;
    }

    public function evaluate($value) {
        $red = '#BF656B';
        $green = '#75A574';
        switch ($value) {
            case 'module':
                if (!file_exists('application/' . $this->getRequest()->getProperty('module', 'index')))
                    $html = '<span style="color:' . $red . '">Not exist module <strong>' . $this->getRequest()->getProperty('module', 'index') . '</strong> required.</span>';
                else
                    $html = '<span style="color:' . $green . '">Module ' . $this->getRequest()->getProperty('module', 'index') . ' found.</span>';
                break;
            case 'cmd':
                if (!file_exists('application/' . $this->getRequest()->getProperty('module', 'index') . '/controllers/' . ucwords($this->getRequest()->getProperty('cmd', 'Index')) . 'Controller.php'))
                    $html = '<span style="color:' . $red . '">Not exist controller ' . ucwords($this->getRequest()->getProperty('cmd', 'Index')) . 'Controller.php required.</span>';
                else
                    $html = '<span style="color:' . $green . '">Controller ' . ucwords($this->getRequest()->getProperty('cmd', 'Index')) . 'Controller.php found.</span>';
                break;
            case 'func':
                if ($this->getRequest()->getProperty('func', 0) == 0)
                    if ($this->getRequest()->getProperty('cmd', 0) == 0) {
                        $view = 'Index';
                        $cmd = 'Index';
                    } else {
                        $view = $this->getRequest()->getProperty('cmd', 0);
                        $cmd = $this->getRequest()->getProperty('cmd', 0);
                    }
                else
                    $view = $this->getRequest()->getProperty('func', 0);

                if (!file_exists('application/' . $module . '/views/' . ucwords($cmd) . '/' . $view))
                    $html = '<span style="color:' . $red . '">Not exist the view ' . ucwords($controller) . ' required.</span>';
                else
                    $html = '<span style="color:' . $green . '">The view ' . ucwords($controller) . ' found.</span>';
                break;
        }
        return $html;
    }

}