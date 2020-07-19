<?php

namespace App\Http\Requests\Admin\Application;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreApplication extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('admin.application.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string',"max:100"],
            'url' => ['required', 'string'],
            'icon' => ['nullable', 'string',"string"],
            'description' => ['nullable', 'string'],
            'isNewPage' => ['nullable', 'boolean'],
            'isNewPageForIframe' => ['nullable', 'boolean'],
            'activated' => ['required', 'boolean'],
            'isFeatured' => ['required', 'boolean'],
            'category_id' => ['required', 'numeric'],
        ];
    }

    /**
    * Modify input data
    *
    * @return array
    */
    public function getSanitized(): array
    {
        $sanitized = $this->validated();
        $sanitized['category_id'] = $this->getCategoryId();

        //Add your code for manipulation with request data here

        return $sanitized;
    }

    public function getCategoryId(){
        if ($this->has('category')){
            return $this->get('category')['id'];
        }
        if ($this->has('category_id')){
            return $this->get('category_id');
        }
        return null;
    }
}
