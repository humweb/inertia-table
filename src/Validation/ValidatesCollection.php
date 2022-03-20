<?php

namespace Humweb\Table\Validation;

trait ValidatesCollection
{
    /**
     * @param  array  $input  Input variables
     *
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateFilterInput($input, $itemKey = 'field')
    {
        validator()->validate($input, $this->getValidationRules($itemKey));
    }

    /**
     * @return array
     */
    public function getValidationRules($key)
    {
        return $this->filter(function ($item) {
            return !empty($item->rules);
        })->mapWithKeys(function ($item) use ($key) {
            return [$item->$key => $item->rules];
        })->all();
    }

}
