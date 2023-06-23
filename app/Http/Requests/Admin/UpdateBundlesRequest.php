<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBundlesRequest extends FormRequest
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
            'courses' => 'required',
            'title' => 'required',
            'titulotendencia1' => 'required|string|max:100',
            'titulotendencia2' => 'required|string|max:100',
            'titulotendencia3' => 'required|string|max:100',
            'tendencia1' => 'required|string|max:1000',
            'tendencia2' => 'required|string|max:1000',
            'tendencia3' => 'required|string|max:1000',
            'titulocard1' => 'required|string|max:100',
            'titulocard2' => 'required|string|max:100',
            'titulocard3' => 'required|string|max:100',
            'card1' => 'required|string|max:1000',
            'card2' => 'required|string|max:1000',
            'card3' => 'required|string|max:1000',            
            'start_date' => 'nullable|date_format:'.config('app.date_format'),
        ];
    }
}
