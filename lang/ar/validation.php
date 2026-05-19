<?php

return [
    'required' => 'حقل :attribute مطلوب.',
    'email' => 'يجب أن يكون حقل :attribute بريدًا إلكترونيًا صالحًا.',
    'string' => 'يجب أن يكون حقل :attribute نصًا.',
    'max' => [
        'string' => 'يجب ألا يتجاوز حقل :attribute :max حرفًا.',
        'numeric' => 'يجب ألا يتجاوز حقل :attribute :max.',
    ],
    'min' => [
        'numeric' => 'يجب ألا يقل حقل :attribute عن :min.',
    ],
    'date' => 'يجب أن يكون حقل :attribute تاريخًا صالحًا.',
    'exists' => 'القيمة المحددة في :attribute غير صالحة.',
    'in' => 'القيمة المحددة في :attribute غير صالحة.',
    'boolean' => 'يجب أن يكون حقل :attribute صحيحًا أو خطأ.',
    'integer' => 'يجب أن يكون حقل :attribute عددًا صحيحًا.',
];
