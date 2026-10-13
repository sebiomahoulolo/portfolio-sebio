<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public const BUDGETS = [
        'moins-500k' => 'Moins de 500 000 FCFA',
        '500k-1500k' => '500 000 à 1 500 000 FCFA',
        '1500k-3m' => '1 500 000 à 3 000 000 FCFA',
        'plus-3m' => 'Plus de 3 000 000 FCFA',
        'a-definir' => 'À définir ensemble',
    ];

    protected $errorBag = 'contact';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(ContactMessage::TYPES))],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+().\s-]{6,40}$/'],
            'company' => ['nullable', 'string', 'max:160'],
            'subject' => ['required', 'string', 'min:3', 'max:190'],
            'budget' => ['nullable', Rule::in(array_keys(self::BUDGETS))],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            // Pièges à robots : champ invisible qui doit rester vide.
            'website' => ['present', 'max:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'email' => 'adresse email',
            'phone' => 'téléphone',
            'company' => 'entreprise',
            'subject' => 'sujet',
            'message' => 'message',
        ];
    }

    public function messages(): array
    {
        return [
            'website.max' => 'Votre message n\'a pas pu être envoyé.',
            'website.present' => 'Votre message n\'a pas pu être envoyé.',
            'message.min' => 'Décrivez votre besoin en quelques phrases (20 caractères minimum).',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return url()->previous().'#contact';
    }
}
