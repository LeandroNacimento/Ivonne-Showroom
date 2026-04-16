<?php

return [
    'default' => 'alpha',
    'one_size_type' => 'one_size',
    'one_size_value' => 'UNICO',
    'one_size_label' => 'Único',
    'one_size_availability_label' => 'Talle único',

    'types' => [
        'alpha' => [
            'label' => 'XS / S / M / L / XL / XXL',
            'values' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
        ],
        'numeric_1_5' => [
            'label' => '1 / 2 / 3 / 4 / 5',
            'values' => ['1', '2', '3', '4', '5'],
        ],
        'numeric_36_48' => [
            'label' => '36 / 38 / 40 / 42 / 44 / 46 / 48',
            'values' => ['36', '38', '40', '42', '44', '46', '48'],
        ],
        'one_size' => [
            'label' => 'Talle único',
            'values' => ['UNICO'],
        ],
    ],
];
