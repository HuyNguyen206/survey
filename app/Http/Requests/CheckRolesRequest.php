<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CheckRolesRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
			'name' => 'required|max:50|unique:roles',
			'description' => 'max:200',
			'level' => 'integer|min:1',
			'rate' => 'integer|min:0|max:1'
        ];
    }
}
