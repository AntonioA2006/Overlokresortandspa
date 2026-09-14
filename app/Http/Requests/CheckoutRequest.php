<?php

namespace App\Http\Requests;

class CheckoutRequest extends SearchAvailabilityRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return parent::rules();
    }
}
