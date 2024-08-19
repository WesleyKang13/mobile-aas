<?php

namespace App\Classes;

/**
 * Simple Form Builder implementation (has hardcoded bootstrap wrappers in the class)
 * @author
 */

class FormBuilder {

    /**
     * [$_open Form Open State]
     * @var boolean
     */
    private static $_open = false;
    /**
     * [$_input Default input values for fields]
     * @var array
     */
    private static $_input = [];
    /**
     * [$_errors Error placeholder (Laravel errors())]
     * @var null
     */
    private static $_errors = NULL;

    /**
     * Generate Form Open Tag
     * @param  string $action  Form URL
     * @param  string $method  Form Method (default is post)
     * @param  array  $options Array of options to apply to form tag
     * @return string Rendered Open Tag
     */
    public static function open($action = '', $method = 'post', $options = []) {

        if (static::$_open == true) {
            throw new Exception('FormBuilder - Form is already open.');
        }

        static::$_open = true;
        static::$_input = [];
        static::$_errors = null;

        if ($method == '')
            $method = 'post';

        return "<form method=\"{$method}\" action=\"{$action}\" ".static::getOptions($options).">";
    }

    /**
     * Generate Form Close Tag
     * @return string Rendered Close Tag
     */
    public static function close() {
        if (static::$_open == false) {
            throw new Exception('FormBuilder - Form is not open, cannot close and unopened form.');
        }

        return '</form>';

    }

    /**
     * Set Form Defaults/input
     * @param array|object $input Input array or object
     */
    public static function setInput($input = []) {
        if (is_array($input))
            static::$_input = $input;
        elseif (is_object($input))
            static::$_input = $input->toArray();
        else
            static::$_input = null;

    }

    /**
     * Set Form ERrors
     * @param object $errors Laravel Error Object
     */
    public static function setErrors($errors = [] ) {
        static::$_errors = $errors;

    }

    /**
     * check for errors
     * @param  string  $name fields anme
     * @return boolean|string       returns false or error string
     */
    private static function hasError($name = '') {
        if (static::$_errors === null)
            return false;

        if (!is_object(static::$_errors))
            return false;

        return static::$_errors->first($name);

    }

    /**
     * Generate HTML ooptions from array
     * @param  array  $options key indexed array of options
     * @return string string of rendered options
     */
    private static function getOptions($options = []) {
        $return = '';
        foreach ($options as $key => $value) {
            $return .= " {$key}=\"$value\"";
        }

        return $return;
    }


    /**
     * Wrap input in html formatted
     * @param  string $input html input/form tag
     * @param  string $name  field name
     * @param  [type] $label form field label (optional)
     * @return string rendered wrapped html form element
     */
    private static function wrapInput($input = 'No Input', $name = 'no-name', $label = NULL){
        if ($label === NULL)
            $label = ucfirst($name);

        $error = static::hasError($name);



        $return =  "<div class=\"form-group row\">\n";

        if ($label != '' and $label !== NULL) {
                    $return .= "<label for=\"{$name}\" class=\"col-sm-4 col-form-label\">{$label}</label>\n
                <div class=\"col-sm-8\">\n";
        }else{
            $return .= "<div class=\"col-sm-12\">\n";
        }

        $return .= $input."\n
                        <div class=\"invalid-feedback\">{$error}</div>\n
                    </div>\n
                </div>\n";

        return $return;
    }


    /**
     * Generate Input Tag
     * @param  string $name    Field Name
     * @param  string $type    Field Type
     * @param  array  $options array of html options
     * @return string          Rendered input
     */
    private static function genInput($name = 'input-name', $type = 'text', $options = [] ) {
        $class = 'form-control ';
        $value = '';
        if (is_array(static::$_input) and isset(static::$_input[$name]))
            $value = static::$_input[$name];

        $value = old($name, $value);
        $required = '';

        if (static::hasError($name))
            $class .= 'is-invalid ';

        if (array_key_exists('class', $options)){
            $class .= $options['class'];
            unset($options['class']);
        }

        if (array_key_exists('required', $options)){
            $required =' required';
            unset($options['required']);
        }


        return "<input type=\"{$type}\" name=\"{$name}\" class=\"{$class}\"  value=\"{$value}\" ".static::getOptions($options)." {$required}>";

    }


    /**
     * Generate Text Area Tag
     * @param  string  $name    Field Name
     * @param  integer $rows    Number of rows (optons)
     * @param  array   $options array of html optoins
     * @return string           Renered textarea
     */
    private static function genTextarea($name = 'input-name', $rows = 5, $options = [] ) {
        $class = 'form-control ';
        $value = '';
        if (is_array(static::$_input) and isset(static::$_input[$name]))
            $value = static::$_input[$name];
        $value = old($name, $value);
        $required = '';

        if (static::hasError($name))
            $class .= 'is-invalid';

        if (array_key_exists('class', $options)){
            $class .= $options['class'];
            unset($options['class']);
        }

        if (array_key_exists('required', $options)){
            $required =' required';
            unset($options['required']);
        }


        return "<textarea name=\"{$name}\" class=\"{$class}\"  rows=\"{$rows}\" ".static::getOptions($options)." {$required}>{$value}</textarea>";

    }

    /**
     * Generate Select Tah
     * @param  string $name    Field Name
     * @param  array  $values  Array of key indexed value
     * @param  array  $options array of html options
     * @return string          Rendered select tag
     */
    private static function genSelect($name = 'input-name', $values = [], $options = [] ) {
        $class = 'form-control ';
        $selected = '';
        if (is_array(static::$_input) and isset(static::$_input[$name]))
            $selected = static::$_input[$name];
        $selected = old($name, $selected);


        $required = '';

        if (static::hasError($name))
            $class .= 'is-invalid';

        if (array_key_exists('class', $options)){
            $class .= $options['class'];
            unset($options['class']);
        }

        if (array_key_exists('required', $options)){
            $required =' required';
            unset($options['required']);
        }

        $output = "<select name=\"{$name}\" class=\"{$class}\"  ".static::getOptions($options)." {$required}>\n";

        foreach ($values as $k => $v) {
            $output .= "<option value=\"{$k}\" ";
            if ($k == $selected)
                $output .= ' selected ';
            $output .= ">{$v}</option>\n";
        }


        $output .= "</select>\n";

        return $output;

    }


    /**
     * Generate Form Input Tag
     * @param  string $name    Field name
     * @param  string $label   Field Label
     * @param  array  $options array of html tag options (optional)
     * @return string          Rendered Form Tag
     */
    public static function input($name, $label, $options = [], $nowrap=false) {
        $input = static::genInput($name, 'text', $options);
        if ($nowrap)
            return $input;
        return static::wrapInput($input, $name, $label);
    }


    /**
     * Generate Password Input Tag
     * @param  string $name    Field name
     * @param  string $label   Field Label
     * @param  array  $options array of html tag options (optional)
     * @return string          Rendered Form Tag
     */
    public static function password($name, $label, $options = [], $nowrap=false) {
        $input = static::genInput($name, 'password', $options);
        if ($nowrap)
            return $input;
        return static::wrapInput($input, $name, $label);
    }

    /**
     * Generate Select Input Tag
     * @param  string $name    Field name
     * @param  string $label   Field Label
     * @param  array  $values  Array of key value options
     * @param  array  $options array of html tag options (optional)
     * @return string          Rendered Form Tag
     */
    public static function select($name, $label, $values = [], $options = [], $nowrap = false) {
        $input = static::genSelect($name, $values, $options);
        if ($nowrap)
            return $input;
        return static::wrapInput($input, $name, $label);
    }

    /**
     * Generaate Yes No Input Tag
     * @param  string $name    Field name
     * @param  string $label   Field Label
     * @param  array  $options array of html tag options (optional)
     * @return string          Rendered Form Tag
     */
    public static function yesNo($name, $label, $options = [], $nowrap =false) {
        $input = static::genSelect($name, ['No', 'Yes'], $options);
        if ($nowrap)
            return $input;
        return static::wrapInput($input, $name, $label);
    }


    /**
     * Generate Textarea input tag
     * @param  string $name    Field name
     * @param  string $label   Field Label
     * @param  integer $rows   Number of rows (optional)
     * @param  array  $options array of html tag options (optional)
     * @return string          Rendered Form Ta
     */
    public static function textarea($name, $label, $rows = 5, $options = [], $nowrap = false) {
        $input = static::genTextarea($name, $rows, $options);
        if ($nowrap)
            return $input;

        return static::wrapInput($input, $name, $label);
    }


    /**
     * Generate Hidden Input Tag
     * @param  string $name    Field name
     * @param  string $value   Value to use if not already set in SetInput()
     * @param  array  $id      Option ID tag value
     * @return string          Rendered Form Tag
     */
    public static function hidden($name, $value = '', $id = '') {

        if (is_array(static::$_input) and isset(static::$_input[$name]))
            $value = static::$_input[$name];
        $value = old($name, $value);

        if ($id !='')
            $id = 'id="'.$id.'" ';


        return "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\" {$id}/>";
    }


    /**
     * Generate Submit Input Tag
     * @param  string $labal   Button Label
     * @param  array  $options array of html tag options (optional)
     * @return string          Rendered Form Tag
     */
    public static function submit($label, $options = [], $nowrap = false) {
        $input = '';
        $name = 'submit';
        $class = 'btn btn-primary';
        if (isset($options['class'])) {
            $class .= $options['class'];
            unset($options['class']);
        }
        $input = "<input type=\"submit\" value=\"{$label}\" class=\"{$class}\" ".static::getOptions($options)." />";
        if ($nowrap)
            return $input;
        return static::wrapInput($input, $name, '&nbsp;');
    }

    public static function saveAndNew(){
        $checked = old('_save_and_new', '');

        if ($checked == 1 or $checked == true or strtolower($checked) == 'on')
            $checked = ' checked';
        else
            $checked = '';

        return "<div class=\"form-check form-check-inline\">
                <input class=\"form-check-input\" type=\"checkbox\" name=\"_save_and_new\" {$checked}>
                <label class=\"form-check-label\" for=\"_save_and_new\">Save &amp; New</label>
            </div>";
    }

    /**
     * Generate Form Date Tag
     * @param  string $name    Field name
     * @param  string $label   Field Label
     * @param  array  $options array of html tag options (optional)
     * @return string          Rendered Form Tag
     */
    public static function date($name, $label, $options = [], $nowrap=false) {
        $input = static::genInput($name, 'date', $options);
        if ($nowrap)
            return $input;
        return static::wrapInput($input, $name, $label);
    }


    /**
     * Generate Form File Tag
     * @param  string $name    Field name
     * @param  string $label   Field Label
     * @param  array  $options array of html tag options (optional)
     * @return string          Rendered Form Tag
     */
    public static function file($name, $label, $options = [], $nowrap=false) {
        $input = static::genInput($name, 'file', $options);
        if ($nowrap)
            return $input;
        return static::wrapInput($input, $name, $label);
    }
}
