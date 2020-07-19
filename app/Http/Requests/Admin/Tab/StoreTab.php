<?php

namespace App\Http\Requests\Admin\Tab;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTab extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('admin.tab.create');
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
            'body' => ['nullable', 'string'],
            'tabtype_id' => ['nullable', 'numeric'],
            'url' => ['nullable', 'string'],
            'category_id' => ['nullable'],
            'activated' => ['required','boolean']
        ];
    }

    /**
    * Modify input data
    *
    * @return array
    */
    public function getSanitized($tabtype): array
    {

        $sanitized = $this->validated();

        $sanitized['category_id'] = $this->getCategoryId();
        
        if($tabtype->body_required && (((!isset($sanitized['body'])) || $sanitized['body'] == "")) ){
            $json = [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'body' => ['The body field is required.']
                ]
            ];
            throw new HttpResponseException(response()->json($json, 422)); 
        }

        if($tabtype->url_required && (((!isset($sanitized['url'])) || $sanitized['url'] == "")) ){
            $json = [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'url' => ['The url field is required.']
                ]
            ];
            throw new HttpResponseException(response()->json($json, 422)); 
        }
        if($tabtype->category_required && (((!isset($sanitized['category_id'])) || $sanitized['category_id'] == "")) ){
            $json = [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'category_id' => ['The category field is required.']
                ]
            ];
            throw new HttpResponseException(response()->json($json, 422)); 
        }

        
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
    public function getTabtypeId(){
        if ($this->has('tabtype')){
            return $this->get('tabtype')['id'];
        }
        return null;
    }
}
